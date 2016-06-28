<?php
/*
* BestWebSoft product list
*/

$bws_plugins_category = array(
	'advertisement' 	=> __( 'Advertisement', 'bestwebsoft' ),
	'content-and-media' => __( 'Content & Media', 'bestwebsoft' ),
	'management' 		=> __( 'Management', 'bestwebsoft' ),
	'security'			=> __( 'Security', 'bestwebsoft' ),
	'site-stats'		=> __( 'Site Stats', 'bestwebsoft' ),
	'social'			=> __( 'Social', 'bestwebsoft' ),
	'utilities'			=> __( 'Utilities', 'bestwebsoft' ),
	'other'				=> __( 'Other', 'bestwebsoft' )
);

$bws_plugins = array(
	'captcha/captcha.php' => array(
		'category'		=> 'security',
		'name'			=> 'Captcha',
		'description'	=> 'Plugin intended to prove that the visitor is a human being and not a spam robot.',
		'link'			=> 'http://bestwebsoft.com/products/captcha/?k=d678516c0990e781edfb6a6c874f0b8a&pn=' . $bws_plugin_info["id"] . '&v=' . $bws_plugin_info["version"] . '&wp_v=' . $wp_version,
		'settings'		=> 'admin.php?page=captcha.php',
		'pro_version'	=> 'captcha-pro/captcha_pro.php',
		'purchase'		=> 'http://bestwebsoft.com/products/captcha/buy/?k=ff7d65e55e5e7f98f219be9ed711094e&pn=' . $bws_plugin_info["id"] . '&v=' . $bws_plugin_info["version"] . '&wp_v=' . $wp_version,
		'pro_settings'	=> 'admin.php?page=captcha_pro.php'
	),
	'car-rental/car-rental.php' => array(
		'category'		=> 'content-and-media',
		'name'			=> 'Car Rental',
		'description'	=> 'A convenient plugin that adds Car Rental functionality.',
		'link'			=> 'http://bestwebsoft.com/products/car-rental/?k=444cac6df9a0d3a9763ab4753d24941b&pn=' . $bws_plugin_info["id"] . '&v=' . $bws_plugin_info["version"] . '&wp_v=' . $wp_version,
		'settings'		=> 'admin.php?page=car-rental-settings',
		'pro_version'	=> 'car-rental-pro/car-rental-pro.php',
		'purchase'		=> 'http://bestwebsoft.com/products/car-rental/buy/?k=7643d4f0698252fa1159de078d22269c&pn=' . $bws_plugin_info["id"] . '&v=' . $bws_plugin_info["version"] . '&wp_v=' . $wp_version,
		'pro_settings'	=> 'admin.php?page=car-rental-pro-settings'
	),
	'contact-form-plugin/contact_form.php' => array(
		'category'		=> 'other',
		'name'			=> 'Contact Form',
		'description'	=> 'Add Contact Form to your WordPress website.',
		'link'			=> 'http://bestwebsoft.com/products/contact-form/?k=012327ef413e5b527883e031d43b088b&pn=' . $bws_plugin_info["id"] . '&v=' . $bws_plugin_info["version"] . '&wp_v=' . $wp_version,
		'settings'		=> 'admin.php?page=contact_form.php',
		'pro_version'	=> 'contact-form-pro/contact_form_pro.php',
		'purchase'		=> 'http://bestwebsoft.com/products/contact-form/buy/?k=773dc97bb3551975db0e32edca1a6d71&pn=' . $bws_plugin_info["id"] . '&v=' . $bws_plugin_info["version"] . '&wp_v=' . $wp_version,
		'pro_settings'	=> 'admin.php?page=contact_form_pro.php'
	),
	'contact-form-multi/contact-form-multi.php' => array(
		'category'		=> 'other',
		'name'			=> 'Contact Form Multi',
		'description'	=> 'Add-on to the Contact Form plugin that allows to create and implement multiple contact forms.',
		'link'			=> 'http://bestwebsoft.com/products/contact-form-multi/?k=83cdd9e72a9f4061122ad28a67293c72&pn=' . $bws_plugin_info["id"] . '&v=' . $bws_plugin_info["version"] . '&wp_v=' . $wp_version,
		'settings'		=> '',
		'pro_version'	=> 'contact-form-multi-pro/contact-form-multi-pro.php',
		'purchase' 		=> 'http://bestwebsoft.com/products/contact-form-multi/buy/?k=fde3a18581c143654f060c398b07e8ac&pn=' . $bws_plugin_info["id"] . '&v=' . $bws_plugin_info["version"] . '&wp_v=' . $wp_version,
		'pro_settings' 	=> ''
	),
	'contact-form-to-db/contact_form_to_db.php' => array(
		'category'		=> 'other',
		'name'			=> 'Contact Form to DB',
		'description'	=> 'Allows you to manage the messages that have been sent from your site.',
		'link'			=> 'http://bestwebsoft.com/products/contact-form-to-db/?k=ba3747d317c2692e4136ca096a8989d6&pn=' . $bws_plugin_info["id"] . '&v=' . $bws_plugin_info["version"] . '&wp_v=' . $wp_version,
		'settings'		=> 'admin.php?page=cntctfrmtdb_settings',
		'pro_version'	=> 'contact-form-to-db-pro/contact_form_to_db_pro.php',
		'purchase' 		=> 'http://bestwebsoft.com/products/contact-form-to-db/buy/?k=6ce5f4a9006ec906e4db643669246c6a&pn=' . $bws_plugin_info["id"] . '&v=' . $bws_plugin_info["version"] . '&wp_v=' . $wp_version,
		'pro_settings' 	=> 'admin.php?page=cntctfrmtdbpr_settings'
	),
	'custom-fields-search/custom-fields-search.php' => array(
		'category'		=> 'content-and-media',
		'name'			=> 'Custom Fields Search',
		'description'	=> 'Allows you to add website search any existing custom fields.',
		'link'			=> 'http://bestwebsoft.com/products/custom-fields-search/?k=f3f8285bb069250c42c6ffac95ed3284&pn=' . $bws_plugin_info["id"] . '&v=' . $bws_plugin_info["version"] . '&wp_v=' . $wp_version,
		'settings'		=> 'admin.php?page=custom_fields_search.php'
	),
	'custom-search-plugin/custom-search-plugin.php' => array(
		'category'		=> 'content-and-media',
		'name'			=> 'Custom Search',
		'description'	=> 'Allows to extend your website search functionality by adding a custom post type.',
		'link'			=> 'http://bestwebsoft.com/products/custom-search/?k=933be8f3a8b8719d95d1079d15443e29&pn=' . $bws_plugin_info["id"] . '&v=' . $bws_plugin_info["version"] . '&wp_v=' . $wp_version,
		'settings'		=> 'admin.php?page=custom_search.php',
		'pro_version'	=> 'custom-search-pro/custom-search-pro.php',
		'purchase'		=> 'http://bestwebsoft.com/products/custom-search/buy/?k=062b652ac6ac8ba863c9f30fc21d62c6&pn=' . $bws_plugin_info["id"] . '&v=' . $bws_plugin_info["version"] . '&wp_v=' . $wp_version,
		'pro_settings'	=> 'admin.php?page=custom_search_pro.php'
	),	
	'db-manager/db-manager.php' => array(
		'category'		=> 'utilities',
		'name'			=> 'DB Manager',
		'description'	=> 'Allows you to download the latest version of PhpMyadmin and Dumper and manage your site.',
		'link'			=> 'http://bestwebsoft.com/products/db-manager/?k=01ed9731780d87f85f5901064b7d76d8&pn=' . $bws_plugin_info["id"] . '&v=' . $bws_plugin_info["version"] . '&wp_v=' . $wp_version,
		'install_url'	=> 'http://bestwebsoft.com/products/db-manager/download/?k=01ed9731780d87f85f5901064b7d76d8&pn=' . $bws_plugin_info["id"] . '&v=' . $bws_plugin_info["version"] . '&wp_v=' . $wp_version,
		'settings'		=> 'admin.php?page=db-manager.php',
		'icon'			=> plugins_url( 'icons/plugins/', __FILE__ ) . 'db-manager.png'
	),
	'donate-button/donate.php' => array(
		'category'		=> 'other',
		'name'			=> 'Donate',
		'description'	=> 'Makes it possible to place donation buttons of various payment systems on your web page.',
		'link'			=> 'http://bestwebsoft.com/products/donate/?k=a8b2e2a56914fb1765dd20297c26401b&pn=' . $bws_plugin_info["id"] . '&v=' . $bws_plugin_info["version"] . '&wp_v=' . $wp_version,
		'settings'		=> 'admin.php?page=donate.php'
	),
	'email-queue/email-queue.php' => array(
		'category'		=> 'other',
		'name'			=> 'Email Queue',
		'description'	=> 'Allows to manage email massages sent by BestWebSoft plugins.',
		'link'			=> 'http://bestwebsoft.com/products/email-queue/?k=e345e1b6623f0dca119bc2d9433b130b&pn=' . $bws_plugin_info["id"] . '&v=' . $bws_plugin_info["version"] . '&wp_v=' . $wp_version,
		'settings'		=> 'admin.php?page=mlq_settings'
	),
	'error-log-viewer/error-log-viewer.php' => array(
		'category'		=> 'utilities',
		'name'			=> 'Error Log Viewer',
		'description'	=> "Work with log files and folders on the WordPress server",
		'link'			=> 'http://bestwebsoft.com/products/error-log-viewer/?k=da0de8bd2c7a0b2fea5df64d55a368b3&pn=' . $bws_plugin_info["id"] . '&v=' . $bws_plugin_info["version"] . '&wp_v=' . $wp_version,
		'settings'		=> 'admin.php?page=rrrlgvwr.php&tab=settings'
	),
	'facebook-button-plugin/facebook-button-plugin.php' => array(
		'category'		=> 'social',
		'name'			=> 'Facebook Button',
		'description'	=> 'Allows you to add the Follow and Like buttons the easiest way.',
		'link'			=> 'http://bestwebsoft.com/products/facebook-like-button/?k=05ec4f12327f55848335802581467d55&pn=' . $bws_plugin_info["id"] . '&v=' . $bws_plugin_info["version"] . '&wp_v=' . $wp_version,
		'settings'		=> 'admin.php?page=facebook-button-plugin.php',
		'pro_version'	=> 'facebook-button-pro/facebook-button-pro.php',
		'purchase' 		=> 'http://bestwebsoft.com/products/facebook-like-button/buy/?k=8da168e60a831cfb3525417c333ad275&pn=' . $bws_plugin_info["id"] . '&v=' . $bws_plugin_info["version"] . '&wp_v=' . $wp_version,
		'pro_settings' 	=> 'admin.php?page=facebook-button-pro.php'
	),
	'bws-featured-posts/bws-featured-posts.php' => array(
		'category'		=> 'content-and-media',
		'name'			=> 'Featured Posts',
		'description'	=> 'Displays featured posts randomly on any website page.',
		'link'			=> 'http://bestwebsoft.com/products/featured-posts/?k=f0afb31185ba7c7d6d598528d69f6d97&pn=' . $bws_plugin_info["id"] . '&v=' . $bws_plugin_info["version"] . '&wp_v=' . $wp_version,
		'settings'		=> 'admin.php?page=featured-posts.php'
	),		
	'gallery-plugin/gallery-plugin.php' => array(
		'category'		=> 'content-and-media',
		'name'			=> 'Gallery',
		'description'	=> 'Allows you to implement a Gallery page into your website.',
		'link'			=> 'http://bestwebsoft.com/products/gallery/?k=2da21c0a64eec7ebf16337fa134c5f78&pn=' . $bws_plugin_info["id"] . '&v=' . $bws_plugin_info["version"] . '&wp_v=' . $wp_version,
		'settings'		=> 'admin.php?page=gallery-plugin.php',
		'pro_version'	=> 'gallery-plugin-pro/gallery-plugin-pro.php',
		'purchase'		=> 'http://bestwebsoft.com/products/gallery/buy/?k=382e5ce7c96a6391f5ffa5e116b37fe0&pn=' . $bws_plugin_info["id"] . '&v=' . $bws_plugin_info["version"] . '&wp_v=' . $wp_version,
		'pro_settings'	=> 'admin.php?page=gallery-plugin-pro.php'
	),
	'gallery-categories/gallery-categories.php' => array(
		'category'		=> 'content-and-media',
		'name'			=> 'Gallery Categories',
		'description'	=> 'Add-on for Gallery Plugin by BestWebSoft',
		'link'			=> 'http://bestwebsoft.com/products/gallery-categories/?k=7d68c7bfec2486dc350c67fff57ad433&pn=' . $bws_plugin_info["id"] . '&v=' . $bws_plugin_info["version"] . '&wp_v=' . $wp_version,
		'settings'		=> ''
	),
	'google-one/google-plus-one.php' => array(
		'category'		=> 'social',
		'name'			=> 'Google +1',
		'description'	=> 'Allows you to see how many times your page has been liked on Google Search Engine as well as who has liked the article.',
		'link'			=> 'http://bestwebsoft.com/products/google-plus-one/?k=ce7a88837f0a857b3a2bb142f470853c&pn=' . $bws_plugin_info["id"] . '&v=' . $bws_plugin_info["version"] . '&wp_v=' . $wp_version,
		'settings'		=> 'admin.php?page=google-plus-one.php',
		'pro_version'	=> 'google-one-pro/google-plus-one-pro.php',
		'purchase' 		=> 'http://bestwebsoft.com/products/google-plus-one/buy/?k=f4b0a62d155c9df9601a0531ad5bd832&pn=' . $bws_plugin_info["id"] . '&v=' . $bws_plugin_info["version"] . '&wp_v=' . $wp_version,
		'pro_settings' 	=> 'admin.php?page=google-plus-one-pro.php'
	),
	'adsense-plugin/adsense-plugin.php' => array(
		'category'		=> 'advertisement',
		'name'			=> 'Google AdSense',
		'description'	=> 'Allows Google AdSense implementation to your website.',
		'link'			=> 'http://bestwebsoft.com/products/google-adsense/?k=60e3979921e354feb0347e88e7d7b73d&pn=' . $bws_plugin_info["id"] . '&v=' . $bws_plugin_info["version"] . '&wp_v=' . $wp_version,
		'settings'		=> 'admin.php?page=adsense-plugin.php',
		'pro_version'	=> 'adsense-pro/adsense-pro.php',
		'purchase'		=> 'http://bestwebsoft.com/products/google-adsense/buy/?k=c23889b293d62aa1ad2c96513405f0e1&pn=' . $bws_plugin_info["id"] . '&v=' . $bws_plugin_info["version"] . '&wp_v=' . $wp_version,
		'pro_settings'	=> 'admin.php?page=adsense-pro.php'
	),
	'bws-google-analytics/bws-google-analytics.php' => array(
		'category'		=> 'site-stats',
		'name'			=> 'Google Analytics',
		'description'	=> 'Allows you to retrieve basic stats from Google Analytics account and add the tracking code to your blog.',
		'link'			=> 'http://bestwebsoft.com/products/bws-google-analytics/?k=261c74cad753fb279cdf5a5db63fbd43&pn=' . $bws_plugin_info["id"] . '&v=' . $bws_plugin_info["version"] . '&wp_v=' . $wp_version,
		'settings'		=> 'admin.php?page=bws-google-analytics.php',
		'pro_version'	=> 'bws-google-analytics-pro/bws-google-analytics-pro.php',
		'purchase' 		=> 'http://bestwebsoft.com/products/bws-google-analytics/buy/?k=83796e84fec3f70ecfcc8894a73a6c4a&pn=' . $bws_plugin_info["id"] . '&v=' . $bws_plugin_info["version"] . '&wp_v=' . $wp_version,
		'pro_settings' 	=> 'admin.php?page=bws-google-analytics-pro.php'
	),
	'google-captcha/google-captcha.php' => array(
		'category'		=> 'security',
		'name'			=> 'Google Captcha (reCAPTCHA)',
		'description'	=> 'Plugin intended to prove that the visitor is a human being and not a spam robot.',
		'link'			=> 'http://bestwebsoft.com/products/google-captcha/?k=7b59fbe542acf950b29f3e020d5ad735&pn=' . $bws_plugin_info["id"] . '&v=' . $bws_plugin_info["version"] . '&wp_v=' . $wp_version,
		'settings'		=> 'admin.php?page=google-captcha.php',
		'pro_version'	=> 'google-captcha-pro/google-captcha-pro.php',
		'purchase'		=> 'http://bestwebsoft.com/products/google-captcha/buy/?k=773d30149acf1edc32e5c0766b96c134&pn=' . $bws_plugin_info["id"] . '&v=' . $bws_plugin_info["version"] . '&wp_v=' . $wp_version,
		'pro_settings'	=> 'admin.php?page=google-captcha-pro.php'
	),
	'bws-google-maps/bws-google-maps.php' => array(
		'category'		=> 'content-and-media',
		'name'			=> 'Google Maps',
		'description'	=> 'Easy to set up and insert Google Maps to your website.',
		'link'			=> 'http://bestwebsoft.com/products/bws-google-maps/?k=d8fac412d7359ebaa4ff53b46572f9f7&pn=' . $bws_plugin_info["id"] . '&v=' . $bws_plugin_info["version"] . '&wp_v=' . $wp_version,
		'settings'		=> 'admin.php?page=bws-google-maps.php',
		'pro_version'	=> 'bws-google-maps-pro/bws-google-maps-pro.php',
		'purchase' 		=> 'http://bestwebsoft.com/products/bws-google-maps/buy/?k=117c3f9fc17f2c83ef430a8a9dc06f56&pn=' . $bws_plugin_info["id"] . '&v=' . $bws_plugin_info["version"] . '&wp_v=' . $wp_version,
		'pro_settings' 	=> 'admin.php?page=bws-google-maps-pro.php'
	),
	'google-sitemap-plugin/google-sitemap-plugin.php' => array(
		'category'		=> 'other',
		'name'			=> 'Google Sitemap',
		'description'	=> 'Allows you to add sitemap file to Google Webmaster Tools.',
		'link'			=> 'http://bestwebsoft.com/products/google-sitemap/?k=5202b2f5ce2cf85daee5e5f79a51d806&pn=' . $bws_plugin_info["id"] . '&v=' . $bws_plugin_info["version"] . '&wp_v=' . $wp_version,
		'settings'		=> 'admin.php?page=google-sitemap-plugin.php',
		'pro_version'	=> 'google-sitemap-pro/google-sitemap-pro.php',
		'purchase'		=> 'http://bestwebsoft.com/products/google-sitemap/buy/?k=7ea384a5cc36cb4c22741caa20dcd56d&pn=' . $bws_plugin_info["id"] . '&v=' . $bws_plugin_info["version"] . '&wp_v=' . $wp_version,
		'pro_settings'	=> 'admin.php?page=google-sitemap-pro.php'
	),	
	'google-shortlink/google-shortlink.php' => array(
		'category'		=> 'other',
		'name'			=> 'Google Shortlink',
		'description'	=> 'Allows you to get short links from goo.gl servise without leaving your site.',
		'link'			=> 'http://bestwebsoft.com/products/google-shortlink/?k=afcf3eaed021bbbbeea1090e16bc22db&pn=' . $bws_plugin_info["id"] . '&v=' . $bws_plugin_info["version"] . '&wp_v=' . $wp_version,
		'settings'		=> 'admin.php?page=gglshrtlnk_options'
	),	
	'htaccess/htaccess.php' => array(
		'category'		=> 'security',
		'name'			=> 'Htaccess',
		'description'	=> 'Allows controlling access to your website using the directives Allow and Deny.',
		'link'			=> 'http://bestwebsoft.com/products/htaccess/?k=2b865fcd56a935d22c5c4f1bba52ed46&pn=' . $bws_plugin_info["id"] . '&v=' . $bws_plugin_info["version"] . '&wp_v=' . $wp_version,
		'settings'		=> 'admin.php?page=htaccess.php',
		'pro_version'	=> 'htaccess-pro/htaccess-pro.php',
		'purchase' 		=> 'http://bestwebsoft.com/products/htaccess/buy/?k=59e9209a32864be534fda77d5e591c15&pn=' . $bws_plugin_info["id"] . '&v=' . $bws_plugin_info["version"] . '&wp_v=' . $wp_version,
		'pro_settings' 	=> 'admin.php?page=htaccess-pro.php'
	),
	'job-board/job-board.php' => array(
		'category'		=> 'content-and-media',
		'name'			=> 'Job Board',
		'description'	=> 'Allows to create a job-board page on your site.',
		'link'			=> 'http://bestwebsoft.com/products/job-board/?k=b0c504c9ce6edd6692e04222af3fed6f&pn=' . $bws_plugin_info["id"] . '&v=' . $bws_plugin_info["version"] . '&wp_v=' . $wp_version,
		'settings'		=> 'admin.php?page=job-board.php'
	),
	'limit-attempts/limit-attempts.php' => array(
		'category'		=> 'security',
		'name'			=> 'Limit Attempts',
		'description'	=> 'Allows you to limit rate of login attempts by the ip, and create whitelist and blacklist.',
		'link'			=> 'http://bestwebsoft.com/products/limit-attempts/?k=b14e1697ee4d008abcd4bd34d492573a&pn=' . $bws_plugin_info["id"] . '&v=' . $bws_plugin_info["version"] . '&wp_v=' . $wp_version,
		'settings'		=> 'admin.php?page=limit-attempts.php',
		'pro_version'	=> 'limit-attempts-pro/limit-attempts-pro.php',
		'purchase' 		=> 'http://bestwebsoft.com/products/limit-attempts/buy/?k=9d42cdf22c7fce2c4b6b447e6a2856e0&pn=' . $bws_plugin_info["id"] . '&v=' . $bws_plugin_info["version"] . '&wp_v=' . $wp_version,
		'pro_settings'	=> 'admin.php?page=limit-attempts-pro.php'
	),
	'bws-linkedin/bws-linkedin.php' => array(
		'category'		=> 'social',
		'name'			=> 'LinkedIn',
		'description'	=> "Add LinkedIn Widgets, 'Share' and 'Follow' Buttons in the easiest way.",
		'link'			=> 'http://bestwebsoft.com/products/linkedin/?k=d63c7319622ccc5f589dd2d545c1d77c&pn=' . $bws_plugin_info["id"] . '&v=' . $bws_plugin_info["version"] . '&wp_v=' . $wp_version,
		'settings'		=> 'admin.php?page=linkedin.php',
		'pro_version'	=> 'bws-linkedin-pro/bws-linkedin-pro.php',
		'purchase' 		=> 'http://bestwebsoft.com/products/linkedin/buy/?k=41dcc36192994408d24b103a02134567&pn=' . $bws_plugin_info["id"] . '&v=' . $bws_plugin_info["version"] . '&wp_v=' . $wp_version,
		'pro_settings'	=> 'admin.php?page=linkedin-pro.php'
	),
	'multilanguage/multilanguage.php' => array(
		'category'		=> 'content-and-media',
		'name'			=> 'Multilanguage',
		'description'	=> 'Allows to create content on a Wordpress site in different languages.',
		'link'			=> 'http://bestwebsoft.com/products/multilanguage/?k=7d68c7bfec2486dc350c67fff57ad433&pn=' . $bws_plugin_info["id"] . '&v=' . $bws_plugin_info["version"] . '&wp_v=' . $wp_version,
		'settings'		=> 'admin.php?page=mltlngg_settings',
		'pro_version'	=> 'multilanguage-pro/multilanguage-pro.php',
		'purchase' 		=> 'http://bestwebsoft.com/products/multilanguage/buy/?k=2d1121cd9a5ced583fc29eefd51bdf57&pn=' . $bws_plugin_info["id"] . '&v=' . $bws_plugin_info["version"] . '&wp_v=' . $wp_version,
		'pro_settings'	=> 'admin.php?page=mltlnggpr_settings'
	),	
	'pagination/pagination.php' => array(
		'category'		=> 'content-and-media',
		'name'			=> 'Pagination',
		'description'	=> 'Add pagination block to your WordPress website.',
		'link'			=> 'http://bestwebsoft.com/products/pagination/?k=22adb940256f149559ba8fedcd728ac8&pn=' . $bws_plugin_info["id"] . '&v=' . $bws_plugin_info["version"] . '&wp_v=' . $wp_version,
		'settings'		=> 'admin.php?page=pagination.php'
	),
	'pdf-print/pdf-print.php' => array(
		'category'		=> 'other',
		'name'			=> 'PDF & Print',
		'description'	=> 'Allows you to create PDF and Print page with adding appropriate buttons to the content.',
		'link'			=> 'http://bestwebsoft.com/products/pdf-print/?k=bfefdfb522a4c0ff0141daa3f271840c&pn=' . $bws_plugin_info["id"] . '&v=' . $bws_plugin_info["version"] . '&wp_v=' . $wp_version,
		'settings'		=> 'admin.php?page=pdf-print.php',
		'pro_version'	=> 'pdf-print-pro/pdf-print-pro.php',
		'purchase' 		=> 'http://bestwebsoft.com/products/pdf-print/buy/?k=fd43a0e659ddc170a9060027cbfdcc3a&pn=' . $bws_plugin_info["id"] . '&v=' . $bws_plugin_info["version"] . '&wp_v=' . $wp_version,
		'pro_settings' 		=> 'admin.php?page=pdf-print-pro.php'
	),
	'bws-pinterest/bws-pinterest.php' => array(
		'category'		=> 'social',
		'name'			=> 'Pinterest',
		'description'	=> 'Add Pinterest buttons and widgets to your WordPress website',
		'link'			=> 'http://bestwebsoft.com/products/pinterest/?k=504107b6213f247a67fe7ffb94e97c78&pn=' . $bws_plugin_info["id"] . '&v=' . $bws_plugin_info["version"] . '&wp_v=' . $wp_version,
		'settings'		=> 'admin.php?page=pinterest.php',
		'pro_version'	=> 'bws-pinterest-pro/bws-pinterest-pro.php',
		'purchase' 		=> 'http://bestwebsoft.com/products/pinterest/buy/?k=ab0069edd1914a3ca8f541bfd88bb0bb&pn=' . $bws_plugin_info["id"] . '&v=' . $bws_plugin_info["version"] . '&wp_v=' . $wp_version,
		'pro_settings'	=> 'admin.php?page=pinterest-pro.php'
	),
	'bws-popular-posts/bws-popular-posts.php' => array(
		'category'		=> 'content-and-media',
		'name'			=> 'Popular Posts',
		'description'	=> 'This plugin will help you can display the most popular posts on your blog in the widget.',
		'link'			=> 'http://bestwebsoft.com/products/popular-posts/?k=4d529f116d2b7f7df3a78018c383f975&pn=' . $bws_plugin_info["id"] . '&v=' . $bws_plugin_info["version"] . '&wp_v=' . $wp_version,
		'settings'		=> 'admin.php?page=popular-posts.php'
	),
	'portfolio/portfolio.php' => array(
		'category'		=> 'content-and-media',
		'name'			=> 'Portfolio',
		'description'	=> 'Allows you to create a page with the information about your past projects.',
		'link'			=> 'http://bestwebsoft.com/products/portfolio/?k=1249a890c5b7bba6bda3f528a94f768b&pn=' . $bws_plugin_info["id"] . '&v=' . $bws_plugin_info["version"] . '&wp_v=' . $wp_version,
		'settings'		=> 'admin.php?page=portfolio.php',
		'pro_version'	=> 'portfolio-pro/portfolio-pro.php',
		'purchase' 		=> 'http://bestwebsoft.com/products/portfolio/buy/?k=2cc716026197d36538a414b728e49fdd&pn=' . $bws_plugin_info["id"] . '&v=' . $bws_plugin_info["version"] . '&wp_v=' . $wp_version,
		'pro_settings' 	=> 'admin.php?page=portfolio-pro.php'
	),
	'post-to-csv/post-to-csv.php' => array(
		'category'		=> 'utilities',
		'name'			=> 'Post to CSV',
		'description'	=> 'The plugin allows to export posts of any types to a csv file.',
		'link'			=> 'http://bestwebsoft.com/products/post-to-csv/?k=653aa55518ae17409293a7a894268b8f&pn=' . $bws_plugin_info["id"] . '&v=' . $bws_plugin_info["version"] . '&wp_v=' . $wp_version,
		'settings'		=> 'admin.php?page=post-to-csv.php'
	),
	'profile-extra-fields/profile-extra-fields.php' => array(
		'category'		=> 'management',
		'name'			=> 'Profile Extra Fields',
		'description'	=> "Add additional fields on the user's profile page",
		'link'			=> 'http://bestwebsoft.com/products/profile-extra-fields/?k=fe3b6c3dbc80bd4b1cf9a27a2f339820&pn=' . $bws_plugin_info["id"] . '&v=' . $bws_plugin_info["version"] . '&wp_v=' . $wp_version,
		'settings'		=> 'admin.php?page=profile-extra-fields.php'
	),
	'promobar/promobar.php' => array(
		'category'		=> 'advertisement',
		'name'			=> 'PromoBar',
		'description'	=> 'This plugin allows placing banners with any data on your website.',
		'link'			=> 'http://bestwebsoft.com/products/promobar/?k=619eac2232d9cfa382c4e678c3b14766&pn=' . $bws_plugin_info["id"] . '&v=' . $bws_plugin_info["version"] . '&wp_v=' . $wp_version,
		'settings'		=> 'admin.php?page=promobar.php',
		'pro_version'	=> 'promobar-pro/promobar-pro.php',
		'purchase' 		=> 'http://bestwebsoft.com/products/promobar/buy/?k=a9b09708502f12a1483532ba12fe2103&pn=' . $bws_plugin_info["id"] . '&v=' . $bws_plugin_info["version"] . '&wp_v=' . $wp_version,
		'pro_settings'	=> 'admin.php?page=promobar-pro.php'
	),
	'quotes-and-tips/quotes-and-tips.php' => array(
		'category'		=> 'content-and-media',
		'name'			=> 'Quotes and Tips',
		'description'	=> 'Allows you to implement quotes & tips block into your web site.',
		'link'			=> 'http://bestwebsoft.com/products/quotes-and-tips/?k=5738a4e85a798c4a5162240c6515098d&pn=' . $bws_plugin_info["id"] . '&v=' . $bws_plugin_info["version"] . '&wp_v=' . $wp_version,
		'settings'		=> 'admin.php?page=quotes-and-tips.php'
	),
	'realty/realty.php' => array(
		'category'		=> 'content-and-media',
		'name'			=> 'Realty',
		'description'	=> 'A convenient plugin that adds Real Estate functionality.',
		'link'			=> 'http://bestwebsoft.com/products/realty/?k=d55de979dbbbb7af0b2ff1d7f43884fa&pn=' . $bws_plugin_info["id"] . '&v=' . $bws_plugin_info["version"] . '&wp_v=' . $wp_version,
		'settings'		=> 'admin.php?page=realty_settings',
		'pro_version'	=> 'realty-pro/realty-pro.php',
		'purchase' 		=> 'http://bestwebsoft.com/products/realty/buy/?k=c7791f0a72acfb36f564a614dbccb474&pn=' . $bws_plugin_info["id"] . '&v=' . $bws_plugin_info["version"] . '&wp_v=' . $wp_version,
		'pro_settings'	=> 'admin.php?page=realty_pro_settings'
	),	
	're-attacher/re-attacher.php' => array(
		'category'		=> 'utilities',
		'name'			=> 'Re-attacher',
		'description'	=> 'This plugin allows to attach, unattach or reattach media item in different post.',
		'link'			=> 'http://bestwebsoft.com/products/re-attacher/?k=4d529f116d2b7f7df3a78018c383f975&pn=' . $bws_plugin_info["id"] . '&v=' . $bws_plugin_info["version"] . '&wp_v=' . $wp_version,
		'settings'		=> 'admin.php?page=re-attacher.php'
	),
	'relevant/related-posts-plugin.php' => array(
		'category'		=> 'content-and-media',
		'name'			=> 'Relevant - Related Posts',
		'description'	=> 'Allows you to display related posts with similar words in category, tags, title or by adding special meta key for posts.',
		'link'			=> 'http://bestwebsoft.com/products/related-posts/?k=73fb737037f7141e66415ec259f7e426&pn=' . $bws_plugin_info["id"] . '&v=' . $bws_plugin_info["version"] . '&wp_v=' . $wp_version,
		'settings'		=> 'admin.php?page=related-posts-plugin.php'
	),
	'sender/sender.php' => array(
		'category'		=> 'social',
		'name'			=> 'Sender',
		'description'	=> 'You can send mails to all users or to certain categories of users.',
		'link'			=> 'http://bestwebsoft.com/products/sender/?k=89c297d14ba85a8417a0f2fc05e089c7&pn=' . $bws_plugin_info["id"] . '&v=' . $bws_plugin_info["version"] . '&wp_v=' . $wp_version,
		'settings'		=> 'admin.php?page=sndr_settings',
		'pro_version'	=> 'sender-pro/sender-pro.php',
		'purchase' 		=> 'http://bestwebsoft.com/products/sender/buy/?k=dc5d1a87bdc8aeab2de40ffb99b38054&pn=' . $bws_plugin_info["id"] . '&v=' . $bws_plugin_info["version"] . '&wp_v=' . $wp_version,
		'pro_settings'	=> 'admin.php?page=sndrpr_settings'
	),
	'bws-smtp/bws-smtp.php' => array(
		'category'		=> 'utilities',
		'name'			=> 'SMTP',
		'description'	=> 'This plugin introduces an easy way to configure sending email messages via SMTP.',
		'link'			=> 'http://bestwebsoft.com/products/smtp/?k=0546419f962704429ad2d9b88567752f&pn=' . $bws_plugin_info["id"] . '&v=' . $bws_plugin_info["version"] . '&wp_v=' . $wp_version,
		'settings'		=> 'admin.php?page=bwssmtp_settings'
	),
	'social-buttons-pack/social-buttons-pack.php' => array(
		'category'		=> 'social',
		'name'			=> 'Social Buttons Pack',
		'description'	=> 'Add Social buttons to your WordPress website.',
		'link'			=> 'http://bestwebsoft.com/products/social-buttons-pack/?k=b6440fad9f54274429e536b0c61b42da&pn=' . $bws_plugin_info["id"] . '&v=' . $bws_plugin_info["version"] . '&wp_v=' . $wp_version,
		'settings'		=> 'admin.php?page=social-buttons.php'
	),
	'subscriber/subscriber.php' => array(
		'category'		=> 'social',
		'name'			=> 'Subscriber',
		'description'	=> 'This plugin allows you to subscribe users for newsletters from your website.',
		'link'			=> 'http://bestwebsoft.com/products/subscriber/?k=a4ecc1b7800bae7329fbe8b4b04e9c88&pn=' . $bws_plugin_info["id"] . '&v=' . $bws_plugin_info["version"] . '&wp_v=' . $wp_version,
		'settings'		=> 'admin.php?page=sbscrbr_settings_page',
		'pro_version'	=> 'subscriber-pro/subscriber-pro.php',
		'purchase' 		=> 'http://bestwebsoft.com/products/subscriber/buy/?k=02dbb8b549925d9b74e70adc2a7282e4&pn=' . $bws_plugin_info["id"] . '&v=' . $bws_plugin_info["version"] . '&wp_v=' . $wp_version,
		'pro_settings' 	=> 'admin.php?page=sbscrbrpr_settings_page'
	),
	'bws-testimonials/bws-testimonials.php' => array(
		'category'		=> 'content-and-media',
		'name'			=> 'Testimonials',
		'description'	=> 'Allows creating and displaying a Testimonial on your website.',
		'link'			=> 'http://bestwebsoft.com/products/testimonials/?k=3fe4bb89dc901c98e43a113e08f8db73&pn=' . $bws_plugin_info["id"] . '&v=' . $bws_plugin_info["version"] . '&wp_v=' . $wp_version,
		'settings'		=> 'admin.php?page=testimonials.php'
	),
	'timesheet/timesheet.php' => array(
		'category'		=> 'management',
		'name'			=> 'Timesheet',
		'description'	=> 'Allows you to fill out and view the work schedule of users.',
		'link'			=> 'http://bestwebsoft.com/products/timesheet/?k=06a58bb78c17a43df01825925f05a5c1&pn=' . $bws_plugin_info["id"] . '&v=' . $bws_plugin_info["version"] . '&wp_v=' . $wp_version,
		'settings'		=> 'admin.php?page=timesheet_settings'
	),	
	'twitter-plugin/twitter.php' => array(
		'category'		=> 'social',
		'name'			=> 'Twitter',
		'description'	=> 'Allows you to add the Twitter "Follow" and "Like" buttons the easiest way.',
		'link'			=> 'http://bestwebsoft.com/products/twitter/?k=f8cb514e25bd7ec4974d64435c5eb333&pn=' . $bws_plugin_info["id"] . '&v=' . $bws_plugin_info["version"] . '&wp_v=' . $wp_version,
		'settings'		=> 'admin.php?page=twitter.php',
		'pro_version'	=> 'twitter-pro/twitter-pro.php',
		'purchase' 		=> 'http://bestwebsoft.com/products/twitter/buy/?k=63ecbf0cc9cebf060b5a3c9362299700&pn=' . $bws_plugin_info["id"] . '&v=' . $bws_plugin_info["version"] . '&wp_v=' . $wp_version,
		'pro_settings' 	=> 'admin.php?page=twitter-pro.php'
	),
	'updater/updater.php' => array(
		'category'		=> 'utilities',
		'name'			=> 'Updater',
		'description'	=> 'Allows you to update plugins and WP core.',
		'link'			=> 'http://bestwebsoft.com/products/updater/?k=66f3ecd4c1912009d395c4bb30f779d1&pn=' . $bws_plugin_info["id"] . '&v=' . $bws_plugin_info["version"] . '&wp_v=' . $wp_version,
		'settings'		=> 'admin.php?page=updater-options',
		'pro_version'	=> 'updater-pro/updater_pro.php',
		'purchase' 		=> 'http://bestwebsoft.com/products/updater/buy/?k=cf633acbefbdff78545347fe08a3aecb&pn=' . $bws_plugin_info["id"] . '&v=' . $bws_plugin_info["version"] . '&wp_v=' . $wp_version,
		'pro_settings' 	=> 'admin.php?page=updater-pro-options'
	),
	'user-role/user-role.php' => array(
		'category'		=> 'utilities',
		'name'			=> 'User Role',
		'description'	=> 'Allows to change wordpress user role capabilities.',
		'link'			=> 'http://bestwebsoft.com/products/user-role/?k=dfe2244835c6fbf601523964b3f34ccc&pn=' . $bws_plugin_info["id"] . '&v=' . $bws_plugin_info["version"] . '&wp_v=' . $wp_version,
		'settings'		=> 'admin.php?page=user-role.php',
		'pro_version'	=> 'user-role-pro/user-role-pro.php',
		'purchase' 		=> 'http://bestwebsoft.com/products/user-role/buy/?k=cfa9cea6613fb3d7c0a3622fa2faaf46&pn=' . $bws_plugin_info["id"] . '&v=' . $bws_plugin_info["version"] . '&wp_v=' . $wp_version,
		'pro_settings' 	=> 'admin.php?page=user-role-pro.php'
	),	
	'visitors-online/visitors-online.php' => array(
		'category'		=> 'site-stats',
		'name'			=> 'Visitors Online',
		'description'	=> 'See how many users, guests and bots are online at the website.',
		'link'			=> 'http://bestwebsoft.com/products/visitors-online/?k=93c28013a4f830671b3bba9502ed5177&pn=' . $bws_plugin_info["id"] . '&v=' . $bws_plugin_info["version"] . '&wp_v=' . $wp_version,
		'settings'		=> 'admin.php?page=visitors-online.php',
		'pro_version'	=> 'visitors-online-pro/visitors-online-pro.php',
		'purchase' 		=> 'http://bestwebsoft.com/products/visitors-online/buy/?k=f9a746075ff8a0a6cb192cb46526afd2&pn=' . $bws_plugin_info["id"] . '&v=' . $bws_plugin_info["version"] . '&wp_v=' . $wp_version,
		'pro_settings'	=> 'admin.php?page=visitors-online-pro.php'
	),	
	'zendesk-help-center/zendesk-help-center.php' => array(
		'category'		=> 'utilities',
		'name'			=> 'Zendesk Help Center',
		'description'	=> 'This plugin allows to backup&export Zendesk Help Center.',
		'link'			=> 'http://bestwebsoft.com/products/zendesk-help-center/?k=2a5fd2f4b2f4bde46f2ca44b8d15846d&pn=' . $bws_plugin_info["id"] . '&v=' . $bws_plugin_info["version"] . '&wp_v=' . $wp_version,
		'settings'		=> 'admin.php?page=zendesk_hc.php&tab=settings',
		'pro_version'	=> 'zendesk-help-center-pro/zendesk-help-center-pro.php',
		'purchase' 		=> 'http://bestwebsoft.com/products/zendesk-help-center/buy/?k=45199e4538b5befe4d9566868a61a3aa&pn=' . $bws_plugin_info["id"] . '&v=' . $bws_plugin_info["version"] . '&wp_v=' . $wp_version,
		'pro_settings'	=> 'admin.php?page=zendesk_hc.php&tab=settings'
	)
);	

$themes = array(
	(object) array( 
		'name' 		=> 'Real Estate',
		'slug' 		=> 'realestate',
		'href' 		=> 'http://bestwebsoft.com/products/real-estate-creative-wordpress-theme/'
	),
	(object) array( 
		'name' 		=> 'Unity',
		'slug' 		=> 'unity',
		'href' 		=> 'http://bestwebsoft.com/products/unity-multipurpose-wordpress-theme/'
	),
	(object) array( 
		'name' 		=> 'Opening',
		'slug' 		=> 'opening',
		'href' 		=> 'http://bestwebsoft.com/products/opening-job-board-wordpress-theme/'
	)
);