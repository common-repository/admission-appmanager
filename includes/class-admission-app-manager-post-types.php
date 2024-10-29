<?php

use Carbon\Carbon;

class Admission_App_Manager_Post_Types
{

    private $steps;

    public function __construct()
    {
        add_action('init', array(&$this, 'init_school'));
        add_action('init', array(&$this, 'init_combination'));
        add_action('init', array(&$this, 'init_application'));
        add_action('init', array(&$this, 'init_type'));
        add_action('init', array(&$this, 'init_document'));

        add_action('init', array(&$this, 'remove_post_type_support'));

        add_filter('wp_insert_post_data', array(&$this, 'modify_post_title'), 99, 2);

        add_filter('post_row_actions', array(&$this, 'edit_action_links'), 10, 2);

        add_action('admin_notices', array(&$this, 'select_application_notice'));

        add_action('admin_init', array(&$this, 'add_role_caps'), 999);

        add_filter('parse_query', array(&$this, 'applications_table_filter'));

        add_action('admin_menu', array(&$this, 'disable_new_documents_buttons'));

        add_filter('redirect_post_location', array(&$this, 'publish_document_redirect'), 20, 2);

        add_action('save_post', array(&$this, 'send_notification'), 99, 3);

        add_action('save_post', array(&$this, 'edit_application'), 40, 3);

        add_action('save_post', array(&$this, 'edit_combination'), 40, 3);

        add_action('admin_action_duplicate_post_as_draft', array(&$this, 'duplicate_post_as_draft'));
        add_filter('post_row_actions', array(&$this, 'combinations_duplicate_post_link'), 10, 2);

        add_filter('manage_application_posts_columns', array(&$this, 'application_columns_head'));
        add_action('manage_application_posts_custom_column', array(&$this, 'application_columns_content'), 10, 2);
        //add_filter('parse_query', array(&$this, 'application_posts_filter'));
    }

    /**
     * Setup school custom post type
     */
    public function init_school()
    {
        $labels = array(
            'name' => _x('Schools', 'post type general name', 'admission-app-manager'),
            'singular_name' => _x('School', 'post type singular name', 'admission-app-manager'),
            'menu_name' => _x('Schools', 'admin menu', 'admission-app-manager'),
            'name_admin_bar' => _x('School', 'add new on admin bar', 'admission-app-manager'),
            'add_new' => _x('Add New', 'school', 'admission-app-manager'),
            'add_new_item' => __('Add New School', 'admission-app-manager'),
            'new_item' => __('New School', 'admission-app-manager'),
            'edit_item' => __('Edit School', 'admission-app-manager'),
            'view_item' => __('View School', 'admission-app-manager'),
            'all_items' => __('All Schools', 'admission-app-manager'),
            'search_items' => __('Search Schools', 'admission-app-manager'),
            'parent_item_colon' => __('Parent Schools:', 'admission-app-manager'),
            'not_found' => __('No Schools found.', 'admission-app-manager'),
            'not_found_in_trash' => __('No Schools found in Trash.', 'admission-app-manager')
        );

        $args = array(
            'labels' => $labels,
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'query_var' => true,
            'rewrite' => array('slug' => 'school'),
            'capability_type' => 'post',
            'has_archive' => true,
            'hierarchical' => false,
            'supports' => array('title'),
            'menu_icon' => 'dashicons-building'
        );

        register_post_type('school', $args);
    }

