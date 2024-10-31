<?php

   /*
   Plugin Name: Seahorse GDPR Data Manager
   Plugin URI: https://www.gdpr-data-manager.com
   description: This plugin offers a set of tools to assist website owners adhere to the most critical data compliance obligations raised by GDPR. Search customer records and take action, it's that simple!
   Version: 2.6.0
   Author: Seahorse
   Text Domain: seahorse-gdm
   Contributors: wpseahorse, echomedia
   Tags: LGPD, CCPA, woocommerce, edd, privacy policy, GDPR, Right to Erasure, Right to Forget, Right To Portability, Export Data, Delete Data, regulation, compliance, easy digital downloads, users, user management, delete, export
   Requires at least: 4.4
   Tested up to: 5.6
   Stable tag: 4.3
   Author URI: https://www.seahorse-data.com
   License: GPLv2 or later
   License URI:  https://www.gnu.org/licenses/gpl-2.0.html
   */

// /* prevent access from outside cms */
define('SHGDPRDM_VERSION', '2.6.0');
define('SHGDPRDM_SUPPORTED_PLUGINS', serialize(array('wcom' => 'wooCommerce')));
define('SHGDPRDM_PRO', serialize(array('wcf' => array('Woo-Commerce-Plugin', 'Woo-Commerce-Guest-Accounts'), 'eddf' => array('Easy-Digital-Downloads-Plugin') )));
define('SHGDPRDM_SUPPORTED_PLUGINS_OPTIONS', serialize(array('Woo-Commerce-Plugin', 'Woo-Commerce-Guest-Accounts', 'Easy-Digital-Downloads-Plugin' )));
define('SHGDPRDM_VALIDATE_DEFAULT_URL', 'https://www.gdpr-data-manager.com');
define('SHGDPRDM_VALIDATE_UAT_URL', 'https://uat.gdpr-data-manager.com');
define('SHGDPRDM_SUPPORT_EMAIL', 'info@seahorse-data.com');


// SH Note: Control for testing
define('SHGDPRDM_TESTING', null);

global $wpdb;
if ($wpdb) {
    define('SHGDPRDM_ABSPATH', 'You are not authorised to access this file.');
} else {
    wp_die('You are not authorised to access this file.');
}

/**
 * Initialise the internationalisation domain
 */
function seahorse_gdpr_data_manager_load_plugin_textdomain()
{
    // wp_die(basename(dirname(__FILE__)) . '/languages/');
    load_plugin_textdomain('seahorse-gdpr-data-manager', FALSE, basename(dirname(__FILE__)) . '/languages/');
}
add_action('plugins_loaded', 'seahorse_gdpr_data_manager_load_plugin_textdomain');



include_once(dirname(__FILE__) . '/shared/shgdprdm_validate_control.class.php');
// regardless if its admin or non-admin, run the inclusion of Shortcodes functions
shgdprdm_includeSiteShortCode();


if (!is_admin()) {
    if (shgdprdm_validateNonAdminRequest()) {
        add_filter('shgdprdm_validateNotAdmin', 'shgdprdm_validateNonAdminRequest');
        add_action('after_setup_theme', 'shgdprdm_runFilterNonAdminRequest'); // this is needed for Exernal user
    } else {
        return;
    }
} else {

  // Include the main GDPR Data Manager class.
    if (is_admin() && !class_exists('shgdprdm_GdprDataManager')) {
        try {
            shgdprdm_checkFileExists(dirname(__FILE__) . '/classes/shgdprdm_gdprdatamanager.class.php');
            include_once dirname(__FILE__) . '/classes/shgdprdm_gdprdatamanager.class.php';
        } catch (Exception $e) {
            $msg = $e->getMessage();
            add_action('admin_notices', 'shgdprdm_exceptionErrorNotice');
        }
        if (class_exists('SHGdprdm_GdprDataManager')) {
            try {
                $seahorseGdprDataManagerPlugin = new SHGdprdm_GdprDataManager(__FILE__);
                // $seahorseGdprDataManagerPlugin = new SHGdprdm_GdprDataManager( );
                try {
                    $seahorseGdprDataManagerPlugin->shgdprdm_register();
                } catch (Exception $e) {
                    $msg = $e->getMessage();
                    add_action('admin_notices', 'shgdprdm_exceptionErrorNotice');
                }
            } catch (Exception $e) {
                $msg = $e->getMessage();
                add_action('admin_notices', 'shgdprdm_exceptionErrorNotice');
            }
        }
    }
}

