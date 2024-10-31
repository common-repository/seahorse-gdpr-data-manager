<?php
/* prevent access from outside cms */
defined('SHGDPRDM_ABSPATH') or die('You are not authorised to access this file.');

if (!class_exists('SHGdprdm_ShortCodes')) {
    class SHGdprdm_ShortCodes
    {
        protected $pluginDirectory;
        protected $shgdprdm_assets;
        
        public function __construct()
        {
            if (count(func_get_args()) !== 1) {
                $siteEmail = '';
                if (get_bloginfo('admin_email')) {
                    $siteEmail = '<br><a class="button shgdprdm-usr-btn shgdprdm-usr-btn-regular" href="mailto:'.get_bloginfo("admin_email").'">Contact '.get_bloginfo("name").'</a>';
                }
                throw new Exception('<strong>GDPR Data Manager - ERROR!</strong>
            <br>Action cannot be performed. (ref: shgdprdm-SCodes-001)
            <br>Please Contact '.get_bloginfo("name").$siteEmail.'.');
            }
          
          
            $params= func_get_args();
            $param = $params[0];
            // exit(print_r(dirname($param)));
            $this->pluginDirectory = dirname($param);
            $this->shgdprdm_assets = $this->pluginDirectory.'/assets/';
          
            if (method_exists($this, 'register_wp')) {
                self::register_wp();
            }
        } // end class construct
        
        
        // register any WP dependencies
        private function register_wp()
        {
            add_action('init_register', array($this,'shgdprdm_register_wp'));
            do_action('init_register');
        }
        
        // These are the dependencies required for the shortcodes to run
        public function shgdprdm_register_wp()
        {
            require_once $this->shgdprdm_assets.'shgdprdm_language.php';
        }
        
        
        
        // Shortcode Helper Function
        private function getShortcodeSection($locale, $option_name, $sectionType, $sectionOptions)
        {
            $text = '';
            $default_text = 'No default ' . $sectionType . ' currently available';

            $option_item = $sectionOptions['item'];
            $defaultVar = $sectionOptions['default'];

            if ($sectionType === 'Heading') {
                $text .= '<p><h2>';
            } else {
                $text .= '<p>';
            }

            // Extract the correct Default Text
            if (defined($defaultVar)) {
                $defaultText = constant($defaultVar);
            } else {
                $defaultText = $default_text;
            }
            
            // Set the Output text
            if (false === get_option($option_name)) {
                // If there is no Option Currently Saved, use default text
                $text .= $defaultText;
            } elseif (empty($locale)) {
                // If a Language attribute has not been added to the shortcode

                // Find the current Site Locale
                $siteLocale = get_locale();

                if (isset(get_option($option_name)[$siteLocale][$option_item]) &&
                    get_option($option_name)[$siteLocale][$option_item] != ''
                ) {
                    // If there is an option for the language of the site
                    $text .= get_option($option_name)[$siteLocale][$option_item];
                } elseif (isset(get_option($option_name)['legacy'][$option_item]) &&
                    get_option($option_name)['legacy'][$option_item] != ''
                ) {
                    // If there is no option for the site language, but there is a
                    // previously saved option - convert old saved option
                    $text .= get_option($option_name)['legacy'][$option_item];
                } elseif (isset(get_option($option_name)[$option_item]) &&
                    get_option($option_name)[$option_item] != ''
                ) {
                    // Fallback to capture any pre-update saved option
                    $text .= get_option($option_name)[$option_item];
                } else {
                    $text .= $defaultText;
                }
            } else {
                // If a language attribute has been set in the shortcode

                if (isset(get_option($option_name)[$locale][$option_item]) &&
                    get_option($option_name)[$locale][$option_item] != ''
                ) {
                    // Check if there is a matching option for the described language
                    $text .= get_option($option_name)[$locale][$option_item];
                } elseif (isset(get_option($option_name)['legacy'][$option_item]) &&
                    get_option($option_name)['legacy'][$option_item] != ''
                ) {
                    // If there is no option for the described language, but there is a
                    // previously saved option - convert old saved option
                    $text .=  get_option($option_name)['legacy'][$option_item];
                } elseif (isset(get_option($option_name)[$option_item]) &&
                    get_option($option_name)[$option_item] != ''
                ) {
                    // Fallback to capture any pre-update saved option
                    $text .= get_option($option_name)[$option_item];
                } else {
                    $text .= $defaultText;
                }
            }

            if ($sectionType === 'Heading') {
                $text .= '</h2></p>';
            } else {
                $text .= '</p>';
            }
            return $text;
        }
        
        // Add Shortcode
        public function shgdprdm_6esa6gxzr7_shortcode($atts = [], $content = null, $tag = '')
        {
          
            // normalize attribute keys, lowercase
            $atts = array_change_key_case((array)$atts, CASE_LOWER);

            // override default attributes with user attributes
            $wporg_atts = shortcode_atts(['lang' => ''], $atts, $tag);
            

            $sections = array(
                'shgdprdm_ppolicy_gen_options' => array(
                    'Heading' => array(
                        'item' => 'ppolicy_gen_header_option',
                        'default' => 'SHGDPRDM_DEFAULT_POLICY_GEN_HEADER_CONDITIONS_TEXT'
                    ),
                    'text' => array(
                        'item' => 'ppolicy_gen_option',
                        'default' => 'SHGDPRDM_DEFAULT_POLICY_GEN_CONDITIONS_TEXT',
                    )
                ),
                'shgdprdm_ppolicy_mng_options' => array(
                    'Heading' => array(
                        'item' => 'ppolicy_mng_header_option',
                        'default' => 'SHGDPRDM_DEFAULT_POLICY_MNG_HEADER_CONDITIONS_TEXT'
                    ),
                    'text' => array(
                        'item' => 'ppolicy_mng_option',
                        'default' => 'SHGDPRDM_DEFAULT_POLICY_MNG_CONDITIONS_TEXT'
                    )
                ),
                'shgdprdm_ppolicy_ico_options' => array(
                    'Heading' => array(
                        'item' => 'ppolicy_ico_header_option',
                        'default' => 'SHGDPRDM_DEFAULT_POLICY_ICO_HEADER_CONDITIONS_TEXT'
                    ),
                    'text' => array(
                        'item' => 'ppolicy_ico_option',
                        'default' => 'SHGDPRDM_DEFAULT_POLICY_ICO_CONDITIONS_TEXT'
                    )
                ),
                'shgdprdm_ppolicy_use_options' => array(
                    'Heading' => array(
                        'item' => 'ppolicy_use_header_option',
                        'default' => 'SHGDPRDM_DEFAULT_POLICY_USE_HEADER_CONDITIONS_TEXT'
                    ),
                    'text' => array(
                        'item' => 'ppolicy_use_option',
                        'default' => 'SHGDPRDM_DEFAULT_POLICY_USE_CONDITIONS_TEXT'
                    )
                ),
                'shgdprdm_ppolicy_sha_options' => array(
                    'Heading' => array(
                        'item' => 'ppolicy_sha_header_option',
                        'default' => 'SHGDPRDM_DEFAULT_POLICY_SHA_HEADER_CONDITIONS_TEXT'
                    ),
                    'text' => array(
                        'item' => 'ppolicy_sha_option',
                        'default' => 'SHGDPRDM_DEFAULT_POLICY_SHA_CONDITIONS_TEXT'
                    )
                )
            );

            $text = '';
            foreach ($sections as $sectionOptionName => $sectionDetails) {
                foreach ($sectionDetails as $sectionPart => $partDetails) {
                    $text .= $this->getShortcodeSection(
                        $wporg_atts['lang'],
                        $sectionOptionName,
                        $sectionPart,
                        $partDetails
                    );
                }
            }
            
            return $text;
        }
        
        
        
          
        // Function to add the shortcode into WP Shortcodes
        public function shgdprdm_shortcodes_init()
        {
            add_shortcode('gdm-privacy-policy-custom', array($this, 'shgdprdm_6esa6gxzr7_shortcode'));
        }
        
        // public function being called from main Plugin File
        public function register_shortcodes()
        {
            add_action('init', array($this, 'shgdprdm_shortcodes_init' ));
        }
    } // End of Class
} // End if class exists
