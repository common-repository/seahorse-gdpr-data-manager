<?php
defined('SHGDPRDM_ABSPATH') or die('You are not authorised to access this file.');

define('SHGDPRDM_LABEL_TITLE', 'GDPR Data Manager');

define(
    'SHGDPRDM_ACTIVATION_NOTICE_INTRO',
    sprintf(
        esc_html__(
            'Thanks for installing GDPR Data Manager, to access your licence key visit: %s.',
            'seahorse-gdpr-data-manager'
        ),
        '<em><a href="'.SHGDPRDM_VALIDATE_DEFAULT_URL.'/downloads/" target="_blank">GDPR Data Manager</a></em>'
    )
);

define(
    'SHGDPRDM_ACTIVATION_NOTICE_ACTIVATE',
    sprintf(
        esc_html__(
            'Join us in celebrating the launch of the %s plugin with a %s3-Month Free Trial%s period! Limited time only.',
            'seahorse-gdpr-data-manager'
        ),
        'GDPR Data Manager',
        '<strong>',
        '</strong>'
    )
);

define(
    'SHGDPRDM_LABEL_SUBTITLE',
    esc_html__(
        'Search User',
        'seahorse-gdpr-data-manager'
    )
);

define(
    'SHGDPRDM_LABEL_NAV_HOME',
    esc_html__(
        'Your Database',
        'seahorse-gdpr-data-manager'
    )
);

define(
    'SHGDPRDM_LABEL_NAV_SEARCH',
    esc_html__(
        'Search',
        'seahorse-gdpr-data-manager'
    )
);

define(
    'SHGDPRDM_LABEL_NAV_PENDING',
    esc_html__(
        'Pending',
        'seahorse-gdpr-data-manager'
    )
);

define(
    'SHGDPRDM_LABEL_NAV_PRIVACY',
    esc_html__(
        'Privacy Policy',
        'seahorse-gdpr-data-manager'
    )
);

define(
    'SHGDPRDM_LABEL_NAV_OPTIONS',
    esc_html__(
        'Options',
        'seahorse-gdpr-data-manager'
    )
);

define(
    'SHGDPRDM_LABEL_NAV_REVIEW',
    esc_html__(
        'History',
        'seahorse-gdpr-data-manager'
    )
);

define(
    'SHGDPRDM_LABEL_NAV_HELP',
    esc_html__(
        'Help & Support',
        'seahorse-gdpr-data-manager'
    )
);

define(
    'SHGDPRDM_LABEL_NAV_REGISTER',
    esc_html__(
        'Register Licence',
        'seahorse-gdpr-data-manager'
    )
);

define(
    'SHGDPRDM_LABEL_TITLE_OPTIONS',
    sprintf(
        esc_html__(
            '%s - Options',
            'seahorse-gdpr-data-manager'
        ),
        'GDPR Data Manager'
    )
);


//// Email text contents
define(
    'SHGDPRDM_DELETE_MAIL_SUBJECT',
    esc_html__(
        'Verification of Data Deletion Request',
        'seahorse-gdpr-data-manager'
    )
);

define(
    'SHGDPRDM_DELETE_MAIL_BODY_1',
    sprintf(
        esc_html__(
            '%sPlease confirm that you have requested your data be completely removed from the %s database.%s%sThis action cannot be undone. For security reasons this link will expire in 24 hours and can only be used once. If you do nothing, the status of your data will remain unchanged.%s%sClick this link to verify the request: %s',
            'seahorse-gdpr-data-manager'
        ),
        '<p>',
        get_bloginfo('name'),
        '</p>',
        '<p>',
        '</p>',
        '<p>',
        '</p>'
    )
);

define(
    'SHGDPRDM_DELETE_MAIL_BODY_1_ADMIN',
    sprintf(
        esc_html__(
            '%sPlease confirm that you wish to proceed with the completion of Record Synchronisation.%sWhen executed, this action will delete all the user data for the syncronised record from the %s database.%sOnce executed, this action cannot be undone.%s%sClick this link to verify the request: %s',
            'seahorse-gdpr-data-manager'
        ),
        '<p>',
        '<br>',
        get_bloginfo('name'),
        '<br>',
        '</p>',
        '<p>',
        '</p>'
    )
);

