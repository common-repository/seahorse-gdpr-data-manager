<?php
defined('SHGDPRDM_ABSPATH') or die('You are not authorised to access this file.');

// SH NOTE: add in the condition re: must come from file

// Make CSV
function shgdprdm_makeCsvFile($data)
{
    $csv_out = array();
    $dataCount = count($data);
    $tcount = count($data['tableDetails']);

    foreach ($data['tableDetails'] as $tName => $tData) {
        // Confirm Args is an array &&  Confirm Args has data to itterate over
        if (is_array($tData) && (count($tData) > 0)) {
            // Confirm Args has data to itterate over (sublevel) && Confirm Args (sub level) is an object
            $keysZero = array_keys($tData);
            $zeroKey = $keysZero[0];
            $dataZero = $tData[$zeroKey];
            if (!empty($dataZero) && is_object($dataZero)) {
                $dataObjects = get_object_vars($dataZero);
                $dataObjectsKeys = array_keys($dataObjects);
                $dataObjectsKeysZero = $dataObjectsKeys[0];
                if ($dataZero->$dataObjectsKeysZero) {
                    $output = array();
                    $headers = array();
                    $rows = array();
                    $detailsCount = 0;
                    foreach ($tData as $col => $details) {
                        $rowsString = '';
                        $itemCount = 1;
                        $totalItems = count((array)$details);
                        foreach ($details as $name => $contents) {
                            if (is_array($contents)) {
                                foreach ($contents as $label => $contentData) {
                                    if ($detailsCount == 0) {
                                        array_push($headers, $label);
                                    }

                                    // if(!is_array($contentData)) {
                                    // $contentData = strip_tags($contentData);
                                    // }
                                    // $rowsString .= htmlentities(shgdprdm_formatCSV($contentData)).",";

                                    if (is_array($contentData)) {
                                        foreach ($contentData as $contentKey => $contentVal) {
                                            $contentData[htmlentities(shgdprdm_formatCSV($contentKey))] = htmlentities(shgdprdm_formatCSV($contentVal));
                                            unset($contentData[$contentKey]);
                                        }
                                        $rowsString .= json_encode($contentData).",";
                                    } else {
                                        $rowsString .= htmlentities(shgdprdm_formatCSV($contentData)).",";
                                    }
                                }
                                $rowsString = substr($rowsString, 0, -1);
                            } else {
                                if ($detailsCount == 0) {
                                    array_push($headers, $name);
                                }
                                $rowsString .= htmlentities(shgdprdm_formatCSV($contents));
                            }
                            $rowsString .=  ",";
                        }
                        $rowsString = substr($rowsString, 0, -1);
                        $rows[$detailsCount] = $rowsString;
                        $detailsCount++;
                    }
                    $output['headers'] = $headers;
                    $output['rows'] = $rows;
                    $csv_out[$tName] = $output;
                }
            }
        }
    }
    $csvcount = count($csv_out);
    return $csv_out;
}

function shgdprdm_formatCSV($content)
{
    $content = str_replace(',', '&#x2c', $content);
    return $content;
}


// function defination to convert array to xml
function shgdprdm_arrayToXml($table, $data, &$xml_data)
{
    foreach ($data as $key => $value) {
        if (is_numeric($key)) {
            $key = 'item'.$key; //dealing with <0/>..<n/> issues
        }
        // Strip spaces from keys
        $key = str_replace(' ', '', $key);
        if ($table == 'WooCommerce' && !is_array($value)) {
            $value = strip_tags($value); //
        }
        if (is_array($value)) {
            $subnode = $xml_data->addChild($key);
            shgdprdm_arrayToXml($table, $value, $subnode);
        } else {
            $xml_data->addChild("$key", htmlspecialchars("$value"));
        }
    }
}

