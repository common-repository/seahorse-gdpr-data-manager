<?php
defined('SHGDPRDM_ABSPATH') or die('You are not authorised to access this file.');
// THis PAge Can intentionally be accessed directly

function shgdprdm_userAccess($incPath, $classPath, $pluginDir){
  // /** Allow for cross-domain requests (from the front end). */
  if(function_exists('send_origin_headers')){
    // echo "<br>DEBUG 2.1";
   send_origin_headers();
  }
  // // echo "<br>DEBUG 2.2";
  // @header( 'Content-Type: text/html; charset=' . get_option( 'blog_charset' ) );
  @header( 'X-Robots-Tag: noindex' );
  // // echo "<br>DEBUG 3";
  //
  if(function_exists('send_nosniff_header')){
    // echo "<br>DEBUG 4.1";
   send_nosniff_header();
  }
  // // echo "<br>DEBUG 4.2";
  //
  if(function_exists('nocache_headers')){
    // echo "<br>DEBUG 4.2";
   nocache_headers();
  }
  // echo "<br>DEBUG 4.3";

  if( isset($_POST) && !empty($_POST) ) {
    // wp_die(print_r($_POST));
    if( !shgdprdm_validateExternalPost($_POST) ) {
      wp_die( shgdprdm_userFailureRedirectNotice('UAC', '001') );
      // wp_safe_redirect( get_home_url().'gdpr-data-manager/verify/?ra=&at=&ue=' );
      // die('Error: '.__FILE__.' | '.__LINE__);
    }
  }


  if(
      ( isset($_POST) && !empty($_POST) )
      &&
      (
        !isset($_POST['shgdprdm_exptd'])
        ||
        (
          ( isset($_POST['shgdprdm_exptd']) && !empty($_POST['shgdprdm_exptd']) ) &&
          ( isset($_POST['shgdprdm_export_xml']) && isset($_POST['shgdprdm_export_csv']) && isset($_POST['shgdprdm_export_json']) ) &&
          ( empty($_POST['shgdprdm_export_xml']) && empty($_POST['shgdprdm_export_csv']) && empty($_POST['shgdprdm_export_json']) )
        )
        ||
        (
          ( isset($_POST['shgdprdm_exptd']) && !empty($_POST['shgdprdm_exptd']) ) &&
          ( isset($_POST['shgdprdm_delete_user']) && empty($_POST['shgdprdm_delete_user']) )
        )
      )
  ) {
    // echo "<br>DEBUG 5";
    if(SHGDPRDM_TESTING){
      die('shgdprdm_access - line 31');
    }
    else{
      // echo "<br>DEBUG 6";
      wp_die( shgdprdm_userFailureRedirectNotice('UAC', '002') );
      // wp_safe_redirect( get_home_url().'gdpr-data-manager/verify/?ra=&at=&ue=' );
      // die('Error: '.__FILE__.' | '.__LINE__);
      // wp_safe_redirect( get_home_url() );
    }

    // die('POST BUT NOT CORRECT VARIABLES<br>'.serialize($_POST));
  }
  // else{
  //   wp_die( shgdprdm_userFailureRedirectNotice('003') );
  //   // wp_safe_redirect( get_home_url().'gdpr-data-manager/verify/?ra=&at=&ue=' );
  //   // die('Error: '.__FILE__.' | '.__LINE__);
  // }

  if(
    (
      empty($_POST) && !empty($_GET) &&
      (
        ( !isset($_GET['at']) || empty($_GET['at']) ) ||
        ( !isset($_GET['ue']) || empty($_GET['ue']) ) ||
        ( !isset($_GET['ra']) || empty($_GET['ra']) )
      )
    )
  ) {
    // echo "<br>DEBUG 7";
    if(SHGDPRDM_TESTING){
      die('shgdprdm_access - line 50');
    }
    else{
      // echo "<br>DEBUG 8";
      die('Error: '.__FILE__.' | '.__LINE__);
      // wp_safe_redirect( get_home_url() );
    }
    die();
  }



  /// Original
  // if(
  //   ( isset($_POST) && isset($_POST['shgdprdm_exptd']) &&
  //   ( isset($_POST['shgdprdm_export_xml']) || isset($_POST['shgdprdm_export_csv']) || isset($_POST['shgdprdm_export_json']) || isset($_POST['shgdprdm_delete_user']) )) &&
  //   ( isset($_GET['at']) && isset($_GET['ue']) && isset($_GET['ra']) )
  // ){
  //   $shgdprdm_user_assets = $incPath.'shgdprdm_ext_action.php';
  // }
  // else if(
  //   (empty($_POST) && !empty($_GET)) &&
  //   ( isset($_GET['at']) && isset($_GET['ue']) && isset($_GET['ra']) )
  // ){
  //   $shgdprdm_user_assets = $incPath.'shgdprdm_ext_view.php';
  // }
  // else{
  //   $shgdprdm_user_assets = $incPath.'shgdprdm_ext_view.php';
  // }


  if( isset($_POST) && isset($_POST['shgdprdm_exptd']) && isset($_POST['shgdprdm_delete_user']) && isset($_GET['at']) && isset($_GET['ue']) && isset($_GET['ra']) ){
    // wp_die('Error 1 ');
    $shgdprdm_user_assets = array($incPath.'shgdprdm_ext_action.php',$incPath.'shgdprdm_ext_view.php');
  }
  else if(
    ( isset($_POST) && isset($_POST['shgdprdm_exptd']) &&
    ( isset($_POST['shgdprdm_export_xml']) || isset($_POST['shgdprdm_export_csv']) || isset($_POST['shgdprdm_export_json']) )) &&
    ( isset($_GET['at']) && isset($_GET['ue']) && isset($_GET['ra']) )
  ){
    // wp_die('Error 2 ');
    $shgdprdm_user_assets = array($incPath.'shgdprdm_ext_action.php');
  }
  else if(
    (empty($_POST) && !empty($_GET)) &&
    ( isset($_GET['at']) && isset($_GET['ue']) && isset($_GET['ra']) )
  ){
    // wp_die('Error 3 ');
    $shgdprdm_user_assets = array($incPath.'shgdprdm_ext_view.php');
  }
  else{
    // wp_die('Error 4 ');
    $shgdprdm_user_assets = array($incPath.'shgdprdm_ext_view.php');

  }
  add_action('shgdprdm_export', 'shgdprdm_exportAction', 1, 3 );

  do_action('shgdprdm_export',$shgdprdm_user_assets, $classPath, $pluginDir);
  // if( isset($_POST['shgdprdm_delete_user']) && isset($_POST['shgdprdm_exptd']) && !defined($_POST['shgdprdm_exportCsv']) ){
  //   // echo "In Delete";
  // }
}

