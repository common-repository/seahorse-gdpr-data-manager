<?php
defined('SHGDPRDM_ABSPATH') or die('You are not authorised to access this file.');
// Functions for history/review page

// Print the Intro text
function shgdprdm_reviewHistorySectionText() {
echo '<p>Details of previously Exported & Deleted Data activity</p>';
}

// Print the data table
function shgdprdm_getReviewHistoryData($externalRecordCount) {
  try{
    $activityHistoryTable = new SHGdprdm_HistoryList($externalRecordCount);
    $display = $activityHistoryTable->shgdprdm_reviewHistoryTable();
    $recordCount = $activityHistoryTable->shgdprdm_getRecordCount();
    return $return = array('recordCount' => $recordCount);
  }
  catch(Exception $e){
    echo '<p>Warning! '.$e->getMessage().'</p>';
    return;
  }
}

// Call AWS (get)
function shgdprdm_getReviewHistoryExternalData() {
  $ref = null;
  if(false !== get_option('shgdprdm_adminVerifyLicence') &&
      isset(get_option('shgdprdm_adminVerifyLicence')['licence_number']) &&
      get_option('shgdprdm_adminVerifyLicence')['licence_number'] != '' &&
      isset(get_option('shgdprdm_adminVerifyLicence')['licence_valid']) &&
      get_option('shgdprdm_adminVerifyLicence')['licence_valid'] == 'valid' ){
    $ref = get_option('shgdprdm_adminVerifyLicence')['licence_number'];
  }
  if($ref){


  $response = wp_remote_request( 'https://nmpnvr9xz9.execute-api.eu-west-1.amazonaws.com/dev/SHGdprDm_History',
    array(
      'ssl_verify' => true,
      'method'     => 'GET',
      'headers' => array('X-API-KEY' => 'izE7gj1NNa2dMwxG8W4u32LxGxY4x8ZM7veRD5iL', 'name' => $ref),
      'body' => array('shgdprdm_reglcn' => $ref)
      )
    );
  // Check for Error
  $rCode = wp_remote_retrieve_response_code( $response );
  $rStatus = wp_remote_retrieve_response_message( $response );
  $rHeaders = wp_remote_retrieve_headers( $response );
  $rBody = wp_remote_retrieve_body( $response );
  $rContentType = wp_remote_retrieve_header( $response, 'content-type' );
  $isError = shgdprdm_getReviewHistoryResponseError($rCode, $rStatus);

  if( !$isError ){
    // Check if response is JSON & convert
    if(strpos( $rContentType, 'json' ) > -1){
      $rBody = json_decode( $rBody );
    }
    else{
      $isError = "<p>ERROR!<br>Data cannot be Retrieved</p>";
    }
  }

  if( !$isError ){
    $isError = shgdprdm_getReviewHistoryExternalError( $rBody );
  }

  if( !$isError ){
    return shgdprdm_makeReviewHistoryExternal($rBody);
    echo "<p style='padding-left:15px;'>Response Text: <br><strong>SUCCESS</strong></p>";
  }
  else{
    echo $isError;
  }
  echo "<p style='padding-left:15px;'>Response Text: <br><strong>FINISHED</strong></p>";
  }
  else{
    echo "Invalid or Expired Licence Number";
  }
}

