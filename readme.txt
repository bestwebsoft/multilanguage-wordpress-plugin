=== Multilanguage by BestWebSoft ===
Contributors: bestwebsoft
Donate link: http://bestwebsoft.com/donate/
Tags: add translation, add translations, best multilanguage plugin, bilingual, free multilanguage plugin, free plugin, globalization, international, international plugin, language, language switcher, localization, multilanguage, multi language, multilanguage plugin, multilingual, plugin, simple multilanguage plugin, simple language switcher, switcher, translate, translation, translations, translation-ready, widget, wordpress multilanguage plugin, wp multilanguage plugin, wordpress plugin, wp plugin
Requires at least: 3.8
Tested up to: 4.3.1
Stable tag: 1.1.2
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

This plugin allows you to create content on a Wordpress site in different languages.

== Description ==

Multilanguage allows you to create a multilingual WordPress website. Hence, you can create or edit posts, pages, or create categories and post tags as usual, and then define the language for each of them. The translation of a post, whether it was created in the default language or not, is optional. All translations must be done manually using Multilanguage plugin, because it does not integrate any automatic or professional translation service.

NOTE: This plugin will only work with the themes and plugins, which were developed according to Wordpress Codex standards. Also, it uses standard filters (the_title, the_content, get_terms, get_the_terms, get_term, wp_get_nav_menu_items).

http://www.youtube.com/watch?v=Si6ulEWuY1E 

<a href="http://wordpress.org/plugins/multilanguage/faq/" target="_blank">Multilanguage by BestWebSoft FAQ</a>

<a href="http://support.bestwebsoft.com" target="_blank">Multilanguage by BestWebSoft Support</a>

<a href="http://bestwebsoft.com/products/multilanguage/?k=1d4576a3a2c4fc0f127ce2ee0341d81b" target="_blank">Upgrade to Multilanguage Pro by BestWebSoft</a>

= Features =

* You can use as many languages as you want. By default, you can choose one of 76 pre-installed languages. However, you can add any number of languages to list of pre-installed languages in "include/languages.php" file.
* You can add translations for posts, pages, categories, and post tags.
* The language is either set by the language switcher or by the language code in url (as directory).
* A customizable language switcher is provided as a widget, which can be displayed in the form of a Drop-down list of languages, Drop-down list of flags icons or Flags icons.
* Default searching by the language which is currently selected for the site or all available languages.
* You can determine the priority of language display ​​in the switcher by changing the priority of language display on the plugin settings page.
* The admin interface is translation-ready.

= Translation =

* Polish (pl_PL) (thanks to <a href="mailto:emiljo@tlen.pl">Emil Król</a>)
* Russian (ru_RU)
* Ukrainian (uk)

If you would like to create your own language pack or update the existing one, you can send <a href="http://codex.wordpress.org/Translating_WordPress" target="_blank">the text of PO and MO files</a> for <a href="http://support.bestwebsoft.com" target="_blank">BestWebSoft</a> and we'll add it to the plugin. You can download the latest version of the program for work with PO and MO files <a href="http://www.poedit.net/download.php" target="_blank">Poedit</a>.

= Technical support =

Dear users, our plugins are available for free download. If you have any questions or recommendations regarding the functionality of our plugins (existing options, new options, current issues), please feel free to contact us. Please note that we accept requests in English only. All messages in another languages won't be accepted.

If you notice any bugs in the plugin's work, you can notify us about it and we'll then investigate and fix the issue. Your request should contain URL of the website, issues description and WordPress admin panel credentials.
Moreover, we can customize the plugin according to your requirements. It's a paid service (as a rule it costs $40, but the price can vary depending on the amount of the necessary changes and their complexity). Please note that we could also include this or that feature (developed for you) in the next release and then share it with other users.
We can fix some things for free for the users who provide a translation of our plugin into their native language (this should be a new translation of a certain plugin, you can check available translations on the official plugin page).

== Installation ==

1. Upload the `multilanguage` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin using the 'Plugins' menu in your WordPress admin panel.
3. You can adjust the necessary settings using your WordPress admin panel in "BWS Plugins" > "Multilanguage".
4. Plugin page is located in main menu.