define(
    'SHGDPRDM_EXPORT_MAIL_SUBJECT',
    esc_html__(
        'Verification of Data Export Request',
        'seahorse-gdpr-data-manager'
    )
);

define(
    'SHGDPRDM_EXPORT_MAIL_BODY_1',
    sprintf(
        esc_html__(
            '%sPlease confirm that you have requested your data be exported from the %s database.%s%sClick this link to verify the request: %s',
            'seahorse-gdpr-data-manager'
        ),
        '<p>',
        get_bloginfo('name'),
        '</p>',
        '<p>',
        '</p>'
    )
);

define(
    'SHGDPRDM_DELETE_EXPORT_MAIL_BODY_2',
    sprintf(
        esc_html__(
            '%sPlease ignore this email if this was made in error or may be malicious.%s%sRegards%s%s Team%s',
            'seahorse-gdpr-data-manager'
        ),
        '<p>',
        '</p>',
        '<p>',
        '<br>',
        get_bloginfo('name'),
        '</p>'
    )
);

define(
    'SHGDPRDM_DELETE_EXPORT_VERIFY_LINK_TEXT',
    esc_html__(
        'Verify Request',
        'seahorse-gdpr-data-manager'
    )
);
//// END: Email text contents

define('SHGDPRDM_ICON_SEARCH', '&#xf179;'); // Magnifying Glass
define('SHGDPRDM_ICON_X', '&#9447;');

define(
    'SHGDPRDM_LABEL_SEARCHBOX',
    sprintf(
        esc_html__(
            'Search%sUser',
            'seahorse-gdpr-data-manager'
        ),
        '<br>'
    )
);

define(
    'SHGDPRDM_PLACEHOLDER_SEARCHBOX_ID',
    esc_html__(
        'Enter User ID Number',
        'seahorse-gdpr-data-manager'
    )
);
define(
    'SHGDPRDM_PLACEHOLDER_SEARCHBOX_EMAIL',
    esc_html__(
        'Enter User Email Address',
        'seahorse-gdpr-data-manager'
    )
);
define(
    'SHGDPRDM_DEFAULT_REPLACEMENT_TEXT_IN_DB',
    esc_html__(
        'The content has been deleted by the user',
        'seahorse-gdpr-data-manager'
    )
);

define(
    'SHGDPRDM_DEFAULT_PRIVACY_POLICY_DISCLAIMER_TEXT',
    sprintf(
        esc_html__(
            'The boiler plate text provided here makes a number of assumptions. %s strongly advises that users read and edit the text below as required to best suit the operations of your site. The Right to Data section is only available to %s and %s users as this relates to %s services that require a licence.',
            'seahorse-gdpr-data-manager'
        ),
        'GDM ',
        '<a href="'.SHGDPRDM_VALIDATE_DEFAULT_URL.'/downloads/gdpr-data-manager-standard/" target="_blank">Standard</a>',
        '<a href="'.SHGDPRDM_VALIDATE_DEFAULT_URL.'/downloads/gdpr-data-manager/" target="_blank">Pro</a>',
        'GDM'
    )
);


/// PP General Information section
define(
    'SHGDPRDM_DEFAULT_POLICY_GEN_HEADER_CONDITIONS_TEXT',
    esc_html__(
        'General information',
        'seahorse-gdpr-data-manager'
    )
);

define(
    'SHGDPRDM_DEFAULT_POLICY_GEN_CONDITIONS_TEXT',
    sprintf(
        esc_html__(
            '%sWelcome to the %s Privacy Policy page. When you use site, you trust us with your information.
            This Privacy Policy is meant to help you understand what data we collect, why we collect it, and what we do with it.
            When you share information with us, we can make our services even better for you.
            For instance, we can show you more relevant search results, help you connect with people or to make sharing with others quicker and easier.
            As you use our services, we want you to be clear how we are using information and the ways in which you can protect your privacy.
            This is important, we hope you will take time to read it carefully. We have tried to keep it as simple as possible.
            Remember, you can find controls to manage your information and protect your privacy and security.%s',
            'seahorse-gdpr-data-manager'
        ),
        '<p>',
        get_bloginfo('name'),
        '</p>'
    )
);

