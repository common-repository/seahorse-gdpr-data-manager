<?php
defined('SHGDPRDM_ABSPATH') or die('You are not authorised to access this file.');

function shgdprdm_exportIsAdministratorRole($userID){

  if(user_can( $userID, 'administrator' )){
    // throw new Exception('Error! Action cannot be performed by this user (Admin User - Administrator).');
    return true;
  }
  else if( user_can( $userID, 'manage_options' ) ){
    // throw new Exception('Error! Action cannot be performed by this user (Admin User - Manage Options).');
    return true;
  }
  return false;
}


function shgdprdm_exportCsv($filename = NULL, $data = NULL, $userRef = NULL)
{
  // Check if request originated in User Export File
  $shgdprdm_rqstogn = shgdprdm_validateRqstogn(debug_backtrace()[0]['file']);
  $isAdminUser = shgdprdm_exportIsAdministratorRole($userRef);

  // Check for current user privileges

  // If origin is user export then the user cannot be an administrator
  if( $shgdprdm_rqstogn && $isAdminUser){
  // if( $shgdprdm_rqstogn &&
  //     ( current_user_can( 'manage_options' ) ||
  //       is_admin() ||
  //       current_user_can( 'administrator' )
  //     )
  //   ){
      if(SHGDPRDM_TESTING){
        die('shgdprdm_export - line 19');
      }
      else {
        wp_safe_redirect( get_home_url() );
      }
    die();
    // return false;
  }

  // If origin is NOT user export (eg origin admin section) then the user cannot be an administrator
  if( current_user_can( 'manage_options' ) && !$shgdprdm_rqstogn){
    if(SHGDPRDM_TESTING){
      die('shgdprdm_export - line 31');
    }
    else {
      wp_safe_redirect( get_home_url() );
    }
    die();
    // return false;
  }
  // Check if we are in WP-Admin
  // If origin is NOT user export (eg origin admin section) then the request must come from Admin Area
  if( is_admin() && !$shgdprdm_rqstogn ){
    if(SHGDPRDM_TESTING){
      die('shgdprdm_export - line 43');
    }
    else{
      wp_safe_redirect( get_home_url() );
    }
    die();
    // return false;
  }
  // Check if user can carry out administrator actions
  // If origin is NOT user export (eg origin admin section) then the user cannot be an administrator
  if ( current_user_can( 'administrator' )  && !$shgdprdm_rqstogn ) {
    if(SHGDPRDM_TESTING){
      die('shgdprdm_export - line 55');
    }
    else{
      wp_safe_redirect( get_home_url() );
    }
    die();
    // return false;
  }


  if($data){
    $csv = shgdprdm_makeCsvFile($data);
  }
  else{
    if(SHGDPRDM_TESTING){
      die('shgdprdm_export - line 70');
    }
    else{
      wp_safe_redirect( get_home_url() );
    }
    die();
    // return false;
    // echo "<script>console.log('ERROR - NO DATA')</script>";
  }
  ob_start();
  $tempFileName = get_temp_dir()."export.csv";
  $file = tempnam(get_temp_dir(), 'csv');
  $handle = fopen($file, 'w');
  ftruncate($handle, 0);
  foreach($csv as $tName => $data){
    $header_row = $csv[$tName]['headers'];
    $list = $csv[$tName]['rows'];
    fputcsv( $handle, $header_row );
    foreach ($list as $line){
      fputcsv($handle,explode(',',$line));
    }
    fwrite($handle, "\n");
  }
  fclose($handle);
  if (file_exists($file)) {
    ob_end_clean();
    $sitename = get_bloginfo('name');
    $sitename = str_replace(' ', '_', $sitename);

    if($userRef){
      $fileNameID = $userRef;
    }
    else if(!empty($_POST['shgdprdm_uid'])){
      // Sanitize & Validate Input
      $uid = sanitize_text_field($_POST['shgdprdm_uid']);
      if( is_numeric( $uid ) && intval( $uid ) ){
        $fileNameID = intval( $uid );
      }
      else{
        $fileNameID = 'Unknown_User';
      }
    }
    else if( !empty($_POST['shgdprdm_user_email']) ){
      // Sanitize & Validate Input
      $email = sanitize_email( $_POST['shgdprdm_user_email'] );
      if( is_email( $email ) ){
        $fileNameID = $email;
      }
      else{
        $fileNameID = 'Unknown_User';
      }
    }
    else{
      $fileNameID = 'Unknown_User';
    }
    // $filename = $sitename."_ShGdprDataMgr_".($userRef?$userRef:$_POST['shgdprdm_uid'])."_".date('dmy')."_".date('His');
    $filename = $sitename."_ShGdprDataMgr_".$fileNameID."_".date('dmy')."_".date('His');
    header("Content-type: application/csv");
    header("Content-disposition: attachment; filename = ".$filename.".csv");
    readfile($file);
    unlink($file);
    ob_end_flush();
    return true;
    exit;
  }
  else{
    if(SHGDPRDM_TESTING){
      die('shgdprdm_export - line 109');
    }
    else{
      wp_safe_redirect( get_home_url() );
    }
    die();
    // echo "FILE DOES NOT EXIST!";
    // return false;
  }
  $page = admin_url( "?page=seahorse_gdpr_data_manager_plugin&tab=gdpr_data_manager" );
  ob_end_flush();
  return true;
  exit;
}

