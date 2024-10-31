<?php
defined('SHGDPRDM_ABSPATH') or die('You are not authorised to access this file.');
if (!class_exists('SHGdprdm_ValidateLicenceKey')) {
    class SHGdprdm_ValidateLicenceKey
    {
        private $siteName = SHGDPRDM_VALIDATE_DEFAULT_URL;
        private $siteNameUAT = SHGDPRDM_VALIDATE_UAT_URL;
        private $itemID_P = '174';
        private $itemID_S = '6213';
        private $inputKey;
        private $keyResponse;
        private $decodedResponse;
        private $licenceStatus;
        private $validationRef;
        private $type;
        private $expiryDate;
        private $licenceType;


        /** Class constructor */
        public function __construct()
        {
            if (count(func_get_args()) !== 1) {
                throw new Exception('Error! Action cannot be performed - Error Ref: VALC_001');
            }
            $this->type = func_get_args();

            if ($this->type[0] === 'deactivate') {
                if (get_option('shgdprdm_adminVerifyLicence') === false || get_option('shgdprdm_adminVerifyLicence')['licence_number'] == '') {
                    throw new Exception('Error! Action cannot be performed - Error Ref: VALC_002');
                }
                $this->inputKey = get_option('shgdprdm_adminVerifyLicence')['licence_number'];

                if (method_exists($this, 'shgdprdm_deactivate')) {
                    $this->keyResponse = $this->shgdprdm_deactivate();
                }
            } elseif ($this->type[0] === 'activate') {
                if (get_option('shgdprdm_adminHasLicence') === false || get_option('shgdprdm_adminHasLicence')['licence_number'] == '') {
                    throw new Exception('Error! Action cannot be performed - Error Ref: VALC_003');
                }
                $this->inputKey = get_option('shgdprdm_adminHasLicence')['licence_number'];

                if (method_exists($this, 'shgdprdm_checkKey')) {
                    $this->keyResponse = $this->shgdprdm_checkKey();
                } else {
                    $this->keyResponse = false;
                }
            } elseif ($this->type[0] === 'check-licence') {
                if (get_option('shgdprdm_adminVerifyLicence') === false || get_option('shgdprdm_adminVerifyLicence')['licence_number'] == '') {
                    throw new Exception('Error! Action cannot be performed - Error Ref: VALC_004');
                }
                $this->inputKey = get_option('shgdprdm_adminVerifyLicence')['licence_number'];
                if (method_exists($this, 'shgdprdm_checkLicence')) {
                    $this->keyResponse = $this->shgdprdm_checkLicence();

                    // If licence not valid on this site & licence is valid & there are no activations left
                    if ($this->keyResponse && $this->validationRef == 'valid') {
                        if (
                  false === get_option('shgdprdm_adminVerifyLicence') ||
                  (
                      false !== get_option('shgdprdm_adminVerifyLicence') &&
                    get_option('shgdprdm_adminVerifyLicence')['licence_valid'] !== true &&
                    get_option('shgdprdm_adminVerifyLicence')['licence_msg'] !== 'valid'
                  )
                ) {
                            if ($this->decodedResponse->activations_left === 0) {
                                $this->keyResponse = false;
                                $this->validationRef = 'no_activations_left';
                            }
                        }
                    }
                } else {
                    $this->keyResponse = false;
                }
            } else {
                $this->keyResponse = false;
                throw new Exception('Error! Action cannot be performed - Error Ref: VALC_005');
            }


            if (method_exists($this, 'shgdprdm_updateSystemInfo')) {
                $this->shgdprdm_updateSystemInfo();
            } else {
                delete_option('shgdprdm_adminVerifyLicence');
            }
        }

        public function __destruct()
        {
        }

        /**
         * Run Validation Check on Licence Key
         *
         * @return boolean|fn(boolean response)
         */
        private function shgdprdm_checkKey()
        {
            if ($this->inputKey) {
                $existingKey = '';
                if (false !== get_option('shgdprdm_adminVerifyLicence')) {
                    $existingKey = get_option('shgdprdm_adminVerifyLicence')['licence_number'];
                }
                if (!$existingKey || $existingKey == '' || $existingKey == null) {
                    $this->shgdprdm_activate();
                    if ($this->licenceStatus == 'valid') {
                        return true;
                    }
                    return false;
                } elseif ($existingKey !== $this->inputKey) {
                    $this->shgdprdm_activate();
                    if ($this->licenceStatus == 'valid') {
                        return true;
                    }
                    return false;
                } elseif ($existingKey == $this->inputKey && false === get_option('shgdprdm_adminVerifyLicence')['licence_valid']) {
                    $this->shgdprdm_activate();
                    if ($this->licenceStatus == 'valid') {
                        return true;
                    }
                    return false;
                } else {
                    return true;
                }
            } else {
                throw new Exception('Error! Action cannot be performed - Error Ref: VALC_006');
            }
        }


        /**
         * Run Remote API Call
         *
         * @return JSON Array|FALSE
         */
        private function shgdprdm_validateRequest($type)
        {
            if ($type != 'check_license' && $type != 'activate_license' && $type != 'deactivate_license') {
                $this->keyResponse = false;
                return;
            }
            
            $this->licenceType = null;

            // data to send in our API request
            $api_params = array(
                'edd_action' => $type,
                'license'    => $this->inputKey,
                'item_id'    => $this->itemID_S, // The ID of the item in EDD
                'url'        => home_url(),
            );

            $response = wp_remote_post($this->siteName, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ));
            $this->keyResponse = $response;

            if ($this->keyResponse !== false) {
                $this->licenceType = '1';
            }

            /// Added to allow checks on other sites
            if (
                $this->shgdprdm_getLicenceInfo() !== 'valid' &&
                $this->shgdprdm_getLicenceInfo() !== 'deactiated' &&
                $this->shgdprdm_getLicenceInfo() !== 'inactive' &&
                $this->shgdprdm_getLicenceInfo() !== 'expired'
            ) {
                $response = wp_remote_post($this->siteNameUAT, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ));
                $this->keyResponse = $response;
            }
            if ($this->keyResponse !== false) {
                $this->licenceType = '1';
            }
            
            
            /// Added to allow checks for multiple Licence Types
            if (
                $this->shgdprdm_getLicenceInfo() !== 'valid' &&
                $this->shgdprdm_getLicenceInfo() !== 'deactiated' &&
                $this->shgdprdm_getLicenceInfo() !== 'inactive' &&
                $this->shgdprdm_getLicenceInfo() !== 'expired'
            ) {
                // Change Item ID
                $api_params['item_id'] = $this->itemID_P;
               
                if (
                    $this->shgdprdm_getLicenceInfo() !== 'valid' &&
                    $this->shgdprdm_getLicenceInfo() !== 'deactiated' &&
                    $this->shgdprdm_getLicenceInfo() !== 'inactive' &&
                    $this->shgdprdm_getLicenceInfo() !== 'expired'
                ) {
                    $response = wp_remote_post($this->siteName, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ));
                    $this->keyResponse = $response;
                }
          
                if ($this->keyResponse !== false) {
                    $this->shgdprdm_getLicenceInfo();
                    if (!empty($this->decodedResponse->price_id)) {
                        $this->licenceType = '2.'.$this->decodedResponse->price_id;
                    }
                }
            }
            
            
            /// Added to allow checks on other sites
            if (
                $this->shgdprdm_getLicenceInfo() !== 'valid' &&
                $this->shgdprdm_getLicenceInfo() !== 'deactiated' &&
                $this->shgdprdm_getLicenceInfo() !== 'inactive' &&
                $this->shgdprdm_getLicenceInfo() !== 'expired'
            ) {
                $response = wp_remote_post($this->siteNameUAT, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ));
                $this->keyResponse = $response;
            }
            if ($this->keyResponse !== false) {
                $this->shgdprdm_getLicenceInfo();
                if (!empty($this->decodedResponse->price_id)) {
                    $this->licenceType = '2.'.$this->decodedResponse->price_id;
                }
            }
            
            


            // $response = wp_remote_request(
            //   $this->siteName.
            //   '/?edd_action='.$type.
            //   '&item_id='.$this->itemID.
            //   '&license='.$this->inputKey.
            //   '&url='.home_url()
            // );				// Correct CaLL

            if (is_wp_error($response)) {
                if (defined('SHGDPRDM_DEBUG')) {
                    if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)) {
                        if (is_wp_error($response) && !empty($response->get_error_message())) {
                            $excceptionMessage = '<br><p>Error Code' . $response->get_error_code() . '<br>Error Message: ' . $response->get_error_message() . '</p>';
                        } else {
                            $excceptionMessage =  '<br><p>Error! Action cannot be performed - Error Ref: VALC_007</p>';
                        }
                    } else {
                        $excceptionMessage = '';
                    }
                }
                throw new Exception($excceptionMessage);
                return;
            } else {
                $this->keyResponse = $response;
                return;
            }
        }


        /**
         * Run Activation on a valid Licence Key
         *
         * @return boolean
         */
        private function shgdprdm_activate()
        {
            $this->shgdprdm_validateRequest('activate_license');
            if ($this->keyResponse) {
                $this->validationRef = $this->shgdprdm_getLicenceInfo();
                if ($this->validationRef) {
                    if ($this->validationRef == 'valid') {
                        $this->licenceStatus = $this->validationRef;
                        return true;
                    }
                    return false;
                }
                return false;
            }
            return false;
        }


        /**
        * Run De-activatin on a valid Licence Key
        *
        * @return boolean
        */
        private function shgdprdm_deactivate()
        {
            $this->shgdprdm_validateRequest('deactivate_license');
            if ($this->keyResponse) {
                $this->validationRef = $this->shgdprdm_getLicenceInfo();
                if ($this->validationRef) {
                    return true;
                }
                return false;
            }
            return false;
        }

        private function shgdprdm_checkLicence()
        {
            $this->shgdprdm_validateRequest('check_license');
            if ($this->keyResponse) {
                $this->validationRef = $this->shgdprdm_getLicenceInfo();
                $this->licenceStatus = $this->validationRef;
                if ($this->validationRef === 'valid' || $this->validationRef === 'deactiated' || $this->validationRef === 'inactive') {
                    return true;
                }
                return false;
            }
            return false;
        }

        /**
         * Extract licence status from request response
         *
         * @return String | FALSE
         */
        private function shgdprdm_getLicenceInfo()
        {
            if (!$this->keyResponse) {
                return false;
            }

            $response_key = 'license';
            $this->decodedResponse = json_decode(wp_remote_retrieve_body($this->keyResponse));
            /* If invalid JSON, return original result body. */
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->decodedResponse = false;
                return false;
            }

            /* If return key is set and exists, return array item. */
            if (false === $this->decodedResponse->success) {
                if (property_exists($this->decodedResponse, 'error')) {
                    return $this->decodedResponse->error;
                }
                return $this->decodedResponse->$response_key;
            } else {
                if ($this->decodedResponse->$response_key) {
                    $this->expiryDate = $this->decodedResponse->expires;
                    return  $this->decodedResponse->$response_key;
                }
                return false;
            }
        }



        private function shgdprdm_updateSystemInfo()
        {
            if ($this->keyResponse && $this->licenceStatus == 'valid') {
                $valid = true;
            } else {
                $valid = false;
            }
            if (false === get_option('shgdprdm_adminVerifyLicence')) {
                add_option('shgdprdm_adminVerifyLicence', array('licence_number' => $this->inputKey, 'licence_valid' => $valid, 'licence_msg' => $this->validationRef, 'licence_type' => $this->licenceType ));
            } else {
                update_option('shgdprdm_adminVerifyLicence', array('licence_number' => $this->inputKey, 'licence_valid' => $valid, 'licence_msg' => $this->validationRef, 'licence_type' => $this->licenceType ));
            }
            return;
        }


        /**
        * Run Check on Remaining Subscription Duration
        *
        * @return date string
        */
        public function shgdprdm_getExpiry()
        {
            if (!$this->decodedResponse) {
                return false;
            }

            if (
    $this->licenceStatus !== 'valid' &&
    $this->licenceStatus !== 'deactivated' &&
    $this->licenceStatus !== 'inactive' &&
    $this->licenceStatus !== 'expired'
  ) {
                return false;
            }

            if ($this->expiryDate) {
                return $this->expiryDate;
            } else {
                return false;
            }
        }

        /**
         * Return Result from  Key VAlidation Process
         *
         * @return null|string
         */
        public function shgdprdm_validate()
        {
            return $this->keyResponse;
        }

        /**
        * Return the current status of the licence, i.e. "valid", "inactive"
        *
        * @return null|string
        */
        public function shgdprdm_getStatus()
        {
            if (!$this->validationRef) {
                return false;
            }
            return $this->validationRef;
        }

        /**
        * Return the licence Type
        * '1' = Standard
        * '2' = Pro
        *
        * @return null|string
        */
        public function shgdprdm_getLicType()
        {
            return $this->licenceType;
        }
    } // end of class
}
