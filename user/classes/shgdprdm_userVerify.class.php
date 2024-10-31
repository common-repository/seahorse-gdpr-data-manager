<?php
defined('SHGDPRDM_ABSPATH') or die('You are not authorised to access this file.');
// echo "In Class shgdprdm_VFY";
if (!class_exists('SHGdprdm_VFY')) {
    class SHGdprdm_VFY
    {
        protected $uPath;
        protected $cPath;
        protected $shgdprdm_user_assets;
        protected $shgdprdm_assets;
        protected $shgdprdm_includes;
        protected $shgdprdm_classes;
        protected $shgdprdm_shared;
        protected $shgdprdm_user_template;
        private $validateControl;
        protected $shgdprdm_user_style;
        protected $shgdprdm_user_script;
        protected $at_url;
        protected $ue_url;
        protected $ra_url;
        protected $at;
        protected $ue;
        protected $ra;
        protected $oData;
        protected $accArr;
        protected $all_meta_for_user;
        protected $user;
        protected $pp_text;
        protected $usrdel;
        protected $pluginDir;
        protected $disasterSync;
        protected $requestStatus;
        protected $requestStatusRef;
        protected $guestOrders;

        public function __construct()
        {
            if (count(func_get_args()) !== 1) {
                $siteEmail = '';
                if (get_bloginfo('admin_email')) {
                    $siteEmail = '<br><a class="button shgdprdm-usr-btn shgdprdm-usr-btn-regular" href="mailto:'.get_bloginfo("admin_email").'">Contact '.get_bloginfo("name").'</a>';
                }
                throw new Exception('<strong>GDPR Data Manager - ERROR!</strong>
        <br>Action cannot be performed. (ref: shgdprdm-UVC-001)
        <br>Please Contact '.get_bloginfo("name").$siteEmail.'.');
            }
            if (isset($_POST['shgdprdm_exptd'])) {
                if (!$this->validatePost()) {
                    throw new Exception('<strong>GDPR Data Manager - ERROR!</strong>
          <br>Action cannot be performed. (ref: shgdprdm-UVC-002)
          <br>Please Contact '.get_bloginfo("name").$siteEmail.'.');
                }
            }


            $this->pluginDir = func_get_args()[0];
            $this->shgdprdm_classes = $this->pluginDir.'/classes/';

            $this->uPath = get_option('shgdprdm_user_path');
            $this->disasterSync = 0;
            // echo "<br>USerPath: ".  $this->uPath;
            // echo "<br>FILE: ".__FILE__;


            // $this->cPath = WP_PLUGIN_DIR.'/seahorse-gdpr-data-manager';
            $this->shgdprdm_user_assets = $this->uPath.'/';
            $this->shgdprdm_assets = $this->pluginDir.'/assets/';
            $this->shgdprdm_includes = $this->pluginDir.'/inc/';
            $this->shgdprdm_shared = $this->pluginDir.'/shared/';
            // $this->shgdprdm_assets = $this->cPath.'/assets/';
            // $this->shgdprdm_includes = $this->cPath.'/inc/';
            $this->shgdprdm_user_template = $this->shgdprdm_user_assets.'templates/shgdprdm_ext_access.php';
            // $this->shgdprdm_user_style = plugins_url().'/seahorse-gdpr-data-manager/user/assets/shgdprdm_user_style.css';
            //$this->shgdprdm_user_script = plugins_url().'/seahorse-gdpr-data-manager/user/assets/shgdprdm_user_script.js';
            $this->shgdprdm_user_style = plugins_url('shgdprdm_user_style.css', $this->uPath.'assets/shgdprdm_user_style.css');
            $this->shgdprdm_user_script = plugins_url('shgdprdm_user_script.js', $this->uPath.'assets/shgdprdm_user_script.js');

            if (method_exists($this, 'register_wp')) {
                self::register_wp();
            }
            if (method_exists($this, 'extract')) {
                self::extract();
            }
            if (method_exists($this, 'confirmValidLink')) {
                // if(self::confirmValidLink()){
                self::confirmValidLink();
                if ($this->requestStatusRef) {
                    if (method_exists($this, 'setDetails')) {
                        self::setDetails();
                    }
                    if (method_exists($this, 'getOrdersForUpdate')) {
                        $this->getOrdersForUpdate();
                    }
                    if (method_exists($this, 'update_status')) {
                        self::update_status();
                    }
                    if ($this->oData) {
                        if (method_exists($this, 'setDisasterSync')) {
                            $this->setDisasterSync();
                        }
                        if (method_exists($this, 'setAaccessBtn')) {
                            self::setAaccessBtn();
                        }
                        if (method_exists($this, 'setPrivacyPolicy')) {
                            self::setPrivacyPolicy();
                        }
                        if (method_exists($this, 'setTandCText')) {
                            self::setTandCText();
                        }
                    }
                }
                // else{
        //   wp_die("Oops...something has gone wrong! Ref. UVC_001 ");
        // }
            }
        }

        private function register_wp()
        {
            add_action('init_register', array($this,'shgdprdm_register_wp'));
            do_action('init_register');
        }

        public function shgdprdm_register_wp()
        {
            /** Load WordPress Administration APIs */
            require_once $this->shgdprdm_assets.'shgdprdm_language.php';
            require_once $this->shgdprdm_includes.'shgdprdm_helpers.php';
            require_once $this->shgdprdm_includes.'shgdprdm_export_helpers.php';
            require_once $this->shgdprdm_includes.'shgdprdm_opt_review.php'; // Required for External DB Update
      
            require_once $this->shgdprdm_includes.'shgdprdm_opt_pending.php';
      
            if (!class_exists('SHGdprdm_ValidateControl')) {
                require_once $this->shgdprdm_shared.'shgdprdm_validate_control.class.php';
            }
            $this->validateControl = new SHGdprdm_ValidateControl();
            
            // require_once $this->shgdprdm_classes.'shgdprdm_wcf.class.php';
            if (
              file_exists($this->shgdprdm_classes.'shgdprdm_wcf.pro.class.php') &&
              $this->validateControl->shgdprdm_validateVerifyLicence() &&
              $this->validateControl->shgdprdm_validateHasLicence() &&
              $this->validateControl->shgdprdm_validateIsProLicence('wcf')
            ) {
                require_once $this->shgdprdm_classes.'shgdprdm_wcf.pro.class.php';
            }
            // if(file_exists($this->shgdprdm_classes.'shgdprdm_eddf.pro.class.php')){
            //   require_once $this->shgdprdm_classes.'shgdprdm_eddf.pro.class.php';
            // }
        
            require_once $this->shgdprdm_classes.'shgdprdm_msf.class.php';
        }

        private function extract()
        {
            if (isset($_GET['at'])) {
                $this->at_url = sanitize_text_field($_GET['at']);
                $at = base64_decode($this->at_url);
                $this->at = unserialize($at);
            } else {
                $this->at = false;
            }

            if (isset($_GET['ue'])) {
                $this->ue_url = sanitize_text_field($_GET['ue']);
                $ue = base64_decode($this->ue_url);
                $this->ue = unserialize($ue);
            } else {
                $this->ue = false;
            }

            if (isset($_GET['ra'])) {
                $this->ra_url = sanitize_text_field($_GET['ra']);
                $ra = base64_decode($this->ra_url);
                $this->ra = unserialize($ra);
            } else {
                // wp_die('No Random String Set');
                $this->ra = false;
            }

            if (isset($_GET['deleted'])) {
                $this->usrdel = sanitize_text_field($_GET['deleted']);
            } else {
                $this->usrdel = false;
            }
        }

        private function update_status()
        {
            // wp_die("in update_status");
            $status = shgdprdm_updateDbPendingStatus($this->ra, $this->at, $this->ue, $this->guestOrders);

            // wp_die("Status: ".$status);
            // echo "This at: ".$this->at;
            // echo "<br>Status: ".$status;
            if (false !== $status) {
                // echo "UPDATE_DB_PENDING STATUS = true";
                // get text output of type
                $this->oData = shgdprdm_pendingConvertType($this->at);
            // echo "THIS oData 1: ".$this->oData;
            } else {
                // echo "UPDATE_DB_PENDING STATUS = false";
                $this->oData = false;
            }
            // echo "<br>THIS oData 2: ".$this->oData;
        }

        private function setDisasterSync()
        {
            $this->disasterSync = shgdprdm_getdisasterSyncVal($this->ue, 6, $this->ra);
        }

        private function setAaccessBtn()
        {
            $this->accArr = shgdprdm_buildExtSubmitAccessArrays($this->at);
            // $this->accArr = shgdprdm_make_ext_action_buttons($this->at);

      // shgdprdm_make_ext_action_buttons
        }

        private function setDetails()
        {
            // get Details
            // $this->user = get_user_by('id', shgdprdm_get_request_status($this->ra, $this->at, $this->ue));
            // $this->all_meta_for_user = get_user_meta(shgdprdm_get_request_status($this->ra, $this->at, $this->ue));


            // $userID = shgdprdm_getRequestStatus($this->ra, $this->at, $this->ue);
            $userID = $this->requestStatusRef;
            if (is_numeric($userID)) {
                $this->user = get_user_by('id', $userID);
                $this->all_meta_for_user = get_user_meta($userID);
            } elseif (is_email($userID)) {
                $this->user = (object)array('user_login'=>'Guest','user_registered'=>'Guest');
                // $orders = wc_get_orders(array('email' => $userID));
                $orders = (function_exists('wc_get_orders') ? wc_get_orders(array('email' => $userID)) : '');
                $eddCustomer = (class_exists('EDD_Customer') ? new EDD_Customer($userID)  : '');
                if ($orders) {
                    $this->all_meta_for_user["first_name"][0] = $orders[0]->get_billing_first_name();
                    $this->all_meta_for_user["last_name"][0] =  $orders[0]->get_billing_last_name();
                }
                // Allow for EDD Guest
                elseif (!empty($eddCustomer)) {
                    $names = explode(" ", $eddCustomer->name);
                    if (!empty($names[0])) {
                        $this->all_meta_for_user["first_name"][0] = $names[0];
                        unset($names[0]);
                        if (!empty($names)) {
                            $surnames = implode(" ", $names);
                        } else {
                            $surnames = 'Unknown';
                        }
                    } else {
                        $this->all_meta_for_user["first_name"][0] = 'Unknown';
                    }

                    if (!empty($surnames)) {
                        $this->all_meta_for_user["last_name"][0] = $surnames;
                    } else {
                        $this->all_meta_for_user["last_name"][0] = 'Unknown';
                    }
                } else {
                    $this->all_meta_for_user["first_name"][0] = 'Unknown';
                    $this->all_meta_for_user["last_name"][0] = 'Unknown';
                }
            } else {
                $this->user = (object)array('user_login'=>'Unknown','user_registered'=>'Unknown');
                $this->all_meta_for_user["first_name"][0] = 'Unknown';
                $this->all_meta_for_user["last_name"][0] = 'Unknown';
            }
        }

        private function getOrdersForUpdate()
        {
            $this->guestOrders = array();
            if (is_email($this->requestStatusRef)) {
                $orders = (function_exists('wc_get_orders') ? wc_get_orders(array('email' => $this->requestStatusRef)) : '');
                if ($orders) {
                    foreach ($orders as $order) {
                        array_push($this->guestOrders, $order->get_order_number());
                    }
                }
            }

            return;
        }

        private function confirmValidLink()
        {
            $userData = shgdprdm_getRequestStatus($this->ra, $this->at, $this->ue);
            // wp_die(print_r($userData));
            $this->requestStatusRef = $userData['userRef'];
            $this->requestStatus = $userData['status'];
            // wp_die($userData['userRef'].' | '.$userData['status']);
      // return shgdprdm_getRequestStatus($this->ra, $this->at, $this->ue);
      // if(!$this->requestStatus){
      //   $this->requestStatus = 10;
      // }
        }

        public function shgdprdm_getRequestStatus()
        {
            return $this->requestStatus;
        }


        private function setTandCText()
        {
            if (isset(get_option('shgdprdm_tandc_options')['tandc_option']) && get_option('shgdprdm_tandc_options')['tandc_option'] != '') {
                $this->tandc_text = get_option('shgdprdm_tandc_options')['tandc_option'];
            } else {
                $this->tandc_text = SHGDPRDM_DEFAULT_TERMS_CONDITIONS_TEXT;
            }
        }


        private function setPrivacyPolicy()
        {
            if (isset(get_option('shgdprdm_ppolicy_options')['ppolicy_option']) && get_option('shgdprdm_ppolicy_options')['ppolicy_option'] != '') {
                $this->pp_text = get_option('shgdprdm_ppolicy_options')['ppolicy_option'];
            } else {
                $this->pp_text =  get_bloginfo('url').'/'.SHGDPRDM_DEFAULT_PRIVACY_POLICY_LINK;
            }
        }

        // public function getUserDetails(){
        //   return $this->user;
        // }
        //
        // public function getUe(){
        //   return $this->ue;
        // }
        //
        // public function getUserMeta(){
        //   return $this->all_meta_for_user;
        // }
        //
        // public function getAccessBtn(){
        //   return $this->accArr;
        // }
        //
        // public function getOdata(){
        //   return $this->oData;
        // }
        //
        // public function getPpol(){
        //   return $this->pp_text;
        // }
        //
        // public function getTemplate(){
        //   return $this->shgdprdm_user_template;
        // }
        //
        // public function getStyles(){
        //   return $this->shgdprdm_user_style;
        // }
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
            if (self::makePostGet() == sanitize_text_field($_POST['shgdprdm_exptd'])) {
                return true;
            }
            return false;
        }
    }
}
