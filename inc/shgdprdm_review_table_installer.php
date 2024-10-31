<?php
defined('SHGDPRDM_ABSPATH') or die('You are not authorised to access this file.');
function shgdprdm_insertTable(){


  global $wpdb;
  $table_name = $wpdb->prefix . "shgdprdm_history";
  $depricated_table_name = $wpdb->prefix . "mdv_history";
  $shgdprdm_history_db_version = '1.0.0';
  $charset_collate = $wpdb->get_charset_collate();

  $hasDepricatedTable = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $depricated_table_name ) );
  $hasCurrentTable = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) );

  // Delete empty table if exists
  if ( $hasCurrentTable == $table_name && $hasDepricatedTable == $depricated_table_name ) {

    if($wpdb->get_var( "SELECT COUNT(*) FROM {$table_name};") <= 0 ){
      $wpdb->query( "DROP TABLE IF EXISTS {$table_name};" );
      $wpdb->query( "ALTER TABLE {$depricated_table_name} RENAME TO {$table_name};" );
    }
    else{
        $wpdb->query(  "INSERT INTO {$table_name} SELECT * FROM {$depricated_table_name};" );
        $wpdb->query( "DROP TABLE IF EXISTS {$depricated_table_name};" );
    }
  }


  if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) ) != $table_name ) {

    $sql = "CREATE TABLE $table_name (
        `actionID` mediumint(11) NOT NULL AUTO_INCREMENT,
        `actionType` int(5) NOT NULL,
        `userID` varchar(255) NOT NULL,
        `userEmail` varchar(255) NOT NULL,
        `actionedBy` varchar(255) NOT NULL,
        `actionTimestamp` datetime NOT NULL,
        `actionVerify` tinyint(1) NOT NULL,
        `randString` varchar(255) NOT NULL,
        `actionVerifyTimestamp` datetime NOT NULL,
        `disasterSync` tinyint(1) NOT NULL DEFAULT 0,
        PRIMARY KEY (actionID),
        KEY `userID` (userID),
        KEY `randString` (randString)
  )    $charset_collate;";

    require_once( get_home_path() . 'wp-admin/includes/upgrade.php' );

    dbDelta( $sql );
    add_option( 'my_db_version', $shgdprdm_history_db_version );
  }



}
shgdprdm_insertTable();
?>