    /**
     * Setup application custom post type
     */
    public function init_combination()
    {
        $labels = array(
            'name' => _x('Application Requirements', 'post type general name', 'admission-app-manager'),
            'singular_name' => _x('Application Requirement', 'post type singular name', 'admission-app-manager'),
            'menu_name' => _x('Application Requirements', 'admin menu', 'admission-app-manager'),
            'name_admin_bar' => _x('Application Requirement', 'add new on admin bar', 'admission-app-manager'),
            'add_new' => _x('Add New', 'Application Requirement', 'admission-app-manager'),
            'add_new_item' => __('Add New Application Requirement', 'admission-app-manager'),
            'new_item' => __('New Application Requirement', 'admission-app-manager'),
            'edit_item' => __('Edit Application Requirement', 'admission-app-manager'),
            'view_item' => __('View Application Requirement', 'admission-app-manager'),
            'all_items' => __('All Application Requirements', 'admission-app-manager'),
            'search_items' => __('Search Application Requirements', 'admission-app-manager'),
            'parent_item_colon' => __('Parent Application Requirements:', 'admission-app-manager'),
            'not_found' => __('No Application Requirements found.', 'admission-app-manager'),
            'not_found_in_trash' => __('No Application Requirements found in Trash.', 'admission-app-manager')
        );

        $args = array(
            'labels' => $labels,
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'query_var' => true,
            'rewrite' => array('slug' => 'combination'),
            'capability_type' => 'post',
            'has_archive' => true,
            'hierarchical' => false,
            'supports' => array('title'),
            'menu_icon' => 'dashicons-networking'
        );

        register_post_type('combination', $args);
    }

    public function init_application()
    {
        $labels = array(
            'name' => _x('Applications', 'post type general name', 'admission-app-manager'),
            'singular_name' => _x('Application', 'post type singular name', 'admission-app-manager'),
            'menu_name' => _x('Applications', 'admin menu', 'admission-app-manager'),
            'name_admin_bar' => _x('Application', 'add new on admin bar', 'admission-app-manager'),
            'add_new' => _x('Add New', 'Application', 'admission-app-manager'),
            'add_new_item' => __('Add New Application', 'admission-app-manager'),
            'new_item' => __('New Application', 'admission-app-manager'),
            'edit_item' => __('Edit Application', 'admission-app-manager'),
            'view_item' => __('View Application', 'admission-app-manager'),
            'all_items' => __('All Applications', 'admission-app-manager'),
            'search_items' => __('Search Applications', 'admission-app-manager'),
            'parent_item_colon' => __('Parent Applications:', 'admission-app-manager'),
            'not_found' => __('No Applications found.', 'admission-app-manager'),
            'not_found_in_trash' => __('No Applications found in Trash.', 'admission-app-manager')
        );

        $args = array(
            'labels' => $labels,
            'public' => false,
            'publicly_queryable' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'query_var' => true,
            'rewrite' => array('slug' => 'application'),
            'capability_type' => array('aam_application', 'aam_applications'),
            'map_meta_cap' => true,
            'has_archive' => false,
            'hierarchical' => false,
            'supports' => array('title'),
            'menu_icon' => 'dashicons-clipboard'
        );

        register_post_type('application', $args);
    }

    public function init_step()
    {
        $labels = array(
            'name' => _x('Steps', 'post type general name', 'admission-app-manager'),
            'singular_name' => _x('Step', 'post type singular name', 'admission-app-manager'),
            'menu_name' => _x('Steps', 'admin menu', 'admission-app-manager'),
            'name_admin_bar' => _x('Step', 'add new on admin bar', 'admission-app-manager'),
            'add_new' => _x('Add New', 'Step', 'admission-app-manager'),
            'add_new_item' => __('Add New Step', 'admission-app-manager'),
            'new_item' => __('New Step', 'admission-app-manager'),
            'edit_item' => __('Edit Step', 'admission-app-manager'),
            'view_item' => __('View Step', 'admission-app-manager'),
            'all_items' => __('All Steps', 'admission-app-manager'),
            'search_items' => __('Search Steps', 'admission-app-manager'),
            'parent_item_colon' => __('Parent Steps:', 'admission-app-manager'),
            'not_found' => __('No Steps found.', 'admission-app-manager'),
            'not_found_in_trash' => __('No Steps found in Trash.', 'admission-app-manager')
        );

        $args = array(
            'labels' => $labels,
            'public' => false,
            'publicly_queryable' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'query_var' => true,
            'rewrite' => array('slug' => 'step'),
            'capability_type' => 'post',
            'has_archive' => false,
            'hierarchical' => false,
            'supports' => array('title'),
            'menu_icon' => 'dashicons-performance'
        );

        register_post_type('step', $args);
    }

