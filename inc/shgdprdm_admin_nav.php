<?php
defined('SHGDPRDM_ABSPATH') or die('You are not authorised to access this file.');

function shgdprdm_adminNav($active_tab)
{
    ?>
    <h2 class="nav-tab-wrapper">

    <a href="?page=seahorse_gdpr_data_manager_plugin&tab=gdpr_data_manager" class="nav-tab <?php echo $active_tab == 'gdpr_data_manager' || $active_tab == '' ? 'nav-tab-active' : ''; ?>">
        <span class="shgdprdm_icon-xl dashicons dashicons-search"></span>
        <span class="nav-heading"><?php echo SHGDPRDM_LABEL_NAV_SEARCH;?></span>
    </a>

    <a href="?page=seahorse_gdpr_data_manager_plugin&tab=gdpr_data_manager_home" class="nav-tab <?php echo $active_tab == 'gdpr_data_manager_home' ? 'nav-tab-active' : ''; ?>">
        <span class="shgdprdm_icon-xl dashicons dashicons-dashboard"></span>
        <span class="nav-heading"><?php echo SHGDPRDM_LABEL_NAV_HOME;?></span>
    </a>

    <a href="?page=seahorse_gdpr_data_manager_plugin&tab=gdpr_data_manager_privacy_policy" class="nav-tab <?php echo $active_tab == 'gdpr_data_manager_privacy_policy' ? 'nav-tab-active' : ''; ?>">
        <span class="shgdprdm_icon-xl dashicons dashicons-shield"></span>
        <span class="nav-heading"><?php echo SHGDPRDM_LABEL_NAV_PRIVACY;?></span>
    </a>
    
    <!--<a href="?page=seahorse_gdpr_data_manager_plugin&tab=gdpr_data_manager_search_options" class="nav-tab <?php // echo $active_tab == 'gdpr_data_manager_search_options' ? 'nav-tab-active' : '';?>">-->
    <!--  <span class="shgdprdm_icon-xl dashicons dashicons-admin-generic"></span>-->
    <!--  <span class="nav-heading"><?php // echo SHGDPRDM_LABEL_NAV_OPTIONS;?></span>-->
    <!--</a>-->

    <a href="?page=seahorse_gdpr_data_manager_plugin&tab=gdpr_data_manager_pending_actions" class="nav-tab <?php echo $active_tab == 'gdpr_data_manager_pending_actions' ? 'nav-tab-active' : ''; ?>">
        <span class="shgdprdm_icon-xl dashicons dashicons-flag"></span>
        <span class="nav-heading"><?php echo SHGDPRDM_LABEL_NAV_PENDING;?></span>
    </a>

    <a href="?page=seahorse_gdpr_data_manager_plugin&tab=gdpr_data_manager_review_history" class="nav-tab <?php echo $active_tab == 'gdpr_data_manager_review_history' ? 'nav-tab-active' : ''; ?>">
        <span class="shgdprdm_icon-xl dashicons dashicons-list-view"></span>
        <span class="nav-heading"><?php echo SHGDPRDM_LABEL_NAV_REVIEW;?></span>
    </a>

    <a href="?page=seahorse_gdpr_data_manager_plugin&tab=gdpr_data_manager_help" class="nav-tab <?php echo $active_tab == 'gdpr_data_manager_help' ? 'nav-tab-active' : ''; ?>">
        <span class="shgdprdm_icon-xl dashicons dashicons-editor-help"></span>
        <span class="nav-heading"><?php echo SHGDPRDM_LABEL_NAV_HELP;?></span>
    </a>

    <a href="?page=seahorse_gdpr_data_manager_plugin&tab=gdpr_data_manager_register_licence" class="nav-tab <?php echo $active_tab == 'gdpr_data_manager_register_licence' ? 'nav-tab-active' : ''; ?>">
        <span class="shgdprdm_icon-xl dashicons dashicons-admin-network"></span>
        <span class="nav-heading"><?php echo SHGDPRDM_LABEL_NAV_REGISTER;?></span>
    </a>

    </h2>
    <?php
}


