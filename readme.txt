=== GDPR Data Manager ===
Author: Seahorse
Contributors: wpseahorse, echomedia
Tags: LGPD, CCPA, woocommerce, edd, privacy policy, GDPR, Right to Erasure, Right to Forget, Right To Portability, Export Data, Delete Data, regulation, compliance, easy digital downloads, users, user management, delete, export
Requires at least: 4.4
Tested up to: 5.6
Requires PHP: 5.6
Stable tag: trunk

Is your site GDPR/CCPA/LGPD compliant? Our GDPR/CCPA/LGPD compliance plugin assists website owners adhere to the most critical data compliance obligations raised by GDPR/CCPA/LGPD

== Description ==

[GDPR Data Manager](https://www.gdpr-data-manager.com) is a plugin developed by [Seahorse](https://www.seahorse-data.com) which assists website and webshop owners to adhere to the most critical data compliance obligations raised by GDPR/CCPA/LGPD.

### YOUR WORDPRESS GDPR/CCPA/LGPD COMPLIANCE ASSISTANT

= What does it do? =
* Action **Right to Forget** (Delete) and **Right to Portability** (Export) requests easily and transparently using a customizable interface
* Add your customized Privacy Policy via shortcode
* Understand your data with our Database Overview

= Who is this plugin for? =
If your are gathering personally identifying information (PII) from users (eCommerce customers, contributors etc.) within the European Union (EU), you must comply with the General Data Protection Regulation (EU) 2016/679 ('GDPR'). This plugin also applies to the California Consumer Privacy Act ('AB-375') ('CCPA') effective from January 1, 2020.

#### SUPPORT FOR:
* WordPress Users
* WooCommerce
* Easy Digital Downloads

#### KEY FEATURES:
* **User actionable** : once a user request is actioned by the site admin, an email is sent to the user which acts as a trigger for the Export / Delete action, to be completed by the user.
* **Action logging** : all activity including user and admin actions are logged for audit purposes. The audit data is stored indefinitely on the GDM remote storage for retrieval as required. The GDM remote storage does not record any Personal Identifiable Information (PII) as per GDPR/CCPA/LGPD guidelines.
* **Remote Backup** : in case of data roll-back (restoring database from a previous version) avoid having to request users complete the process again. GDM does this automatically.
* **Customization** : real-time editor for customization of template views
* **Export** : option of 3 exports formats CSV, JSON & XML
* **Database Overview** : understand your database as per GDPR regulations i.e. see where personally identifying information (PII) data exists on your database
* **Privacy Policy** : custom Privacy Policy generator via shortcode
* **WP Multilingual** : compatible

#### TRANSLATIONS:
* French (FR)
* German (DE)
* Danish (DK)
* Italian (IT)
* Spanish (ES)

#### ROADMAP:
* User initiation of data requests
* Expansion of Database Overview
* WP eCommerce (Support)
* Ecwid Ecommerce (Support)
* WP EasyCart (Support)
* Translations: Portuguese (PT) / Brazil (BR), Dutch (NL)

== Screenshots ==
1. Search Interface
2. Admin Search Output with facility to action Export (Portability) or Delete (Erasure)
3. User Template page (user completes actions)
4. Sample history records (audit trail) including sync records from remote server
5. Privacy Policy editor inc user page template view
6. Your Database Overview


== Installation ==
To install this plugin:

1. Upload the entire 'seahorse-gdpr-data-manager' folder to the '/wp-content/plugins/' directory
2. Activate the plugin through the 'Plugins' menu in WordPress


== Frequently Asked Questions ==

= What makes this different to the native WordPress Erase / Delete Data feature? =
GDM does not put the responsibility on the webmaster to securely generate and send requested data. No user data is sent over email, instead a secure link is emailed to the requestor which expires once activated or after 24 hours of inactivity. Requestors must complete the actions themselves ensuring that no export data is left accessible on webmasters machines in line with GDPR/CCPA/LGPD compliance.

= What happens if I roll-back my database and user data which has been deleted is returned? =
GDM stores activity remotely so in the event of a roll-back, the plugin will compare remote activity with local data to sync any changes. Users will not have to go through the verification process again. Data controllers can re-run the already verified actions

= What if I have a Right to Forget request from a WooCommerce Guest user? =
GDM can isolate WooCommerce guest data in the same way as regular user data. If the request is for data deletion, GDM only deletes the user data leaving system data intact for future reporting etc. as per data protection guidelines.

= How is contributed content handled in cases of data deletion requests? =
A distinction is made by GDM between contributed 'content' and 'content attributes' so in the case of posts, a deletion request will lead to the post being assigned to the deleted user as author (so no associated PII data) but the contributed content Title and Body text will remain.

= If a user requests their data to be deleted, what happens to posts etc. that they have contributed? =
The content (body, title etc.) remains in place - only the associated PII (author detail etc.) is removed.

= If a user deletes their data, will my eCommerce reports be effected? =
GDM maintains all operational data after a user deletion including some high level data (e.g. high level location data of customer etc.) so reporting is uneffected. All eCommerce order data persists - only user PII is removed.


== Video Guides ==

How-to guide outlining how to delete user data from your site - GDPR/CCPA/LGPD "Right to Erasure" requests

https://www.youtube.com/embed/J6pXvhuqTWg

How to add a Privacy Policy to my site

https://www.youtube.com/embed/lNGOnKqjvA4

How to export user data

https://www.youtube.com/embed/grP6aAQAAcE


== Changelog ==

= 2.6.0 =
* updates related to LGPD

= 2.5.1 =
* language updates related to CCPA

= 2.5.1 =
* change of domain

= 2.4.31 =
* bug fix for pro privacy policy shortcode

= 2.4.3 =
* multilingual compatibility added IT / ES

= 2.4.1 =
* multilingual compatibility added FR / DE / DK

= 2.0.11 =
* added warning for users regarding pending orders (wc/edd). Further advancement of multilingual

= 2.0.1 =
* licence type handling upgraded & help and support content

= 1.3.11 =
* patch applied to privacy policy shortcode gen

= 1.3.1 =
* new plan offerings & Custom Privacy Policy Generator: major release

= 1.2.20 =
* customizable 'Right to Data' section of Privacy Policy via short-code

= 1.2.11 =
* minor release: patch for EDD Guest user data

= 1.2.1 =
* Support for Easy Digital Downloads added

= 1.0.15 =
* v1.0 of the 'Your Database' section

= 1.0.14 =
* fix's applied to external links and updates to free trial period

= 1.0.13 =
* updates to system licensing method inc. UX edits

= 1.0.12 =
* compatibility issue for PHP 7.1+ bug fix

= 1.0.11 =
* Edits to user email text content

= 1.0.10 =
* Expansion of disaster sync functionality and addition of free features (template views)

= 1.0.9 =
* WC Guest deletion patched and post content deletion bug fix

= 1.0.4 =
* updates to handling of disaster record syncing and delete data process

= 1.0.3 =
* update to naming conventions as per WP guidelines

= 1.0.2 =
* Fix - zip installation process error


== Upgrade Notice ==

= 1.0.13 =
License validation working
