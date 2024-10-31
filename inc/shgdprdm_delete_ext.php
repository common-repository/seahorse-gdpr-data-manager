<?php
defined('SHGDPRDM_ABSPATH') or die('You are not authorised to access this file.');

// Check if request originated in User Export File
$shgdprdm_rqstogn = shgdprdm_validateRqstogn(debug_backtrace()[0]['file']);
// Check for current user privileges
// If origin is user export then the user cannot be an administrator
if( !$shgdprdm_rqstogn){
  if(SHGDPRDM_TESTING){
    wp_die('shgdprdm_delete_ext - line 18  ');
  }
  else{
    wp_safe_redirect( get_home_url() );
  }
  die();
}

function shgdprdm_deleteUserExternal($userID, $isDisasterSync = FALSE){ // either Email Addy or ID
  if(!$userID || $userID == '' || $userID == NULL){
     if(!shgdprdm_deleteCheckIfGuestWooCommerce($userID)){
       if(SHGDPRDM_TESTING){
         wp_die('shgdprdm_delete_ext - line 31');
       }
       else{
         wp_safe_redirect( get_home_url() );
       }
       die();
     }
  }
  // Check for current user privileges
  // If origin is user export then the user cannot be an administrator
  if(shgdprdm_exportIsAdministratorRole($userID)){
    if(SHGDPRDM_TESTING){
      die('shgdprdm_delete_ext - line 42');
    }
    else{
      wp_die('You are not authorised to perform this action');
      wp_safe_redirect( get_home_url() );
    }
    die();
  }

  global $seahorseGdprDataManagerPlugin;
  if($seahorseGdprDataManagerPlugin){
    require_once $seahorseGdprDataManagerPlugin->shgdprdm_getPluginDir().'classes/shgdprdm_mdf.class.php';
  }
  else{
    global $seahorseMyDataViewExternalAccessRequest;
    require_once $seahorseMyDataViewExternalAccessRequest->shgdprdm_getPluginDir().'classes/shgdprdm_mdf.class.php';
  }

  if(class_exists('SHGdprdm_MDF')){
    try{
      $delete = new SHGdprdm_MDF($userID);
    } catch (Exception $e) {
      die($e->getMessage());
      if(SHGDPRDM_TESTING){
        die('shgdprdm_delete_ext - line 46');
      }
      else{
        wp_safe_redirect( get_home_url() );
      }
    }

      $delete->shgdprdm_deleteCommentsAndMeta();
      $delete->shgdprdm_deletePostsAndMeta();
      $delete->shgdprdm_deleteWooCommerce();
      $delete->shgdprdm_deleteEasyDigitalDownloads();

      $delete->shgdprdm_deleteUser( $isDisasterSync );
  }
  else{
    wp_die('Oops! Something has gone wrong. Error Code: shgdprdm-DEX_001');
  }
  return true;
}

function shgdprdm_deleteCheckIfGuestWooCommerce($ref){
  if(is_email($ref)){
    $orders = (function_exists('wc_get_orders') ? wc_get_orders(array('email' => $ref)) : '');
    if($orders){
      return true;
    }
  }
  return false;
}
 ?>