function shgdprdm_adminNavCheckStatus()
{
    // If Both Options Are Not Set
    if (false === get_option('shgdprdm_adminHasLicence') || false === get_option('shgdprdm_adminVerifyLicence')) {
        return false;
    }
    // If the sub-option of "Has Licence" is not Set
    if (false !== get_option('shgdprdm_adminHasLicence') && !isset(get_option('shgdprdm_adminHasLicence')['licence_number'])) {
        return false;
    }
    // If the sub-options of "Validate Licence" are not Set
    if (false !== get_option('shgdprdm_adminVerifyLicence') && !isset(get_option('shgdprdm_adminVerifyLicence')['licence_number'])   && !isset(get_option('shgdprdm_adminVerifyLicence')['licence_valid'])) {
        return false;
    }
    // If the Licence Numbers of both options do not match
    if (get_option('shgdprdm_adminHasLicence')['licence_number'] != get_option('shgdprdm_adminVerifyLicence')['licence_number']) {
        return false;
    }
    // If "Has Licence" option is set & sub-option is set BUT sub-option is empty/false
    if (false !== get_option('shgdprdm_adminHasLicence') &&
        isset(get_option('shgdprdm_adminHasLicence')['licence_number']) &&
        (
            false === get_option('shgdprdm_adminHasLicence')['licence_number'] ||
            get_option('shgdprdm_adminHasLicence')['licence_number'] == ''
        )
    ) {
        return false;
    }
    // If "Verify  Licence" option is set & sub-option "licence number" is set BUT sub-option is empty/false
    if (false !== get_option('shgdprdm_adminVerifyLicence') &&
        isset(get_option('shgdprdm_adminVerifyLicence')['licence_number']) &&
        (
            false === get_option('shgdprdm_adminVerifyLicence')['licence_number'] ||
            get_option('shgdprdm_adminVerifyLicence')['licence_number'] == ''
        )
    ) {
        return false;
    }
    // If "Verify  Licence" option is set & sub-option "licence valid" is set BUT sub-option is empty/false
    if (false !== get_option('shgdprdm_adminVerifyLicence') &&
        isset(get_option('shgdprdm_adminVerifyLicence')['licence_valid']) &&
        (
            false === get_option('shgdprdm_adminVerifyLicence')['licence_valid'] ||
            get_option('shgdprdm_adminVerifyLicence')['licence_valid'] == ''
        )
    ) {
        return false;
    }
    return true;
}

function shgdprdm_makeNoLicenceNoticeOpenTags()
{
    ?>
    <div>
        <div><hr></div>

        <div id='shgdprdm-disabled-btn-group'>
    <?php
}

function shgdprdm_makeNoLicenceNoticeCloseTags()
{
    ?>
            <p style='clear:both'></p>
        </div>
    </div>
    <?php
}

function shgdprdm_makeNoLicenceNotice()
{
    ?>

    <p style="clear:both"></p>

    <div id="shgdprdm-verify-action-data-disabled-btn-notice">

        <div>
            <h1 id="shgdprdm-add-licence-notice-main-text">Discover the full set of tools that allows you to manage the most common and important customer data issues raised by GDPR</h1>
            <p id="shgdprdm-add-licence-notice-sub-text">Self Actionable Requests / Action Logging / Off-site Backups / Pending Actions / Activity History</p>

            <a href="<?php echo SHGDPRDM_VALIDATE_DEFAULT_URL;?>/downloads/gdpr-data-manager/" target="_blank">
            <div id="shgdprdm-add-licence-notice-inner-container">
                <div class="shgdprdm-add-licence-notice-icon">
                <span class="dashicons dashicons-plus"></span>
                </div>
                <div class="shgdprdm-add-licence-notice-notice">
                <?php echo SHGDPRDM_war_008; ?>
                <br><br><strong>Purchase Licence Key</strong>
                </div>
            </div>
            </a>

        </div>
    </div>
    <?php
}

function shgdprdm_makeNoLicenceActionSection($sectionName, $reference = null)
{
    if ($sectionName == 'sh_general') {
        return shgdprdm_makeNoLicenceActionSectionSeahorse();
    } elseif ($sectionName == 'sh_actionBtns') {
        return shgdprdm_makeNoLicenceActionSectionActionButtons($reference);
    // } else if ($sectionName == 'sh_options') {
    //   return shgdprdm_makeNoLicenceActionSectionOptionsView();
    } else {
        return "FAIL";
    }
}

