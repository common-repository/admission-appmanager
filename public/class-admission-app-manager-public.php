<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://www.mariotadic.com
 * @since      1.0.0
 *
 * @package    Admission_App_Manager
 * @subpackage Admission_App_Manager/public
 */
use Carbon\Carbon;

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Admission_App_Manager
 * @subpackage Admission_App_Manager/public
 * @author     Mario Tadic <tadic.mario@gmail.com>
 */
class Admission_App_Manager_Public
{

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $admission_app_manager The ID of this plugin.
     */
    private $admission_app_manager;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string $admission_app_manager The name of the plugin.
     * @param      string $version The version of this plugin.
     */
    public function __construct($admission_app_manager, $version)
    {

        $this->admission_app_manager = $admission_app_manager;
        $this->version = $version;

        add_shortcode('applications_list', array(&$this, 'client_applications'));
        add_shortcode('application', array(&$this, 'client_application'));
        add_shortcode('aam_edit_profile', array(&$this, 'aam_edit_profile'));

    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Admission_App_Manager_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Admission_App_Manager_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_style($this->admission_app_manager, plugin_dir_url(__FILE__) . 'css/admission-app-manager-public.css', array(), $this->version, 'all');

    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts()
    {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Admission_App_Manager_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Admission_App_Manager_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_script($this->admission_app_manager, plugin_dir_url(__FILE__) . 'js/admission-app-manager-public.js', array('jquery'), $this->version, false);

    }

    public function client_applications()
    {
        $applications = new WP_Query(array(
            'post_type' => 'application',
            'meta_key' => '_aam_application_client',
            'meta_value' => get_current_user_id()
        ));

        $settings = get_option('aam_settings');

        if (!isset($settings['applications_page'])) {
            _e('No single application page selected', 'admission-app-manager');
            exit;
        }

        if ($applications->have_posts()) { ?>

            <table class="aam-applications">
                <thead>
                <tr>
                    <th>School</th>
                    <th>Program</th>
                    <th>Intake</th>
                    <th>Round</th>
                    <th>Completed</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>

                <?php

                while ($applications->have_posts()) {
                    $applications->the_post();

                    $combination_data = get_post_meta(get_the_ID(), '_aam_application_combination', true);

                    $school = get_post($combination_data['school']);

                    $intake = get_term($combination_data['intake'], 'intake');
                    ?>

                    <tr>
                        <td><?php echo $school->post_title; ?></td>
                        <td><?php echo $combination_data['program']; ?></td>
                        <td><?php echo $intake->name; ?></td>
                        <td><?php echo $combination_data['round']; ?></td>
                        <td>
                            <div class="aam-progress-bar"
                                 data-completion="<?php echo get_post_meta(get_the_ID(), '_aam_total_completion', true); ?>">
                                <span></span></div>
                        </td>
                        <td>
                            <a href="<?php echo add_query_arg('application_id', get_the_ID(), get_permalink($settings['single_application_page'])) ?>"><?php _e('View'); ?></a>
                        </td>
                    </tr>

                <?php
                }

                ?>
                </tbody>
            </table>

        <?php
        } else {
            _e('No active application currently');
        }
    }

    public function client_application()
    {
        $application_id = isset($_REQUEST['application_id']) ? esc_attr($_REQUEST['application_id']) : null;
        $document_id = isset($_REQUEST['document_id']) ? esc_attr($_REQUEST['document_id']) : null;
        $step_id = isset($_REQUEST['step_id']) ? esc_attr($_REQUEST['step_id']) : null;

        $client = get_post_meta($application_id, '_aam_application_client', true);

        $settings = get_option('aam_settings');

        $notification = false;
        $post_errors = array();

        if (($client == get_current_user_id() || current_user_can('manage_options')) && $application_id) {

            if (isset($_POST['action']) && $_POST['action'] == 'upload_document') {
                $title = $_POST['title'];
                $file = $_FILES['file'];

                if ($title == '') {
                    $post_errors['title'] = 'Please enter title';
                }

                if (empty($file) || $file['size'] == 0 || $file['error'] > 0) {
                    $post_errors['file'] = 'Please select document for upload';
                }

                if (!isset($application_id) || !isset($document_id) || !isset($step_id)) {
                    $post_errors['error'] = 'Something is wrong, please try again';
                }

                if (empty($post_errors)) {
                    require_once(ABSPATH . 'wp-admin/includes/image.php');
                    require_once(ABSPATH . 'wp-admin/includes/file.php');
                    require_once(ABSPATH . 'wp-admin/includes/media.php');

                    $new_document_data = array(
                        'post_author' => get_current_user_id(),
                        'post_title' => wp_strip_all_tags($title),
                        'post_type' => 'document',
                        'post_content' => '',
                        'post_status' => 'publish'
                    );

                    $new_document_id = wp_insert_post($new_document_data);

                    $attach_id = media_handle_upload('file', $new_document_id);

                    update_post_meta($new_document_id, '_aam_document_application_id', $application_id);
                    update_post_meta($new_document_id, '_aam_document_step_id', $step_id);
                    update_post_meta($new_document_id, '_aam_document_document_id', $document_id);
                    update_post_meta($new_document_id, '_aam_document_document', wp_get_attachment_url($attach_id));

                    send_notification($new_document_id);

                    $notification = __('New document is added!');
                }
            }

            $steps = $this->get_steps($application_id);

            $combination_data = get_post_meta($application_id, '_aam_application_combination', true);

            $school = get_post($combination_data['school']);

            $application = get_post($application_id);

            $consultant = get_userdata($application->post_author);

            $intake = get_term($combination_data['intake'], 'intake');

            $completion = $this->get_completion($steps);

            if ($steps && !$document_id && !$step_id) {
                ?>
                <p>
                    <strong>Consultant:</strong> <?php echo $consultant->display_name; ?><br>
                    <strong>School:</strong> <?php echo $school->post_name; ?><br>
                    <strong>Program:</strong> <?php echo $combination_data['program']; ?><br>
                    <strong>Intake:</strong> <?php echo $intake->name; ?><br>
                    <strong>Round:</strong> <?php echo $combination_data['round']; ?><br>
                    <strong>Completed:</strong> <?php echo $completion; ?>%
                </p>

                <?php $i = 1; ?>
                <?php foreach ($steps as $sk => $step) : ?>
                    <table class="aam-application" style="margin-bottom:20px;">
                        <thead>
                        <tr>
                            <th style="text-align: right; width:30px;"><?php _e('#'); ?></th>
                            <th><?php _e('Document type'); ?></th>
                            <th><?php _e('Prompt'); ?></th>
                            <th><?php _e('Completion'); ?></th>
                            <th><?php _e('Required'); ?></th>
                            <?php if (!$step['required']): ?>
                                <th><?php _e('Option'); ?></th>
                            <?php endif; ?>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td style="text-align: right; width:30px;"><?php echo $i; ?></td>
                            <td><?php echo $step['document']; ?></td>
                            <td><?php echo $step['prompt']; ?></td>
                            <td><?php echo $this->get_document_completion($steps, $sk); ?>%</td>
                            <td><?php echo $step['required'] ? 'Yes' : 'No'; ?></td>
                            <?php if (!$step['required']): ?>
                                <td>
                                    <a href="<?php echo admin_url('admin.php?page=aam&application_id=' . $application_id . '&action=change_activity&step_id=' . $step['id']); ?>"><?php echo $step['active'] == 1 ? 'Disable' : 'Enable'; ?></a>
                                </td>
                            <?php endif; ?>
                        </tr>
                        </tbody>
                    </table>

                    <div style="padding-left:20px;">
                        <table class="widefat" style="margin-bottom:20px;">
                            <thead>
                            <tr>
                                <th style="width:30px; text-align: right;"><?php _e('#'); ?></th>
                                <th><?php _e('Step'); ?></th>
                                <th><?php _e('Deadline'); ?></th>
                                <th><?php _e('Last uploader'); ?></th>
                                <th><?php _e('Completion'); ?></th>
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
                                        <a href="<?php echo add_query_arg(array(
                                            'application_id' => $application_id,
                                            'document_id' => $item->document_id,
                                            'step_id' => $item->step_id
                                        ), get_permalink($settings['single_application_page'])); ?>"><?php echo $item->name; ?></a>
                                    </td>
                                    <td><?php echo $deadline->format(get_option('date_format')); ?></td>
                                    <td><?php echo $last_document ? $last_document_author->display_name : '-'; ?></td>
                                    <td><?php echo $item->completion; ?>%</td>
                                </tr>
                                <?php $k++; ?>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php $i++; ?>
                <?php endforeach; ?>
            <?php
            } else {
                $documents = $this->get_documents($application_id, $document_id, $step_id);

                echo $notification ? '<p class="notification">' . $notification . '</p>' : '';

                //var_dump($steps[$document_id]['items']);

                $step = $steps[$document_id]['items'][$this->_find_step($step_id, $steps[$document_id]['items'])];

                ?>
                <p>
                    <strong>Consultant:</strong> <?php echo $consultant->display_name; ?><br>
                    <strong>School:</strong> <?php echo $school->post_name; ?><br>
                    <strong>Program:</strong> <?php echo $combination_data['program']; ?><br>
                    <strong>Intake:</strong> <?php echo $intake->name; ?><br>
                    <strong>Round:</strong> <?php echo $combination_data['round']; ?><br>
                    <strong>Completed:</strong> <?php echo $completion; ?>%
                </p>

                <p>
                    <strong>Step name:</strong> <?php echo $step->name; ?><br>
                    <strong>Prompt:</strong> <?php echo $step->prompt; ?><br>
                    <strong>Deadline:</strong> <?php echo date(get_option('date_format'), strtotime($step->deadline)); ?><br>
                    <strong>Completed:</strong> <?php echo $step->done . '%'; ?>
                </p>

                <h2>Step documents</h2>
                <?php

                if ($documents) {
                    ?>
                    <table class="aam-documents">
                        <thead>
                        <tr>
                            <th>Document name</th>
                            <th>Date</th>
                            <th>Author</th>
                            <th>Final</th>
                        </tr>
                        </thead>

                        <tbody>
                        <?php foreach ($documents as $document) : ?>
                            <?php $attachment = get_attachment_data_by_url(get_post_meta($document->ID, '_aam_document_document', true)); ?>
                            <?php $final = get_post_meta($document->ID, '_aam_document_final', true); ?>
                            <?php $author = get_user_by('id', $attachment->post_author); ?>
                            <tr>
                                <td>
                                    <a href="<?php echo get_post_meta($document->ID, '_aam_document_document', true); ?>"><?php echo $document->post_title; ?></a>
                                </td>
                                <td><?php echo date(get_option('date_format'), strtotime($attachment->post_date)); ?></td>
                                <td><?php echo $author->display_name; ?></td>
                                <td><?php echo $final ? 'yes' : 'no' ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php
                } else {
                    _e('No documents uploaded for this step');
                }

                ?>
                <h2>New document</h2>

                <?php
                if (!empty($post_errors)) { ?>
                    <ul class="aam-validation">
                        <?php foreach ($post_errors as $error) { ?>
                            <li><?php echo $error; ?></li>
                        <?php } ?>
                    </ul>
                <?php } ?>

                <form method="post" enctype="multipart/form-data" action="">
                    <input type="hidden" name="action" value="upload_document"/>
                    <input type="hidden" name="application_id" value="<?php echo $application_id; ?>"/>
                    <input type="hidden" name="document_id" value="<?php echo $document_id; ?>"/>
                    <input type="hidden" name="step_id" value="<?php echo $step_id; ?>"/>
                    <?php wp_nonce_field('new-document'); ?>

                    <div class="form-group">
                        <label for="document-name">Document name</label>
                        <input type="text" id="document-name" class="aam-input" name="title"/>
                    </div>

                    <div class="form-group">
                        <label for="file">Document</label>
                        <input type="file" name="file" id="file" class="aam-upload"/>
                    </div>

                    <button type="submit">Upload</button>
                </form>
            <?php
            }
        } else {
            _e('ERROR! You can\'t access selected application!');
        }
    }

    /**
     * Find step in steps array by step key
     * @param $step_id
     * @param $steps
     * @return bool|int|string
     */
    private function _find_step($step_id, $steps)
    {
        foreach ($steps as $k => $v) {
            if ($v->step_id == $step_id) {
                return $k;
            }
        }

        return false;
    }

    public function get_steps($application_id = 0)
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'aam_applications';

        $steps = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table_name AS apps LEFT JOIN wp_posts AS posts ON posts.ID = apps.document_type WHERE apps.application_id = %d AND apps.active = 1 ORDER BY apps.sort",
                $application_id
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

                $sorted[$step->document_id]['items'][] = $step;
            }
        }

        return $sorted;
    }

    public function get_step_completion($status = 0)
    {
        if ($status) {

            $status = get_option('status_' . $status);

            return $status['percent'];
        }

        return 0;
    }

    public function get_completion($steps = array())
    {
        $total = 0;
        $document_total = 0;

        foreach ($steps as $step) {
            foreach ($step['items'] as $item) {
                $done = $item->step_weight * $item->completion / 100;

                $document_total += $done;
            }

            $total += $step['weight'] * $document_total / 100;

            $document_total = 0;
        }

        return $total;
    }

    public function get_document_completion($steps = array(), $document_id = '')
    {
        $total = 0;

        foreach ($steps[$document_id]['items'] as $item) {
            $done = $item->step_weight * $item->completion / 100;

            $total += $done;
        }

        return $total;
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

    public function get_documents($application_id, $document_id, $step_id)
    {
        $document_items = get_posts(array(
            'post_type' => 'document',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => '_aam_document_application_id',
                    'value' => $application_id,
                    'compare' => '='
                ),
                array(
                    'key' => '_aam_document_step_id',
                    'value' => $step_id,
                    'compare' => '='
                ),
                array(
                    'key' => '_aam_document_document_id',
                    'value' => $document_id,
                    'compare' => '='
                )
            )
        ));

        return $document_items;
    }

    /**
     * Shortcode for frontend client user profile edit
     */
    public function aam_edit_profile()
    {
        global $current_user;

        $bo_days = get_the_author_meta('aam_bo_days', $current_user->ID);

        if (!$bo_days || !is_array($bo_days)) {
            $bo_days = array(array('from' => '', 'to' => ''));
        }

        $error = array();
        /* If profile was saved, update profile. */
        if ('POST' == $_SERVER['REQUEST_METHOD'] && !empty($_POST['action']) && $_POST['action'] == 'update-user') {

            /* Update user information. */
            if (!empty($_POST['email'])) {
                if (!is_email(esc_attr($_POST['email'])))
                    $error[] = __('The Email you entered is not valid.  please try again.', 'profile');
                elseif (email_exists(esc_attr($_POST['email'])) != $current_user->ID)
                    $error[] = __('This email is already used by another user.  try a different one.', 'profile');
                else {
                    wp_update_user(array('ID' => $current_user->ID, 'user_email' => esc_attr($_POST['email'])));
                }
            } else {
                $error[] = __('The Email you entered is not valid.  please try again.', 'profile');
            }

            if (!empty($_POST['first-name']))
                update_user_meta($current_user->ID, 'first_name', esc_attr($_POST['first-name']));
            if (!empty($_POST['last-name']))
                update_user_meta($current_user->ID, 'last_name', esc_attr($_POST['last-name']));

            if (!empty($_POST['aam_bo_days'])) {
                $bo_days_data = array();

                //var_dump($_POST['aam_bo_days']);

                foreach ($_POST['aam_bo_days'] as $day) {
                    if ($this->_validateDate($day['from']) && $this->_validateDate($day['to'])) {
                        $bo_days_data[] = array(
                            'from' => $day['from'],
                            'to' => $day['to']
                        );
                    }
                }

                //var_dump($bo_days_data);

                if (update_user_meta($current_user->ID, 'aam_bo_days', $bo_days_data)) {
                    $bo_days = $bo_days_data;
                }
            }


            if (count($error) == 0) {
                //action hook for plugins and extra fields saving
                do_action('edit_user_profile_update', $current_user->ID);
            }
        } ?>

        <?php if (!is_user_logged_in()) : ?>
        <p class="warning">
            <?php _e('You must be logged in to edit your profile.', 'profile'); ?>
        </p><!-- .warning -->
    <?php else : ?>
        <?php if (count($error) > 0) echo '<p class="error">' . implode("<br />", $error) . '</p>'; ?>
        <form method="post" id="adduser" action="<?php the_permalink(); ?>">
            <p class="form-username">
                <label for="first-name"><?php _e('First Name', 'profile'); ?></label>
                <input class="text-input" name="first-name" type="text" id="first-name"
                       value="<?php the_author_meta('first_name', $current_user->ID); ?>"/>
            </p><!-- .form-username -->
            <p class="form-username">
                <label for="last-name"><?php _e('Last Name', 'profile'); ?></label>
                <input class="text-input" name="last-name" type="text" id="last-name"
                       value="<?php the_author_meta('last_name', $current_user->ID); ?>"/>
            </p><!-- .form-username -->
            <p class="form-email">
                <label for="email"><?php _e('E-mail *', 'profile'); ?></label>
                <input class="text-input" name="email" type="text" id="email"
                       value="<?php the_author_meta('user_email', $current_user->ID); ?>"/>
            </p><!-- .form-email -->

            <p class="form-bo-days">
                <label><?php _e('Black-out days', 'aam'); ?></label>
            </p>

            <table class="aam-bo-days">
                <tbody>
                <?php foreach ($bo_days as $k => $day) : ?>
                    <tr>
                        <td>
                            <label><?php _e('From', 'aam'); ?></label>
                            <input class="text-input" name="aam_bo_days[<?php echo $k; ?>][from]" type="text"
                                   value="<?php echo $day['from']; ?>" placeholder="dd/mm/yyyy"/>
                        </td>
                        <td>
                            <label><?php _e('To', 'aam'); ?></label>
                            <input class="text-input" name="aam_bo_days[<?php echo $k; ?>][to]" type="text"
                                   value="<?php echo $day['to']; ?>" placeholder="dd/mm/yyyy"/>
                        </td>
                        <td class="aam-controls">
                            <a href="#" class="aam-remove-row">X</a>&nbsp;
                            <a href="#" class="aam-add-row">+</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

            <script>
                jQuery(document).ready(function ($) {
                    var index = <?php echo count($bo_days); ?>;
                    var table = $('.aam-bo-days');

                    table.on('click', '.aam-remove-row', function (e) {
                        e.preventDefault();

                        var left = table.children().children().length;

                        if (left > 1) {
                            $(this).parent().parent().remove();
                        }
                    });

                    table.on('click', '.aam-add-row', function (e) {
                        e.preventDefault();

                        index++;

                        table.append('<tr>' +
                        '<td> ' +
                        '<label>From</label> ' +
                        '<input type="text" name="aam_bo_days[' + index + '][from]" placeholder="dd/mm/yyyy" class="text-input" value=""/> ' +
                        '</td>' +
                        '<td> ' +
                        '<label>To</label> ' +
                        '<input type="text" name="aam_bo_days[' + index + '][to]" placeholder="dd/mm/yyyy" class="text-input" value=""/> ' +
                        '</td>' +
                        '<td class="aam-controls"> ' +
                        '<a href="#" class="aam-remove-row">X</a>&nbsp; ' +
                        '<a href="#" class="aam-add-row">+</a> ' +
                        '</td> ' +
                        '</tr>');
                    });
                });
            </script>

            <p class="form-submit">
                <input name="updateuser" type="submit" id="updateuser" class="submit button"
                       value="<?php _e('Update', 'profile'); ?>"/>
                <?php wp_nonce_field('update-user') ?>
                <input name="action" type="hidden" id="action" value="update-user"/>
            </p><!-- .form-submit -->
        </form><!-- #adduser -->
    <?php endif;
    }

    private function _validateDate($date, $format = 'd/m/Y')
    {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }
}