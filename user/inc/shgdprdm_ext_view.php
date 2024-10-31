<?php
defined('SHGDPRDM_ABSPATH') or die('You are not authorised to access this file.');
function shgdprdm_actionUserView($classPath, $pluginDir)
{
    require_once $classPath.'shgdprdm_extTmpl.class.php';
    if (!is_admin() && class_exists('SHGdprdm_UTMP')) {
        $shgdprdmutmp = new SHGdprdm_UTMP($pluginDir);
        
        if (
             isset($_POST) && isset($_POST['shgdprdm_exptd']) &&
             (isset($_POST['shgdprdm_export_xml']) || isset($_POST['shgdprdm_export_csv']) || isset($_POST['shgdprdm_export_json']))
        ) {
            unset($_POST['shgdprdm_exptd'], $_POST['shgdprdm_exptd'], $_POST['shgdprdm_export_xml'], $_POST['shgdprdm_export_csv'], $_POST['shgdprdm_export_json']);
        }
        
            
        return array( $shgdprdmutmp->shgdprdm_makeUserTemplate(), $shgdprdmutmp->shgdprdm_getRequestStatus() );
            
    // echo $shgdprdmutmp->shgdprdm_makeUserTemplate();
    } else {
        if (SHGDPRDM_TESTING) {
            die('shgdprdm_ext_view - line 28');
        } else {
            wp_die(shgdprdm_userFailureRedirectNotice('EXV', '001'));
            // wp_safe_redirect( get_home_url() );
        }
        die();
    }
}
