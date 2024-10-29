<?php

/**
 * @param $post_id
 * @return mixed
 */
function send_notification( $post_id ) {
    global $wpdb;

    $table_name = $wpdb->prefix . 'aam_applications';
    $owner = 0;

    $post_type = get_post_type( $post_id );
    $post = get_post( $post_id );

    if($post_type == 'document' && $post->post_status == 'publish') {

        //$document = get_post($post_id);
        $application_id = get_post_meta( $post_id, '_aam_document_application_id', true );
        $document_id = get_post_meta( $post_id, '_aam_document_document_id', true );
        $step_id = get_post_meta( $post_id, '_aam_document_step_id', true );

        $application = get_post( $application_id );

        $consultant = get_user_by('id', $application->post_author);
        $client_id = get_post_meta($application_id, '_aam_application_client', true);
        $client = get_user_by('id', $client_id);

        $document_name = get_post_meta( $post_id, '_aam_document_document', true );

        $uploader_template = nl2br(get_user_meta($application->post_author, 'aam_uploader_email_template', true));
        $other_template = nl2br(get_user_meta($application->post_author, 'aam_other_email_template', true));

        $subject = 'Application #' . $application->ID . ' - new document uploaded';

        $headers = array();
        $headers[] = 'Content-Type: text/html; charset=UTF-8';
        $headers[] = 'From: Test <tadic.mario+test@gmail.com>';

        $uploader_name = '';
        $other_name = '';
        $uploader = null;
        $other = null;

        //if client is uploader
        if($post->post_author == $client_id) {
            $headers[] = 'From: ' . $client->display_name . ' <' . $client->user_email . '>';

            $uploader_name = $client->display_name;
            $other_name = $consultant->display_name;
            $uploader = $client;
            $other = $consultant;

            $owner = $consultant->ID;
        }
        //if consultant is uploader
        else if($post->post_author == $application->post_author) {

            $from = get_user_meta($consultant->ID, 'aam_from', true);
            $bcc = get_user_meta($consultant->ID, 'aam_bcc', true);

            $headers[] = 'From: ' . $consultant->display_name . ' <' . $from . '>';
            $headers[] = 'Bcc: <' . $bcc . '>';

            $uploader_name = $consultant->display_name;
            $other_name = $client->display_name;
            $uploader = $consultant;
            $other = $client;

            $owner = $client->ID;
        }

        //replace placeholders
        $uploader_template = str_replace('%name%', $uploader_name, $uploader_template);
        $uploader_template = str_replace('%file%', $document_name, $uploader_template);

        wp_mail( $uploader->user_email, $subject, $uploader_template, $headers );

        //replace placeholders
        $other_template = str_replace('%name%', $uploader_name, $other_template);
        $other_template = str_replace('%file%', $document_name, $other_template);
        $other_template = str_replace('%other%', $other_name, $other_template);
        $other_template = str_replace('%link%', '<a href="' . $document_name . '">here</a>', $other_template);

        wp_mail( $other->user_email, $subject, $other_template, $headers );

        //set other user as owner of task
        return $wpdb->update(
            $table_name,
            array(
                'owner' => $owner
            ),
            array(
                'application_id'    => $application_id,
                'document_id'       => $document_id,
                'step_id'           => $step_id
            )
        );
    }
}

/**
 * @param $url
 *
 * @return mixed
 */
function get_attachment_data_by_url( $url ) {
    global $wpdb;

    $upload_dir = wp_upload_dir();

    $url = str_replace($upload_dir['baseurl'] . '/', '', $url);

    $id = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT wposts.ID FROM $wpdb->posts wposts, $wpdb->postmeta wpostmeta
				WHERE wposts.ID = wpostmeta.post_id
				AND wpostmeta.meta_key = '_wp_attached_file'
				AND wpostmeta.meta_value = '%s'
				AND wposts.post_type = 'attachment'", $url
        )
    );

    return get_post( $id );
}