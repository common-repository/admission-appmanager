<?php

use Carbon\Carbon;

class ApplicationHelper
{

    private $application_id;

    private $application;

    public $application_data;

    private $client;

    private $combination_data;

    private $combination;

    private $deadline;

    private $steps;

    private $table_name = 'aam_applications';

    public $documents;

    public $bo_days = array();

    public function __construct($application_id)
    {
        $this->application_id = $application_id;
    }

    /**
     * Set application data
     */
    public function set_application()
    {
        $this->application = get_post($this->application_id);
    }

    /**
     * Set client data
     */
    public function set_client()
    {
        $client_id = get_post_meta($this->application_id, '_aam_application_client', true);

        if ($client_id) {
            $this->client = get_user_by('id', $client_id);
        }

        $this->_set_bo_days();
    }

    /**
     * Set blackout days from client and consultant BO days
     */
    private function _set_bo_days()
    {
        $client_bo_days = get_user_meta($this->client->ID, 'aam_bo_days', true);
        $consultant_bo_days = get_user_meta(get_current_user_id(), 'aam_bo_days', true);

        if (!$client_bo_days)
            $client_bo_days = array();

        if (!$consultant_bo_days)
            $consultant_bo_days = array();

        $this->bo_days = array_merge($client_bo_days, $consultant_bo_days);
    }

    public function set_combination_data()
    {
        $this->combination_data = get_post_meta($this->application_id, '_aam_application_combination', true);
    }

    public function set_combination()
    {
        if ($this->combination_data) {
            $this->combination = get_post($this->combination_data['combination']);
        }
    }

    /**
     * Set deadline for selected round
     */
    public function set_deadline()
    {
        if ($this->combination) {
            $deadlines = get_post_meta($this->combination->ID, '_aam_combination_deadlines', true);
            $this->deadline = $deadlines[$this->combination_data['round'] - 1];
        }
    }