function shgdprdm_makeXmlFile($data = null)
{
    // initializing or creating array
    if (!$data) {
        $data = array('tableDetails' => array('Failed' =>array(0 =>'No Data')));
    }
    // creating object of SimpleXMLElement
    $xml_data = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><data></data>');

    // function call to convert array to xml
    if (isset($data['tableDetails']['shgdprdm_tableCount'])) {
        unset($data['tableDetails']['shgdprdm_tableCount']);
    }
    if (isset($data['tableDetails']['shgdprdm_dataTableCount'])) {
        unset($data['tableDetails']['shgdprdm_dataTableCount']);
    }
    foreach ($data['tableDetails'] as $tblName => $tblData) {
        $tblCount = count($tblData);
        if ($tblCount > 0) {
            foreach ($tblData as $row => $rowData) {
                shgdprdm_arrayToXml($tblName, $rowData, $xml_data);
            }
        }
    }
    //saving generated xml file;
    $tempFileName = get_temp_dir()."export.xml";
    $result = $xml_data->asXML($tempFileName);
    return $tempFileName;
}


function shgdprdm_makeJsonFile($data)
{
    if ($data) {
        if (isset($data['tableDetails']['shgdprdm_tableCount'])) {
            unset($data['tableDetails']['shgdprdm_tableCount']);
        }
        if (isset($data['tableDetails']['shgdprdm_dataTableCount'])) {
            unset($data['tableDetails']['shgdprdm_dataTableCount']);
        }
        $jArray = array();
        $jCount = 1;
        foreach ($data['tableDetails'] as $tblName => $tblData) {
            $tblCount = count($tblData);
            if ($tblCount > 0) {
                if ($tblName == 'WooCommerce' && !is_array($tblData)) {
                    $tblData = strip_tags($tblData); //
                }
                if ($tblName == 'WooCommerce' && is_array($tblData)) {
                    foreach ($tblData as $kData => $vData) {
                        $vData = (array)$vData;
                        foreach ($vData as $name => $val) {
                            if (is_array($val)) {
                                foreach ($val as $subName => $subVal) {
                                    $val[$subName] = strip_tags($subVal);
                                }
                                $vData[$name] = $val;
                            } else {
                                $vData[$name] = strip_tags($val);
                            }
                        }
                        $vData = (object)$vData;
                        $tblData[$kData] = $vData;
                    }
                }
                $jArray['t'.$jCount] = $tblData;
                $jCount++;
            }
        }
        return $jData = json_encode($jArray);
    } else {
        $jData = json_encode(array('failed' => 'no data'));
        return false;
    }
}

function shgdprdm_writeJsonFile($jData)
{
    $tempFileName = get_temp_dir()."export.json";
    $filePath = fopen($tempFileName, 'w');
    fwrite($filePath, $jData);
    fclose($filePath);
    return $tempFileName;
}


function shgdprdm_buildExtSubmitAccessArrays($type)
{
    if ($type == 5) {
        $AccArr = array(1, 2, 3);
    } elseif ($type == 6) {
        $AccArr = array(1, 2, 3, 4);
    }

    return $AccArr;
}

function shgdprdm_makeExtActionButtons($type, $user, $location)
{
    if (isset($type)) {
        $AccArr = shgdprdm_buildExtSubmitAccessArrays($type);
    } else {
        return;
    }

    $html = '';
    $html .= '<form id="shgdprdm_dl_group" method="post" action="/gdpr-data-manager/verify/'.$location.'">';
    $html .= '<input type="hidden" id="shgdprdm_user_email" name ="shgdprdm_user_email" value="'.$user.'"/>
        <input type="hidden" id="shgdprdm_expRd" name ="shgdprdm_expRd" value="'.$location.'"/>';
    $acVal = 1;
    if (in_array($acVal, $AccArr)) {
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
        $html .= '<input type="submit" name="shgdprdm_export_xml" id="shgdprdm_export_xml" class="button shgdprdm-usr-btn shgdprdm-usr-btn-regular" value="' . $btnText . '" title="' . $btnHoverText . '"  />';
    }
    $acVal = 2;
    if (in_array($acVal, $AccArr)) {
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
        $html .= '<input type="submit" name="shgdprdm_export_csv" id="shgdprdm_export_csv" class="button shgdprdm-usr-btn shgdprdm-usr-btn-regular" value="' . $btnText . '" title="' . $btnHoverText . '"  />';
    }
    $acVal = 3;
    if (in_array($acVal, $AccArr)) {
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
        $html .= '<input type="submit" name="shgdprdm_export_json" id="shgdprdm_export_json" class="button shgdprdm-usr-btn shgdprdm-usr-btn-regular" value="' . $btnText . '" title="' . $btnHoverText . '"  />';
    }
    $acVal = 4;
    if (in_array($acVal, $AccArr)) {
        $btnText = esc_html__(
            'Delete All Data',
            'seahorse-gdpr-data-manager'
        );
        $btnHoverText = sprintf(
            esc_html__(
                'Download %s Delete Data for Email %s',
                'seahorse-gdpr-data-manager'
            ),
            $this->ue
        );
        $html .= '<input type="submit" name="shgdprdm_delete_user" id="shgdprdm_delete_user" class="button  shgdprdm-usr-btn shgdprdm-usr-btn-delete shgdprdm_delete_user" value="' . $btnText . '" title="' . $btnHoverText . '"  />';
    }
    $html .= '</form>';

    return $html;
}