<a href="https://docs.google.com/document/d/1018gX-8ggRs180LT2B90q0zhEJ7EewDOckpwmkJYb5Y/edit" target="_blank">View a Step-by-step Instruction on Multilanguage Installation</a>.

== Frequently Asked Questions ==

= Will Multilanguage translate my content? =

No, it will not. It manages relationships between sites and translations, but it doesn't change the content.

= Will Multilanguage translate the content automatically? =

No, it will not. All translations must be done manually.

= Can I add more than 76 ​​pre-installed languages? =

Yes, you can add any number of languages to the list of pre-installed languages in file "include/languages.php".

= How can I change content display language? =

You can change content display language through the widget, which is activated automatically when you activate the plugin if you have an active sidebar. If you have several active sidebars, the widget is added to the sidebar, which features a search widget. But if you do not have an active sidebar, you need to create it and add Multilanguage widget manually.

= When changing content display language, will WordPress localization in the frontend be changed as well?  =

In the plugin settings, you can enable/disable an option to switch Wordpress localization when the language is changed in the frontend. If you enable this option, you must install additional language packs for Wordpress to have this function working. However, in the backend, Wordpress localization corresponds to the default language, and cannot be changed.

= Where can I get additional Wordpress language packs? =

You can find all official translation files in WordPress [language repository](http://i18n.svn.wordpress.org/).

= Can I use Multilanguage on a single-site? =

Yes, you can use Multilanguage in a single-site without any problems.

= Can I use Multilanguage on a multi-site? =

Yes, you can use Multilanguage on a multi-site without any problems as well. The plugin settings on each site will be unique.

= When I try to update the post after editing, changes are not saved if I have not filled the title and content fields. Why? =

When Title and Content fields in active language tab are empty, if you try to update the post after editing, the changes will not be saved and an error message will appear. You need to fill at least one field or switch to another language tab, on which the fields are filled, and try to update again.

= I have some problems with the plugin's work. What Information should I provide to receive proper support? =

Please make sure that the problem hasn't been discussed yet on our forum (<a href="http://support.bestwebsoft.com" target="_blank">http://support.bestwebsoft.com</a>). If no, please provide the following data along with your problem's description:

1. the link to the page where the problem occurs
2. the name of the plugin and its version. If you are using a pro version - your order number.
3. the version of your WordPress installation
4. copy and paste into the message your system status report. Please read more here: <a href="https://docs.google.com/document/d/1Wi2X8RdRGXk9kMszQy1xItJrpN0ncXgioH935MaBKtc/edit" target="_blank">Instruction on System Status</a>

== Screenshots ==

1. Plugin setting page with a table of languages ​​that have been added to the translation.
2. Plugin setting page with a form to add a language.
3. Plugin setting page with the main plugin settings.
4. Post/page editor with translation tabs.
5. Translation form for adding a new category.
6. Translation form for editing a category.
7. Translation form for adding a new tag.
8. Adding and settings language switcher widget in admin panel.
9. Language switcher widget in frontend (Drop-down languages list).
10. Language switcher widget in frontend (Drop-down flag icons).
11. Language switcher widget in frontend (Flag icons).

== Changelog ==

= V1.1.2 - 29.09.2015 =
* Bugfix : We fixed the bug with missing argument 2 for mltlngg_the_title_filter.
* NEW : A button for Multilanguage shortcode inserting to the content was added.
* NEW : Ability to restore settings to defaults.
* NEW : Ability to default searching by the language which is currently selected for the site or all available languages.

= V1.1.1 - 17.08.2015 =
* New : We added new languages.

= V1.1.0 - 10.07.2015 =
* Bugfix : We fixed not saving data error when switching between tabs.

= V1.0.9 - 11.06.2015 =
* Bugfix : We added post updating when the plugin is deactivated for some time.
* Bugfix : We fixed the bug with adding Russian language.

= V1.0.8 - 29.04.2015 =
* Bugfix : We fixed the bug when using the plugin with the php version less that 5.3.
* Bugfix : We fixed the bug when using editor in the frontend.
* Bugfix : We fixed the bug with read more tag when using it on pages.

= V1.0.7 - 21.04.2015 =
* NEW : The Polish language file is added.
* Bugfix : A bug with displaying video was fixed.
* Bugfix : A bug with compatibility with the Promobar plugin was fixed.
* Bugfix : Page address changing bug when and address features letter combinations from the translation slug was fixed.
* Bugfix : Display of categories translation in the Dashboard was fixed.
* Bugfix : Access to the files in WP root directory bug was fixed.
* Bugfix : Excerpt translation bug was fixed.
* Bugfix : A bug with displaying messages when language chart is being edited was fixed.
* Bugfix : A bug with compatibility with  Booking System (Booking Calendar) plugin was fixed.
* Bugfix : A bug with uploading images in NextGEN Gallery plugin when working with  Multilanguage was fixed.
* Update : An image for en_US was changed for the USA flag, and en_GB language was added.
* Update : We updated all functionality for wordpress 4.2-RC2.

= V1.0.6 - 24.02.2015 =
* NEW: We added shortcode and the strings to paste into the template source code.
* Bugfix : Fixed pagination for default permalinks.
* Bugfix : Fixed author link for default permalinks.

= V1.0.5 - 19.01.2015 =
* Bugfix : Bugs with page title is fixed.

= V1.0.4 - 14.01.2015 =
* Bugfix : Private and password protected posts display was fixed.
* Bugfix : We fixed errors while using <!--more-->.
* Bugfix : Bugs that occurred if SERVER_PORT was present in the website address were fixed.

= V1.0.3 - 26.12.2014 =
* Bugfix : Error that occurs when WP is installed in a subfolder was fixed.

= V1.0.2 - 22.12.2014 =
* Bugfix : We fixed errors while front page displays a static page.
* Bugfix : We removed constant __DIR__.
* Update : We updated all functionality for wordpress 4.1.

= V1.0.1 - 30.10.2014 =
* Bugfix : We fixed errors while saving custom posts.

= V1.0.0 - 22.10.2014 =
* Release date of Multilanguage.

== Upgrade Notice ==

= V1.1.2 =
We fixed the bug with missing argument 2 for mltlngg_the_title_filter. A button for Multilanguage shortcode inserting to the content was added. Ability to restore settings to defaults. Ability to default searching by the language which is currently selected for the site or all available languages.

= V1.1.1 =
We added new languages.

= V1.1.0 =
We fixed not saving data error when switching between tabs.

= V1.0.9 =
We added post updating when the plugin is deactivated for some time. We fixed the bug with adding Russian language.

= V1.0.8 =
We fixed the bug when using the plugin with the php version less that 5.3. We fixed the bug when using editor in the frontend. We fixed the bug with read more tag when using it on pages.

= V1.0.7 =
The Polish language file is added. A bug with displaying video was fixed. A bug with compatibility with the Promobar plugin was fixed. Page address changing bug when and address features letter combinations from the translation slug was fixed. Display of categories translation in the Dashboard was fixed. Access to the files in WP root directory bug was fixed. Excerpt translation bug was fixed. A bug with displaying messages when language chart is being edited was fixed. A bug with compatibility with Booking System (Booking Calendar) plugin was fixed. A bug with uploading images in NextGEN Gallery plugin when working with Multilanguage was fixed. An image for en_US was changed for the USA flag, and en_GB language was added. We updated all functionality for wordpress 4.2-RC2.

= V1.0.6 =
We added shortcode and the strings to paste into the template source code. Fixed pagination for default permalinks. Fixed author link for default permalinks.

= V1.0.5 =
Bugs with page title is fixed.

= V1.0.4 =
Private and password protected posts display was fixed. We fixed errors while using <!--more-->. Bugs that occurred if SERVER_PORT was present in the website address were fixed.

= V1.0.3 =
Error that occurs when WP is installed in a subfolder was fixed.

= V1.0.2 =
We fixed errors while front page displays a static page. We removed constant __DIR__. We updated all functionality for wordpress 4.1.

= V1.0.1 =
We fixed errors while saving custom posts.

= V1.0.0 =
* Release date of Multilanguage.