// Call AWS (set)
function shgdprdm_setReviewHistoryExternalData($shgdprdmArray = array(), $createNew=FALSE) {
  // $shgdprdmSet = array(
  //   'shgdprdm_what' => 2,
  //   'shgdprdm_when' => date('Y-m-d H:i:s'),
  //   'shgdprdm_awho' => 4,
  //   'shgdprdm_bwho' => 'test.put@gmail.com',
  //   'shgdprdm_cwho' => 7
  // );
  $ref = null;
  if(false !== get_option('shgdprdm_adminVerifyLicence') &&
      isset(get_option('shgdprdm_adminVerifyLicence')['licence_number']) &&
      get_option('shgdprdm_adminVerifyLicence')['licence_number'] != '' &&
      isset(get_option('shgdprdm_adminVerifyLicence')['licence_valid']) &&
      get_option('shgdprdm_adminVerifyLicence')['licence_valid'] == 'valid' ){
    $ref = get_option('shgdprdm_adminVerifyLicence')['licence_number'];
  }
  if( $ref) {
    // $shgdprdmArray  = array(
    //     'shgdprdm_reglcn' => $ref,
    //     'shgdprdm_awhat' => 1,
    //     'shgdprdm_uwho' => 22,
    //     'shgdprdm_awho' => 3,
    //     'shgdprdm_awhen' => date('Y-m-d H:i:s'),
    //     'shgdprdm_uwhat' => 2,
    //     'shgdprdm_uwhen' => ''
    //   );

    if ( $createNew ){
      $shgdprdmArray =array();
      $shgdprdmArray['shgdprdm_regNew'] = $ref;
    }
    else {
      if( empty($shgdprdmArray ) ){
        echo "Invalid Data";
        return;
      }
    }

    if( !empty($shgdprdmArray) ){
      // else{
      //   $shgdprdmSetUpdate  = array(
      //     'shgdprdm_liId',
      //     'shgdprdm_awhat',
      //     'shgdprdm_uwho',
      //     'shgdprdm_awho',
      //     'shgdprdm_awhen',
      //     'shgdprdm_uwhat',
      //     'shgdprdm_uwhen'
      //   );
      // }
      $shgdprdmArray['shgdprdm_reglcn'] = $ref;
      $shgdprdmJson = "'".json_encode($shgdprdmArray )."'";

      $response = wp_remote_request( 'https://nmpnvr9xz9.execute-api.eu-west-1.amazonaws.com/dev/SHGdprDm_History', array(
        'ssl_verify' => true,
        'method'     => 'PUT',
        'body' => $shgdprdmJson,
        'headers' => array('X-API-KEY' => 'U3RF9KGMeu6ppUx7ZuaOI2QtmHmUyrM77BdV6tmE', 'Content-Type' => 'application/json')
      ) );
      // Check for Error
      $rCode = wp_remote_retrieve_response_code( $response );
      $rStatus = wp_remote_retrieve_response_message( $response );
      $rHeaders = wp_remote_retrieve_headers( $response );
      $rBody = wp_remote_retrieve_body( $response );
      $rContentType = wp_remote_retrieve_header( $response, 'content-type' );

      $isError = shgdprdm_getReviewHistoryResponseError($rCode, $rStatus);

      if( !$isError ){
        // Check if response is JSON & convert
        if(strpos( $rContentType, 'json' ) > -1){
          $rBody = json_decode( $rBody );
        }
        else{
          $isError = "<p>ERROR!<br>Data cannot be Added at this time</p>";
          return FALSE;
        }
        /* If invalid JSON, return original result body. */
        if (json_last_error() !== JSON_ERROR_NONE) {
            $isError = "<p>ERROR!<br>Data cannot be Added at this time</p>";
            return FALSE;
        }

      }
      if( !$isError ){
        $isError = shgdprdm_getReviewHistoryExternalError( $rBody );
      }

      if(!$isError){
        return shgdprdm_getReviewHistoryExternalInsertResponse( $rBody );
      }
      else{
        return $isError;
      }
    }
  }
  else{
    return FALSE;
  }
}


// Check for errors from AWS request
function shgdprdm_getReviewHistoryResponseError($rCode, $rStatus) {
  // Check for Http Response Errors from API
  if( $rCode != '200' && strtoupper($rStatus) != 'OK' ){
    return TRUE;
  }
  return FALSE;
}

