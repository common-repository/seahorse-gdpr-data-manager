<?php
defined('SHGDPRDM_ABSPATH') or die('You are not authorised to access this file.');

function shgdprdm_actionUserExport($classPath, $parentDir, $delete=false)
{
    if (
         isset($_POST) && isset($_POST['shgdprdm_exptd']) &&
         (isset($_POST['shgdprdm_export_xml']) || isset($_POST['shgdprdm_export_csv']) || isset($_POST['shgdprdm_export_json']) || isset($_POST['shgdprdm_delete_user']))
    ) {
        // Verify the user at this step before processing the search function in admin?
        require_once $classPath.'shgdprdm_userExport.class.php';
        // require_once WP_PLUGIN_DIR.'/seahorse-gdpr-data-manager/user/inc/shgdprdm_userExport.class.php';

        if (!is_admin() && class_exists('SHGdprdm_UEXP')) {
            try {
                $export = new SHGdprdm_UEXP($parentDir, $delete);
                //exit(print_r($export));
            } catch (Exception $e) {
                //echo ;
                //exit("Error: ".$e->getMessage());
                if (strpos($e->getMessage(), 'gdpr-data-manager/verify') !== false) {
                    // wp_die( shgdprdm_userFailureRedirectNotice('EXA', '001') );
                    wp_safe_redirect(get_home_url().$e->getMessage());
                    exit;
                } else {
                    wp_die(shgdprdm_userFailureRedirectNotice('EXA', '001'));
                    // die($e->getMessage());
                // wp_safe_redirect( get_home_url() );
                }
            }
            // if(isset($export) && $delete !== NULL){
                // 	$export->shgdprdm_deleteExport();
                // }
        }
        // unset( $_POST['shgdprdm_exptd'], $_POST['shgdprdm_export_xml'], $_POST['shgdprdm_export_csv'], $_POST['shgdprdm_export_json'], $_POST['shgdprdm_delete_user'] );
        unset($_POST['shgdprdm_export_xml'], $_POST['shgdprdm_export_csv'], $_POST['shgdprdm_export_json']);
    } else {
        if (SHGDPRDM_TESTING) {
            die('shgdprdm_ext_action - line 13');
        }
        // if(SHGDPRDM_TESTING){
        wp_die(shgdprdm_userFailureRedirectNotice('EXA', '003'));
        // wp_safe_redirect( get_home_url() );
        exit();
        // }
        die();
    }
}
