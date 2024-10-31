<?php
/* prevent access from outside cms */
defined('SHGDPRDM_ABSPATH') or die('You are not authorised to access this file.');
// define( 'SHGDPRDM_VERSION', '1.0.0' );

if (!class_exists('SHGdprdm_GdprDataManager')) {
    class SHGdprdm_GdprDataManager
    {
        private $pluginBaseDir;
        private $pluginDirectory;
        private $assetPath;
        private $imgPath;
        private $classPath;
        private $templatesPath;
        private $includesPath;
        private $userPath;
        private $sharedPath;
        public $validateControl;
        public $customers_obj;
        public $hasWooCommerce;

        public function __construct()
        {
            $params= func_get_args();
            if (count($params) !== 1) {
                throw new Exception('<strong>GDPR Data Manager - ERROR!</strong><br>Action cannot be performed. (ref: shgdprdm-CER-001)<br>Please deactivate & reactivate this plugin.');
            }
            $param = $params[0];
            $this->pluginDirectory = plugin_dir_path($param);
            $this->pluginBaseDir = $param;



            if (is_admin()) {
                if (!function_exists('get_plugin_data')) {
                    add_action('admin_init', array($this, 'shgdprdm_plugin_data'));
                }
                $this->assetPath = $this->pluginDirectory.'assets/';
                $this->imgPath = $this->assetPath.'images/';
                $this->classPath = $this->pluginDirectory.'/classes/';
                $this->templatesPath = $this->pluginDirectory.'/templates/';
                $this->includesPath = $this->pluginDirectory.'/inc/';
                $this->userPath = $this->pluginDirectory.'user/';
                $this->sharedPath = $this->pluginDirectory.'shared/';
                
                add_action('admin_init', array($this, 'shgdprdm_incl_validate'));
                $this->validateControl = new SHGdprdm_ValidateControl();
            }
        }

        public function shgdprdm_plugin_data()
        {
            require_once(get_home_path() . 'wp-admin/includes/plugin.php');
        }

        // methods
        public function shgdprdm_register()
        {


        // add_action('admin_init', array($this, 'shgdprdm_language'));
            add_action('admin_init', array($this, 'shgdprdm_admin_register_settings' ));

            add_action('admin_init', array($this, 'shgdprdm_admin_init'));

            add_filter('admin_init', function () {
                return '<strong>TEST FILTER</strong>';
            });

            if (isset($_GET['action']) && $_GET['action'] == 'shgdprdm_export_action') {
                add_action('admin_post_shgdprdm_export_action', array($this, 'shgdprdm_export_action'));
            }

            if (isset($_GET['action']) && $_GET['action'] == 'shgdprdm_export_action') {
                if (isset($_POST['shgdprdm_delete_user']) ||  isset($_POST['shgdprdm_verify_delete']) || isset($_POST['shgdprdm_verify_export'])) {
                    add_action('admin_post_shgdprdm_export_action', array($this, 'shgdprdm_delete_action'));
                } else {
                    wp_die('<strong>GDPR Data Manager Plugin Says:<br>ERROR!</strong><br>Unauthorised Action<br><em>(ref: shgdprdm-CER-002)</em>');
                }
            }

            if (isset($_GET['action']) && $_GET['action'] == 'shgdprdm_external_sync') {
                if (isset($_POST['shgdprdm_sync_ext_record_ref'])) {
                    add_action('admin_post_shgdprdm_external_sync', array($this, 'shgdprdm_sync_missing_record'));
                } else {
                    wp_die('<strong>GDPR Data Manager Plugin Says:<br>ERROR!</strong><br>Unauthorised Action<br><em>(ref: shgdprdm-CER-003)</em>');
                }
            }

            if (isset($_GET['action']) &&  $_GET['action'] != 'shgdprdm_search_action_hook' && $_GET['action'] != 'shgdprdm_export_action'  && $_GET['action'] != 'shgdprdm_external_sync' &&
              (strpos($_GET['action'], 'activate') < 0) &&  (strpos($_GET['action'], 'deactivate') < 0)) {
                wp_die('<strong>GDPR Data Manager Plugin Says:<br>ERROR!</strong><br>Unauthorised Action<br><em>(ref: shgdprdm-CER-004)</em>');
            }

            if (isset($_POST['search-return']) && isset($_POST['search-return']) && $_POST['search-return']) {
                add_action('admin_init', array($this, 'shgdprdm_search_action_return'));
            }

            add_action('admin_init', array($this, 'shgdprdm_language'));
            add_action('admin_init', array($this, 'shgdprdm_errors_action'));
            add_action('admin_init', array($this, 'shgdprdm_helpers' ));

            
            
            add_action('admin_enqueue_scripts', array($this, 'shgdprdm_enqueue_assets'));

            add_action('admin_enqueue_scripts', array($this, 'shgdprdm_enqueue_action_assets'));

            add_action('admin_enqueue_scripts', array($this, 'codemirror_enqueue_scripts'));

            add_action('admin_init', array($this, 'shgdprdm_register_conditional_scripts'));

            add_action('admin_menu', array($this, 'shgdprdm_add_admin_page'));

            add_action('admin_menu', array($this, 'shgdprdm_registerLicence'));

            add_action('admin_init', array($this, 'shgdprdm_register_settings_cb'));

            add_action('admin_init', array($this, 'shgdprdm_main'));
            
            add_action('admin_init', array($this, 'shgdprdm_home'));

            add_action('admin_init', array($this, 'shgdprdm_pending'));

            add_action('admin_init', array($this, 'shgdprdm_options'));

            add_action('admin_init', array($this, 'shgdprdm_records'));

            add_action('admin_init', array($this, 'shgdprdm_help'));

            if (isset($_GET['action']) && $_GET['action'] == 'shgdprdm_search_action_hook') {
                add_action('admin_post_shgdprdm_search_action_hook', array($this, 'shgdprdm_search_action'));
            }

            // Add a link to settings page beside the plugin name
            add_filter("plugin_action_links_seahorse-gdpr-data-manager/seahorse-gdpr-data-manager.php", array($this, 'shgdprdm_settings_link'));

            // This adds notice into the WP notice region
            add_action('admin_notices', array($this, 'shgdprdm_admin_notice__dynamic'));

            add_action('plugins_loaded', array($this, 'shgdprdm_review'));

            add_action('plugins_loaded', array($this, 'shgdprdm_has_woocommerce'));

            add_filter('generate_rewrite_rules', array($this,  'shgdprdm_ext_usr_url_rule'));
        }

        // https://metabox.io/how-to-create-a-virtual-page-in-wordpress/


        // Add redirect for the externally aessed user file
        public function shgdprdm_ext_usr_url_rule()
        { // filter
            add_rewrite_rule('gdpr-data-manager/verify/?$', 'index.php?page=seahorse-gdpr-data-manager', 'top');
        }

        public function shgdprdm_url_flush_rewrite_rules()
        {
            global $wp_rewrite;
            $wp_rewrite->flush_rules();
        }

        public function shgdprdm_settings_link($links)
        {
            $settings_link = '<a href="admin.php?page=seahorse_gdpr_data_manager_plugin&tab=gdpr_data_manager_privacy_policy">Settings</a>';
            array_push($links, $settings_link);
            return $links;
        }
        // public function shgdprdm_settings_link($links){
        //     $settings_link = '<a href="admin.php?page=seahorse_gdpr_data_manager_plugin&tab=gdpr_data_manager_search_options">Settings</a>';
        //     array_push($links,$settings_link);
        //     return $links;
        // }

        public function shgdprdm_add_admin_page()
        {
            $page = array($this, 'shgdprdm_admin_index');
            
            $this->validateControl = new SHGdprdm_ValidateControl();
        
            $proIcon = '';
            if (
                file_exists($this->classPath.'shgdprdm_wcf.pro.class.php') &&
                file_exists($this->classPath.'shgdprdm_eddf.pro.class.php') &&
                $this->validateControl->shgdprdm_validateVerifyLicence() &&
                $this->validateControl->shgdprdm_validateHasLicence() &&
                ($this->validateControl->shgdprdm_validateIsProLicence('wcf') || $this->validateControl->shgdprdm_validateIsProLicence('eddf'))
                ) {
                // $proIcon = ' <span><img style="height:16px; width:16px;" src = "'.plugins_url('assets/images/shgdprdm_icon_pro.png' , dirname(__FILE__) ).'"/></span>';
                $proIcon = ' <span><strong><em> (PRO)</em></strong></span>';
            } elseif ($this->validateControl->shgdprdm_validateVerifyLicence() && $this->validateControl->shgdprdm_validateHasLicence()) {
                $proIcon = ' <span><strong><em> (Standard)</em></strong></span>';
            } else {
                $proIcon = ' <span><strong><em> (Basic)</em></strong></span>';
            }
            
            add_menu_page(
                'Seahorse GDPR Data Manager Plugin',
                'GDPR Data Manager'.$proIcon,
                'manage_options',
                'seahorse_gdpr_data_manager_plugin',
                $page,
                plugins_url('assets/images/shgdprdm_icon1616.png', dirname(__FILE__)),
                110
        );

            // add submenu page (for my dashboard page)
            add_submenu_page(
                'seahorse_gdpr_data_manager_plugin',
                'Your Database',
                'Your Database',
                'manage_options',
                'seahorse_gdpr_data_manager_plugin&tab=gdpr_data_manager_home',
                $page
        );

            // add submenu page (for Options Page)
            add_submenu_page(
                'seahorse_gdpr_data_manager_plugin',
                'Privacy Policy',
                'Privacy Policy',
                'manage_options',
                'seahorse_gdpr_data_manager_plugin&tab=gdpr_data_manager_privacy_policy',
                $page
        );

            // add_submenu_page(
            //   'seahorse_gdpr_data_manager_plugin',
            //   'Options',
            //   'Options',
            //   'manage_options',
            //   'seahorse_gdpr_data_manager_plugin&tab=gdpr_data_manager_search_options',
            //   $page
            // );

            // add submenu page (for Pending Page)
            add_submenu_page(
                'seahorse_gdpr_data_manager_plugin',
                'Pending',
                'Pending',
                'manage_options',
                'seahorse_gdpr_data_manager_plugin&tab=gdpr_data_manager_pending_actions',
                $page
        );



            // add submenu page (for Activity History Page)
            add_submenu_page(
                'seahorse_gdpr_data_manager_plugin',
                'History',
                'History',
                'manage_options',
                'seahorse_gdpr_data_manager_plugin&tab=gdpr_data_manager_review_history',
                $page
        );

            // add submenu page (for Help & Support Page)
            add_submenu_page(
                'seahorse_gdpr_data_manager_plugin',
                'Help & Support',
                'Help & Support',
                'manage_options',
                'seahorse_gdpr_data_manager_plugin&tab=gdpr_data_manager_help',
                $page
        );
        }

        public function shgdprdm_register_settings_cb()
        {
            register_setting('seahorse-gdpr-data-manager-settings-group', 'seahorse_gdpr_data_manager_options', 'shgdprdm_options_sanitize');
            register_setting('seahorse-gdpr-data-manager-settings-group-search', 'seahorse_gdpr_data_manager_search_options', 'shgdprdm_search_options_sanitize');
        } // end seahorse_gdpr_data_manager_register_settings_cb

        public function shgdprdm_options_sanitize($input)
        {
            $input['name'] = sanitize_text_field($input['name']);
            $input['email'] = sanitize_email($input['email']);
            $input['url'] = esc_url($input['url']);
            return $input;
        } // end seahorse_gdpr_data_manager_options_sanitize

        public function shgdprdm_search_options_sanitize($input)
        {
            $input['emailSearch'] = sanitize_text_field($input['emailSearch']);
            return $input;
        } // end seahorse_gdpr_data_manager_options_sanitize

        public function shgdprdm_admin_register_settings()
        {
            // Add the section to reading settings so we can add our fields to it
            add_settings_section(
                'seahorse_gdpr_data_manager_section',
                'Example settings section in GDPR Data Manager',
                'seahorse_gdpr_data_manager_section_callback_function',
                'admin'
          );
            // Add the field with the names and function to use for our new settings, put it in our new section
            add_settings_field(
                'seahorse_gdpr_data_manager_option_name',
                'GDPR Data Manager',
                'seahorse_gdpr_data_manager_callback_function',
                'GDPR Data Manager',
                'seahorse_gdpr_data_manager_section'
          );

            add_settings_field(
                'seahorse_gdpr_data_manager_search_option_name',
                'GDPR Data Manager',
                'seahorse_gdpr_data_manager_callback_function',
                'GDPR Data Manager',
                'seahorse_gdpr_data_manager_section'
          );
            // Register our setting so that $_POST handling is done for us and
            // our callback function just has to echo the <input>
            register_setting('GDPR Data Manager', 'seahorse_gdpr_data_manager_option_name');
            register_setting('GDPR Data Manager', 'seahorse_gdpr_data_manager_search_option_name');
            register_setting('seahorse_gdpr_data_manager_options_group', 'seahorse_gdpr_data_manager_option_name', 'seahorse_gdpr_data_manager_callback');
        }

        public function shgdprdm_admin_init()
        {
            // delete_option('shgdprdm_adminHasLicence');
            register_setting('shgdprdm_search_options', 'shgdprdm_search_options', 'shgdprdm_search_options_validate');
            add_settings_section('shgdprdm_main', 'User Search Option', array( $this, 'shgdprdm_search_option_section_text'), 'shgdprdm_plugin');
            add_settings_field('shgdprdm_text_string', 'Search By: ', array( $this, 'shgdprdm_search_option_setting_string'), 'shgdprdm_plugin', 'shgdprdm_main');


            // Register settings for selecting/de-selecting which supported plugins to include in the search
            if (defined('SHGDPRDM_SUPPORTED_PLUGINS_OPTIONS')) {
                foreach (unserialize(SHGDPRDM_SUPPORTED_PLUGINS_OPTIONS) as $option) {
                    register_setting('shgdprdm_admin_plugins_settings_group', $option);
                }
            } else {
                if (SHGDPRDM_TESTING) {
                    wp_die("Main Class: Line 261");
                }
            }

            
            add_settings_section('shgdprdm_main', 'Refine Your Search', array( $this, 'shgdprdm_admin_plugins_settings_section_text'), 'shgdprdm_extra_search');
            add_settings_field('shgdprdm_text_string', 'Refine By: ', array( $this, 'shgdprdm_admin_plugins_settings_setting_string'), 'shgdprdm_extra_search', 'shgdprdm_main');

            register_setting('shgdprdm_text_options', 'shgdprdm_text_options', array($this,'shgdprdm_text_options_validate'));
            add_settings_section('shgdprdm_main', 'Replacement Text Setting', array( $this, 'shgdprdm_text_option_section_text'), 'shgdprdm_text');
            add_settings_field('shgdprdm_text_string', 'Replacement Text', array( $this, 'shgdprdm_text_option_setting_string'), 'shgdprdm_text', 'shgdprdm_main');


            // Added by AB - 15/06/19 PP General information
            register_setting('shgdprdm_ppolicy_gen_options', 'shgdprdm_ppolicy_gen_options', array($this,'shgdprdm_ppolicy_gen_options_validate'));
            add_settings_section('shgdprdm_header', 'Privacy Policy "General information" Section', array( $this, 'shgdprdm_ppolicy_gen_header_option_section_text'), 'shgdprdm_ppolicy_gen_options');
            add_settings_section('shgdprdm_main', '', array( $this, 'shgdprdm_ppolicy_gen_option_section_text'), 'shgdprdm_ppolicy_gen_options');

            add_settings_field('shgdprdm_text_string_header', 'Section Heading:', array( $this, 'shgdprdmPpolicyGenHeaderOptionSettingString'), 'shgdprdm_ppolicy_gen_options', 'shgdprdm_header');
            add_settings_field('shgdprdm_text_string', 'Section Content:', array( $this, 'shgdprdm_ppolicy_gen_option_setting_string'), 'shgdprdm_ppolicy_gen_options', 'shgdprdm_main');


            // Updated by PC - 29/05/19 (Re-named header option parent to match main option parent ('shgdprdm_tandc_options'). Removed 2nd validation. Grouped setting sections & setting fields into same section)
            register_setting('shgdprdm_tandc_options', 'shgdprdm_tandc_options', array($this,'shgdprdm_tandc_options_validate'));
            add_settings_section('shgdprdm_header', 'Privacy Policy "Right to Data" Section', array( $this, 'shgdprdm_tandc_header_option_section_text'), 'shgdprdm_tandc_options');
            add_settings_section('shgdprdm_main', '', array( $this, 'shgdprdm_tandc_option_section_text'), 'shgdprdm_tandc_options');

            add_settings_field('shgdprdm_text_string_header', 'Section Heading:', array( $this, 'shgdprdm_tandc_header_option_setting_string'), 'shgdprdm_tandc_options', 'shgdprdm_header');
            add_settings_field('shgdprdm_text_string', 'Section Content:', array( $this, 'shgdprdm_tandc_option_setting_string'), 'shgdprdm_tandc_options', 'shgdprdm_main');

            register_setting('shgdprdm_ppolicy_options', 'shgdprdm_ppolicy_options', array($this,'shgdprdm_ppolicy_options_validate'));
            add_settings_section('shgdprdm_main', '', array( $this, 'shgdprdm_ppolicy_option_section_text'), 'shgdprdm_ppolicy');
            add_settings_field('shgdprdm_text_string', 'Privacy Policy Link', array( $this, 'shgdprdm_ppolicy_option_setting_string'), 'shgdprdm_ppolicy', 'shgdprdm_main');
        
        
            // Added by AB - 16/06/19 PP Management of personal data
            register_setting('shgdprdm_ppolicy_mng_options', 'shgdprdm_ppolicy_mng_options', array($this,'shgdprdm_ppolicy_mng_options_validate'));
            add_settings_section('shgdprdm_header', 'Privacy Policy "Management of personal data" Section', array( $this, 'shgdprdm_ppolicy_mng_header_option_section_text'), 'shgdprdm_ppolicy_mng_options');
            add_settings_section('shgdprdm_main', '', array( $this, 'shgdprdm_ppolicy_mng_option_section_text'), 'shgdprdm_ppolicy_mng_options');

            add_settings_field('shgdprdm_text_string_header', 'Section Heading:', array( $this, 'shgdprdm_ppolicy_mng_header_option_setting_string'), 'shgdprdm_ppolicy_mng_options', 'shgdprdm_header');
            add_settings_field('shgdprdm_text_string', 'Section Content:', array( $this, 'shgdprdm_ppolicy_mng_option_setting_string'), 'shgdprdm_ppolicy_mng_options', 'shgdprdm_main');
        
        
            // Added by AB - 16/06/19 PP Information we collect
            register_setting('shgdprdm_ppolicy_ico_options', 'shgdprdm_ppolicy_ico_options', array($this,'shgdprdm_ppolicy_ico_options_validate'));
            add_settings_section('shgdprdm_header', 'Privacy Policy "Information we collect" Section', array( $this, 'shgdprdm_ppolicy_ico_header_option_section_text'), 'shgdprdm_ppolicy_ico_options');
            add_settings_section('shgdprdm_main', '', array( $this, 'shgdprdm_ppolicy_ico_option_section_text'), 'shgdprdm_ppolicy_ico_options');

            add_settings_field('shgdprdm_text_string_header', 'Section Heading:', array( $this, 'shgdprdm_ppolicy_ico_header_option_setting_string'), 'shgdprdm_ppolicy_ico_options', 'shgdprdm_header');
            add_settings_field('shgdprdm_text_string', 'Section Content:', array( $this, 'shgdprdm_ppolicy_ico_option_setting_string'), 'shgdprdm_ppolicy_ico_options', 'shgdprdm_main');
        
        
            // Added by AB - 16/06/19 PP How we use your information
            register_setting('shgdprdm_ppolicy_use_options', 'shgdprdm_ppolicy_use_options', array($this,'shgdprdm_ppolicy_use_options_validate'));
            add_settings_section('shgdprdm_header', 'Privacy Policy "How we use information" Section', array( $this, 'shgdprdm_ppolicy_use_header_option_section_text'), 'shgdprdm_ppolicy_use_options');
            add_settings_section('shgdprdm_main', '', array( $this, 'shgdprdm_ppolicy_use_option_section_text'), 'shgdprdm_ppolicy_use_options');

            add_settings_field('shgdprdm_text_string_header', 'Section Heading:', array( $this, 'shgdprdm_ppolicy_use_header_option_setting_string'), 'shgdprdm_ppolicy_use_options', 'shgdprdm_header');
            add_settings_field('shgdprdm_text_string', 'Section Content:', array( $this, 'shgdprdm_ppolicy_use_option_setting_string'), 'shgdprdm_ppolicy_use_options', 'shgdprdm_main');
        
        
            // Added by AB - 16/06/19 PP Sharing your information
            register_setting('shgdprdm_ppolicy_sha_options', 'shgdprdm_ppolicy_sha_options', array($this,'shgdprdm_ppolicy_sha_options_validate'));
            add_settings_section('shgdprdm_header', 'Privacy Policy "Sharing your information" Section', array( $this, 'shgdprdm_ppolicy_sha_header_option_section_text'), 'shgdprdm_ppolicy_sha_options');
            add_settings_section('shgdprdm_main', '', array( $this, 'shgdprdm_ppolicy_sha_option_section_text'), 'shgdprdm_ppolicy_sha_options');

            add_settings_field('shgdprdm_text_string_header', 'Section Heading:', array( $this, 'shgdprdm_ppolicy_sha_header_option_setting_string'), 'shgdprdm_ppolicy_sha_options', 'shgdprdm_header');
            add_settings_field('shgdprdm_text_string', 'Section Content:', array( $this, 'shgdprdm_ppolicy_sha_option_setting_string'), 'shgdprdm_ppolicy_sha_options', 'shgdprdm_main');
        

            register_setting('shgdprdm_reglicnum_options', 'shgdprdm_reglicnum_options', array($this,'shgdprdm_reglicnum_options_validate'));

            add_settings_section('shgdprdm_main', 'Register & Validate Your Plugin Licence Number', array( $this, 'shgdprdm_reglicnum_section_text'), 'shgdprdm_reglicnum');
            add_settings_field('shgdprdm_text_string', 'Plugin Licence Number', array( $this, 'shgdprdm_reglicnum_setting_string'), 'shgdprdm_reglicnum', 'shgdprdm_main');

            register_setting('shgdprdm_admin_msg_options', 'shgdprdm_admin_msg', 'shgdprdm_admin_msg_options_validate');
            register_setting('shgdprdm_admin_msg_options', 'shgdprdm_admin_notice', 'shgdprdm_admin_notice_options_validate');

            register_setting('shgdprdm_user_msg_options', 'shgdprdm_user_msg', 'shgdprdm_user_msg_options_validate');

            register_setting('shgdprdm_admin_plugin_options', 'shgdprdm_admin_plugins', 'shgdprdm_admin_plugin_options_validate');

            register_setting('shgdprdm_sync_delete', 'shgdprdm_sync_delete', 'shgdprdm_sync_delete_options_validate');
        }

        public function shgdprdm_search_option_section_text()
        {
            echo '<p>Option for how User Data is Searched</p>';
        }

        public function shgdprdm_admin_plugins_settings_section_text()
        {
            echo '<p>Option for Refining the depth of your Search </p>';
        }

        public function shgdprdm_text_option_section_text()
        {
            echo '<p>Setting for Replacement Text In Database</p>';
        }


        public function shgdprdm_ppolicy_gen_option_section_text()
        {
            echo '';
        }

        public function shgdprdm_ppolicy_gen_header_option_section_text()
        {
            echo '<p>Text for your Site\'s Privacy Policy: General section</p>';
        }

  
        public function shgdprdm_ppolicy_mng_option_section_text()
        {
            echo '';
        }

        public function shgdprdm_ppolicy_mng_header_option_section_text()
        {
            echo '<p>Text for your Site\'s Privacy Policy: Management section</p>';
        }

    
    
        public function shgdprdm_ppolicy_ico_option_section_text()
        {
            echo '';
        }

        public function shgdprdm_ppolicy_ico_header_option_section_text()
        {
            echo '<p>Text for your Site\'s Privacy Policy: Information Collection section</p>';
        }

        public function shgdprdm_ppolicy_use_option_section_text()
        {
            echo '';
        }

        public function shgdprdm_ppolicy_use_header_option_section_text()
        {
            echo '<p>Text for your Site\'s Privacy Policy: How we use information section</p>';
        }

      
        public function shgdprdm_ppolicy_sha_option_section_text()
        {
            echo '';
        }

        public function shgdprdm_ppolicy_sha_header_option_section_text()
        {
            echo '<p>Text for your Site\'s Privacy Policy: Sharing information section</p>';
        }

    

        public function shgdprdm_tandc_option_section_text()
        {
            echo '';
        }

        public function shgdprdm_tandc_header_option_section_text()
        {
            echo '<p>Text for your Site\'s Privacy Policy: Right to Data section</p>';
        }

        public function shgdprdm_ppolicy_option_section_text()
        {
            //echo '<p>URL for your Site\'s Privacy Policy</p>';
            echo '';
        }

        public function shgdprdm_reglicnum_section_text()
        {
            echo '<p>Register & Validate your Plugin\'s Licence Number</p>';
        }

        public function shgdprdm_search_option_setting_string()
        {
            $options = get_option('shgdprdm_search_options');
            $emailSelect = '';
            $idSelect = '';
            if ($options['search_option'] == '1') {
                $emailSelect = 'checked';
                $idSelect = '';
            }
            if ($options['search_option'] == '2') {
                $idSelect = 'checked';
                $emailSelect = '';
            }
            $html =  "
      <div class='shgdprdm-checkboxgroup'>
      <input type='radio' id='shgdprdm_search_options[search_option][email]' name='shgdprdm_search_options[search_option]' class='shgdprdm-radio-email' value='1' {$emailSelect} />
      <label for='shgdprdm_search_options[search_option][email]'>User Email</label>
      </div>
      <div class='shgdprdm-checkboxgroup'>
      <input type='radio' id='shgdprdm_search_options[search_option][id]' name='shgdprdm_search_options[search_option]' class='shgdprdm-radio-id' value='2' {$idSelect} />
      <label for='shgdprdm_search_options[search_option][id]'>User ID</label>
      </div>";
            echo $html;
        }

        public function shgdprdm_admin_plugins_settings_setting_string()
        {
            $options = shgdprdm_getOptionsGroup('shgdprdm_admin_plugins_settings_group');
            if (defined('SHGDPRDM_SUPPORTED_PLUGINS_OPTIONS')) {
                echo shgdprdm_searchExtraOptionFields(unserialize(SHGDPRDM_SUPPORTED_PLUGINS_OPTIONS), $options);
            } else {
                if (SHGDPRDM_TESTING) {
                    die("Main Class: Line 332");
                }
            }
        }

        public function shgdprdm_text_option_setting_string()
        {
            if (isset(get_option('shgdprdm_text_options')['text_option']) && get_option('shgdprdm_text_options')['text_option'] != '') {
                $text = get_option('shgdprdm_text_options')['text_option'];
            } else {
                $text =  SHGDPRDM_DEFAULT_REPLACEMENT_TEXT_IN_DB;
            }
            $placeholder = SHGDPRDM_DEFAULT_REPLACEMENT_TEXT_IN_DB;
            echo "<textarea id='shgdprdm_text_option_input' name='shgdprdm_text_options[text_option]' rows='3' cols='5' wrap='soft' value='{$text}' placeholder='{$placeholder}' disabled>{$text}</textarea>";
        }

        private function shgdprdmPrivacyPolicyFieldText($optionName, $optionSubName, $defaultText)
        {
            // Return Default Text if there is no Option Available
            if (false === get_option($optionName)) {
                return $returnText = $defaultText;
            }

            // Get the Site Locale
            $locale = get_locale();
            // Get the Option
            $option = get_option($optionName);

            if (isset($option[$locale][$optionSubName]) && $option[$locale][$optionSubName] !== '') {
                // New Locale specific text
                $returnText = $option[$locale][$optionSubName];
            } elseif (isset($option['legacy'][$optionSubName]) && $option['legacy'][$optionSubName] !== '' && !is_array($option['legacy'][$optionSubName])) {
                // Previously Saved & Updated
                $returnText = $option['legacy'][$optionSubName];
            } elseif (isset($option[$optionSubName]) && $option[$optionSubName] !== '' && !is_array($option[$optionSubName])) {
                // Previously saved
                $returnText = $option[$optionSubName];
            } else {
                // Default Text
                $returnText = $defaultText;
            }

            return $returnText;
        }

        // PP Gen header
        public function shgdprdmPpolicyGenHeaderOptionSettingString()
        {
            $text = $this->shgdprdmPrivacyPolicyFieldText('shgdprdm_ppolicy_gen_options', 'ppolicy_gen_header_option', SHGDPRDM_DEFAULT_POLICY_GEN_HEADER_CONDITIONS_TEXT);
            echo "<input id='shgdprdm_ppolicy_gen_header_option_input' class='pp_section_header' name='shgdprdm_ppolicy_gen_options[ppolicy_gen_header_option]' type='text' value='{$text}'/>";
        }

        // PP Gen body
        public function shgdprdm_ppolicy_gen_option_setting_string()
        {
            $content = $this->shgdprdmPrivacyPolicyFieldText('shgdprdm_ppolicy_gen_options', 'ppolicy_gen_option', SHGDPRDM_DEFAULT_POLICY_GEN_CONDITIONS_TEXT);
            $editor_id = 'shgdprdm_ppolicy_gen_option_input';
            $settings = array( 'textarea_name' => 'shgdprdm_ppolicy_gen_options[ppolicy_gen_option]', 'editor_height' => 325 );
            echo wp_editor($content, $editor_id, $settings);
        }
  

        public function shgdprdm_tandc_option_setting_string()
        {
            $text = $this->shgdprdmPrivacyPolicyFieldText('shgdprdm_tandc_options', 'tandc_option', SHGDPRDM_DEFAULT_TERMS_CONDITIONS_TEXT);
            if (function_exists('shgdprdm_adminNavCheckStatus') && shgdprdm_adminNavCheckStatus()) {
                $content = $text;
                $editor_id = 'shgdprdm_tandc_option_input';
                $settings = array( 'textarea_name' => 'shgdprdm_tandc_options[tandc_option]', 'editor_height' => 325);
                echo wp_editor($content, $editor_id, $settings);
            } else {
                echo "<textarea id='shgdprdm_tandc_option_input' name='shgdprdm_tandc_options[tandc_option]' disabled rows='3' cols='5' value='{$text}' placeholder=''>{$text}</textarea>";
            }
        }

        // Updated by PC - 29/05/19 (Re-named header option parent to match main option parent ('shgdprdm_tandc_options'). sanitized input)
        public function shgdprdm_tandc_header_option_setting_string()
        {
            $text = $this->shgdprdmPrivacyPolicyFieldText('shgdprdm_tandc_options', 'tandc_header_option', SHGDPRDM_DEFAULT_TERMS_HEADER_CONDITIONS_TEXT);
            
            if (function_exists('shgdprdm_adminNavCheckStatus') && shgdprdm_adminNavCheckStatus()) {
                echo "<input id='shgdprdm_tandc_header_option_input' class='pp_section_header' name='shgdprdm_tandc_options[tandc_header_option]' type='text' value='{$text}'/>";
            } else {
                echo "<input id='shgdprdm_tandc_header_option_input' class='pp_section_header' name='shgdprdm_tandc_options[tandc_header_option]' type='text' value='{$text}' disabled>";
            }
        }

        public function shgdprdm_ppolicy_option_setting_string()
        {
            $text = $this->shgdprdmPrivacyPolicyFieldText('shgdprdm_ppolicy_options', 'ppolicy_option', SHGDPRDM_DEFAULT_PRIVACY_POLICY_LINK);

            if (function_exists('shgdprdm_adminNavCheckStatus') && shgdprdm_adminNavCheckStatus()) {
                echo "<label class='shgdprdm-option-label' for='shgdprdm_ppolicy_option_input'>".get_bloginfo('url')."/</label>
                <input id='shgdprdm_ppolicy_option_input' name='shgdprdm_ppolicy_options[ppolicy_option]' type='text' value='{$text}'/>";
            } else {
                echo "<label class='shgdprdm-option-label' for='shgdprdm_ppolicy_option_input'>".get_bloginfo('url')."/</label>
                <input id='shgdprdm_ppolicy_option_input' name='shgdprdm_ppolicy_options[ppolicy_option]' type='text' value='{$text}'disabled>";
            }
        }

    
      
        // PP mng header
        public function shgdprdm_ppolicy_mng_header_option_setting_string()
        {
            $text = $this->shgdprdmPrivacyPolicyFieldText('shgdprdm_ppolicy_mng_options', 'ppolicy_mng_header_option', SHGDPRDM_DEFAULT_POLICY_MNG_HEADER_CONDITIONS_TEXT);
            
            echo "<input id='shgdprdm_ppolicy_mng_header_option_input' class='pp_section_header' name='shgdprdm_ppolicy_mng_options[ppolicy_mng_header_option]' type='text' value='{$text}'/>";
        }

        // PP mng body
        public function shgdprdm_ppolicy_mng_option_setting_string()
        {
            $content = $this->shgdprdmPrivacyPolicyFieldText('shgdprdm_ppolicy_mng_options', 'ppolicy_mng_option', SHGDPRDM_DEFAULT_POLICY_MNG_CONDITIONS_TEXT);

            $editor_id = 'shgdprdm_ppolicy_mng_option_input';
            $settings = array( 'textarea_name' => 'shgdprdm_ppolicy_mng_options[ppolicy_mng_option]', 'editor_height' => 325 );
            echo wp_editor($content, $editor_id, $settings);
        }

    
    
        // PP ico header
        public function shgdprdm_ppolicy_ico_header_option_setting_string()
        {
            $text = $this->shgdprdmPrivacyPolicyFieldText('shgdprdm_ppolicy_ico_options', 'ppolicy_ico_header_option', SHGDPRDM_DEFAULT_POLICY_ICO_HEADER_CONDITIONS_TEXT);

            echo "<input id='shgdprdm_ppolicy_ico_header_option_input' class='pp_section_header' name='shgdprdm_ppolicy_ico_options[ppolicy_ico_header_option]' type='text' value='{$text}'/>";
        }

        // PP ico body
        public function shgdprdm_ppolicy_ico_option_setting_string()
        {
            $content = $this->shgdprdmPrivacyPolicyFieldText('shgdprdm_ppolicy_ico_options', 'ppolicy_ico_option', SHGDPRDM_DEFAULT_POLICY_ICO_CONDITIONS_TEXT);

            $editor_id = 'shgdprdm_ppolicy_ico_option_input';
            $settings = array( 'textarea_name' => 'shgdprdm_ppolicy_ico_options[ppolicy_ico_option]', 'editor_height' => 325 );
            echo wp_editor($content, $editor_id, $settings);
        }
    
    
    
        
        // PP USE header
        public function shgdprdm_ppolicy_use_header_option_setting_string()
        {
            $text = $this->shgdprdmPrivacyPolicyFieldText('shgdprdm_ppolicy_use_options', 'ppolicy_use_header_option', SHGDPRDM_DEFAULT_POLICY_USE_HEADER_CONDITIONS_TEXT);

            echo "<input id='shgdprdm_ppolicy_use_header_option_input' class='pp_section_header' name='shgdprdm_ppolicy_use_options[ppolicy_use_header_option]' type='text' value='{$text}'/>";
        }

        // PP USE body
        public function shgdprdm_ppolicy_use_option_setting_string()
        {
            $content = $this->shgdprdmPrivacyPolicyFieldText('shgdprdm_ppolicy_use_options', 'ppolicy_use_option', SHGDPRDM_DEFAULT_POLICY_USE_CONDITIONS_TEXT);

            $editor_id = 'shgdprdm_ppolicy_use_option_input';
            $settings = array( 'textarea_name' => 'shgdprdm_ppolicy_use_options[ppolicy_use_option]', 'editor_height' => 325 );
            echo wp_editor($content, $editor_id, $settings);
        }
    
    
    
            
        // PP SHA header
        public function shgdprdm_ppolicy_sha_header_option_setting_string()
        {
            $text = $this->shgdprdmPrivacyPolicyFieldText('shgdprdm_ppolicy_sha_options', 'ppolicy_sha_header_option', SHGDPRDM_DEFAULT_POLICY_SHA_HEADER_CONDITIONS_TEXT);

            echo "<input id='shgdprdm_ppolicy_sha_header_option_input' class='pp_section_header' name='shgdprdm_ppolicy_sha_options[ppolicy_sha_header_option]' type='text' value='{$text}'/>";
        }

        // PP USE body
        public function shgdprdm_ppolicy_sha_option_setting_string()
        {
            $content = $this->shgdprdmPrivacyPolicyFieldText('shgdprdm_ppolicy_sha_options', 'ppolicy_sha_option', SHGDPRDM_DEFAULT_POLICY_SHA_CONDITIONS_TEXT);

            $editor_id = 'shgdprdm_ppolicy_sha_option_input';
            $settings = array( 'textarea_name' => 'shgdprdm_ppolicy_sha_options[ppolicy_sha_option]', 'editor_height' => 325 );
            echo wp_editor($content, $editor_id, $settings);
        }
    
    

        public function shgdprdm_reglicnum_setting_string()
        {
            $text = '';
            $disabled = '';
            $placeholder =  'Please enter your Licence Number (delivered with your purchase confirmation)';

            if (false !== get_option('shgdprdm_adminVerifyLicence')) {
                $text = get_option('shgdprdm_adminVerifyLicence')['licence_number'];
                if (get_option('shgdprdm_adminVerifyLicence')['licence_valid'] === true) {
                    $disabled = 'disabled';
                }
            }
            echo "<input id='shgdprdm_reglicnum_input' name='shgdprdm_reglicnum_options[licence_number]' type='text' value='{$text}' placeholder='{$placeholder}' {$disabled}/>";
        }

        // validate our options
        public function shgdprdm_search_options_validate($input)
        {
            $newinput['text_string'] = trim($input['text_string']);
            if (!preg_match('/^[a-z0-9]{32}$/i', $newinput['text_string'])) {
                $newinput['text_string'] = '';
            }
            return $newinput;
        }

        public function shgdprdm_default_if_empty($input, $replacement)
        {
            if (!$input) {
                $input = $replacement;
            }
            return $input;
        }

        // Validate Replacement Text Input
        public function shgdprdm_text_options_validate($input)
        {
            if (!$input['text_option'] || $input['text_option'] == '' || !isset($input['text_option'])) {
                $input['text_option'] = SHGDPRDM_DEFAULT_REPLACEMENT_TEXT_IN_DB;
                $originalText = get_option('shgdprdm_text_options')['text_option'];
                add_settings_error('GDPR Data Manager', 'emptydata', __('Settings Notice! You have entered a blank value. The default text has been applied.<br>The previously saved text was: '.$originalText, 'err'), 'error');
            }
            $input['text_option'] = sanitize_textarea_field(wp_strip_all_tags($input['text_option']));
            return $input;
        }


        // Validate PP Gen Text Input
        public function shgdprdm_ppolicy_gen_options_validate($input)
        {
            $locale = get_locale();
            if (isset($input['ppolicy_gen_option']) && $input['ppolicy_gen_option'] === '') {
                $input['ppolicy_gen_option'] = SHGDPRDM_DEFAULT_POLICY_GEN_CONDITIONS_TEXT;
                if (false !== get_option('shgdprdm_ppolicy_gen_options')) {
                    if (isset(get_option('shgdprdm_ppolicy_gen_options')[$locale]['ppolicy_gen_option'])) {
                        $originalText = get_option('shgdprdm_ppolicy_gen_options')[$locale]['ppolicy_gen_option'];
                    } elseif (isset(get_option('shgdprdm_ppolicy_gen_options')['legacy']['ppolicy_gen_option'])) {
                        $originalText = get_option('shgdprdm_ppolicy_gen_options')['legacy']['ppolicy_gen_option'];
                    } elseif (isset(get_option('shgdprdm_ppolicy_gen_options')['ppolicy_gen_option'])) {
                        $originalText = get_option('shgdprdm_ppolicy_gen_options')['ppolicy_gen_option'];
                    } else {
                        $originalText = '';
                    }
                } else {
                    $originalText = '';
                }
                add_settings_error('GDPR Data Manager', 'emptydata', __('Settings Notice! You have entered a blank value in the Section Content. The default text has been applied.<br>The previously saved text was: '.$originalText, 'err'), 'error');
            }
            if (isset($input['ppolicy_gen_header_option']) && $input['ppolicy_gen_header_option'] === '') {
                $input['ppolicy_gen_header_option'] = SHGDPRDM_DEFAULT_POLICY_GEN_HEADER_CONDITIONS_TEXT;
                if (false !== get_option('shgdprdm_ppolicy_gen_options')) {
                    if (isset(get_option('shgdprdm_ppolicy_gen_options')[$locale]['ppolicy_gen_header_option'])) {
                        $originalText = get_option('shgdprdm_ppolicy_gen_options')[$locale]['ppolicy_gen_header_option'];
                    } elseif (isset(get_option('shgdprdm_ppolicy_gen_options')['legacy']['ppolicy_gen_header_option'])) {
                        $originalText = get_option('shgdprdm_ppolicy_gen_options')['legacy']['ppolicy_gen_header_option'];
                    } elseif (isset(get_option('shgdprdm_ppolicy_gen_options')['ppolicy_gen_header_option'])) {
                        $originalText = get_option('shgdprdm_ppolicy_gen_options')['ppolicy_gen_header_option'];
                    } else {
                        $originalText = '';
                    }
                } else {
                    $originalText = '';
                }
                add_settings_error('GDPR Data Manager', 'emptydata', __('Settings Notice! You have entered a blank value in the Section Heading. The default text has been applied.<br>The previously saved text was: '.$originalText, 'err'), 'error');
            }
            
            $options = array();
            $options[$locale]['ppolicy_gen_option'] = strip_tags($input['ppolicy_gen_option'], '<p><a><b><i><u><strong><em>');
            $options[$locale]['ppolicy_gen_header_option'] = sanitize_text_field(wp_strip_all_tags($input['ppolicy_gen_header_option']));
            
            if (false === get_option('shgdprdm_ppolicy_gen_options')) {
                return $options;
            } else {
                // Get The Current Option
                $fullOption = get_option('shgdprdm_ppolicy_gen_options');
                // Remove the language specific Values (of they exist)
                if (isset($fullOption[$locale])) {
                    unset($fullOption[$locale]);
                }
                // Merge the new inputs with the existing
                $options = array_merge($options, $fullOption);

                // Check for legacy & Incorporate if exist
                $heading = (isset($fullOption['ppolicy_gen_header_option']) ? $fullOption['ppolicy_gen_header_option'] : '');
                $body =  (isset($fullOption['ppolicy_gen_option']) ? $fullOption['ppolicy_gen_option'] : '');
                if (!empty($heading) && !empty($body)) {
                    delete_option('shgdprdm_ppolicy_gen_options');
                    $options['legacy'] = array(
                        'ppolicy_gen_header_option' => $heading,
                        'ppolicy_gen_option' => $body
                    );
                    unset($options['ppolicy_gen_header_option']);
                    unset($options['ppolicy_gen_option']);
                    add_option('shgdprdm_ppolicy_gen_options', $options);
                }

                // Return the full options
                return $options;
            }
            // Default
            return $options;
        }



        // Validate Terms & Conditions Text Input
        // Updated by PC - 29/05/19 (Merged separate validate functions. Re-named header option parent to match main option parent. sanitized input)
        public function shgdprdm_tandc_options_validate($input)
        {
            $locale = get_locale();
            if (isset($inputBody['tandc_option']) && $inputBody['tandc_option'] === '') {
            // if (!$input['tandc_option'] || $input['tandc_option'] == '' || !isset($input['tandc_option'])) {
                $input['tandc_option'] = SHGDPRDM_DEFAULT_TERMS_CONDITIONS_TEXT;
                if (false !== get_option('shgdprdm_tandc_options')) {
                    if (isset(get_option('shgdprdm_tandc_options')[$locale]['tandc_option'])) {
                        $originalText = get_option('shgdprdm_tandc_options')[$locale]['tandc_option'];
                    } elseif (isset(get_option('shgdprdm_tandc_options')['legacy']['tandc_option'])) {
                        $originalText = get_option('shgdprdm_tandc_options')['legacy']['tandc_option'];
                    } elseif (isset(get_option('shgdprdm_tandc_options')['tandc_option'])) {
                        $originalText = get_option('shgdprdm_tandc_options')['tandc_option'];
                    } else {
                        $originalText = '';
                    }
                } else {
                    $originalText = '';
                }
                add_settings_error('GDPR Data Manager', 'emptydata', __('Settings Notice! You have entered a blank value in the Section Content. The default text has been applied.<br>The previously saved text was: '.$originalText, 'err'), 'error');
            }
            if (isset($input['tandc_header_option']) && $input['tandc_header_option'] === '') {
            // if (!$input['tandc_header_option'] || $input['tandc_header_option'] == '' || !isset($input['tandc_header_option'])) {
                $input['tandc_header_option'] = SHGDPRDM_DEFAULT_TERMS_HEADER_CONDITIONS_TEXT;
                if (false !== get_option('shgdprdm_tandc_options')) {
                    if (isset(get_option('shgdprdm_tandc_options')[$locale]['tandc_header_option'])) {
                        $originalText = get_option('shgdprdm_tandc_options')[$locale]['tandc_header_option'];
                    } elseif (isset(get_option('shgdprdm_tandc_options')['legacy']['tandc_header_option'])) {
                        $originalText = get_option('shgdprdm_tandc_options')['legacy']['tandc_header_option'];
                    } elseif (isset(get_option('shgdprdm_tandc_options')['tandc_header_option'])) {
                        $originalText = get_option('shgdprdm_tandc_options')['tandc_header_option'];
                    } else {
                        $originalText = '';
                    }
                } else {
                    $originalText = '';
                }
                // $originalText = get_option('shgdprdm_tandc_options')['tandc_header_option'];
                add_settings_error('GDPR Data Manager', 'emptydata', __('Settings Notice! You have entered a blank value in the Section Heading. The default text has been applied.<br>The previously saved text was: '.$originalText, 'err'), 'error');
            }
            // $input['tandc_option'] = strip_tags($input['tandc_option'], '<p><a><b><i><u><strong><em>');
            // $input['tandc_header_option'] = sanitize_text_field(wp_strip_all_tags($input['tandc_header_option']));
            // return $input;

            $options = array();
            $options[$locale]['tandc_header_option'] = sanitize_text_field(wp_strip_all_tags($input['tandc_header_option']));
            $options[$locale]['tandc_option'] = strip_tags($input['tandc_option'], '<p><a><b><i><u><strong><em>');
            
            if (false === get_option('shgdprdm_tandc_options')) {
                return $options;
            } else {
                // Get The Current Option
                $fullOption = get_option('shgdprdm_tandc_options');
                // Remove the language specific Values (of they exist)
                if (isset($fullOption[$locale])) {
                    unset($fullOption[$locale]);
                }
                // Merge the new inputs with the existing
                $options = array_merge($options, $fullOption);

                // Check for legacy & Incorporate if exist
                $heading = (isset($fullOption['tandc_header_option']) ? $fullOption['tandc_header_option'] : '');
                $body =  (isset($fullOption['tandc_option']) ? $fullOption['tandc_option'] : '');
                if (!empty($heading) && !empty($body)) {
                    delete_option('shgdprdm_tandc_options');
                    $options['legacy'] = array(
                        'tandc_header_option' => $heading,
                        'tandc_option' => $body
                    );
                    unset($options['tandc_header_option']);
                    unset($options['tandc_option']);
                    add_option('shgdprdm_tandc_options', $options);
                }

                // Return the full options
                return $options;
            }
            // Default
            return $options;
        }


        // Validate Privacy Policy Link Text Input
        public function shgdprdm_ppolicy_options_validate($input)
        {
            // if (!$input['ppolicy_option'] || $input['ppolicy_option'] == '' || !isset($input['ppolicy_option'])) {
            //     $input['ppolicy_option'] = SHGDPRDM_DEFAULT_PRIVACY_POLICY_LINK;
            //     $originalText = get_option('shgdprdm_ppolicy_options')['ppolicy_option'];
            //     add_settings_error('GDPR Data Manager', 'emptydata', __('Settings Notice! You have entered a blank value. The default text has been applied.<br>The previously saved text was: '.$originalText, 'err'), 'error');
            //     // return $input;
            // }
            // $input['ppolicy_option'] = sanitize_text_field(wp_strip_all_tags($input['ppolicy_option']));
            // return $input;

            $locale = get_locale();
            if (isset($inputBody['ppolicy_option']) && $inputBody['ppolicy_option'] === '') {
                $input['ppolicy_option'] = SHGDPRDM_DEFAULT_TERMS_CONDITIONS_TEXT;
                if (false !== get_option('shgdprdm_ppolicy_options')) {
                    if (isset(get_option('shgdprdm_ppolicy_options')[$locale]['ppolicy_option'])) {
                        $originalText = get_option('shgdprdm_ppolicy_options')[$locale]['ppolicy_option'];
                    } elseif (isset(get_option('shgdprdm_ppolicy_options')['legacy']['ppolicy_option'])) {
                        $originalText = get_option('shgdprdm_ppolicy_options')['legacy']['ppolicy_option'];
                    } elseif (isset(get_option('shgdprdm_ppolicy_options')['ppolicy_option'])) {
                        $originalText = get_option('shgdprdm_ppolicy_options')['ppolicy_option'];
                    } else {
                        $originalText = '';
                    }
                } else {
                    $originalText = '';
                }
                add_settings_error('GDPR Data Manager', 'emptydata', __('Settings Notice! You have entered a blank value in the Section Content. The default text has been applied.<br>The previously saved text was: '.$originalText, 'err'), 'error');
            }

            $options = array();
            $options[$locale]['ppolicy_option'] = sanitize_text_field(wp_strip_all_tags($input['ppolicy_option']));
            
            if (false === get_option('shgdprdm_ppolicy_options')) {
                return $options;
            } else {
                // Get The Current Option
                $fullOption = get_option('shgdprdm_ppolicy_options');
                // Remove the language specific Values (of they exist)
                if (isset($fullOption[$locale])) {
                    unset($fullOption[$locale]);
                }
                // Merge the new inputs with the existing
                $options = array_merge($options, $fullOption);

                // Check for legacy & Incorporate if exist
                $body =  (isset($fullOption['ppolicy_option']) ? $fullOption['ppolicy_option'] : '');
                if (!empty($heading) && !empty($body)) {
                    delete_option('shgdprdm_ppolicy_options');
                    $options['legacy'] = array(
                        'ppolicy_option' => $body
                    );
                    unset($options['ppolicy_option']);
                    add_option('shgdprdm_ppolicy_options', $options);
                }

                // Return the full options
                return $options;
            }
            // Default
            return $options;
        }
    
    
    
    
        // Validate PP MNG Text Input
        public function shgdprdm_ppolicy_mng_options_validate($input)
        {
            // // exit(print_r($input));
            // if (!$input['ppolicy_mng_option'] || $input['ppolicy_mng_option'] == '' || !isset($input['ppolicy_mng_option'])) {
            //     $input['ppolicy_mng_option'] = SHGDPRDM_DEFAULT_POLICY_MNG_CONDITIONS_TEXT;
            //     $originalText = get_option('shgdprdm_ppolicy_mng_options')['ppolicy_mng_option'];
            //     add_settings_error('GDPR Data Manager', 'emptydata', __('Settings Notice! You have entered a blank value in the Section Content. The default text has been applied.<br>The previously saved text was: '.$originalText, 'err'), 'error');
            // }
            // if (!$input['ppolicy_mng_header_option'] || $input['ppolicy_mng_header_option'] == '' || !isset($input['ppolicy_mng_header_option'])) {
            //     $input['ppolicy_mng_header_option'] = SHGDPRDM_DEFAULT_POLICY_MNG_HEADER_CONDITIONS_TEXT;
            //     $originalText = get_option('shgdprdm_ppolicy_mng_options')['ppolicy_mng_header_option'];
            //     add_settings_error('GDPR Data Manager', 'emptydata', __('Settings Notice! You have entered a blank value in the Section Heading. The default text has been applied.<br>The previously saved text was: '.$originalText, 'err'), 'error');
            // }
            // $input['ppolicy_mng_option'] = strip_tags($input['ppolicy_mng_option'], '<p><a><b><i><u><strong><em>');
            // $input['ppolicy_mng_header_option'] = sanitize_text_field(wp_strip_all_tags($input['ppolicy_mng_header_option']));
            // return $input;


            $locale = get_locale();
            if (isset($inputBody['ppolicy_mng_option']) && $inputBody['ppolicy_mng_option'] === '') {
                $input['ppolicy_mng_option'] = SHGDPRDM_DEFAULT_TERMS_CONDITIONS_TEXT;
                if (false !== get_option('shgdprdm_ppolicy_mng_options')) {
                    if (isset(get_option('shgdprdm_ppolicy_mng_options')[$locale]['ppolicy_mng_option'])) {
                        $originalText = get_option('shgdprdm_ppolicy_mng_options')[$locale]['ppolicy_mng_option'];
                    } elseif (isset(get_option('shgdprdm_ppolicy_mng_options')['legacy']['ppolicy_mng_option'])) {
                        $originalText = get_option('shgdprdm_ppolicy_mng_options')['legacy']['ppolicy_mng_option'];
                    } elseif (isset(get_option('shgdprdm_ppolicy_mng_options')['ppolicy_mng_option'])) {
                        $originalText = get_option('shgdprdm_ppolicy_mng_options')['ppolicy_mng_option'];
                    } else {
                        $originalText = '';
                    }
                } else {
                    $originalText = '';
                }
                add_settings_error('GDPR Data Manager', 'emptydata', __('Settings Notice! You have entered a blank value in the Section Content. The default text has been applied.<br>The previously saved text was: '.$originalText, 'err'), 'error');
            }
            if (isset($input['ppolicy_mng_header_option']) && $input['ppolicy_mng_header_option'] === '') {
                $input['ppolicy_mng_header_option'] = SHGDPRDM_DEFAULT_TERMS_HEADER_CONDITIONS_TEXT;
                if (false !== get_option('shgdprdm_ppolicy_mng_options')) {
                    if (isset(get_option('shgdprdm_ppolicy_mng_options')[$locale]['ppolicy_mng_header_option'])) {
                        $originalText = get_option('shgdprdm_ppolicy_mng_options')[$locale]['ppolicy_mng_header_option'];
                    } elseif (isset(get_option('shgdprdm_ppolicy_mng_options')['legacy']['ppolicy_mng_header_option'])) {
                        $originalText = get_option('shgdprdm_ppolicy_mng_options')['legacy']['ppolicy_mng_header_option'];
                    } elseif (isset(get_option('shgdprdm_ppolicy_mng_options')['ppolicy_mng_header_option'])) {
                        $originalText = get_option('shgdprdm_ppolicy_mng_options')['ppolicy_mng_header_option'];
                    } else {
                        $originalText = '';
                    }
                } else {
                    $originalText = '';
                }
                add_settings_error('GDPR Data Manager', 'emptydata', __('Settings Notice! You have entered a blank value in the Section Heading. The default text has been applied.<br>The previously saved text was: '.$originalText, 'err'), 'error');
            }

            $options = array();
            $options[$locale]['ppolicy_mng_header_option'] = sanitize_text_field(wp_strip_all_tags($input['ppolicy_mng_header_option']));
            $options[$locale]['ppolicy_mng_option'] = strip_tags($input['ppolicy_mng_option'], '<p><a><b><i><u><strong><em>');
            
            if (false === get_option('shgdprdm_ppolicy_mng_options')) {
                return $options;
            } else {
                // Get The Current Option
                $fullOption = get_option('shgdprdm_ppolicy_mng_options');
                // Remove the language specific Values (of they exist)
                if (isset($fullOption[$locale])) {
                    unset($fullOption[$locale]);
                }
                // Merge the new inputs with the existing
                $options = array_merge($options, $fullOption);

                // Check for legacy & Incorporate if exist
                $heading = (isset($fullOption['ppolicy_mng_header_option']) ? $fullOption['ppolicy_mng_header_option'] : '');
                $body =  (isset($fullOption['ppolicy_mng_option']) ? $fullOption['ppolicy_mng_option'] : '');
                if (!empty($heading) && !empty($body)) {
                    delete_option('shgdprdm_ppolicy_mng_options');
                    $options['legacy'] = array(
                        'ppolicy_mng_header_option' => $heading,
                        'ppolicy_mng_option' => $body
                    );
                    unset($options['ppolicy_mng_header_option']);
                    unset($options['ppolicy_mng_option']);
                    add_option('shgdprdm_ppolicy_mng_options', $options);
                }

                // Return the full options
                return $options;
            }
            // Default
            return $options;
        }
    
 
    
        // Validate PP ICO Text Input
        public function shgdprdm_ppolicy_ico_options_validate($input)
        {
            // // exit(print_r($input));
            // if (!$input['ppolicy_ico_option'] || $input['ppolicy_ico_option'] == '' || !isset($input['ppolicy_ico_option'])) {
            //     $input['ppolicy_ico_option'] = SHGDPRDM_DEFAULT_POLICY_ICO_CONDITIONS_TEXT;
            //     $originalText = get_option('shgdprdm_ppolicy_ico_options')['ppolicy_ico_option'];
            //     add_settings_error('GDPR Data Manager', 'emptydata', __('Settings Notice! You have entered a blank value in the Section Content. The default text has been applied.<br>The previously saved text was: '.$originalText, 'err'), 'error');
            // }
            // if (!$input['ppolicy_ico_header_option'] || $input['ppolicy_ico_header_option'] == '' || !isset($input['ppolicy_ico_header_option'])) {
            //     $input['ppolicy_ico_header_option'] = SHGDPRDM_DEFAULT_POLICY_ICO_HEADER_CONDITIONS_TEXT;
            //     $originalText = get_option('shgdprdm_ppolicy_ico_options')['ppolicy_ico_header_option'];
            //     add_settings_error('GDPR Data Manager', 'emptydata', __('Settings Notice! You have entered a blank value in the Section Heading. The default text has been applied.<br>The previously saved text was: '.$originalText, 'err'), 'error');
            // }
            // $input['ppolicy_ico_option'] = strip_tags($input['ppolicy_ico_option'], '<p><a><b><i><u><strong><em>');
            // $input['ppolicy_ico_header_option'] = sanitize_text_field(wp_strip_all_tags($input['ppolicy_ico_header_option']));
            // return $input;

            $locale = get_locale();
            if (isset($inputBody['ppolicy_ico_option']) && $inputBody['ppolicy_ico_option'] === '') {
                $input['ppolicy_ico_option'] = SHGDPRDM_DEFAULT_TERMS_CONDITIONS_TEXT;
                if (false !== get_option('shgdprdm_ppolicy_ico_options')) {
                    if (isset(get_option('shgdprdm_ppolicy_ico_options')[$locale]['ppolicy_ico_option'])) {
                        $originalText = get_option('shgdprdm_ppolicy_ico_options')[$locale]['ppolicy_ico_option'];
                    } elseif (isset(get_option('shgdprdm_ppolicy_ico_options')['legacy']['ppolicy_ico_option'])) {
                        $originalText = get_option('shgdprdm_ppolicy_ico_options')['legacy']['ppolicy_ico_option'];
                    } elseif (isset(get_option('shgdprdm_ppolicy_ico_options')['ppolicy_ico_option'])) {
                        $originalText = get_option('shgdprdm_ppolicy_ico_options')['ppolicy_ico_option'];
                    } else {
                        $originalText = '';
                    }
                } else {
                    $originalText = '';
                }
                add_settings_error('GDPR Data Manager', 'emptydata', __('Settings Notice! You have entered a blank value in the Section Content. The default text has been applied.<br>The previously saved text was: '.$originalText, 'err'), 'error');
            }
            if (isset($input['ppolicy_ico_header_option']) && $input['ppolicy_ico_header_option'] === '') {
                $input['ppolicy_ico_header_option'] = SHGDPRDM_DEFAULT_TERMS_HEADER_CONDITIONS_TEXT;
                if (false !== get_option('shgdprdm_ppolicy_ico_options')) {
                    if (isset(get_option('shgdprdm_ppolicy_ico_options')[$locale]['ppolicy_ico_header_option'])) {
                        $originalText = get_option('shgdprdm_ppolicy_ico_options')[$locale]['ppolicy_ico_header_option'];
                    } elseif (isset(get_option('shgdprdm_ppolicy_ico_options')['legacy']['ppolicy_ico_header_option'])) {
                        $originalText = get_option('shgdprdm_ppolicy_ico_options')['legacy']['ppolicy_ico_header_option'];
                    } elseif (isset(get_option('shgdprdm_ppolicy_ico_options')['ppolicy_ico_header_option'])) {
                        $originalText = get_option('shgdprdm_ppolicy_ico_options')['ppolicy_ico_header_option'];
                    } else {
                        $originalText = '';
                    }
                } else {
                    $originalText = '';
                }
                add_settings_error('GDPR Data Manager', 'emptydata', __('Settings Notice! You have entered a blank value in the Section Heading. The default text has been applied.<br>The previously saved text was: '.$originalText, 'err'), 'error');
            }

            $options = array();
            $options[$locale]['ppolicy_ico_header_option'] = sanitize_text_field(wp_strip_all_tags($input['ppolicy_ico_header_option']));
            $options[$locale]['ppolicy_ico_option'] = strip_tags($input['ppolicy_ico_option'], '<p><a><b><i><u><strong><em>');
            
            if (false === get_option('shgdprdm_ppolicy_ico_options')) {
                return $options;
            } else {
                // Get The Current Option
                $fullOption = get_option('shgdprdm_ppolicy_ico_options');
                // Remove the language specific Values (of they exist)
                if (isset($fullOption[$locale])) {
                    unset($fullOption[$locale]);
                }
                // Merge the new inputs with the existing
                $options = array_merge($options, $fullOption);

                // Check for legacy & Incorporate if exist
                $heading = (isset($fullOption['ppolicy_ico_header_option']) ? $fullOption['ppolicy_ico_header_option'] : '');
                $body =  (isset($fullOption['ppolicy_ico_option']) ? $fullOption['ppolicy_ico_option'] : '');
                if (!empty($heading) && !empty($body)) {
                    delete_option('shgdprdm_ppolicy_ico_options');
                    $options['legacy'] = array(
                        'ppolicy_ico_header_option' => $heading,
                        'ppolicy_ico_option' => $body
                    );
                    unset($options['ppolicy_ico_header_option']);
                    unset($options['ppolicy_ico_option']);
                    add_option('shgdprdm_ppolicy_ico_options', $options);
                }

                // Return the full options
                return $options;
            }
            // Default
            return $options;
        }
    
    
        // Validate PP USE Text Input
        public function shgdprdm_ppolicy_use_options_validate($input)
        {
            // // exit(print_r($input));
            // if (!$input['ppolicy_use_option'] || $input['ppolicy_use_option'] == '' || !isset($input['ppolicy_use_option'])) {
            //     $input['ppolicy_use_option'] = SHGDPRDM_DEFAULT_POLICY_USE_CONDITIONS_TEXT;
            //     $originalText = get_option('shgdprdm_ppolicy_use_options')['ppolicy_use_option'];
            //     add_settings_error('GDPR Data Manager', 'emptydata', __('Settings Notice! You have entered a blank value in the Section Content. The default text has been applied.<br>The previously saved text was: '.$originalText, 'err'), 'error');
            // }
            // if (!$input['ppolicy_use_header_option'] || $input['ppolicy_use_header_option'] == '' || !isset($input['ppolicy_use_header_option'])) {
            //     $input['ppolicy_use_header_option'] = SHGDPRDM_DEFAULT_POLICY_USE_HEADER_CONDITIONS_TEXT;
            //     $originalText = get_option('shgdprdm_ppolicy_use_options')['ppolicy_use_header_option'];
            //     add_settings_error('GDPR Data Manager', 'emptydata', __('Settings Notice! You have entered a blank value in the Section Heading. The default text has been applied.<br>The previously saved text was: '.$originalText, 'err'), 'error');
            // }
            // $input['ppolicy_use_option'] = strip_tags($input['ppolicy_use_option'], '<p><a><b><i><u><strong><em>');
            // $input['ppolicy_use_header_option'] = sanitize_text_field(wp_strip_all_tags($input['ppolicy_use_header_option']));
            // return $input;



            $locale = get_locale();
            if (isset($inputBody['ppolicy_use_option']) && $inputBody['ppolicy_use_option'] === '') {
                $input['ppolicy_use_option'] = SHGDPRDM_DEFAULT_TERMS_CONDITIONS_TEXT;
                if (false !== get_option('shgdprdm_ppolicy_use_options')) {
                    if (isset(get_option('shgdprdm_ppolicy_use_options')[$locale]['ppolicy_use_option'])) {
                        $originalText = get_option('shgdprdm_ppolicy_use_options')[$locale]['ppolicy_use_option'];
                    } elseif (isset(get_option('shgdprdm_ppolicy_use_options')['legacy']['ppolicy_use_option'])) {
                        $originalText = get_option('shgdprdm_ppolicy_use_options')['legacy']['ppolicy_use_option'];
                    } elseif (isset(get_option('shgdprdm_ppolicy_use_options')['ppolicy_use_option'])) {
                        $originalText = get_option('shgdprdm_ppolicy_use_options')['ppolicy_use_option'];
                    } else {
                        $originalText = '';
                    }
                } else {
                    $originalText = '';
                }
                add_settings_error('GDPR Data Manager', 'emptydata', __('Settings Notice! You have entered a blank value in the Section Content. The default text has been applied.<br>The previously saved text was: '.$originalText, 'err'), 'error');
            }
            if (isset($input['ppolicy_use_header_option']) && $input['ppolicy_use_header_option'] === '') {
                $input['ppolicy_use_header_option'] = SHGDPRDM_DEFAULT_TERMS_HEADER_CONDITIONS_TEXT;
                if (false !== get_option('shgdprdm_ppolicy_use_options')) {
                    if (isset(get_option('shgdprdm_ppolicy_use_options')[$locale]['ppolicy_use_header_option'])) {
                        $originalText = get_option('shgdprdm_ppolicy_use_options')[$locale]['ppolicy_use_header_option'];
                    } elseif (isset(get_option('shgdprdm_ppolicy_use_options')['legacy']['ppolicy_use_header_option'])) {
                        $originalText = get_option('shgdprdm_ppolicy_use_options')['legacy']['ppolicy_use_header_option'];
                    } elseif (isset(get_option('shgdprdm_ppolicy_use_options')['ppolicy_use_header_option'])) {
                        $originalText = get_option('shgdprdm_ppolicy_use_options')['ppolicy_use_header_option'];
                    } else {
                        $originalText = '';
                    }
                } else {
                    $originalText = '';
                }
                add_settings_error('GDPR Data Manager', 'emptydata', __('Settings Notice! You have entered a blank value in the Section Heading. The default text has been applied.<br>The previously saved text was: '.$originalText, 'err'), 'error');
            }

            $options = array();
            $options[$locale]['ppolicy_use_header_option'] = sanitize_text_field(wp_strip_all_tags($input['ppolicy_use_header_option']));
            $options[$locale]['ppolicy_use_option'] = strip_tags($input['ppolicy_use_option'], '<p><a><b><i><u><strong><em>');
            
            if (false === get_option('shgdprdm_ppolicy_use_options')) {
                return $options;
            } else {
                // Get The Current Option
                $fullOption = get_option('shgdprdm_ppolicy_use_options');
                // Remove the language specific Values (of they exist)
                if (isset($fullOption[$locale])) {
                    unset($fullOption[$locale]);
                }
                // Merge the new inputs with the existing
                $options = array_merge($options, $fullOption);

                // Check for legacy & Incorporate if exist
                $heading = (isset($fullOption['ppolicy_use_header_option']) ? $fullOption['ppolicy_use_header_option'] : '');
                $body =  (isset($fullOption['ppolicy_use_option']) ? $fullOption['ppolicy_use_option'] : '');
                if (!empty($heading) && !empty($body)) {
                    delete_option('shgdprdm_ppolicy_use_options');
                    $options['legacy'] = array(
                        'ppolicy_use_header_option' => $heading,
                        'ppolicy_use_option' => $body
                    );
                    unset($options['ppolicy_use_header_option']);
                    unset($options['ppolicy_use_option']);
                    add_option('shgdprdm_ppolicy_use_options', $options);
                }

                // Return the full options
                return $options;
            }
            // Default
            return $options;
        }
    
    
        
        // Validate PP SHA Text Input
        public function shgdprdm_ppolicy_sha_options_validate($input)
        {
            // // exit(print_r($input));
            // if (!$input['ppolicy_sha_option'] || $input['ppolicy_sha_option'] == '' || !isset($input['ppolicy_sha_option'])) {
            //     $input['ppolicy_sha_option'] = SHGDPRDM_DEFAULT_POLICY_SHA_CONDITIONS_TEXT;
            //     $originalText = get_option('shgdprdm_ppolicy_sha_options')['ppolicy_sha_option'];
            //     add_settings_error('GDPR Data Manager', 'emptydata', __('Settings Notice! You have entered a blank value in the Section Content. The default text has been applied.<br>The previously saved text was: '.$originalText, 'err'), 'error');
            // }
            // if (!$input['ppolicy_sha_header_option'] || $input['ppolicy_sha_header_option'] == '' || !isset($input['ppolicy_sha_header_option'])) {
            //     $input['ppolicy_sha_header_option'] = SHGDPRDM_DEFAULT_POLICY_SHA_HEADER_CONDITIONS_TEXT;
            //     $originalText = get_option('shgdprdm_ppolicy_sha_options')['ppolicy_sha_header_option'];
            //     add_settings_error('GDPR Data Manager', 'emptydata', __('Settings Notice! You have entered a blank value in the Section Heading. The default text has been applied.<br>The previously saved text was: '.$originalText, 'err'), 'error');
            // }
            // $input['ppolicy_sha_option'] = strip_tags($input['ppolicy_sha_option'], '<p><a><b><i><u><strong><em>');
            // $input['ppolicy_sha_header_option'] = sanitize_text_field(wp_strip_all_tags($input['ppolicy_sha_header_option']));
            // return $input;
            


            $locale = get_locale();
            if (isset($inputBody['ppolicy_sha_option']) && $inputBody['ppolicy_sha_option'] === '') {
                $input['ppolicy_sha_option'] = SHGDPRDM_DEFAULT_TERMS_CONDITIONS_TEXT;
                if (false !== get_option('shgdprdm_ppolicy_sha_options')) {
                    if (isset(get_option('shgdprdm_ppolicy_sha_options')[$locale]['ppolicy_sha_option'])) {
                        $originalText = get_option('shgdprdm_ppolicy_sha_options')[$locale]['ppolicy_sha_option'];
                    } elseif (isset(get_option('shgdprdm_ppolicy_sha_options')['legacy']['ppolicy_sha_option'])) {
                        $originalText = get_option('shgdprdm_ppolicy_sha_options')['legacy']['ppolicy_sha_option'];
                    } elseif (isset(get_option('shgdprdm_ppolicy_sha_options')['ppolicy_sha_option'])) {
                        $originalText = get_option('shgdprdm_ppolicy_sha_options')['ppolicy_sha_option'];
                    } else {
                        $originalText = '';
                    }
                } else {
                    $originalText = '';
                }
                add_settings_error('GDPR Data Manager', 'emptydata', __('Settings Notice! You have entered a blank value in the Section Content. The default text has been applied.<br>The previously saved text was: '.$originalText, 'err'), 'error');
            }
            if (isset($input['ppolicy_sha_header_option']) && $input['ppolicy_sha_header_option'] === '') {
                $input['ppolicy_sha_header_option'] = SHGDPRDM_DEFAULT_TERMS_HEADER_CONDITIONS_TEXT;
                if (false !== get_option('shgdprdm_ppolicy_sha_options')) {
                    if (isset(get_option('shgdprdm_ppolicy_sha_options')[$locale]['ppolicy_sha_header_option'])) {
                        $originalText = get_option('shgdprdm_ppolicy_sha_options')[$locale]['ppolicy_sha_header_option'];
                    } elseif (isset(get_option('shgdprdm_ppolicy_sha_options')['legacy']['ppolicy_sha_header_option'])) {
                        $originalText = get_option('shgdprdm_ppolicy_sha_options')['legacy']['ppolicy_sha_header_option'];
                    } elseif (isset(get_option('shgdprdm_ppolicy_sha_options')['ppolicy_sha_header_option'])) {
                        $originalText = get_option('shgdprdm_ppolicy_sha_options')['ppolicy_sha_header_option'];
                    } else {
                        $originalText = '';
                    }
                } else {
                    $originalText = '';
                }
                add_settings_error('GDPR Data Manager', 'emptydata', __('Settings Notice! You have entered a blank value in the Section Heading. The default text has been applied.<br>The previously saved text was: '.$originalText, 'err'), 'error');
            }

            $options = array();
            $options[$locale]['ppolicy_sha_header_option'] = sanitize_text_field(wp_strip_all_tags($input['ppolicy_sha_header_option']));
            $options[$locale]['ppolicy_sha_option'] = strip_tags($input['ppolicy_sha_option'], '<p><a><b><i><u><strong><em>');
            
            if (false === get_option('shgdprdm_ppolicy_sha_options')) {
                return $options;
            } else {
                // Get The Current Option
                $fullOption = get_option('shgdprdm_ppolicy_sha_options');
                // Remove the language specific Values (of they exist)
                if (isset($fullOption[$locale])) {
                    unset($fullOption[$locale]);
                }
                // Merge the new inputs with the existing
                $options = array_merge($options, $fullOption);

                // Check for legacy & Incorporate if exist
                $heading = (isset($fullOption['ppolicy_sha_header_option']) ? $fullOption['ppolicy_sha_header_option'] : '');
                $body =  (isset($fullOption['ppolicy_sha_option']) ? $fullOption['ppolicy_sha_option'] : '');
                if (!empty($heading) && !empty($body)) {
                    delete_option('shgdprdm_ppolicy_sha_options');
                    $options['legacy'] = array(
                        'ppolicy_sha_header_option' => $heading,
                        'ppolicy_sha_option' => $body
                    );
                    unset($options['ppolicy_sha_header_option']);
                    unset($options['ppolicy_sha_option']);
                    add_option('shgdprdm_ppolicy_sha_options', $options);
                }

                // Return the full options
                return $options;
            }
            // Default
            return $options;
        }
    
    

        public function shgdprdm_admin_msg_options_validate($input)
        {
            if (null != $input) {
                if (false === get_option('shgdprdm_admin_msg')) {
                    add_option('shgdprdm_admin_msg', $input);
                } else {
                    update_option('shgdprdm_admin_msg', $input);
                }
            }
            update_option('shgdprdm_admin_msg', $input);
            return $input;
        }

        public function shgdprdm_admin_notice_options_validate($input)
        {
            if (null != $input) {
                if (false === get_option('shgdprdm_admin_notice')) {
                    add_option('shgdprdm_admin_notice', $input);
                } else {
                    update_option('shgdprdm_admin_notice', $input);
                }
            }
            // Override
            update_option('shgdprdm_admin_notice', $input);
            return $input;
        }

        public function shgdprdm_user_msg_options_validate($input)
        {
            if (null != $input) {
                if (false === get_option('shgdprdm_user_msg')) {
                    add_option('shgdprdm_user_msg', $input);
                } else {
                    update_option('shgdprdm_user_msg', $input);
                }
            }
            update_option('shgdprdm_user_msg', $input);
            return $input;
        }

        public function shgdprdm_reglicnum_options_validate($input)
        {
            $updateFunction = '';
            if (isset($input['shgdprdm_deactivate_licence'])) {
                $updateFunction = 'shgdprdm_deactivateLicenceKey';
            }
            if (isset($input['shgdprdm_register_licence'])) {
                $updateFunction = 'shgdprdm_validateLicenceKey';
            }
            $runUpdate = false;
            if (isset($input['licence_number'])) { // If deactivate licence, then this does not apply
                if (!empty($input['licence_number'])) {
                    $input['licence_number'] = sanitize_textarea_field(wp_strip_all_tags($input['licence_number']));
                    $runUpdate = true;
                    if (false === get_option('shgdprdm_adminHasLicence')) {
                        add_option('shgdprdm_adminHasLicence', array('licence_number' => $input['licence_number']));
                    } else {
                        $currentNum = '';
                        if (false !== get_option('shgdprdm_adminVerifyLicence')) {
                            $currentNum = get_option('shgdprdm_adminVerifyLicence')['licence_number'];
                        }
                        update_option('shgdprdm_adminHasLicence', array('licence_number' => $input['licence_number'] ));
                        if ($currentNum == $input['licence_number'] && get_option('shgdprdm_adminVerifyLicence')['licence_valid'] === true) {
                            $runUpdate = false;
                        }
                    }
                    if ($runUpdate) {
                        if ($updateFunction) {
                            add_action('updated_option_action', $updateFunction, 10, 0);
                            do_action('updated_option_action', $updateFunction, 10, 0);
                            // remove the action
                            remove_action('updated_option_action', $updateFunction, 10, 0);
                        }
                    }
                    return $input;
                } else { // if input=>licence_number is empty
                    if (false === get_option('shgdprdm_adminVerifyLicence')) {
                        add_option('shgdprdm_adminVerifyLicence', array('licence_number' => $input['licence_number'], 'licence_valid' => false, 'licence_msg' => false ));
                    } else {
                        update_option('shgdprdm_adminVerifyLicence', array('licence_number' => $input['licence_number'], 'licence_valid' => false, 'licence_msg' => false ));
                    }
                    delete_option('shgdprdm_adminHasLicence');
                    return $input;
                }
            }

            // If there is no input=>licence_number or if De-activate
            if (isset($input['shgdprdm_deactivate_licence'])) {
                add_action('updated_option_action', $updateFunction, 10, 0);
                do_action('updated_option_action', $updateFunction, 10, 0);
                // // remove the action
                remove_action('updated_option_action', $updateFunction, 10, 0);
                delete_option('shgdprdm_adminHasLicence');
                return $input;
            }
            // Fall back in the event that the validation calss is not entered into
            if (false === get_option('shgdprdm_adminVerifyLicence')) {
                add_option('shgdprdm_adminVerifyLicence', array('licence_number' => $input['licence_number'], 'licence_valid' => false, 'licence_msg' => false ));
            } else {
                update_option('shgdprdm_adminVerifyLicence', array('licence_number' => $input['licence_number'], 'licence_valid' => false, 'licence_msg' => false ));
            }
            delete_option('shgdprdm_adminHasLicence');
            return $input;
        }

        public function shgdprdm_admin_plugin_options_validate($input)
        {
            return '<script>console.log("Extra Option: '.$input.'");</script>';
            if (null != $input && is_array($input)) {
                if (false === get_option('shgdprdm_admin_plugins')) {
                    add_option('shgdprdm_admin_plugins', $input);
                } else {
                    update_option('shgdprdm_admin_plugins', $input);
                }
            }
            // Override
            return $input;
        }

        public function shgdprdm_admin_plugin_settings_validate($input)
        {
            if (null == $input) {
                $input =  array('Woo-Commerce-Guest-Accounts' => array('Woo-Commerce-Guest-Accounts','test') ) ;
                add_option('Woo-Commerce-Guest-Accounts', $input);
            }
            if (is_array($input)) {
                if (false === get_option('shgdprdm_admin_plugins_settings')) {
                    add_option('shgdprdm_admin_plugins_settings', $input);
                } else {
                    update_option('shgdprdm_admin_plugins_settings', 'test');
                }
            }
            // Override
            return $input;
        }

        public function shgdprdm_admin_index()
        {
            require_once $this->includesPath.'shgdprdm_admin_nav.php';
            require_once $this->templatesPath.'shgdprdm_admin.php';
        }


        public function shgdprdm_main()
        {
            require_once $this->templatesPath.'shgdprdm_main.php';
            require_once $this->includesPath.'shgdprdm_sef.php';

            // Set the replacement Text for delete
            if (false === get_option('shgdprdm_text_options')) {
                add_option('shgdprdm_text_options', array('text_option' => (defined('SHGDPRDM_DEFAULT_REPLACEMENT_TEXT_IN_DB')?SHGDPRDM_DEFAULT_REPLACEMENT_TEXT_IN_DB:'Deleted User') ));
            }
            if (false !== get_option('shgdprdm_text_options') && null == get_option('shgdprdm_text_options')['text_option']) {
                update_option('shgdprdm_text_options', array('text_option' => (defined('SHGDPRDM_DEFAULT_REPLACEMENT_TEXT_IN_DB')?SHGDPRDM_DEFAULT_REPLACEMENT_TEXT_IN_DB:'Deleted User') ));
            }
            // Overwrite Legacy before patch
            if (false !== get_option('shgdprdm_text_options') && get_option('shgdprdm_text_options')['text_option'] === 'SHGDPRDM_DEFAULT_REPLACEMENT_TEXT_IN_DB') {
                update_option('shgdprdm_text_options', array('text_option' => (defined('SHGDPRDM_DEFAULT_REPLACEMENT_TEXT_IN_DB')?SHGDPRDM_DEFAULT_REPLACEMENT_TEXT_IN_DB:'Deleted User') ));
            }
        }


        public function shgdprdm_home()
        {
            require_once $this->templatesPath.'shgdprdm_home.php';
            require_once $this->includesPath.'shgdprdm_opt_home.php';
        }
        
        public function shgdprdm_incl_validate()
        {
            if (!class_exists('SHGdprdm_ValidateControl')) {
                require_once $this->sharedPath.'shgdprdm_validate_control.class.php';
            }
            $this->validateControl = new SHGdprdm_ValidateControl();
        }

        public function shgdprdm_pending()
        {
            if (empty($this->validateControl)) {
                $this->validateControl = new SHGdprdm_ValidateControl();
            }
            if ($this->validateControl->shgdprdm_validateVerifyLicence() && $this->validateControl->shgdprdm_validateHasLicence()) {
                if (file_exists($this->classPath.'shgdprdm_wpListClass.class.php') && file_exists($this->classPath.'shgdprdm_pending.class.php')) {
                    require_once $this->classPath.'shgdprdm_wpListClass.class.php';
                    require_once $this->classPath.'shgdprdm_pending.class.php';
                }
            }
            require_once $this->templatesPath.'shgdprdm_pending.php';
            require_once $this->includesPath.'shgdprdm_opt_pending.php';
        }

        public function shgdprdm_options()
        {
            require_once $this->templatesPath.'shgdprdm_options.php';
            require_once $this->includesPath.'shgdprdm_opt_sb.php';
            require_once $this->includesPath.'shgdprdm_opt_rt.php';
            require_once $this->includesPath.'shgdprdm_opt_tc.php';
            require_once $this->includesPath.'shgdprdm_opt_pp.php';
        }

        public function shgdprdm_review()
        {
            if (empty($this->validateControl)) {
                $this->validateControl = new SHGdprdm_ValidateControl();
            }
            if ($this->validateControl->shgdprdm_validateVerifyLicence() && $this->validateControl->shgdprdm_validateHasLicence()) {
                // if( $this->shgdprdm_verifyLicence() ){
                if (file_exists($this->classPath.'shgdprdm_wpListClass.class.php') && file_exists($this->classPath.'shgdprdm_review.class.php')) {
                    require_once $this->classPath.'shgdprdm_wpListClass.class.php';
                    require_once $this->classPath.'shgdprdm_review.class.php';
                }
            }
            require_once $this->templatesPath.'shgdprdm_review.php';
            require_once $this->includesPath.'shgdprdm_opt_review.php';
        }

        public function shgdprdm_records()
        {
            if (empty($this->validateControl)) {
                $this->validateControl = new SHGdprdm_ValidateControl();
            }
            require_once $this->includesPath.'shgdprdm_search_submit.php';
            require_once $this->includesPath.'shgdprdm_disrec_helpers.php';
            require_once $this->templatesPath.'shgdprdm_disrec.php';
            if (
                file_exists($this->classPath.'shgdprdm_wcf.pro.class.php') &&
                $this->validateControl->shgdprdm_validateVerifyLicence() &&
                $this->validateControl->shgdprdm_validateHasLicence() &&
                $this->validateControl->shgdprdm_validateIsProLicence('wcf')
            ) {
                require_once $this->classPath.'shgdprdm_wcf.pro.class.php';
            }
            if (
                file_exists($this->classPath.'shgdprdm_eddf.pro.class.php') &&
                $this->validateControl->shgdprdm_validateVerifyLicence() &&
                $this->validateControl->shgdprdm_validateHasLicence() &&
                $this->validateControl->shgdprdm_validateIsProLicence('eddf')
            ) {
                require_once $this->classPath.'shgdprdm_eddf.pro.class.php';
            }
            if ($this->validateControl->shgdprdm_validateVerifyLicence() && $this->validateControl->shgdprdm_validateHasLicence()) {
                // if( $this->shgdprdm_verifyLicence() ){
                if (file_exists($this->includesPath.'shgdprdm_disrec_helpers_prem.php')) {
                    require_once $this->includesPath.'shgdprdm_disrec_helpers_prem.php';
                }
            }
        }

        public function shgdprdm_help()
        {
            require_once $this->includesPath.'shgdprdm_opt_help.php';
            require_once $this->templatesPath.'shgdprdm_help.php';
        }

        public function shgdprdm_registerLicence()
        {
            require_once $this->classPath.'shgdprdm_validate.class.php';
            require_once $this->includesPath.'shgdprdm_reg_lic_num.php';
            require_once $this->templatesPath.'shgdprdm_register.php';
        }

        public function shgdprdm_language()
        {
            require_once $this->assetPath.'shgdprdm_language.php';
        }

        public function shgdprdm_helpers()
        {
            require_once $this->includesPath.'shgdprdm_helpers.php';
        }

        public function shgdprdm_export_action()
        {
            if (empty($this->validateControl)) {
                $this->validateControl = new SHGdprdm_ValidateControl();
            }
            require_once $this->includesPath.'shgdprdm_dbf.php';
            require_once $this->includesPath.'shgdprdm_export_helpers.php';
            require_once $this->includesPath.'shgdprdm_export.php';
            if (
                file_exists($this->classPath.'shgdprdm_wcf.pro.class.php') &&
                $this->validateControl->shgdprdm_validateVerifyLicence() &&
                $this->validateControl->shgdprdm_validateHasLicence() &&
                $this->validateControl->shgdprdm_validateIsProLicence('wcf')
            ) {
                require_once $this->classPath.'shgdprdm_wcf.pro.class.php';
            }
            if (
                file_exists($this->classPath.'shgdprdm_eddf.pro.class.php') &&
                $this->validateControl->shgdprdm_validateVerifyLicence() &&
                $this->validateControl->shgdprdm_validateHasLicence() &&
                $this->validateControl->shgdprdm_validateIsProLicence('eddf')
            ) {
                require_once $this->classPath.'shgdprdm_eddf.pro.class.php';
            }
        }

        public function shgdprdm_has_woocommerce()
        {
            !defined('WC_VERSION') ? $this->hasWooCommerce = false : $this->hasWooCommerce = WC_VERSION;
            update_option('shgdprdm_admin_plugins', array('hasWooCommerce' => $this->hasWooCommerce));
        }

        public function shgdprdm_delete_action()
        {
            require_once $this->includesPath.'shgdprdm_verify_delete.php';
        }

        public function shgdprdm_verify_delete_action()
        {
            require_once $this->includesPath.'shgdprdm_verify_delete.php';
        }

        public function shgdprdm_search_action()
        {
            require_once $this->includesPath.'shgdprdm_msf.php';
        }

        public function shgdprdm_search_action_return()
        {
            require_once $this->classPath.'shgdprdm_msf.class.php';
        }

        public function shgdprdm_sync_missing_record()
        {
            require_once $this->includesPath.'shgdprdm_dbf.php';
            require_once $this->classPath.'shgdprdm_sync.class.php';
            require_once $this->includesPath.'shgdprdm_sync_ext_record.php';
        }

        public function shgdprdm_errors_action()
        {
            require_once $this->includesPath.'shgdprdm_error.php';
        }

        public function shgdprdm_admin_notice__success($msg = null)
        {
            if (!$msg) {
                $msg = 'Success!';
            } ?>
        <div class="notice notice-success is-dismissible">
            <p><?php _e($msg, 'gdpr-data-manager'); ?></p>
        </div>
        <?php
        }

        public function shgdprdm_admin_notice__dynamic()
        {
            $shgdprdm_admin_msg_option = get_option('shgdprdm_admin_msg');
            if (isset($shgdprdm_admin_msg_option) && $shgdprdm_admin_msg_option != '') {
                $class = $shgdprdm_admin_msg_option['class'];
                $msg = $shgdprdm_admin_msg_option['msg']; ?>
          <div class="notice notice-<?=$class; ?> is-dismissible">
              <p><?php _e($msg, 'gdpr-data-manager'); ?></p>
          </div>
      <?php
            }
        }

        public function shgdprdm_admin_notice__error($msg = null)
        {
            if (!$msg) {
                $msg = '<strong>error!</strong><br>Something has gone wrong.';
            } ?>
        <div class="notice notice-error is-dismissible">
            <p><?php _e($msg, 'gdpr-data-manager'); ?></p>
        </div>
        <?php
        }

        public function shgdprdm_registerNewLicenceNotice()
        {
            if (empty($this->validateControl)) {
                $this->validateControl = new SHGdprdm_ValidateControl();
            }
            if (get_transient('shgdprdm_registerNewLicenceNotice')) {
                if (!$this->validateControl->shgdprdm_validateHasLicence()) {?>
          <div class="notice notice-info is-dismissible">
              <div class="shgdprdm-notice-icon-text-wrapper">
                <div class="shgdprdm-notice-icon-container">
                  <div class="shgdprdm-notice-icon-inner-container">
                    <img src="<?php echo plugins_url('assets/images/shgdprdm_logo-icon.png', dirname(__FILE__)); ?>" alt="Seahorse Logo"/>
                  </div>
                </div>
                <div class="shgdprdm-notice-icon-text-container">
                  <div>
                    <p><?php echo SHGDPRDM_ACTIVATION_NOTICE_INTRO ?></p>
                  </div>
                </div>
              </div>
          </div>
        <?php
        }
                /* Delete transient, only display this notice once. */
                delete_transient('shgdprdm_registerNewLicenceNotice');
            }
        }

        public function shgdprdm_activate()
        {
            // flush reqrite rules
            flush_rewrite_rules();

            set_transient('shgdprdm_registerNewLicenceNotice', true, 5);

            // Add a new Role & User for reassigning deleted user to
            // https://codex.wordpress.org/Roles_and_Capabilities
            // https://wordpress.stackexchange.com/questions/4725/how-to-change-a-users-role
            // https://developer.wordpress.org/reference/functions/add_role/
            // https://developer.wordpress.org/plugins/users/roles-and-capabilities/
            // https://codex.wordpress.org/Function_Reference/add_role
            // https://wordpress.stackexchange.com/questions/262423/do-custom-user-roles-have-any-default-capabilities
            // https://developer.wordpress.org/plugins/users/working-with-users/
            // https://developer.wordpress.org/reference/functions/get_user_by/
            add_role('custom_role', 'GDM Deleted User (GDPR)');
            wp_create_user('DeletedUser', '123456', 'deleted@deleted.com');
            $newU = get_user_by('email', 'deleted@deleted.com');
            $newU->set_role('GDM Deleted User (GDPR)');
            if (get_option('shgdprdm_deleted_user_id') != null && get_option('shgdprdm_deleted_user_id') != '' && get_option('shgdprdm_deleted_user_id') !== '') {
                update_option('shgdprdm_deleted_user_id', $newU->ID);
            } else {
                add_option('shgdprdm_deleted_user_id', $newU->ID);
            }
            update_option('shgdprdm_search_options', array('search_option' => '1'));
            // if(!isset(get_option('shgdprdm_text_options')['text_option'])){
            //   update_option('shgdprdm_text_options', array('text_option' => SHGDPRDM_DEFAULT_REPLACEMENT_TEXT_IN_DB));
            // }
            if (get_option('shgdprdm_admin_notice') != '') {
                update_option('shgdprdm_admin_notice', '');
            }
            if (get_option('shgdprdm_admin_plugins_settings') === null) {
                update_option('shgdprdm_admin_plugins_settings', array('extraSearchOption' => array()));
            }
            if (get_option('shgdprdm_user_path') == null || get_option('shgdprdm_user_path') == '' || get_option('shgdprdm_user_path') === '') {
                add_option('shgdprdm_user_path', $this->userPath);
            } elseif (get_option('shgdprdm_user_path') != $this->userPath) {
                update_option('shgdprdm_user_path', $this->userPath);
            }
            if (get_option('shgdprdm_user_fns') == null || get_option('shgdprdm_user_fns') == '' || get_option('shgdprdm_user_fns') === '') {
                add_option('shgdprdm_user_fns', get_home_path());
            } elseif (get_option('shgdprdm_user_fns') != get_home_path()) {
                update_option('shgdprdm_user_fns', get_home_path());
            }
            include $this->includesPath.'shgdprdm_review_table_installer.php' ;
        }

        public function shgdprdm_deactivate()
        {
            update_option('shgdprdm_search_options', array('search_option' => ''));
            // delete_option('shgdprdm_text_options');
        }

        public function shgdprdm_uninstall()
        {
        }

        public function shgdprdm_enqueue_assets()
        {
            // enqueue pages/css/js etc
            wp_enqueue_style('shshgdprdm_styles', plugins_url('/assets/shgdprdm_style.css', $this->pluginBaseDir));
            wp_enqueue_script('shshgdprdm_script', plugins_url('/assets/shgdprdm_script.js', $this->pluginBaseDir), array( ), SHGDPRDM_VERSION, true);
        }

        public function shgdprdm_register_conditional_scripts()
        {
            wp_register_script('shgdprdmdel_script', plugins_url('/assets/shgdprdm_delete_script.js', $this->pluginBaseDir), array(), SHGDPRDM_VERSION, true);
        }

        public function shgdprdm_enqueue_action_assets()
        {
            wp_enqueue_script('shgdprdmdel_script', plugins_url('/assets/shgdprdm_delete_script.js', $this->pluginBaseDir), array(), SHGDPRDM_VERSION, true);
        }

        public function codemirror_enqueue_scripts($hook)
        {
            $cm_settings['codeEditor'] = wp_enqueue_code_editor(array('type' => 'text/html'));
            wp_localize_script('jquery', 'cm_settings', $cm_settings);

            wp_enqueue_script('wp-theme-plugin-editor');
            wp_enqueue_style('wp-codemirror');
        }

        public function shgdprdm_getPluginDir()
        {
            return $this->pluginDirectory;
        }
    } // end of class
} //end of "if class exists"