function shgdprdm_getReviewHistoryExternalError( $rBody ) {
  // Check for  Errors in the data returned from API

  // Check that there is contents
  if(!$rBody){
    return "<p>ERROR!<br>No Data Retrieved?</p>";
  }
  // Check for an error from AWS
  else if(isset($rBody->errorMessage)){
    return "<p>ERROR!<br>Message: ".$rBody->errorMessage."</p>";
  }
  // Confirm the content is an object (for proper manipulation in layout)
  else if(gettype($rBody) != 'object'){
    return "<p style='padding-left:15px;'>Response Text: <br><strong>".gettype($rBody)."</strong><br>".(gettype($rBody) == 'string' ? $rBody : '')."</p>";
  }
  else{
    return FALSE;
  }
}

function shgdprdm_makeReviewHistoryExternal($rBody) {
  $html = '';
  $output = array();
  $columns = array('exportID', 'export_type','userID','userEmail','actionedBy','actionTimestamp');
  $headers = array('exportID'=>'Ref', 'export_type'=>'Type', 'userID'=>'User Ref',
                  'userEmail'=>'User Email', 'actionedBy'=>'Admin Action', 'actionTimestamp'=>'Timestamp');

    $columns = array( 'shgdprdm_records' );
    $headers = array(
                    'shgdprdm_records'=>
                        array('Type', 'User Ref', 'Actioned By (Admin)', 'Admin Timestamp',
                          'User Action Taken','User Action Timestamp', 'Local Storage Ref.'
                        ),
                    );

    if(isset($rBody->Item) ){
      // "Single Row";
      $rBody = array($rBody->Item);
    }
    else if(isset($rBody->Items) ){
      // "Multiple Rows";
      $rBody = $rBody->Items;
    }

    if( count($rBody) == 1 ){
    $html.= "<br>";
    $html.= "<table class='wp-list-table widefat fixed striped posts'>";
    $itemCount = 0;
    $externalRefs = array();

    // Make table layout
    foreach($rBody[0]->shgdprdm_records as $row){
      if($itemCount == 0){
        $html.= "<thead><tr>";
        $html.= "<th>Ref.</th>";
        foreach($columns as $index => $cName){
          if( isset($headers[$cName]) ){
            if( is_array($headers[$cName]) ){
              foreach($headers[$cName] as $subHeading){
                $html.= "<th>".$subHeading."</th>";
              }
            }
            else{
              $html.= "<th>".$headers[$cName]."</th>";
            }
          }
        }
        $html.= "</thead></tr><tbody>";
      }
      $html.= "<tr>";
      $html.= "<td>".$itemCount.'</td>';
      foreach($columns as $cName){
        if( isset($row->$cName) ){
          if( is_object($row->$cName) || is_array($row->$cName) ){
            foreach($row->$cName as $recordData){
              foreach($recordData as $field => $data){
                $html.= "<td>".$data.'</td>';
              }
            }
          }
          else{
            $html.= "<td>".$row->$cName."</td>";
          }
        }
        else{
          if(isset($row[6])){
            array_push($externalRefs, $row[6]);
          }
          foreach($row as $recordData){
            if(  is_array($recordData) ){
              $convertToEmail = array();
              foreach($recordData as $orderId){

                $order = (function_exists('wc_get_order') ? wc_get_order(  $orderId  ) : '');
                if( $order ){
                  $orderEmail = $order->get_billing_email();
                  if( $orderEmail ){
                    if( !in_array( $orderEmail, $convertToEmail ) ){
                      array_push( $convertToEmail, $orderEmail );
                    }
                  }
                  else{
                    array_push( $convertToEmail, 'Unknown Email Ref: '.$orderId );
                  }
                }
                else{
                  array_push( $convertToEmail, 'Unknown Email Ref: '.$orderId );
                }
              }
              $html.= "<td>Woo Commerce Guest Users:";
              foreach($convertToEmail as $orderEmail){
                $html.= '<br>'.$orderEmail;
              }
              $html.= '</td>';
            }
            else{
              $html.= "<td>".$recordData.'</td>';
            }
          }
        }
      }
      $html.= "</tr>";
      $itemCount++;
    }
    $html.= "</tbody>";
    $html.= "</table>";
    $output['recordCount'] = count($rBody[0]->shgdprdm_records);
    $output['recordRefs'] = $externalRefs;
    $output['data'] = $rBody[0]->shgdprdm_records;
    }
    else{
      $html.= "<br>";
      $html.= "<p style='padding-left:15px;'><strong>No Records to Display - Invalid or Expired Licence Number</strong></p>";
      $output['recordCount'] = 0;
      $output['recordRefs'] = array();
      $output['data'] = array();
    }
    $output['display'] = $html;
    return $output;
  }

  // process the response recieved from DB update attempt
  function shgdprdm_getReviewHistoryExternalInsertResponse( $rBody ) {
    $responseType = gettype( $rBody );
    if(isset($rBody->Ref) ){
      // "Single Row";
      $rBody = array($rBody->Ref);
      if($rBody[0] == 'Updated!'){
        return TRUE;
      }
      else{
        return FALSE;
      }
    }
    else{
      return FALSE;
      $output =  "<p style='padding-left:15px;'>Testing<br><strong>".$responseType." | ";
      foreach($rBody as $a => $b){
        $output .= $b;
      }
      $output .= "</strong></p>";
      return $output;
      return "<p style='padding-left:15px;'>Error!<br><strong>There is an error in the Daabase Response.</strong></p>";
    }
  }

