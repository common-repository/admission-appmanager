<?php

use Carbon\Carbon;

class Aam_App_Dashboard
{

    private $application_id;
    private $step_id;
    private $document_id;
    private $steps;
    private $complete;

    private $date_format;

    public function __construct()
    {
        add_thickbox();

        $this->router();

        $this->date_format = get_option('date_format');
    }

    /**
     * Route various actions on application dashboard
     */
    public function router()
    {
        $success = false;

        if (isset($_POST['action']) && $_POST['action'] == 'delegate') {
            $success = $this->save_delegation();
        }

        if (isset($_POST['action']) && $_POST['action'] == 'change_status') {
            $success = $this->save_status();
        }

        if (isset($_GET['action']) && $_GET['action'] == 'change_activity') {
            $success = $this->change_activity();
        }

        if (isset($_REQUEST['application_id'])) {
            $this->application_id = $_REQUEST['application_id'];

            $this->step_id = isset($_REQUEST['step_id']) ? $_REQUEST['step_id'] : 0;
            $this->document_id = isset($_REQUEST['document_id']) ? $_REQUEST['document_id'] : 0;

            $this->display_dashboard($success);
        } else {
            $this->select_application();
        }
    }

    /**
     * Display application dashboard
     */
    public function display_dashboard($success)
    {
        $query = array(
            'post_type' => 'application',
            'p' => $this->application_id
        );

        if (!current_user_can('manage_options')) {
            $query['author'] = get_current_user_id();
        }

        $application = new WP_Query($query);

        //if no application with those params redirect to applications
        if (!$application->have_posts()) {
            $this->select_application();
        }

        while ($application->have_posts()) : $application->the_post();

            //get client
            $client_id = get_post_meta(get_the_ID(), '_aam_application_client', true);
            $client = get_user_by('id', $client_id);

            //get combination data
            $combination_data = get_post_meta(get_the_ID(), '_aam_application_combination', true);
            $combination = get_post($combination_data['combination']);

            //deadline
            $deadlines = get_post_meta($combination->ID, '_aam_combination_deadlines', true);
            $deadline = $deadlines[$combination_data['round'] - 1];

            //school
            $school_id = $combination_data['school'];
            $school = get_post($school_id);

            //programs
            $program = $combination_data['program'];

            //intake
            $intake_id = $combination_data['intake'];
            $intake = get_term_by('id', $intake_id, 'intake');

            $document_items = array();

            if ($this->application_id && $this->step_id && $this->document_id) {

                $document_items = get_posts(array(
                    'post_type' => 'document',
                    'posts_per_page' => -1,
                    'meta_query' => array(
                        array(
                            'key' => '_aam_document_application_id',
                            'value' => $this->application_id,
                            'compare' => '='
                        ),
                        array(
                            'key' => '_aam_document_step_id',
                            'value' => $this->step_id,
                            'compare' => '='
                        ),
                        array(
                            'key' => '_aam_document_document_id',
                            'value' => $this->document_id,
                            'compare' => '='
                        )
                    )
                ));
            }

            $this->set_steps();

            $this->get_completion();

            ?>
            <div class="wrap">

                <?php if ($success) : ?>
                    <div class="notice notice-success"><p><?php echo $success; ?></p></div>
                <?php endif; ?>

                <?php if (!$this->step_id || !$this->document_id) : ?>
                    <h2><?php the_title(); ?> <a href="<?php echo admin_url('post-new.php?post_type=application'); ?>"
                                                 class="add-new-h2"><?php _e('Add Application'); ?></a></h2>

                    <div id="poststuff">
                        <div id="post-body" class="metabox-holder columns-2">

                            <div id="postbox-container-1" class="postbox-container">
                                <div id="side-sortables" class="meta-box-sortables ui-sortable">

                                    <div id="application_info_data" class="postbox ">
                                        <h3 class="hndle"><span><?php _e('Application data'); ?></span></h3>

                                        <div class="inside">
                                            <p>
                                                <strong>Client: </strong><?php echo $client->data->display_name; ?><br>
                                                <strong>Consultant: </strong><?php the_author(); ?><br>
                                                <strong>School: </strong><?php echo $school->post_title; ?><br>
                                                <strong>Program: </strong> <?php echo $program; ?><br>
                                                <strong>Intake: </strong> <?php echo $intake->name; ?><br>
                                                <strong>Round: </strong> <?php echo $combination_data['round']; ?><br>
                                                <strong>Created: </strong> <?php echo get_the_date(get_option('date_format'), get_the_ID()); ?>
                                                <br>
                                                <strong>Deadline: </strong> <?php echo date(get_option('date_format'), strtotime($deadline['date'])); ?><br>
                                            </p>

                                            <strong><?php _e('Completion'); ?>:</strong>

                                            <div class="progress-bar" data-completion="<?php echo $this->complete; ?>">
                                                <span></span></div>
                                        </div>
                                    </div>

                                    <div id="application_delegation_data" class="postbox ">
                                        <h3 class="hndle"><span><?php _e('Delegation'); ?></span></h3>

                                        <div class="inside">
                                            <form action="" method="POST">
                                                <input type="hidden" name="application_id" value="<?php the_ID(); ?>"/>
                                                <input type="hidden" name="action" value="delegate"/>

                                                <?php echo $this->get_consultants_dropdown(get_the_author_meta('ID')); ?>
                                                <hr>
                                                <input name="save" type="submit"
                                                       class="button button-primary button-large widefat"
                                                       value="Delegate">
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div id="postbox-container-2" class="postbox-container">
                                <div class="meta-box-sortables">
                                    <div id="aam-application-steps" class="postbox ">
                                        <h3 class="hndle"><span><?php _e('Documents'); ?></span></h3>

                                        <div class="inside">

                                            <div class="panel-wrap">

                                                <div id="aam_application_steps" class="panel">

                                                    <?php $i = 1; ?>
                                                    <?php foreach ($this->steps as $sk => $step) : ?>
                                                        <table class="widefat" style="margin-bottom:20px;">
                                                            <thead>
                                                            <tr>
                                                                <th style="width:20px; text-align: right;"><?php _e('#'); ?></th>
                                                                <th style="width:150px;"><?php _e('Document type'); ?></th>
                                                                <th><?php _e('Prompt'); ?></th>
                                                                <th style="width:200px;"><?php _e('Completion'); ?></th>
                                                                <th style="width:80px;"><?php _e('Required'); ?></th>
                                                                <?php if (!$step['required']): ?>
                                                                    <th style="width:50px;"><?php _e('Option'); ?></th>
                                                                <?php endif; ?>
                                                                <th style="width:50px;text-align:center;">Steps</th>
                                                            </tr>
                                                            </thead>
                                                            <tbody>
                                                            <tr>
                                                                <td><?php echo $i; ?></td>
                                                                <td><?php echo $step['document']; ?></td>
                                                                <td><?php echo $step['prompt']; ?></td>
                                                                <td>
                                                                    <div class="progress-bar"
                                                                         data-completion="<?php echo $this->get_document_completion($sk); ?>">
                                                                        <span></span></div>
                                                                </td>
                                                                <td><?php echo $step['required'] ? 'Yes' : 'No'; ?></td>
                                                                <?php if (!$step['required']): ?>
                                                                    <td>
                                                                        <a href="<?php echo admin_url('admin.php?page=aam&application_id=' . $this->application_id . '&action=change_activity&step_id=' . $step['id']); ?>"><?php echo $step['active'] == 1 ? 'Disable' : 'Enable'; ?></a>
                                                                    </td>
                                                                <?php endif; ?>
                                                                <td class="aam-show-steps"><a href="#"
                                                                                              class="aam-caret closed"
                                                                                              data-open="aam-step-container-<?php echo get_the_ID(); ?>-<?php echo $sk; ?>"><img
                                                                            src="<?php echo plugin_dir_url(__FILE__); ?>img/arrow.png"/></a>
                                                                </td>
                                                            </tr>
                                                            </tbody>
                                                        </table>

                                                        <div class="aam-steps-container"
                                                             id="aam-step-container-<?php echo get_the_ID(); ?>-<?php echo $sk; ?>">
                                                            <table class="widefat" style="margin-bottom:20px;">
                                                                <thead>
                                                                <tr>
                                                                    <th style="width:20px; text-align: right;"><?php _e('#'); ?></th>
                                                                    <th style="width:150px;"><?php _e('Step'); ?></th>
                                                                    <th><?php _e('Deadline'); ?></th>
                                                                    <th><?php _e('Last uploader'); ?></th>
                                                                    <th style="width:200px;"><?php _e('Completion'); ?></th>
                                                                </tr>
                                                                </thead>
                                                                <tbody>
                                                                <?php $k = 1; ?>
                                                                <?php foreach ($step['items'] as $item) : ?>
                                                                    <?php $last_document = $this->get_last_document($item->document_id, $item->step_id); ?>
                                                                    <?php $last_document_author = $last_document ? get_user_by('id', $last_document->post_author) : null; ?>
                                                                    <?php $deadline = new Carbon($item->deadline); ?>
                                                                    <tr>
                                                                        <td><?php echo $k; ?></td>
                                                                        <td>
                                                                            <?php if (!$item->parent_id || $item->parent_completion == 100) { ?>
                                                                                <a href="<?php echo admin_url() . '?page=aam&application_id=' . $this->application_id . '&document_id=' . $item->document_id . '&step_id=' . $item->step_id; ?>"><?php echo $item->name; ?></a>
                                                                            <?php } else { ?>
                                                                                <?php echo $item->name; ?>
                                                                            <?php } ?>
                                                                        </td>
                                                                        <td><?php echo $deadline->format(get_option('date_format')); ?></td>
                                                                        <td><?php echo $last_document ? $last_document_author->display_name : '-'; ?></td>
                                                                        <td>
                                                                            <div class="progress-bar"
                                                                                 data-completion="<?php echo $item->completion; ?>">
                                                                                <span></span></div>
                                                                        </td>
                                                                    </tr>
                                                                    <?php $k++; ?>
                                                                <?php endforeach; ?>
                                                                </tbody>
                                                            </table>
                                                        </div>

                                                        <?php $i++; ?>
                                                    <?php endforeach; ?>

                                                    <div class="clear"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- /post-body -->
                        <br class="clear">
                    </div>
                    <!-- /poststuff -->
                <?php else : ?>
                    <h2><?php the_title(); ?> - <?php _e('Document step'); ?>
                        <a href="<?php echo admin_url('post-new.php?post_type=document&application_id=' . $this->application_id .
                            '&step_id=' . $this->step_id .
                            '&document_id=' . $this->document_id); ?>"
                           class="add-new-h2"><?php _e('Add New Document'); ?></a>
                        <a href="<?php echo admin_url('admin.php?page=aam&application_id=' . $this->application_id); ?>"
                           class="add-new-h2"><?php _e('Back to application'); ?></a></h2>

