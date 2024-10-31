<?php
defined('SHGDPRDM_ABSPATH') or die('You are not authorised to access this file.');

function shgdprdm_getdisasterSyncValonSearch($shgdprdm_user_email, $shgdprdm_verify_delete_val)
{
    global $wpdb;
    $table = $wpdb->prefix.'shgdprdm_history';
    $disasterSync = $wpdb->get_results($wpdb->prepare("SELECT disasterSync, actionTimestamp FROM {$table}
    WHERE userEmail = %s AND actionType = %d AND disasterSync = %d AND actionVerify = %d ORDER BY actionTimestamp DESC", array($shgdprdm_user_email, $shgdprdm_verify_delete_val, 1, 100)));

    $countRecs = count($disasterSync);
    if ($countRecs > 0) {
        $disasterSyncVal = $disasterSync[0]->disasterSync;
        $date = $disasterSync[0]->actionTimestamp;

        return array($disasterSyncVal, $date, $countRecs);
    }
    return array(null,null,null);
    // $disasterSync = $wpdb->get_var( $wpdb->prepare("SELECT disasterSync FROM {$table}
    // WHERE userEmail = %s AND actionType = %d AND disasterSync = %d AND actionVerify = %d", array($shgdprdm_user_email, $shgdprdm_verify_delete_val, 1, 4) ) );
    // return $disasterSync;
}

function shgdprdm_getDisasterSyncRegDateCheck($shgdprdm_user_email)
{
    $user = get_user_by('email', $shgdprdm_user_email);
    if ($user) {
        return $regD = $user->user_registered;
    }

    // Check if Guest User
    // Get all orders for this Email
    // Find date of most recent order
    // return this date
    if (shgdprdm_getOptionsGroup('shgdprdm_admin_plugins_settings_group') && get_option('Woo-Commerce-Guest-Accounts') !== null) {
        if (function_exists('wc_get_orders')) {
            $orders = wc_get_orders(array('email' => $shgdprdm_user_email));
            if (!empty($orders)) {
                if (count($orders) === 1) {
                    $orderDate = $orders[0]->get_date_completed()->format('Y-m-d H:i:s');
                    if (!$orderDate) {
                        $orderDate = $orders[0]->get_date_paid()->format('Y-m-d H:i:s');
                        if (!$orderDate) {
                            $orderDate = $orders[0]->get_date_created()->format('Y-m-d H:i:s');
                        }
                    }
                    return $orderDate;
                } else {
                    $orderDates = array();
                    foreach ($orders as $order) {
                        $orderDate = $order->get_date_completed()->format('Y-m-d H:i:s');
                        if (!$orderDate) {
                            $orderDate = $order->get_date_paid()->format('Y-m-d H:i:s');
                            if (!$orderDate) {
                                $orderDate = $order->get_date_created()->format('Y-m-d H:i:s');
                            }
                        }
                        array_push($orderDates, $orderDate);
                    }
                    if (!empty($orderDates)) {
                        $maxDate = max($orderDates);
                        return $maxDate;
                    }
                    return false;
                }
            }
            return false;
        }
        return false;
    }
    return false;
}

function shgdprdm_makeDisasterSyncInconsistError()
{
    $html = '';
    $html .= '<div class="shgdprdm-notice shgdprdm-error">';
    $html .= '<div class="shgdprdm-complete-sync-warning-container">';
    $html .= '<span class="shgdprdm_icon-xl dashicons shgdprdm-complete-sync-warning shgdprdm-not-valid dashicons-warning"></span>';
    $html .= '<p class="shgdprdm_icon-xl shgdprdm-complete-sync-warning shgdprdm-not-valid"><strong>WARNING!</strong></p>';
    $html .= '<br>';
    $html .= '<p>User\'s records associated with this Email Address were previously deleted.</p>';
    $html .= '<br>';
    $html .= '<p>Subsequent Record Synchronisation actions were not completed by the Administrator.</p>';
    $html .= '<br>';
    $html .= '<p>You are advised to contact the User to discuss prior to processing this request</p>';
    $html .= '<br>';
    $html .= '<p style="font-weight:100;"><em>Ref: shgdprdm_DRH_001</em></p>';
    $html .= '</div>';
    $html .= '</div>';
    return $html;
}



function shgdprdm_buildSubmitAccessArrays($type)
{
    if ($type == 'sh_dev') {
        $AccArr = array(1, 2, 3, 4, 5, 6); // Developer
        $exportBtnGrp = "";
        $actionBtnGrp = "";
    } elseif ($type == 1) {
        $AccArr = array(5, 6); // Admin
        // $exportBtnGrp = " class='hidden' ";
        $exportBtnGrp = "";
        $actionBtnGrp = "";
    } elseif ($type == 2) {
        $AccArr = array(1, 2, 3); // User - Export
        $exportBtnGrp = "";
        $actionBtnGrp = " class='hidden' ";
    } elseif ($type == 3) {
        $AccArr = array(1, 2, 3, 4); // User - Delete
        $exportBtnGrp = "";
        $actionBtnGrp = " class='hidden' ";
    } elseif ($type == 4) {
        $AccArr = array(5, 6); // admin snyc record
        $exportBtnGrp = " class='hidden' ";
        $actionBtnGrp = "";
    }


    return array('Access' => $AccArr, 'Export-Btn' => $exportBtnGrp, 'Action-Btn' => $actionBtnGrp);
}


function shgdprdm_makeRecordsActionButtons($data, $details, $type, $userNotice = false)
{
    $detailsID = $details->ID;
    $detailsEmail = $details->Email;


    if (isset($type)) {
        $AccArr = shgdprdm_buildSubmitAccessArrays($type);
    } else {
        return;
        // $AccArr = array(1, 2, 3, 4, 5, 6);
    }
    ?>

    <div>
    <div><hr></div>

    <?php
    if ($userNotice) {
        echo "<br>";
        echo shgdprdm_makeDisasterSyncInconsistError();
    }
    ?>

    <div id="shgdprdm-download-data-btn-group">
        <form id="shgdprdm_dl_group" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>?action=shgdprdm_export_action">
            <input type="hidden" id="shgdprdm_uid" name ="shgdprdm_uid" value="<?php echo $detailsID; ?>"/>
            <input type="hidden" id="shgdprdm_user_email" name ="shgdprdm_user_email" value="<?php echo $detailsEmail; ?>"/>
            <?php
            if (in_array(5, $AccArr['Access']) || in_array(6, $AccArr['Access'])) {
                if (strtoupper($detailsID) == 'GUEST') {
                    $orderIDs = array();
                    foreach ($data as $post => $item) {
                        array_push($orderIDs, $item->ID);
                    }
                    $orderIDs = base64_encode(serialize($orderIDs)); ?>
                    <input type="hidden" id="shgdprdm_oids" name ="shgdprdm_oids" value="<?php echo $orderIDs; ?>"/>
                    <?php
                }
            }
            ?>

            <div id="shgdprdm-export-btn-group" <?php echo $AccArr['Export-Btn']; ?>>
                <?php
                $acVal = 1;
                if (in_array($acVal, $AccArr['Access'])) {
                    $btnText = sprintf(
                        esc_html__(
                            'Download %s',
                            'seahorse-gdpr-data-manager'
                        ),
                        'XML'
                    );
                    $btnHoverText = sprintf(
                        esc_html__(
                            'Download Format: %s',
                            'seahorse-gdpr-data-manager'
                        ),
                        'XML'
                    );
                    $attr = array( 'id' => 'shgdprdm_export_xml', 'title' => $btnHoverText);
                    submit_button($btnText, 'primary', 'shgdprdm_export_xml', false, $attr);
                }
                    
                $acVal = 2;
                if (in_array($acVal, $AccArr['Access'])) {
                    $btnText = sprintf(
                        esc_html__(
                            'Download %s',
                            'seahorse-gdpr-data-manager'
                        ),
                        'CSV'
                    );
                    $btnHoverText = sprintf(
                        esc_html__(
                            'Download Format: %s',
                            'seahorse-gdpr-data-manager'
                        ),
                        'CSV'
                    );
                    $attr = array( 'id' => 'shgdprdm_export_xml', 'title' => $btnHoverText);
                    submit_button($btnText, 'primary', 'shgdprdm_export_csv', false, $attr);
                }

                $acVal = 3;
                if (in_array($acVal, $AccArr['Access'])) {
                    $btnText = sprintf(
                        esc_html__(
                            'Download %s',
                            'seahorse-gdpr-data-manager'
                        ),
                        'JSON'
                    );
                    $btnHoverText = sprintf(
                        esc_html__(
                            'Download Format: %s',
                            'seahorse-gdpr-data-manager'
                        ),
                        'JSON'
                    );
                    $attr = array( 'id' => 'shgdprdm_export_json', 'title' => $btnHoverText);
                    submit_button($btnText, 'primary', 'shgdprdm_export_json', false, $attr);
                }
                    
                $acVal = 4;
                if (in_array($acVal, $AccArr['Access'])) {
                    $btnText = esc_html__(
                        'Delete User',
                        'seahorse-gdpr-data-manager'
                    );
                    $btnHoverText = sprintf(
                        esc_html__(
                            'Download & Delete Data for ID %s',
                            'seahorse-gdpr-data-manager'
                        ),
                        $detailsID
                    );
                    $attr = array( 'id' => 'shgdprdm_delete_user', 'title' => $btnHoverText);
                    submit_button($btnText, 'shgdprdm_delete_user', 'shgdprdm_delete_user', false, $attr);
                }
                ?>

                <p style="clear:both"></p>
            </div>

            <div id="shgdprdm-verify-action-data-btn-group" <?php echo $AccArr['Action-Btn']; ?>>
                <?php
                $acVal = 5;
                if (in_array($acVal, $AccArr['Access'])) {
                    $attr = array( 'id' => 'shgdprdm_verify_export', 'title' => 'Verify Data Export for ID '.$detailsID); ?>
                    <div id="shgdprdm-verify-export-data-btn-container" <?php echo $AccArr['Export-Btn']; ?>>
                    <input type="hidden" id="shgdprdm_verify_export_val" name ="shgdprdm_verify_export_val" value="<?php echo $acVal; ?>"/>
                    <?php submit_button('Verify Export', 'primary', 'shgdprdm_verify_export', false, $attr); ?>
                    </div>
                    <?php
                }
                
                $acVal = 6;
                if (in_array($acVal, $AccArr['Access'])) {
                    $attr = array( 'id' => 'shgdprdm_verify_delete', 'title' => 'Verify Data Deletion for ID '.$detailsID); ?>
                    <div id="shgdprdm-verify-delete-data-btn-container">
                    <input type="hidden" id="shgdprdm_verify_delete_val" name ="shgdprdm_verify_delete_val" value="<?php echo $acVal; ?>"/>
                    <?php submit_button('Verify Delete', 'shgdprdm_verify_delete', 'shgdprdm_verify_delete', false, $attr); ?>
                    </div>
                    <?php
                }
                ?>

                <p style="clear:both"></p>
            </div>
        </form>
    </div>

    <?php
}
