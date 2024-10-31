<?php
defined('SHGDPRDM_ABSPATH') or die('You are not authorised to access this file.');
// Functions for history/review page

// Print the Intro text
function shgdprdm_pendingHistorySectionText() {
echo '<p>Details of pending action requests: </p>';
}

// Print the data table
function shgdprdm_getPendingHistoryData() {
  $activityHistoryTable = new SHGdprdm_PendingList();
  $activityHistoryTable->shgdprdm_pendingHistoryTable();
}

// Call AWS (get)
function shgdprdm_getPendingHistoryExternalData() {
  $response = wp_remote_request( 'https://nmpnvr9xz9.execute-api.eu-west-1.amazonaws.com/dev/shgdprdm_history',
    array(
      'ssl_verify' => true,
      'method'     => 'GET',
      'headers' => array('X-API-KEY' => 'U3RF9KGMeu6ppUx7ZuaOI2QtmHmUyrM77BdV6tmE')
      )
    );
  // Check for Error
  $rCode = wp_remote_retrieve_response_code( $response );
  $rStatus = wp_remote_retrieve_response_message( $response );
  $rHeaders = wp_remote_retrieve_headers( $response );
  $rBody = wp_remote_retrieve_body( $response );
  $rContentType = wp_remote_retrieve_header( $response, 'content-type' );

  $isError = shgdprdm_getPendingHistoryResponseError($rCode, $rStatus);

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
    $isError = shgdprdm_getPendingHistoryExternalError( $rBody );
  }

  if( !$isError ){
    echo shgdprdm_makePendingHistoryExternal($rBody);
    echo "<p style='padding-left:15px;'>Response Text: <br><strong>SUCCESS</strong></p>";
  }
  else{
    echo $isError;
  }
  echo "<p style='padding-left:15px;'>Response Text: <br><strong>FINISHED</strong></p>";
}

// Call AWS (set)
function shgdprdm_setPendingHistoryExternalData($shgdprdmArray = array()) {
  $shgdprdmSet = array(
    'shgdprdm_what' => 2,
    'shgdprdm_when' => date('Y-m-d H:i:s'),
    'shgdprdm_awho' => 4,
    'shgdprdm_bwho' => 'test.put@gmail.com',
    'shgdprdm_cwho' => 7
  );
  $shgdprdmJson = "'".json_encode($shgdprdmSet)."'";
  $response = wp_remote_request( 'https://nmpnvr9xz9.execute-api.eu-west-1.amazonaws.com/dev/shgdprdm_history', array(
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

  $isError = shgdprdm_getPendingHistoryResponseError($rCode, $rStatus);

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
    $isError = shgdprdm_getPendingHistoryExternalError( $rBody );
  }

  if(!$isError){
    echo shgdprdm_getPendingHistoryExternalInsertResponse( $rBody );
  }
  else{
    echo $isError;
  }
}


// Check for errors from AWS request
function shgdprdm_getPendingHistoryResponseError($rCode, $rStatus) {
  // Check for Http Response Errors from API
  if( $rCode != '200' && strtoupper($rStatus) != 'OK' ){
    return "<p><strong>Error!</strong><br>Bad Http Response:<br>Code: ".$rCode."<br>Status: ".$rStatus."</p>";
  }
  return FALSE;
}

function shgdprdm_getPendingHistoryExternalError( $rBody ) {
  // Check for  Errors in the data returned from API

  // Check that there is contents
  if(!$rBody){
    return "<p>ERROR!<br>No Data Retrieved</p>";
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

function shgdprdm_makePendingHistoryExternal($rBody) {
  $output = '';
  $columns = array('exportID', 'export_type','userID','userEmail','actionedBy','actionTimestamp');
  $headers = array('exportID'=>'Ref', 'export_type'=>'Type', 'userID'=>'User Ref',
                  'userEmail'=>'User Email', 'actionedBy'=>'Admin Action', 'actionTimestamp'=>'Timestamp');

    if(isset($rBody->Item) ){
      // "Single Row";
      $rBody = array($rBody->Item);
    }
    else if(isset($rBody->Items) ){
      // "Multiple Rows";
      $rBody = $rBody->Items;
    }

    $output.= "<br>";
    $output.= "<table>";
    $itemCount = 0;

    // Make table layout
    foreach($rBody as $row){
      if($itemCount == 0){
        $output.= "<thead><tr>";
        foreach($columns as $index => $cName){
          $output.= "<th>".$headers[$cName]."</th>";
        }
        $output.= "</thead></tr><tbody>";
      }
      $output.= "<tr>";
      foreach($columns as $cName){
        $output.= "<td>".$row->$cName  ."</td>";
      }
      $output.= "</tr>";
      $itemCount++;
    }
    $output.= "</tbody>";
    $output.= "</table>";

    return $output;
  }

  // process the response recieved from DB update attempt
  function shgdprdm_getPendingHistoryExternalInsertResponse( $rBody ) {
    $responseType = gettype( $rBody );
    if(isset($rBody->Ref) ){
      // "Single Row";
      $rBody = array($rBody->Ref);
      return "<p style='padding-left:15px;'>Database Update Success!<br> Reference Code:<strong>".$rBody[0]."</strong></p>";
    }
    else{
      return "<p style='padding-left:15px;'>Error!<br><strong>There is an error in the Daabase Response.</strong></p>";
    }
  }

function shgdprdm_pendingPrettyPrint($data){
  $output = array();
  // $output = '';
  foreach($data as $row => $details){
    $output[$row] = array();
    foreach($details as $col => $val){
      if($col == 'actionType'){
        $output[$row][$col] = shgdprdm_pendingConvertType($val);
      }
      else if($col == 'actionedBy'){
        $output[$row][$col] = shgdprdm_pendingConvertAdmin($val);
      }
      else if($col == 'actionVerify'){
        $output[$row][$col] = shgdprdm_reviewConvertVerify($val);
      }
      else{
        $output[$row][$col] = $val;
      }
    }
  }
  return $output;
}

function shgdprdm_pendingConvertType($data){
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

function shgdprdm_pendingConvertAdmin($data){
  $uData = get_user_by( 'id', $data );
  $oData = 'unknown';
  if ( ! empty( $uData ) ) {
    $oData = $uData->user_nicename;
  }
  return $oData;
}

function shgdprdm_pendingLikeType($text){
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

function shgdprdm_pendingLikeAdmin($text){
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
?>