    public function init_type()
    {
        $labels = array(
            'name' => _x('Document Types', 'post type general name', 'admission-app-manager'),
            'singular_name' => _x('Document Type', 'post type singular name', 'admission-app-manager'),
            'menu_name' => _x('Document Types', 'admin menu', 'admission-app-manager'),
            'name_admin_bar' => _x('Document Type', 'add new on admin bar', 'admission-app-manager'),
            'add_new' => _x('Add New', 'Document Type', 'admission-app-manager'),
            'add_new_item' => __('Add New Document Type', 'admission-app-manager'),
            'new_item' => __('New Document Type', 'admission-app-manager'),
            'edit_item' => __('Edit Document Type', 'admission-app-manager'),
            'view_item' => __('View Document Type', 'admission-app-manager'),
            'all_items' => __('All Document Types', 'admission-app-manager'),
            'search_items' => __('Search Document Types', 'admission-app-manager'),
            'parent_item_colon' => __('Parent Document Types:', 'admission-app-manager'),
            'not_found' => __('No Document Types found.', 'admission-app-manager'),
            'not_found_in_trash' => __('No Document Types found in Trash.', 'admission-app-manager')
        );

        $args = array(
            'labels' => $labels,
            'public' => false,
            'publicly_queryable' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'query_var' => true,
            'rewrite' => array('slug' => 'type'),
            'capability_type' => 'post',
            'has_archive' => false,
            'hierarchical' => false,
            'supports' => array('title'),
            'menu_icon' => 'dashicons-media-text'
        );

        register_post_type('type', $args);
    }

    /**
     * Init document custom post type
     */
    public function init_document()
    {
        $labels = array(
            'name' => _x('Documents', 'post type general name', 'admission-app-manager'),
            'singular_name' => _x('Document', 'post type singular name', 'admission-app-manager'),
            'menu_name' => _x('Documents', 'admin menu', 'admission-app-manager'),
            'name_admin_bar' => _x('Document', 'add new on admin bar', 'admission-app-manager'),
            'add_new' => _x('Add New', 'Document', 'admission-app-manager'),
            'add_new_item' => __('Add New Document', 'admission-app-manager'),
            'new_item' => __('New Document', 'admission-app-manager'),
            'edit_item' => __('Edit Document', 'admission-app-manager'),
            'view_item' => __('View Document', 'admission-app-manager'),
            'all_items' => __('All Documents', 'admission-app-manager'),
            'search_items' => __('Search Documents', 'admission-app-manager'),
            'parent_item_colon' => __('Parent Documents:', 'admission-app-manager'),
            'not_found' => __('No Documents found.', 'admission-app-manager'),
            'not_found_in_trash' => __('No Documents found in Trash.', 'admission-app-manager')
        );

        $args = array(
            'labels' => $labels,
            'public' => false,
            'publicly_queryable' => false,
            'show_ui' => true,
            'show_in_menu' => false,
            'query_var' => true,
            'rewrite' => array('slug' => 'document'),
            'capability_type' => array('aam_application', 'aam_applications'),
            'has_archive' => false,
            'hierarchical' => false,
            'supports' => array('title')
        );

        register_post_type('document', $args);
    }

    /**
     * Remove "Add New" buttons on document post type
     */
    function disable_new_documents_buttons()
    {
        global $submenu;

        unset($submenu['edit.php?post_type=document'][10]);

        if (
            (isset($_GET['post_type']) && $_GET['post_type'] == 'document') ||
            (isset($_GET['post']) && get_post_type($_GET['post']) == 'document')
        ) {
            echo '<style type="text/css">
    		#favorite-actions, .add-new-h2, .tablenav { display:none; }
    		</style>';
        }
    }

    /**
     * Redirect to documents on document save
     *
     * @param $location
     * @param $post_id
     *
     * @return string|void
     */
    function publish_document_redirect($location, $post_id)
    {
        $post_type = get_post_type($post_id);

        if ($post_type == 'document') {
            $document_id = get_post_meta($post_id, '_aam_document_document_id', true);
            $step_id = get_post_meta($post_id, '_aam_document_step_id', true);
            $application_id = get_post_meta($post_id, '_aam_document_application_id', true);

            $location = admin_url('admin.php?page=aam&application_id=' . $application_id . '&document_id=' . $document_id . '&step_id=' . $step_id);
        }

        return $location;
    }