// This updates the initial click
function shgdprdm_updateDbPendingStatus($ra, $at, $ue, $guestOrders = array())
{
    global $wpdb;
    $table = $wpdb->prefix.'shgdprdm_history';

    //check if valid
    $userID = shgdprdm_getRequestStatus($ra, $at, $ue);

    if (count($userID) == 0) {
        // wp_die('FAIL 1 - '.__FILE__.' | LIne: '.__LINE__);
        return false;
    } else {
        /// update verify status in db


        $isDisasterSync = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT disasterSync
                FROM {$table}
                WHERE userEmail = %s
                AND actionType = %d
                AND disasterSync = %d
                AND randString = %s",
                array($ue, '6' , '1', $ra)
            )
        );

        // If disaster Recovery
        if ($isDisasterSync == 1) {
            // $actV = 6;
            $actV = 104;
        } else {
            $actV = 2;
        }

        // Update V2 - Add record to Dynamo at each step
        $changeRows = 0;
        $rowID = null;

        // Get number of rows affected
        $changeRows = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$table}
                WHERE randString = %s AND actionType = %d AND userEmail = %s",
                array($ra, $at, $ue)
            )
        );

        // Udpate DB if there is a row to be updated
        // Otehrwise return FALSE
        if ($changeRows > 0 && $changeRows < 2) {
            $row = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT * FROM {$table}
                    WHERE randString = %s AND actionType = %d AND userEmail = %s",
                    array($ra, $at, $ue)
                ),
                ARRAY_N
            );

            if (!empty($row)) {
                if (($isDisasterSync==1 && $row[6] == '5') || ($isDisasterSync==1 && $row[6] == '103') || ($isDisasterSync!=1 && $row[6] == '1')) {
                    // Update the Remote DB
                    $userWhoData = 'Unknown';
                    if (is_numeric($row[2])) {
                        $userWhoData = $row[2];
                    }
                    if (strtoupper($row[2])=='GUEST' && !empty($guestOrders)) {
                        $userWhoData = $guestOrders;
                    }
                    $updateTime = date('Y-m-d H:i:s');
                    $updateData = array(
                        'shgdprdm_rid' => $row[0],
                        'shgdprdm_awhat' => $row[1],
                        'shgdprdm_uwho' => $userWhoData,
                        'shgdprdm_awho' => $row[4],
                        'shgdprdm_awhen' => $row[5],
                        'shgdprdm_uwhat' => (($isDisasterSync==1) ? 104 : 2),
                        // 'shgdprdm_uwhat' => ( ($isDisasterSync==1) ? 6 : 2 ), legacy
                        'shgdprdm_uwhen' => $updateTime
                    );
                    // print_r($updateData);
                    // Update Dynamo DB
                    if (shgdprdm_setReviewHistoryExternalData($updateData)) {
                        // Udpate & Return Local DB
                        $localUpdate = $wpdb->update($table, array( 'actionVerify' => $actV, 'actionVerifyTimestamp' => $updateTime ), array( 'randString' => $ra, 'actionType' => $at, 'userEmail' => $ue));
                        // wp_die('FAIL 2 - '.__FILE__.' | LIne: '.__LINE__);
                        return $localUpdate;
                    }
                    // else{
        //   exit('Failed to update remote DB');
        // }
                }
                // wp_die('FAIL 3 - '.__FILE__.' | LIne: '.__LINE__);
                return true;
            }
            // wp_die('FAIL 4 - '.__FILE__.' | LIne: '.__LINE__);
            return false;
        }
        // wp_die('FAIL 5 - Change Rows: '.$changeRows.' | '.__FILE__.' | LIne: '.__LINE__);
        return false;
        // return $wpdb->update( $table, array( 'actionVerify' => $actV, 'actionVerifyTimestamp' => date('Y-m-d H:i:s') ), array( 'randString' => $ra, 'actionType' => $at, 'userEmail' => $ue) );
    }
}