if (is_admin() && isset($seahorseGdprDataManagerPlugin)) {
    // activation
    register_activation_hook(__FILE__, array($seahorseGdprDataManagerPlugin, 'shgdprdm_activate'));
    add_action('admin_notices', array($seahorseGdprDataManagerPlugin, 'shgdprdm_registerNewLicenceNotice'));
}


// deactivation
// SH NOTES: There is another line to go here
if (is_admin() && isset($seahorseGdprDataManagerPlugin)) {
    register_deactivation_hook(__FILE__, array($seahorseGdprDataManagerPlugin , 'shgdprdm_deactivate'));
}
// uninstall

// function tp check if file exists & return error exception message
function shgdprdm_checkFileExists($filePath)
{
    if (!file_exists($filePath)) {
        $siteEmail = '';
        $shgdprdm_name = 'the Plugin Developer';
        $shgdprdm_link = '<br><a class="button shgdprdm-usr-btn shgdprdm-usr-btn-regular" href="'.SHGDPRDM_VALIDATE_DEFAULT_URL.'" target="_blank"><em>Contact Seahorse Data Management</em></a>';
        if (get_bloginfo('admin_email')) {
            $siteEmail = '<br><a class="button shgdprdm-usr-btn shgdprdm-usr-btn-regular" href="mailto:'.get_bloginfo("admin_email").'">Contact '.get_bloginfo("name").'</a>';
        }
        throw new Exception('<strong>GDPR Data Manager - ERROR! <br>Reference File Does Not Exist <br> exr_0001_'.(is_admin()?"1":"2").' </strong><br>Please Contact '.(is_admin()?$shgdprdm_name.$shgdprdm_link:get_bloginfo("name").$siteEmail));
    }
}

// function to generate Error notice
function shgdprdm_exceptionErrorNotice()
{
    global $msg;
    $class = 'notice notice-error';
    $message = $msg;
    if ($message) { ?>
    <div class="<?php echo esc_attr($class);?>"><p><?php echo  $message ;?></p></div>
  <?php }
}

function shgdprdm_logErrMsg($message)
{
    if (WP_DEBUG === true) {
        if (is_array($message) || is_object($message)) {
            error_log(print_r($message, true));
        } else {
            error_log($message);
        }
    }
}


function shgdprdm_validateNonAdminRequest()
{
    if (!isset($_GET) || empty($_GET)) {
        // wp_die("<br>Fail 1");
        return false;
    }
    if (!isset($_GET['at']) || empty($_GET['at'])) {
        // wp_die("<br>Fail 2");
        return false;
    }
    if (!isset($_GET['ue']) || empty($_GET['ue'])) {
        // wp_die("<br>Fail 3");
        return false;
    }
    if (!isset($_GET['ra']) || empty($_GET['ra'])) {
        // wp_die("<br>Fail 4");
        return false;
    }


    return true;
}

function shgdprdm_sanitizeNonAdminRequest()
{
    // We know GET variable are as expected at this Point
    $sanitizedVars = array();
    $sanitizedVars['at'] = sanitize_text_field($_GET['at']);
    $sanitizedVars['ue'] = sanitize_text_field($_GET['ue']);
    $sanitizedVars['ra'] = sanitize_text_field($_GET['ra']);
    return $sanitizedVars;
}