function shgdprdm_makeNoLicenceActionSectionSeahorse()
{
    ?>
    <div id="shgdprdm-verify-action-data-disabled-btn-group">

        <div id="postbox-container-1" class="postbox-container mdv-profile-container">
            <div class="meta-box-sortables">

                <div class="postbox">

                    <h2 class="shgdprdm-disabled-general-title">
                        <span class="shgdprdm-disabled-general-title-text">
                            <?php
                            esc_attr_e(
                                'Seahorse - GDPR Data Manager',
                                'WpAdminStyle'
                            );
                            ?>
                        </span>
                        <span class="shgdprdm-disabled-general-title-img">
                            <img src="<?php echo plugins_url('assets/images/shgdprdm_logo-icon.png', dirname(__FILE__));?>" alt="Seahorse Logo"/>
                        </span>
                    </h2>

                    <div class="inside">
                        <p class="shgdprdm-center"><?php echo SHGDPRDM_Seahorse_Profile_Text;?></p>
                    </div>
                    <!-- .inside -->
                    <a href="<?php echo SHGDPRDM_VALIDATE_DEFAULT_URL;?>/downloads/gdpr-data-manager/" target="_blank">
                        <div id="shgdprdm-add-licence-notice-inner-container">
                            <div class="shgdprdm-add-licence-notice-notice">
                                <?php
                                $noticeText = esc_html__(
                                    'Please Purchase a Licence here to make this feature available',
                                    'seahorse-gdpr-data-manager'
                                );
                                echo $noticeText;
                                ?> 
                            </div>
                        </div>
                    </a>

                </div>
                <!-- .postbox -->

            </div>
            <!-- .meta-box-sortables -->

        </div>

        <p style="clear:both"></p>

    </div>

    <?php
}

function shgdprdm_makeNoLicenceActionSectionActionButtons($ref)
{
    if ($ref) {
        $detailsID = $ref->ID;
    } else {
        $detailsID = 'xxx';
    }
    ?>
    <div id="shgdprdm-verify-action-data-disabled-btn-group">

        <div id="shgdprdm-verify-export-data-btn-container">
        <div id="shgdprdm_verify_export" class="button button-primary" title="Verify Data Export for ID <?php echo $detailsID; ?>" disabled>Verify Export</div>
        </div>

        <div id="shgdprdm-verify-delete-data-btn-container">
        <div id="shgdprdm_verify_delete" class="button button-primary shgdprdm_verify_delete" title="Verify Data Deletion for ID <?php echo $detailsID; ?>" disabled>Verify Delete</div>
        </div>

        <a href="<?php echo SHGDPRDM_VALIDATE_DEFAULT_URL; ?>/downloads/gdpr-data-manager/" target="_blank">
            <div id="shgdprdm-add-licence-notice-inner-container">
                <div class="shgdprdm-add-licence-notice-notice">
                    <?php
                    $noticeText = esc_html__(
                        'Please Purchase a Licence here to make these features available',
                        'seahorse-gdpr-data-manager'
                    );
                    echo $noticeText;
                    ?> 
                </div>
            </div>
        </a>

        <p style="clear:both"></p>

    </div>

    <?php
}


function shgdprdm_makeNoLicenceActionSectionOptionsView()
{
    ?>

    <div id="shgdprdm-verify-action-data-disabled-btn-group" class="shgdprdm-disabled-options-view">

        <div id="shgdprdm-search-options-container">
            <div id="shgdprdm-search-by-container">
                <?php echo shgdprdm_optionSearchByView(); ?>
            </div>

            <?php
            if (shgdprdm_getOptionsGroup('shgdprdm_admin_plugins_settings_group')) { ?>
                <div id="shgdprdm-search-extra-container">
                    <?php echo shgdprdm_optionSearchExtrasView(); ?>
                </div>
                <?php
            }
            ?>
        </div>

        <div id="shgdprdm-text-edits-container">
            <div id="shgdprdm-text-option-container">
                <?php echo shgdprdm_optionReplacementTextView(); ?>
            </div>
            <div id="shgdprdm-tandc-option-container">
                <?php echo shgdprdm_optionTandcLinkView(); ?>
            </div>
        </div>

        <div id="shgdprdm-link-edits-container">
            <div id="shgdprdm-ppolicy-option-container">
                <?php echo shgdprdm_optionPpolicyLinkView(); ?>
            </div>
            <div id="shgdprdm-user-email-body-container">
                <?php echo shgdprdm_optionUserEmailBodyView(); ?>
            </div>
        </div>

        <div id="shgdprdm-user-page-preview-container">
            <?php echo shgdprdm_optionUserPagePreviewView(); ?>
        </div>
    </div>
    <?php
}