function shgdprdm_updateDbVerifyAction($ra, $ue, $updateVal, $updateAction = null, $guestOrders = array())
{
    if (!$updateAction) {
        wp_die(shgdprdm_userFailureRedirectNotice('EXH', '001'));
        // wp_die('Error Udpating Action: '.__FILE__.' | '.__LINE__);
    }
    if (!$updateVal) {
        wp_die(shgdprdm_userFailureRedirectNotice('EXH', '002'));
    }

    global $wpdb;
    $table = $wpdb->prefix.'shgdprdm_history';

    $isDisasterSync = $wpdb->get_var($wpdb->prepare("SELECT disasterSync FROM {$table}
  WHERE userEmail = %s AND actionType = %d AND (disasterSync = %d OR disasterSync = %d ) AND randString = %s", array($ue, 6 , 1, 2, $ra)));

    // Update V2 - Add record to Dynamo at each step

    $typeSearchBy = $updateAction;
    if ($isDisasterSync == 1 || $isDisasterSync == 2) {
        $typeSearchBy = 6;
    }

    $changeRows = 0;
    $rowID = null;

    // Get number of rows affected
    $changeRows = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$table}
  WHERE randString = %s AND actionType = %d AND userEmail = %s", array($ra, $typeSearchBy, $ue)));

    // Udpate DB if there is a row to be updated
    // Otehrwise return FALSE
    if ($changeRows > 0 && $changeRows < 2) {
        $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table}
    WHERE randString = %s AND actionType = %d AND userEmail = %s", array($ra, $typeSearchBy, $ue)), ARRAY_N);

        if (!empty($row)) {
            // Update the Remote DB
            $userWhoData = 'Unknown';
            if (is_numeric($row[2])) {
                $userWhoData = $row[2];
            }
            if (strtoupper($row[2])=='GUEST' && !empty($guestOrders)) {
                $userWhoData = $guestOrders;
            }
            $updateTime = date('Y-m-d H:i:s');
            $updateData = array(
                'shgdprdm_rid' => $row[0],
                'shgdprdm_awhat' => $row[1],
                'shgdprdm_uwho' => $userWhoData,
                'shgdprdm_awho' => $row[4],
                'shgdprdm_awhen' => $row[5],
                'shgdprdm_uwhat' => $updateVal,
                'shgdprdm_uwhen' => $updateTime
            );
            // exit(print_r($updateData));
            // Update Dynamo DB
            if (shgdprdm_setReviewHistoryExternalData($updateData)) {
                // Udpate & Return Local DB
                $typeUpdateBy = $typeSearchBy;
                $updateVals = array( 'actionVerify' => $updateVal, 'actionVerifyTimestamp' => $updateTime );
                if ($isDisasterSync == 1 || $isDisasterSync == 2) {
                    $updateVals['disasterSync'] = 2;
                }
                $localUpdate = $wpdb->update($table, $updateVals, array( 'randString' => $ra, 'actionType' => $typeUpdateBy, 'userEmail' => $ue));
                // wp_die('FAIL 2 - '.__FILE__.' | LIne: '.__LINE__);
                return $localUpdate;
            }
            wp_die('FAIL 3 - '.__FILE__.' | LIne: '.__LINE__);
            return false;
        }
        wp_die('FAIL 4 - '.__FILE__.' | LIne: '.__LINE__);
        return false;
    }
    wp_die('FAIL 5 - Change Rows: '.$changeRows.' | '.__FILE__.' | LIne: '.__LINE__);
    return false;



    // if($isDisasterSync == 1 || $isDisasterSync == 2){
  //   return $wpdb->update( $table, array( 'actionVerify' => $updateVal, 'actionVerifyTimestamp' => date('Y-m-d H:i:s'), 'disasterSync' => 2 ), array( 'randString' => $ra, 'actionType' => 6, 'userEmail' => $ue) );
  // }
  // return $wpdb->update( $table, array( 'actionVerify' => $updateVal, 'actionVerifyTimestamp' => date('Y-m-d H:i:s') ), array( 'randString' => $ra, 'actionType' => $updateAction, 'userEmail' => $ue) );
}


