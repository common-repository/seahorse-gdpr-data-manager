<?php
defined('SHGDPRDM_ABSPATH') or die('You are not authorised to access this file.');

require_once 'shgdprdm_userVerify.class.php';
if (!class_exists('SHGdprdm_UTMP')) {
    class SHGdprdm_UTMP extends SHGdprdm_VFY
    {
        public function __construct()
        {
            // parent::__construct(func_get_args()[0]);
            $params= func_get_args();
            $param = $params[0];
            parent::__construct($param);
            add_action('wp_enqueue_scripts', array($this, 'shgdprdm_enqueue_user_assets'));
        }


        public function shgdprdm_enqueue_user_assets()
        {
            wp_enqueue_style('shgdprdm_user_style', $this->shgdprdm_user_style);
            wp_enqueue_style('dashicons');
            wp_enqueue_script('script-name', $this->shgdprdm_user_script, array(), '1.0.0', true);
            if (! wp_script_is('jquery', 'enqueued')) {
                //Enqueue
                wp_enqueue_script('jquery');
            }
        }

        public function shgdprdm_makeUserTemplate()
        {
            $html = '';
            $html .= self::shgdprdm_makeUserTemplateOpen();
            if ($this->oData && ($this->requestStatus == 8 || $this->requestStatus == 9  || $this->requestStatus == 106 || $this->requestStatus == 10 || $this->requestStatus == 11 || $this->requestStatus == 107)) {
                $html .= self::shgdprdm_makeConfirmationTemplate();
            } elseif ($this->oData) {
                $html .= self::shgdprdm_makeActionTemplate();
            } else {
                $html .= self::shgdprdm_makeFailedTemplate();
                // $html .= "<br>O Data: ".$this->oData;
                    // $html .= "<br>Request Status: ".$this->requestStatus;
            }
            $html .= self::shgdprdm_makeUserTemplateClose();

            return $html;
        }

        private function shgdprdm_makeUserTemplateOpen()
        {
            $title = esc_html(strip_tags(get_bloginfo('name')));
            $admin_title = get_bloginfo('name');

            $html = '';
            $html .= '<html><head><title>'.$admin_title.'</title>';
            $html .= '<meta name="viewport" content="width=device-width,initial-scale=1.0">';
            $html .= wp_head();

            $html .= '</head>

								<body class="login login-action-login wp-core-ui locale-en-gb shgdprdm-usr-page">

									<div class="shgpdrdm-row">
										<div class="shgpdrdm-center-block shgdprdm-ext-frame">';

            $html .= shgdprdm_siteLogo();

            return $html;
        }

        private function shgdprdm_makeUserTemplateClose()
        {
            $html = '';
            $html .= '<div class="clear"></div>';

            $html .= wp_footer();
            $html .= '
				</body>
				</html>';
            return $html;
        }

        private function shgdprdm_makeFailedTemplate()
        {
            $textOption = get_option('shgdprdm_user_msg');

            $html = '';

            if ($textOption) {
                $noticeText = $textOption['msg'];
                $noticeClass = $textOption['class'];
            } else {
                $noticeText = esc_html__(
                    'Your access link has expired.',
                    'seahorse-gdpr-data-manager'
                );
                $noticeClass = 'warning';
            }
            $html .= '<div class="shgdprdm-usrmsg-container shgdprdm-usrmsg-'.$noticeClass.'">
									<div><span class="shgdprdm_icon-xl dashicons dashicons-warning"></span></div>
									<div class="shgdprdm-usrmsg-notice">';
            $html .= $noticeText;
            $html .= '</div></div>';

            $html .= '<p>Please contact '.get_bloginfo("name").' to request a new link.</p>';
            $html .= '<p>';
            $html .= sprintf(
                esc_html__(
                    'Please contact %s to request a new link.',
                    'seahorse-gdpr-data-manager'
                ),
                get_bloginfo("name")
            );
            $html .= '</p>';

            $html .= '<br>';
            $html .= '<div class="shgdprdm-user-view-failed-btn-container">';
            if (get_bloginfo('admin_email')) {
                $linkText = esc_html__(
                    'Contact',
                    'seahorse-gdpr-data-manager'
                );
                $html .= '<div class="shgdprdm-user-view-failed-btn"><a class="shgdprdm-button shgdprdm-usr-btn shgdprdm-usr-btn-regular" href="mailto:'.get_bloginfo("admin_email").'">' . $linkText . ' ' . get_bloginfo("name").'</a></div>';
            }
            $linkText = esc_html__(
                'Return to',
                'seahorse-gdpr-data-manager'
            );
            $html .= '<div class="shgdprdm-user-view-failed-btn"><a class="shgdprdm-button shgdprdm-usr-btn shgdprdm-usr-btn-regular" href="'.get_home_url().'">' . $linkText . ' ' . get_bloginfo("name").'</a></div>';
            $html .= '</div>';

            return $html;
        }

        private function shgdprdm_makeConfirmationTemplate()
        {
            $noticeText = '<p><strong>';
            $noticeText .= sprintf(
                esc_html__(
                    'Your Request for Account at %s to be Deleted has been processed.',
                    'seahorse-gdpr-data-manager'
                ),
                get_bloginfo("name")
            );
            $noticeText .= '</strong></p><p>';
            $noticeText .= esc_html__(
                'A confirmation email has been sent.',
                'seahorse-gdpr-data-manager'
            );
            $noticeText .= '</p>';
            $noticeClass = 'success';

            $html = '';

            $html .= '<div class="shgdprdm-usrmsg-container shgdprdm-usrmsg-'.$noticeClass.'">
									<div><span class="shgdprdm_icon-xl dashicons dashicons-yes"></span></div>
									<div class="shgdprdm-usrmsg-notice">';
            $html .= $noticeText;
            $html .= '</div></div>';

            $html .= '<br>';
            $html .= '<div class="shgdprdm-user-view-failed-btn-container">';
            if (get_bloginfo('admin_email')) {
                $linkText = esc_html__(
                    'Contact',
                    'seahorse-gdpr-data-manager'
                );
                $html .= '<div class="shgdprdm-user-view-failed-btn"><a class="shgdprdm-button shgdprdm-usr-btn shgdprdm-usr-btn-regular" href="mailto:'.get_bloginfo("admin_email").'">' . $linkText . ' '. get_bloginfo("name").'</a></div>';
            }
            $linkText = esc_html__(
                'Return to',
                'seahorse-gdpr-data-manager'
            );
            $html .= '<div class="shgdprdm-user-view-failed-btn"><a class="shgdprdm-button shgdprdm-usr-btn shgdprdm-usr-btn-regular" href="'.get_home_url().'">' . $linkText . ' '. get_bloginfo("name").'</a></div>';
            $html .= '</div>';

            return $html;
        }

        private function shgdprdm_makeActionTemplate()
        {
            // Error Handle if all variable for display are not available
            if (!isset($this->all_meta_for_user["first_name"]) ||
                    !isset($this->all_meta_for_user["last_name"]) ||
                    empty($this->ue) ||
                    empty($this->user->user_login) ||
                    empty($this->user->user_registered)
            ) {
                $errorMsg = 'Ooops! Something has gone wrong. Ref: ETC_001';
                if (get_bloginfo('admin_email')) {
                    $linkText = esc_html__(
                        'Contact',
                        'seahorse-gdpr-data-manager'
                    );
                    $errorMsg .= '<div class="shgdprdm-user-view-failed-btn"><a class="shgdprdm-button shgdprdm-usr-btn shgdprdm-usr-btn-regular" href="mailto:'.get_bloginfo("admin_email").'">' . $linkText . ' '. get_bloginfo("name").'</a></div>';
                }
                $linkText = esc_html__(
                    'Return to',
                    'seahorse-gdpr-data-manager'
                );
                $errorMsg .= '<div class="shgdprdm-user-view-failed-btn"><a class="shgdprdm-button shgdprdm-usr-btn shgdprdm-usr-btn-regular" href="'.get_home_url().'">' . $linkText . ' '. get_bloginfo("name").'</a></div>';
                wp_die($errorMsg);
            }
            $params = "?ra=".$this->ra_url."&at=".$this->at_url."&ue=".$this->ue_url;

            $html = '';

            if (($this->disasterSync != 1) && (in_array(4, $this->accArr))) {
                $noticeText = esc_html__(
                    'Data must be downloaded prior to Deleting.',
                    'seahorse-gdpr-data-manager'
                );
                $noticeClass = 'warning';
                $html .= '<div id="shgdprdm_download_first_notice" class="shgdprdm-usrmsg-container shgdprdm-usrmsg-'.$noticeClass.'">
										<div><span class="shgdprdm_icon-xl dashicons dashicons-warning"></span></div>
										<div class="shgdprdm-usrmsg-notice">';
                $html .= $noticeText;
                $html .= '</div></div>';
            }
            
            

            if ($this->disasterSync == 1) {
                $html .= '<p id="shgdprdm-usrmsg-error">Admin Sync Action: '.$this->oData.' from '.get_bloginfo("name").'</p>';
            } else {
                $linkText = esc_html__(
                    'Request from',
                    'seahorse-gdpr-data-manager'
                );
                $html .= '<p id="shgdprdm-user-notice">'.$this->oData.' ' . $linkText . ' '.get_bloginfo("name").'</p>';
            }
            
            
            
            /// added to alert users to data deletion with orders pending
            if (($this->disasterSync != 1) && (in_array(4, $this->accArr))) {
                $proSupportType = '';
                if (defined('SHGDPRDM_PRO')) {
                    $validateControl = new SHGdprdm_ValidateControl();
                
                    if ($validateControl->shgdprdm_validateVerifyLicence() && $validateControl->shgdprdm_validateHasLicence()) {
                        foreach (unserialize(SHGDPRDM_PRO) as $proSupport => $proOptions) {
                            if ($validateControl->shgdprdm_validateIsProLicence($proSupport)) {
                                $proSupportType = $proSupport;
                            }
                        }
                    }
                }
            
                if ($proSupportType == 'wcf' || $proSupportType == 'eddf') {
                    $WarningText = sprintf(
                        esc_html__(
                            'Warning: If you have in-complete orders pending and execute this action %s will be unable to fulfil your order',
                            'seahorse-gdpr-data-manager'
                        ),
                        get_bloginfo('name')
                    );
              
                    $html .= '<div id="shgdprdm_download_first_notice" class="shgdprdm-usrmsg-container shgdprdm-usrmsg-warning">
		        <div>
		          <span class="shgdprdm_icon-xl dashicons dashicons-warning"></span>
		        </div>
		             <div class="shgdprdm-usrmsg-notice">
		          <span style="padding:1px; margin-top: 0px;">'.$WarningText.'</span>
		        </div>
		      </div>';
                }
            }

            $html .= '<div id="shgdprdm-download-data-btn-group">';
            $html .= '<table class="shgdprdm-table shgdprdm-table-striped shgdprdm-table-responsive shgdprdm-table-condensed">

									<tbody>
						        <tr>
						          <th>'.esc_html__('First Name', 'seahorse-gdpr-data-manager').'</th>
							            <td>'.$this->all_meta_for_user["first_name"][0].'</td>
						        </tr>
						        <tr>
						          <th>'.esc_html__('Last Name', 'seahorse-gdpr-data-manager').'</th>
						            <td>'.$this->all_meta_for_user["last_name"][0].'</td>
						        </tr>
										<tr>
						          <th>'.esc_html__('Email', 'seahorse-gdpr-data-manager').'</th>
						          	<td>'.$this->ue.'</td>
						        </tr>
										<tr>
											<th>'.esc_html__('Login Name', 'seahorse-gdpr-data-manager').'</th>
												<td>'.$this->user->user_login.'</td>
										</tr>
										<tr>
											<th>'.esc_html__('Registration Date', 'seahorse-gdpr-data-manager').'</th>
												<td>'.$this->user->user_registered.'</td>
										</tr>
								</tbody>
							</table>';

            if ($this->disasterSync == 1) {
                $html .= '<p>';
                $html .= esc_html__(
                    'You are about to sync this delete action on your local database.',
                    'seahorse-gdpr-data-manager'
                );
                $html .= '</p>';

                $html .= '<p>';
                $html .= esc_html__(
                    'This action cannot be undone',
                    'seahorse-gdpr-data-manager'
                );
                $html .= '</p>';
            } else {
                $html .= '<p>';
                $html .= sprintf(
                    esc_html__(
                        'You can view our full %s Privacy Policy Here%s',
                        'seahorse-gdpr-data-manager'
                    ),
                    '<a href="'.$this->pp_text.'" target="_blank">',
                    '</a>'
                );
                $html .= '</p>';

                $html .= '<p>'.$this->tandc_text.'</p>';

                $html .= '<p></p>';
            }
            $html .= $this->shgdprdm_makeUserActionButtons($params);
            $html .= '</div>
				</div>
			</div>';

            return $html;
        }


        private function shgdprdm_makeUserActionButtons($params)
        {
            $rewritePrefix = '';
            if (strpos(get_option('permalink_structure'), 'index.php') > -1) {
                $rewritePrefix = '/index.php';
            }
            $html = '';
            $html .= '<form id="shgdprdm_dl_group" method="post" action="'.$rewritePrefix.'/gdpr-data-manager/verify/'.$params.'"
						<div id="shgdprdm-export-btn-group">';
            $html .= wp_nonce_field('/gdpr-data-manager/verify/', 'shgdprdmexv_nonce');
            $html .= '<input type="hidden" id="shgdprdm_user_email" name ="shgdprdm_user_email" value="'.$this->ue.'"/>
							<input type="hidden" id="shgdprdm_exptd" name ="shgdprdm_exptd" value="'.$params.'"/>';

            $acVal = 1;
            if ((in_array($acVal, $this->accArr)) && ($this->disasterSync == 0)) {
                $btnText = sprintf(
                    esc_html__(
                        'Download %s',
                        'seahorse-gdpr-data-manager'
                    ),
                    'XML'
                );
                $btnHoverText = sprintf(
                    esc_html__(
                        'Download Format: %s',
                        'seahorse-gdpr-data-manager'
                    ),
                    'XML'
                );
                $html .= '<input type="submit" name="shgdprdm_export_xml" id="shgdprdm_export_xml" class="shgdprdm-button shgdprdm-usr-btn shgdprdm-usr-btn-regular" value="' . $btnText . '" title="' . $btnHoverText . '"  /> ';
            }

            $acVal = 2;
            if ((in_array($acVal, $this->accArr)) && ($this->disasterSync == 0)) {
                $btnText = sprintf(
                    esc_html__(
                        'Download %s',
                        'seahorse-gdpr-data-manager'
                    ),
                    'CSV'
                );
                $btnHoverText = sprintf(
                    esc_html__(
                        'Download Format: %s',
                        'seahorse-gdpr-data-manager'
                    ),
                    'CSV'
                );
                $html .= '<input type="submit" name="shgdprdm_export_csv" id="shgdprdm_export_csv" class="shgdprdm-button shgdprdm-usr-btn shgdprdm-usr-btn-regular" value="' . $btnText . '" title="' . $btnHoverText . '"  /> ';
            }

            $acVal = 3;
            if ((in_array($acVal, $this->accArr)) && ($this->disasterSync == 0)) {
                $btnText = sprintf(
                    esc_html__(
                        'Download %s',
                        'seahorse-gdpr-data-manager'
                    ),
                    'JSON'
                );
                $btnHoverText = sprintf(
                    esc_html__(
                        'Download Format: %s',
                        'seahorse-gdpr-data-manager'
                    ),
                    'JSON'
                );
                $html .= '<input type="submit" name="shgdprdm_export_json" id="shgdprdm_export_json" class="shgdprdm-button shgdprdm-usr-btn shgdprdm-usr-btn-regular" value=' . $btnText . '" title="' . $btnHoverText . '"  /> ';
            }

            $acVal = 4;
            if (in_array($acVal, $this->accArr)) {
                if ($this->disasterSync == 1) {
                    $disabled = '';
                    $styleClass = 'shgdprdm_delete_user';
                } else {
                    $disabled = 'disabled ="disabled"';
                    $styleClass = '';
                }
                $btnText = esc_html__(
                    'Delete All Data',
                    'seahorse-gdpr-data-manager'
                );
                $btnHoverText = sprintf(
                    esc_html__(
                        'Download %s Delete Data for Email %s',
                        'seahorse-gdpr-data-manager'
                    ),
                    '&amp;',
                    $this->ue
                );
                $html .= '<input type="submit" name="shgdprdm_delete_user" id="shgdprdm_delete_user" class="shgdprdm-button shgdprdm-usr-btn shgdprdm-usr-btn-delete '.$styleClass.'" value="' . $btnText . '" '.$disabled.' title="' . $btnHoverText . '"  /> ';
            }
            $html .= '<p style="clear:both"></p>';


            $html .= '</div>

            </form>';

            return $html;
        }
    } // end of class
} // end of if class exists
