<?php
defined('SHGDPRDM_ABSPATH') or die('You are not authorised to access this file.');
function shgdprdm_reviewHelpPage()
{
    if (shgdprdm_getUserNotice()) {
        echo shgdprdm_getUserNotice();
    } ?>
  <div class="container">

    <div>
      <h2>Help & Support</h2>
    </div>
    <div><hr></div>

    <!-- user guide -->
    <!-- <div>
      <?php shgdprdm_helpGuideSectionText(); ?>
      <?php echo shgdprdm_getHelpGuide(); ?>
    </div>
      <div><hr></div> -->

      <!-- support guide -->
      <div>
        <?php shgdprdm_helpSupportSectionText(); ?>
        <?php echo shgdprdm_getHelpSupport(); ?>
      </div>


    <!-- faq guide -->
    <div>
      <?php shgdprdm_faqSectionText(); ?>
      <?php echo shgdprdm_getFaq(); ?>
    </div>
    <div><hr></div>
    
    
    <!-- video guide -->
    <div>
      <?php shgdprdm_videoSectionText(); ?>
      <?php echo shgdprdm_videoView(); ?>
    </div>
    <div><hr></div>



  </div>

<?php
} ?>