    /**
     * Send notification on document save
     *
     * @param $post_id
     */
    public function send_notification($post_id, $post, $update)
    {
        if (is_admin()) send_notification($post_id);
    }

    /**
     * When saving application also update applications helper table
     *
     * @param $post_id
     */
    public function edit_application($post_id, $post, $update)
    {
        global $wpdb;

        $post_type = get_post_type($post_id);

        if ('application' == $post_type) {

            $prompt_docs = array();

            $app_table = $wpdb->prefix . 'aam_applications';

            $app_combination = get_post_meta($post_id, '_aam_application_combination', true);

            if ($app_combination && isset($app_combination['combination'])) {

                $documents = get_post_meta($app_combination['combination'], '_aam_combination_documents', true);

                if ($documents) {
                    foreach ($documents as $dk => $document) {

                        if (isset($document['type'])) {
                            $steps = get_post_meta($document['type'], '_aam_type_steps', true);

                            if ($steps) {
                                foreach ($steps as $sk => $step) {
                                    if ($step['name']) {
                                        $item_id = $wpdb->get_var(
                                            $wpdb->prepare(
                                                "SELECT id FROM $app_table WHERE application_id = %d AND document_id = %s AND step_id = %s",
                                                $post_id,
                                                $document['slug'],
                                                $step['slug']
                                            )
                                        );

                                        if (!$item_id) {
                                            $wpdb->insert(
                                                $app_table,
                                                array(
                                                    'name' => $step['name'],
                                                    'application_id' => $post_id,
                                                    'document_id' => $document['slug'],
                                                    'document_type' => (int)$document['type'],
                                                    'step_id' => $step['slug'],
                                                    'prompt' => $document['prompt'],
                                                    'required' => $document['required'] == 'on',
                                                    'document_weight' => $document['weight'],
                                                    'step_weight' => $step['weight'],
                                                    'step_days' => $step['days'],
                                                    'step_max_days' => $step['max_days'],
                                                    'step_min_days' => $step['min_days'],
                                                    'sort' => $dk . '.' . $sk,
                                                    'parent_id' => (isset($step['parent']) && $step['parent'] != 0 ? $steps[$step['parent'] - 1]['slug'] : NULL)
                                                )
                                            );
                                        } else {
                                            $wpdb->update(
                                                $app_table,
                                                array(
                                                    'name' => $step['name'],
                                                    'prompt' => $document['prompt'],
                                                    'required' => (isset($document['required']) && $document['required'] == 'on'),
                                                    'document_weight' => $document['weight'],
                                                    'step_weight' => $step['weight'],
                                                    'step_days' => $step['days'],
                                                    'step_max_days' => $step['max_days'],
                                                    'step_min_days' => $step['min_days'],
                                                    'sort' => $dk . '.' . $sk,
                                                    'parent_id' => ((isset($step['parent']) && $step['parent']) != 0 ? $steps[$step['parent'] - 1]['slug'] : NULL)
                                                ),
                                                array(
                                                    'application_id' => $post_id,
                                                    'document_id' => $document['slug'],
                                                    'document_type' => (int)$document['type'],
                                                    'step_id' => $step['slug'],
                                                )
                                            );
                                        }
                                    }
                                }
                            }

                            //check weather we send prompts for this document type to client on application save
                            $send_prompt = get_post_meta($document['type'], '_aam_type_send', true);

                            if ($send_prompt == 'on') {
                                if (!isset($prompts[$document['type']])) {
                                    $prompt_docs[$document['type']] = array();
                                }

                                $prompt_docs[$document['type']][] = $document['prompt'];
                            }
                        }
                    }

                    $helper = new ApplicationHelper($post_id);

                    $helper->calc_deadlines(true);
                }
            }
        }
    }