function shgdprdm_exportAction($path, $classPath, $pluginDir){
  
  // echo "<br>DEBUG 12";
  // $path .= '/user/inc/shgdprdm_ext_view.php';
  // echo "<br>PATH In fn(): ".$shgdprdm_user_assets;


  foreach($path as $fpath){
    require_once $fpath;
  }
  if(function_exists ( 'shgdprdm_actionUserExport' )){
    shgdprdm_actionUserExport( $classPath, $pluginDir );
  }
  // else{
  //   exit("Function Does Not Exist");
  // }
  if(function_exists ( 'shgdprdm_actionUserView' )){
    // wp_die("Fn Exists shgdprdm_actionUserView");
    $viewData = shgdprdm_actionUserView($classPath, $pluginDir);
    // wp_die("View: ".$viewData[1]);
    // echo $viewData[1];
    // wp_die("View Data: ".$viewData[1]);
    // if( !function_exists ( 'shgdprdm_actionUserExport' ) || (function_exists ( 'shgdprdm_actionUserExport' ) && $viewData[1] != 1) ){
    //   echo $viewData[0];
    // }
    // echo "DB: ".$viewData[1];
    // echo "POST: ".serialize($_POST);
    // if($viewData[1] != 5){
    //   wp_die("DB: ".$viewData[1]);
    // }
    if( !function_exists ( 'shgdprdm_actionUserExport' ) || (function_exists ( 'shgdprdm_actionUserExport' ) && $viewData[1] != 1 && $viewData[1] != 8 || $viewData[1] != 9 || $viewData[1] != 106) ){
      // wp_die($viewData[0]);
      echo $viewData[0];
    }
    // else{
    //   wp_die("Fn does nto exits");
    //   // echo $viewData[0];
    // }
    // wp_die("DB: ".$viewData[1]);
    // if($viewData[1] == 5){
    //   $viewData = shgdprdm_actionUserView($classPath, $pluginDir);
    //   wp_die("Second REf: ".$viewData[1]);
    // }
    if($viewData[1] == 8 || $viewData[1] ==  9 || $viewData[1] ==  106){
      // wp_die($viewData[1]);
      // wp_die('Correct Delete Sequence');
      if(function_exists ( 'shgdprdm_actionUserExport' )){
        // wp_die('Correct Delete Sequence - Function Exists');
        shgdprdm_actionUserExport( $classPath, $pluginDir, TRUE );
        // $viewData = shgdprdm_actionUserView($classPath, $pluginDir);
        // echo "DB DElete: ".$viewData[1];
        // echo $viewData[0];
      }
      // else{
      //   wp_die('FAIL: Correct Delete Sequence - Function Exists');
      // }
    }
    // echo "Request: ".$viewData[1];
    // wp_die("Request: ".$viewData[1]);
  }
  // else{
  //   wp_die("Function Does Not Exist");
  // }
  // if(function_exists ( 'shgdprdm_actionUserExport' )){
  //   shgdprdm_actionUserExport( $classPath, $pluginDir );
  // }
  // else{
  //   wp_die( "Error" );
  // }
  // wp_die("End of View Action");
  exit();



  // require_once $path;
  // if(function_exists ( 'shgdprdm_actionUserExport' )){
  //   shgdprdm_actionUserExport( $classPath, $pluginDir );
  // }
  // else if(function_exists ( 'shgdprdm_actionUserView' )){
  //   shgdprdm_actionUserView($classPath, $pluginDir);
  // }
  // else{
  //   wp_die( "Error" );
  // }
  // exit();
  
}

