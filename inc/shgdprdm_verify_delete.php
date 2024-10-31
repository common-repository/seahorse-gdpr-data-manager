<?php
defined('SHGDPRDM_ABSPATH') or die('You are not authorised to access this file.');

function shgdprdm_randomString(
    $length,
    $set = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789',
    $repeat = 10
) {
    return substr(str_shuffle(str_repeat($set, $repeat)), 0, $length);
}



function shgdprdm_verifyExportDelete($data = null, $url = null)
{

  // Check for current user privileges
    if (!current_user_can('manage_options')) {
        die('Manage Options');
        return false;
    }

    // Check if we are in WP-Admin
    if (!is_admin()) {
        die('Is Admin');
        return false;
    }

    // Check if user can carry out administrator actions
    if (!current_user_can('administrator')) {
        die('Administrator');
        return false;
    }

    $isDisasterSync = shgdprdm_getdisasterSyncVal($data['shgdprdm_user_email'], $data['shgdprdm_verify_delete_val']);


    if ($_SERVER['HTTP_HOST'] == 'localhost') {
        $to = get_option('admin_email');
    } elseif (!empty($data['shgdprdm_user_email'])) {
        // Sanitize & Validate Input
        if (is_email($data['shgdprdm_user_email'])) {
            $to = sanitize_email($data['shgdprdm_user_email']);
        } else {
            $to = 'Unknown_User';
        }
    } else {
        $to = get_option('admin_email');
    }



    if (isset($data['shgdprdm_verify_export']) &&
        !empty($data['shgdprdm_verify_export']) &&
        $data['shgdprdm_verify_export'] == 'Verify Export' &&
        isset($data['shgdprdm_verify_export_val']) &&
        !empty($data['shgdprdm_verify_export_val']) &&
        $data['shgdprdm_verify_export_val'] == 5
    ) {
        $atVal = $data['shgdprdm_verify_export_val'];
        $subject = SHGDPRDM_EXPORT_MAIL_SUBJECT;
        $body = SHGDPRDM_EXPORT_MAIL_BODY_1;
    } elseif (isset($data['shgdprdm_verify_delete']) &&
        !empty($data['shgdprdm_verify_delete']) &&
        $data['shgdprdm_verify_delete'] == 'Verify Delete' &&
        isset($data['shgdprdm_verify_delete_val']) &&
        !empty($data['shgdprdm_verify_delete_val']) &&
        $data['shgdprdm_verify_delete_val'] == 6
    ) {
        $atVal = $data['shgdprdm_verify_delete_val'];
        $subject = SHGDPRDM_DELETE_MAIL_SUBJECT;
        $body = SHGDPRDM_DELETE_MAIL_BODY_1;
        if (($isDisasterSync == 1)) {
            $body = SHGDPRDM_DELETE_MAIL_BODY_1_ADMIN;
        }
    } else {
        die('Something Not Set in POST');
        return false;
    }

    if (!$url) {
        $url = esc_url(admin_url('admin.php'))."?page=seahorse_gdpr_data_manager_plugin&tab=gdpr_data_manager_pending_actions";
    }

    //// create rand string for Verification
    $randString = shgdprdm_randomString(85);

    /// base encode for email link
    $at = base64_encode(serialize($atVal));
    $ue = base64_encode(serialize($to));
    $ra = base64_encode(serialize($randString));

    // $dynamicContent = "<p>".get_bloginfo( 'url' )."/gdpr-data-manager/verify/?ra=$ra&at=$at&ue=$ue</p>";
    $rewritePrefix = '';
    if (strpos(get_option('permalink_structure'), 'index.php') > -1) {
        $rewritePrefix = '/index.php';
    }
    $dynamicContent = "<p><a href='".get_bloginfo('url').$rewritePrefix."/gdpr-data-manager/verify/?ra=".$ra."&at=".$at."&ue=".$ue."' target='_blank'>" . SHGDPRDM_DELETE_EXPORT_VERIFY_LINK_TEXT . "</a></p>";

    // $subject = DELETE_MAIL_SUBJECT;
    $body .= $dynamicContent.SHGDPRDM_DELETE_EXPORT_MAIL_BODY_2;
    $headers = array('Content-Type: text/html; charset=UTF-8');

    //// check if record is a sync record
    if (($isDisasterSync == 1)) {
        $to = get_option('admin_email');
    }

    /// mail functionality here
    if (!wp_mail($to, $subject, $body, $headers)) {
        echo 'Mail send fail: not sent '.$to;
    } else {
        if (is_numeric($data['shgdprdm_uid'])) {
            // shgdprdm_updateActionsHistory($expt, $uid, $aid, $acv = NULL, $rand = NULL, $avt = '0000-00-00 00:00:00', $sync = '', $key = NULL)
            $ref = shgdprdm_updateActionsHistory(
                $atVal,
                $data['shgdprdm_uid'],
                get_current_user_id(),
                (($isDisasterSync==1) ? 103 : 1),
                // ( ($isDisasterSync==1) ? 5 : 1 ),
                $randString,
                '0000-00-00 00:00:00',
                (($isDisasterSync==1) ? 1 : 0)
            );
        } else {
            $ref = shgdprdm_updateActionsHistory(
                $atVal,
                $data['shgdprdm_user_email'],
                get_current_user_id(),
                (($isDisasterSync==1) ? 103 : 1),
                // ( ($isDisasterSync==1) ? 5 : 1 ),
                $randString,
                '0000-00-00 00:00:00',
                (($isDisasterSync==1) ? 1 : 0)
            );
        }

        if (!$ref) {
            update_option('shgdprdm_admin_msg', array('class' => 'error', 'msg' => SHGDPRDM_err_009.'<br>Error Code 4.1'));
            $url = esc_url(admin_url('admin.php'))."?page=seahorse_gdpr_data_manager_plugin&tab=gdpr_data_manager_review_history";
            wp_redirect($url);
            exit;
        }
        // exit("Ref: ".$ref);
        //die("<br>OIDS: ".unserialize(base64_decode($data['shgdprdm_oids'])) );
        $updateData = array(
            'shgdprdm_rid' => $ref,
            'shgdprdm_awhat' => $atVal,
            'shgdprdm_uwho' => strtoupper($data['shgdprdm_uid'])=='GUEST'?unserialize(base64_decode($data['shgdprdm_oids'])):$data['shgdprdm_uid'],
            'shgdprdm_awho' => get_current_user_id(),
            'shgdprdm_awhen' => date('Y-m-d H:i:s'),
            'shgdprdm_uwhat' => (($isDisasterSync==1) ? 103 : 1), //Pending
            // 'shgdprdm_uwhat' => ( ($isDisasterSync==1) ? 5 : 1 ), //Pending
            'shgdprdm_uwhen' => '0000-00-00 00:00:00'
        );
        // shgdprdm_setReviewHistoryExternalData( $updateData );
        if (!shgdprdm_setReviewHistoryExternalData($updateData)) {
            update_option('shgdprdm_admin_msg', array('class' => 'error', 'msg' => SHGDPRDM_err_009.'<br>Error Code 4.2'));
            $url = esc_url(admin_url('admin.php'))."?page=seahorse_gdpr_data_manager_plugin&tab=gdpr_data_manager_review_history";
            wp_redirect($url);
            exit;
        }
        wp_redirect($url);
        exit;
    }
}

