<?php

class Admission_App_Manager_Custom_Fields {

	public function __construct() {
		add_action( 'cmb2_init', array(&$this, 'set_program_cf') );
		add_action( 'cmb2_init', array(&$this, 'set_school_cf') );
		add_action( 'cmb2_init', array(&$this, 'set_combination_cf') );
		add_action( 'cmb2_init', array(&$this, 'set_application_cf') );
		//add_action( 'cmb2_init', array(&$this, 'set_step_cf') );
		add_action( 'cmb2_init', array(&$this, 'set_document_type_cf') );
		add_action( 'cmb2_init', array(&$this, 'set_document_cf') );

		add_action( 'cmb2_render_text_disabled', array( &$this, 'cmb2_render_callback_for_text_disabled' ), 10, 5 );
		add_filter( 'cmb2_sanitize_text_disabled', array( &$this, 'cmb2_sanitize_text_disabled_callback' ), 10, 2 );
	}

	public function cmb2_render_callback_for_text_disabled( $field, $escaped_value, $object_id, $object_type, $field_type_object ) {
		echo $field_type_object->input( array( 'type' => 'text', 'disabled' => 'disabled' ) );
	}

	function cmb2_sanitize_text_disabled_callback( $override_value, $value ) {
		return $value;
	}

	public function set_program_cf() {

		//get all schools for dropdown
		$args = array(
			'post_type' => 'school'
		);

		$query = new WP_Query($args);

		$schools = array();

		foreach($query->get_posts() as $school) {
			$schools[$school->ID] = $school->post_title;
		}

		$prefix = '_aam_program_';

		$cmb = new_cmb2_box( array(
			'id'            => $prefix . 'metabox',
			'title'         => __( 'Program data', 'cmb2' ),
			'object_types'  => array( 'program', ),
			'context'       => 'normal',
			'priority'      => 'high',
			'show_names'    => true
		) );

		$cmb->add_field( array(
			'name'             => __( 'School', 'cmb2' ),
			'desc'             => __( 'select school', 'cmb2' ),
			'id'               => $prefix . 'school',
			'type'             => 'select',
			'show_option_none' => true,
			'options'          => $schools
		) );

		$cmb->add_field( array(
			'name' => __( 'Program URL', 'cmb2' ),
			'id'   => $prefix . 'program_url',
			'type' => 'text_url',
		) );
	}

	public function set_school_cf() {
		$prefix = '_aam_school_';

		$cmb = new_cmb2_box( array(
			'id'            => $prefix . 'general',
			'title'         => __( 'School data', 'cmb2' ),
			'object_types'  => array( 'school', ),
			'context'       => 'normal',
			'priority'      => 'high',
			'show_names'    => true
		) );

		$cmb->add_field( array(
			'name'     => __( 'Location', 'cmb2' ),
			'desc'     => __( 'select location', 'cmb2' ),
			'id'       => $prefix . 'location',
			'type'     => 'taxonomy_select',
			'taxonomy' => 'location', // Taxonomy Slug
		) );

		$cmb->add_field( array(
			'name'     => __( 'Timezone', 'cmb2' ),
			'desc'     => __( 'select timezone', 'cmb2' ),
			'id'       => $prefix . 'timezone',
			'type'     => 'select_timezone',
			//'taxonomy' => 'timezone', // Taxonomy Slug
		) );

		$cmb_group = new_cmb2_box( array(
			'id'            => $prefix . 'programs',
			'title'         => __( 'Programs', 'cmb2' ),
			'object_types'  => array( 'school', ),
			'context'       => 'normal',
			'priority'      => 'high',
			'show_names'    => true
		) );

		$group_id = $cmb_group->add_field( array(
			'id'          => $prefix . 'programs_group',
			'type'        => 'group',
			//'description' => __( 'Enter this school programs', 'cmb2' ),
			'options'     => array(
				'group_title'   => __( 'Program {#}', 'cmb2' ),
				'add_button'    => __( 'Add Another Program', 'cmb2' ),
				'remove_button' => __( 'Remove Program', 'cmb2' ),
				'sortable'      => true
			),
		) );

		$cmb_group->add_group_field( $group_id, array(
			'name'       => __( 'Name', 'cmb2' ),
			'id'         => 'name',
			'type'       => 'text',
		) );

		$cmb_group->add_group_field( $group_id, array(
			'name'       => __( 'Program URL', 'cmb2' ),
			'id'         => 'url',
			'type'       => 'text_url',
		) );
	}