    /**
     * Get steps from database
     */
    public function set_steps()
    {
        global $wpdb;

        $app_table = $wpdb->prefix . 'aam_applications';

        $this->steps = $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM $app_table WHERE application_id = %d", $this->application_id)
        );
    }

    /**
     * Set documents
     */
    public function set_documents()
    {
        $documents = get_post_meta($this->combination->ID, '_aam_combination_documents', true);

        $this->documents = array();

        $sum = 0;

        foreach ($documents as $dk => $document) {
            $documentSteps = get_post_meta($document['type'], '_aam_type_steps', true);

            $this->documents[$dk] = array(
                'data' => $document,
                'doc' => get_post($document['type']),
                'steps' => array()
            );

            $sum += $document['weight'];

            foreach ($documentSteps as $sk => $step) {
                $this->documents[$dk]['steps'][$sk] = $step;
            }
        }

        $this->sum = $sum;
    }

    /**
     * Set application data
     * @param bool $only_active
     */
    public function set_application_data($only_active = true)
    {
        global $wpdb;

        $table_name = $wpdb->prefix . $this->table_name;

        if ($only_active) {
            $this->application_data = $wpdb->get_results(
                $wpdb->prepare("SELECT * FROM $table_name WHERE application_id = %d AND active = 1", $this->application_id),
                ARRAY_A
            );
        } else {
            $this->application_data = $wpdb->get_results(
                $wpdb->prepare("SELECT * FROM $table_name WHERE application_id = %d", $this->application_id),
                ARRAY_A
            );
        }
    }

    /**
     * Calculate deadlines for application
     * @param bool $save
     */
    public function calc_deadlines($save = false)
    {
        $this->set_application();

        $this->set_application_data();

        $this->set_combination_data();

        $this->set_combination();

        $this->set_deadline();

        $this->set_steps();

        $this->set_documents();

        $createdDate = new Carbon($this->application->post_date);
        $deadlineDate = new Carbon($this->deadline['date']);

        //calc diff between start and deadline dates
        $diff = $deadlineDate->diffInDays($createdDate);

        //sum all step days
        $days = 0;

        foreach ($this->application_data as $step) {
            $days += $step['step_days'];
        }

        //calculate off days
        $free_days = $this->calc_free_days($createdDate, $deadlineDate);

        //calc working days
        $working_days = $diff - $free_days;

        //var_dump($days); var_dump($working_days); die;

        if ($days > $working_days) {
            $this->recursive_decrease($days - $working_days);
        } else if ($days < $working_days) {
            $this->recursive_increase($working_days - $days);
        }

        //fb($this->documents);

        $this->set_deadlines($createdDate);

        //echo '<pre>'; var_dump($this->application_data); die;

        //fb($this->documents);

        if ($save) {
            $this->save_deadlines();
        }
    }

    /**
     * Calculate number of free days according to consultant free days and blackout days
     *
     * @param Carbon $createdDate
     * @param Carbon $deadlineDate
     *
     * @return int
     */
    public function calc_free_days(Carbon $createdDate, Carbon $deadlineDate)
    {
        $diff = $deadlineDate->diffInDays($createdDate);
        $free_days_option = get_user_meta(get_current_user_id(), 'aam_off_days', true);
        $free_days = $free_days_option ? array_keys($free_days_option) : array();

        $free_days_total = 0;

        $newDate = clone $createdDate;

        if (!empty($free_days) || !empty($this->bo_days)) {
            for ($i = 1; $i <= $diff; $i++) {
                $newDate = clone $newDate;
                $newDate->addDay();

                if ($this->check_bo_days($newDate) || in_array(strtolower($newDate->format('D')), $free_days)) {
                    $free_days_total++;
                }
            }
        }

        return $free_days_total;
    }

    /**
     * Check if selected date is between some of the blackout ranges
     * @param Carbon $date
     * @return bool
     */
    private function check_bo_days(Carbon $date)
    {
        $bo_days = array();

        foreach ($this->bo_days as $day) {
            if ($day['from'] && $day['to']) {
                $bo_days[] = array(
                    'from' => new Carbon($day['from']),
                    'to' => new Carbon($day['to'])
                );
            }
        }

        if (!empty($bo_days)) {
            foreach ($bo_days as $day) {
                if ($date->between($day['from'], $day['to'])) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * If document days is more then working days decrease by diff
     *
     * @param int $sub
     */
    public function recursive_decrease($sub = 0)
    {
        foreach ($this->application_data as $k => $step) {
            if ($sub > 0 && $step['step_min_days'] < $step['step_days']) {
                $this->application_data[$k]['step_days']--;

                $sub--;
            }
        }

        if ($sub > 0) {
            $this->recursive_decrease($sub);
        }
    }

    /**
     * If document days if less then working days increase by diff
     *
     * @param int $sub
     */
    public function recursive_increase($sub = 0)
    {
    	//var_dump($this->application_data); die;
        foreach ($this->application_data as $k => $step) {
            if ($sub > 0 && $step['step_max_days'] > $step['step_days']) {
                $this->application_data[$k]['step_days']++;

                $sub--;
            }
        }

        if ($sub > 0) {
        	//var_dump($sub);
            $this->recursive_increase($sub);
        }
    }

    /**
     * Set deadlines for all of the documents and steps
     *
     * @param Carbon $startDate
     */
    public function set_deadlines(Carbon $startDate)
    {
        $free_days_option = get_user_meta(get_current_user_id(), 'aam_off_days', true);
        $free_days = $free_days_option ? array_keys($free_days_option) : array();

        $probe = clone $startDate;

        foreach ($this->application_data as $sk => $step) {
            for ($i = 1; $i <= $step['step_days']; $i++) {
                $day = $probe->addDay();

                $newDay = $this->find_next_free($day, $free_days);
            }

            $this->application_data[$sk]['deadline'] = clone $newDay;
        }
    }

    /**
     * Recursive function for finding next available day
     *
     * @param Carbon $day
     * @param array $off_days
     *
     * @return Carbon
     */
    private function find_next_free(Carbon $day, $off_days = array())
    {
        if (in_array(strtolower($day->format('D')), $off_days) || $this->check_bo_days($day)) {
            $day->addDay();

            return $this->find_next_free($day, $off_days);
        } else {
            return $day;
        }
    }

    /**
     * Save deadlines to helper database
     */
    public function save_deadlines()
    {
        global $wpdb;

        $table_name = $wpdb->prefix . $this->table_name;

        foreach ($this->application_data as $step) {

            $wpdb->update(
                $table_name,
                array(
                    'deadline' => $step['deadline']->format('Y-m-d')
                ),
                array(
                    'id' => $step['id']
                )
            );
        }

        do_action('aam_save_deadlines', $this->application_data);
    }
}