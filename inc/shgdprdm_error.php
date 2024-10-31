<?php
defined('SHGDPRDM_ABSPATH') or die('You are not authorised to access this file.');
// Make the WP_Error object global
$shgdprdm_error = '';
function shgdprdm_setErrors() {

    // Access the global variable
    global $shgdprdm_error;
    // instantiate the class
    $shgdprdm_error = new WP_Error;
    $shgdprdm_error->add( 'err_001', SHGDPRDM_err_001 );
    $shgdprdm_error->add( 'err_002', SHGDPRDM_err_002 );
    $shgdprdm_error->add( 'err_003', SHGDPRDM_err_003 );
    $shgdprdm_error->add( 'err_004', SHGDPRDM_err_004 );
    $shgdprdm_error->add( 'err_005', SHGDPRDM_err_005 );
    $shgdprdm_error->add( 'err_006', SHGDPRDM_err_006 );
    $shgdprdm_error->add( 'err_007', SHGDPRDM_err_007 );
    $shgdprdm_error->add( 'err_008', SHGDPRDM_err_008 );
    $shgdprdm_error->add( 'err_009', SHGDPRDM_err_009 );

    $shgdprdm_error->add( 'war_001', SHGDPRDM_war_001 );
    $shgdprdm_error->add( 'war_002', SHGDPRDM_war_002 );
    $shgdprdm_error->add( 'war_003', SHGDPRDM_war_003 );
    $shgdprdm_error->add( 'war_004', SHGDPRDM_war_004 );
    $shgdprdm_error->add( 'war_005', SHGDPRDM_war_005 );
    $shgdprdm_error->add( 'war_006', SHGDPRDM_war_006 );

    $shgdprdm_error->add( 'msg_001', SHGDPRDM_msg_001 );
    $shgdprdm_error->add( 'msg_001', SHGDPRDM_msg_002 );
    $shgdprdm_error->add( 'msg_001', SHGDPRDM_msg_003 );
}

// Extract the User notice from given reference code
function shgdprdm_getUserNotice($shgdprdm_msgCode = NULL) {
  // TESTING
  $shgdprdm_notice = get_option('shgdprdm_admin_msg');
  if(isset($shgdprdm_notice) && $shgdprdm_notice != ''){
    global $shgdprdm_error;
    $shgdprdm_class = NULL;
    $shgdprdm_msg = NULL;

    if($shgdprdm_msgCode){
      if($shgdprdm_error->get_error_message( $shgdprdm_msgCode ) && $shgdprdm_error->get_error_message( $shgdprdm_msgCode ) != ''){
        $shgdprdm_msg = $shgdprdm_error->get_error_message( $shgdprdm_msgCode );
        $shgdprdm_class = shgdprdm_getNoticeClass($shgdprdm_msgCode);
      }
    }
    else if(isset($shgdprdm_notice) && $shgdprdm_notice != ''){
      $shgdprdm_class = $shgdprdm_notice['class'];
      $shgdprdm_msg = $shgdprdm_notice['msg'];
    }

    if($shgdprdm_class && $shgdprdm_msg){
      $html = shgdprdm_getNoticeHtml($shgdprdm_class, $shgdprdm_msg);
      return $html;
    }
    return false;
  }
  return false;
}

// get the class from a given error code
function shgdprdm_getNoticeClass($msgCode){
  $errorInitial = trim($msgCode)[0];
  if($errorInitial == 'e'){
    $class = 'error';
  }
  else if($errorInitial == 'w'){
    $class = 'warning';
  }
  else if($errorInitial == 'm'){
    $class = 'success';
  }
  else{
    $class = '';
  }
  return $class;
}

// Make html for error notice passing the notice details
function shgdprdm_getNoticeHtml($class, $msg){
  $html = '<div id="shgdprdm-admin-user-notice">
            <div class="shgdprdm-notice '.$class.' is-dismissible">
              <p>'. $msg .'</p>
            </div>
          </div>';
  return $html;
}

// Instantiate the Errors
shgdprdm_setErrors();
?>
