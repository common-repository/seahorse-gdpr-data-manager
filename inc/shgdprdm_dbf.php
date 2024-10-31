<?php
defined('SHGDPRDM_ABSPATH') or die('You are not authorised to access this file.');
// Update the activity records table
function shgdprdm_updateActionsHistory($expt, $uid, $aid, $acv = NULL, $rand = NULL, $avt = '0000-00-00 00:00:00', $sync = '', $key = NULL){
  global $wpdb;
  $table = $wpdb->prefix.'shgdprdm_history';
  if(is_numeric($uid)){
    $ud = get_user_by( 'id', $uid );
    $uem = 'unknown';
    if ( ! empty( $ud ) ) {
      $uem =  $ud->user_email;
    }
  }
  else if(is_email($uid) ){
    $uem = $uid;
    $uid = 'Guest';
  }
  else{
    return FALSE;
  }
  $vals = array($expt, $uid, $uem, $aid, date('Y-m-d H:i:s'), $acv, $rand, $avt, $sync);
  if($key){
    $vals = array_merge((array)$key,$vals);
  }
  $cols = shgdprdm_getCols($key);

  $data = shgdprdm_makeInputDataArray($cols, $vals);

  if( empty($data) ){
    return FALSE;
  }

  $rowcount = 0;

  if($key){
    $rowcount = $wpdb->get_var( $wpdb->prepare("SELECT COUNT(*) FROM $table WHERE actionID = %d ", array($key) ));
  }
  if($rowcount < 1){
    $wpdb->insert($table,$data);
    $eid = $wpdb->insert_id;
    return $eid;
  }
  if($rowcount == 1 && $acv && $avt ){
    // return $acv;
    $updated = $wpdb->update( $table, array( 'actionVerify' => $acv, 'actionVerifyTimestamp' => $avt), array( 'actionID' => $key) );
    if($updated !== FALSE){
      return $key;
    }
    return FALSE;
  }
  return FALSE;
}

// Get all the column names in the table that are not the primary key
function shgdprdm_getCols($includeKey = FALSE, $tableName = 'shgdprdm_history'){
  global $wpdb;
  $query = "SELECT COLUMN_NAME as columnName FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_NAME = '".$wpdb->prefix.$tableName."'
  AND TABLE_SCHEMA = '".DB_NAME."'";

  if(!$includeKey ){
    $query .= " AND COLUMN_KEY NOT LIKE 'pri%'";
  }

  return $results = $wpdb->get_results( $query, ARRAY_N );
}
 ?>
