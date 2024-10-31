<?php
// /* prevent access from outside cms */
defined('SHGDPRDM_ABSPATH') or die('You are not authorised to access this file.');

if(!class_exists('SHGdprdm_ExternalAccessRequest')){
  class SHGdprdm_ExternalAccessRequest {
    private $pluginDirectory;
    private $pluginBaseDir;
    private $userPath;
    private $userClassPath;
    private $userIncludesPath;

    function __construct(){
      $siteEmail = '';
      if(get_bloginfo('admin_email')){
        $siteEmail = '<br><a class="button shgdprdm-usr-btn shgdprdm-usr-btn-regular" href="mailto:'.get_bloginfo("admin_email").'">Contact '.get_bloginfo("name").'</a>';
      }
      if(count(func_get_args()) !== 1){
        throw new Exception('<strong>GDPR Data Manager - ERROR!</strong>
        <br>Action cannot be performed. (ref: shgdprdm-EARC-001)
        <br>Please Contact '.get_bloginfo("name").$siteEmail.'.');
      }
      if( !isset($_GET) ){
        throw new Exception('<strong>GDPR Data Manager - ERROR!</strong>
        <br>Action cannot be performed. (ref: shgdprdm-EARC-002)
        <br>Please Contact '.get_bloginfo("name").$siteEmail.'.');
      }
      if( !self::shgdprdm_validateUserExportGet($_GET) ){
        throw new Exception('<strong>GDPR Data Manager - ERROR!</strong>
        <br>Action cannot be performed. (ref: shgdprdm-EARC-003)
        <br>Please Contact '.get_bloginfo("name").$siteEmail.'.');
      }

      // $this->pluginDirectory = plugin_dir_path(func_get_args()[0]); // Error in PHP 7

      $params= func_get_args();
      // if(count( $params ) !== 1){
      //   throw new Exception('<strong>GDPR Data Manager - ERROR!</strong><br>Action cannot be performed. (ref: shgdprdm-CER-001)<br>Please deactivate & reactivate this plugin.');
      // }
	    $param = $params[0];
      $this->pluginDirectory = plugin_dir_path( $param);
      $this->pluginBaseDir = func_get_args()[0];
      $this->userPath = $this->pluginDirectory.'user/';
      $this->userIncludesPath = $this->userPath.'inc/';
      $this->userClassPath = $this->userPath.'classes/';
    }

    function shgdprdm_registerExternal(){
      //$error = NULL;
      update_option('shgdprdm_user_msg','');
      if( isset($_POST) && !empty($_POST) ){
        if( !self::shgdprdm_validateUserExportPost($_POST) ){
          unset( $_POST['shgdprdm_exptd'], $_POST['shgdprdm_export_xml'], $_POST['shgdprdm_export_csv'], $_POST['shgdprdm_export_json'], $_POST['shgdprdm_delete_user'] );
        }
        add_action('init', array($this, 'shgdprdm_externalLaunch'));
      }
      else if( isset($_GET) && !empty($_GET) ){
        if( !self::shgdprdm_validateUserExportGet($_GET) ){
          unset( $_GET['ra'], $_GET['ue'], $_GET['at'] );
        }
        add_action('init', array($this, 'shgdprdm_externalLaunch'));
      }
      else{
        $siteEmail = '';
        if(get_bloginfo('admin_email')){
          $siteEmail = '<br><a class="button shgdprdm-usr-btn shgdprdm-usr-btn-regular" href="mailto:'.get_bloginfo("admin_email").'">Contact '.get_bloginfo("name").'</a>';
        }
        throw new Exception('<strong>GDPR Data Manager - ERROR!</strong>
        <br>Action cannot be performed. (ref: shgdprdm-EARC-004)
        <br>Please Contact '.get_bloginfo("name").$siteEmail.'.');
      }
      // else{
      //   wp_die('<strong>GDPR Data Manager - ERROR!</strong><br>'.get_option('shgdprdm_user_msg')['msg'].'<br>Please Contact '.get_bloginfo("name").$siteEmail.'.' );
      // }
      // if(
      //     ( isset($_POST) && !empty($_POST) )
      //     &&
      //     (
      //       !isset($_POST['shgdprdm_exptd'])
      //       ||
      //       (
      //         ( isset($_POST['shgdprdm_exptd']) && !empty($_POST['shgdprdm_exptd']) ) &&
      //         ( isset($_POST['export_xml']) && isset($_POST['export_csv']) && isset($_POST['export_json']) ) &&
      //         ( empty($_POST['export_xml']) && empty($_POST['export_csv']) && empty($_POST['export_json']) )
      //       )
      //       ||
      //       (
      //         ( isset($_POST['shgdprdm_exptd']) && !empty($_POST['shgdprdm_exptd']) ) &&
      //         ( isset($_POST['delete_user']) && empty($_POST['delete_user']) )
      //       )
      //     )
      // ){
      //   $error = 1;
      // }
      // if(
      //   (
      //     empty($_POST) &&
      //     (
      //       ( !isset($_GET['at']) || empty($_GET['at']) ) ||
      //       ( !isset($_GET['ue']) || empty($_GET['ue']) ) ||
      //       ( !isset($_GET['ra']) || empty($_GET['ra']) )
      //     )
      //   )
      // ){
      //   $error = 1;
      // }

      // if(!$error){
      //   // add_action('init',array($this, 'shgdprdm_deleteFn'));
      //   add_action('init', array($this, 'shgdprdm_externalLaunch'));
      // }
    }

    public function shgdprdm_externalLaunch(){
        require_once $this->userPath.'shgdprdm_access.php';
        add_action('shgdprdm_access_fn', 'shgdprdm_userAccess', 1, 3 );
        do_action('shgdprdm_access_fn',$this->userIncludesPath,$this->userClassPath,$this->pluginDirectory);
        if(isset($_POST['shgdprdm_delete_user'])){
          add_action('shgdprdm_access_fn', 'shgdprdm_userDelete', 1, 3 );
          do_action('shgdprdm_access_fn',$this->userIncludesPath,$this->userClassPath,$this->pluginDirectory);
        }
    }

    public function shgdprdm_getPluginDir(){
      return $this->pluginDirectory;
    }

    private function shgdprdm_validateUserExportPost($postData){
      // return FALSE;
      if( !isset( $postData['shgdprdm_exptd'] ) ){
        // wp_die('NO EXPTD: '.__FILE__.' | '.__LINE__);
        update_option('shgdprdm_user_msg',array('class' => 'error', 'msg' => 'Action Could Not Be Performed. <em>error Code 11.1</em>'));
        return FALSE;
      }
      if( empty( $postData['shgdprdm_exptd'] ) ){
        update_option('shgdprdm_user_msg',array('class' => 'error', 'msg' => 'Action Could Not Be Performed. <em>error Code 11.2</em>'));
        return FALSE;
      }
      if( !self::shgdprdm_validateUserExportPostData($postData['shgdprdm_exptd'])){
        update_option('shgdprdm_user_msg',array('class' => 'error', 'msg' => 'Action Could Not Be Performed. <em>error Code 11.3</em>'));
        return FALSE;
      }
      if( !isset($_POST['shgdprdm_export_xml']) && !isset($_POST['shgdprdm_export_csv']) && !isset($_POST['shgdprdm_export_json']) && !isset($_POST['shgdprdm_delete_user']) ){
        update_option('shgdprdm_user_msg',array('class' => 'error', 'msg' => 'Action Could Not Be Performed. <em>error Code 11.4</em>'));
        return FALSE;
      }
      return TRUE;
    }

    private function shgdprdm_validateUserExportPostData($postDataString){

      if( strpos( $postDataString, '?ra=') != 0 ){
        update_option('shgdprdm_user_msg',array('class' => 'error', 'msg' => 'Action Could Not Be Performed. <em>error Code 11.3.1</em>'));
        return FALSE;
      }
      if( strpos( $postDataString, '&at=') === false ){
        update_option('shgdprdm_user_msg',array('class' => 'error', 'msg' => 'Action Could Not Be Performed. <em>error Code 11.3.2</em>'));
        return FALSE;
      }
      if( strpos( $postDataString, '&ue=') === false ){
        update_option('shgdprdm_user_msg',array('class' => 'error', 'msg' => 'Action Could Not Be Performed. <em>error Code 11.3.3</em>'));
        return FALSE;
      }

      return TRUE;
    }

    private function shgdprdm_validateUserExportGet($getData){
      // return FALSE;
      if( !isset($getData['at']) || empty($getData['at']) ){
        update_option('shgdprdm_user_msg',array('class' => 'error', 'msg' => 'Action Could Not Be Performed. <em>error Code 11.5</em>'));
        return FALSE;
      }
      if( !isset($getData['ue']) || empty($getData['ue']) ){
        update_option('shgdprdm_user_msg',array('class' => 'error', 'msg' => 'Action Could Not Be Performed. <em>error Code 11.6</em>'));
        return FALSE;
      }
      if( !isset($getData['ra']) || empty($getData['ra']) ){
        update_option('shgdprdm_user_msg',array('class' => 'error', 'msg' => 'Action Could Not Be Performed. <em>error Code 11.7</em>'));
        return FALSE;
      }
      return TRUE;
    }


  }
} // End



 ?>