/// PP Right to Data section
define(
    'SHGDPRDM_DEFAULT_TERMS_HEADER_CONDITIONS_TEXT',
    esc_html__(
        'Right to access, edit and delete data and to object to data processing',
        'seahorse-gdpr-data-manager'
    )
);


define(
    'SHGDPRDM_DEFAULT_TERMS_CONDITIONS_TEXT',
    sprintf(
        esc_html__(
            '%sOur customers have the right to access, correct and delete personal data relating to them,
            and to object to the processing of such data, by emailing a request to
            %s, at any time.
            %s makes every effort to put in place suitable precautions to safeguard the security
            and privacy of personal data, and to prevent it from being altered, corrupted, destroyed or accessed by
            unauthorized third parties. However, %s does not control each and every risk
            related to the use of the Internet, and therefore warns the Site users of the potential risks involved
            in the functioning and use of the Internet. The Site may include links to other web sites or other internet sources.
            As %s cannot control these web sites and external sources,
            %s cannot be held responsible for the provision or display of these web sites and external sources,
            and may not be held liable for the content, advertising, products, services or any other material
            available on or from these web sites or external sources.%s',
            'seahorse-gdpr-data-manager'
        ),
        '<p>',
        '<a href="mailto:'.get_bloginfo('admin_email').'">DPO Email</a>',
        get_bloginfo('name'),
        get_bloginfo('name'),
        get_bloginfo('name'),
        get_bloginfo('name'),
        '</p>'
    )
);
define('SHGDPRDM_DEFAULT_PRIVACY_POLICY_LINK', 'privacy-policy');

/// PP Management of personal data section
define(
    'SHGDPRDM_DEFAULT_POLICY_MNG_HEADER_CONDITIONS_TEXT',
    esc_html__(
        'Management of personal data',
        'seahorse-gdpr-data-manager'
    )
);

define(
    'SHGDPRDM_DEFAULT_POLICY_MNG_CONDITIONS_TEXT',
    sprintf(
        esc_html__(
            '%sYou can view or edit your personal data online for many of the %s services
            but for some you may have to submit a request to our %s.
            You can also make choices about the collection and use of your data.
            How you can access or control your personal data will depend on which services you use.
            You can choose whether you wish to receive promotional communications from our site by email, SMS, post, and telephone.
            If you receive promotional email or SMS messages from us and would like to opt out, you can do so by
            following the directions in that message. These choices do not apply to mandatory service communications
            that are part of certain services e.g. e-commerce transactions etc.%s',
            'seahorse-gdpr-data-manager'
        ),
        '<p>',
        get_bloginfo('name'),
        '<a href="mailto:'.get_bloginfo('admin_email').'">DPO Email</a>',
        '</p>'
    )
);


/// PP Information we collect section
define(
    'SHGDPRDM_DEFAULT_POLICY_ICO_HEADER_CONDITIONS_TEXT',
    esc_html__(
        'Information we collect',
        'seahorse-gdpr-data-manager'
    )
);

define(
    'SHGDPRDM_DEFAULT_POLICY_ICO_CONDITIONS_TEXT',
    sprintf(
        esc_html__(
            '%sThe %s site collects data to operate effectively and provide
            you the best experience of our services. You provide some of this data directly,
            such as when you create a personal account. We get some of it by recording how you
            interact with our services by, for example, using technologies like cookies, and receiving
            error reports or usage data from software running on your device.
            We also obtain data from third parties (including other companies).
            For example, we supplement the data we collect by gathering demographic data
            from other companies e.g. Google Analytics.
            We also use services from third parties to help us determine a location based on your IP address
            in order to customise certain services to your location.
            The data we collect depends on the services and features you use.%s',
            'seahorse-gdpr-data-manager'
        ),
        '<p>',
        get_bloginfo('name'),
        '</p>'
    )
);

