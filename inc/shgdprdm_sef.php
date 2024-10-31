<?php
defined('SHGDPRDM_ABSPATH') or die('You are not authorised to access this file.');
// Search Extra Functions

function shgdprdm_optionSearchExtras()
{
    ?>
    <form method="post" action="/wp-admin/options.php">
      <?php settings_fields('shgdprdm_admin_plugins_settings_group'); ?>
      <?php do_settings_sections('shgdprdm_extra_search'); ?>
    
      <div style="display:inline-block">
        <?php submit_button('Save Settings'); ?>
      </div>
    </form>

    <?php
}



function shgdprdm_optionSearchExtrasView()
{
    ?>
  <h2>Refine Your Search</h2>
  <p>Option for Refining the depth of your Search</p>
  <h4>Refine By:</h4>
  <div id="shgdprdm-search-extra-options-table-container">
  <?php
  $options = shgdprdm_getOptionsGroup('shgdprdm_admin_plugins_settings_group');
    if (defined('SHGDPRDM_SUPPORTED_PLUGINS_OPTIONS')) {
        shgdprdm_searchExtraOptionFields(unserialize(SHGDPRDM_SUPPORTED_PLUGINS_OPTIONS), $options, true);
    } else { ?>
    <p>Additional Search Options Currently Unavailable</p>
  <?php
  } ?>
  </div>
  <?php
}

function shgdprdm_displaySearchExtrasPro()
{
    $disabledOptions = array();
    if (defined('SHGDPRDM_PRO')) {
        $phpV = '';
        if (defined('PHP_VERSION_ID')) {
            $phpV = substr(PHP_VERSION_ID, 0, 1);
        } elseif (function_exists('phpversion')) {
            $phpV = substr(phpversion(), 0, 1);
        } else {
            $phpV = '5';
        }
    
        if ($phpV == '7') {
            $classPath = dirname(__DIR__, 1);
        } else {
            $classPath = realpath(__DIR__ . '/..');
        }
    
        $validateControl = new SHGdprdm_ValidateControl();
    
        foreach (unserialize(SHGDPRDM_PRO) as $proSupport => $proOptions) {
            if (
              file_exists($classPath."/classes/shgdprdm_".$proSupport.".pro.class.php") &&
              $validateControl->shgdprdm_validateVerifyLicence() &&
              $validateControl->shgdprdm_validateHasLicence() &&
              $validateControl->shgdprdm_validateIsProLicence($proSupport)
            ) {
                foreach ($proOptions as $optionName) {
                    $optionsStatus[$optionName] = array();
                }
                // foreach ($proOptions as $optionName) {
                //     $disabledOptions[$optionName] = array('disabled' => 'disabled = disabled', 'cursor' => 'cursor: not-allowed;');
                // }
                // $selectAllDisabled = true;
            } else {
                foreach ($proOptions as $optionName) {
                    $optionsStatus[$optionName] = array('disabled' => 'disabled = disabled', 'cursor' => 'cursor: not-allowed;');
                }
            }
        }
    } // End: If PRO Defined
  
    return $optionsStatus;
}

function shgdprdm_countPros()
{
    $proCount = 0;
    if (defined('SHGDPRDM_PRO')) {
        foreach (unserialize(SHGDPRDM_PRO) as $proSupport => $proOptions) {
            foreach ($proOptions as $optionName) {
                $proCount++;
            }
        }
    } // End: If PRO Defined
    return $proCount;
}

function shgdprdm_countProSettings($proSettings)
{
    $count = 0;
    if (!empty($proSettings)) {
        foreach ($proSettings as $proName => $proVals) {
            if (empty($proVals)) {
                $count++;
            }
        }
    }
    return $count;
}

function shgdprdm_searchExtraOptionFields($supportedOptions, $foundOptions, $display=false)
{
    $optionsDisplay = shgdprdm_displaySearchExtrasPro();
    
    $extraSearchOptions = $foundOptions;
    
    

    $selectAllDisabled = false;
    if (!$display) {
        $style_all = 'top:-55px;left:175px;';
        $style_select = 'top:-40px;';
    } else {
        $style_all = '';
        $style_select = '';
    }
    
    if (!empty($optionsDisplay) || (empty($optionsDisplay) && !empty($extraSearchOptions))) {
        $displaySelectAll = true;
      
        foreach ($optionsDisplay as $oName => $oVals) {
            if (empty($oVals)) {
                // if (empty($oVals) && isset($extraSearchOptions[$oName])) {
                $extraSearchOptions[$oName]['display'] = true;
            } else {
                $extraSearchOptions[$oName] = $oVals;
                $extraSearchOptions[$oName]['display'] = false;
                $displaySelectAll = false;
            }
        }
        
        $otherProAvailable = array(); ?>
        
        <div style="position:relative;<?php echo $style_all; ?>">
        <?php
        if ($displaySelectAll === true) { ?>
          <label for="shgdprdm-search-extra-select-all" style="min-width:55px;width:55px;max-width:55px;font-style:italic;display:inline-block;">Select All</label>
          <input id="shgdprdm-search-extra-select-all" style="margin-top:0px;" type="checkbox" />
          
        <?php
        } else { ?>
          <div style="min-width:55px;width:55px;max-width:55px;font-style:italic;display:inline-block;"></div>
        <?php
        } ?>
      </div>
      
      <div style="margin-left: 10px;position:relative;<?php echo $style_select; ?>">
        <?php
        foreach ($extraSearchOptions as $soIx => $searchOption) {
            $value = isset($searchOption[$soIx]) ? $searchOption[$soIx] : '';
            $checked = isset($searchOption[$soIx]) ? $searchOption[$soIx] : '';
            $label = str_replace('-', ' ', $soIx);
            $disabled = isset($searchOption['disabled']) ? $searchOption['disabled'] : '';
            $cursor = isset($searchOption['cursor']) ? $searchOption['cursor'] : ''; ?>
           
            <input id="shgdprdm_plugins_settings[<?php echo $soIx; ?>]" style="margin-top:0px;<?php echo $cursor; ?>" type="checkbox" name="<?php echo $soIx; ?>[<?php echo $soIx; ?>]" value="<?php echo $value; ?>" <?php checked('checked', $checked, true); ?> <?php echo $disabled ; ?>  />
            <label for="shgdprdm_plugins_settings[<?php echo $soIx; ?>]"><?php echo $label; ?></label>
            <?php
            if (!empty($disabled)) {
                ?>
              <div style="display:inline-block;vertical-align: middle;">
                <a href="<?php echo SHGDPRDM_VALIDATE_DEFAULT_URL; ?>/downloads/gdpr-data-manager/" target="_blank">
                  &nbsp;Add this Plugin Support
                </a>
              </div>
            <?php
            } ?>
            <br>
        <?php
        } ?>
      </div>
    <?php
    }
}