function shgdprdm_exportXml($data, $userRef = NULL){
  // Check if request originated in User Export File
  $shgdprdm_rqstogn = shgdprdm_validateRqstogn(debug_backtrace()[0]['file']);
  $isAdminUser = shgdprdm_exportIsAdministratorRole($userRef);

  // Check for current user privileges

  // If origin is user export then the user cannot be an administrator
  if( $shgdprdm_rqstogn && $isAdminUser){
    // if( $shgdprdm_rqstogn &&
    //   ( current_user_can( 'manage_options' ) ||
    //     is_admin() ||
    //     current_user_can( 'administrator' )
    //   )
    // ){
    if(SHGDPRDM_TESTING){
      die('shgdprdm_export - line 138');
    }
    else{
      wp_safe_redirect( get_home_url() );
    }
    die();
    // return false;
  }
  // If origin is NOT user export (eg origin admin section) then the user cannot be an administrator
  if( current_user_can( 'manage_options' ) && !$shgdprdm_rqstogn ){
    if(SHGDPRDM_TESTING){
      die('shgdprdm_export - line 144');
    }
    else{
      wp_safe_redirect( get_home_url() );
    }
    die();
    // return false;
 }
  // Check if we are in WP-Admin
  // If origin is NOT user export (eg origin admin section) then the request must come from Admin Area
  if( is_admin()  && !$shgdprdm_rqstogn ){
    if(SHGDPRDM_TESTING){
      die('shgdprdm_export - line 156');
    }
    else{
      wp_safe_redirect( get_home_url() );
    }
    die();
    // return false;
   }
  // Check if user can carry out administrator actions
  // If origin is NOT user export (eg origin admin section) then the user cannot be an administrator
  if ( current_user_can( 'administrator' ) && !$shgdprdm_rqstogn ) {
    if(SHGDPRDM_TESTING){
      die('shgdprdm_export - line 168');
    }
    else{
      wp_safe_redirect( get_home_url() );
    }
    die();
    // return false;
  }

  // flush(); // Flush the buffer
  // ob_flush();
                                  ob_start();
   $file = '';
  // if($data){
    $file = shgdprdm_makeXmlFile($data);
    // $file = shgdprdm_make_xml_file($_POST['shgdprdm_expRd']);
  // }
  if (file_exists($file)) {
    $sitename = get_bloginfo('name');
    $sitename = str_replace(' ', '_', $sitename);
    if($userRef){
      $fileNameID = $userRef;
    }
    else if(!empty($_POST['shgdprdm_uid'])){
      // Sanitize & Validate Input
      $uid = sanitize_text_field( $_POST['shgdprdm_uid'] );
      if( is_numeric( $uid ) && intval( $uid ) ){
        $fileNameID = intval( $uid );
      }
      else{
        $fileNameID = 'Unknown_User';
      }
    }
    else if(!empty($_POST['shgdprdm_user_email']) ){
      // Sanitize & Validate Input
      $email = sanitize_email($_POST['shgdprdm_user_email']);
      if( is_email( $email ) ){
        $fileNameID = $email;
      }
      else{
        $fileNameID = 'Unknown_User';
      }
    }
    else{
      $fileNameID = 'Unknown_User';
    }
    $filename = $sitename."_ShGdprDataMgr_".$fileNameID."_".date('dmy')."_".date('His');
    // $filename = $sitename."_ShGdprDataMgr_".($userRef?$userRef:$_POST['shgdprdm_uid'])."_".date('dmy')."_".date('His');
    // $filename = $sitename."_ShGdprDataMgr_".$_POST['shgdprdm_uid']."_".date('dmy')."_".date('His');
                      ob_end_clean();
    header('Content-type: text/xml');
    header('Content-Disposition: attachment; filename="'.$filename.'.xml"');
    readfile($file);
    unlink($file);
    // ob_get_clean();
                      ob_end_flush();
    return TRUE;
    exit;
  }
  else{
    if(SHGDPRDM_TESTING){
      die('shgdprdm_export - line 202');
    }
    else{
      wp_safe_redirect( get_home_url() );
    }
    die();
    // echo "FILE DOES NOT EXIST!";
    // return false;
  }
}