    /**
     * Create auto posts with list of prompts for each document type
     *
     * @param $post_id
     * @param $post
     * @param $update
     */
    public function edit_combination($post_id, $post, $update)
    {
        if ($post->post_type == 'combination') {
	        if ( get_post_meta( $post_id, '_aam_combination_settings_make_posts', true ) === 'on' ) {
		        $documents = get_post_meta( $post_id, '_aam_combination_documents', true );
		        $school_id = get_post_meta( $post_id, '_aam_combination_school', true );
		        $program   = get_post_meta( $post_id, '_aam_combination_program', true );
		        $intakes = wp_get_post_terms( $post_id, 'intake');

		        $school = get_post( $school_id );
		        $intake = get_term( $intakes[0], 'intake' );

		        $sorted = array();

		        if ( $documents ) {
			        foreach ( $documents as $document ) {
				        if ( ! isset( $sorted[ $document['type'] ] ) ) {
					        $sorted[ $document['type'] ] = array();
				        }

				        $sorted[ $document['type'] ][] = $document;
			        }

			        foreach ( $sorted as $type => $items ) {
				        $document_type = get_post( $type );

				        $title   = $school->post_title . ' ' . $program . ' ' . $intake->name . ' ' . $document_type->post_title;
				        $author  = $post->post_author;
				        $content = '';

				        if ( $items ) {
					        $content .= '<ul>';

					        foreach ( $items as $item ) {
						        $content .= '<li>' . $item['prompt'] . ' (' . ( $item['required'] == 'on' ? 'required' : 'optional' ) . ')</li>';
					        }

					        $content .= '</ul>';
				        }

				        if ( ! $update ) {
					        $this->create_auto_post( $title, $author, $content, $post_id, $type );
				        } else {
					        $auto_post = get_posts( array(
						        'post_type'   => 'post',
						        'post_status' => 'publish',
						        'meta_query'  => array(
							        array(
								        'key'     => '_aam_auto_combination',
								        'value'   => $post_id,
								        'compare' => '=',
							        ),
							        array(
								        'key'     => '_aam_auto_document',
								        'value'   => $type,
								        'compare' => '='
							        )
						        )
					        ) );

					        if ( $auto_post && count( $auto_post ) > 0 ) {
						        $this->update_auto_post( $auto_post[0]->ID, $title, $author, $content );
					        } else {
						        $this->create_auto_post( $title, $author, $content, $post_id, $type );
					        }
				        }
			        }
		        }
	        }
        }
    }

    /**
     * Create auto post
     *
     * @param $title
     * @param $author
     * @param $content
     * @param $combination_id
     * @param $type_id
     */
    private function create_auto_post($title, $author, $content, $combination_id, $type_id)
    {
        // Create post object
        $my_post = array(
            'post_title' => $title,
            'post_content' => $content,
            'post_status' => 'publish',
            'post_author' => $author
        );

        // Insert the post into the database
        if ($post_id = wp_insert_post($my_post)) {
            update_post_meta($post_id, '_aam_auto_combination', $combination_id);
            update_post_meta($post_id, '_aam_auto_document', $type_id);
        }
    }

    /**
     * Update auto post
     *
     * @param $post_id
     * @param $title
     * @param $author
     * @param $content
     */
    private function update_auto_post($post_id, $title, $author, $content)
    {
        $my_post = array(
            'ID' => $post_id,
            'post_title' => $title,
            'post_content' => $content,
            'post_status' => 'publish',
            'post_author' => $author
        );

        // Insert the post into the database
        wp_insert_post($my_post);
    }

    /**
     * Create new capabilities for consultant so he can reach applications
     * Apply same capabilities to admin
     */
    public function add_role_caps()
    {

        // Add the roles you'd like to administer the custom post types
        $roles = array('consultant', 'administrator');

        // Loop through each role and assign capabilities
        foreach ($roles as $the_role) {

            $role = get_role($the_role);

            //$role->add_cap( 'read' );
            $role->add_cap('read_aam_application');
            $role->add_cap('read_private_aam_applications');
            $role->add_cap('edit_aam_application');
            $role->add_cap('edit_aam_applications');
            $role->add_cap('edit_others_aam_applications');
            $role->add_cap('edit_published_aam_applications');
            $role->add_cap('publish_aam_applications');
            $role->add_cap('delete_others_aam_applications');
            $role->add_cap('delete_private_aam_applications');
            $role->add_cap('delete_published_aam_applications');

        }
    }

