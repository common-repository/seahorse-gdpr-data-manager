<?php
defined('SHGDPRDM_ABSPATH') or die('You are not authorised to access this file.');

if (!class_exists('SHGdprdm_UEXP')) {
    class SHGdprdm_UEXP
    {

  // protected $uPath;
        protected $cPath;
        protected $ud;
        protected $uem;
        protected $uid;
        protected $ext;

        protected $shgdprdm_user_assets;
        protected $shgdprdm_assets;
        protected $shgdprdm_includes;
        protected $shgdprdm_user_template;
        protected $shgdprdm_user_style;
        protected $at;
        protected $ue;
        protected $ra;
        protected $oData;
        protected $accArr;
        protected $all_meta_for_user;
        protected $user;
        protected $pp_text;
        protected $rdiurl;
        protected $pluginDir;
        protected $pluginIncDir;
        protected $pluginClassDir;
        protected $pluginSharedDir;
        private $validateControl;
        private $isDisasterSync;
        private $storedGuestOrders;
        private $guestOrderRefs;
        private $storedGuestDownloads;
        private $exportType;

        public function __construct()
        {
            $siteEmail = '';
            if (get_bloginfo('admin_email')) {
                $siteEmail = '<br><a class="button shgdprdm-usr-btn shgdprdm-usr-btn-regular" href="mailto:'.get_bloginfo("admin_email").'">Contact '.get_bloginfo("name").'</a>';
            }
            $argsCount = count(func_get_args());
            if ($argsCount < 1 || $argsCount > 2) {
                throw new Exception('<strong>GDPR Data Manager - ERROR!</strong>
        <br>Action cannot be performed. (ref: shgdprdm-CER-003)
        <br>Please Contact '.get_bloginfo("name").$siteEmail.'.');
            }

            if (!$this->validatePost()) {
                throw new Exception('<strong>GDPR Data Manager - ERROR!</strong>
        <br>Action cannot be performed. (ref: shgdprdm-CER-003)
        <br>Please Contact '.get_bloginfo("name").$siteEmail.'.');
            }


            $this->pluginDir = func_get_args()[0];
            $this->exportType = func_get_args()[1];

            $this->pluginIncDir = $this->pluginDir.'/inc/';
            $this->pluginClassDir = $this->pluginDir.'/classes/';
            $this->pluginSharedDir = $this->pluginDir.'/shared/';

            $this->ra = false;
            $this->isDisasterSync = false;

            $this->storedGuestOrders = array();

            if (method_exists($this, 'register_wpcls')) {
                $this->register_wpcls();
            }
            if (method_exists($this, 'shgdprdm_extract_uem')) {
                $this->shgdprdm_extract_uem();
            }
            if (method_exists($this, 'shgdprdm_extract_uid')) {
                $this->shgdprdm_extract_uid();
            }
            if (method_exists($this, 'shgdprdm_extract_redirectURL')) {
                $this->shgdprdm_extract_redirectURL();
            }
      
            // Failing comewhere from here
            if ($this->uid === null || $this->uid === '' || $this->uid === 0 || $this->uid === '0') {
                // Add for Guest WooCom User
                if (!$this->shgdprdm_checkIfGuestWooCommerce()) {
                    if (!$this->shgdprdm_checkIfGuestEddCustomer()) {
                        throw new Exception($this->rdiurl);
                        wp_die('Action Cannot be performed by this user');
                    }
                }
                if (method_exists($this, 'shgdprdm_getGuestWooCommerceOrdersForUpdate')) {
                    $this->shgdprdm_getGuestWooCommerceOrdersForUpdate();
                }
            }
            if (user_can($this->uid, 'manage_options')) {
                throw new Exception('Error! Action cannot be performed by this user.');
                wp_die('Action Cannot be performed by this user');
            }

            $this->ext = null;

            if (method_exists($this, 'shgdprdm_extract_ext')) {
                $this->shgdprdm_extract_ext();
            }
            if (method_exists($this, 'shgdprdm_extract_ud')) {
                $this->shgdprdm_extract_ud();
            }
            if (!isset($this->exportType) || (isset($this->exportType) && $this->exportType === false)) {
                if (method_exists($this, 'shgdprdm_export')) {
                    $this->shgdprdm_export();
                    // wp_die("Exporter Class Construct - 8");
                }
            }
            // wp_die("Exporter Class Construct - 9");

            if (isset($this->exportType) && $this->exportType === true) {
                if (method_exists($this, 'shgdprdm_deleteExport')) {
                    $this->shgdprdm_deleteExport();
                    // wp_die("Exporter Class Construct - 10");
                }
                // throw new Exception('In Delete');
            }
            // wp_die("Exporter Class Construct - 11");
      // else{
      //   throw new Exception('Not in Delete');
      // }


      // if ( method_exists($this, 'setPrivacyPolicy' ) ) {
      //   self::setPrivacyPolicy();
      // }
      // Do not initiate the class if the user has admin capabilities
      // wp_die("End Exporter Class Construct");
        }

        private function register_wpcls()
        {
            add_action('init_register', array($this,'shgdprdm_register_wpcls'));
            do_action('init_register');
        }

        public function shgdprdm_register_wpcls()
        {
            require_once $this->pluginClassDir.'shgdprdm_msf.class.php';
            if (!class_exists('SHGdprdm_ValidateControl')) {
                require_once $this->pluginSharedDir.'shgdprdm_validate_control.class.php';
            }
            $this->validateControl = new SHGdprdm_ValidateControl();
            // require_once $this->pluginClassDir.'shgdprdm_wcf.class.php';
            if (
              file_exists($this->pluginClassDir.'shgdprdm_wcf.pro.class.php')  &&
              $this->validateControl->shgdprdm_validateVerifyLicence() &&
              $this->validateControl->shgdprdm_validateHasLicence() &&
              $this->validateControl->shgdprdm_validateIsProLicence('wcf')
            ) {
                require_once $this->pluginClassDir.'shgdprdm_wcf.pro.class.php';
            }
            // require_once $this->pluginClassDir.'shgdprdm_eddf.class.php';
            if (
                file_exists($this->pluginClassDir.'shgdprdm_eddf.pro.class.php') &&
                $this->validateControl->shgdprdm_validateVerifyLicence() &&
                $this->validateControl->shgdprdm_validateHasLicence() &&
                $this->validateControl->shgdprdm_validateIsProLicence('eddf')
              ) {
                require_once $this->pluginClassDir.'shgdprdm_eddf.pro.class.php';
            }
            require_once $this->pluginIncDir.'shgdprdm_export.php';
            require_once $this->pluginIncDir.'shgdprdm_export_helpers.php';
            require_once $this->pluginIncDir.'shgdprdm_opt_review.php'; // Required for External DB Update
        }

        private function shgdprdm_extract_uem()
        {
            if (isset($_GET['ue'])) {
                // $this->ue_url = $_GET['ue'];
                $ue = base64_decode(sanitize_text_field($_GET['ue']));
                $this->uem = unserialize($ue);
            } else {
                // wp_die('No User Email Set');
                $this->uem = false;
            }
        }
        private function shgdprdm_extract_ura()
        {
            if (isset($_GET['ra'])) {
                //$this->ra_url = $_GET['ra'];
                $ra = base64_decode(sanitize_text_field($_GET['ra']));
                $this->ra = unserialize($ra);
            } else {
                // wp_die('No Random String Set');
                $this->ra = false;
            }
        }
        private function shgdprdm_extract_uat()
        {
            if (isset($_GET['at'])) {
                // $this->at_url = $_GET['at'];
                $at = base64_decode(sanitize_text_field($_GET['at']));
                $this->at = unserialize($at);
            } else {
                // wp_die('No Action Set');
                $this->at = false;
            }
        }
        private function shgdprdm_extract_uid()
        {
            $user = get_user_by('email', $this->uem);
            if ($user) {
                $this->uid = $user->ID;
            } else {
                $this->uid = null;
            }
        }
        private function shgdprdm_extract_ext()
        {
            if (isset($_POST['shgdprdm_export_xml'])) {
                $this->ext = 1;
            } elseif (isset($_POST['shgdprdm_export_csv'])) {
                $this->ext = 2;
            } elseif (isset($_POST['shgdprdm_export_json'])) {
                $this->ext = 3;
            } elseif (isset($_POST['shgdprdm_delete_user'])) {
                $this->ext = 4;
            } else {
                $this->ext = null;
            }
            // wp_die("This EXT: ".$this->ext);
        }

        private function shgdprdm_extract_redirectURL()
        {
            if (isset($_GET['at']) && isset($_GET['ue']) && isset($_GET['ra'])) {
                $this->rdiurl = "/gdpr-data-manager/verify/?ra=".sanitize_text_field($_GET['ra'])."&at=".sanitize_text_field($_GET['at'])."&ue=".sanitize_text_field($_GET['ue']);
            } else {
                $this->rdiurl = "";
            }
        }
        private function shgdprdm_extractCondirmationRedirectURL()
        {
            if (isset($_GET['at']) && isset($_GET['ue']) && isset($_GET['ra'])) {
                $this->rdiurl = "/gdpr-data-manager/verify-deleted/?ra=".sanitize_text_field($_GET['ra'])."&at=".sanitize_text_field($_GET['at'])."&ue=".sanitize_text_field($_GET['ue']);
            } else {
                $this->rdiurl = "";
            }
        }

        private function shgdprdm_extract_ud()
        {
            if (class_exists('SHGdprdm_MSF')) {
                $ud = new SHGdprdm_MSF($this->uem);
                $this->ud = $ud->shgdprdm_getDisplay();
                // wp_die(print_r($this->ud));
            }
        }

        private function shgdprdm_register_delete()
        {

      // require_once $this->cPath.'/inc/shgdprdm_delete_ext.php';
            require_once $this->pluginIncDir.'shgdprdm_delete_ext.php';
        }

        private function getRedirectURL()
        {
            return $this->rdiurl;
        }

        private function shgdprdm_checkIfGuestWooCommerce()
        {
            // wp_die("User Email: ".$this->uem);
            if ($this->uem) {
                // $orders = wc_get_orders(array('email' => $this->uem));
                $orders = (function_exists('wc_get_orders') ? wc_get_orders(array('email' => $this->uem)) : '');
                // wp_die("Orders: ".print_r($orders) );
                if ($orders) {
                    $this->storedGuestOrders = $orders;
                    // echo "HAS ORDERS";
                    return true;
                }
                return false;
            }
            return false;
        }

        private function shgdprdm_getGuestWooCommerceOrdersForUpdate()
        {
            $this->guestOrderRefs = array();
            if (!empty($this->storedGuestOrders)) {
                foreach ($this->storedGuestOrders as $order) {
                    array_push($this->guestOrderRefs, $order->get_order_number());
                }
            }
            return;
        }

        private function shgdprdm_checkIfGuestEddCustomer()
        {
            if ($this->uem) {
                $eddCustomer = (class_exists('EDD_Customer') ? new EDD_Customer($this->uem)  : '');
                if ($eddCustomer) {
                    $this->storedGuestDownloads = $eddCustomer->payment_ids;
                    return true;
                }
                return false;
            }
            return false;
        }
    
        private function shgdprdm_export()
        {
      
      // extract the Random String
            $this->shgdprdm_extract_ura();
            $this->shgdprdm_extract_uat();
            // wp_die("In Fn: shgdprdm_export");
            switch ($this->ext) {
        case 1:
          // wp_die("In XML");
        // echo serialize($this->ud);
          // shgdprdm_export_xml(NULL);
          // shgdprdm_exportXml($this->ud, $this->uid);
          // if(TRUE){
          //   update_option('shgdprdm_admin_msg',array('class' => 'success', 'msg' => '@DONE!'));
          // }
          if (shgdprdm_exportXml($this->ud, $this->uid)) {
              if ($this->ra) {
                  try {
                      $updated = shgdprdm_updateDbVerifyAction($this->ra, $this->uem, 7, $this->at, $this->guestOrderRefs);
                  } catch (Exception $e) {
                      $updated = false;
                      // wp_die("Failed: ".$e->getMessage());
                  }
              }
              if (false === $updated) {
                  wp_die('Oops! Something has gone wrong. Error Ref: UEC_001');
              }
          } else {
              wp_die('Oops! Something has gone wrong. Error Ref: UEC_002');
          }
        break;

        case 2:
          // wp_die("In CSV");
        // get_user_by('user_email',$this->uem)->ID
          // shgdprdm_exportCsv(NULL, $this->ud, $this->uid);
          if (shgdprdm_exportCsv(null, $this->ud, $this->uid)) {
              if ($this->ra) {
                  try {
                      $updated = shgdprdm_updateDbVerifyAction($this->ra, $this->uem, 7, $this->at, $this->guestOrderRefs);
                  } catch (Exception $e) {
                      $updated = false;
                      // wp_die("Failed: ".$e->getMessage());
                  }
              }
              if (false === $updated) {
                  wp_die('Oops! Something has gone wrong. Error Ref: UEC_003');
              }
          } else {
              wp_die('Oops! Something has gone wrong. Error Ref: UEC_004');
          }
        break;
        case 3:
          // wp_die("In JSON");
          // shgdprdm_exportJson($this->ud, $this->uid);
          if (shgdprdm_exportJson($this->ud, $this->uid)) {
              if ($this->ra) {
                  try {
                      $updated = shgdprdm_updateDbVerifyAction($this->ra, $this->uem, 7, $this->at, $this->guestOrderRefs);
                  } catch (Exception $e) {
                      $updated = false;
                      // wp_die("Failed: ".$e->getMessage());
                  }
              }
              if (false === $updated) {
                  wp_die('Oops! Something has gone wrong. Error Ref: UEC_005');
              }
          } else {
              wp_die('Oops! Something has gone wrong. Error Ref: UEC_006');
          }
        break;

        case 4:
        // echo "REDIRECT: ".self::getRedirectURL();
          // $this->shgdprdm_extract_ura();
          //
          // if($this->ra){
          //   $this->isDisasterSync = shgdprdm_getdisasterSyncVal($this->uem, 6, $this->ra);
          // }
          //
          // $status = shgdprdm_getRequestStatus($this->ra, 6, $this->uem);
          //
          //
          // if( isset($status['status']) ){
          //   if(($status['status'] == 7)){
          //     wp_die('Ready to Download');
          //   }
          // }
          // wp_die("Disaster Sync: ".$this->isDisasterSync);


          // if(!$this->isDisasterSync){
          //   if( shgdprdm_exportJson($this->ud, $this->uid) ){
          //     $processDelete = TRUE;
          //   }
          //   else{
          //     $processDelete = FALSE;
          //   }
          // }
          // else{
          //   $processDelete = TRUE;
          // }



          // wp_die('After JSON Export');


          // if($processDelete){
          //   if ( method_exists($this, 'shgdprdm_register_delete' ) ) {
          //     self::shgdprdm_register_delete();
          //     if(is_numeric($this->uid)){
          //       $deleteBy = $this->uid;
          //     }
          //     else{
          //       $deleteBy = $this->uem;
          //     }
          //     // wp_die('After Register Delete');
          //     // echo "DELETE BY: ".$deleteBy;
          //     if(shgdprdm_deleteUserExternal( $deleteBy )){
          //
                if ($this->ra) {
                    $this->isDisasterSync = shgdprdm_getdisasterSyncVal($this->uem, 6, $this->ra);
                }
                $updateVal = 8;
                if ($this->isDisasterSync) {
                    $updateVal = 106;
                }
                if ($this->ra) {
                    try {
                        $updated = shgdprdm_updateDbVerifyAction($this->ra, $this->uem, $updateVal, 6, $this->guestOrderRefs);
                    } catch (Exception $e) {
                        $updated = false;
                        // wp_die("Failed: ".$e->getMessage());
                    }
                } else {
                    wp_die('Oops! Something has gone wrong. Error Ref: UEC_007');
                    $updated = shgdprdm_updateDbVerifyAction(false, $this->uem, $updateVal, 6, $this->guestOrderRefs);
                }
                // unset( $_POST['shgdprdm_exptd'], $_POST['shgdprdm_export_xml'], $_POST['shgdprdm_export_csv'], $_POST['shgdprdm_export_json'], $_POST['shgdprdm_delete_user'] );
                unset($_POST['shgdprdm_export_xml'], $_POST['shgdprdm_export_csv'], $_POST['shgdprdm_export_json']);

                if (false === $updated) {
                    wp_die('Oops! Something has gone wrong. Error Ref: UEC_008');
                }

          //       // unset( $_POST );
          //       self::shgdprdm_extractCondirmationRedirectURL();
          //     }
          //   }
          //   else{
          //     wp_die('Oops! Something has gone wrong. Error Ref: UEC_002');
          //   }
          //   wp_safe_redirect(get_home_url().self::getRedirectURL());
          // }
          // else{
          //   wp_die('Oops! Something has gone wrong. Error Ref: UEC_003');
          // }
        break;
        default:
        // $this->shgdprdm_extract_ura();

        // if($this->ra){
        //   $this->isDisasterSync = shgdprdm_getdisasterSyncVal($this->uem, 6, $this->ra);
        // }

        // $status = shgdprdm_getRequestStatus($this->ra, 6, $this->uem);
        //
        //
        // if( isset($status['status']) ){
        //   if(($status['status'] == 7)){
        //     wp_die('Ready to Download');
        //   }
        //   else{
        //     wp_die('FAIL: Ready to Download');
        //   }
        // }
        // else{
          wp_die('Oops! Something has gone wrong. Error Ref: UEC_009');
        // }

          exit();
      }
        }

        private function shgdprdm_deleteExport()
        {
            $this->shgdprdm_extract_ura();
            if ($this->ra) {
                $this->isDisasterSync = shgdprdm_getdisasterSyncVal($this->uem, 6, $this->ra);
            }
            // wp_die('IN DELETE EXPORT');
            $status = shgdprdm_getRequestStatus($this->ra, 6, $this->uem);
            // wp_die('Status: '.serialize($status));


            if (isset($status['status'])) {
                // wp_die('Correct Delete Sequence - In Delete Function: Status: '.$status['status'].' (Class Export - shgdprdm_deleteExport)');
                if (($status['status'] == 8) || ($status['status'] == 9) || ($status['status'] == 106)) {
                    if (method_exists($this, 'shgdprdm_register_delete')) {
                        $this->shgdprdm_register_delete();
                        if (is_numeric($this->uid)) {
                            $deleteBy = $this->uid;
                        } else {
                            $deleteBy = $this->uem;
                        }
                        // wp_die('After Register Delete');
                        // echo "DELETE BY: ".$deleteBy;
                        // wp_die('Correct Delete Sequence - In Delete - Delete: '.$deleteBy.' (Class Export - shgdprdm_deleteExport)');
                        if (shgdprdm_deleteUserExternal($deleteBy, $this->isDisasterSync)) {
                            // Update DB Verify Action to 10 (or 11 if Admin)
                            $updateVal = 10;
                            if ($this->isDisasterSync) {
                                $updateVal = 107;
                            }
                            if ($this->ra) {
                                $updated = shgdprdm_updateDbVerifyAction($this->ra, $this->uem, $updateVal, 6, $this->guestOrderRefs);
                                if (false === $updated) {
                                    wp_die('Oops! Something has gone wrong. Error Ref: UEC_010');
                                }
                            } else {
                                $updateVal = 27;
                                if ($this->isDisasterSync) {
                                    $updateVal = 127;
                                }
                                $updated = shgdprdm_updateDbVerifyAction(false, $this->uem, $updateVal, 6, $this->guestOrderRefs);
                                wp_die('Oops! Something has gone wrong. Error Ref: UEC_011');
                            }
                            unset($_POST['shgdprdm_exptd'], $_POST['shgdprdm_export_xml'], $_POST['shgdprdm_export_csv'], $_POST['shgdprdm_export_json'], $_POST['shgdprdm_delete_user']);
                        } else {
                            $updateVal = 27;
                            if ($this->isDisasterSync) {
                                $updateVal = 127;
                            }
                            if ($this->ra) {
                                shgdprdm_updateDbVerifyAction($this->ra, $this->uem, $updateVal, 6);
                            } else {
                                shgdprdm_updateDbVerifyAction(false, $this->uem, $updateVal, 6);
                            }
                            wp_die('Oops! Something has gone wrong. Error Ref: UEC_012');
                        }
                        return;
                    }
                    // else{
                    //   wp_die('Method "Register Delete" Not Found');
                    // }
                    return;
                    // wp_die('Ready to Download');
                }
                // else{
        //   wp_die('FAIL: Ready to Download: '.$status['status']);
        // }
            } else {
                wp_die('Oops! Something has gone wrong. Error Ref: UEC_013');
            }


            exit();
        }


        private function makePostGet()
        {
            if (empty($_GET['ra']) || empty($_GET['at']) || empty($_GET['ue'])) {
                return false;
            }
            return '?ra='.sanitize_text_field($_GET['ra']).'&at='.sanitize_text_field($_GET['at']).'&ue='.sanitize_text_field($_GET['ue']);
        }
        private function validatePost()
        {
            if (empty($_POST['shgdprdm_exptd'])) {
                return false;
            }
            if ($this->makePostGet() == sanitize_text_field($_POST['shgdprdm_exptd'])) {
                return true;
            }
            return false;
        }
    }
}
//
// if( !is_admin() && class_exists('SHGdprdm_UEXP') ){
//     try{
//       new SHGdprdm_UEXP();
//     } catch (Exception $e) {
//       // echo "Error: ".$e;
//       if(strpos($e->getMessage(),'gdpr-data-manager/verify') !== false){
//         wp_safe_redirect( get_home_url().$e->getMessage() );
//         exit;
//       }
//       else{
//         die($e->getMessage());
//         wp_safe_redirect( get_home_url() );
//       }
//     }
// }
