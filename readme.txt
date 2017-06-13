=== Multilanguage by BestWebSoft ===
Contributors: bestwebsoft
Donate link: https://bestwebsoft.com/donate/
Tags: add translation, bilingual, multilanguage, multilanguage plugin, translate, translate posts, international, international plugin, multilingual, language switcher, switcher, translation-ready
Requires at least: 3.9
Tested up to: 4.8
Stable tag: 1.2.4
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Translate WordPress website content to other languages manually. Create multilingual pages, posts, widgets, menus, etc.

== Description ==

Multilanguage plugin is a great way to translate your WordPress website to other languages. Add translated content to pages, posts, widgets, menus, custom post types, taxonomies, etc. Let your visitors switch languages and browse content in their language.

Create and manage your multilingual website today!

**NOTE: Plugin does NOT translate texts automatically!** It works with themes and plugins developed according to WordPress Codex standards. It uses standard filters (the_title, the_content, get_terms, get_the_terms, get_term, wp_get_nav_menu_items).

http://www.youtube.com/watch?v=Si6ulEWuY1E

= Free Features =

* Manually translate:
	* Pages
	* Posts
	* Post categories
	* Post tags
	* Menus (partially)
* 80+ pre-installed languages
* Add new languages
* Choose the default language
* Search website content by:
	* Current language
	* All languages
* Add language switcher to:
	* Navigation menu
	* Widgets
* Change display order in the language switcher
* Multiple language switcher layouts
	* Drop-down list with languages and icons
	* Drop-down flag icons
	* Flag icons
	* Languages list
* Choose language flag icon:
	* Default
	* Custom
* Display translation availability in the posts and taxonomy lists
* Add hreflang links to <head> section
* Hide link slug for the default language
* Translation-ready admin dashboard
* Add custom code via plugin settings page
* Compatible with latest WordPress version
* Incredibly simple settings for fast setup without modifying code
* Detailed step-by-step documentation and videos
* Multilingual and RTL ready

