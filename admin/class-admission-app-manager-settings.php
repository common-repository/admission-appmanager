<?php

class Aam_Settings
{
    public function __construct()
    {
        if (isset($_POST['save_settings'])) {
            $this->save_settings();
        } else {
            $this->render_form();
        }
    }

    public function render_form()
    { ?>
        <?php $options = get_option('aam_settings', array()); ?>

        <div class="wrap">
            <h2><?php _e('Settings', 'admission-app-manager'); ?></h2>

            <form method="post" action="">
                <input type="hidden" name="save_settings" value="1">
                <table class="form-table">
                    <tbody>

                    <tr>
                        <th scope="row"><label for="applications_page">Applications page</label></th>
                        <td>
                            <?php wp_dropdown_pages(array(
                                'name' => 'aam_settings[applications_page]',
                                'id' => 'applications_page',
                                'selected' => isset($options['applications_page']) ? $options['applications_page'] : null
                            )); ?>
                            <p class="description"><?php _e('Select Applications page'); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="applications_page">Single Application page</label></th>
                        <td>
                            <?php wp_dropdown_pages(array(
                                'name' => 'aam_settings[single_application_page]',
                                'id' => 'single_application_page',
                                'selected' => isset($options['single_application_page']) ? $options['single_application_page'] : null
                            )); ?>
                            <p class="description"><?php _e('Select Single Application page'); ?></p>
                        </td>
                    </tr>
                    </tbody>
                </table>


                <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary"
                                         value="Save Changes"></p></form>

        </div>
    <?php }

    public function save_settings()
    {
        if (is_admin() && current_user_can('manage_options')) {
            update_option('aam_settings', $_POST['aam_settings']);

            $this->render_form();
        }
    }
}

function aam_settings()
{
    return new Aam_Settings();
}