/// PP How we use your information section
define(
    'SHGDPRDM_DEFAULT_POLICY_USE_HEADER_CONDITIONS_TEXT',
    esc_html__(
        'How we use your information',
        'seahorse-gdpr-data-manager'
    )
);

define(
    'SHGDPRDM_DEFAULT_POLICY_USE_CONDITIONS_TEXT',
    sprintf(
        esc_html__(
            '%s%s uses the data we collect for three basic purposes:
            to operate our business and provide (including improving and personalizing) the services we offer,
            to send communications, including promotional communications, and to display advertising.
            In carrying out these purposes, we combine data we collect through the various site services
            you use to give you a more seamless, consistent and personalized experience.
            However, to enhance privacy, we have built in technological and procedural safeguards
            designed to prevent certain data combinations.
            For example, we store data we collect from you when you are unauthenticated (not signed in)
            separately from any account information that directly identifies you,
            such as your name, email address or phone number.%s',
            'seahorse-gdpr-data-manager'
        ),
        '<p>',
        get_bloginfo('name'),
        '</p>'
    )
);

/// PP Sharing your information section
define(
    'SHGDPRDM_DEFAULT_POLICY_SHA_HEADER_CONDITIONS_TEXT',
    esc_html__(
        'Sharing your information',
        'seahorse-gdpr-data-manager'
    )
);

define(
    'SHGDPRDM_DEFAULT_POLICY_SHA_CONDITIONS_TEXT',
    sprintf(
        esc_html__(
            '%s%s will only share your personal data
            with your consent or as necessary to complete any transaction or provide any
            service you have requested or authorized.
            For example, we share your content with third parties when you tell us to do so.
            When you provide payment data to make a purchase, we will share payment data
            with banks and other entities that process payment transactions or provide other financial services,
            and for fraud prevention and credit risk reduction.
            In addition, we share personal data among our controlled affiliates and subsidiaries.
            We also share personal data with vendors or agents working on our behalf for the
            purposes described in this statement.
            For example, companies we’ve hired to provide customer service support or assist in
            protecting and securing our systems and services may need access to personal data in order to
            provide those functions.
            In such cases, these companies must abide by our data privacy and security requirements
            and are not allowed to use personal data they receive from us for any other purpose.
            We may also disclose personal data as part of a corporate transaction
            such as a merger or sale of assets.%s',
            'seahorse-gdpr-data-manager'
        ),
        '<p>',
        get_bloginfo('name'),
        '</p>'
    )
);

//// database tab - traffic light descriptions
define(
    'SHGDPRDM_INDICATE_GREEN',
    sprintf(
        esc_html__(
            '%s will search and process data in these tables: %scontains personally identifying information - PII%s',
            'seahorse-gdpr-data-manager'
        ),
        'GDPR Data Manager',
        '<strong>',
        '</strong>'
    )
);

define(
    'SHGDPRDM_INDICATE_ORANGE',
    sprintf(
        esc_html__(
            '%s will search these tables (%scontains personally identifying information - PII%s)
            but will not process the data as no active licence type is set:
            see %s',
            'seahorse-gdpr-data-manager'
        ),
        'GDPR Data Manager',
        '<strong>',
        '</strong>',
        '<a href="'.SHGDPRDM_VALIDATE_DEFAULT_URL.'/downloads/" target="_blank">GDM plan (Standard or Pro)</a>'
    )
);

define(
    'SHGDPRDM_INDICATE_RED',
    sprintf(
        esc_html__(
            '%s will not search this table',
            'seahorse-gdpr-data-manager'
        ),
        'GDPR Data Manager'
    )
);

/// upgrade data
define(
    'SHGDPRDM_PRO_UPGRADE_TEXT',
    sprintf(
        esc_html__(
            'Upgrade to the %s Version to expand what data can be searched: %s | %s',
            'seahorse-gdpr-data-manager'
        ),
        'PRO',
        '<a href="'.SHGDPRDM_VALIDATE_DEFAULT_URL.'/downloads/gdpr-data-manager/" target="_blank">WooCommerce</a>',
        '<a href="'.SHGDPRDM_VALIDATE_DEFAULT_URL.'/downloads/gdpr-data-manager/" target="_blank">Easy Digital Downloads</a>'
    )
);