function shgdprdm_optionUserEmailBodyView()
{
    ?>
    <h2>
        <?php
        $noticeText = esc_html__(
            'CUSTOMER EMAIL',
            'seahorse-gdpr-data-manager'
        );
        echo $noticeText;
        ?>
    </h2>
    <p>
        <strong>
            <?php
            $noticeText = esc_html__(
                'Subject',
                'seahorse-gdpr-data-manager'
            );
            echo $noticeText;
            ?>
        : </strong>
        <?php echo SHGDPRDM_EXPORT_MAIL_SUBJECT; ?>
    </p>
    <p>
        <?php echo SHGDPRDM_EXPORT_MAIL_BODY_1;?> <em>
        <a href='#'>
            <?php
            $text = esc_html__(
                'Verify Export/Delete',
                'seahorse-gdpr-data-manager'
            );
            echo $text;
            ?>
        </a></em>
    </p>
    <p><?php echo SHGDPRDM_DELETE_EXPORT_MAIL_BODY_2;?></p>
    <?php
}

function shgdprdm_optionUserPagePreviewView()
{
    ?>
    <h2>
        <?php
        $text = esc_html__(
            'User Delete/Export Page View',
            'seahorse-gdpr-data-manager'
        );
        echo $text;
        ?>
    </h2>

    <p>
        <?php
        $text = sprintf(
            esc_html__(
                'Updating the %sRight to Data%s section on the left will be reflected in this preview. This is the user page for Right to Forget / Portability requests. The %sRight to Data%s section is replicated on this page as well as your Privacy Policy page. You can also edit your Privacy Policy Page URL.',
                'seahorse-gdpr-data-manager'
            ),
            '<b>',
            '</b>',
            '<b>',
            '</b>'
        );
        echo $text;
        ?>
    </p>
    <div class="shgpdrdm-row">
        <div class="shgpdrdm-center-block shgdprdm-ext-frame shgdprdm-ext-frame-preview">
            <?php echo shgdprdm_siteLogo();?>
            <div id="shgdprdm_download_first_notice" class="shgdprdm-usrmsg-container shgdprdm-usrmsg-warning">
                <div>
                    <span class="shgdprdm_icon-xl dashicons dashicons-warning"></span>
                </div>
                <div class="shgdprdm-usrmsg-notice">
                    <?php
                    $noticeText = esc_html__(
                        'Data must be downloaded prior to Deleting.',
                        'seahorse-gdpr-data-manager'
                    );
                    echo $noticeText;
                    ?>
                </div>
            
            </div>
            <p id="shgdprdm-user-notice">
                <?php
                $text = sprintf(
                    esc_html__(
                        'Data Export / Delete Request from %s',
                        'seahorse-gdpr-data-manager'
                    ),
                    get_bloginfo("name")
                );
                echo $text;
                ?>
            </p>
        
            <?php
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
            
                echo ' <div id="shgdprdm_download_first_notice" class="shgdprdm-usrmsg-container shgdprdm-usrmsg-warning">
                    <div>
                        <span class="shgdprdm_icon-xl dashicons dashicons-warning"></span>
                    </div>
                    <div class="shgdprdm-usrmsg-notice">
                        <p style="padding:1px;">'.$WarningText.'</p>
                    </div>  
                </div>';
            }
            ?>
        
            <div id="shgdprdm-download-data-btn-group">
                <table class="wp-list-table widefat fixed striped posts">
                <!-- <table class="shgdprdm-table shgdprdm-table-striped shgdprdm-table-responsive shgdprdm-table-condensed"> -->
                    <tbody>
                    <tr>
                    <th><?php echo esc_html__('First Name', 'seahorse-gdpr-data-manager'); ?></th>
                        <td><em><?php echo esc_html__('Example', 'seahorse-gdpr-data-manager'); ?>:</em> John/Mary</td>
                    </tr>
                    <tr>
                    <th><?php echo esc_html__('Last Name', 'seahorse-gdpr-data-manager'); ?></th>
                    <td><em><?php echo esc_html__('Example', 'seahorse-gdpr-data-manager'); ?>:</em> Smith/Jones</td>
                    </tr>
                        <tr>
                    <th><?php echo esc_html__('Email', 'seahorse-gdpr-data-manager'); ?></th>
                    <td><em><?php echo esc_html__('Example', 'seahorse-gdpr-data-manager'); ?>:</em> customer@email.com</td>
                    </tr>
                        <tr>
                            <th><?php echo esc_html__('Login Name', 'seahorse-gdpr-data-manager'); ?></th>
                            <td><em><?php echo esc_html__('Example', 'seahorse-gdpr-data-manager'); ?>:</em> CustomerOne</td>
                        </tr>
                        <tr>
                            <th><?php echo esc_html__('Registration Date', 'seahorse-gdpr-data-manager'); ?></th>
                            <td><em><?php echo esc_html__('Example', 'seahorse-gdpr-data-manager'); ?>:</em> 1st January 2000</td>
                        </tr>
                    </tbody>
                </table>
            
                <?php
                if (false !== get_option('shgdprdm_ppolicy_options') && !empty(get_option('shgdprdm_ppolicy_options')['ppolicy_option']) && get_option('shgdprdm_ppolicy_options')['ppolicy_option'] != '') {
                    $ppolicyLink = get_option('shgdprdm_ppolicy_options')['ppolicy_option'];
                } else {
                    $ppolicyLink = SHGDPRDM_DEFAULT_PRIVACY_POLICY_LINK;
                }
                if (false !== get_option('shgdprdm_tandc_options') && !empty(get_option('shgdprdm_tandc_options')['tandc_option']) && get_option('shgdprdm_tandc_options')['tandc_option'] != '') {
                    $tandcText = get_option('shgdprdm_tandc_options')['tandc_option'];
                } else {
                    $tandcText = SHGDPRDM_DEFAULT_TERMS_CONDITIONS_TEXT;
                }
                ?>

                <p></p>
            
                <?php
                $privacyPolicyText = sprintf(
                    esc_html__(
                        'You can view our full %s Privacy Policy Here%s',
                        'seahorse-gdpr-data-manager'
                    ),
                    '<a href="' . get_bloginfo('url') . '/' . $ppolicyLink . '" target="_blank">',
                    '</a>'
                );
                ?>
                <p><?php echo $privacyPolicyText;?></p>
                <p></p>
                <p><?php echo $tandcText;?></p>
                <p></p>
                <?php echo shgdprdm_makeUserActionButtonsView();?>
            </div>
            
        </div>
    </div>
    <div class="clear"></div>
    <?php
}