function shgdprdm_validateExportPost($postData)
{
    // Expecting:
    // shgdprdm_user_email (email Address)
    // shgdprdm_exptd (encoded text)
    // shgdprdm_export_xml ("Download XML")
    // shgdprdm_export_csv ("Download CSV")
    // shgdprdm_export_json ("Download JSON")
    // shgdprdm_delete_user ("Delete All Data")


    // Check for expected posts
    // Expecting:
    // shgdprdm_user_email
    // shgdprdm_uid
    // shgdprdm_verify_export_val
    // shgdprdm_verify_delete_val
    if (!isset($postData['shgdprdm_user_email']) || empty($postData['shgdprdm_user_email']) || !is_email($postData['shgdprdm_user_email'])) {
        update_option('shgdprdm_admin_msg', array('class' => 'error', 'msg' => SHGDPRDM_err_009.'<br>Error Code 3.7.1'));
        return false;
    }
    if (!isset($postData['shgdprdm_uid']) || empty($postData['shgdprdm_uid'])) {
        update_option('shgdprdm_admin_msg', array('class' => 'error', 'msg' => SHGDPRDM_err_009.'<br>Error Code 3.7.2'));
        return false;
    }
    if (!is_numeric($postData['shgdprdm_uid'])) {
        if (strtoupper($postData['shgdprdm_uid']) != 'GUEST') {
            update_option('shgdprdm_admin_msg', array('class' => 'error', 'msg' => SHGDPRDM_err_009.'<br>Error Code 3.7.3'));
            return false;
        } else {
            if (!isset($postData['shgdprdm_oids'])  || empty($postData['shgdprdm_oids'])) {
                update_option('shgdprdm_admin_msg', array('class' => 'error', 'msg' => SHGDPRDM_err_009.'<br>Error Code 3.7.4'));
                return false;
            }
        }
    }
    if (!isset($postData['shgdprdm_verify_export_val']) || empty($postData['shgdprdm_verify_export_val']) || !is_numeric($postData['shgdprdm_verify_export_val']) || $postData['shgdprdm_verify_export_val'] != 5) {
        update_option('shgdprdm_admin_msg', array('class' => 'error', 'msg' => SHGDPRDM_err_009.'<br>Error Code 3.7.5'));
        return false;
    }
    if (!isset($postData['shgdprdm_verify_delete_val']) || empty($postData['shgdprdm_verify_delete_val']) || !is_numeric($postData['shgdprdm_verify_delete_val']) || $postData['shgdprdm_verify_delete_val'] != 6) {
        update_option('shgdprdm_admin_msg', array('class' => 'error', 'msg' => SHGDPRDM_err_009.'<br>Error Code 3.7.6'));
        return false;
    }
    // if( isset($_POST['shgdprdm_verify_export']) && isset($_POST['shgdprdm_verify_delete'] ) ){
    //   update_option('shgdprdm_admin_msg',array('class' => 'error', 'msg' => SHGDPRDM_err_009.'<br>Error Code 3.7.5'));
    //   return FALSE;
    // }
    return true;
}

