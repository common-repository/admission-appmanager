<?php

class Admission_App_Manager_Taxonomy {

	public function __construct() {
		add_action( 'init', array( &$this, 'init_location' ), 0 );
		//add_action( 'init', array( &$this, 'init_timezone' ), 0 );
		add_action( 'init', array( &$this, 'init_intake' ), 0 );
		add_action( 'init', array( &$this, 'init_status' ), 0 );

		add_action( 'status_add_form_fields', array( &$this, 'status_add_new_meta_fields' ), 10, 2 );
		add_action( 'status_edit_form_fields', array( &$this, 'status_edit_meta_fields' ), 10, 2 );
		add_action( 'edited_status', array( &$this, 'save_status_meta' ), 10, 2 );
		add_action( 'create_status', array( &$this, 'save_status_meta' ), 10, 2 );
	}

	public function init_location() {
		$labels = array(
			'name'              => _x( 'Locations', 'taxonomy general name', 'admission-app-manager' ),
			'singular_name'     => _x( 'Location', 'taxonomy singular name', 'admission-app-manager' ),
			'search_items'      => __( 'Search Locations', 'admission-app-manager' ),
			'all_items'         => __( 'All Locations', 'admission-app-manager' ),
			'parent_item'       => __( 'Parent Location', 'admission-app-manager' ),
			'parent_item_colon' => __( 'Parent Location:', 'admission-app-manager' ),
			'edit_item'         => __( 'Edit Location', 'admission-app-manager' ),
			'update_item'       => __( 'Update Location', 'admission-app-manager' ),
			'add_new_item'      => __( 'Add New Location', 'admission-app-manager' ),
			'new_item_name'     => __( 'New Location Name', 'admission-app-manager' ),
			'menu_name'         => __( 'Location', 'admission-app-manager' ),
		);

		$args = array(
			'hierarchical'      => true,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => array( 'slug' => 'location' ),
			'meta_box_cb'       => false
		);

		register_taxonomy( 'location', array( 'school' ), $args );
	}

	public function init_timezone() {
		$labels = array(
			'name'              => _x( 'Timezones', 'taxonomy general name', 'admission-app-manager' ),
			'singular_name'     => _x( 'Timezone', 'taxonomy singular name', 'admission-app-manager' ),
			'search_items'      => __( 'Search Timezones', 'admission-app-manager' ),
			'all_items'         => __( 'All Timezones', 'admission-app-manager' ),
			'parent_item'       => __( 'Parent Timezone', 'admission-app-manager' ),
			'parent_item_colon' => __( 'Parent Timezone:', 'admission-app-manager' ),
			'edit_item'         => __( 'Edit Timezone', 'admission-app-manager' ),
			'update_item'       => __( 'Update Timezone', 'admission-app-manager' ),
			'add_new_item'      => __( 'Add New Timezone', 'admission-app-manager' ),
			'new_item_name'     => __( 'New Timezone Name', 'admission-app-manager' ),
			'menu_name'         => __( 'Timezone', 'admission-app-manager' ),
		);

		$args = array(
			'hierarchical'      => true,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => array( 'slug' => 'timezone' ),
			'meta_box_cb'       => false
		);

		register_taxonomy( 'timezone', array( 'school' ), $args );
	}

	public function init_intake() {
		$labels = array(
			'name'              => _x( 'Intakes', 'taxonomy general name', 'admission-app-manager' ),
			'singular_name'     => _x( 'Intake', 'taxonomy singular name', 'admission-app-manager' ),
			'search_items'      => __( 'Search Intakes', 'admission-app-manager' ),
			'all_items'         => __( 'All Intakes', 'admission-app-manager' ),
			'parent_item'       => __( 'Parent Intake', 'admission-app-manager' ),
			'parent_item_colon' => __( 'Parent Intake:', 'admission-app-manager' ),
			'edit_item'         => __( 'Edit Intake', 'admission-app-manager' ),
			'update_item'       => __( 'Update Intake', 'admission-app-manager' ),
			'add_new_item'      => __( 'Add New Intake', 'admission-app-manager' ),
			'new_item_name'     => __( 'New Intake Name', 'admission-app-manager' ),
			'menu_name'         => __( 'Intake', 'admission-app-manager' ),
		);

		$args = array(
			'hierarchical'      => true,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => array( 'slug' => 'intake' ),
			'meta_box_cb'       => false
		);

		register_taxonomy( 'intake', array( 'school' ), $args );
	}