                    <div id="poststuff">
                        <div id="post-body" class="metabox-holder columns-2">

                            <div id="postbox-container-1" class="postbox-container">
                                <div id="side-sortables" class="meta-box-sortables ui-sortable">

                                    <div id="application_info_data" class="postbox ">
                                        <h3 class="hndle"><span><?php _e('Application data'); ?></span></h3>

                                        <div class="inside">
                                            <p>
                                                <strong>Client: </strong><?php echo $client->data->display_name; ?><br>
                                                <strong>School: </strong><?php echo $school->post_title; ?><br>
                                                <strong>Program: </strong> <?php echo $program; ?><br>
                                                <strong>Intake: </strong> <?php echo $intake->name; ?><br>
                                                <strong>Round: </strong> <?php echo $combination_data['round']; ?><br>
                                                <strong>Created: </strong> <?php echo get_the_date(get_option('date_format'), get_the_ID()); ?>
                                                <br>
                                                <strong>Deadline: </strong> <?php echo date(get_option('date_format'), strtotime($deadline['date'])); ?><br>
                                            </p>

                                            <strong><?php _e('Completion'); ?>:</strong>

                                            <div class="progress-bar" data-completion="<?php echo $this->complete; ?>">
                                                <span></span></div>
                                        </div>
                                    </div>