function shgdprdm_makeUserActionButtonsView()
{
    ?>
    <div id="shgdprdm-export-btn-group">
        <div id="shgdprdm-verify-export-data-xml-btn-container">
            <?php
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
            ?>
            <div id="shgdprdm_export_xml_view" class="button button-primary" title="<?php echo $btnHoverText;?>" disabled><?php echo $btnText;?></div>
        </div>

        <div id="shgdprdm-verify-export-data-csv-btn-container">
            <?php
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
            ?>
            <div id="shgdprdm_export_csv_view" class="button button-primary" title="<?php echo $btnHoverText;?>" disabled><?php echo $btnText;?></div>
        </div>

        <div id="shgdprdm-verify-export-data-json-btn-container">
            <?php
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
            ?>
            <div id="shgdprdm_export_json_view" class="button button-primary" title="<?php echo $btnHoverText;?>" disabled><?php echo $btnText;?></div>
        </div>

        <div id="shgdprdm-verify-delete-data-btn-container">
            <?php
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
                'User'
            );
            ?>
            <div id="shgdprdm_delete_user_view" class="button button-primary shgdprdm_verify_delete" title="<?php echo $btnHoverText;?>" disabled><?php echo $btnText;?></div>
        </div>

        <p style="clear:both"></p>
    </div>
    <?php
}


if (!function_exists('shgdprdm_siteLogo')) {
    function shgdprdm_siteLogo()
    {
        $custom_logo_id =  (null !== get_theme_mod('custom_logo')) ? get_theme_mod('custom_logo') : '';
        if ($custom_logo_id) {
            $logo = wp_get_attachment_image_src($custom_logo_id, 'full');
            if (has_custom_logo()) {
                return "<p><img src='". esc_url($logo[0]) ."' alt='".get_bloginfo('name')." Logo'/></p>";
            } else {
                return '<h1>'. get_bloginfo('name') .'</h1>';
            }
        }

        $header_logo = (null !== get_theme_mod('header_logo_url')) ? get_theme_mod('header_logo_url') : '';
        if ($header_logo) {
            return  "<p><img src='".esc_url(wp_sprintf($header_logo, get_stylesheet_directory_uri()))."' alt='".get_bloginfo('name')." Logo'/></p>";
        } else {
            return '<h1>'. get_bloginfo('name') .'</h1>';
        }
    }
}