function shgdprdm_getRequestStatus($ra, $at, $ue)
{
    if (!$ra) {
        wp_die(shgdprdm_userFailureRedirectNotice('EXH', '003'));
    }
    if (!$at) {
        wp_die(shgdprdm_userFailureRedirectNotice('EXH', '004'));
    }
    if (!$ue) {
        wp_die(shgdprdm_userFailureRedirectNotice('EXH', '005'));
    }


    global $wpdb;
    $table = $wpdb->prefix.'shgdprdm_history';

    // If disaster Recovery
    $isDisasterSync = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT disasterSync
            FROM {$table}
            WHERE userEmail = %s
            AND actionType = %d
            AND (disasterSync = %d OR disasterSync = %d )
            AND randString = %s",
            array($ue, 6 , 1, 2, $ra)
        )
    );
    // wp_die('Disaster Sync: '.$isDisasterSync);

    // Test for page reload after delete is complete
    if ($isDisasterSync == 1 || $isDisasterSync == 2) {
        $data = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT userID, userEmail, actionVerify
                FROM {$table}
                WHERE randString = %s
                AND userEmail = %s
                AND DATE_SUB(actionTimestamp, INTERVAL 24 HOUR) < NOW()
                AND (
                (actionVerify = %d AND disasterSync = %d )
                OR (actionVerify = %d AND disasterSync = %d)
                OR (actionVerify = %d AND disasterSync = %d)
                OR (actionVerify = %d AND disasterSync = %d)
                )",
                array($ra, $ue, 5, 1, 9, 2, 103, 1, 106, 2)
            )
        );
    // OR (actionVerify = %d AND disasterSync = %d)
     // array($ra, $ue, 5, 1, 8, 2, 9, 2 )
    } else {
        //// check if db time is valid: actionTimestamp <= actionTimestamp + 24 hours AND that it has not already been actioned
        $data = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT userID, userEmail, actionVerify
                FROM {$table}
                WHERE randString = %s
                AND userEmail = %s
                AND DATE_SUB(actionTimestamp, INTERVAL 24 HOUR) < NOW()
                AND ( actionVerify = %d OR actionVerify = %d)",
                array($ra, $ue, 1, 8)
            )
        );
    }

    if (count($data) <> 1) {
        // exit( "Error: No Data returned from Veriiction of Link");
        return false;
    }

    // TESTING
    // $disTest = $wpdb->get_var( $wpdb->prepare("SELECT disasterSync FROM {$table}
    // WHERE userEmail = %s AND actionType = %d AND randString = %s", array($ue, 6 , $ra) ) );
    //
    // $actVTest = $wpdb->get_var( $wpdb->prepare("SELECT actionVerify FROM {$table}
    // WHERE userEmail = %s AND actionType = %d AND randString = %s", array($ue, 6 , $ra) ) );
    //
    // wp_die( shgdprdm_userFailureRedirectNotice( 'EXH', '006').' | '.$disTest.' | '.$actVTest );


    if (is_numeric($data[0]->userID)) {
        return $return = array('userRef' => $data[0]->userID, 'status' => $data[0]->actionVerify) ;
    } else {
        return $return = array('userRef' => $data[0]->userEmail, 'status' => $data[0]->actionVerify) ;
    }
}