function shgdprdm_reviewPrettyPrint($data){
  $output = array();
  //$output = '';
  foreach($data as $row => $details){
    $output[$row] = array();
    foreach($details as $col => $val){
      if($col == 'actionType'){
        $output[$row][$col] = shgdprdm_reviewConvertType($val);
      }
      else if($col == 'actionedBy'){
        $output[$row][$col] = shgdprdm_reviewConvertAdmin($val);
      }
      else if($col == 'actionVerify'){
        $output[$row][$col] = shgdprdm_reviewConvertVerify($val);
      }
      else if( $col == 'actionVerifyTimestamp' && $val == '0000-00-00 00:00:00' ){
        $output[$row][$col] = 'User Action Pending';
      }
      else{
        $output[$row][$col] = $val;
      }
    }
  }
  return $output;
}

function shgdprdm_reviewConvertType($data){
  switch ($data) {
    case 1:
        $oData = 'CSV Export';
        break;
    case 2:
        $oData = 'XML Export';
        break;
    case 3:
        $oData = 'JSON Export';
        break;
    case 4:
        $oData = 'Delete & Export';
        break;
    case 5:
        $oData = 'Verify Export';
        break;
    case 6:
        $oData = 'Verify Delete';
        break;
    default:
        $oData = $data;
    }
    return $oData;
}

function shgdprdm_reviewConvertVerify($data){
  switch ($data) {
    case 0:
        $oData = 'Undefined';
        break;
    case 1:
        $oData = 'User Action Pending';
        break;
    case 2:
        $oData = 'Link Actioned by User';
        break;
    case 3:
        $oData = 'Link Time Elapsed';
        break;
    case 4: // Legacy
        $oData = 'Record Synced By Admin';
        break;
    case 5: // Legacy
        $oData = 'Admin Action Pending (Synced Record)';
        break;
    case 6: // Legacy
        $oData = 'Link Actioned by Admin (Sync Record)';
        break;
    case 7:
        $oData = 'User Data Downloaded By User';
        break;
    case 8: // Legacy
        $oData = 'Delete Button Actioned by User';
        break;
    case 9: // Legacy
        $oData = 'Delete Button Actioned by Admin';
        break;
    case 10:
        $oData = 'User Data Deleted By User';
        break;
    case 11:  // Legacy
        $oData = 'Record Synced & User Data Deleted By Admin';
        break;
    case 27:
        $oData = 'User Delete - Error';
        break;
    case 101: // Legacy
        $oData = 'User Delete - Error';
        break;
    case 111: // Legacy
        $oData = 'Admin Delete - Error';
        break;

    case 100:
        $oData = 'Record Synced By Admin';
        break;
    // Cannot have case 101 - This is legacy Use
    case 102:
        $oData = 'User Data Deleted By User. (Record Synced By Admin - No Data to Delete)';
        break;
    case 103:
        $oData = 'Admin Action Pending (Synced Record)';
        break;
    case 104:
        $oData = 'Link Actioned by Admin (Sync Record)';
        break;
    case 105:
        $oData = 'Link Time Elapsed (Admin)';
        break;
    case 106:
        $oData = 'Delete Button Actioned by Admin';
        break;
    case 107:
        $oData = 'Record Synced & User Data Deleted By Admin';
        break;
    case 127:
        $oData = 'Admin Delete - Error';
        break;
    default:
        $oData = $data;
    }
    return $oData;
}

