<?php

require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

/**
 * Fired during plugin activation
 *
 * @link       http://www.mariotadic.com
 * @since      1.0.0
 *
 * @package    Admission_App_Manager
 * @subpackage Admission_App_Manager/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Admission_App_Manager
 * @subpackage Admission_App_Manager/includes
 * @author     Mario Tadic <tadic.mario@gmail.com>
 */
class Admission_App_Manager_Activator
{

    /**
     * Short Description. (use period)
     *
     * Long Description.
     *
     * @since    1.0.0
     */
    public static function activate()
    {

        self::add_roles();

        //db operations
        self::add_delegations_table();
        self::add_applications_table();
        self::create_pages();

    }

    /**
     * Add custom roles
     *
     * @since 1.0.0
     */
    private function add_roles()
    {
        //Add client custom role
        remove_role('client');
        add_role('client', __('Client'), array('read' => true));

        remove_role('school');
        add_role('school', __('School'), array(
            'read' => true,
            'manage_school' => true
        ));

        //Add consultant custom role
        remove_role('consultant');
        add_role('consultant', __('Consultant'), array(
            'read' => true,
            'manage_clients' => true
        ));

        //add same capability to admin
        $role = get_role('administrator');
        $role->add_cap('manage_clients');
        $role->add_cap('manage_school');
    }

    /**
     * Create wp_aam_delegations table
     *
     * @since 1.0.0
     */
    private function add_delegations_table()
    {
        global $wpdb;
        global $charset_collate;

        $table_name = $wpdb->prefix . 'aam_delegations';
        $posts_name = $wpdb->prefix . 'posts';
        $users_name = $wpdb->prefix . 'users';

        $sql = "CREATE TABLE $table_name IF NOT EXISTS (
  				id int unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  				consultant_id bigint(20) unsigned NOT NULL,
  				application_id bigint(20) unsigned NOT NULL,
  				FOREIGN KEY (consultant_id) REFERENCES $users_name (ID) ON DELETE CASCADE ON UPDATE CASCADE,
  				FOREIGN KEY (application_id) REFERENCES $posts_name (ID) ON DELETE CASCADE ON UPDATE CASCADE
				) $charset_collate;";

        dbDelta($sql);
    }

    private function add_applications_table()
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'aam_applications';
        $posts_table = $wpdb->prefix . 'posts';
        $users_table = $wpdb->prefix . 'users';
        $terms_table = $wpdb->prefix . 'terms';

        $sql = "CREATE TABLE $table_name IF NOT EXISTS (
		  id int(11) NOT NULL AUTO_INCREMENT,
		  name varchar(255) NOT NULL,
		  application_id bigint(20) unsigned NOT NULL,
		  document_id varchar(32) NOT NULL,
		  document_type bigint(20) unsigned NOT NULL,
		  step_id varchar(32) NOT NULL,
		  deadline date DEFAULT NULL,
		  active tinyint(1) NOT NULL DEFAULT '1',
		  last_uploader bigint(20) unsigned DEFAULT NULL,
		  status bigint(20) unsigned DEFAULT NULL,
		  done int(3) unsigned DEFAULT NULL,
		  sort varchar(10) DEFAULT NULL,
		  prompt varchar(255) DEFAULT NULL,
		  required tinyint(1) NOT NULL DEFAULT '0',
		  document_weight int(11) NOT NULL,
		  step_weight int(11) NOT NULL,
		  step_days int(11) NOT NULL,
		  step_max_days int(11) NOT NULL,
		  step_min_days int(11) NOT NULL,
		  owner bigint(20) unsigned DEFAULT NULL,
		  parent_id varchar(32) DEFAULT NULL,
		  PRIMARY KEY (id),
		  KEY document_id (document_id),
		  KEY step_id (step_id),
		  KEY application_id (application_id),
		  KEY document_type (document_type),
		  KEY last_uploader (last_uploader),
		  KEY status (status),
		  KEY owner (owner),
		  FOREIGN KEY (owner) REFERENCES $users_table (ID) ON DELETE CASCADE ON UPDATE CASCADE,
		  FOREIGN KEY (application_id) REFERENCES $posts_table (ID) ON DELETE CASCADE ON UPDATE CASCADE,
		  FOREIGN KEY (document_type) REFERENCES $posts_table (ID) ON DELETE CASCADE ON UPDATE CASCADE,
		  FOREIGN KEY (last_uploader) REFERENCES $users_table (ID) ON DELETE CASCADE ON UPDATE CASCADE,
		  FOREIGN KEY (status) REFERENCES $terms_table (term_id)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

        dbDelta($sql);
    }

    /**
     * Create pages needed for public part of app
     */
    private function create_pages() {
        $settings = get_option('aam_settings', array());

        if (!isset($settings['applications_page'])) {
            $my_applications = array(
                'post_title' => 'My Applications',
                'post_content' => '[applications_list]',
                'post_status' => 'publish',
                'post_type' => 'page'
            );

            $post_id = wp_insert_post($my_applications);

            $settings['applications_page'] = $post_id;
        }

        if (!isset($settings['single_application_page'])) {
            $single_application_page = array(
                'post_title' => 'Application',
                'post_content' => '[application]',
                'post_status' => 'publish',
                'post_type' => 'page'
            );

            $post_id = wp_insert_post($single_application_page);

            $settings['single_application_page'] = $post_id;
        }

        update_option('aam_settings', $settings);
    }

}