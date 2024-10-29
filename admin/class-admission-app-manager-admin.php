<?php

use Carbon\Carbon;

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://www.mariotadic.com
 * @since      1.0.0
 *
 * @package    Admission_App_Manager
 * @subpackage Admission_App_Manager/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Admission_App_Manager
 * @subpackage Admission_App_Manager/admin
 * @author     Mario Tadic <tadic.mario@gmail.com>
 */
class Admission_App_Manager_Admin {

	/**
	 * @since    1.0.0
	 * @access   private
	 * @var      string $admission_app_manager The ID of this plugin.
	 */
	private $admission_app_manager;

	/**
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 *
	 * @param      string $admission_app_manager The name of this plugin.
	 * @param      string $version The version of this plugin.
	 */
	public function __construct( $admission_app_manager, $version ) {

		$this->admission_app_manager = $admission_app_manager;
		$this->version               = $version;

		$this->load_dependencies();

	}

	public function load_dependencies() {

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/helpers/admission-app-manager-application.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-admission-app-manager-app-dashboard.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-admission-app-manager-importer.php';

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->admission_app_manager, plugin_dir_url( __FILE__ ) . 'css/admission-app-manager-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( $this->admission_app_manager, plugin_dir_url( __FILE__ ) . 'js/admission-app-manager-admin.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Menu settings
	 */
	public function aam_add_main_menu_item() {
		add_menu_page( __( 'Admission AppManager', 'admission-app-manager' ), __( 'Admission AppManager', 'admission-app-manager' ), 'edit_aam_applications', 'aam', 'init_dashboard', 'dashicons-welcome-learn-more' );
		add_submenu_page( 'aam', __( 'Dashboard', 'admission-app-manager' ), __( 'Dashboard', 'admission-app-manager' ), 'edit_aam_applications', 'aam', 'init_dashboard' );
		add_submenu_page( 'aam', 'Settings', 'Settings', 'manage_options', 'aam_settings', 'aam_settings' );
		add_submenu_page( 'aam', 'Import', 'Import', 'edit_aam_applications', 'aam_import', 'aam_init_importer' );
	}

	/**
	 * Add custom fields to user profile
	 *
	 * @param $user
	 */
	public function aam_consultant_custom_fields( $user ) {
		if ( $user->roles[0] === 'consultant' ) {
			?>
			<h3>Consultant data</h3>

			<table class="form-table">
				<tr>
					<th><label for="aam_from">From Email address</label></th>
					<td><input type="text" name="aam_from"
					           value="<?php echo esc_attr( get_the_author_meta( 'aam_from', $user->ID ) ); ?>"
					           class="regular-text"/></td>
				</tr>

				<tr>
					<th><label for="aam_bcc">BCC Email address</label></th>
					<td><input type="text" name="aam_bcc"
					           value="<?php echo esc_attr( get_the_author_meta( 'aam_bcc', $user->ID ) ); ?>"
					           class="regular-text"/></td>
				</tr>

				<?php $days = get_the_author_meta( 'aam_off_days', $user->ID ); ?>

				<tr>
					<th><label for="aam_off_days">Off Days</label></th>
					<td><input type="checkbox" name="aam_off_days[mon]"
					           value="1" <?php echo $days && $days['mon'] ? 'checked' : ''; ?> /> Monday
					</td>
				</tr>
				<tr>
					<th>&nbsp;</th>
					<td><input type="checkbox" name="aam_off_days[tue]"
					           value="1" <?php echo $days && $days['tue'] ? 'checked' : ''; ?> /> Tuesday
					</td>
				</tr>
				<tr>
					<th>&nbsp;</th>
					<td><input type="checkbox" name="aam_off_days[wed]"
					           value="1" <?php echo $days && $days['wed'] ? 'checked' : ''; ?> /> Wednesday
					</td>
				</tr>
				<tr>
					<th>&nbsp;</th>
					<td><input type="checkbox" name="aam_off_days[thu]"
					           value="1" <?php echo $days && $days['thu'] ? 'checked' : ''; ?> /> Thursday
					</td>
				</tr>
				<tr>
					<th>&nbsp;</th>
					<td><input type="checkbox" name="aam_off_days[fri]"
					           value="1" <?php echo $days && $days['fri'] ? 'checked' : ''; ?> /> Friday
					</td>
				</tr>
				<tr>
					<th>&nbsp;</th>
					<td><input type="checkbox" name="aam_off_days[sat]"
					           value="1" <?php echo $days && $days['sat'] ? 'checked' : ''; ?> /> Saturday
					</td>
				</tr>
				<tr>
					<th>&nbsp;</th>
					<td><input type="checkbox" name="aam_off_days[sun]"
					           value="1" <?php echo $days && $days['sun'] ? 'checked' : ''; ?> /> Sunday
					</td>
				</tr>

				<tr>
					<th><label for="aam_uploader_email_template">Uploader email template</label></th>
					<td>
                        <textarea name="aam_uploader_email_template" class="regular-text"
                                  id="aam_uploader_email_template" rows="5" cols="30"><?php
	                        echo esc_attr( get_the_author_meta( 'aam_uploader_email_template', $user->ID ) ); ?></textarea>

						<p class="description">Placeholders: %name% - uploader name, %file% - file name</p>
					</td>
				</tr>

				<tr>
					<th><label for="aam_other_email_template">Other party email template</label></th>
					<td>
                        <textarea name="aam_other_email_template" class="regular-text"
                                  id="aam_other_email_template" rows="5" cols="30"><?php
	                        echo esc_attr( get_the_author_meta( 'aam_other_email_template', $user->ID ) ); ?></textarea>

						<p class="description">Placeholders: %name% - uploader name, %other% - other party name, %file%
							- file name, %link% - file link</p>
					</td>
				</tr>

				<tr>
					<th><label for="aam_application_email_before">Application email before content</label></th>
					<td>
                        <textarea name="aam_application_email_before" class="regular-text"
                                  id="aam_application_email_before" rows="5" cols="30"><?php
	                        echo esc_attr( get_the_author_meta( 'aam_application_email_before', $user->ID ) ); ?></textarea>
					</td>
				</tr>

				<tr>
					<th><label for="aam_application_email_after">Application email after content</label></th>
					<td>
                        <textarea name="aam_application_email_after" class="regular-text"
                                  id="aam_application_email_after" rows="5" cols="30"><?php
	                        echo esc_attr( get_the_author_meta( 'aam_application_email_after', $user->ID ) ); ?></textarea>
					</td>
				</tr>
			</table>
			<?php
			$this->_aam_bo_days_form( $user, 'consultant' );
		}
	}

	/**
	 * Save user settings on profile save
	 *
	 * @param $user_id
	 */
	public function aam_save_consultant_custom_fields( $user_id ) {
		if ( isset( $_POST['aam_from'] ) ) {
			update_user_meta( $user_id, 'aam_from', sanitize_text_field( $_POST['aam_from'] ) );
		}

		if ( isset( $_POST['aam_bcc'] ) ) {
			update_user_meta( $user_id, 'aam_bcc', sanitize_text_field( $_POST['aam_bcc'] ) );
		}

		if ( isset( $_POST['aam_off_days'] ) ) {
			update_user_meta( $user_id, 'aam_off_days', $_POST['aam_off_days'] );
		} else {
			delete_user_meta( $user_id, 'aam_off_days' );
		}

		if ( isset( $_POST['aam_uploader_email_template'] ) ) {
			update_user_meta( $user_id, 'aam_uploader_email_template', $_POST['aam_uploader_email_template'] );
		}

		if ( isset( $_POST['aam_other_email_template'] ) ) {
			update_user_meta( $user_id, 'aam_other_email_template', $_POST['aam_other_email_template'] );
		}

		if ( isset( $_POST['aam_application_email_before'] ) ) {
			update_user_meta( $user_id, 'aam_application_email_before', $_POST['aam_application_email_before'] );
		}

		if ( isset( $_POST['aam_application_email_after'] ) ) {
			update_user_meta( $user_id, 'aam_application_email_after', $_POST['aam_application_email_after'] );
		}
	}

	/**
	 * Add consultant assignment option to client profile
	 *
	 * @param $user
	 */
	public function aam_client_custom_fields( $user ) {
		if ( $user->roles[0] == 'client' ) {

			$assigned = get_the_author_meta( 'aam_assigned_consultant', $user->ID );

			$consultants = get_users( array( 'role' => 'Consultant' ) );

			?>
			<h3>Client data</h3>

			<table class="form-table">
				<tr>
					<th><label for="aam_consultant">Assigned to</label></th>
					<td>
						<select name="aam_consultant" class="regular-text">
							<option value="">--- Select consultant ---</option>
							<?php foreach ( $consultants as $cons ) : ?>
								<option
									value="<?php echo $cons->ID; ?>" <?php echo $cons->ID == $assigned ? 'selected' : ''; ?>><?php echo $cons->display_name; ?></option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>
			</table>
			<?php
		}
	}

	/**
	 * Add custom fields for client user for his own edit
	 *
	 * @param $user
	 */
	public function aam_client_own_custom_fields( $user ) {
		$this->_aam_bo_days_form( $user, 'client' );
	}

	private function _aam_bo_days_form( $user, $usertype = 'client' ) {
		if ( $user->roles[0] == $usertype ) {
			$bo_days = get_the_author_meta( 'aam_bo_days', $user->ID );

			if ( ! $bo_days || ! is_array( $bo_days ) ) {
				$bo_days = array( array( 'from' => '', 'to' => '' ) );
			}

			?>
			<h3>Blackout days</h3>

			<table class="form-table aam-<?php echo $usertype; ?>-bo-days">
				<tbody>
				<?php foreach ( $bo_days as $key => $range ) : ?>
					<tr>
						<th><label>From</label></th>
						<td style="width:200px">
							<input type="text" name="aam_bo_days[<?php echo $key; ?>][from]" placeholder="dd/mm/yyyy"
							       class="regular_text"
							       value="<?php echo esc_attr( $range['from'] ); ?>"/>
						</td>
						<th style="width:50px"><label for="aam_bo_days_to">To</label></th>
						<td style="width:200px">
							<input type="text" name="aam_bo_days[<?php echo $key; ?>][to]" placeholder="dd/mm/yyyy"
							       class="regular_text"
							       value="<?php echo esc_attr( $range['to'] ); ?>"/>
						</td>
						<td>
							<a href="#" class="aam-remove-row"
							   style="color: #F00; font-weight: 700; text-decoration: none;">X</a>&nbsp;
							<a href="#" class="aam-add-row"
							   style="color: #0F0; font-weight: 700; text-decoration: none;">+</a>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
			<script>
				jQuery(document).ready(function ($) {
					var index = <?php echo count( $bo_days ); ?>;
					var table = $('.aam-<?php echo $usertype; ?>-bo-days');

					table.on('click', '.aam-remove-row', function (e) {
						e.preventDefault();

						var left = table.children().children().length;

						console.log(left);

						if (left > 1) {
							$(this).parent().parent().remove();
						}
					});

					table.on('click', '.aam-add-row', function (e) {
						e.preventDefault();

						index++;

						table.append('<tr>' +
							'<th><label>From</label></th> ' +
							'<td style="width:200px"> ' +
							'<input type="text" name="aam_bo_days[' + index + '][from]" placeholder="dd/mm/yyyy" class="regular_text" value=""/> ' +
							'</td>' +
							'<th style="width:50px"><label for="aam_bo_days_to">To</label></th> ' +
							'<td style="width:200px"> ' +
							'<input type="text" name="aam_bo_days[' + index + '][to]" placeholder="dd/mm/yyyy" class="regular_text" value=""/> ' +
							'</td>' +
							'<td> ' +
							'<a href="#" class="aam-remove-row" style="color: #F00; font-weight: 700; text-decoration: none;">X</a>&nbsp; ' +
							'<a href="#" class="aam-add-row" style="color: #0F0; font-weight: 700; text-decoration: none;">+</a> ' +
							'</td> ' +
							'</tr>');
					});
				});
			</script>
			<?php
		}
	}

	/**
	 * Save consultant assignment
	 *
	 * @param $user_id
	 */
	public function aam_save_client_custom_fields( $user_id ) {
		if ( isset( $_POST['aam_consultant'] ) ) {
			update_user_meta( $user_id, 'aam_assigned_consultant', sanitize_text_field( $_POST['aam_consultant'] ) );
		}
	}

	/**
	 * Save client fields
	 *
	 * @param $user_id
	 */
	public function aam_save_client_own_custom_fields( $user_id ) {
		update_user_meta( $user_id, 'aam_bo_days', $_POST['aam_bo_days'] );
	}

	/**
	 * Add consultant assignment option to client profile
	 *
	 * @param $user
	 */
	public function aam_school_custom_fields( $user ) {
		global $wpdb;

		if ( $user->roles[0] == 'school' ) {

			$assigned = get_the_author_meta( 'aam_assigned_school', $user->ID );

			$schools = get_posts( array(
				'post_type'      => 'school',
				'posts_per_page' => - 1
			) );

			?>
			<h3>School options</h3>

			<table class="form-table">
				<tr>
					<th><label for="aam_school">Assigned school</label></th>
					<td>
						<select name="aam_school" class="regular-text" id="aam_school">
							<option value="">--- Select school ---</option>
							<?php foreach ( $schools as $school ) : ?>
								<option
									value="<?php echo $school->ID; ?>" <?php echo $school->ID == $assigned ? 'selected' : ''; ?>><?php echo $school->post_title; ?></option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>
			</table>
			<?php
		}
	}

	/**
	 * Save consultant assignment
	 *
	 * @param $user_id
	 */
	public function aam_save_school_custom_fields( $user_id ) {
		if ( isset( $_POST['aam_school'] ) ) {
			update_user_meta( $user_id, 'aam_assigned_school', sanitize_text_field( $_POST['aam_school'] ) );
		} else {
			delete_user_meta( $user_id, 'aam_assigned_school' );
		}
	}

	/**
	 * Add hash to filename on file upload
	 *
	 * @param $filename
	 *
	 * @return string
	 */
	public function make_filename_hash( $filename ) {
		$info = pathinfo( $filename );
		$ext  = empty( $info['extension'] ) ? '' : '.' . $info['extension'];
		$name = basename( $filename, $ext );

		return $name . '_' . md5( $name . time() ) . $ext;
	}

	/**
	 * Send all prompts for documents to client
	 *
	 * @param $application
	 */
	public function aam_send_prompts_to_client( $application ) {
		$application_id = count( $application ) > 0 ? $application[0]['application_id'] : false;

		$documents = array();

		if ( $application_id ) {
			$combination_data = get_post_meta( $application_id, '_aam_application_combination', true );

			if ( isset( $combination_data['combination'] ) ) {
				$combination_documents = get_post_meta( $combination_data['combination'], '_aam_combination_documents', true );

				if ( $combination_documents && count( $combination_documents ) > 0 ) {
					foreach ( $combination_documents as $document ) {
						$send_check = get_post_meta( $document['type'], '_aam_type_send', true );

						if ( $send_check && $send_check === 'on' ) {
							if ( ! isset( $documents[ $document['type'] ] ) ) {
								$documents[ $document['type'] ] = array();
							}

							$documents[ $document['type'] ][] = array(
								'prompt'   => $document['prompt'],
								'required' => $document['required']
							);
						}
					}
				}
			}
		}

		//send emails
		$client_id    = get_post_meta( $application_id, '_aam_application_client', true );
		$client       = get_userdata( $client_id );
		$client_email = $client->user_email;

		$current_user = wp_get_current_user();

		$from = get_user_meta( $current_user->ID, 'aam_from', true );
		$bcc  = get_user_meta( $current_user->ID, 'aam_bcc', true );

		$before_text = get_user_meta( $current_user->ID, 'aam_application_email_before', true );
		$after_text  = get_user_meta( $current_user->ID, 'aam_application_email_after', true );

		$headers   = array();
		$headers[] = 'Content-Type: text/html; charset=UTF-8';
		$headers[] = 'From: ' . $current_user->display_name . ' <' . ( $from ? $from : $current_user->user_email ) . '>';

		if ( $bcc ) {
			$headers[] = 'Bcc: ' . $bcc;
		}

		foreach ( $documents as $document_id => $prompts ) {
			$document_data = get_post( $document_id );

			$body = nl2br( $before_text );
			$body .= '<ul>';

			foreach ( $prompts as $prompt ) {
				$body .= '<li>' . $prompt['prompt'] . '</li>';
			}

			$body .= '</ul>';
			$body .= nl2br( $after_text );

			wp_mail( $client_email, 'Prompts for ' . $document_data->post_title, $body, $headers );
		}
	}

	/**
	 * Send all application deadlines to client on application save
	 *
	 * @param $application
	 */
	public function aam_send_deadlines_to_client( $application ) {
		$application_id = count( $application ) > 0 ? $application[0]['application_id'] : false;

		$sorted = array();

		if ( $application ) {
			$current = null;

			foreach ( $application as $step ) {
				if ( ! isset( $sorted[ $step['document_id'] ] ) ) {
					$document_data = get_post( $step['document_type'] );

					$sorted[ $step['document_id'] ] = array(
						'id'       => $step['id'],
						'document' => $document_data->post_title,
						'prompt'   => $step['prompt'],
						'type'     => $step['document_type'],
						'weight'   => $step['document_weight'],
						'required' => $step['required'],
						'active'   => $step['active'],
						'items'    => array()
					);
				}

				$sorted[ $step['document_id'] ]['items'][] = $step;
			}
		}

		//send emails
		$client_id    = get_post_meta( $application_id, '_aam_application_client', true );
		$client       = get_userdata( $client_id );
		$client_email = $client->user_email;

		$current_user = wp_get_current_user();

		$before_text = get_user_meta( $current_user->ID, 'aam_application_email_before', true );
		$after_text  = get_user_meta( $current_user->ID, 'aam_application_email_after', true );

		$from = get_user_meta( $current_user->ID, 'aam_from', true );
		$bcc  = get_user_meta( $current_user->ID, 'aam_bcc', true );

		$i = 1;

		$body = nl2br( $before_text );

		foreach ( $sorted as $sk => $step ) :
			$body .= '<b>' . $step['document'] . ' - ' . $step['prompt'] . '</b>';

			$body .= '<ul>';

			$k = 1;
			foreach ( $step['items'] as $item ) :
                //var_dump($item);
				//$deadline = new Carbon($item->deadline);

				$body .= '<li>' . $k . '. ' . $item['name'] . ' - ' . $item['deadline']->format( get_option( 'date_format' ) ) . '</li>';

				$k ++;
			endforeach;
			$body .= '</ul>';

			$i ++;
		endforeach;

		$body .= nl2br( $after_text );

		$headers   = array();
		$headers[] = 'Content-Type: text/html; charset=UTF-8';
		$headers[] = 'From: ' . $current_user->display_name . ' <' . ( $from ? $from : $current_user->user_email ) . '>';

		if ( $bcc ) {
			$headers[] = 'Bcc: ' . $bcc;
		}

		wp_mail( $client_email, 'Application deadlines', $body, $headers );
	}

	/**
	 * Redirect user after successful login.
	 *
	 * @param string $redirect_to URL to redirect to.
	 * @param string $request URL the user is coming from.
	 * @param object $user Logged user's data.
	 *
	 * @return string
	 */
	public function aam_login_redirect( $redirect_to, $request, $user ) {
		//is there a user to check?
		if ( isset( $user->roles ) && is_array( $user->roles ) ) {
			if ( in_array( 'client', $user->roles ) ) {
				// if user is client first get applications page id
				$settings = get_option( 'aam_settings', array() );

				if ( isset( $settings['applications_page'] ) ) {
					return get_permalink( $settings['applications_page'] );
				}

				return $redirect_to;
			} else if ( in_array( 'consultant', $user->roles ) ) {
				return admin_url( 'edit.php?post_type=application' );
			} else {
				return $redirect_to;
			}
		} else {
			return $redirect_to;
		}
	}

	/**
	 * Remove all dashboard widgets
	 */
	public function remove_dashboard_meta() {
		remove_meta_box( 'dashboard_incoming_links', 'dashboard', 'normal' );
		remove_meta_box( 'dashboard_plugins', 'dashboard', 'normal' );
		remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );
		remove_meta_box( 'dashboard_secondary', 'dashboard', 'normal' );
		remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
		remove_meta_box( 'dashboard_recent_drafts', 'dashboard', 'side' );
		remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );
		remove_meta_box( 'dashboard_right_now', 'dashboard', 'normal' );
		remove_meta_box( 'dashboard_activity', 'dashboard', 'normal' );//since 3.8
	}

	/**
	 * Force single column widget on admin dashboard
	 *
	 * @param $columns
	 *
	 * @return mixed
	 */
	public function aam_screen_layout_columns( $columns ) {
		$columns['dashboard'] = 1;

		return $columns;
	}

	/**
	 * Force single column widget on admin dashboard
	 * @return int
	 */
	public function aam_screen_layout_dashboard() {
		return 1;
	}

	/**
	 * Add a widget to the dashboard.
	 *
	 * This function is hooked into the 'wp_dashboard_setup' action below.
	 */
	public function aam_add_dashboard_widgets() {

		wp_add_dashboard_widget(
			'aam_dashboard_widget',         // Widget slug.
			'Admission AppManager',         // Title.
			array( &$this, 'aam_dashboard_widget_function' ) // Display function.
		);
	}

	/**
	 * Create the function to output the contents of our Dashboard Widget.
	 */
	public function aam_dashboard_widget_function() {
		global $wpdb;

		$is_admin      = current_user_can( 'manage_options' );
		$is_consultant = current_user_can( 'manage_clients' );

		$modifier = "";

		if ( $is_consultant && ! $is_admin ) {
			$consultant_id = get_current_user_id();

			$modifier .= " and um.meta_value = $consultant_id ";
		}

		$q = '';

		if ( isset( $_POST['q'] ) && isset( $_POST['action'] ) && $_POST['action'] == 'aam-widget-search' ) {
			$q = $_POST['q'];

			$modifier .= " and (p.post_title like '%%%s%%' or us1.user_login like '%%%s%%' or us2.user_login like '%%%s%%') ";
		}

		$query = "select p.ID, p.post_title, pm1.meta_value as completed, us1.ID as client_id,
                        us1.user_login as client_login, us2.ID as consultant_id, us2.user_login as consultant_login
                        from wp_posts as p
                        left join wp_postmeta as pm1 on p.ID = pm1.post_id and pm1.meta_key = '_aam_total_completion'
                        left join wp_postmeta as pm2 on pm2.post_id = p.ID and pm2.meta_key = '_aam_application_client'
                        left join wp_users as us1 on us1.ID = pm2.meta_value
                        left join wp_usermeta as um on um.user_id = us1.ID and um.meta_key = 'aam_assigned_consultant'
                        left join wp_users as us2 on us2.ID = um.meta_value
                        where pm1.meta_value < 100 $modifier order by us2.user_login, us1.user_login;";

		$data = $wpdb->get_results( $wpdb->prepare( $query, $q, $q, $q ) );

		if ( $is_admin ) {

			$transformed = array();

			foreach ( $data as $row ) {
				if ( ! isset( $transformed[ $row->consultant_id ] ) ) {
					$transformed[ $row->consultant_id ] = array(
						'consultant_login' => $row->consultant_login,
						'clients'          => array()
					);
				}

				if ( ! isset( $transformed[ $row->consultant_id ]['clients'][ $row->client_id ] ) ) {
					$transformed[ $row->consultant_id ]['clients'][ $row->client_id ] = array(
						'client_login' => $row->client_login,
						'applications' => array()
					);
				}

				$transformed[ $row->consultant_id ]['clients'][ $row->client_id ]['applications'][] = $row;
			} ?>

			<ul class="aam-widget-list">
				<?php foreach ( $transformed as $ck => $cv ) : ?>
					<li>
						<div class="inner">
							<a href="#"
							   class="aam-caret aam-toggle closed"
							   data-open="aam-widget-consultant-<?php echo get_the_ID(); ?>-<?php echo $ck; ?>"><img
									src="<?php echo plugin_dir_url( __FILE__ ); ?>img/arrow.png"/></a>&nbsp;
							<?php echo $cv['consultant_login']; ?>
						</div>

						<ul id="aam-widget-consultant-<?php echo get_the_ID(); ?>-<?php echo $ck; ?>" class="hidden">
							<?php foreach ( $cv['clients'] as $clk => $clv ) : ?>
								<li>
									<div class="inner">
										<a href="#"
										   class="aam-caret aam-toggle closed"
										   data-open="aam-widget-client-<?php echo get_the_ID(); ?>-<?php echo $clk; ?>"><img
												src="<?php echo plugin_dir_url( __FILE__ ); ?>img/arrow.png"/></a>&nbsp;
										<?php echo $clv['client_login']; ?>
									</div>

									<ul id="aam-widget-client-<?php echo get_the_ID(); ?>-<?php echo $clk; ?>"
									    class="hidden">
										<?php foreach ( $clv['applications'] as $app ) : ?>
											<li>
												<div class="inner">
													<a href="<?php echo admin_url( 'admin.php?post=aam&action=edit' ); ?>"><?php echo $app->post_title; ?></a>

													<div class="aam-pb-container">
														<div class="progress-bar"
														     data-completion="<?php echo $app->completed; ?>">
															<span>&nbsp;<?php echo $app->completed; ?>%</span></div>
													</div>
												</div>
											</li>
										<?php endforeach; ?>
									</ul>
								</li>
							<?php endforeach; ?>
						</ul>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php } else if ( $is_consultant && ! $is_admin ) {
			$transformed = array();

			foreach ( $data as $row ) {
				if ( ! isset( $transformed[ $row->client_id ] ) ) {
					$transformed[ $row->client_id ] = array(
						'client_login' => $row->client_login,
						'applications' => array()
					);
				}

				$transformed[ $row->client_id ]['applications'][] = $row;
			} ?>

			<ul class="aam-widget-list">
				<?php foreach ( $transformed as $clk => $clv ) : ?>
					<li>
						<div class="inner">
							<a href="#"
							   class="aam-caret aam-toggle closed"
							   data-open="aam-widget-client-<?php echo get_the_ID(); ?>-<?php echo $clk; ?>"><img
									src="<?php echo plugin_dir_url( __FILE__ ); ?>img/arrow.png"/></a>&nbsp;
							<?php echo $clv['client_login']; ?>
						</div>

						<ul id="aam-widget-client-<?php echo get_the_ID(); ?>-<?php echo $clk; ?>"
						    class="hidden">
							<?php foreach ( $clv['applications'] as $app ) : ?>
								<li>
									<div class="inner">
										<a href="<?php echo admin_url( 'admin.php?post=aam&action=edit' ); ?>"><?php echo $app->post_title; ?></a>

										<div class="aam-pb-container">
											<div class="progress-bar"
											     data-completion="<?php echo $app->completed; ?>">
												<span>&nbsp;<?php echo $app->completed; ?>%</span></div>
										</div>
									</div>
								</li>
							<?php endforeach; ?>
						</ul>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php } ?>
		<div class="aam-widget-footer">
			<form action="<?php echo admin_url( '/' ); ?>" method="post">
				<input placeholder="Search" id="aam-widget-search" name="q" class="prompt" type="text"
				       value="<?php echo $_POST['q']; ?>"/>
				<input type="hidden" name="action" value="aam-widget-search"/>
				<button class="button button-primary" type="submit">Search</button>
			</form>
		</div>
		<?php
	}
}