function shgdprdm_exportJson($data, $userRef = NULL){
  // wp_die('IN JSON Export - LINE: '.__LINE__);
  // Check if request originated in User Export File
  $shgdprdm_rqstogn = shgdprdm_validateRqstogn(debug_backtrace()[0]['file']);
  $isAdminUser = shgdprdm_exportIsAdministratorRole($userRef);

  // Check for current user privileges

  // If origin is user export then the user cannot be an administrator
  if( $shgdprdm_rqstogn && $isAdminUser){
    // wp_die('IN JSON Export - LINE: '.__LINE__);
  // if( $shgdprdm_rqstogn &&
  //     ( current_user_can( 'manage_options' ) ||
  //       is_admin() ||
  //       current_user_can( 'administrator' )
  //     )
  //   ){
      // die('Fail 1');
      if(SHGDPRDM_TESTING){
        die('shgdprdm_export - line 228');
      }
      else{
        wp_die('Ooops! Something has gone wrong. Error Ref EXP_001');
        wp_safe_redirect( get_home_url() );
      }
      die();
      // return false;
  }

  // wp_die('IN JSON Export - LINE: '.__LINE__); - Delete Workign to Here


  // If origin is NOT user export (eg origin admin section) then the user cannot be an administrator
  if( current_user_can( 'manage_options' ) && !$shgdprdm_rqstogn ){
    // wp_die('IN JSON Export - LINE: '.__LINE__);
    // die('Fail 2');
    if(SHGDPRDM_TESTING){
      die('shgdprdm_export - line 240');
    }
    else{
      wp_die('Ooops! Something has gone wrong. Error Ref EXP_002');
      wp_safe_redirect( get_home_url() );
    }
    die();
    // return false;
  }


  // wp_die('IN JSON Export - LINE: '.__LINE__); - Delete Workign to Here


  // Check if we are in WP-Admin
  // If origin is NOT user export (eg origin admin section) then the request must come from Admin Area
  if( is_admin()  && !$shgdprdm_rqstogn ){
    // wp_die('IN JSON Export - LINE: '.__LINE__);
    // die('Fail 3');
    if(SHGDPRDM_TESTING){
      die('shgdprdm_export - line 252');
    }
    else{
      wp_die('Ooops! Something has gone wrong. Error Ref EXP_003');
      wp_safe_redirect( get_home_url() );
    }
    die();
    // return false;
   }

   // wp_die('IN JSON Export - LINE: '.__LINE__); - Delete Workign to Here


  // Check if user can carry out administrator actions
  // If origin is NOT user export (eg origin admin section) then the user cannot be an administrator
  if ( current_user_can( 'administrator' ) && !$shgdprdm_rqstogn ) {
    // wp_die('IN JSON Export - LINE: '.__LINE__);
    // die('Fail 4');
    if(SHGDPRDM_TESTING){
      die('shgdprdm_export - line 266');
    }
    else{
      wp_die('Ooops! Something has gone wrong. Error Ref EXP_003');
      wp_safe_redirect( get_home_url() );
    }
    die();
    // return false;
  }


  // wp_die('IN JSON Export - LINE: '.__LINE__); - Delete Workign to Here


  if($data){
    // wp_die('IN JSON Export - LINE: '.__LINE__);
    // wp_die('IN JSON Export - LINE: '.__LINE__);- Delete Workign to Here
    if( !empty($_POST['shgdprdm_uid']) ){
      // Sanitize & Validate Input
      $uid = sanitize_text_field( $_POST['shgdprdm_uid'] );
      if( is_numeric( $uid ) && intval( $uid ) ){
        $dataID = intval( $uid );
      }
      else{
        $dataID = NULL;
      }
    }
    else{
      $dataID = NULL;
    }
    // ob_start();
    // wp_die('IN JSON Export - LINE: '.__LINE__);
    $jData = shgdprdm_makeJsonFile( ( (isset($dataID) && $dataID)?$dataID:$data) );
    if($jData){
      // wp_die('IN JSON Export - LINE: '.__LINE__);
      // wp_die('IN JSON Export - '.print_r($jData).'<br> LINE: '.__LINE__);
      $file = shgdprdm_writeJsonFile($jData);
      
      if( file_exists($file) ){
        // wp_die('IN JSON Export - LINE: '.__LINE__);
        $sitename = get_bloginfo('name');
        $sitename = str_replace(' ', '_', $sitename);
        if($userRef){
          $fileNameID = $userRef;
        }
        else if(!empty($_POST['shgdprdm_uid'])){
          // Sanitize & Validate Input
          $uid = $_POST['shgdprdm_uid'];
          if( is_numeric( $uid ) && intval( $uid ) ){
            $fileNameID = intval( $uid );
          }
          else{
            $fileNameID = 'Unknown_User';
          }
        }
        else if(!empty($_POST['shgdprdm_user_email'])){
          // Sanitize & Validate Input
          $email = sanitize_email($_POST['shgdprdm_user_email']);
          if( is_email( $email ) ){
            $fileNameID = $email;
          }
          else{
            $fileNameID = 'Unknown_User';
          }
        }
        else{
          $fileNameID = 'Unknown_User';
        }
        // wp_die('IN JSON Export - LINE: '.__LINE__);
        $filename = $sitename."_ShGdprDataMgr_".$fileNameID."_".date('dmy')."_".date('His');
        // $filename = $sitename."_ShGdprDataMgr_".($userRef?$userRef:$_POST['shgdprdm_uid'])."_".date('dmy')."_".date('His');


        // wp_die('IN JSON Export - LINE: '.__LINE__); - Delete Workign to Here
        // wp_die('IN JSON Export - <br>FILENAME: '.print_r($filename));
        // wp_die('IN JSON Export - <br>FILENAME: '.print_r($filename).' <br>FILE: '.print_r($file).'<br>LINE: '.__LINE__);


        // ob_end_clean();
        header('Content-type: text/json');
        // wp_die('IN JSON Export - LINE: '.__LINE__);
        // header('Content-Disposition: attachment; filename="Test"');
        header('Content-Disposition: attachment; filename="'.$filename.'.json"');
        // wp_die('IN JSON Export - LINE: '.__LINE__);
        readfile($file);
        unlink($file);
        // ob_end_flush();

        // wp_die('IN JSON Export - LINE: '.__LINE__);

        return TRUE;
        exit;
      }
      else{
        // die('Fail 5');
        if(SHGDPRDM_TESTING){
          die('shgdprdm_export - line 293');
        }
        else{
          wp_die('Ooops! Something has gone wrong. Error Ref EXP_004');
          wp_safe_redirect( get_home_url() );
        }
        die();
        // echo "FILE DOES NOT EXIST!";
        // return false;
      }
    }
    else{
      // die('Fail 6');
      if(SHGDPRDM_TESTING){
        die('shgdprdm_export - line 306');
      }
      else{
        wp_die('Ooops! Something has gone wrong. Error Ref EXP_005');
        wp_safe_redirect( get_home_url() );
      }
      die();
      // echo "JSON DOES NOT EXIST!";
      // return false;
    }
  }
  else{
    // wp_die('IN JSON Export - LINE: '.__LINE__);
    // die('Fail 7');
    if(SHGDPRDM_TESTING){
      die('shgdprdm_export - line 319');
    }
    else{
      wp_die('Ooops! Something has gone wrong. Error Ref EXP_006');
      wp_safe_redirect( get_home_url() );
    }
    die();
    // echo "DATA DOES NOT EXIST!";
    // return false;
  }
}

 ?>