	public function set_combination_cf() {
		//get schools
		$args = array(
			'post_type' => 'school'
		);

		$query = new WP_Query($args);

		$schools = array();

		foreach($query->get_posts() as $school) {
			$schools[$school->ID] = $school->post_title;
		}

		//get post if editing
		$school_id = 0;

		if(isset($_GET['post']) && $_GET['post']) {
			$school_id = get_post_meta($_GET['post'], '_aam_combination_school', true);
		}

		//get programs
		$args = array(
			'post_type' => 'school',
		);

		if($school_id) {
			$args['p'] = $school_id;
		}

		$query = new WP_Query($args);

		$pgs = array();

		foreach($query->get_posts() as $school) {
			$programs = get_post_meta($school->ID, '_aam_school_programs_group', true);

            if($programs) {
                foreach ($programs as $program) {
                    $pgs[$program['name']] = $program['name'];
                }
            }
		}

		$prefix = '_aam_combination_';

		$cmb = new_cmb2_box( array(
			'id'            => $prefix . 'metabox',
			'title'         => __( 'Application Requirement data', 'cmb2' ),
			'object_types'  => array( 'combination', ),
			'context'       => 'normal',
			'priority'      => 'high',
			'show_names'    => true
		) );

		$cmb->add_field( array(
			'name'             => __( 'School', 'cmb2' ),
			'desc'             => __( 'select school', 'cmb2' ),
			'id'               => $prefix . 'school',
			'type'             => 'select',
			'show_option_none' => true,
			'options'          => $schools
		) );

		$cmb->add_field( array(
			'name'             => __( 'Program', 'cmb2' ),
			'desc'             => __( 'select program', 'cmb2' ),
			'id'               => $prefix . 'program',
			'type'             => 'select',
			'show_option_none' => true,
			'options'          => $pgs
		) );

		$cmb->add_field( array(
			'name'     => __( 'Intake', 'cmb2' ),
			'desc'     => __( 'select intake', 'cmb2' ),
			'id'       => $prefix . 'intake',
			'type'     => 'taxonomy_select',
			'taxonomy' => 'intake',
		) );

		$cmb->add_field( array(
			'name'       => __( 'Rounds', 'cmb2' ),
			'desc'       => __( 'Enter number of rounds', 'cmb2' ),
			'id'         => $prefix . 'rounds',
			'type'       => 'text',
			'default'    => 0
		) );

		$group_id = $cmb->add_field( array(
			'id'          => $prefix . 'deadlines',
			'type'        => 'group',
			'options'     => array(
				'group_title'   => __( 'Deadline {#}', 'cmb2' ),
				'add_button'    => __( 'Add Another Deadline', 'cmb2' ),
				'remove_button' => __( 'Remove Deadline', 'cmb2' ),
				'sortable'      => true
			),
		) );

		$cmb->add_group_field( $group_id, array(
			'name'       => __( 'Deadline Date', 'cmb2' ),
			'id'         => 'date',
			'type'       => 'text_date',
            'date_format'=> get_option('date_format')
		) );

		//add documents meta box
		$query = new WP_Query(array(
			'post_type' => 'type'
		));

		$documents = array('' => 'Select document type');

		foreach($query->get_posts() as $document) {
			$documents[$document->ID] = $document->post_title;
		}

		$cmb = new_cmb2_box( array(
			'id'            => $prefix . 'types_metabox',
			'title'         => __( 'Documents', 'cmb2' ),
			'object_types'  => array( 'combination', ),
			'context'       => 'normal',
			'priority'      => 'high',
			'show_names'    => true
		) );

		$group_id = $cmb->add_field( array(
			'id'          => $prefix . 'documents',
			'type'        => 'group',
			'options'     => array(
				'group_title'   => __( 'Document {#}', 'cmb2' ),
				'add_button'    => __( 'Add Another Document', 'cmb2' ),
				'remove_button' => __( 'Remove Document', 'cmb2' ),
				'sortable'      => true
			),
		) );

		$cmb->add_group_field( $group_id, array(
			'name'       => __( 'Document type', 'cmb2' ),
			'id'         => 'type',
			'type'       => 'select',
			'options'   =>  $documents,
			'classes'   => 'aam-document-type'
		) );

		$cmb->add_group_field( $group_id, array(
			'name'       => __( 'Document name', 'cmb2' ),
			'id'         => 'name',
			'type'       => 'text',
			'classes'     => 'aam-document-name'
		) );

		$cmb->add_group_field( $group_id, array(
			'name'       => __( 'Required', 'cmb2' ),
			'id'         => 'required',
			'type'       => 'checkbox'
		) );

		$cmb->add_group_field( $group_id, array(
			'name'       => __( 'Prompt', 'cmb2' ),
			'id'         => 'prompt',
			'type'       => 'wysiwyg'
		) );

		$cmb->add_group_field( $group_id, array(
			'name'       => __( 'Percentage weight', 'cmb2' ),
			'id'         => 'weight',
			'type'       => 'text_small'
		) );

		$cmb->add_group_field( $group_id, array(
			'name'       => 'Slug',
			'id'         => 'slug',
			'type'       => 'hidden',
			'desc'       => 'generated automatically when you save document type, must be unique within document type',
			'sanitization_cb'   => array( &$this, 'generate_slug' )
		) );

		$prefix = '_aam_combination_settings_';

		$cmb = new_cmb2_box( array(
			'id'            => $prefix . 'metabox',
			'title'         => __( 'Application Requirement settings', 'cmb2' ),
			'object_types'  => array( 'combination', ),
			'context'       => 'side',
			'priority'      => 'low',
			'show_names'    => true
		) );

		$cmb->add_field( array(
			'name' => __('Make prompt posts'),
			'desc' => 'for each document type make new post (optional)',
			'id'   => $prefix . 'make_posts',
			'type' => 'checkbox',
			'default' => true
		) );
	}