// Notifications

// Errors
define(
    'SHGDPRDM_err_001',
    sprintf(
        esc_html__(
            '%sInvalid email format.%sEmail string value was not of valid format',
            'seahorse-gdpr-data-manager'
        ),
        '<strong>',
        '</strong><br>'
    )
);

define(
    'SHGDPRDM_err_002',
    esc_html__(
        'Cannot connect to DB. CMS app was unable to connect to the DB',
        'seahorse-gdpr-data-manager'
    )
);

define(
    'SHGDPRDM_err_003',
    esc_html__(
        'DB table not found. Table declared in module code was not found',
        'seahorse-gdpr-data-manager'
    )
);

define(
    'SHGDPRDM_err_004',
    esc_html__(
        'Data export error. Could not export data from table (+MySQL error)',
        'seahorse-gdpr-data-manager'
    )
);

define(
    'SHGDPRDM_err_005',
    esc_html__(
        'MySQL error Return MySQL system error for display',
        'seahorse-gdpr-data-manager'
    )
);

define(
    'SHGDPRDM_err_006',
    esc_html__(
        'Delete data error. Error deleting data (+MySQL error)',
        'seahorse-gdpr-data-manager'
    )
);

define(
    'SHGDPRDM_err_007',
    sprintf(
        esc_html__(
            '%sInvalid/Unknown Email%sThe Email address provided is not valid or is unknown',
            'seahorse-gdpr-data-manager'
        ),
        '<strong>',
        '</strong><br>'
    )
);

define(
    'SHGDPRDM_err_008',
    sprintf(
        esc_html__(
            '%sInvalid/Unknown User ID%sThe User ID Number provided is not valid or is unknown',
            'seahorse-gdpr-data-manager'
        ),
        '<strong>',
        '</strong><br>'
    )
);

define(
    'SHGDPRDM_err_009',
    sprintf(
        esc_html__(
            '%sInvalid Request%sYou are not authorised to carry out this request',
            'seahorse-gdpr-data-manager'
        ),
        '<strong>',
        '</strong><br>'
    )
);

define(
    'SHGDPRDM_err_009_1',
    sprintf(
        esc_html__(
            '%sINTENTIONAL TESTING ERROR CREATED (Replicate err_009)%sYou are not authorised to carry out this request',
            'seahorse-gdpr-data-manager'
        ),
        '<strong>',
        '</strong><br>'
    )
);

define(
    'SHGDPRDM_err_010',
    sprintf(
        esc_html__(
            '%sData Retrieval Error%sThere is a problem with the retrieved data.%sPlease contact %s',
            'seahorse-gdpr-data-manager'
        ),
        '<strong>',
        '</strong><br>',
        '<br>',
        '<a href="'.SHGDPRDM_VALIDATE_DEFAULT_URL.'" target="_blank">Seahorse Data Management</a>'
    )
);

define(
    'SHGDPRDM_err_011',
    sprintf(
        esc_html__(
            '%sData Synchronise Error%sA problem has been identifed when attempting to Synchronise these data.%sPlease contact %sError Ref: ',
            'seahorse-gdpr-data-manager'
        ),
        '<span><strong>',
        '</strong><br>',
        '<br>',
        '<a href="'.SHGDPRDM_VALIDATE_DEFAULT_URL.'" target="_blank">Seahorse Data Management</a><br><strong>'
    )
);


  // '<strong>Licence Key Not Registered</strong><br>
  // Please Register & Activate the Licence Key that your received when purchasing this plugin. ');

// Warnings
define(
    'SHGDPRDM_war_001',
    sprintf(
        esc_html__(
            '%sNo records found%sSearch terms returned 0 results',
            'seahorse-gdpr-data-manager'
        ),
        '<strong>',
        '</strong><br>'
    )
);

