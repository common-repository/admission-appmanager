<?php
class Admission_App_Manager_Ajax {

	public function __construct() {
		add_action( 'wp_ajax_get_programs', array(&$this, 'aam_applications_get_programs') );
		add_action( 'wp_ajax_get_intakes', array(&$this, 'aam_applications_get_intakes') );
		add_action( 'wp_ajax_get_rounds', array(&$this, 'aam_applications_get_rounds') );
		add_action( 'wp_ajax_find_combination', array(&$this, 'aam_applications_find_combination') );
	}

	public function aam_applications_get_programs() {

		$school_id = esc_attr($_REQUEST['school_id']);

		$args = array(
			'post_type' => 'school',
			'p'         => $school_id
		);

		$query = new WP_Query($args);

		$json = array();

		foreach($query->get_posts() as $school) {
			$programs = get_post_meta($school->ID, '_aam_school_programs_group', true);

			foreach($programs as $program) {
				$json[$program['name']] = $program['name'];
			}
		}

		echo json_encode($json);

		wp_die();
	}

	public function aam_applications_get_intakes() {

		$school_id = esc_attr($_REQUEST['school_id']);
		$program = esc_attr($_REQUEST['program']);

		$args = array(
			'post_type' => 'combination',
			'meta_query' => array(
				array(
					'key' => '_aam_combination_school',
					'compare' => '=',
					'value' => $school_id
				),
				array(
					'key' => '_aam_combination_program',
					'compare' => '=',
					'value' => $program
				)
			)
		);

		$query = new WP_Query($args);

		$json = array();

		foreach($query->get_posts() as $c) {
			$intakes = wp_get_post_terms($c->ID, 'intake');

			foreach($intakes as $intake) {
				$json[$intake->term_id] = $intake->name;
			}
		}

		echo json_encode($json);

		wp_die();
	}

	/**
	 * Get rounds when program is selected
	 */
	public function aam_applications_get_rounds() {
		global $wpdb;

		$program_id = esc_attr($_REQUEST['program_id']);

		$programs_table = $wpdb->prefix . 'aam_programs';
		$program = $wpdb->get_row($wpdb->prepare("SELECT * FROM $programs_table WHERE id = %d", $program_id), ARRAY_A);

		echo json_encode(array('rounds' => $program['rounds']));

		wp_die();
	}

	/**
	 * Find combinations by selected school, program and intake
	 */
	public function aam_applications_find_combination() {
		$school = esc_attr($_REQUEST['school']);
		$program = esc_attr($_REQUEST['program']);
		$intake = esc_attr($_REQUEST['intake']);

		$args = array(
			'post_type' => 'combination',
			'tax_query' => array(
				array(
					'taxonomy' => 'intake',
					'field'    => 'term_id',
					'terms'    => $intake
				),
			),
			'meta_query' => array(
				array(
					'key'     => '_aam_combination_school',
					'value'   => $school,
					'compare' => '=',
				),
				array(
					'key'     => '_aam_combination_program',
					'value'   => $program,
					'compare' => '=',
				),
			)
		);

		$query = new WP_Query($args);

		$json = array();

		if($query->have_posts()) {
			foreach ( $query->get_posts() as $comb ) {
				$json = array(
					'id'  => $comb->ID,
					'rounds' => get_post_meta( $comb->ID, '_aam_combination_rounds', true )
                );
			}
		}

		echo json_encode($json);

		wp_die();
	}
}

$ajax = new Admission_App_Manager_Ajax();