> **Pro Features**
>
> All features from Free version included plus:
>
> * Translate custom:
> 	* Post types
> 	* Taxonomies
> 	* Fields
> * Translate:
> 	* Widget titles
> 	* Website title
> 	* Website tagline
> * Create separate menus for different languages
> * Display/hide widgets for different languages
> * Switch language automatically based on user’s IP
> * Add short language code version to the URL
> * Get answer to your support question within one business day ([Support Policy](https://bestwebsoft.com/support-policy/))
>
> [Upgrade to Pro Now](https://bestwebsoft.com/products/wordpress/plugins/multilanguage/?k=1d4576a3a2c4fc0f127ce2ee0341d81b)

If you have a feature suggestion or idea you'd like to see in the plugin, we'd love to hear about it! [Suggest a Feature](https://support.bestwebsoft.com/hc/en-us/requests/new)

= Documentation & Videos =

* [[Doc] Installation](https://docs.google.com/document/d/1-hvn6WRvWnOqj5v5pLUk7Awyu87lq5B_dO-Tv-MC9JQ/)
* [[Doc] How to use](https://docs.google.com/document/d/1y_c25pWDedi4FghjWj7W2Qleb-JsC10fGFinw4hy8T0/)

= Help & Support =

Visit our Help Center if you have any questions, our friendly Support Team is happy to help — <https://support.bestwebsoft.com/>

= Translation =

* Czech (cs_CZ) (thanks to [PaMaDeSSoft](mailto:info@pamadessoft.cz), www.pamadessoft.cz)
* Polish (pl_PL) (thanks to [Emil Król](mailto:emiljo@tlen.pl))
* Russian (ru_RU)
* Ukrainian (uk)

Some of these translations are not complete. We are constantly adding new features which should be translated. If you would like to create your own language pack or update the existing one, you can send [the text of PO and MO files](http://codex.wordpress.org/Translating_WordPress) to [BestWebSoft](https://support.bestwebsoft.com/hc/en-us/requests/new) and we'll add it to the plugin. You can download the latest version of the program for work with PO and MO [files Poedit](http://www.poedit.net/download.php).

= Recommended Plugins =

* [Updater](https://bestwebsoft.com/products/wordpress/plugins/updater/?k=3a06ca59d129f65a2259ac56620ce27e) - Automatically check and update WordPress website core with all installed plugins and themes to the latest versions.
* [PDF & Print](https://bestwebsoft.com/products/wordpress/plugins/pdf-print/?k=e7f954c2040303cfe69904409d8ba2ed) - Generate PDF files and print WordPress posts/pages. Customize document header/footer styles and appearance.

== Installation ==

1. Upload the `multilanguage` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin using the 'Plugins' menu in your WordPress admin panel.
3. You can adjust the necessary settings using your WordPress admin panel in "BWS Panel" > "Multilanguage".
4. Plugin page is located in main menu.

[View a Step-by-step Instruction on Multilanguage Installation](https://docs.google.com/document/d/1-hvn6WRvWnOqj5v5pLUk7Awyu87lq5B_dO-Tv-MC9JQ/)

== Frequently Asked Questions ==

= Does Multilanguage plugin translate website content automatically? =

No, it does not. It allows to manage relationships between website and translations, but it doesn’t change the content.

= How do I change the language my content is displayed in? =

You can change content display language through the widget, which is activated automatically when you activate the plugin if you have an active sidebar. If you have several active sidebars, the widget is added to the sidebar, which features a search widget. But if you do not have an active sidebar, you need to create it and add Multilanguage widget manually.

= Where do I get these language packs of the extra languages? =

You can find all official translation files in [WordPress language repository](http://i18n.svn.wordpress.org/).

= So whenever I change the language for my content, WordPress localization in the frontend also changes? =

In the plugin settings, you can enable/disable an option to switch WordPress localization when the language is changed in the frontend. If you enable this option, you must install additional language packs for WordPress to have this function working. However, in the backend, WordPress localization corresponds to the default language, and cannot be changed.

= There is no adding block for Multilanguage switcher in Edit Menus =

It seems like this block is disabled in Screen Options of your page. Please open Screen Options on the page (in the upper right corner of the screen) and enable Multilanguage checkbox.

= Can I use Multilanguage plugin on a multisite? =

Yes, you can use Multilanguage on a multi-site without any problems as well. The plugin settings on each site will be unique.

= How can I add more alternate links for current page? =

You can use 'bwsplgns_mltlngg_add_alt_links' filter to do that. For example, you want that search engines firstly indexed exactly English-language pages of your site for people from New Zealand and South Africa. Just add the following code into "functions.php" of your theme:

`function get_additional_links( $links_attr ) {
	/* English for New Zealand */
	$links_attr[] = array( 'hreflang' => 'en_NZ', 'link_param' => 'en_GB' );
	/* English for South Africa */
	$links_attr[] = array( 'hreflang' => 'en_ZA', 'link_param' => 'en_GB' );
	return $links_attr;
}
add_filter( 'bwsplgns_mltlngg_add_alt_links', 'get_additional_links' );`

Note: before you have to choose English (for England) as one of languages of your website.

= I added several whitespaces between words during the post content editing in the text mode. They are gone after I switched to the visual mode. Why? =

After the mode switching, the post content has been automatically filtered to remove all odd symbols or HTML tags which can disrupt pages layout on your website. This functionality is related to WordPress core and our plugin does not affect it in any way.

= When title and content is empty, none of the changes get saved when I update the post =

When title and content fields in active language tab are empty when you are trying to update the post after editing, the changes will not be saved and an error message will appear. You need to fill at least one field or switch to another language tab (which contains some content in the specified input fields) and try to update once again.

= How can I change element's style in the frontend for certain language? =

"mltlngg-{current language slug}" class type has been added to <body> tag for an ability to stylize content of certain language. For example, if you want to set a green color for <p> when the English language is selected, it is necessary to:
1. Go to WordPress admin panel "Multilanguage" -> "Custom Code";
2. Check off "Activate" in "Editing bws-custom-code.css" block and enter the following code:
`.mltlngg-en_US p {
	color: green;
}`
3. Save changes.

= I have some problems with the plugin's work. What Information should I provide to receive proper support? =

Please make sure that the problem hasn't been discussed yet on our forum (<https://support.bestwebsoft.com>). If no, please provide the following data along with your problem's description:
- The link to the page where the problem occurs
- The name of the plugin and its version. If you are using a pro version - your order number.
- The version of your WordPress installation
- Copy and paste into the message your system status report. Please read more here: [Instruction on System Status](https://docs.google.com/document/d/1Wi2X8RdRGXk9kMszQy1xItJrpN0ncXgioH935MaBKtc/)

== Screenshots ==

1. Language switcher widget in the frontend.
2. Language switcher in the site menu.
3. Plugin settings page.
4. Plugin languages page.
5. Edit language page.
6. Post/page editor with translation tabs.
7. Translation form for adding a new category.
8. Translation form for editing a category.
9. Translation form for adding a new tag.
10. Adding and configuring language switcher widget in the admin panel.
11. Adding language switcher into site menu.

== Changelog ==

= V1.2.4 - 13.06.2017 =
* Update : The list of languages has been updated.
* Update : Czech language file has been updated.
* Bugfix : Bug with website's default language switching has been fixed.
* Bugfix : Bug with website search has been fixed.
* Bugfix : Bug with hidden current language tab during post editing has been fixed.
* Bugfix : Yoast SEO compatibility issue has been fixed.
* Bugfix : WP-CLI compatibility issue has been fixed.

= V1.2.3 - 24.03.2017 =
* Bugfix : Bug with hiding link slug for the default language was fixed.
* Update : Czech language file was updated.

= V1.2.2 - 22.03.2017 =
* NEW: Ability to change a language flag icon was added.
* NEW: New language switcher type was added.
* Update : The plugin settings page has been updated.
* NEW: Translation Saving Mode was added.
* Bugfix : Bugs with search were fixed.
* Bugfix : Wrong link formation bug in the menu Language switcher was fixed.

= V1.2.1 - 14.12.2016 =
* NEW: Compatibility with Visual Composer Backend Editor and Divi builder was added.
* PRO: Compatibility with Page Builder by SiteOrigin was added.

= V1.2.0 - 30.09.2016 =
* NEW : The arabic language file was added.

= V1.1.9 - 18.08.2016 =
* NEW: Compatibility with PDF & Print by BestWebSoft plugin.
* Pro: Ability to translate custom fields.

= V1.1.8 - 15.07.2016 =
* Update : BWS panel section was updated.

= V1.1.7 - 28.06.2016 =
* Bugfix : Bug with displaying the list of languages was fixed.
* NEW : "mltlngg-{current language slug}" class type has been added to <body> tag for an ability to stylize content of certain language.

= V1.1.6 - 30.05.2016 =
* NEW : Ability to display aligned languages switcher in the site menu has been added.
* Bugfix : Bug with displaying alternative page links was fixed.
* Bugfix : The conflict with WooCommerce REST API was resolved.

= V1.1.5 - 04.04.2016 =
* NEW : Ability to add languages switcher into site menu.
* NEW : The column which displays translation availability has been added to the posts and taxonomies list.
* Update : Displaying Language switcher widget as Drop-down languages list was changed.
* NEW : Ability to add custom styles.
* NEW : The Czech language file was added.
* Update : Saving data for languages in admin panel has been improved.

= V1.1.4 - 22.02.2016 =
* NEW : Ability to add links to your current page for each language from your site into the tag <head> has been added.
* NEW : Ability to hide link slug for default language has been added.
* Update : Widget with languages switcher has been updated.

= V1.1.3 - 04.12.2015 =
* Bugfix : The bug with default searching was fixed.
* Bugfix : The bug with plugin menu duplicating was fixed.

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

= V1.2.4 =
* Bugs fixed.
* Languages updated.

= V1.2.3 =
* Bugs fixed.

= V1.2.2 =
* Functionality expanded.
* Usability improved.
* Bugs fixed.

= V1.2.1 =
* Functionality expanded.

= V1.2.0 =
* New languages added.

= V1.1.9 =
* Functionality expanded.

= V1.1.8 =
* Usability improved.

= V1.1.7 =
Bug with displaying the list of languages was fixed. "mltlngg-{current language slug}" class type has been added to <body> tag for an ability to stylize content of certain language.

= V1.1.6 =
Ability to display aligned languages switcher in the site menu has been added. Bug with displaying alternative page links was fixed. The conflict with WooCommerce REST API was resolved.

= V1.1.5 =
Ability to add languages switcher into site menu. The column which displays translation availability has been added to the posts and taxonomies list. Displaying Language switcher widget as Drop-down languages list was changed. Ability to add custom styles. The Czech language file was added. Saving data for languages in admin panel has been improved.

= V1.1.4 =
Ability to add links to your current page for each language from your site into the tag <head> has been added. Ability to hide link slug for default language has been added. Widget with languages switcher has been updated.

= V1.1.3 =
The bug with default searching was fixed. The bug with plugin menu duplicating was fixed.

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