function shgdprdm_reviewConvertAdmin($data){
  $uData = get_user_by( 'id', $data );
  $oData = 'unknown';
  if ( ! empty( $uData ) ) {
    $oData = $uData->user_nicename;
  }
  return $oData;
}

function shgdprdm_reviewLikeType($text){
  if( is_numeric($text) ){
    return NULL;
  }
  $addLike = '';
  if( strpos('EXPORT', strtoupper($text) ) > -1 ){
    $addLike = " OR actionType = '1' OR actionType = '2' OR actionType = '3' OR actionType = '4' OR actionType = '5' ";
  }
  else if( strpos('CSV', strtoupper($text) ) > -1 ){
    $addLike = " OR actionType = '1' ";
  }
  else if( strpos('XML', strtoupper($text) ) > -1 ){
    $addLike = " OR actionType = '2' ";
  }
  else if( strpos('JSON', strtoupper($text) ) > -1 ){
    $addLike = " OR actionType = '3' ";
  }
  else if( strpos('Delete&', strtoupper($text) ) > -1 ){
    $addLike = " OR actionType = '4' ";
  }
  else if( strpos('Verify Export', strtoupper($text) ) > -1 ){
    $addLike = " OR actionType = '5' ";
  }
  else if( strpos('Verify Delete', strtoupper($text) ) > -1 ){
    $addLike = " OR actionType = '6' ";
  }

  if($addLike){
    return $addLike;
  }
  return NULL;
}

function shgdprdm_reviewLikeAdmin($text){
  if(is_numeric($text)){
    return NULL;
  }
  $addLike = '';

  $args = array (
    // Search for the nicename text.
    'search' => $text,
    // Search the `nice_name` field only.
    'search_columns' => array( 'nice_name' ),
    // Return the `ID` field only.
    'fields' => 'ID',
  );
  // Create the WP_User_Query object
  $wp_user_query = new WP_User_Query($args);
  // Get the results
  $uData = $wp_user_query->get_results();

  if ( ! empty( $uData ) ) {
    return $addLike = " OR actionedBy = '".$uData[0]."' ";
  }
  return NULL;
}

function shgdprdm_mapLegacyRefs($ref){

  switch ($ref) {

    case 4: // 'Record Synced By Admin'
        $newRef = 100;
        break;
    case 5: // Admin Action Pending (Synced Record)
        $newRef = 103;
        break;
    case 6: // Link Actioned by Admin (Sync Record)
        $newRef = 104;
        break;
    case 9: // Delete Button Actioned by Admin
        $newRef = 106;
        break;
    case 11: // Record Synced & User Data Deleted By Admin
        $newRef = 107;
        break;
    case 111: // Admin Delete - Error
        $newRef = 127;
        break;

    case 101: // User Delete - Error
        $newRef = 27;
        break;

    default:
        $newRef = $ref;
    }
    return $newRef;
}
?>
