<?php
defined('SHGDPRDM_ABSPATH') or die('You are not authorised to access this file.');
if (!class_exists('SHGdprdm_ValidateControl')) {
    class SHGdprdm_ValidateControl
    {
        
        /**
         * Class constructor
         *
         * return void
         */
        public function __construct()
        {
        }
        
        public function shgdprdm_validateVerifyLicence()
        {
            if (false === get_option('shgdprdm_adminVerifyLicence')) {
                return false;
            }
            if (false !== get_option('shgdprdm_adminVerifyLicence')) {
                if (get_option('shgdprdm_adminVerifyLicence')['licence_number'] == null || get_option('shgdprdm_adminVerifyLicence')['licence_number'] == '') {
                    return false;
                }
                if (
                      get_option('shgdprdm_adminVerifyLicence')['licence_valid'] == null ||
                      get_option('shgdprdm_adminVerifyLicence')['licence_valid'] == '' ||
                      get_option('shgdprdm_adminVerifyLicence')['licence_valid'] === false
                    ) {
                    return false;
                }
                if (
                      get_option('shgdprdm_adminVerifyLicence')['licence_msg'] == null ||
                      get_option('shgdprdm_adminVerifyLicence')['licence_msg'] == '' ||
                      get_option('shgdprdm_adminVerifyLicence')['licence_msg'] != 'valid'
                    ) {
                    return false;
                }
            }
            return true;
        }
        
        public function shgdprdm_validateHasLicence()
        {
            if (false === get_option('shgdprdm_adminHasLicence')) {
                return false;
            }
            if (false !== get_option('shgdprdm_adminHasLicence')) {
                if (get_option('shgdprdm_adminHasLicence')['licence_number'] == null || get_option('shgdprdm_adminHasLicence')['licence_number'] == '') {
                    return false;
                }
            }
            return true;
        }
    
        public function shgdprdm_validateIsProLicence($lType)
        {
            if (false === get_option('shgdprdm_adminVerifyLicence')) {
                return false;
            }
            if (!isset(get_option('shgdprdm_adminVerifyLicence')['licence_type']) || (null === get_option('shgdprdm_adminVerifyLicence')['licence_type'])) {
                return false;
            }
            
            // if (null === get_option('shgdprdm_adminVerifyLicence')['licence_type']) {
            //     return false;
            // }
            
            if ($lType === 'wcf' && get_option('shgdprdm_adminVerifyLicence')['licence_type'] === '2.1') {
                return true;
            }
            if ($lType === 'eddf' && get_option('shgdprdm_adminVerifyLicence')['licence_type'] === '2.2') {
                return true;
            }
            if (get_option('shgdprdm_adminVerifyLicence')['licence_type'] === '2.3') {
                return true;
            }
            // if ($lType === 'all' && get_option('shgdprdm_adminVerifyLicence')['licence_type'] === '2.1') {
            //     return true;
            // }
            return false;
        }
    }
}
