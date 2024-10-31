<?php
defined('SHGDPRDM_ABSPATH') or die('You are not authorised to access this file.');
/* Admin Search page for plugin */
function shgdprdm_searchPage()
{
    $searchBy = shgdprdm_getSearchOption();
    $proSettings = shgdprdm_displaySearchExtrasPro();
    $proSettingsCount = shgdprdm_countProSettings($proSettings);
    // print_r($proSettings);?>
  <div>
    <?php
    if (shgdprdm_getUserNotice()) {
        echo shgdprdm_getUserNotice();
    } ?>
    <div id="shgdprdm-search-by-header-container" style="width:470px;">
      <div style="margin-top:20px;float:left;margin-right:50px;">
        <h4 style="display:inline-block;">Search By: </h4>
        <h2 style="display:inline-block;"><?php echo $searchBy[0]; ?></h2>
      </div>
         
      <!-- Addition for in-line editing of Additional Searches June 2019 -->
      <!-- Button to display the checkboxes & submit button -->
      <div id="edit-shgdprdm-search-by-options-inline" style="margin-top: 30px;display: inline-block;float:right;">
        <?php
        if (shgdprdm_countPros() && $proSettingsCount > 0) { ?>
          <div id="edit-shgdprdm-search-by-options-inline-btn" class="button button-primary">Click to change "Search-By"</div>
        <?php
        } ?>
      </div>
    </div>
    
    
      <div style="clear:both";></div>
           
      <!-- include the search-by form - keep separate from submit form  -->
      <div id="shgdprdm-search-by-options-inline-container">
        <?php echo shgdprdm_optionSearchBy(); ?>
        <div><hr class="options-divider"></div>
      </div>
    <!-- End Addition for in-line editing of Additional Searches June 2019 -->
      
    <?php


    $exportclass = '';
    if ($searchBy[0] == 'Email Address') {
        $placeholder = SHGDPRDM_PLACEHOLDER_SEARCHBOX_EMAIL;
    } else {
        $placeholder = SHGDPRDM_PLACEHOLDER_SEARCHBOX_ID;
    }
    if (isset($_POST['seahorse_shgdprdm_search_options[emailSearch]'])) {
        $retrievedValue = sanitize_email(esc_attr($seahorse_shgdprdm_search_options['emailSearch']));
        $value = is_email($retrievedValue) ? $retrievedValue : '';
    } else {
        $value = '';
    }

    if (shgdprdm_isReDelete(false)) {
        $value = shgdprdm_getUserIdEmail(shgdprdm_isReDelete(true), $searchBy[1]);
        $exportclass = ' shgdprdm_search-field-admin-action';
        echo shgdprdm_getReExportNotice($value);
    } ?>
    <div id="shgdprdm-main-search-fields-container">
      <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>?action=shgdprdm_search_action_hook">
          <input type="hidden" name="shgdprdmscon" value="<?php echo $searchBy[1]; ?>">
          <?php wp_nonce_field('shgdprdm_search_action_hook', 'shgdprdmsch_nonce'); ?>
          <div style="display:inline-block;">
  
              <div id="shgdprdm-search-label-container">
                <label for="shgdprdmsparam" style="font-size:1.3em;font-weight:600;"><?php echo SHGDPRDM_LABEL_SEARCHBOX; ?></label>
              </div>
              <div style="display:inline-block;">
                <input type="search" class="shgdprdm_search-field<?php echo $exportclass; ?>" id="shgdprdmsparam" name="shgdprdmsparam" value="<?php echo $value; ?>" placeholder = "<?php echo $placeholder; ?>"/></input>
              </div>
      
              <div id="shgdprdm-search-btn-container">
                <?php
                $attr = array( 'id' => 'search', 'title' => 'Search User Data');
    submit_button(SHGDPRDM_ICON_SEARCH, 'search', 'search', true, $attr); ?>
              </div>
      
          </div>

          
      </form>
    </div>
    
    
    <!-- Pro Support -->
    <!-- Additional Search Options -->
    <?php
    // If there are no Pro Options inlcuded in Licence
    if (shgdprdm_countPros() > 0 && $proSettingsCount === 0) {
        echo shgdprdm_defaultSearchBy(); ?>
        <div style="clear:both";><hr class="options-divider"></div>
        <?php
    }
          
    if (shgdprdm_countPros() > 0 && $proSettingsCount > 0) {
        if (shgdprdm_getOptionsGroup('shgdprdm_admin_plugins_settings_group')) {
            echo shgdprdm_displaySearchExtras($proSettings);
        }
    } ?>
    <!-- End: Additional Search Options -->
      
      
    <!-- Pro Support -->
    <!-- Replacement Text Option -->
    <?php
    if (shgdprdm_countPros() > 0 && $proSettingsCount > 0) { ?>
      <div id="shgdprdm-text-option-container">
        <?php echo shgdprdm_optionReplacementText(); ?>
      </div>
      <?php
    } else {
        echo shgdprdm_defaultReplacementText();
    } ?>
    <!-- End: Replacement Text Option -->

    
    
  </div>

  <?php
  update_option('shgdprdm_admin_msg', '');
}
?>
