<?php

/**
 * Fired during plugin deactivation
 *
 * @link       http://www.mariotadic.com
 * @since      1.0.0
 *
 * @package    Admission_App_Manager
 * @subpackage Admission_App_Manager/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Admission_App_Manager
 * @subpackage Admission_App_Manager/includes
 * @author     Mario Tadic <tadic.mario@gmail.com>
 */
class Admission_App_Manager_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		self::remove_roles();
	}

	private function remove_roles() {
		remove_role( 'client' );
		remove_role( 'consultant' );
	}

}