	public function set_application_cf() {
		$user_id = get_current_user_id();

		//get users
		$args = array(
			'role'          => 'Client'
		);

        if (!current_user_can( 'manage_options' )) {
            $args['meta_key'] = 'aam_assigned_consultant';
            $args['meta_compare'] = '=';
            $args['meta_value'] = $user_id;
        }

		$query = get_users( $args );

		$users = array();

		foreach($query as $user) {
			$users[$user->ID] = $user->display_name;
		}

		//get combinations
		$args = array(
			'post_type' => 'combination'
		);

		$query = new WP_Query($args);

		$combinations = array();

		foreach($query->get_posts() as $combination) {
			$combinations[$combination->ID] = $combination->post_title;
		}

		$prefix = '_aam_application_';

		$cmb = new_cmb2_box( array(
			'id'            => $prefix . 'metabox',
			'title'         => __( 'Application data', 'cmb2' ),
			'object_types'  => array( 'application', ),
			'context'       => 'normal',
			'priority'      => 'high',
			'show_names'    => true
		) );

		$cmb->add_field( array(
			'name'             => __( 'Client', 'cmb2' ),
			'desc'             => __( 'select client', 'cmb2' ),
			'id'               => $prefix . 'client',
			'type'             => 'select',
			'show_option_none' => true,
			'options'          => $users,
		) );

		/*$cmb->add_field( array(
			'name'             => __( 'BlackOut Days from', 'cmb2' ),
			'id'               => $prefix . 'bo_days_from',
			'type'             => 'text_date',
		) );

		$cmb->add_field( array(
			'name'             => __( 'BlackOut Days to', 'cmb2' ),
			'id'               => $prefix . 'bo_days_to',
			'type'             => 'text_date',
		) );*/

		$cmb->add_field( array(
			'name'             => __( 'Combination', 'cmb2' ),
			'id'               => $prefix . 'combination',
			'type'             => 'combination'
		) );
	}

	/*public function set_step_cf() {
		$steps = array();

		$args = array(
			'post_type' => 'step'
		);

		if($_GET['post']) {
			$args['post__not_in'] = array($_GET['post']);
		}

		$query = new WP_Query($args);

		if($query->have_posts()) {
			while($query->have_posts()) {
				$query->the_post();

				$steps[get_the_ID()] = get_the_title();
			}
		}

		$prefix = '_aam_step_';

		$cmb = new_cmb2_box( array(
			'id'            => $prefix . 'metabox',
			'title'         => __( 'Step data', 'cmb2' ),
			'object_types'  => array( 'step', ),
			'context'       => 'normal',
			'priority'      => 'high',
			'show_names'    => true
		) );

		$cmb->add_field( array(
			'name'             => __( 'Order', 'cmb2' ),
			'desc'             => __( 'step order number', 'cmb2' ),
			'id'               => $prefix . 'order',
			'type'             => 'text'
		) );

		$cmb->add_field( array(
			'name'             => __( 'Days', 'cmb2' ),
			'desc'             => __( 'step duration', 'cmb2' ),
			'id'               => $prefix . 'days',
			'type'             => 'text'
		) );

		$cmb->add_field( array(
			'name'             => __( 'Maximum Days', 'cmb2' ),
			'desc'             => __( 'maximum step duration', 'cmb2' ),
			'id'               => $prefix . 'max_days',
			'type'             => 'text'
		) );

		$cmb->add_field( array(
			'name'             => __( 'Minimum Days', 'cmb2' ),
			'desc'             => __( 'minimum step duration', 'cmb2' ),
			'id'               => $prefix . 'min_days',
			'type'             => 'text'
		) );

		$cmb->add_field( array(
			'name'             => __( 'Parent', 'cmb2' ),
			'desc'             => __( 'select parent step', 'cmb2' ),
			'id'               => $prefix . 'parent',
			'type'             => 'select',
			'show_option_none' => true,
			'options'          => $steps,
		) );

		$cmb->add_field( array(
			'name'             => __( 'Owner', 'cmb2' ),
			'id'               => $prefix . 'owner',
			'type'             => 'select',
			'show_option_none' => false,
			'options'          => array(
				'client' => 'Client',
				'consultant' => 'Consultant'
			)
		) );
	}*/