function shgdprdm_validateNonAdminRequestNonce()
{
    $getVars = shgdprdm_sanitizeNonAdminRequest();

    if (!empty($_POST['shgdprdmexv_nonce']) && !wp_verify_nonce(sanitize_text_field($_POST['shgdprdmexv_nonce']), '/gdpr-data-manager/verify/?ra='.$getVars['ra'].'&at='.$getVars['at'].'&ue='.$getVars['ue'])) {
        return false;
    }

    if (!empty($_POST['shgdprdmexv_nonce']) && !check_admin_referer('/gdpr-data-manager/verify/?ra='.$getVars['ra'].'&at='.$getVars['at'].'&ue='.$getVars['ue'], 'shgdprdmexv_nonce')) {
        return false;
    }

    return true;
}


function shgdprdm_addFilterNonAdminRequest()
{
    add_filter('shgdprdm_validateNotAdmin', 'shgdprdm_validateNonAdminRequest');
}


function shgdprdm_runFilterNonAdminRequest()
{
    $shgdprdm_request_query = false;
    $shgdprdm_request_query = apply_filters('shgdprdm_validateNotAdmin', 'shgdprdm_validateNonAdminRequest');

    if ($shgdprdm_request_query === true || $shgdprdm_request_query === 1|| $shgdprdm_request_query === '1') {
        shgdprdm_runExternalNonAdminRequest();
    }
}


function shgdprdm_runExternalNonAdminRequest()
{
    if (!class_exists('SHGdprdm_ExternalAccessRequest')) {
        try {
            shgdprdm_checkFileExists(dirname(__FILE__) . '/user/classes/shgdprdm_ExternalAccessRequest.class.php');
            include_once dirname(__FILE__) . '/user/classes/shgdprdm_ExternalAccessRequest.class.php';
        } catch (Exception $e) {
            $msg = $e->getMessage();
            wp_die($msg);
        }
    }
    if (class_exists('SHGdprdm_ExternalAccessRequest')) {
        try {
            global $seahorseMyDataViewExternalAccessRequest;
            $seahorseMyDataViewExternalAccessRequest = new SHGdprdm_ExternalAccessRequest(__FILE__);

            $seahorseMyDataViewExternalAccessRequest->shgdprdm_registerExternal();
        } catch (Exception $e) {
            $msg = $e->getMessage();
            wp_die($msg);
        }
    } else {
        $siteEmail = '';
        if (get_bloginfo('admin_email')) {
            $siteEmail = '<br><a class="button shgdprdm-usr-btn shgdprdm-usr-btn-regular" href="mailto:'.get_bloginfo("admin_email").'">Contact '.get_bloginfo("name").'</a>';
        }
        wp_die('<strong>GDPR Data Manager - ERROR!</strong>
    <br>Action cannot be performed. (ref: shgdprdm-SHDM-001)
    <br>Please Contact '.get_bloginfo("name").$siteEmail.'.');
    }
}


function shgdprdm_includeSiteShortCode()
{
    $valdiateControl = new SHGdprdm_ValidateControl();

    if (!class_exists('SHGdprdm_ShortCodes')) {
        try {
            if (
                $valdiateControl->shgdprdm_validateVerifyLicence() &&
                $valdiateControl->shgdprdm_validateHasLicence()
                // &&
                // ($valdiateControl->shgdprdm_validateIsProLicence('wcf') || $valdiateControl->shgdprdm_validateIsProLicence('eddf'))
            ) {
                shgdprdm_checkFileExists(dirname(__FILE__) . '/shared/shortcodes.pro.class.php');
                include_once dirname(__FILE__) . '/shared/shortcodes.pro.class.php';


            } else {
                shgdprdm_checkFileExists(dirname(__FILE__) . '/shared/shortcodes.class.php');
                include_once dirname(__FILE__) . '/shared/shortcodes.class.php';

            }

        } catch (Exception $e) {
            $msg = $e->getMessage();
            wp_die($msg);
        }
    }
    if (class_exists('SHGdprdm_ShortCodes')) {
        try {
            global $seahorseMyDataViewShortCodes;
            $seahorseMyDataViewShortCodes = new SHGdprdm_ShortCodes(__FILE__);

            $seahorseMyDataViewShortCodes->register_shortcodes();
        } catch (Exception $e) {
            $msg = $e->getMessage();
            wp_die($msg);
        }
    } else {
        wp_die('Shortcide not exists');
    }
}