define(
    'SHGDPRDM_war_002',
    sprintf(
        esc_html__(
            '%sMultiple records found%sMore than 1 user with same email address found',
            'seahorse-gdpr-data-manager'
        ),
        '<strong>',
        '</strong><br>'
    )
);

define(
    'SHGDPRDM_war_003',
    sprintf(
        esc_html__(
            '%sData set too large%sThe size of user data is too large for display',
            'seahorse-gdpr-data-manager'
        ),
        '<strong>',
        '</strong><br>'
    )
);

define(
    'SHGDPRDM_war_004',
    sprintf(
        esc_html__(
            '%sAdministrator User%sThis action cannot be carried out on Administration Users',
            'seahorse-gdpr-data-manager'
        ),
        '<strong>',
        '</strong><br>'
    )
);

define(
    'SHGDPRDM_war_005',
    sprintf(
        esc_html__(
            '%sNo Search Details%sNo input details were detected in the search request',
            'seahorse-gdpr-data-manager'
        ),
        '<strong>',
        '</strong><br>'
    )
);

define(
    'SHGDPRDM_war_006',
    sprintf(
        esc_html__(
            '%sMultiple Inputs%sMultiple search inputs were detected. Search must be for a single user only',
            'seahorse-gdpr-data-manager'
        ),
        '<strong>',
        '</strong><br>'
    )
);

define(
    'SHGDPRDM_war_007',
    sprintf(
        esc_html__(
            '%s%sUpgrade to access all the %s Features!%s Select the %s plan %s that best suits your needs.',
            'seahorse-gdpr-data-manager'
        ),
        '<strong>',
        '<a href="'.SHGDPRDM_VALIDATE_DEFAULT_URL.'/downloads/" target="_blank">',
        'GDM',
        '</a></strong>',
        'GDM',
        '(Standard or Pro)'
    )
);

define(
    'SHGDPRDM_war_008',
    sprintf(
        esc_html__(
            '%sLicence Key Not Registered%sPlease Register & Activate your Licence Key to access the full suite of GDM features.',
            'seahorse-gdpr-data-manager'
        ),
        '<strong>',
        '</strong><br>'
    )
);


// success
define(
    'SHGDPRDM_msg_001',
    sprintf(
        esc_html__(
            '%sData returned.%sUser data retrieved and returned for display',
            'seahorse-gdpr-data-manager'
        ),
        '<strong>',
        '</strong><br>'
    )
);

define(
    'SHGDPRDM_msg_002',
    esc_html__(
        'Data downloaded. The size of user data is too large for display',
        'seahorse-gdpr-data-manager'
    )
);

define(
    'SHGDPRDM_msg_003',
    esc_html__(
        'Data deleted. Requested user data deleted',
        'seahorse-gdpr-data-manager'
    )
);


// admin eMail
define(
    'SHGDPRDM_aem_001',
    sprintf(
        esc_html__(
            '%sWarning!%s
            %s Plugin has detected an unauthorised attempt to access user data.%s
            The plugin has forbidden access to the data.%s
            Plugin Access by User: ',
            'seahorse-gdpr-data-manager'
        ),
        '<strong>',
        '</strong><br>',
        'GDPR Data Manager',
        '<br>',
        '<br>'
    )
);


// Seahorse Profile
define(
    'SHGDPRDM_Seahorse_Profile_Text',
    sprintf(
        esc_html__(
            'Understanding your data is essential to the success of your business.%s
            Securing, Managing and Monitoring your data is our focus.%s
            Challenges to your ongoing success can happen. Corrupted data, legal obligations such as %s and undetected data breaches can hinder your progress.%s
            Take control of your data with Seahorse.%s
            Tailored and Bespoke solutions along with more great CMS Plugins are available from us at %s',
            'seahorse-gdpr-data-manager'
        ),
        '<br><br>',
        '<br><br>',
        'GDPR',
        '<br><br>',
        '<br><br>',
        '<a href="'.SHGDPRDM_VALIDATE_DEFAULT_URL.'" target="_blank">Seahorse Plugin Shop</a>'
    )
);
