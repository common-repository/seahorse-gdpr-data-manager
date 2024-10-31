<?php
defined('SHGDPRDM_ABSPATH') or die('You are not authorised to access this file.');
function shgdprdm_adminMenu()
{
    ?>
  <div id="shgdprdm-admin" class="wrap">
    <h1><?php echo SHGDPRDM_LABEL_TITLE;?></h1>
  
    <?php
    // $string = sprintf(__(SHGDPRDM_DELETE_MAIL_BODY_1), '<p>', get_bloginfo('name'), '</p>', '<p>', '</p>', '<p>', '</p>');
    // echo $string;

    // echo SHGDPRDM_DELETE_MAIL_BODY_1;
    // echo '<hr>';
    // echo SHGDPRDM_DELETE_MAIL_BODY_1_ADMIN;
    // echo '<hr>';
    // echo SHGDPRDM_EXPORT_MAIL_BODY_1;
    // echo '<hr>';
    ?>

    <?php
    settings_errors();

    if (!$_POST) {
        update_option('shgdprdm_admin_msg', '');
    }
    if (!shgdprdm_adminNavCheckStatus()) {
        // echo "NO LICENCE";
        update_option('shgdprdm_admin_msg', array( 'class' => 'warning', 'msg' => SHGDPRDM_war_007));
    }
    if ($_POST && !isset($_POST['shgdprdm-review-search-submit'])) {
        // Validate the POST
        if (shgdprdm_validateReturnedSearchData($_POST)) {
            // Validate the Nonce
            if (shgdprdm_validateReturnedSearchDataNonce(sanitize_text_field($_POST['shgdprdmrd_nonce']))) {
                // Validate the User Credentials
                if (shgdprdm_validateReturnedSearchDataUser()) {
                    // Extract the Data from the Validated POST
                    $displayData = shgdprdm_extractReturnedSearchData($_POST['data']);
                    // Validate the Extracted Data & Render if Valid
                    if (shgdprdm_validateExtractedSearchData($displayData)) {
                        echo shgdprdm_recordsView($displayData);
                    }
                    // If extracted Data is not Valid
                    else {
                        exit(wp_safe_redirect(admin_url('index.php?page=seahorse_gdpr_data_manager_plugin')));
                    }
                }
                // If User Credentials are insufficient
                else {
                    update_option('shgdprdm_admin_msg', array('class' => 'error', 'msg' => SHGDPRDM_err_009.' <em>error ref: ADM_001</em>'));
                    exit(wp_safe_redirect(admin_url('index.php?page=seahorse_gdpr_data_manager_plugin')));
                }
            }
            // If Nonce is not valid
            else {
                update_option('shgdprdm_admin_msg', array('class' => 'error', 'msg' => SHGDPRDM_err_009.' <em>error ref: ADM_002</em>'));
                exit(wp_safe_redirect(admin_url('index.php?page=seahorse_gdpr_data_manager_plugin')));
            }
        }
        // If Returned POST is not valid
        else {
            exit(wp_safe_redirect(admin_url('index.php?page=seahorse_gdpr_data_manager_plugin')));
        }
    } else {
        $active_tab = '';
        if (isset($_GET[ 'tab' ])) {
            $active_tab = $_GET[ 'tab' ];
        } // end if
        echo shgdprdm_adminNav($active_tab);

        if ($active_tab == 'gdpr_data_manager_home') {
            echo shgdprdm_HomeInfoPage();
        } elseif ($active_tab == 'gdpr_data_manager_pending_actions') {
            echo shgdprdm_pendingHistoryPage();
        }
        // else if( $active_tab == 'gdpr_data_manager_search_options' ) {
        //   echo shgdprdm_searchOptionsPage();
        // }
        elseif ($active_tab == 'gdpr_data_manager_privacy_policy') {
            echo shgdprdm_privacyPolicyOptionsPage();
        } elseif ($active_tab == 'gdpr_data_manager_review_history') {
            echo shgdprdm_reviewHistoryPage();
        } elseif ($active_tab == 'gdpr_data_manager_help') {
            echo shgdprdm_reviewHelpPage();
        } elseif ($active_tab == 'gdpr_data_manager_register_licence') {
            echo shgdprdm_registerLicencePage();
        } else {
            echo shgdprdm_searchPage();
        } // end final else if
    } // end else
    update_option('shgdprdm_admin_msg', ''); ?>
  </div>
<?php
} // end function

echo shgdprdm_adminMenu();
?> 
