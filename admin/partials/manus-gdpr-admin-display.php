<?php
/**
 * Provide a admin area view for the plugin
 *
 * @link       #
 * @since      1.0.0
 *
 * @package    Manus_GDPR
 * @subpackage Manus_GDPR/admin/partials
 */
?>

<div class="wrap">

    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

    <form method="post" name="manus_gdpr_options_form" action="options.php">

        <?php
            settings_fields( 'manus_gdpr_options_group' );
            do_settings_sections( 'manus-gdpr' );
            submit_button();
        ?>

    </form>

</div>