    /**
     * Filter applications so only current users applications are displayed
     * if user is not admin
     *
     * @param $query
     */
    function applications_table_filter($query)
    {
        if (is_admin() AND $query->query['post_type'] == 'application' && !current_user_can('manage_options')) {
            $qv = &$query->query_vars;

            $qv['author'] = get_current_user_id();
        }
    }

    /**
     * @param $query
     */
    function documents_table_filter($query)
    {
        if (is_admin() AND $query->query['post_type'] == 'document') {
            $qv = &$query->query_vars;

            $qv['meta_query'] = array(
                array(
                    'key' => 'application_id',
                    'value' => $_GET['application_id'],
                    'compare' => '='
                ),
                array(
                    'key' => 'step_id',
                    'value' => $_GET['step_id'],
                    'compare' => '='
                ),
                array(
                    'key' => 'document_id',
                    'value' => $_GET['document_id'],
                    'compare' => '='
                )
            );
        }
    }

    /**
     * Remove some post type features
     */
    public function remove_post_type_support()
    {
        remove_post_type_support('combination', 'title');
        remove_post_type_support('application', 'title');
        //remove_post_type_support( 'document', 'title' );
    }

    /**
     * Auto generate post titles for combinations and applications
     *
     * @param $data
     * @param $postarr
     *
     * @return mixed
     */
    public function modify_post_title($data, $postarr)
    {
        if ($data['post_type'] == 'combination') {
            $school_title = '';
            $program_title = '';
            $intake_title = '';

            if (isset($postarr['ID'])) {
                if (isset($postarr['_aam_combination_school'])) {
                    $school = get_post($postarr['_aam_combination_school']);
                    $school_title = $school->post_title;
                }

                if (isset($postarr['_aam_combination_program'])) {
                    $program_title = $postarr['_aam_combination_program'];
                }

                if (isset($postarr['_aam_combination_intake'])) {
                    $intake = get_term_by('slug', $postarr['_aam_combination_intake'], 'intake');
                    $intake_title = $intake->name;
                }
            }

            $data['post_title'] = 'C' . $postarr['ID'] . ' - ' . $school_title . ' - ' . $program_title . ' - ' . $intake_title;
        }

        if ($data['post_type'] == 'application') {
            $data['post_title'] = 'Application #' . $postarr['ID'];
        }

        return $data;
    }

    /**
     * @param $actions
     * @param $object
     * @return mixed
     */
    public function edit_action_links($actions, $object)
    {
        //fb($actions);

        if (in_array($object->post_type, array('application'))) {
            unset($actions['view']);
            //unset($actions['trash']);
            unset($actions['inline hide-if-no-js']);
        }

        if ($object->post_type == 'application') {
            $actions['edit_badges'] = '<a class="app-dashboard" href="' . admin_url('admin.php?page=aam&application_id=' . $object->ID) . '" style="color:red;">' . __('Application dashboard', 'admission-app-manager') . '</a>';
        }

        return $actions;
    }

    /**
     * Display error notification if not application is selected in app dashboard
     */
    public function select_application_notice()
    {
        if (isset($_GET['display_notice_error']) && $_GET['display_notice_error'] == true) {
            $class = "error";
            $message = __("Please select application first", 'admission-app-manager');
            echo "<div class=\"$class\"> <p>$message</p></div>";
        }
    }