if ($_POST) {
    if (shgdprdm_validateExportPost($_POST)) {
        if (isset($_GET['action']) && $_GET['action'] == 'shgdprdm_export_action') {
            if (isset($_POST['shgdprdm_verify_export'])) {
                if ($_POST['shgdprdm_verify_export']) {
                    if (shgdprdm_verifyExportDelete($_POST)) {
                        return;
                    } else {
                        update_option('shgdprdm_admin_msg', array('class' => 'error', 'msg' => SHGDPRDM_err_009.'<br>Error Code 3.1'));
                        exit(wp_safe_redirect(admin_url('index.php?page=seahorse_gdpr_data_manager_plugin')));
                    }
                    unset($_POST['shgdprdm_user_email'], $_POST['shgdprdm_uid'], $_POST['shgdprdm_verify_export_val'], $_POST['shgdprdm_verify_delete_val']);
                } else {
                    update_option('shgdprdm_admin_msg', array('class' => 'error', 'msg' => SHGDPRDM_err_009.'<br>Error Code 3.2'));
                    exit(wp_safe_redirect(admin_url('index.php?page=seahorse_gdpr_data_manager_plugin')));
                }
            } elseif (isset($_POST['shgdprdm_verify_delete'])) {
                if ($_POST['shgdprdm_verify_delete']) {
                    if (shgdprdm_verifyExportDelete($_POST)) {
                        return;
                    } else {
                        update_option('shgdprdm_admin_msg', array('class' => 'error', 'msg' => SHGDPRDM_err_009.'<br>Error Code 3.3'));
                        exit(wp_safe_redirect(admin_url('index.php?page=seahorse_gdpr_data_manager_plugin')));
                    }
                    unset($_POST['shgdprdm_user_email'], $_POST['shgdprdm_uid'], $_POST['shgdprdm_verify_export_val'], $_POST['shgdprdm_verify_delete_val']);
                } else {
                    update_option('shgdprdm_admin_msg', array('class' => 'error', 'msg' => SHGDPRDM_err_009.'<br>Error Code 3.4'));
                    exit(wp_safe_redirect(admin_url('index.php?page=seahorse_gdpr_data_manager_plugin')));
                }
            } else {
                update_option('shgdprdm_admin_msg', array('class' => 'error', 'msg' => SHGDPRDM_err_009.'<br>Error Code 3.5'));
                exit(wp_safe_redirect(admin_url('index.php?page=seahorse_gdpr_data_manager_plugin')));
            }
        } else {
            update_option('shgdprdm_admin_msg', array('class' => 'error', 'msg' => SHGDPRDM_err_009.'<br>Error Code 3.6'));
            exit(wp_safe_redirect(admin_url('index.php?page=seahorse_gdpr_data_manager_plugin')));
        }
    } else {
        // update_option('shgdprdm_admin_msg',array('class' => 'error', 'msg' => SHGDPRDM_err_009.'<br>Error Code 3.7'));
        exit(wp_safe_redirect(admin_url('index.php?page=seahorse_gdpr_data_manager_plugin')));
    }
} else {
    update_option('shgdprdm_admin_msg', array('class' => 'error', 'msg' => SHGDPRDM_err_009.'<br>Error Code 3.8'));
    exit(wp_safe_redirect(admin_url('index.php?page=seahorse_gdpr_data_manager_plugin')));
}