function shgdprdm_siteLogo()
{
    // $logo = (null !== get_theme_mod( 'header_logo_url')) ? get_theme_mod( 'header_logo_url') : '';
    // if ( $logo ) {
    //   return "<p><img src='".get_theme_mod( 'header_logo_url')."'/></p>";
    // } else {
    //   return '<h1>'. get_bloginfo( 'name' ) .'</h1>';
    // }

    // return serialize(get_theme_mods());

    $custom_logo_id =  (null !== get_theme_mod('custom_logo')) ? get_theme_mod('custom_logo') : '';
    if ($custom_logo_id) {
        $logo = wp_get_attachment_image_src($custom_logo_id, 'full');
        if (has_custom_logo()) {
            return "<p><img src='". esc_url($logo[0]) ."' alt='".get_bloginfo('name')." Logo'/></p>";
        } else {
            return '<h1>'. get_bloginfo('name') .'</h1>';
        }
    }

    $header_logo = (null !== get_theme_mod('header_logo_url')) ? get_theme_mod('header_logo_url') : '';
    if ($header_logo) {
        return  "<p><img src='".esc_url(wp_sprintf($header_logo, get_stylesheet_directory_uri()))."' alt='".get_bloginfo('name')." Logo'/></p>";
    } else {
        return '<h1>'. get_bloginfo('name') .'</h1>';
    }
}

//function to check if the non-admin origin file-path is as expected
function shgdprdm_validateRqstogn($rqstogn)
{
    return get_option('shgdprdm_user_path').'classes/shgdprdm_userExport.class.php' === $rqstogn?true:false;
}

function shgdprdm_getdisasterSyncVal($shgdprdm_user_email, $shgdprdm_verify_delete_val, $ra = null)
{
    global $wpdb;
    $table = $wpdb->prefix.'shgdprdm_history';
    $disasterSyncVal = null;
    if ($ra != null) {
    // $disasterSync = $wpdb->get_var( $wpdb->prepare("SELECT disasterSync FROM {$table}
        // WHERE userEmail = %s AND actionType = %d AND (disasterSync = %d OR disasterSync = %d ) AND randString = %s", array($shgdprdm_user_email, $shgdprdm_verify_delete_val, 1, 2, $ra) ) );

        $disasterSync = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT disasterSync, actionTimestamp
                FROM {$table}
                WHERE userEmail = %s
                AND actionType = %d
                AND (disasterSync = %d OR disasterSync = %d )
                AND randString = %s
                ORDER BY actionTimestamp DESC",
                array($shgdprdm_user_email, $shgdprdm_verify_delete_val, 1, 2, $ra)
            )
        );
    } else {
        // $disasterSync = $wpdb->get_var( $wpdb->prepare("SELECT disasterSync FROM {$table}
        //   WHERE userEmail = %s AND actionType = %d AND disasterSync = %d AND actionVerify = %d", array($shgdprdm_user_email, $shgdprdm_verify_delete_val, 1, 4) ) );
        $disasterSync = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT disasterSync, actionTimestamp
                FROM {$table}
                WHERE userEmail = %s
                AND actionType = %d
                AND disasterSync = %d
                AND (actionVerify = %d OR actionVerify = %d )",
                array($shgdprdm_user_email, $shgdprdm_verify_delete_val, 1, 4, 100)
            )
        );
    }


    if (count($disasterSync) > 0) {
        $date = $disasterSync[0]->actionTimestamp;

        $user = get_user_by('email', $shgdprdm_user_email);
        if ($user) {
            $regD = $user->user_registered;
            if ($regD) {
                if ($date >=  $regD) {
                    $disasterSyncVal = $disasterSync[0]->disasterSync;
                }
            }
        } else {
            // This needs to be updated for WooCommerce Guests
            $disasterSyncVal = $disasterSync[0]->disasterSync;
        }
    }
    return $disasterSyncVal;

    // return $disasterSync;
}