function shgdprdm_validateExternalPost($postData){
  if( !$postData || empty($postData) ){
    // wp_die('No Post');
    return FALSE;
  }
  if( empty($postData['shgdprdm_exptd']) ){
    // wp_die('No EXPTD');
    return FALSE;
  }
  // if( empty($postData['shgdprdm_uid']) ){
  //   // wp_die('No UID');
  //   return FALSE;
  // }
  if( empty($postData['shgdprdm_user_email']) ){
    // wp_die('No User Email');
    return FALSE;
  }
  if( !isset($postData['shgdprdm_export_xml']) && !isset($postData['shgdprdm_export_csv']) && !isset($postData['shgdprdm_export_json']) && !isset($postData['shgdprdm_delete_user']) ){
    // wp_die('No Submit');
    return FALSE;
  }
  // if( empty($postData['shgdprdm_export_csv']) ){
  //   wp_die('No CSV');
  //   return FALSE;
  // }
  // if( empty($postData['shgdprdm_export_json']) ){
  //   wp_die('No JSON');
  //   return FALSE;
  // }
  // if( isset($postData['shgdprdm_delete_user']) && empty($postData['shgdprdm_delete_user']) ){
  //   wp_die('No Delete');
  //   return FALSE;
  // }
  return TRUE;

}

function shgdprdm_userFailureRedirectNotice($file, $position){
  $html = '';
  $siteEmail = '';
  if(get_bloginfo('admin_email')){
    $siteEmail = '<br><a class="button shgdprdm-usr-btn shgdprdm-usr-btn-regular" href="mailto:'.get_bloginfo("admin_email").'">Contact '.get_bloginfo("name").'</a>';
  }
  $html .= '<strong>GDPR Data Manager - ERROR!</strong>
  <br>Action cannot be performed. (ref: shgdprdm-'.$file.'-'.$position.')
  <br>Please Contact '.get_bloginfo("name").'.'.$siteEmail;

  return $html;
}

?>