                                    <div id="document_step_status" class="postbox ">
                                        <h3 class="hndle"><span><?php _e('Status'); ?></span></h3>

                                        <div class="inside">
                                            <form method="POST" action="">
                                                <input type="hidden" name="application_id"
                                                       value="<?php echo $this->application_id; ?>"/>
                                                <input type="hidden" name="step_id"
                                                       value="<?php echo $this->step_id; ?>"/>
                                                <input type="hidden" name="document_id"
                                                       value="<?php echo $this->document_id; ?>"/>
                                                <input type="hidden" name="action" value="change_status"/>

                                                <?php echo $this->get_statuses_dropdown(); ?>
                                                <hr>
                                                <input name="save" type="submit"
                                                       class="button button-primary button-large widefat"
                                                       value="Change status">
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div id="postbox-container-2" class="postbox-container">
                                <div class="meta-box-sortables">
                                    <div id="aam-application-step" class="postbox ">
                                        <h3 class="hndle"><span><?php _e('Documents'); ?></span></h3>

                                        <div class="inside">

                                            <div class="panel-wrap">

                                                <div id="aam_application_step" class="panel">

                                                    <table class="widefat">
                                                        <thead>
                                                        <tr>
                                                            <th>Document</th>
                                                            <th>Type</th>
                                                            <th>Uploaded</th>
                                                            <th>Author</th>
                                                            <th>Final?</th>
                                                        </tr>
                                                        </thead>
                                                        <tbody>
                                                        <?php foreach ($document_items as $item) : ?>
                                                            <?php $attachment = get_attachment_data_by_url(get_post_meta($item->ID, '_aam_document_document', true)); ?>
                                                            <?php $final = get_post_meta($item->ID, '_aam_document_final', true); ?>
                                                            <?php $author = get_user_by('id', $attachment->post_author); ?>
                                                            <tr>
                                                                <td>
                                                                    <a href="<?php echo admin_url() . 'post.php?post=' . $item->ID . '&action=edit&application_id=' . $this->application_id . '&document_id=' . $_GET['document_id'] . '&step_id=' . $_GET['step_id']; ?>"><?php echo get_post_meta($item->ID, '_aam_document_document', true); ?></a>
                                                                </td>
                                                                <td><?php echo $attachment->post_mime_type; ?></td>
                                                                <td><?php echo date(get_option('date_format'), strtotime($attachment->post_date)); ?></td>
                                                                <td><?php echo $author->display_name; ?></td>
                                                                <td><?php echo $final ? 'yes' : 'no' ?></td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                        </tbody>
                                                    </table>