	public function init_status() {
		$labels = array(
			'name'              => _x( 'Statuses', 'taxonomy general name', 'admission-app-manager' ),
			'singular_name'     => _x( 'Status', 'taxonomy singular name', 'admission-app-manager' ),
			'search_items'      => __( 'Search Statuses', 'admission-app-manager' ),
			'all_items'         => __( 'All Statuses', 'admission-app-manager' ),
			'parent_item'       => __( 'Parent Status', 'admission-app-manager' ),
			'parent_item_colon' => __( 'Parent Status:', 'admission-app-manager' ),
			'edit_item'         => __( 'Edit Status', 'admission-app-manager' ),
			'update_item'       => __( 'Update Status', 'admission-app-manager' ),
			'add_new_item'      => __( 'Add New Status', 'admission-app-manager' ),
			'new_item_name'     => __( 'New Status Name', 'admission-app-manager' ),
			'menu_name'         => __( 'Status', 'admission-app-manager' ),
		);

		$args = array(
			'hierarchical'      => true,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => array( 'slug' => 'status' ),
			'meta_box_cb'       => false
		);

		register_taxonomy( 'status', array( 'application' ), $args );
	}

	/**
	 * Add new meta fields to status custom tax
	 */
	public function status_add_new_meta_fields() {
		?>
		<div class="form-field">
			<label for="term_meta[percent]"><?php _e( 'Percent Completion', 'admission-app-manager' ); ?></label>
			<input type="text" name="term_meta[percent]" id="term_meta[percent]" value="">
		</div>

		<div class="form-field">
			<label for="term_meta[notify]"><?php _e( 'Notify', 'admission-app-manager' ); ?></label>
			<input type="checkbox" name="term_meta[notify]" id="term_meta[notify]" value="1">
		</div>

		<div class="form-field">
			<label for="term_meta[complete]"><?php _e( 'Complete', 'admission-app-manager' ); ?></label>
			<input type="checkbox" name="term_meta[complete]" id="term_meta[complete]" value="1">
		</div>
	<?php
	}

	/**
	 * @param $term
	 */
	public function status_edit_meta_fields( $term ) {

		// put the term ID into a variable
		$t_id = $term->term_id;

		// retrieve the existing value(s) for this meta field. This returns an array
		$term_meta = get_option( "status_$t_id" ); ?>

		<tr class="form-field">
			<th scope="row" valign="top"><label
					for="term_meta[percent]"><?php _e( 'Percent Completion', 'admission-app-manager' ); ?></label></th>
			<td>
				<input type="text" name="term_meta[percent]" id="term_meta[percent]"
				       value="<?php echo esc_attr( $term_meta['percent'] ) ? esc_attr( $term_meta['percent'] ) : ''; ?>">
			</td>
		</tr>

		<tr class="form-field">
			<th scope="row" valign="top"><label
					for="term_meta[notify]"><?php _e( 'Notify', 'admission-app-manager' ); ?></label></th>
			<td>
				<input type="checkbox" name="term_meta[notify]" id="term_meta[notify]"
				       value="1" <?php echo esc_attr( $term_meta['notify'] ) == 1 ? 'checked' : ''; ?>>
			</td>
		</tr>

		<tr class="form-field">
			<th scope="row" valign="top"><label
					for="term_meta[complete]"><?php _e( 'Complete', 'admission-app-manager' ); ?></label></th>
			<td>
				<input type="checkbox" name="term_meta[complete]" id="term_meta[complete]"
				       value="1" <?php echo esc_attr( $term_meta['complete'] ) == 1 ? 'checked' : ''; ?>>
			</td>
		</tr>
	<?php
	}

	/**
	 * @param $term_id
	 */
	public function save_status_meta( $term_id ) {
		if ( isset( $_POST['term_meta'] ) ) {
			$t_id = $term_id;

			update_option( "status_$t_id", $_POST['term_meta'] );
		}
	}
}

$taxonomy = new Admission_App_Manager_Taxonomy;