	/**
	 * Document type custom fields
	 */
	public function set_document_type_cf() {
		$prefix = '_aam_type_';

		$cmb = new_cmb2_box( array(
			'id'            => $prefix . 'metabox',
			'title'         => __( 'Document type steps', 'cmb2' ),
			'object_types'  => array( 'type', ),
			'context'       => 'normal',
			'priority'      => 'high',
			'show_names'    => true
		) );

        $cmb->add_field( array(
            'name'             => __( 'Send prompts', 'cmb2' ),
            'id'               => $prefix . 'send',
            'type'             => 'checkbox',
            'desc'              => 'Send all prompts by email to client on application creation'
        ) );

		$group_id = $cmb->add_field( array(
			'id'          => $prefix . 'steps',
			'type'        => 'group',
			'options'     => array(
				'group_title'   => __( 'Step {#}', 'cmb2' ),
				'add_button'    => __( 'Add Another Step', 'cmb2' ),
				'remove_button' => __( 'Remove Step', 'cmb2' ),
				'sortable'      => true
			),
		) );

		$cmb->add_group_field( $group_id, array(
			'name'       => __( 'Step name', 'cmb2' ),
			'id'         => 'name',
			'type'       => 'text',
		) );

		$cmb->add_group_field( $group_id, array(
			'name'       => __( 'Days', 'cmb2' ),
			'id'         => 'days',
			'type'       => 'text_small',
		) );

		$cmb->add_group_field( $group_id, array(
			'name'       => __( 'Max Days', 'cmb2' ),
			'id'         => 'max_days',
			'type'       => 'text_small',
		) );

		$cmb->add_group_field( $group_id, array(
			'name'       => __( 'Min Days', 'cmb2' ),
			'id'         => 'min_days',
			'type'       => 'text_small',
		) );

		$cmb->add_group_field( $group_id, array(
			'name'       => __( 'Percentage weight', 'cmb2' ),
			'id'         => 'weight',
			'type'       => 'text_small',
			'desc'      =>  'step percentage weight'
		) );

        $cmb->add_group_field( $group_id, array(
            'name'       => __( 'Depends on', 'cmb2' ),
            'id'         => 'parent',
            'type'       => 'text_small',
            'desc'      =>  'step # this step depends on'
        ) );

		$cmb->add_group_field( $group_id, array(
			'name'       => 'Slug',
			'id'         => 'slug',
			'type'       => 'hidden',
			'desc'       => 'generated automatically when you save document type, must be unique within document type',
			'sanitization_cb'   => array( &$this, 'generate_slug' )
		) );
	}

	public function set_document_cf() {
		$prefix = '_aam_document_';

		$cmb = new_cmb2_box( array(
			'id'            => $prefix . 'metabox',
			'title'         => __( 'Document data', 'cmb2' ),
			'object_types'  => array( 'document', ),
			'context'       => 'normal',
			'priority'      => 'high',
			'show_names'    => true
		) );

		$cmb->add_field( array(
			'name' => __( 'Document', 'cmb2' ),
			'id'   => $prefix . 'document',
			'type' => 'file'
		) );

		$cmb->add_field( array(
			'name' => __( 'Final?', 'cmb2' ),
			'desc' => __( 'is this document final', 'cmb2' ),
			'id'   => $prefix . 'final',
			'type' => 'checkbox'
		) );

		$cmb->add_field( array(
			'id'   => $prefix . 'application_id',
			'type' => 'hidden',
			'default'   =>  isset($_GET['application_id']) ? $_GET['application_id'] : ''
		) );

		$cmb->add_field( array(
			'id'   => $prefix . 'step_id',
			'type' => 'hidden',
			'default'   =>  isset($_GET['step_id']) ? $_GET['step_id'] : ''
		) );

		$cmb->add_field( array(
			'id'   => $prefix . 'document_id',
			'type' => 'hidden',
			'default'   =>  isset($_GET['document_id']) ? $_GET['document_id'] : ''
		) );

	}

	/**
	 * @param $value
	 * @param $args
	 *
	 * @return string
	 */
	public function generate_slug( $value, $args ) {

		if($value == '') {
			return md5( $args['id'] . time() . rand(0, 1000) );
		}

		return $value;

	}

}

$custom_fields = new Admission_App_Manager_Custom_Fields();