                                                    <div class="clear"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php

        endwhile;
    }

    /**
     * Redirect if no application selected
     */
    public function select_application()
    {
        ?>
        <script>
            window.location = "<?php echo admin_url('edit.php?post_type=application&display_notice_error=true'); ?>";
        </script>
    <?php
    }

    //

    /**
     * @return string
     */
    public function get_statuses_dropdown()
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'aam_applications';

        $selected = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT status FROM $table_name WHERE application_id = %d AND document_id = %s AND step_id = %s",
                $this->application_id,
                $this->document_id,
                $this->step_id
            )
        );

        $statuses = get_terms(array('status'), array(
            'hide_empty' => false
        ));

        $dd = '<select name="status" class="widefat">';
        $dd .= '<option value="">' . __('No status') . '</option>';

        foreach ($statuses as $status) {
            $sel = $selected == $status->term_id ? 'selected' : '';
            $dd .= '<option value="' . $status->term_id . '" ' . $sel . '>' . $status->name . '</option>';
        }

        $dd .= '</select>';

        return $dd;
    }

    public function set_steps()
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'aam_applications';

        $steps = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table_name AS apps LEFT JOIN wp_posts AS posts ON posts.ID = apps.document_type WHERE apps.application_id = %d AND apps.active = 1 ORDER BY apps.sort",
                $this->application_id
            )
        );

        $sorted = array();

        if ($steps) {
            $current = null;

            foreach ($steps as $step) {
                if (!isset($sorted[$step->document_id])) {
                    $sorted[$step->document_id] = array(
                        'id' => $step->id,
                        'document' => $step->post_title,
                        'prompt' => $step->prompt,
                        'type' => $step->document_type,
                        'weight' => $step->document_weight,
                        'required' => $step->required,
                        'active' => $step->active,
                        'items' => array()
                    );
                }

                $step->completion = $this->get_step_completion($step->status);

                //get parent step completion
                if ($step->parent_id) {
                    $step->parent_completion = $this->get_parent_completion($steps, $step->document_id, $step->parent_id);
                }

                $sorted[$step->document_id]['items'][] = $step;
            }
        }

        $this->steps = $sorted;
    }

    /**
     * Get parent step completion
     *
     * @param array $steps
     * @param string $document_id
     * @param string $parent_step_id
     * @return int
     */
    public function get_parent_completion($steps = array(), $document_id = '', $parent_step_id = '')
    {
        foreach ($steps as $step) {
            if ($step->document_id == $document_id && $step->step_id == $parent_step_id) {
                return $this->get_step_completion($step->status);
            }
        }
    }

    /**
     * Calculate completion of whole application
     */
    public function get_completion()
    {
        $total = 0;
        $document_total = 0;

        foreach ($this->steps as $step) {
            foreach ($step['items'] as $item) {
                $done = $item->step_weight * $item->completion / 100;

                $document_total += $done;
            }

            $total += $step['weight'] * $document_total / 100;

            $document_total = 0;
        }

        $this->complete = $total;
    }

    /**
     * @param string $document_id
     *
     * @return float|int
     */
    public function get_document_completion($document_id = '')
    {
        $total = 0;

        foreach ($this->steps[$document_id]['items'] as $item) {
            $done = $item->step_weight * $item->completion / 100;

            $total += $done;
        }

        return $total;
    }

    /**
     * @param int $status
     *
     * @return int
     */
    public function get_step_completion($status = 0)
    {
        if ($status) {

            $status = get_option('status_' . $status);

            return $status['percent'];
        }

        return 0;
    }

    /**
     * Generate consultants drop down menu
     * @param $owner
     *
     * @return string
     */
    private function get_consultants_dropdown($owner)
    {
        global $wpdb;

        $consultants = get_users(array(
            'role' => 'Consultant',
            'exclude' => array($owner)
        ));

        //get delegations
        $table = $wpdb->prefix . 'aam_delegations';

        $delegation = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $table WHERE application_id = %d",
                array(
                    $this->application_id
                )
            )
        );

        $dd = '<select name="aam_delegation" class="widefat">';
        $dd .= '<option value="">' . __('Not delegated') . '</option>';

        $selected = '';

        foreach ($consultants as $consultant) {
            if ($delegation) {
                $selected = $consultant->ID == $delegation->consultant_id ? ' selected ' : '';
            }

            $dd .= '<option value="' . $consultant->ID . '" ' . $selected . '>' . $consultant->display_name . '</option>';
        }

        $dd .= '</select>';

        return $dd;
    }

    public function get_last_document($document_id, $step_id)
    {
        $query = new WP_Query(array(
            'meta_query' => array(
                array(
                    'key' => '_aam_document_document_id',
                    'compare' => '=',
                    'value' => $document_id
                ),
                array(
                    'key' => '_aam_document_step_id',
                    'compare' => '=',
                    'value' => $step_id
                )
            ),
            'orderby' => 'date',
            'order' => 'DESC',
            'posts_per_page' => 1,
            'post_status' => 'publish',
            'post_type' => 'document'
        ));

        $posts = $query->get_posts();

        return isset($posts[0]) ? $posts[0] : null;
    }

    /**
     * Save delegation
     */
    public function save_delegation()
    {
        global $wpdb;

        $table = $wpdb->prefix . 'aam_delegations';

        if (isset($_POST['application_id'])) {
            $delegation = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT * FROM $table WHERE application_id = %d",
                    array(
                        $_POST['application_id']
                    )
                )
            );

            if (isset($_POST['aam_delegation']) && $_POST['aam_delegation'] != '') {
                if ($delegation) {
                    $wpdb->update(
                        $table,
                        array(
                            'consultant_id' => $_POST['aam_delegation']
                        ),
                        array(
                            'application_id' => $_POST['application_id']
                        ),
                        array(
                            '%d'
                        ),
                        array(
                            '%d'
                        )
                    );
                } else {
                    $wpdb->insert(
                        $table,
                        array(
                            'consultant_id' => $_POST['aam_delegation'],
                            'application_id' => $_POST['application_id']
                        ),
                        array(
                            '%d',
                            '%d'
                        )
                    );
                }
            } else {
                if ($delegation) {
                    $wpdb->delete(
                        $table,
                        array(
                            'application_id' => $_POST['application_id']
                        )
                    );
                }
            }
        }

        return 'Delegation successfully changed';

        /*wp_redirect(admin_url('admin.php?page=aam&application_id=' . $_POST['application_id']));
        exit;*/
    }

    public function save_status()
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'aam_applications';

        $status = $_POST['status'];
        $application_id = $_POST['application_id'];
        $step_id = $_POST['step_id'];
        $document_id = $_POST['document_id'];
        $done = 0;

        $statuses = get_option('statuses_' . $application_id);

        if (!$statuses) {
            $statuses = array();
            $statuses[$document_id] = array();
            $statuses[$document_id][$step_id] = $status;
        } else {
            if (!isset($statuses[$document_id])) {
                $statuses[$document_id] = array();
                $statuses[$document_id][$step_id] = $status;
            } else {
                $statuses[$document_id][$step_id] = $status;
            }
        }

        //update step completion (cache in db)
        if ($status) {
            $status_data = get_option('status_' . $status);

            $done = $status_data['percent'];
        }

        $wpdb->update(
            $table_name,
            array(
                'status' => $status,
                'done' => $done
            ),
            array(
                'application_id' => $application_id,
                'document_id' => $document_id,
                'step_id' => $step_id
            )
        );

        //also update complete application completion
        $this->application_id = $application_id;

        $this->set_steps();

        $this->get_completion();

        update_post_meta($application_id, '_aam_total_completion', $this->complete);
        update_option('statuses_' . $application_id, $statuses);

        return 'Status successfully changed';

        /*wp_redirect(admin_url('admin.php?page=aam&application_id=' . $application_id . '&document_id=' . $document_id . '&step_id=' . $step_id));
        exit;*/
    }

    public function change_activity()
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'aam_applications';

        $row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $table_name WHERE id = %d",
                $_GET['step_id']
            ),
            ARRAY_A
        );

        $new = abs($row['active'] - 1);

        $update = array(
            'active' => $new
        );

        if ($new == 0) {
            $update['deadline'] = NULL;
        }

        if ($wpdb->update(
            $table_name,
            $update,
            array(
                'application_id' => $row['application_id'],
                'document_id' => $row['document_id']
            )
        )
        ) {
            $helper = new ApplicationHelper($row['application_id']);

            $helper->calc_deadlines(true);

            return 'Status changed';

            /*wp_redirect(admin_url('admin.php?page=aam&application_id=' . $_GET['application_id']));
            exit;*/
        }
    }
}

function init_dashboard()
{
    $dash = new Aam_App_Dashboard();
}