function shgdprdm_getOptionsGroup($optionsGroupName)
{
    global $new_whitelist_options;
    $options = array();
    // array of option names
    if (isset($new_whitelist_options[ $optionsGroupName ])) {
        $option_names = $new_whitelist_options[ $optionsGroupName ];
        foreach ($option_names as $option_name) {
            $options[$option_name] = get_option($option_name);
        }
    }
    return $options;
}

function shgdprdm_displaySearchExtras($proOptionsArr = null)
{
    $optionsDisplay = shgdprdm_displaySearchExtrasPro();

    $extraSearchOptions = shgdprdm_getOptionsGroup('shgdprdm_admin_plugins_settings_group');

    if (!empty($optionsDisplay) || (empty($optionsDisplay) && !empty($extraSearchOptions))) {
        foreach ($optionsDisplay as $oName => $oVals) {
            if (empty($oVals)) {
                // if (empty($oVals) && isset($extraSearchOptions[$oName])) {
                $extraSearchOptions[$oName]['display'] = true;
            } else {
                $extraSearchOptions[$oName]['display'] = false;
            }
        }
        
        $otherProAvailable = array(); ?>
        
        <table id="shgdprdm-extra-search-options-display-table">
        
          <?php
          foreach ($extraSearchOptions as $soIx => $searchOption) {
              $checked = isset($searchOption[$soIx]) ? $searchOption[$soIx] : '';
              $disabled = isset($searchOption['disabled']) ? true : false;
              $displayed = $searchOption['display'];

              if ($checked === 'checked') {
                  $icon = '<span class="dashicons dashicons-yes" style="color:green"></span>';
                  $statusText = '<em>( included in search )</em>';
              } else {
                  $icon = '<span class="dashicons dashicons-no" style="color:red"></span>';
                  $statusText = '<em>( excluded from search )</em>';
              }
              
              if ($displayed === true) {
                  ?>
                <tr>
                  <td><?php echo $icon.' <strong>'.$soIx.'</strong> '; ?></td>
                  <td><?php echo $statusText ?></td>
                </tr>
              <?php
              } else {
                  $otherProAvailable[] = $soIx;
              }
          } ?>
        </table>
        
        <div id="shgdprdm-search-extra-container">
          <?php echo shgdprdm_optionSearchExtras(); ?>
          <!--<hr class="options-divider">-->
        </div>
        
        
        <!-- Button to display the checkboxes & submit button -->
        <div id="edit-shgdprdm-extra-search-options-inline">
          <div id="edit-shgdprdm-extra-search-options-inline-btn" class="button button-primary">Click to Edit</div>
        </div>
        
        <div style="clear:both";><hr class="options-divider"></div>
        
        <?php
        if (!empty($otherProAvailable)) { ?>
            
            <?php
            if (count($otherProAvailable) === count($optionsDisplay)) {
                // There are no valid pro options
                $text = "Upgrade to the PRO Version to also include the following Plugins In Your Search Results, Data Export and Data Delete Actions:";
                $btnText = "Upgrade to PRO Now";
            } else {
                $text = "Other Plugins Supported:";
                $btnText = "Upgrade Now";
            } ?>
            
            <div id="shgdprdm-search-extra-selected-container" style="margin-top:20px;">
              <div class="shgdprdm-upgrade-to-pro-notice">
                <h2><?php echo $text; ?></h2>
                <ul>
                  <?php
                  foreach ($otherProAvailable as $otherPro) { ?>
                      <li class="shgdprdm-pro-support-list"><strong><span class="dashicons dashicons-arrow-right-alt2" style="color:#008ec2"></span><?php echo $otherPro;?></strong></li>
                      <?php
                  } ?>
                </ul>
              </div>
              <a class="button button-primary" href="<?php echo SHGDPRDM_VALIDATE_DEFAULT_URL; ?>/downloads/gdpr-data-manager/" target="_blank">
                <div id="shgdprdm-upgrade-to-pro-container">
                  <div class="shgdprdm-upgrade-to-pro-notice">
                    <?php echo $btnText; ?>
                  </div>
                </div>
              </a>
              <br>
              <div style="clear:both";><hr class="options-divider"></div>
            </div>
            
            <?php
        }
    } else { ?>
        <div>No Additional Search Options Available</div>
        <?php
    }
}
