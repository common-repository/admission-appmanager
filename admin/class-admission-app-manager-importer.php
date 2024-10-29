<?php

use League\Csv\Reader;

class Admission_App_Manager_Importer {

    private $notices = array();
    private $errors = array();

    public function __construct() {
        $this->router();
    }

    private function router() {
        if(isset($_POST['action']) && $_POST['action'] == 'aam_import_data') {
            $this->import();
        } else {
            $this->render_import_form();
        }
    }

    private function render_import_form() {
        ?>
        <div class="wrap">
            <h2>Import Admission AppManager data</h2>
            <div class="narrow">

                <?php if(count($this->errors) > 0) : foreach($this->errors as $error) : ?>
                    <div class="error"><p><?php echo $error; ?></p></div>
                <?php endforeach; endif; ?>

                <?php if(count($this->notices) > 0) : foreach($this->notices as $notice) : ?>
                    <div class="updated"><p><?php echo $notice; ?></p></div>
                <?php endforeach; endif; ?>

                <p>Please select .csv file to import data</p>
                <form enctype="multipart/form-data" id="import-upload-form" method="post" class="wp-upload-form" action="">
                    <?php wp_nonce_field('import-data'); ?>
                    <p>
                        <label for="type">Data type</label>
                        <select name="type" id="type">
                            <option value="">Select data type</option>
                            <option value="school">Schools</option>
                            <option value="document_type">Document types</option>
                        </select>
                    </p>
                    <p>
                        Examples:<br>
                        <a href="<?php echo plugin_dir_url(__FILE__); ?>/examples/documents.csv">Documents</a><br>
                        <a href="<?php echo plugin_dir_url(__FILE__); ?>/examples/schools2.csv">Schools</a>
                    </p>
                    <p>
                        <label for="upload">Choose a file from your computer:</label>
                        <input type="file" id="upload" name="import" size="25">
                        <input type="hidden" name="action" value="aam_import_data">
                        <input type="hidden" name="max_file_size" value="107374182400">
                    </p>
                    <p class="submit">
                        <button type="submit" class="button">Upload file and import</button>
                    </p>
                </form>
            </div>
        </div>
    <?php
    }

    private function import() {
        if($_FILES['import']['size'] == 0 || !$_POST['type']) {
            $this->errors[] = 'Missing data type or file!';

            $this->render_import_form();
        } else if($_FILES['import']['type'] !== 'text/csv') {
            $this->errors[] = 'File type must be CSV';

            $this->render_import_form();
        } else {
            $upload_dir = wp_upload_dir();
            $upload_file = $upload_dir['path'] . '/' . $_FILES['import']['name'];

            if(move_uploaded_file($_FILES['import']['tmp_name'], $upload_file)) {
                $reader = Reader::createFromPath($upload_file);

                $reader->setFlags(SplFileObject::READ_AHEAD|SplFileObject::SKIP_EMPTY);
                $reader->setDelimiter(';');

                $data = $reader->fetchAssoc();

                switch ($_POST['type']) {
                    case 'school':
                        $this->import_schools($data);
                        break;

                    case 'document_type':
                        $this->import_document_types($data);
                        break;
                }
            }

            $this->notices[] = 'Data imported';

            $this->render_import_form();
        }
    }

    private function import_schools($data) {
        $programs = array();

        foreach($data as $row) {
            $school = get_page_by_title( $row['name'], 'OBJECT', 'school' );

            if(!$school) {
                $school_data = array(
                    'post_author' => get_current_user_id(),
                    'post_title' => wp_strip_all_tags($row['name']),
                    'post_type' => 'school',
                    'post_content' => '',
                    'post_status' => 'publish'
                );

                $school_id = wp_insert_post($school_data);
            } else {
                $school_id = $school->ID;
            }

            $region = get_term_by('name', $row['region'], 'region', 'ARRAY_A');

            if(!$region) {
                $region = wp_insert_term($row['region'], 'region');
            }

            update_post_meta($school_id, '_aam_school_region', $region['term_id']);
            wp_set_object_terms( $school_id, $region['term_id'], 'region');

            $country = get_term_by('name', $row['country'], 'country', 'ARRAY_A');

            if(!$country) {
                $country = wp_insert_term($row['country'], 'country');
            }

            update_post_meta($school_id, '_aam_school_country', $country['term_id']);
            wp_set_object_terms( $school_id, $country['term_id'], 'country');

            //$timezone = get_term_by('name', $row['timezone'], 'timezone', 'ARRAY_A');

            //if(!$timezone) {
            //    $timezone = wp_insert_term($row['timezone'], 'timezone');
            //}

            update_post_meta($school_id, '_aam_school_timezone', $row['timezone']);
            //wp_set_object_terms( $school_id, $timezone['term_id'], 'timezone');

            if(!isset($programs[$school_id])) {
                $programs[$school_id] = array();
            }

            $programs[$school_id][] = array(
                'name'  => $row['program_name'],
                'url'   => $row['program_url']
            );
        }

        if($programs) {
            foreach($programs as $school_id => $program_items) {
                update_post_meta($school_id, '_aam_school_programs_group', $program_items);
            }
        }
    }

    private function import_document_types($data) {
        $steps = array();

        foreach($data as $row) {
            $document = get_page_by_title( $row['name'], 'OBJECT', 'type' );

            if(!$document) {
                $document_data = array(
                    'post_author' => get_current_user_id(),
                    'post_title' => wp_strip_all_tags($row['name']),
                    'post_type' => 'type',
                    'post_content' => '',
                    'post_status' => 'publish'
                );

                $document_id = wp_insert_post($document_data);
            } else {
                $document_id = $document->ID;
            }

            if(!isset($steps[$document_id])) {
                $steps[$document_id] = array();
            }

            $steps[$document_id][] = array(
                'name'      => $row['step_name'],
                'days'      => $row['step_days'],
                'max_days'  => $row['step_max'],
                'min_days'  => $row['step_min'],
                'weight'    => $row['step_weight'],
                'parent'    => $row['step_parent'],
                'slug'      => md5( $document_id . time() . rand(0, 1000) . $row['step_name'] )
            );
        }

        if($steps) {
            foreach($steps as $document_id => $step_items) {
                update_post_meta($document_id, '_aam_type_steps', $step_items);
            }
        }
    }
}

function aam_init_importer()
{
    $importer = new Admission_App_Manager_Importer();
}