<?php
defined('SHGDPRDM_ABSPATH') or die('You are not authorised to access this file.');
// Functions for help & support page

// Print the User Guide Section heading
function shgdprdm_helpGuideSectionText()
{
    echo '<h2>User Guide</h2>';
}

// Print the view for User Guide Section
function shgdprdm_getHelpGuide()
{
    $content = "";
    $content .= "<p>GDPR Data Manager is a set of tools that allows you to manage the most common and important customer data issues raised by GDPR. Search customer records and take action, it's that simple to get started.</p>
  <p>All requests and actions are logged for audit purposes. All data deletion requests are backed up in case you have to restore your data from a previous version and find yourself having to go through all customer requests again. </p>";

    $content .= "<div id='help_lists'>";
    $content .= "<br>";
    $content .= "<h4>Search:</h4>
<ul>
<li>by Email Address (default)</li>
</ul>";
    $content .= "<h4>Options:</h4>
<ul>
<li>Search by Email address (default) or User ID: </li>";
    $content .= "<li>Refine Your Search by checking additional Plugin options (WooCommerce is selected by default)</li>";
    $content .= "<li>Review Replacement Text options. This is the content that will be outputted on Verification email to the user</li>";
    $content .= "</ul>";
    $content .= "<h4>Pending:</h4>
<ul>
<li>Requests are listed here and users have 24 hours from the time the request is raised to complete the action</li>
</ul>";
    $content .= "<h4>History:</h4>
<ul>
<li>All activity is logged here</li>
</ul>";

    $content .= "</div>";

    return $content;
}


// Print the faq Heading
function shgdprdm_faqSectionText()
{
    echo '<h2>FAQ</h2>';
}

// Print the view for faq Section
function shgdprdm_getFaq($num = null)
{
    $str = "<p><strong>Q1. What makes this different to the native WordPress Erase / Delete Data feature?</strong></p>
    <p>A1. GDM does not put the responsibility on the webmaster to securely generate and send requested data. No user data is sent over email, instead a secure link is emailed to the requestor which expires once activated or after 24 hours of inactivity. Requestors must complete the actions themselves ensuring that no export data is left accessible on webmasters machines in line with GDPR/CCPA compliance.</p>
    <p><strong>Q2. What happens if I roll-back my database and user data which has been deleted is returned?</strong></p>
    <p>A2. GDM stores activity remotely so in the event of a roll-back, the plugin will compare remote activity with local data to sync any changes. Users will not have to go through the verification process again. Data controllers can re-run the already verified actions</p>
    <p><strong>Q3. What if I have a Right to Forget request from a WooCommerce Guest user?</strong></p>
    <p>A3. GDM can isolate WooCommerce guest data in the same way as regular user data. If the request is for data deletion, GDM only deletes the user data leaving system data intact for future reporting etc. as per data protection guidlelines.</p>
    <p><strong>Q4. How is contributed content handled in cases of data deletion requests?</strong></p>
    <p>A4. A distinction is made by GDM between contributed 'content' and 'content attributes' so in the case of posts, a deletion request will lead to the post being assigned to the deleted user as author (so no associated PII data) but the contributed content Title and Body text will remain.</p>
    <p><strong>Q5. If a user requests their data to be deleted, what happens to posts etc. that they have contributed?</strong></p>
    <p>A5. The content (body, title etc.) remains in place – only the associated PII (author detail etc.) is removed.</p>
    <p><strong>Q6. If a user deletes their data, will my eCommerce reports be effected?</strong></p>
    <p>A6. GDM maintains all operational data after a user deletion including some high level data (e.g. high level location data of customer etc.) so reporting is uneffected. All eCommerce order data persists – only user PII is removed.</p>";

    return $str;
}


// Print the video Heading
function shgdprdm_videoSectionText()
{
    echo '<h2>Video Guides</h2>';
}

function shgdprdm_videoView($num = null)
{
    $str = '<p>
    <div style="margin-left:50px">
    <h4>1. How-to guide outlining how to delete user data from your site - GDPR/CCPA "Right to Erasure" requests</h4>
    <iframe width="360" height="203" src="https://www.youtube.com/embed/J6pXvhuqTWg" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
    </div>
    </p>
    <p>
    <div style="margin-left:50px">
    <h4>2. How to add a Privacy Policy to my site</h4>
    <iframe width="360" height="203" src="https://www.youtube.com/embed/lNGOnKqjvA4" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
    </div>
    </p>
    <p>
    <div style="margin-left:50px">
    <h4>3. How to export user data</h4>
    <iframe width="360" height="203" src="https://www.youtube.com/embed/grP6aAQAAcE" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
     </div></p>';

    return $str;
}






// Print the Support Heading
function shgdprdm_helpSupportSectionText()
{
    echo '<h2>Support</h2>';
}

// Print the view for User Support Section
function shgdprdm_getHelpSupport()
{
    ///$sEmail = (isset(get_option('shgdprdm_text_options')['support_email_option']) && get_option('shgdprdm_text_options')['support_email_option'] != '') ? get_option('shgdprdm_text_options')['email_option'] : get_bloginfo( 'admin_email' );
    ///$support = "<p><strong>Email Us: </strong>".SHGDPRDM_SUPPORT_EMAIL."</p>";

    $support = "<a class='button button-primary' href='".SHGDPRDM_VALIDATE_DEFAULT_URL."/support' target='_blank'>GDPR Data Manager Support</a>
  <p>You must provide a valid license number to use our Support portal.</p>";

    // $support = "<form method='post' action='#'>";
    // submit_button('Register This Licence', 'primary', 'shgdprdm_reglicnum_options[shgdprdm_register_licence]', true, $attr);
    // $support .= "</form>";


    return $support;
}