    /**
     * Created duplicate from post
     */
    public function duplicate_post_as_draft()
    {
        global $wpdb;
        if (!(isset($_GET['post']) || isset($_POST['post']) || (isset($_REQUEST['action']) && 'rd_duplicate_post_as_draft' == $_REQUEST['action']))) {
            wp_die('No post to duplicate has been supplied!');
        }

        /*
         * get the original post id
         */
        $post_id = (isset($_GET['post']) ? $_GET['post'] : $_POST['post']);
        /*
         * and all the original post data then
         */
        $post = get_post($post_id);

        /*
         * if you don't want current user to be the new post author,
         * then change next couple of lines to this: $new_post_author = $post->post_author;
         */
        $current_user = wp_get_current_user();
        $new_post_author = $current_user->ID;

        /*
         * if post data exists, create the post duplicate
         */
        if (isset($post) && $post != null) {

            /*
             * new post data array
             */
            $args = array(
                'comment_status' => $post->comment_status,
                'ping_status' => $post->ping_status,
                'post_author' => $new_post_author,
                'post_content' => $post->post_content,
                'post_excerpt' => $post->post_excerpt,
                'post_name' => $post->post_name,
                'post_parent' => $post->post_parent,
                'post_password' => $post->post_password,
                'post_status' => 'draft',
                'post_title' => $post->post_title,
                'post_type' => $post->post_type,
                'to_ping' => $post->to_ping,
                'menu_order' => $post->menu_order
            );

            /*
             * insert the post by wp_insert_post() function
             */
            $new_post_id = wp_insert_post($args);

            /*
             * get all current post terms ad set them to the new post draft
             */
            $taxonomies = get_object_taxonomies($post->post_type); // returns array of taxonomy names for post type, ex array("category", "post_tag");
            foreach ($taxonomies as $taxonomy) {
                $post_terms = wp_get_object_terms($post_id, $taxonomy, array('fields' => 'slugs'));
                wp_set_object_terms($new_post_id, $post_terms, $taxonomy, false);
            }

            /*
             * duplicate all post meta just in two SQL queries
             */
            $post_meta_infos = $wpdb->get_results("SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=$post_id");
            if (count($post_meta_infos) != 0) {
                $sql_query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) ";
                foreach ($post_meta_infos as $meta_info) {
                    $meta_key = $meta_info->meta_key;
                    $meta_value = addslashes($meta_info->meta_value);
                    $sql_query_sel[] = "SELECT $new_post_id, '$meta_key', '$meta_value'";
                }
                $sql_query .= implode(" UNION ALL ", $sql_query_sel);
                $wpdb->query($sql_query);
            }


            /*
             * finally, redirect to the edit post screen for the new draft
             */
            wp_redirect(admin_url('post.php?action=edit&post=' . $new_post_id));
            exit;
        } else {
            wp_die('Post creation failed, could not find original post: ' . $post_id);
        }
    }

    /**
     * Add duplicate link for combinations
     *
     * @param $actions
     * @param $post
     * @return mixed
     */
    function combinations_duplicate_post_link($actions, $post)
    {
        if ($post->post_type == 'combination' && current_user_can('edit_posts')) {
            $actions['duplicate'] = '<a href="admin.php?action=duplicate_post_as_draft&amp;post=' . $post->ID . '" title="Duplicate this item" rel="permalink">Duplicate</a>';
        }
        return $actions;
    }

    /**
     * Edit application post type listing columns
     * @param $defaults
     * @return mixed
     */
    public function application_columns_head($defaults)
    {
        unset($defaults['date']);
        unset($defaults['taxonomy-status']);

        $defaults['client'] = 'Client';
        $defaults['done'] = 'Done';
        $defaults['date'] = 'Date';

        return $defaults;
    }

    /**
     * Edit application post type listing columns content
     * @param $column_name
     * @param $post_ID
     */
    public function application_columns_content($column_name, $post_ID)
    {
        if ($column_name == 'client') {
            $client_ID = get_post_meta($post_ID, '_aam_application_client', true);
            $client = get_userdata($client_ID);

            echo $client->display_name;
        }

        if ($column_name == 'done') {
            $done = get_post_meta($post_ID, '_aam_total_completion', true);

            echo '<div class="progress-bar" data-completion="' . $done . '"><span></span></div>';
        }
    }
}

$post_types = new Admission_App_Manager_Post_Types;