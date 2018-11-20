<?php
/*
Plugin Name: Multilanguage by BestWebSoft
Plugin URI: https://bestwebsoft.com/products/wordpress/plugins/multilanguage/
Description: Translate WordPress website content to other languages manually. Create multilingual pages, posts, widgets, menus, etc.
Author: BestWebSoft
Text Domain: multilanguage
Domain Path: /languages
Version: 1.3.2
Author URI: https://bestwebsoft.com/
License: GPLv3 or later
*/

/*  © Copyright 2018  BestWebSoft  ( https://support.bestwebsoft.com )

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
$file_dir = dirname( __FILE__ );
require_once( $file_dir . '/includes/table.php' );
require_once( $file_dir . '/includes/languages.php' );

/* Function add menu pages */
if ( ! function_exists( 'mltlngg_admin_menu' ) ) {
	function mltlngg_admin_menu() {
		global $submenu, $mltlngg_plugin_info, $wp_version;

		$settings = add_menu_page( 'Multilanguage ' . __( 'Settings', 'multilanguage' ), 'Multilanguage', 'manage_options', 'mltlngg_settings', 'mltlngg_settings_page', 'none' );

		add_submenu_page( 'mltlngg_settings', 'Multilanguage ' . __( 'Settings', 'multilanguage' ), __( 'Settings', 'multilanguage' ), 'manage_options', 'mltlngg_settings', 'mltlngg_settings_page' );

		$languages = add_submenu_page( 'mltlngg_settings', 'Multilanguage ' . __( 'Languages', 'multilanguage' ), __( 'Languages', 'multilanguage' ), 'manage_options', 'multilanguage-languages.php', 'mltlngg_settings_page' );

		add_submenu_page( 'mltlngg_settings', 'BWS Panel', 'BWS Panel', 'manage_options', 'mltlngg-bws-panel', 'bws_add_menu_render' );

		if ( isset( $submenu['mltlngg_settings'] ) ) {
			$submenu['mltlngg_settings'][] = array(
				'<span style="color:#d86463"> ' . __( 'Upgrade to Pro', 'multilanguage' ) . '</span>',
				'manage_options',
				'https://bestwebsoft.com/products/wordpress/plugins/multilanguage/?k=fa164f00821ed3a87e6f78cb3f5c277b&pn=143&v=' . $mltlngg_plugin_info["Version"] . '&wp_v=' . $wp_version );
		}

		mltlngg_add_menu_items();
		add_action( 'load-' . $settings, 'mltlngg_add_tabs' );
		add_action( 'load-' . $languages, 'mltlngg_add_tabs' );
	}
}

/* Plugin initialization in backend and frontend */
if ( ! function_exists( 'mltlngg_init' ) ) {
	function mltlngg_init() {
		global $wpdb, $mltlngg_options, $mltlngg_plugin_info;

		require_once( dirname( __FILE__ ) . '/bws_menu/bws_include.php' );
		bws_include_init( plugin_basename( __FILE__ ) );

		if ( empty( $mltlngg_plugin_info ) ) {
			if ( ! function_exists( 'get_plugin_data' ) ) {
				require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			}
			$mltlngg_plugin_info = get_plugin_data( __FILE__ );
		}

		/* check WordPress version */
		bws_wp_min_version_check( plugin_basename( __FILE__ ), $mltlngg_plugin_info, '3.9' );

		mltlngg_register_settings();

		/* If there have been changes in the list of languages do flush_rewrite_rules */
		if ( isset( $mltlngg_options['flush_rewrite_rules'] ) && 1 == $mltlngg_options['flush_rewrite_rules'] ) {
			flush_rewrite_rules();
			unset( $mltlngg_options['flush_rewrite_rules'] );
			update_option( 'mltlngg_options', $mltlngg_options );
		}
		if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			/*Adding Woocommerce compatibility*/
			add_filter( 'woocommerce_ajax_get_endpoint', 'mltlngg_wcmmrc', 10, 2 );
		}
		mltlngg_add_meta_filters();
	}
}

if ( ! function_exists( 'mltlngg_wcmmrc' ) ) {
	function mltlngg_wcmmrc( $ajax, $request ) {
		/*function delete language slug from woocommerce's ajax request*/
		$request = esc_url_raw( add_query_arg( 'wc-ajax', $request, remove_query_arg( array( 'remove_item', 'add-to-cart', 'added-to-cart' ), home_url( '/' ) ) ) );
		return $request;
	}
}

/* Plugin initialization in backend */
if ( ! function_exists( 'mltlngg_admin_init' ) ) {
	function mltlngg_admin_init() {
		global $bws_plugin_info, $mltlngg_plugin_info, $bws_shortcode_list, $mltlngg_options;

		if ( empty( $bws_plugin_info ) ) {
			$bws_plugin_info = array( 'id' => '143', 'version' => $mltlngg_plugin_info["Version"] );
		}

		/* add Multilanguage to global $bws_shortcode_list */
		$bws_shortcode_list['mltlngg'] = array( 'name' => 'Multilanguage', 'js_function' => 'mltlngg_shortcode_init' );

		/* Actions for categories & tags translation */
		$mltlngg_taxonomies = get_object_taxonomies( 'post' );
		if ( is_array( $mltlngg_taxonomies ) ) {
			foreach ( $mltlngg_taxonomies as $mltlngg_taxonomy ) {
				add_action( $mltlngg_taxonomy . '_add_form_fields', 'mltlngg_new_terms_translate' );
				add_action( 'created_' . $mltlngg_taxonomy, 'mltlngg_new_terms_created' );
				add_action( $mltlngg_taxonomy . '_edit_form_fields', 'mltlngg_terms_translate' );
				add_action( 'edited_' . $mltlngg_taxonomy, 'mltlngg_terms_update' );
				add_action( 'delete_' . $mltlngg_taxonomy, 'mltlngg_delete_term' );

				/* Add language column in the taxonomy wp_list_table to display if language is translated */
				add_filter( 'manage_edit-' . $mltlngg_taxonomy . '_columns', 'mltlngg_add_term_column' );
				add_filter( 'manage_' . $mltlngg_taxonomy . '_custom_column', 'mltlngg_term_column', 10, 3 );
			}
		}
		/* Add language column in the post wp_list_table to display if language is translated */
		add_filter( 'manage_edit-post_columns', 'mltlngg_add_post_column' );
		add_filter( 'manage_post_posts_custom_column', 'mltlngg_post_column', 10, 3 );
		add_filter( 'manage_edit-page_columns', 'mltlngg_add_post_column' );
		add_filter( 'manage_page_posts_custom_column', 'mltlngg_post_column', 10, 3 );

		/* add 'Multilanguage switcher' into the Menu */
		add_action( 'wp_update_nav_menu_item', 'mltlngg_wp_update_nav_menu_item', 10, 2 );
		add_meta_box( 'mltlngg_language_switcher_box', __( 'Multilanguage switcher', 'multilanguage' ), 'mltlngg_language_switcher_box', 'nav-menus', 'side', 'high' );
	}
}

if ( ! function_exists( 'mltlngg_language_attributes' ) ) {
	function mltlngg_language_attributes( $output = '' ) {
		if ( false === strpos( $output, 'http://ogp.me/ns' ) ) {
			$prefix = 'prefix="og: http://ogp.me/ns#"';
			$output .= "\n\t" . $prefix;
		}
		return $output;

	}
}

/* Default Plugin settings */
if ( ! function_exists( 'mltlngg_register_settings' ) ) {
	function mltlngg_register_settings() {
		global $mltlngg_plugin_info, $mltlngg_options, $wp_version;
		$db_version = '0.3';

		/* Add options to database */
		if ( ! get_option( 'mltlngg_options' ) ) {
			$options_default = mltlngg_get_options_default();
			add_option( 'mltlngg_options', $options_default );
		}

		$mltlngg_options = get_option( 'mltlngg_options' );

		/* Array merge incase this version has added new options */
		if ( ! isset( $mltlngg_options['plugin_option_version'] ) || $mltlngg_options['plugin_option_version'] != $mltlngg_plugin_info["Version"] ) {

			$options_default = mltlngg_get_options_default();
			$mltlngg_options = array_merge( $options_default, $mltlngg_options );
			$mltlngg_options['plugin_option_version'] = $mltlngg_plugin_info["Version"];
			/* show pro features */
			$mltlngg_options['hide_premium_options'] = array();

			$update_option = true;
			if ( is_multisite() ) {
				switch_to_blog( 1 );
				register_uninstall_hook( __FILE__, 'mltlngg_delete_options' );
				restore_current_blog();
			} else {
				register_uninstall_hook( __FILE__, 'mltlngg_delete_options' );
			}
		}

		if ( ! isset( $mltlngg_options['plugin_db_version'] ) || $mltlngg_options['plugin_db_version'] != $db_version ) {
			global $wpdb;

			mltlngg_plugin_db();

			$mltlngg_options['plugin_db_version'] = $db_version;
			$update_option = true;
		}

		if ( ! isset( $mltlngg_options['wp_version'] ) || $mltlngg_options['wp_version'] != $wp_version ) {
			$mltlngg_options['wp_version'] = $wp_version;
			mltlngg_update_video_options();
			$update_option = true;
		}

		if ( isset( $update_option ) ) {
			update_option( 'mltlngg_options', $mltlngg_options );
		}
	}
}

if ( ! function_exists( 'mltlngg_get_options_default' ) ) {
	function mltlngg_get_options_default() {
		global $mltlngg_plugin_info, $wp_version, $mltlngg_languages;
		/* Set the default language is the same as the language of the Wordpress localization */
		foreach ( $mltlngg_languages as $one_lang ) { /* Search locale in the array of standard languages, source - languages.php */
			$mltlngg_is_lang_exist = array_search( get_locale(), $one_lang );
			if ( false != $mltlngg_is_lang_exist ) { /* If the locale is found set the default language */
				$language_default = $one_lang;
				break;
			}
		}

		/* If the language is not found or is not a standard, set English ('en_US') as the default language */
		if ( ! isset( $language_default ) || NULL == $language_default ) {
			$language_default = array( 'en', 'en_US', 'English', '' );
		}
		/* Set the default options */
		$options_default = array(
			'plugin_option_version'		=> $mltlngg_plugin_info["Version"],
			'wp_version'				=> $wp_version,
			'suggest_feature_banner'	=> 1,
			'display_settings_notice'	=> 1,
			'first_install'				=> strtotime( "now" ),
			'default_language'			=> $language_default[1],
			'list_of_languages'			=> array(
				array(
					'locale'	=> $language_default[1],
					'name'		=> $language_default[2],
					'enable'	=> true,
					'priority'	=> 1
				)
			),
			'save_mode'					=> 'ajax',
			'wp_localization'			=> 1,
			'language_switcher'			=> 'drop-down-list',
			'search'		 			=> 'single',
			'video_providers'			=> array(),
			'translate_open_graph'		=> 0,
			'display_alternative_link'	=> 0,
			'hide_link_slug'			=> 0

		);
		return $options_default;
	}
}

/* Activation plugin function */
if ( ! function_exists( 'mltlngg_plugin_activate' ) ) {
	function mltlngg_plugin_activate( $networkwide ) {
		global $wpdb;
		/* Activation function for network */
		if ( function_exists( 'is_multisite' ) && is_multisite() ) {
			/* check if it is a network activation - if so, run the activation function for each blog id */
			if ( $networkwide ) {
				$old_blog = $wpdb->blogid;
				/* Get all blog ids */
				$blogids = $wpdb->get_col( "SELECT `blog_id` FROM $wpdb->blogs" );
				foreach ( $blogids as $blog_id ) {
					switch_to_blog( $blog_id );
					_mltlngg_plugin_activate();
				}
				switch_to_blog( $old_blog );
				return;
			}
		}
		_mltlngg_plugin_activate();

		/* Activation function for network, check if it is a network activation - if so, run the activation function for each blog id */
		if ( is_multisite() ) {
			switch_to_blog( 1 );
			register_uninstall_hook( __FILE__, 'mltlngg_delete_options' );
			restore_current_blog();
		} else {
			register_uninstall_hook( __FILE__, 'mltlngg_delete_options' );
		}
	}
}

/* Activation function for new blog in network */
if ( ! function_exists( 'mltlngg_new_blog' ) ) {
	function mltlngg_new_blog( $blog_id, $user_id, $domain, $path, $site_id, $meta ) {
		global $wpdb;
		if ( is_plugin_active_for_network( 'multilanguage/multilanguage.php' ) ) {
			$old_blog = $wpdb->blogid;
			switch_to_blog( $blog_id );
			_mltlngg_plugin_activate();
			switch_to_blog( $old_blog );
		}
	}
}

/* Create tables for translations when plugin is activate */
if ( ! function_exists( '_mltlngg_plugin_activate' ) ) {
	function _mltlngg_plugin_activate() {
		global $wpdb, $mltlngg_options;
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		mltlngg_register_settings();

		/* Auto-add Multilanguage widget to active sidebar */
		if ( is_dynamic_sidebar() ) { /* If there is an active sidebar */
			$mltlngg_available_sidebars = get_option( 'sidebars_widgets' ); /* Get all active sidebars */

			/* Delete from database old information about widget */
			foreach ( $mltlngg_available_sidebars as $key1 => $one_sidebar ) {
				if ( $key1 != 'array_version' && isset( $one_sidebar ) ) {
					$widget_exist = preg_grep( '/multi_language_widget-?\d*/', $one_sidebar );
					if ( isset( $widget_exist ) ) {
						foreach ( $widget_exist as $key2 => $one_widget ) {
							unset( $mltlngg_available_sidebars[ $key1 ][ $key2 ] );
						}
					}
				}
			}
			update_option( 'sidebars_widgets', $mltlngg_available_sidebars );

			/* If more than one sidebar */
			if ( count( $mltlngg_available_sidebars ) > 3 ) {
				foreach ( $mltlngg_available_sidebars as $key => $val ) {
					if ( is_array( $val ) && $key != 'wp_inactive_widgets' && $key != 'array_version' && ! preg_match( '/multi_language_widget-?\d*/', implode( ',', $val ) ) && preg_match( '/search.*/', implode( ',', $val ) ) ) { /* If there is no Multilanguage Widget and exist Search Widget in this sidebar */
						array_unshift( $val, 'multi_language_widget-2' ); /* Adding Multilanguage Widget to the top of sidebar */
						$mltlngg_available_sidebars[ $key ] = $val;
						update_option( 'sidebars_widgets', $mltlngg_available_sidebars );
					}
				}
				/* If only one sidebar */
			} elseif ( count( $mltlngg_available_sidebars ) == 3 ) {
				foreach ( $mltlngg_available_sidebars as $key => $val ) {
					if ( is_array( $val ) && $key != 'wp_inactive_widgets' && $key != 'array_version' && ! preg_match( '/multi_language_widget-?\d*/', implode( ',', $val ) ) ) { /* If there is no Multilanguage Widget in this sidebar */
						array_unshift( $val, 'multi_language_widget-2' ); /* Adding Multilanguage Widget to the top of sidebar */
						$mltlngg_available_sidebars[ $key ] = $val;
						update_option( 'sidebars_widgets', $mltlngg_available_sidebars );
					}
				}
			}
			$widget_options = get_option( 'widget_multi_language_widget' ); /* Get widget options */
			if ( ! isset( $widget_options[2] ) ) {
				$widget_options[2] = array( 'wiget_title' => '', 'mltlngg_language_switcher' => 'drop-down-list' ); /* Set widget options */
				update_option( 'widget_multi_language_widget', $widget_options );
			}
		}
		if ( empty( $mltlngg_options ) ) {
			$mltlngg_options = get_option( 'mltlngg_options' );
		}

		if ( isset( $mltlngg_options['deactivation'] ) ) {
			/* update content */
			$posts = $wpdb->get_results(
				"SELECT $wpdb->posts.`ID`, $wpdb->posts.`post_content`, $wpdb->posts.`post_title`, $wpdb->posts.`post_excerpt`
					FROM $wpdb->posts, `" . $wpdb->prefix . "mltlngg_translate`
					WHERE
						`post_type` IN ('page', 'post') AND
						$wpdb->posts.`ID` = `" . $wpdb->prefix . "mltlngg_translate`.`post_ID` AND
						`post_modified` > '" . $mltlngg_options['deactivation'] . "'"
			);

			foreach ( $posts as $key => $value ) {
				$wpdb->update(
					$wpdb->prefix . 'mltlngg_translate',
					array(
						'post_content'	=> $value->post_content,
						'post_title'	=> $value->post_title,
						'post_excerpt'	=> $value->post_excerpt,
					),
					array(
						'post_ID'	=> $value->ID,
						'language'	=> $mltlngg_options['default_language']
					),
					array( '%s', '%s', '%s' ),
					array( '%d', '%s' )
				);
			}

			unset( $mltlngg_options['deactivation'] );
		}

		mltlngg_update_video_options();
		update_option( 'mltlngg_options', $mltlngg_options );
	}
}

if ( ! function_exists( 'mltlngg_plugin_db' ) ) {
	function mltlngg_plugin_db() {
		global $wpdb;
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$mltlngg_sql =
			"CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . "mltlngg_translate` (
			`ID` INT(6) UNSIGNED NOT NULL AUTO_INCREMENT,
			`post_ID` INT(6) NOT NULL,
			`post_content` longtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
			`post_excerpt` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
			`post_title` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
			`language` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
			PRIMARY KEY (`ID`)
			) DEFAULT CHARSET=utf8;";
		dbDelta( $mltlngg_sql );

		$mltlngg_sql =
			"CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . "mltlngg_terms_translate` (
			`ID` INT(6) UNSIGNED NOT NULL AUTO_INCREMENT,
			`term_ID` INT(6) NOT NULL,
			`name` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
			`language` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
			PRIMARY KEY (`ID`)
			) DEFAULT CHARSET=utf8;";
		dbDelta( $mltlngg_sql );

		$column_exists = $wpdb->query( "SHOW COLUMNS FROM `" . $wpdb->prefix . "mltlngg_translate` LIKE 'post_excerpt';" );
		if ( 0 == $column_exists ) {
			$wpdb->query( 'ALTER TABLE `' . $wpdb->prefix . 'mltlngg_translate` ADD `post_excerpt` TEXT NOT NULL AFTER `post_content`' );
		}
	}
}

if ( ! function_exists( 'mltlngg_plugin_load' ) ) {
	function mltlngg_plugin_load() {
		global $mltlngg_get_default_language, $mltlngg_enabled_languages_locale, $mltlngg_enabled_languages;
		$mltlngg_options = get_option( 'mltlngg_options' );

		if ( ! empty( $mltlngg_options ) ) {
			$mltlngg_get_default_language = $mltlngg_options['default_language'];
			$mltlngg_enabled_languages = array(); /* Array with all enabled languages */
			$mltlngg_enabled_languages_locale = array(); /* Array with codes of all enabled languages */
			foreach ( $mltlngg_options['list_of_languages'] as $item ) {
				if ( ! empty( $item['enable'] ) ) {
					$mltlngg_enabled_languages[ $item['locale'] ]	= $item;
					$mltlngg_enabled_languages_locale[]				= $item['locale'];
				}
			}
			$is_admin = mltlngg_is_admin();

			if ( $is_admin && "" == session_id() ) {
				session_start();
			}

			/* Switch display language function */
			mltlngg_get_display_language();

			/* Internationalization */
			load_plugin_textdomain( 'multilanguage', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

			/*
			 * if not admin panel
			 * and not login/logout/ page
			 * redirect to URL with language code
			 */
			if ( ! $is_admin && ! preg_match( "/\/?\w+\.{1}[a-z]{3,4}/", $_SERVER['REQUEST_URI'] ) && ! defined( 'WP_CLI' ) ) {
				/* Change Wordpress localization if the option is enabled */
				if ( $mltlngg_options['wp_localization'] ) {
					add_filter( 'locale', 'mltlngg_switch_wp_locale' );
				}
				mltlngg_redirect();
			}
		}
	}
}

/* Function for change display language */
if ( ! function_exists( 'mltlngg_get_display_language' ) ) {
	function mltlngg_get_display_language() {
		global $mltlngg_current_language, $mltlngg_old_language, $mltlngg_enabled_languages_locale, $mltlngg_enabled_languages, $mltlngg_get_default_language;

		/* Do not change the language on the admin page if language changed in the frontend */
		if ( mltlngg_is_admin() ) {
			$wp_locale = get_locale();
			$mltlngg_current_language = ! empty( $wp_locale ) && in_array( $wp_locale, $mltlngg_enabled_languages_locale ) ? $wp_locale : 'en_US';
		} else {
			/* The language is changed via widget */
			if ( isset( $_POST['mltlngg_change_display_lang'] ) && in_array( $_POST['mltlngg_change_display_lang'], $mltlngg_enabled_languages_locale ) ) {
				if ( empty( $mltlngg_old_language ) && isset( $_COOKIE['mltlngg_language'] ) ) {
					$mltlngg_old_language = $_COOKIE['mltlngg_language'];/* Language before changing */
				}
				$mltlngg_current_language = $_POST['mltlngg_change_display_lang']; /* Language after changing */
				$new_cookie = $mltlngg_current_language;
				/* The language is entered in the url */
			} elseif ( ! empty( $_GET['lang'] ) && in_array( $_GET['lang'], $mltlngg_enabled_languages_locale ) ) {
				$mltlngg_current_language = $_GET['lang'];
				$new_cookie = $mltlngg_current_language;
			} elseif ( preg_match( '~(?<=/)(' . implode( '|', $mltlngg_enabled_languages_locale ) . ')(?![\d\w-])~', $_SERVER['REQUEST_URI'], $matches ) ) {
				$mltlngg_current_language = trim( $matches[1], '/');
				$new_cookie = $mltlngg_current_language;
				/* The language is not changed, leave the current language from session */
			} elseif ( isset( $_COOKIE['mltlngg_language'] ) && in_array( $_COOKIE['mltlngg_language'], $mltlngg_enabled_languages_locale ) && count( $mltlngg_enabled_languages ) > 1 ) {
				$mltlngg_current_language = $_COOKIE['mltlngg_language'];
				/* The language is never chosen, assign the default language from options */
			} else {
				$mltlngg_current_language = $mltlngg_get_default_language;
				$params = explode( '/', trim( $_SERVER['REQUEST_URI'], '/' ) );
				if ( is_array( $params ) && ! empty( $params ) ) {
					foreach ( $mltlngg_enabled_languages_locale as $locale ) {
						$key = array_search( $locale, $params );
						if ( false !== $key ) {
							$mltlngg_current_language = $params[ $key ];
							break;
						}
					}
				}
				$new_cookie = $mltlngg_current_language;
			}
		}

		if ( isset( $new_cookie ) ) {
			$home_url = get_option( 'home' );
			$home_url_parsed = mltlngg_parse_url( $home_url );
			$host = $home_url_parsed['host'];
			$host = preg_replace( "~^(www\.)~", "", $host );
			setcookie( 'mltlngg_language', $new_cookie, time() + 30*DAY_IN_SECONDS, '/', '.' . $host, is_ssl() );
		}
	}
}

/* Function for change Wordpress localization when change display language */
if ( ! function_exists( 'mltlngg_switch_wp_locale' ) ) {
	function mltlngg_switch_wp_locale( $lang = "en_US" ) {
		global $mltlngg_current_language;
		return ( ! empty( $mltlngg_current_language ) ) ? $mltlngg_current_language : $lang;
	}
}

/* Function for redirect when display language is changed */
if ( ! function_exists( 'mltlngg_redirect' ) ) {
	function mltlngg_redirect() {
		global $mltlngg_enabled_languages, $mltlngg_options, $mltlngg_current_language, $mltlngg_get_default_language;

		if ( empty( $mltlngg_options ) ) {
			$mltlngg_options = get_option( 'mltlngg_options' );
		}

		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}

		if (
			( isset( $_POST['wp_customize'] ) && $_POST['wp_customize'] == 'on' ) ||
			( is_plugin_active( 'nextgen-gallery/nggallery.php') && isset( $_REQUEST['photocrati_ajax'] ) ) ||
			1 >= count( $mltlngg_enabled_languages )
		)
			return;

		if ( is_plugin_active( 'woocommerce/woocommerce.php' ) && preg_match( "/\/wc-api\/v([1-3]{1})(.*)?/", $_SERVER['REQUEST_URI'] ) ) {
			/* WooCommerce REST API */
			return;
		}

		$url_parts		= mltlngg_parse_url();
		$request_url	= $url_parts['full_url'];

		$redirect_url = apply_filters( 'mltlngg_redirect_url', mltlngg_get_lang_link() );

		$redirect = false;

		if ( rtrim( preg_replace( "~^http(s)?://~U", "", $redirect_url ), '/' ) != rtrim( preg_replace( "~^http(s)?://~U", "", $request_url ), '/' ) ) {
			$redirect = true;
		}

		$redirect = apply_filters( 'mltlngg_redirect_flag', $redirect, $request_url, $redirect_url );

		if ( $redirect && filter_var( $redirect_url, FILTER_VALIDATE_URL ) ) {
			wp_redirect( $redirect_url );
			exit();
		}
	}
}

if ( ! function_exists( 'mltlngg_server_parts' ) ) {
	function mltlngg_server_parts( $use_forwarded_host = false ) {
		$s					= $_SERVER;
		$ssl				= ( ! empty( $s['HTTPS'] ) && $s['HTTPS'] == 'on' );
		$sp					= strtolower( $s['SERVER_PROTOCOL'] );
		$scheme				= substr( $sp, 0, strpos( $sp, '/' ) ) . ( ( $ssl ) ? 's' : '' );
		$port				= $s['SERVER_PORT'];
		$port				= ( ( ! $ssl && $port == '80' ) || ( $ssl && $port == '443' ) ) ? '' : ':' . $port;
		$host				= ( $use_forwarded_host && isset( $s['HTTP_X_FORWARDED_HOST'] ) ) ? $s['HTTP_X_FORWARDED_HOST'] : ( isset( $s['HTTP_HOST'] ) ? $s['HTTP_HOST'] : null );
		$host				= isset( $host ) ? $host : $s['SERVER_NAME'] . $port;
		$url_origin			= $scheme . '://' . $host;
		$full_url			= $url_origin . $s['REQUEST_URI'];

		$result = array(
			'scheme'		=> $scheme,
			'port'			=> $port,
			'host'			=> $host,
			'url_origin'	=> $url_origin,
			'full_url'		=> $full_url
		);

		return $result;
	}
}

if ( ! function_exists( 'mltlngg_parse_url' ) ) {
	function mltlngg_parse_url( $url = '' ) {
		global $mltlngg_enabled_languages_locale;
		$home_url = get_option( 'home' );

		if ( empty( $url ) ) {
			$server_parts = mltlngg_server_parts();
			$url = $server_parts['full_url'];
		}

		$scheme = parse_url( $url, PHP_URL_SCHEME );
		if ( empty( $scheme ) ) {
			$scheme = is_ssl() ? 'https' : 'http';
		}

		$scheme .= "://";
		$host = parse_url( $url, PHP_URL_HOST );
		$home_host = parse_url( $home_url, PHP_URL_HOST );
		$port = parse_url( $url, PHP_URL_PORT );
		$port = empty( $port ) ? '' : ":" . $port;

		$full_host = $scheme . $host . $port;

		$host_pattern = "~^((.*)" . preg_quote( $home_host . $port, '~') . ")~";
		$home_dir = preg_replace( $host_pattern, '', $home_url );

		$home_url_pattern = "~^(" . preg_quote( $full_host . $home_dir, '~') . ")~U";
		$request_uri = preg_replace( $home_url_pattern, '', $url );

		$reg_exp = '~(?<=/)(' . implode( '|', $mltlngg_enabled_languages_locale ) . ')(?![\d\w-])~';
		preg_match_all( $reg_exp, $request_uri, $matches );

		if ( isset( $matches ) && count( $matches[0] ) > 1 ) {
			/* too many language locales, remove all except the first one */
			unset( $matches[0][0] );
			$reg_exp = '~(/' . implode( '|/', $matches[0] ) . ')(?![\d\w-])~';
			$request_uri = preg_replace( $reg_exp, '', $request_uri );
		}

		$url_parsed = array(
			'scheme'			=> $scheme, 								/* http:// || https:// */
			'host'				=> $host, 									/* www.example.com */
			'port'				=> $port, 									/* :80 */
			'full_host'			=> $full_host, 								/* http://www.example.com:80 */
			'home_dir'			=> $home_dir,								/* /wp - subsite directory or wordpress subdirectory */
			'request_uri'		=> $request_uri,							/* /page-sample?paged=2 */
			'full_url'			=> $full_host . $home_dir . $request_uri, 	/* http://www.example.com:80/wp/page-sample?paged=2 */
		);

		return apply_filters( 'mltlngg_parse_url', $url_parsed );
	}
}

/* Remove all language slugs from the given link */
if ( ! function_exists( 'mltlngg_remove_link_lang' ) ) {
	function mltlngg_remove_link_lang( $url = '' ) {
		global $mltlngg_enabled_languages_locale;
		$is_custom_permalink = get_option( 'permalink_structure' );

		$url_parsed = mltlngg_parse_url( $url );
		$url = $url_parsed['full_url'];

		$url = apply_filters( 'mltlngg_url_before_lang_remove', $url );

		if ( $is_custom_permalink ) {
			$locales = implode( '|', $mltlngg_enabled_languages_locale );
			$url = preg_replace( "~/(" . $locales . ")(?![\d\w-])~", "", $url );
		} else {
			$url = remove_query_arg( 'lang', $url );
		}
		$url = apply_filters( 'mltlngg_url_after_lang_remove', $url );

		return $url;
	}
}

/* Localize given link */
if ( ! function_exists( 'mltlngg_get_lang_link' ) ) {
	function mltlngg_get_lang_link( $args = array() ) {
		global $mltlngg_options, $mltlngg_current_language, $mltlngg_enabled_languages, $mltlngg_enabled_languages_locale, $mltlngg_get_default_language;

		$defaults = array(
			'lang'				=> '',
			'url'				=> '',
			'force'				=> false
		);

		extract( wp_parse_args( $args, $defaults ) );

		if ( empty( $mltlngg_options ) ) {
			$mltlngg_options = get_option( 'mltlngg_options' );
		}

		$url_parsed = mltlngg_parse_url( $url );
		$url = $url_parsed['full_url'];

		if ( empty( $lang ) ) {
			$lang = $mltlngg_current_language;
		}

		if ( ! in_array( $lang, $mltlngg_enabled_languages_locale ) ) {
			$lang = $mltlngg_get_default_language;
		}

		$hide_lang = !! ( ( $force == 'hide' ) || ( $lang == $mltlngg_get_default_language && $mltlngg_options['hide_link_slug'] && $force != 'display' ) );
		/* Clean all labguage slugs */
		$url = mltlngg_remove_link_lang( $url );
		$url_parsed = mltlngg_parse_url( $url );

		$is_custom_permalink = get_option( 'permalink_structure' );
		if ( $is_custom_permalink ) {
			$home_url = get_option( 'home' );
			$locale_string = ( $hide_lang ) ? '' : '/' . $lang;
			$url = $home_url . $locale_string . $url_parsed['request_uri'];
		} else {
			if ( ! $hide_lang ) {
				$url = add_query_arg( 'lang', $lang, $url );
			}

			if ( 'page' == get_option( 'show_on_front' ) ) {
				$homepage_id = get_option( 'page_on_front' );
				$url_parsed = mltlngg_parse_url( $url );
				parse_str( str_replace( '/?', '', $url_parsed['request_uri'] ), $params );

				if ( isset( $params['lang'] ) && 1 == count( $params ) ) {
					/* add page id param if language param exist */
					$url = add_query_arg( 'page_id', $homepage_id, $url );
				} elseif ( ! isset( $params['lang'] ) && isset( $params['page_id'] ) && $homepage_id == $params['page_id'] && 1 == count( $params ) ) {
					/* delete homepage id param if language param is missing */
					$url = remove_query_arg( 'page_id', $url );
				}
			}
		}

		$url = apply_filters( 'mltlngg_get_lang_link', $url, $lang, $force );

		return $url;
	}
}

/* Search in enabled languages for specified lang slug and return corresponding language code */
if ( ! function_exists( 'mltlngg_get_lang_code' ) ) {
	function mltlngg_get_lang_code( $lang_slug = '' ) {
		global $mltlngg_languages, $mltlngg_enabled_languages, $mltlngg_current_language;

		if ( empty( $lang_slug ) ) {
			$lang_slug = $mltlngg_current_language;
		}

		foreach ( $mltlngg_languages as $language ) {
			if ( isset( $mltlngg_enabled_languages[ $language[1] ] ) && $language[1] == $lang_slug ) {
				return $language[0];
			}
		}

		$parts = explode( '_', $lang_slug );
		return strtolower( $parts[0] );
	}
}

if ( ! function_exists( 'mltlngg_wp_head' ) ) {
	function mltlngg_wp_head() {
		mltlngg_add_translated_meta_tags();
		mltlngg_alternate_links();
	}
}

if ( ! function_exists( 'mltlngg_alternate_links' ) ) {
	function mltlngg_alternate_links() {
		global $mltlngg_enabled_languages, $mltlngg_get_default_language, $mltlngg_options;

		if ( ! $mltlngg_options['display_alternative_link'] ) {
			return false;
		}

		$defaults			= array( 'x-default', $mltlngg_get_default_language );
		$link_attr_args		= array(
			array(
				'hreflang'		=> 'x-default',
				'link_param'	=> $mltlngg_get_default_language
			)
		);
		foreach ( $mltlngg_enabled_languages as $lang ) {
			$link_attr_args[] = array(
				'hreflang'		=> $lang['locale'],
				'link_param'	=> $lang['locale']
			);
		}

		/**
		 * You can add, edit or remove some links
		 */
		$link_attr_args = apply_filters( 'bwsplgns_mltlngg_add_alt_links', $link_attr_args );

		if ( empty( $link_attr_args ) || ! is_array( $link_attr_args ) ) {
			return false;
		}

		foreach ( $link_attr_args as $item ) {
			if ( empty( $item['hreflang'] ) || empty( $item['link_param'] ) ) {
				continue;
			}

			$args = array(
				'lang' => $item['link_param'],
				'force' => 'display'
			);

			$language_link = mltlngg_get_lang_link( $args );
			printf(
				'<link rel="alternate" hreflang="%1$s" href="%2$s" />',
				strtolower( str_replace( '_', '-', esc_attr( $item['hreflang'] ) ) ),
				esc_url( rtrim( $language_link, '/' ) )
			);
		}
	}
}

if ( ! function_exists( 'mltlngg_add_meta_filters' ) ) {
	function mltlngg_add_meta_filters() {
		global $mltlngg_current_language, $mltlngg_options;

		if ( 1 == $mltlngg_options['translate_open_graph'] && $mltlngg_current_language !== $mltlngg_options['default_language'] ) {

			if ( is_plugin_active( 'facebook-button-plugin/facebook-button-plugin.php' ) || is_plugin_active( 'facebook-button-plus/facebook-button-plus.php' ) || is_plugin_active( 'facebook-button-pro/facebook-button-pro.php' ) ) {
				add_filter( 'fcbkbttn_meta_title', 'mltlngg_the_title_filter' );
				add_filter( 'fcbkbttn_meta_description', 'mltlngg_localize_excerpt' );
			}

			if ( is_plugin_active( 'all-in-one-seo-pack/all_in_one_seo_pack.php' ) || is_plugin_active( 'all-in-one-seo-pack-pro/all_in_one_seo_pack_pro.php' ) ) {
				add_filter( 'aioseop_description', 'mltlngg_localize_excerpt' );
				add_filter( 'aioseop_opengraph_placeholder', 'mltlngg_the_title_filter' );
			}

			if ( is_plugin_active( 'wordpress-seo/wp-seo.php' ) || is_plugin_active( 'wordpress-seo-pro/wp-seo-pro.php' ) ) {
				add_filter( 'wpseo_opengraph_title', 'mltlngg_wpseo_the_title_filter' );
				add_filter( 'wpseo_opengraph_desc', 'mltlngg_localize_excerpt' );
				add_filter( 'wpseo_title', 'mltlngg_wpseo_the_title_filter' );
				add_filter( 'wpseo_metadesc', 'mltlngg_localize_excerpt' );
			}
		}
	}
}

if ( ! function_exists( 'mltlngg_add_translated_meta_tags' ) ) {
	function mltlngg_add_translated_meta_tags() {
		global $mltlngg_current_language, $mltlngg_options, $post;

		if ( empty( $mltlngg_options ) ) {
			$mltlngg_options = get_option( 'mltlngg_options' );
		}

		if( is_singular() ) {

			$has_filters = 0;

			$meta_filters = array(
				'fcbkbttn_meta_title',
				'fcbkbttn_meta_description',
				'aioseop_description',
				'aioseop_opengraph_placeholder',
				'wpseo_opengraph_title',
				'wpseo_opengraph_desc',
				'wpseo_title',
				'wpseo_metadesc',
			);

			for ( $i= 0; $i < count( $meta_filters ) ; $i++ ) {
				if( has_filter( $meta_filters[$i] ) ) {
					$has_filters++;
				}
			}

			if ( 0 == $has_filters &&
				1== $mltlngg_options['translate_open_graph'] &&
				$mltlngg_current_language !== $mltlngg_options['default_language']
			) {

				$title = mltlngg_the_title_filter( get_the_title(), $post->ID );
				$site_name = get_bloginfo();

				if ( has_excerpt() ) {
					$description = mltlngg_localize_excerpt( get_the_excerpt() );
				} else {
					$description = '';
				}

				print "\n" . '<!-- mltlngg meta start -->';
				print "\n" . '<meta property="og:title" content="' . $title . '"/>';
				print "\n" . '<meta property="og:site_name" content="' . $site_name . '"/>';
				print "\n" . '<meta name="twitter:card" content="summary"/>';
				print "\n" . '<meta name="twitter:site" content="' . $site_name . '"/>';
				print "\n" . '<meta name="twitter:title" content="'. $title .'"/>';
				if ( ! empty( $description ) ) {
					print "\n" . '<meta name="description" content="' . $description . '"/>';
					print "\n" . '<meta property="og:description" content="' . $description . '"/>';
					print "\n" . '<meta name="twitter:description" content="' . $description . '"/>';
				}
				print "\n" . '<!-- mltlngg meta end -->';
			}
		}

	}
}

/* to search for the first occurrence of slug language */
if ( ! function_exists( 'mltlngg_str_replace_once' ) ) {
	function mltlngg_str_replace_once( $needle, $replace, $haystack ) {
		/* Looks for the first occurence of $needle in $haystack */
		/* and replaces it with $replace. */
		$pos = strpos( $haystack, $needle );
		if ( $pos === false ) {
			/* Nothing found */
			return $haystack;
		}
		return substr_replace( $haystack, $replace, $pos, strlen( $needle ) );
	}
}

if ( ! function_exists( 'mltlngg_add_query_vars' ) ) {
	function mltlngg_add_query_vars( $vars ) {
		$vars[] = 'lang';
		return $vars;
	}
}

/* Add rewrite rules for available languages */
if ( ! function_exists( 'mltlngg_rewrite_rules' ) ) {
	function mltlngg_rewrite_rules() {
		global $wp_rewrite, $mltlngg_options;

		if ( empty( $mltlngg_options ) ) {
			$mltlngg_options = get_option( 'mltlngg_options' );
		}

		/* Array with codes of all enabled languages */
		$mltlngg_enabled_languages_locale = array();
		foreach ( $mltlngg_options['list_of_languages'] as $item ) {
			if (
				! empty( $item['enable'] ) &&
				( $item['locale'] != $mltlngg_options['default_language'] || ! $mltlngg_options['hide_link_slug'] )
			) {
				$mltlngg_enabled_languages_locale[] = $item['locale'];
			}
		}
		if ( ! empty( $mltlngg_enabled_languages_locale ) ) {
			$mltlngg_lang_regular = '(' . implode( '|', $mltlngg_enabled_languages_locale ) . ')';
			add_rewrite_tag( '%lang%', $mltlngg_lang_regular, 'lang=' );
			$new_rules = array( $mltlngg_lang_regular . '/?$' => 'index.php?lang=$matches[1]' );
			foreach ( $wp_rewrite->rules as $left => $right ) {
				$new_rules[ $mltlngg_lang_regular . '/' . $left ] = preg_replace_callback( '/matches\[(\d{1,2})\]/', 'mltlngg_replace_matched_rule', $right ) . '&lang=$matches[1]';
			}
			$wp_rewrite->rules = $new_rules + $wp_rewrite->rules;
		}
	}
}

if ( ! function_exists( 'mltlngg_replace_matched_rule' ) ) {
	function mltlngg_replace_matched_rule( $matches ) {
		return 'matches[' . ( $matches[1] + 1 ) . ']';
	}
}

/* Adding language code to url after home_url */
if ( ! function_exists( 'mltlngg_get_url_translated' ) ) {
	function mltlngg_get_url_translated( $url ) {
		global $mltlngg_current_language, $mltlngg_enabled_languages, $mltlngg_options;

		if ( empty( $mltlngg_options ) ) {
			$mltlngg_options = get_option( 'mltlngg_options' );
		}

		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}

		if (
			(
				false === strpos( $url, content_url() ) &&
				false === strpos( $url, includes_url() )
			) &&
			count( ( array ) $mltlngg_enabled_languages ) > 1 &&
			! ( mltlngg_is_admin() ) &&
			false === strpos( $_SERVER['REQUEST_URI'], 'wp-login.php' ) &&
			! ( $mltlngg_current_language === $mltlngg_options['default_language'] && $mltlngg_options['hide_link_slug'] ) &&
			! ( is_plugin_active( 'woocommerce/woocommerce.php' ) && preg_match( "/\/wc-api\/v([1-3]{1})(.*)?/", $_SERVER['REQUEST_URI'] ) )
		) {
			$is_custom_permalink = get_option( 'permalink_structure' );

			if ( $is_custom_permalink ) {
				$homeurl	= get_option( 'home' );
				$url	= str_replace( $homeurl, $homeurl . '/' . $mltlngg_current_language, $url );
			} else {
				$url	= add_query_arg( 'lang', $mltlngg_current_language, $url );
			}
		}
		return $url;
	}
}

/* Load scripts and styles */
if ( ! function_exists( 'mltlngg_script_style' ) ) {
	function mltlngg_script_style() {
		global $hook_suffix, $mltlngg_options, $mltlngg_plugin_info;
		wp_enqueue_style( 'mltlngg_stylesheet', plugins_url( 'css/style.css', __FILE__ ), array(), $mltlngg_plugin_info["Version"] );

		if ( is_admin() ) {
			wp_enqueue_script( 'mltlngg_script', plugins_url( 'js/script.js', __FILE__ ), array( 'jquery' ), $mltlngg_plugin_info["Version"] );
			wp_localize_script( 'mltlngg_script', 'mltlngg_vars',
				array(
					'update_post_error'		=> __( 'Attention!!! The changes will not be saved because Title and Content fields are empty on the current tab! It is recommended to fill in at least one field or switch to the tab with the fields that are already filled.', 'multilanguage' ),
					'confirm_update_post'	=> __( "Switching to another language will remove all unsaved. Save data?", 'multilanguage' ),
					'ajax_nonce'			=> wp_create_nonce( "mltlngg-ajax-nonce" )
				)
			);

			if ( 'nav-menus.php' == $hook_suffix ) {
				wp_enqueue_script( 'mltlngg_nav_menu', plugins_url( 'js/nav-menu.js', __FILE__ ), array( 'jquery' ), $mltlngg_plugin_info["Version"] );

				$items = get_posts( array(
					'numberposts'	=> -1,
					'nopaging'		=> true,
					'post_type'		=> 'nav_menu_item',
					'fields'		=> 'ids',
					'meta_key'		=> '_mltlngg_menu_item',
				) );
				/* the options values for the language switcher */
				$data['value'] = array();
				foreach ( $items as $item ) {
					$data['value'][ $item ] = get_post_meta( $item, '_mltlngg_menu_item', true );
				}
				$data['switcher'] = array(
					'input' => array(
						'aligned-list'		=> __( 'Flag', 'multilanguage' ) . ' + ' . __( 'title', 'multilanguage' ),
						'aligned-titles'	=> __( 'Title', 'multilanguage' ),
						'aligned-icons'		=> __( 'Flag', 'multilanguage' ),
						'drop-down-list'	=> __( 'Drop-down list', 'multilanguage' ) . ' (' . __( 'flag', 'multilanguage' ) . ' + ' . __( 'title', 'multilanguage' ) . ')',
						'drop-down-titles' 	=> __( 'Drop-down list', 'multilanguage' ) . ' (' . __( 'title', 'multilanguage' ) . ')',
						'drop-down-icons' 	=> __( 'Drop-down list', 'multilanguage' ) . ' (' . __( 'flag', 'multilanguage' ) . ')'

					)
				);
				$data['title'] = __( 'Multilanguage switcher', 'multilanguage' );
				wp_localize_script( 'mltlngg_nav_menu', 'mltlngg_var', $data );
			}

			if ( isset( $_GET['page'] ) && 'mltlngg_settings' == $_GET['page'] ) {
				bws_enqueue_settings_scripts();
				bws_plugins_include_codemirror();
			}
		}
	}
}

/* Load scripts and styles */
if ( ! function_exists( 'mltlngg_enqueue_google_script' ) ) {
	function mltlngg_enqueue_google_script( $layout = 'dropdown' ) {
		global $mltlngg_plugin_info, $mltlngg_current_language, $mltlngg_options;

		wp_enqueue_script( 'mltlngg_google_script', plugins_url( 'js/google-script.js', __FILE__ ), array(), $mltlngg_plugin_info["Version"], true );

		$lang = mltlngg_get_lang_code( $mltlngg_current_language );

		wp_localize_script( 'mltlngg_google_script', 'mltlngg_lang_var', array( 'current_lang' => $lang, 'layout' => $layout ) );

		wp_enqueue_script( 'mltlngg_google_translate_api', "//translate.google.com/translate_a/element.js?cb=mltlnggGoogleTranslateElementInit", array( 'mltlngg_google_script' ), false, true );
	}
}

/* Function for disable autosave in post/page editor */
if ( ! function_exists( 'mltlngg_disable_autosave_editor_script' ) ) {
	function mltlngg_disable_autosave_editor_script() {
		wp_dequeue_script( 'autosave' );
		wp_deregister_script( 'autosave' );
	}
}

/**
 * Adds Multilanguage Widget widget.
 */
if ( ! class_exists( 'Mltlngg_Widget' ) ) {
	class Mltlngg_Widget extends WP_Widget {
		/**
		 * Register widget with WordPress.
		 */
		function __construct() {
			parent::__construct(
				'multi_language_widget', /* Base ID */
				'Multilanguage', /* Name */
				array( 'description' => __( 'Content language switcher', 'multilanguage' ), ) /* Args */
			);
		}

		/**
		 * Front-end display of widget.
		 *
		 * @see WP_Widget::widget()
		 *
		 * @param array $args	Widget arguments.
		 * @param array $instance Saved values from database.
		 */
		public function widget( $args, $instance ) {
			global $mltlngg_options;
			$mltlngg_language_switcher = ( isset( $instance['mltlngg_language_switcher'] ) ) ? $instance['mltlngg_language_switcher'] : $mltlngg_options['language_switcher'];
			$title = ( ! empty( $instance['title'] ) ) ? apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base ) : '';
			echo $args['before_widget'];
			echo $args['before_title'] . $title . $args['after_title'];
			/* Language switcher style */
			echo mltlngg_get_switcher_block( $mltlngg_language_switcher );
			echo $args['after_widget'];
		}

		/**
		 * Back-end widget form.
		 *
		 * @see WP_Widget::form()
		 *
		 * @param array $instance Previously saved values from database.
		 */
		public function form( $instance ) {
			global $mltlngg_options;
			$title = ( isset( $instance['title'] ) ) ? $instance['title'] : __( 'Localization', 'multilanguage' );
			$mltlngg_language_switcher = ( isset( $instance['mltlngg_language_switcher'] ) ) ? $instance['mltlngg_language_switcher'] : $mltlngg_options['language_switcher']; ?>
			<p>
				<label><?php _e( 'Title:' ); ?>
					<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
				</label>
			</p>
			<div style="clear: both;">
				<label class="mltlngg_widget_label">
					<input id="<?php echo $this->get_field_id( 'drop-down-list' ); ?>" name="<?php echo $this->get_field_name( 'mltlngg_language_switcher' ); ?>" type="radio" value="drop-down-list" <?php checked( $mltlngg_language_switcher, 'drop-down-list' ); ?> /><?php _e( 'Drop-down list', 'multilanguage' ); ?> (<?php _e( 'flag', 'multilanguage' ); ?> + <?php _e( 'title', 'multilanguage' ); ?>)

				</label>
			</div>
			<div style="clear: both;">
				<label class="mltlngg_widget_label">
					<input id="<?php echo $this->get_field_id( 'drop-down-titles' ); ?>" name="<?php echo $this->get_field_name( 'mltlngg_language_switcher' ); ?>" type="radio" value="drop-down-titles" <?php checked( $mltlngg_language_switcher, 'drop-down-titles' ); ?> /><?php _e( 'Drop-down list', 'multilanguage' ); ?> (<?php _e( 'title', 'multilanguage' ); ?>)

				</label>
			</div>
			<div style="clear: both;">
				<label class="mltlngg_widget_label">
					<input id="<?php echo $this->get_field_id( 'drop-down-icons' ); ?>" name="<?php echo $this->get_field_name( 'mltlngg_language_switcher' ); ?>" type="radio" value="drop-down-icons" <?php checked( $mltlngg_language_switcher, 'drop-down-icons' ); ?> /><?php _e( 'Drop-down list', 'multilanguage' ); ?> (<?php _e( 'flag', 'multilanguage' ); ?>)
				</label>
			</div>
			<div style="clear: both;">
				<label class="mltlngg_widget_label">
					<input id="<?php echo $this->get_field_id( 'flags-icons' ); ?>" name="<?php echo $this->get_field_name( 'mltlngg_language_switcher' ); ?>" type="radio" value="flags-icons" <?php checked( $mltlngg_language_switcher, 'flags-icons' ); ?> /><?php _e( 'Flag', 'multilanguage' ); ?>
				</label>
			</div>
			<div style="clear: both;">
				<label class="mltlngg_widget_label">
					<input id="<?php echo $this->get_field_id( 'gt' ); ?>" name="<?php echo $this->get_field_name( 'mltlngg_language_switcher' ); ?>" type="radio" value="gt" <?php checked( $mltlngg_language_switcher, 'gt' ); ?>><?php printf(
							'%s (%s)',
							__( 'Google Auto Translate', 'multilanguage' ),
							__( 'drop-down only', 'multilanguage' )
						); ?>
				</label>
			</div>
			<div style="clear: both;">
				<label class="mltlngg_widget_label">
					<input id="<?php echo $this->get_field_id( 'gt-horizontal' ); ?>" name="<?php echo $this->get_field_name( 'mltlngg_language_switcher' ); ?>" type="radio" value="gt-horizontal" <?php checked( $mltlngg_language_switcher, 'gt-horizontal' ); ?>><?php printf(
							'%s (%s)',
							__( 'Google Auto Translate', 'multilanguage' ),
							__( 'horizontal', 'multilanguage' )
						); ?>
				</label>
			</div>
			<div style="clear: both;">
				<label class="mltlngg_widget_label">
					<input id="<?php echo $this->get_field_id( 'gt-vertical' ); ?>" name="<?php echo $this->get_field_name( 'mltlngg_language_switcher' ); ?>" type="radio" value="gt-vertical" <?php checked( $mltlngg_language_switcher, 'gt-vertical' ); ?>><?php printf(
							'%s (%s)',
							__( 'Google Auto Translate', 'multilanguage' ),
							__( 'vertical', 'multilanguage' )
						); ?>
				</label>
			</div>
			<div style="clear: both;"></div>
		<?php }

		/**
		 * Sanitize widget form values as they are saved.
		 *
		 * @see WP_Widget::update()
		 *
		 * @param array $new_instance Values just sent to be saved.
		 * @param array $old_instance Previously saved values from database.
		 *
		 * @return array Updated safe values to be saved.
		 */
		public function update( $new_instance, $old_instance ) {
			$instance = array();
			$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
			$instance['mltlngg_language_switcher'] = $new_instance['mltlngg_language_switcher'];
			return $instance;
		}

	} /* class Mltlngg_Widget */
}

/* Register Multilanguage Widget widget */
if ( ! function_exists( 'register_mltlngg_widget' ) ) {
	function register_mltlngg_widget() {
		register_widget( 'Mltlngg_Widget' );
	}
}

if ( ! function_exists( 'mltlngg_get_switcher_block' ) ) {
	function mltlngg_get_switcher_block( $mltlngg_language_switcher = false ) {
		global $mltlngg_current_language, $mltlngg_get_default_language, $mltlngg_enabled_languages, $current_blog, $mltlngg_options, $wp_customize;

		if ( ! $mltlngg_language_switcher ) {
			$mltlngg_language_switcher = $mltlngg_options['language_switcher'];
		}

		if ( isset( $mltlngg_language_switcher['layout'] ) ) {
			$mltlngg_language_switcher = $mltlngg_language_switcher['layout'];
		}

		$switcher = '';

		/* Language switcher style */
		switch ( $mltlngg_language_switcher ) {
			case 'drop-down-titles':
				$options = '';
				foreach ( $mltlngg_enabled_languages as $item ) {
					if ( $item['locale'] != $mltlngg_current_language ) {
						$options .= '<li>
							<button class="mltlngg-lang-button-icons" name="mltlngg_change_display_lang" value="' . $item['locale'] . '" title="' . $item['name'] . '">'
								 . $item['name'] .
							'</button>
						</li>';
					} else {
						$current_language_name = $item['name'];
					}
				}
				$switcher .=
					'<ul class="mltlngg-lang-switch mltlngg-lang-switch-names">
						<li>
							<a>' . $current_language_name . '</a>
							<ul>' . $options . '</ul>
						</li>
					</ul>';
				break;
			case 'drop-down-icons':
				$options = '';
				foreach ( $mltlngg_enabled_languages as $item ) {
					if ( $item['locale'] != $mltlngg_current_language ) {
						$flag = ( empty( $item['flag'] ) ) ? plugins_url( 'images/flags/' , __FILE__ ) . $item['locale'] . '.png' : $item['flag'];
						$options .= '<li>
							<button class="mltlngg-lang-button-icons" name="mltlngg_change_display_lang" value="' . $item['locale'] . '" title="' . $item['name'] . '">
								<img class="mltlngg-lang" src="' . $flag . '" alt="' . $item['name'] . '">
							</button>
						</li>';
					} else {
						$current_language_flag = ( empty( $item['flag'] ) ) ? plugins_url( 'images/flags/' , __FILE__ ) . $item['locale'] . '.png' : $item['flag'];
					}
				}
				$switcher .=
					'<ul class="mltlngg-lang-switch">
						<li>
							<img src="' . $current_language_flag . '">
							<ul>' . $options . '</ul>
						</li>
					</ul>';
				break;
			case 'flags-icons':
				foreach ( $mltlngg_enabled_languages as $item ) {
					$flag = ( empty( $item['flag'] ) ) ? plugins_url( 'images/flags/' , __FILE__ ) . $item['locale'] . '.png' : $item['flag'];
					$switcher .=
						'<button class="mltlngg-lang-button-icons" name="mltlngg_change_display_lang" value="' . $item['locale'] . '" title="' . $item['name'] . '">
							<img class="' . ( $item['locale'] == $mltlngg_current_language ? 'mltlngg-current-lang' : 'mltlngg-lang' ) . '" src="' . $flag . '" alt="' . $item['name'] . '">
						</button>';
				}
				break;
			case 'gt':
				$switcher .= '<div id="mltlngg_google_switcher"></div>';
				mltlngg_enqueue_google_script( 'dropdown' );
				break;
			case 'gt-horizontal':
				$switcher .= '<div id="mltlngg_google_switcher"></div>';
				mltlngg_enqueue_google_script( 'horizontal' );
				break;
			case 'gt-vertical':
				$switcher .= '<div id="mltlngg_google_switcher"></div>';
				mltlngg_enqueue_google_script( 'vertical' );
				break;
			case 'drop-down-list':
			default:
				$options = '';
				foreach ( $mltlngg_enabled_languages as $item ) {
					if ( $item['locale'] != $mltlngg_current_language ) {
						$flag = ( empty( $item['flag'] ) ) ? plugins_url( 'images/flags/' , __FILE__ ) . $item['locale'] . '.png' : $item['flag'];
						$options .= '<li>
							<button class="mltlngg-lang-button-icons" name="mltlngg_change_display_lang" value="' . $item['locale'] . '" title="' . $item['name'] . '">
								<img class="mltlngg-lang" src="' . $flag . '" alt="' . $item['name'] . '"> ' . $item['name'] .
							'</button>
						</li>';
					} else {
						$current_language_name = $item['name'];
						$current_language_flag = ( empty( $item['flag'] ) ) ? plugins_url( 'images/flags/' , __FILE__ ) . $item['locale'] . '.png' : $item['flag'];
					}
				}
				$switcher .=
					'<ul class="mltlngg-lang-switch mltlngg-lang-switch-names">
						<li>
							<a>
								<img src="' . $current_language_flag . '"> ' . $current_language_name .
							'</a>
							<ul>' . $options . '</ul>
						</li>
					</ul>';
				break;
		}

		if ( ! in_array( $mltlngg_language_switcher, array( 'gt', 'gt-horizontal', 'gt-vertical' ) ) ) {
			$switcher = '<form class="mltlngg_switcher" name="mltlngg_change_language" method="post" action="">' . $switcher . '</form>';
		}

		return $switcher;
	}
}

if ( ! function_exists( 'mltlngg_display_switcher' ) ) {
	function mltlngg_display_switcher() {
		echo mltlngg_get_switcher_block();
	}
}

/* Display settings page of plugin */
if ( ! function_exists( 'mltlngg_settings_page' ) ) {
	function mltlngg_settings_page() {
		global $wpdb, $mltlngg_options, $mltlngg_languages, $mltlngg_plugin_info; ?>
		<div class="wrap" id="mltlngg-settings">
			<?php if ( 'mltlngg_settings' == $_GET['page'] ) { /* Showing settings tab */
				require_once( dirname( __FILE__ ) . '/includes/class-mltlngg-settings.php' );
				$page = new Mltlngg_Settings_Tabs( plugin_basename( __FILE__ ) ); ?>
				<h1>Multilanguage <?php _e( 'Settings', 'multilanguage' ); ?></h1>
				<?php $page->display_content();
			} else {
				mltlngg_table();
				bws_plugin_reviews_block( $mltlngg_plugin_info['Name'], 'multilanguage' );
			} ?>
		</div>
	<?php }
}

/* Adding to post/page editor tabs in enabled languages */
if ( ! function_exists( 'mltlngg_showup_language_tabs_in_editor' ) ) {
	function mltlngg_showup_language_tabs_in_editor() {
		global $wpdb, $post, $mltlngg_options, $mltlngg_language, $mltlngg_active_language, $mltlngg_current_language, $mltlngg_enabled_languages, $mltlngg_get_default_language;

		$current_posttype = get_post_type( $post->ID );
		if ( $current_posttype == 'post' || $current_posttype == 'page' ) {
			$original_data = $wpdb->get_row( $wpdb->prepare(
				"SELECT `post_content`, `post_title`, `post_excerpt`
				FROM $wpdb->posts
				WHERE `ID` = %d",
				$post->ID
			), ARRAY_A ); /* Get original Content & Title for save to hidden fields */ ?>
			<!-- Display tabs in editor for all enabled languages -->
			<div id="mltlngg-overlay">
				<div class="mltlngg_backdrop"></div>
				<div class="mltlngg_loader"></div>
			</div>
			<h2 id="get-lang-content" class="mltlngg-nav-tab-wrapper nav-tab-wrapper">
				<?php foreach ( $mltlngg_enabled_languages as $mltlngg_language ) {
					if ( ( isset( $_SESSION['current_language'] ) && $_SESSION['current_language'] == $mltlngg_language['locale'] ) || ( ! isset( $_SESSION['current_language'] ) && isset( $_GET['lang'] ) && $_GET['lang'] == $mltlngg_language['locale'] ) || ( ! isset( $_SESSION['current_language'] ) && ! isset( $_GET['lang'] ) && $mltlngg_options['default_language'] == $mltlngg_language['locale'] ) ) {
						$mltlngg_active_class = 'nav-tab nav-tab-active'; /* Set active language tab */
						$mltlngg_active_language = $mltlngg_language; /* Set active language */
					} else {
						$mltlngg_active_class = 'nav-tab';
					}
					echo '<a class="' . $mltlngg_active_class . '" href="' . get_edit_post_link( $post->ID, '' ) . '&lang=' . $mltlngg_language['locale'] . '" data-lang="' . $mltlngg_language['locale'] . '" value="' . $mltlngg_language['name'] . '">' . $mltlngg_language['name'] . '</a>';
				}
				unset( $_SESSION['current_language'] ); ?>
				<a class="mltlngg_add" href="admin.php?page=multilanguage-languages.php"><?php _e( 'Add Language', 'multilanguage' ); ?></a>
			</h2> <!-- #get-lang-content .nav-tab-wrapper -->
			<?php /* compability with DIVI Builder */
			if ( 'ajax' != $mltlngg_options['save_mode'] || defined( 'ET_BUILDER_THEME' ) || defined( 'WPB_VC_VERSION' ) ) { ?>
				<div id="mltlngg-disable-ajax-tabs" class="update-nag inline hide-if-no-js" style="margin: 0;">
					<div><strong><?php _e( 'Attention', 'multilanguage' ); ?></strong>: <?php _e( 'Please update the post before switching the language. Data is not saved automatically.', 'multilanguage' ); ?></div>
				</div>
			<?php } ?>
			<h2>
				<?php _e( 'Edit for language', 'multilanguage' ); ?>:
				<span id="mltlngg-current-lang"><?php echo $mltlngg_active_language['name']; ?></span>
			</h2>
			<?php wp_nonce_field( 'mltlngg_translate_form', 'mltlngg_translate_form_field' ); ?>
			<!-- Hidden fields with original Title, Content & Active language -->
			<?php if ( is_admin() ) {
				$mltlngg_current_language = ( isset( $_GET['lang'] ) ) ? $_GET['lang'] : ( ( isset( $mltlngg_active_language['locale'] ) ) ? $mltlngg_active_language['locale'] : $mltlngg_options['default_language'] );
			} ?>
			<input id="title-<?php echo $mltlngg_options['default_language']; ?>" type="hidden" value="<?php echo esc_html( $original_data['post_title'] ); ?>" name="title_<?php echo $mltlngg_options['default_language']; ?>">
			<textarea id="content-<?php echo $mltlngg_options['default_language']; ?>" style="display: none;" name="content_<?php echo $mltlngg_options['default_language']; ?>"><?php echo esc_html( $original_data['post_content'] ); ?></textarea>
			<input id="excerpt-<?php echo $mltlngg_options['default_language']; ?>" type="hidden" value="<?php echo esc_html( $original_data['post_excerpt'] ) ?>" name="excerpt_<?php echo $mltlngg_options['default_language']; ?>">
			<input id="mltlngg-active-language" type="hidden" value="<?php echo $mltlngg_active_language['locale']; ?>" name="mltlngg_active_language">
		<?php }
	}
}

/* Function for save post/page changes to translations tables */
if ( ! function_exists( 'mltlngg_save_post' ) ) {
	function mltlngg_save_post( $post_id ) {
		global $wpdb, $mltlngg_options;

		$current_posttype = get_post_type( $post_id );
		if ( $current_posttype == 'post' || $current_posttype == 'page' ) {
			if ( ( isset( $_POST['save'] ) || isset( $_POST['publish'] ) ) && check_admin_referer( 'mltlngg_translate_form', 'mltlngg_translate_form_field' ) ) {
				$_SESSION['current_language'] = $_POST['mltlngg_active_language'];

				/* If autosave option is disabled save all changes */
				if ( 'ajax' != $mltlngg_options['save_mode'] ) {
					/* Formation of a new array with the translation data from all hidden fields */
					$mltlngg_translate_data = array();
					foreach ( $_POST as $key => $value ) {
						if ( preg_match( '/^(title|content|excerpt)[_]([a-z]{2,3}|[a-z]{2,3}[_][A-Z]{2,3})$/', $key, $matches ) ) { /* Search POST with Title or Content */
							$mltlngg_translate_data[ $matches[2] ]['lang'] = $matches[2]; /* Language code */
							$mltlngg_translate_data[ $matches[2] ][ $matches[1] ] = $value; /* Title or Content or Excerpt*/
						}
					}
					/* Save the translation data from new array */
					foreach ( $mltlngg_translate_data as $mltlngg_translate ) {
						if ( $mltlngg_translate['lang'] != $_POST['mltlngg_active_language'] ) {
							/* If active language tab same as default language do not save changes */

							/* Get translation data for current language from database */
							$mltlngg_result = $wpdb->get_row( $wpdb->prepare(
								"SELECT *
								 FROM `" . $wpdb->prefix . "mltlngg_translate`
								 WHERE `post_ID` = %d AND `language` = %s
								", $post_id, $mltlngg_translate['lang']
							), ARRAY_A );
							$excerpt = isset( $mltlngg_translate['excerpt'] ) ? $mltlngg_translate['excerpt'] : '';
							if ( isset( $mltlngg_result['post_content'] ) && isset( $mltlngg_result['post_title'] ) ) { /* If translation is exist in database, update translation */
								if ( $mltlngg_translate['content'] != $mltlngg_result['post_content'] || $mltlngg_translate['title'] != $mltlngg_result['post_title'] || $excerpt != $mltlngg_result['post_excerpt'] ) {
									$wpdb->update(
										$wpdb->prefix . 'mltlngg_translate',
										array(
											'post_content'	=> wp_unslash( sanitize_post_field( 'post_content', $mltlngg_translate['content'], 0, 'db' ) ),
											'post_title'	=> wp_unslash( $mltlngg_translate['title'] ),
											'post_excerpt'	=> wp_unslash( $excerpt ),
										),
										array(
											'post_ID'	=> $post_id,
											'language'	=> $mltlngg_translate['lang']
										),
										array( '%s', '%s', '%s' ),
										array( '%d', '%s' )
									);
								}
							} elseif ( $mltlngg_translate['content'] != "" || $mltlngg_translate['title'] != "" ) {
								/* If translation is not exist in database, create translation */
								$wpdb->insert(
									$wpdb->prefix . 'mltlngg_translate',
									array(
										'post_ID'		=> $post_id,
										'post_content'	=> wp_unslash( sanitize_post_field( 'post_content', $mltlngg_translate['content'], 0, 'db' ) ),
										'post_title'	=> wp_unslash( $mltlngg_translate['title'] ),
										'post_excerpt'	=> wp_unslash( $excerpt ),
										'language'		=> $mltlngg_translate['lang']
									),
									array( '%d', '%s', '%s', '%s', '%s' )
								);
							}
						}
					}
				}
				/* Save the translation data from active language tab */

				/* Get translation data for current language from database */
				$mltlngg_result = $wpdb->get_row( $wpdb->prepare(
					"SELECT *
					 FROM `" . $wpdb->prefix . "mltlngg_translate`
					 WHERE `post_ID` = %d AND `language` = %s
					", $post_id, $_POST['mltlngg_active_language']
				), ARRAY_A );

				$excerpt = isset( $_POST['excerpt'] ) ? $_POST['excerpt'] : '';

				if ( isset( $mltlngg_result['post_content'] ) && isset( $mltlngg_result['post_title'] ) ) { /* If translation is exist in database, update translation */
					if ( $_POST['content'] != $mltlngg_result['post_content'] || $_POST['post_title'] != $mltlngg_result['post_title'] || $excerpt != $mltlngg_result['post_excerpt'] ) {
						$wpdb->update(
							$wpdb->prefix . 'mltlngg_translate',
							array(
								'post_content'	=> wp_unslash( sanitize_post_field( 'post_content', $_POST['content'] , 0, 'db' ) ),
								'post_title'	=> wp_unslash( $_POST['post_title'] ),
								'post_excerpt'	=> wp_unslash( $excerpt ),
							),
							array(
								'post_ID'	=> $post_id,
								'language'	=> $_POST['mltlngg_active_language']
							),
							array( '%s', '%s', '%s' ),
							array( '%d', '%s' )
						);
					}
				} else { /* If translation is not exist in database, create translation */
					$wpdb->insert(
						$wpdb->prefix . 'mltlngg_translate',
						array(
							'post_ID'		=> $post_id,
							'post_content'	=> wp_unslash( sanitize_post_field( 'post_content', $_POST['content'] , 0, 'db' ) ),
							'post_title'	=> wp_unslash( $_POST['post_title'] ),
							'post_excerpt'	=> wp_unslash( $excerpt ),
							'language'		=> $_POST['mltlngg_active_language']
						),
						array( '%d', '%s', '%s', '%s', '%s' )
					);
				}

				/* Save Title & Content to original post */
				if ( $mltlngg_options['default_language'] != $_POST['mltlngg_active_language'] && isset( $_POST['title_' . $mltlngg_options['default_language']] ) && isset( $_POST['content_' . $mltlngg_options['default_language']] ) ) {
					$default_excerpt = isset( $_POST['excerpt_' . $mltlngg_options['default_language'] ] ) ? $_POST['excerpt_' . $mltlngg_options['default_language'] ] : '';
					$post = array(
						'ID'			=> $post_id,
						'post_title'	=> wp_unslash( wp_specialchars_decode( $_POST['title_' . $mltlngg_options['default_language'] ], ENT_COMPAT ) ),
						'post_excerpt'	=> wp_unslash( wp_specialchars_decode( $default_excerpt, ENT_COMPAT ) ),
						'post_content'	=> wp_unslash( sanitize_post_field( 'post_content', wp_specialchars_decode( $_POST['content_' . $mltlngg_options['default_language'] ], ENT_COMPAT ), 0, 'db' ) )
					);
				} else {
					$post = array(
						'ID'			=> $post_id,
						'post_title'	=> wp_unslash( $_POST['post_title'] ),
						'post_excerpt'	=> wp_unslash( $excerpt ),
						'post_content'	=> wp_unslash( sanitize_post_field( 'post_content', $_POST['content'], 0, 'db' ) )
					);
				}
				remove_action( 'save_post', 'mltlngg_save_post' );
				wp_update_post( $post );
				add_action( 'save_post', 'mltlngg_save_post' );
			}
		}
	}
}

/* Callback function for AJAX function */
if ( ! function_exists( 'mltlngg_ajax_callback' ) ) {
	function mltlngg_ajax_callback() {
		global $wpdb, $mltlngg_options;
		check_ajax_referer( 'mltlngg-ajax-nonce', 'security' );
		if ( empty( $_POST['mltlngg_post_id'] ) ) {
			die();
		}
		$mltlngg_old_excerpt = isset( $_POST['mltlngg_old_excerpt'] ) ? $_POST['mltlngg_old_excerpt'] :'';
		$post_id = absint( $_POST['mltlngg_post_id'] );
		/* Auto-update translation if it has been changed when autosave option is enabled */
		if ( 'ajax' == $mltlngg_options['save_mode'] && ! empty( $_POST['old_lang'] ) ) {
			/* Get translation data for previous language from database */
			$mltlngg_result_old = $wpdb->get_row( $wpdb->prepare(
				"SELECT *
				 FROM `" . $wpdb->prefix . "mltlngg_translate`
				 WHERE `post_ID` = %d AND `language` = %s
				", $post_id, $_POST['old_lang']
			), ARRAY_A );
			if ( isset( $mltlngg_result_old['post_content'] ) && isset( $mltlngg_result_old['post_title'] ) ) {
				/* If translation is exist in database, update translation */
				if ( $_POST['mltlngg_old_content'] != $mltlngg_result_old['post_content'] || $_POST['mltlngg_old_title'] != $mltlngg_result_old['post_title'] || $mltlngg_old_excerpt != $mltlngg_result_old['post_excerpt'] ) {
					$wpdb->update(
						$wpdb->prefix . 'mltlngg_translate',
						array(
							'post_content'	=> wp_unslash( sanitize_post_field( 'post_content', $_POST['mltlngg_old_content'] , 0, 'db' ) ),
							'post_title'	=> wp_unslash( $_POST['mltlngg_old_title'] ),
							'post_excerpt'	=> wp_unslash( $mltlngg_old_excerpt ),
						),
						array(
							'post_ID'	=> $post_id,
							'language'	=> $_POST['old_lang']
						),
						array( '%s', '%s', '%s' ),
						array( '%d', '%s' )
					);
				}
			} elseif ( "" != $_POST['mltlngg_old_content'] || "" != $_POST['mltlngg_old_title'] ) {
				/* If translation not exist in database, create translation */
				$wpdb->insert(
					$wpdb->prefix . 'mltlngg_translate',
					array(
						'post_ID'		=> $post_id,
						'post_content'	=> wp_unslash( sanitize_post_field( 'post_content', $_POST['mltlngg_old_content'] , 0, 'db' ) ),
						'post_title'	=> wp_unslash( $_POST['mltlngg_old_title'] ),
						'post_excerpt'	=> wp_unslash( $mltlngg_old_excerpt ),
						'language'		=> $_POST['old_lang']
					),
					array( '%d', '%s', '%s', '%s', '%s' )
				);
			}
		}
		/* If received request for a translation for current language */
		if ( isset( $_POST['get_data'] ) && ! empty( $_POST['new_lang'] ) ) {
			$mltlngg_new_cat_data = array( "cat_translate" => "" );

			 /* Get translation data for current language from database */
			$mltlngg_post_data = $wpdb->get_row( $wpdb->prepare(
				"SELECT *
				 FROM `" . $wpdb->prefix . "mltlngg_translate`
				 WHERE `post_ID` = %d AND `language` = %s
				", $post_id, $_POST['new_lang']
			), ARRAY_A );
			if ( isset( $_POST['cat_id'] ) ) {
				$mltlngg_cat_data = $wpdb->get_results( $wpdb->prepare(
					"SELECT *
					 FROM `" . $wpdb->prefix . "mltlngg_terms_translate`
					 WHERE `language` = %s
					", $_POST['new_lang']
				), ARRAY_A ); /* Get translation data for current language from database */
			}

			if ( ! empty( $mltlngg_cat_data ) ) {
				foreach ( $mltlngg_cat_data as $value ) {
					if ( in_array( $value['term_ID'], $_POST['cat_id'] ) ) {
						$mltlngg_new_cat_mame[ $value['term_ID'] ] = $value['name'];
					}
				}
				if ( ! empty( $mltlngg_new_cat_mame ) ) {
					$mltlngg_new_cat_data = array( "cat_translate" => $mltlngg_new_cat_mame );
				}
			}
			/* add comments before our results */
			echo '<!--mltlngg-ajax-results-->';
			if ( isset( $mltlngg_post_data['post_content'] ) && isset( $mltlngg_post_data['post_title'] ) ) { /* If translation is exist, send translation to ajax */
				echo json_encode( array_merge( $mltlngg_post_data, $mltlngg_new_cat_data ) );
			} else { /* If translation is not exist, send empty translation to ajax */
				$mltlngg_post_data = array(
					'post_content'	=> "",
					'post_title'	=> "",
					'post_excerpt'	=> ""
				);
				echo json_encode( array_merge( $mltlngg_post_data, $mltlngg_new_cat_data ) );
			}
			/* add comments after our results */
			echo '<!--end-mltlngg-ajax-results-->';
		}
		die();
	}
}

/* Add translations form in enabled languages to adding new category/tag */
if ( ! function_exists( 'mltlngg_new_terms_translate' ) ) {
	function mltlngg_new_terms_translate() {
		global $mltlngg_enabled_languages;
		$locale_name = get_locale(); ?>
		<div class="form-field">
			<?php _e( 'Translations', 'multilanguage' ); ?>
			<!-- Translations form for all enabled languages -->
			<table class="form-table" border="0" style="background-color: #fff; width: 95%;">
				<tbody>
				<?php foreach ( $mltlngg_enabled_languages as $one_language ) {
					$flag = ( empty( $one_language['flag'] ) ) ? plugins_url( 'images/flags/' , __FILE__ ) . $one_language['locale'] . '.png' : $one_language['flag']; ?>
					<tr class="form-field" <?php if( $locale_name == $one_language['locale'] ) echo 'style="display:none"'; ?>>
						<td>
							<label for="mltlngg_translate_<?php echo $one_language['locale']; ?>"><img style="margin: auto 5px;" src="<?php echo $flag; ?>" alt="<?php echo $one_language['name']; ?>" title="<?php echo $one_language['name']; ?>" /><?php echo $one_language['name']; ?></label>
						</td>
						<td>
							<input type="text" aria-required="true" size="40" value="" id="mltlngg_translate_<?php echo $one_language['locale']; ?>" name="mltlngg_translate_<?php echo $one_language['locale']; ?>">
						</td>
					</tr>
				<?php } ?>
				</tbody>
			</table><!-- .form-table -->
			<?php wp_nonce_field( 'mltlngg_translate_terms_form', 'mltlngg_translate_terms_form_field' ); ?>
		</div><!-- .form-field -->
	<?php }
}

/* Function for create new category/tag with translations */
if ( ! function_exists( 'mltlngg_new_terms_created' ) ) {
	function mltlngg_new_terms_created( $term_id ) {
		global $wpdb, $mltlngg_enabled_languages;
		$locale_name = get_locale();
		foreach ( $mltlngg_enabled_languages as $one_language ) {
			$cat_lang = 'mltlngg_translate_' . $one_language['locale'];
			$cat_name = ( $one_language['locale'] == $locale_name ) ? $_POST['tag-name'] : $_POST[ $cat_lang ];
			if ( isset( $cat_name ) && "" != $_POST[ $cat_lang ] ) { /* If translation is empty, do not create translation in database */
				$wpdb->insert(
					$wpdb->prefix . 'mltlngg_terms_translate',
					array(
						'term_ID'	=> $term_id,
						'name'		=> wp_unslash( $cat_name ),
						'language'	=> $one_language['locale']
					),
					array( '%d', '%s', '%s' )
				);
			}
		}
	}
}

/* Adding translations form for all enabled languages to edit category/tag */
if ( ! function_exists( 'mltlngg_terms_translate' ) ) {
	function mltlngg_terms_translate() {
		global $tag_ID, $wpdb, $mltlngg_enabled_languages;
		$locale_name = get_locale();
		$mltlngg_term = array();
		foreach ( $mltlngg_enabled_languages as $one_language ) {
			/* Get terms translations from database */
			$mltlngg_term_result = $wpdb->get_row( $wpdb->prepare(
				"SELECT *
				 FROM `" . $wpdb->prefix . "mltlngg_terms_translate`
				 WHERE `term_ID` = %d AND `language` = %s
				", $tag_ID, $one_language['locale']
			), ARRAY_A );
			$mltlngg_term[ $one_language['locale'] ] = $mltlngg_term_result['name'];
		} ?>
		<table class="form-table">
			<tbody>
			<tr class="form-field">
				<th scope="row"><?php _e( 'Translations', 'multilanguage' ); ?></th>
				<td>
					<table class="form-table" border="0" style="background-color: #fff; width: 95%;">
						<tbody>
						<?php foreach ( $mltlngg_enabled_languages as $one_language ) {
							$flag = ( empty( $one_language['flag'] ) ) ? plugins_url( 'images/flags/' , __FILE__ ) . $one_language['locale'] . '.png' : $one_language['flag']; ?>
							<tr class="form-field" <?php if( $locale_name == $one_language['locale'] ) echo 'style="display:none"'; ?>>
								<td>
									<label for="mltlngg_translate_<?php echo $one_language['locale']; ?>"><img style="margin: auto 5px;" src="<?php echo $flag; ?>" alt="<?php echo $one_language['name']; ?>" title="<?php echo $one_language['name']; ?>" /><?php echo $one_language['name']; ?></label>
								</td>
								<td>
									<input type="text" aria-required="true" size="40" value="<?php echo $mltlngg_term[ $one_language['locale'] ]; ?>" id="mltlngg_translate_<?php echo $one_language['locale']; ?>" name="mltlngg_translate_<?php echo $one_language['locale']; ?>">
								</td>
							</tr>
						<?php } ?>
						</tbody>
					</table><!-- .form-table -->
				</td>
			</tr>
			</tbody>
		</table><!-- .form-table -->
		<?php wp_nonce_field( 'mltlngg_translate_terms_form', 'mltlngg_translate_terms_form_field' );
	}
}

/* Function for update translations of category/tag */
if ( ! function_exists( 'mltlngg_terms_update' ) ) {
	function mltlngg_terms_update() {
		global $tag_ID, $wpdb, $mltlngg_enabled_languages;
		$locale_name = get_locale();
		if ( isset( $_POST['action'] ) && 'inline-save-tax' == $_POST['action'] ) {
			$wpdb->update(
				$wpdb->prefix . 'mltlngg_terms_translate',
				array(
					'name'	=> $_POST['name'],
				),
				array(
					'term_ID'	=> $_POST['tax_ID'],
					'language'	=> $locale_name
				),
				array( '%s' ),
				array( '%d', '%s' )
			);
		}
		if ( check_admin_referer( 'mltlngg_translate_terms_form', 'mltlngg_translate_terms_form_field' ) ) {
			foreach ( $mltlngg_enabled_languages as $one_language ) {
				$cat_lang = 'mltlngg_translate_' . $one_language['locale'];

				$mltlngg_result = $wpdb->get_row( $wpdb->prepare(
					"SELECT *
					 FROM `" . $wpdb->prefix . "mltlngg_terms_translate`
					 WHERE `term_ID` = %d AND `language` = %s
					", $tag_ID, $one_language['locale']
				), ARRAY_A ); /* Get terms translations from database */
				$cat_name = ( $mltlngg_result['language'] == $locale_name ) ? $_POST['name'] : $_POST[ $cat_lang ];
				if ( isset( $mltlngg_result['name'] ) && ( ( $_POST[ $cat_lang ] != $mltlngg_result['name'] ) || ( $mltlngg_result['language'] == $locale_name ) ) ) { /* If translation is exist in database, update translation */
					$wpdb->update(
						$wpdb->prefix . 'mltlngg_terms_translate',
						array(
							'name'	=> wp_unslash( $cat_name )
						),
						array(
							'term_ID'	=> $tag_ID,
							'language'	=> $one_language['locale']
						),
						array( '%s' ),
						array( '%d', '%s' )
					);
				} elseif ( ! isset( $mltlngg_result['name'] ) && "" != $_POST[ $cat_lang ] ) {
					/* If translation is not exist in database, create translation */
					$wpdb->insert(
						$wpdb->prefix . 'mltlngg_terms_translate',
						array(
							'term_ID'	=> $tag_ID,
							'name'		=> wp_unslash( $cat_name ),
							'language'	=> $one_language['locale']
						),
						array( '%d', '%s', '%s' )
					);
				}
			}
		}
	}
}

/* Function for delete post translations */
if ( ! function_exists( 'mltlngg_delete_post' ) ) {
	function mltlngg_delete_post( $postid ) {
		global $wpdb;
		$wpdb->query( $wpdb->prepare( "DELETE FROM `" . $wpdb->prefix . "mltlngg_translate` WHERE `post_ID` = %d", $postid ) );
	}
}

/* Function for delete terms translations */
if ( ! function_exists( 'mltlngg_delete_term' ) ) {
	function mltlngg_delete_term( $term ) {
		global $wpdb;
		$wpdb->query( $wpdb->prepare( "DELETE FROM `" . $wpdb->prefix . "mltlngg_terms_translate` WHERE `term_ID` = %d", $term ) );
	}
}

/* add a class with language name */
if ( ! function_exists ( 'mltlngg_add_body_classes' ) ) {
	function mltlngg_add_body_classes( $classes ) {
		global $mltlngg_current_language;
		if ( ! empty( $mltlngg_current_language ) ) {
			$classes[] = 'mltlngg-' . $mltlngg_current_language;
		}
		return $classes;
	}
}

/* Display post_title in the selected language */
if ( ! function_exists( 'mltlngg_the_title_filter' ) ) {
	function mltlngg_the_title_filter( $title, $post_id = null ) {
		global $mltlngg_options, $wpdb, $mltlngg_current_language, $mltlngg_enabled_languages_locale, $mltlngg_active_language, $post;
		if ( ! empty( $post_id ) ) {
			$id = ( $post_id instanceof WP_Post ) ? $post_id->ID : $post_id;

			/* Do not filter, if a navigation menu */
			if ( ! is_nav_menu_item( $id ) ) {
				$mltlngg_post_type = get_post_type( $post_id );
				/* If current post type enabled to translation */
				if ( $mltlngg_post_type == 'post' || $mltlngg_post_type == 'page' ) {
					$is_admin = mltlngg_is_admin();
					if ( $is_admin ) {
						$mltlngg_current_language = ( ! empty( $_SESSION['current_language'] ) ) ? $_SESSION['current_language'] : ( ( isset( $_GET['lang'] ) && in_array( $_GET['lang'], $mltlngg_enabled_languages_locale ) ) ? $_GET['lang'] : ( ( ! isset( $_GET['message'] ) ) ? $mltlngg_options['default_language'] : $mltlngg_active_language['locale'] ) );
					}

					$new_title = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `" . $wpdb->prefix . "mltlngg_translate` WHERE `post_ID` = %d AND `language` = %s", $id, $mltlngg_current_language ), ARRAY_A );
					/* If translation is exist and not empty, filter title */
					if ( isset( $new_title['post_title'] ) && "" != $new_title['post_title'] ) {
						$title = $new_title['post_title'];
						if ( ! $is_admin ) {
							if ( ! empty( $post->post_password ) ) {
								$protected_title_format = apply_filters( 'protected_title_format', __( 'Protected: %s', 'multilanguage' ), $post );
								$title = sprintf( $protected_title_format, $title );
							} else if ( isset( $post->post_status ) && 'private' == $post->post_status ) {
								$private_title_format = apply_filters( 'private_title_format', __( 'Private: %s', 'multilanguage' ), $post );
								$title = sprintf( $private_title_format, $title );
							}
						}
					}
				}
			}
		}
		return $title;
	}
}

if ( ! function_exists( 'mltlngg_wpseo_the_title_filter' ) ) {
	function mltlngg_wpseo_the_title_filter( $title = '' ) {
		global $post;

		if ( isset( $post ) && $post instanceof WP_Post ) {
			$title = mltlngg_the_title_filter( $title, $post->ID );
		}

		return $title;
	}
}

/*
* Display nav_menu title in the selected language && Add language switcher to the menu
*
* @param array $items menu items
* @return array modified items
*/
if ( ! function_exists( 'mltlngg_nav_menu_items_filter' ) ) {
	function mltlngg_nav_menu_items_filter( $items ) {
		if ( function_exists( 'doing_action' ) && doing_action( 'customize_register' ) ) {
			return $items;
		}

		global $mltlngg_options, $wpdb, $mltlngg_current_language, $mltlngg_enabled_languages, $mltlngg_get_default_language, $mltlngg_enabled_languages_locale;

		$is_admin = mltlngg_is_admin();
		$new_items = array();
		$offset = 0;

		foreach ( $items as $key => $item ) {
			if ( $item->type == 'taxonomy' ) {
				$new_title = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT `name`
						 FROM `" . $wpdb->prefix . "mltlngg_terms_translate`
						 WHERE `term_ID` = %d AND `language` = %s
						", $item->object_id, $mltlngg_current_language
					)
				);
				if ( isset( $new_title ) && "" != $new_title ) /* If translation is exist and not empty, filter menu item */
					$item->title = $new_title;
			} elseif ( $item->type == 'post_type' && ( $item->object == 'post' || $item->object == 'page' ) ) {
				$new_title = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT `post_title`
						 FROM `" . $wpdb->prefix . "mltlngg_translate`
						 WHERE `post_ID` = %d AND `language` = %s
						", $item->object_id, $mltlngg_current_language
					)
				);
				if ( isset( $new_title ) && "" != $new_title ) /* If translation is exist and not empty, filter menu item */
					$item->title = $new_title;
			}

			/* split the language switcher menu item in several language menu items */
			if ( ! $is_admin && $mltlngg_language_switcher = get_post_meta( $item->ID, '_mltlngg_menu_item', true ) ) {

				$i = 0;

				$is_dropdown = in_array( $mltlngg_language_switcher, array( 'drop-down-icons', 'drop-down-list', 'drop-down-titles' ) );
				$is_icon = in_array( $mltlngg_language_switcher, array( 'drop-down-icons', 'aligned-icons' ) );
				$is_title = in_array( $mltlngg_language_switcher, array( 'drop-down-titles', 'aligned-titles' ) );

				$classes = array( 'mltlngg-menu-item' );
				if ( 'drop-down-icons' == $mltlngg_language_switcher ) {
					$classes[] = 'mltlngg-menu-item-icon';
				}
				$classes_current = array( 'mltlngg-menu-item-current' );
				if ( ! $is_dropdown ) {
					$classes_current[] = 'current-menu-item';
				}
				/* main li is a current language */
				foreach ( $mltlngg_enabled_languages as $lang ) {
					if ( $mltlngg_current_language == $lang['locale'] ) {
						$mltlngg_current_language_name = $lang['name'];
						$mltlngg_current_language_flag = ( empty( $lang['flag'] ) ) ? plugins_url( 'images/flags/' , __FILE__ ) . $lang['locale'] . '.png' : $lang['flag'];
						break;
					}
				}

				if ( $is_dropdown ) {
					$main_id = $item->ID;
					$lang_item = clone $item;
					if ( $is_title ) {
						$lang_item->title = esc_html( $mltlngg_current_language_name );
					} else {
						$flag_img = '<img class="mltlngg-lang" src="' . $mltlngg_current_language_flag . '" alt="' . $mltlngg_current_language_name . '">';
						$lang_item->title = ( $is_icon ) ? $flag_img : $flag_img . '&nbsp;' . esc_html( $mltlngg_current_language_name );
					}
					$lang_item->classes = array_merge( $classes, $classes_current );
					$lang_item->url = '#';
					$lang_item->menu_order += $offset + $i++;
					$new_items[] = $lang_item;
					$offset += $i - 1;
				}

				foreach ( $mltlngg_enabled_languages as $lang ) {
					if ( $mltlngg_current_language == $lang['locale'] && $is_dropdown ) {
						continue;
					}

					$args = array(
						'lang' => $lang['locale'],
						'force' => 'display'
					);

					$language_link = mltlngg_get_lang_link( $args );
					$language_link = esc_url( $language_link );

					$lang_item = clone $item;
					$lang_item->ID = $lang_item->ID . '-' . $lang['locale'];
					$lang_item->db_id = $lang_item->ID . '-' . $lang['locale'];
					if ( $is_title ) {
						$lang_item->title = esc_html( $lang['name'] );
					} else {
						$flag = ( empty( $lang['flag'] ) ) ? plugins_url( 'images/flags/' , __FILE__ ) . $lang['locale'] . '.png' : $lang['flag'];
						$flag_img = '<img class="mltlngg-lang" src="' . $flag . '" alt="' . $lang['name'] . '">';
						$lang_item->title = ( $is_icon ) ? $flag_img : $flag_img . '&nbsp;' . esc_html( $lang['name'] );
					}
					if ( $mltlngg_current_language == $lang['locale'] ) {
						$lang_item->classes = array_merge( $classes, $classes_current );
					} else {
						$lang_item->classes = $classes;
					}
					$lang_item->url = $language_link;
					if ( $is_dropdown ) {
						$lang_item->menu_item_parent = $main_id;
					}
					$lang_item->lang = $lang['locale'];
					$lang_item->menu_order += $offset + $i++;

					$new_items[] = $lang_item;
				}
				$offset += $i - 1;
			} else {
				$item->menu_order += $offset;
				$new_items[] = $item;
			}
		}
		return $new_items;
	}
}

/* Add action to filter the main content in the tinymce editor */
if ( ! function_exists( 'mltlngg_set_main_content_filter' ) ) {
	function mltlngg_set_main_content_filter( $post ) {
		add_action( 'the_editor_content', 'mltlngg_the_content_filter', 10, 2 );
	}
}

/* Remove action from the tinymce editor */
if ( ! function_exists( 'mltlngg_unset_main_content_filter' ) ) {
	function mltlngg_unset_main_content_filter( $post ) {
		remove_action( 'the_editor_content', 'mltlngg_the_content_filter', 10, 2 );
	}
}

/* Display post_content in the selected language */
if ( ! function_exists( 'mltlngg_the_content_filter' ) ) {
	function mltlngg_the_content_filter( $content, $more_link_text = null, $strip_teaser = false ) {
		global $hook_suffix, $post, $wpdb, $wp_current_filter, $mltlngg_current_language, $mltlngg_active_language, $mltlngg_options, $wp_list_table;

		$is_admin = mltlngg_is_admin();

		if ( $is_admin && ! ( 'post.php' == $hook_suffix || 'post-new.php' == $hook_suffix ) ) {
			return $content;
		}

		/* exclude filter for wp_editor() in 'Add new Comment' block */
		if ( $is_admin && $wp_list_table && 'WP_Post_Comments_List_Table' == get_class( $wp_list_table ) ) {
			return $content;
		}

		$mltlngg_post_type = get_post_type( $post->ID );
		/* If current post type enabled to translation */
		if ( $mltlngg_post_type == 'post' || $mltlngg_post_type == 'page' ) {
			if ( $is_admin ) {
				$mltlngg_current_language = ( isset( $_GET['lang'] ) ) ? $_GET['lang'] : ( ( isset( $mltlngg_active_language['locale'] ) ) ? $mltlngg_active_language['locale'] : $mltlngg_options['default_language'] );
			}

			$new_content = $wpdb->get_var( $wpdb->prepare( "SELECT `post_content` FROM `" . $wpdb->prefix . "mltlngg_translate` WHERE `post_ID` = %d AND `language` = %s ", $post->ID, $mltlngg_current_language ) );
			if ( ! empty( $new_content ) ) {
				if ( ! $is_admin ) {
					if ( ! post_password_required() ) {
						$noteaser		= ( ( false !== strpos( $new_content, '<!--noteaser-->' ) ) ? true : false );
						$extends		= get_extended( $new_content );
						$extended		= $extends['extended'];
						$new_content	= $extends['main'];

						if ( ! empty( $mltlngg_options['video_providers'] ) ) {
							foreach( $mltlngg_options['video_providers'] as $reg_exp ) {
								preg_match( $reg_exp, $new_content, $matches );
								if ( ! empty( $matches[0] ) ) {
									$string			= strstr( $matches[0], '<', true );
									$match			= $string ? '[(' . preg_quote( $string ) . ')]' : '[(' . preg_quote( $matches[0] ) . ')]';
									$new_content	= preg_replace_callback( $match, "mltlngg_videos_filter", $new_content );
								}
								preg_match( $reg_exp, $new_content, $matches );
								if ( ! empty( $matches[0] ) ) {
									$string		= strstr( $matches[0], '<', true );
									$match		= $string ? '[(' . preg_quote( $string ) . ')]' : '[(' . preg_quote( $matches[0] ) . ')]';
									$extended	= preg_replace_callback( $match, "mltlngg_videos_filter", $extended );
								}
							}
						}

						if ( ! is_single() && ! is_page() && ! is_search() ) {
							$more_link_text = __( '(more&hellip;)' );
							$more_link = apply_filters( 'the_content_more_link', ' <a href="' . get_permalink() . "#more-{$post->ID}\" class=\"more-link\">$more_link_text</a>", $more_link_text );

							if ( '' != $extended ) {
								$new_content .= $more_link;
							}
						} elseif ( is_page() ) {
							$new_content .= $extended;
						} else {
							if ( $noteaser ) {
								$new_content = '';
							}

							$new_content .= ( 0 != strlen( $new_content ) ) ? '<span id="more-' . $post->ID . '"></span>' . $extended : $extended;
						}

						/* if it is the_excerpt - remove shortcodes */
						if ( ! empty( $wp_current_filter ) && in_array( 'get_the_excerpt', $wp_current_filter ) ) {
							$new_content = strip_shortcodes( $new_content );
						}

						if ( 0 != strlen( $new_content ) ) {
							return force_balance_tags( $new_content );
						}
					} else {
						$content = get_the_password_form();
					}
				/* If translation is exist and not empty, filter content */
				} else {
					$content = $new_content;
				}
			}
		}
		return $content;
	}
}

if ( ! function_exists( 'mltlngg_providers_filter' ) ) {
	function mltlngg_providers_filter ( $matches ) {
		return $matches[1];
	}
}

/* filter for video on content */
if ( ! function_exists( 'mltlngg_videos_filter' ) ) {
	function mltlngg_videos_filter( $matches ) {
		return "\n" . $matches[0] . "\n";
	}
}

if ( ! function_exists( 'mltlngg_update_video_options' ) ) {
	function mltlngg_update_video_options() {
		if ( ! is_network_admin() ) {
			global $mltlngg_options;

			if ( empty( $mltlngg_options ) ) {
				$mltlngg_options = get_option( 'mltlngg_options' );
				if ( empty( $mltlngg_options ) ) {
					mltlngg_register_settings();
				}
			}

			require_once( ABSPATH . WPINC . '/class-oembed.php' );
			$oembed = _wp_oembed_get_object();

			if ( ! empty( $oembed->providers ) && is_array( $oembed->providers ) ) {
				$mltlngg_options['video_providers'] = array();
				foreach( $oembed->providers as $provider => $url ) {
					$reg_exp = preg_replace_callback( '/^#(.*)((\.\*\#\i)|(\.\*\#)|(\*\#))$/', 'mltlngg_providers_filter', $provider );
					$reg_exp = preg_replace_callback( '/^(.*)(\*)$/', 'mltlngg_providers_filter', $reg_exp );
					$reg_exp = '[(' . $reg_exp . ")(([^\s]*)|([^\t]*)|([^\n]*)|([^\<]*))]i";
					$mltlngg_options['video_providers'][] = $reg_exp;
				}
			}
		}
	}
}

/**
 * Filter the search of terms during the post editing
 * @param    array      $cache      cached array of terms for the given taxonomy
 * @return   array      $cache      extended array of terms
 */

if ( ! function_exists( 'mltlngg_fiter_terms_search' ) ) {
	function mltlngg_fiter_terms_search( $cache ) {

		if (
			isset( $_GET['action'] ) && 'ajax-tag-search' == $_GET['action'] &&
			isset( $_GET['q'] )      && ! empty( $_GET['q'] ) &&
			isset( $_GET['tax'] )    && ! empty( $_GET['tax'] )
		) {
			global $wpdb;

			$term_name	= esc_sql( $_GET[ 'q' ] );
			$tax_name	= esc_sql( $_GET[ 'tax' ] );

			/* looking for the term in accordance with the entered value for the current taxonomy */
			$terms = $wpdb->get_col(
				"SELECT DISTINCT `name`
				FROM `{$wpdb->terms}`
				WHERE `term_id` IN (
					SELECT DISTINCT `{$wpdb->prefix}mltlngg_terms_translate`.`term_ID`
					FROM `{$wpdb->prefix}mltlngg_terms_translate`
					LEFT JOIN `{$wpdb->term_taxonomy }`
						ON `taxonomy`='{$tax_name}'
					WHERE `name` LIKE '%{$term_name}%' AND `{$wpdb->prefix}mltlngg_terms_translate`.`term_ID`=`{$wpdb->term_taxonomy }`.`term_ID`
				);"
			);

			if ( empty( $terms ) || ! is_array( $terms ) ) {
				return $cache;
			}

			$cache = array_merge( $cache, array_filter( $terms ) );
			$cache = array_unique( $cache );
		}

		return $cache;
	}
}

/* Display categories list & tags cloud & categories/tags of posts in the selected language */
if ( ! function_exists( 'mltlngg_terms_filter' ) ) {
	function mltlngg_terms_filter( $terms ) {
		global $wpdb, $mltlngg_current_language;

		/* Filter the search of terms during the post editing */
		if (
			isset( $_GET['action'] ) && 'ajax-tag-search' == $_GET['action'] &&
			isset( $_GET['q'] )      && ! empty( $_GET['q'] ) &&
			isset( $_GET['tax'] )    && ! empty( $_GET['tax'] )
		) {
			$term_name	= esc_sql( $_GET[ 'q' ] );
			$tax_name	= esc_sql( $_GET[ 'tax' ] );

			/* looking for the term in accordance with the entered value for the current taxonomy */
			$terms_array = $wpdb->get_col(
				"SELECT DISTINCT `name`
				FROM `{$wpdb->terms}`
				WHERE `term_id` IN (
					SELECT DISTINCT `{$wpdb->prefix}mltlngg_terms_translate`.`term_ID`
					FROM `{$wpdb->prefix}mltlngg_terms_translate`
					LEFT JOIN `{$wpdb->term_taxonomy }`
						ON `taxonomy`='{$tax_name}'
					WHERE `name` LIKE '%{$term_name}%' AND `{$wpdb->prefix}mltlngg_terms_translate`.`term_ID`=`{$wpdb->term_taxonomy }`.`term_ID`
				);"
			);

			if ( empty( $terms_array ) || ! is_array( $terms_array ) ) {
				return $terms;
			}

			$terms = array_merge( $terms, array_filter( $terms_array ) );
			$terms = array_unique( $terms );
		} else {
			/* Filter terms before the displaying in the frontend */
			foreach ( $terms as $mltlngg_one_term ) {
				if ( isset( $mltlngg_one_term->term_id ) ) {
					$mltlngg_one_term_name = $wpdb->get_var(
						$wpdb->prepare(
							"SELECT `name`
							 FROM `" . $wpdb->prefix . "mltlngg_terms_translate`
							 WHERE `term_ID`=%d AND `language`='%s'
							", $mltlngg_one_term->term_id, $mltlngg_current_language
						)
					);
					if ( isset( $mltlngg_one_term_name ) && "" != $mltlngg_one_term_name ) /* If translation is exist and not empty, filter terms */
						$mltlngg_one_term->name = $mltlngg_one_term_name;
				}
			}
		}
		return $terms;
	}
}

/* Display category & tags names in the title of archive pages in the selected language */
if ( ! function_exists( 'mltlngg_term_filter' ) ) {
	function mltlngg_term_filter( $term ) {
		global $wpdb, $mltlngg_current_language;
		$mltlngg_term_name = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT `name`
				 FROM `" . $wpdb->prefix . "mltlngg_terms_translate`
				 WHERE `term_ID` = %d AND `language` = %s
				", $term->term_id, $mltlngg_current_language
			)
		);
		if ( isset( $mltlngg_term_name ) && "" != $mltlngg_term_name ) /* If translation is exist and not empty, filter term */
			$term->name = $mltlngg_term_name;
		return $term;
	}
}

/* Fixed pagination for default permalinks */
if ( ! function_exists( 'mltlngg_paginate_url_translate' ) ) {
	function mltlngg_paginate_url_translate( $link ) {
		global $mltlngg_current_language;
		if ( strpos( $link, '?lang=' . $mltlngg_current_language . '/' ) ) {
			$link = str_replace( '?lang=' . $mltlngg_current_language . '/', '/', $link );
		}
		return $link;
	}
}
/* Fixed author url for default permalinks */
if ( ! function_exists( 'mltlngg_author_url_fix' ) ) {
	function mltlngg_author_url_fix( $link, $author_id, $author_nicename ) {
		if ( strpos( $link, '?lang=' ) && strpos( $link, '?author=' ) ) {
			$link = str_replace( '?author=', '&author=', $link );
		}
		return $link;
	}
}

if ( ! function_exists( 'mltlngg_localize_excerpt' ) ) {
	function mltlngg_localize_excerpt( $excerpt ) {
		global $wpdb, $post, $mltlngg_current_language, $mltlngg_get_default_language;
		/* If current post type enabled to translation */
		$post_type = get_post_type( $post->ID );
		if ( in_array( $post_type, array( 'post', 'page' ) ) ) {
			$excerpt_new = $wpdb->get_var( $wpdb->prepare( "SELECT `post_excerpt` FROM `" . $wpdb->prefix . "mltlngg_translate` WHERE `post_ID` = %d AND `language` = %s", $post->ID, $mltlngg_current_language ) );
			if ( ! empty( $excerpt_new ) ) {
				return $excerpt_new;
			} else {
				$excerpt_new = $wpdb->get_var( $wpdb->prepare( "SELECT `post_content` FROM `" . $wpdb->prefix . "mltlngg_translate` WHERE `post_ID` = %d AND `language` = %s", $post->ID, $mltlngg_current_language ) );
				$excerpt_new = mb_strimwidth( $excerpt_new, 0, 250 );
				$excerpt_new = $excerpt_new . '...';
				return $excerpt_new;
			}
		}
		return $excerpt;
	}
}

if ( ! function_exists( 'mltlngg_composer_settings_filter' ) ) {
	function mltlngg_composer_settings_filter( $settings, $post_id, $post ) {
		global $wpdb;

		$grid = new Vc_Hooks_Vc_Grid();

		/**
		 * By the moment when we parse post content edited data will be saved,
		 * that is why we always get actual data.
		 */
		$post_data = $wpdb->get_results(
			"SELECT `post_content` FROM `{$wpdb->posts}` WHERE `ID`={$post_id}
			UNION
			SELECT `post_content` FROM `{$wpdb->prefix}mltlngg_translate` WHERE `post_ID`={$post_id};"
		);

		if ( empty( $post_data ) ) {
			return $settings;
		}

		/**
		 * Fetch all grid settings from the post content for each language
		 */
		$all_settings = array( 'vc_grid_id' => array( 'shortcodes' => array() ) );
		foreach( (array)$post_data as $data ) {
			$data->post_content = stripslashes( $data->post_content );
			$lang_settings = $grid->gridSavePostSettingsId( $settings, $post_id, $data );

			if ( empty( $lang_settings['vc_grid_id']['shortcodes'] ) ) {
				continue;
			}

			$all_settings['vc_grid_id']['shortcodes'] = array_merge( $all_settings['vc_grid_id']['shortcodes'], $lang_settings['vc_grid_id']['shortcodes'] );
		}

		if ( empty( $all_settings['vc_grid_id']['shortcodes'] ) ) {
			return $settings;
		}

		if ( empty( $settings['vc_grid_id']['shortcodes'] ) ) {
			$settings['vc_grid_id']['shortcodes'] = $all_settings['vc_grid_id']['shortcodes'];
		} else {
			$settings['vc_grid_id']['shortcodes'] = array_merge( $all_settings['vc_grid_id']['shortcodes'], $settings['vc_grid_id']['shortcodes'] );
		}

		return $settings;
	}
}

if ( ! function_exists( 'mltlngg_search_join' ) ) {
	function mltlngg_search_join( $join ) {
		global $wpdb, $mltlngg_current_language, $mltlngg_options, $mltlngg_enabled_languages_locale, $mltlngg_get_default_language;
		if ( ! is_admin() && is_main_query() && is_search() ) {
			if ( 'all' == $mltlngg_options['search'] ) {
				$wpdb->query( "SET SESSION group_concat_max_len = 100000000;" );
				$join .= "LEFT JOIN (SELECT post_ID, GROUP_CONCAT( DISTINCT post_content ORDER BY post_content ASC SEPARATOR ', ') AS mltlngg_post_content, GROUP_CONCAT( DISTINCT post_title ORDER BY post_title ASC SEPARATOR ', ') AS mltlngg_post_title, GROUP_CONCAT( DISTINCT post_excerpt ORDER BY post_excerpt ASC SEPARATOR ', ') AS mltlngg_post_excerpt FROM `" . $wpdb->prefix . "mltlngg_translate` WHERE `language` IN ('" . implode( "','", $mltlngg_enabled_languages_locale ) . "') AND ( `post_content` != '' OR `post_title` != '' ) GROUP BY `post_ID` ) `" . $wpdb->prefix . "mltlngg_translate` ON " . $wpdb->posts . ".ID = `" . $wpdb->prefix . "mltlngg_translate`.post_ID";
			} else if ( $mltlngg_get_default_language != $mltlngg_current_language ) {
				$join .= "LEFT JOIN (SELECT post_ID, post_content AS mltlngg_post_content, post_title AS mltlngg_post_title, post_excerpt AS mltlngg_post_excerpt FROM `" . $wpdb->prefix . "mltlngg_translate` WHERE `language` = '$mltlngg_current_language' ) `" . $wpdb->prefix . "mltlngg_translate` ON " . $wpdb->posts . ".ID = `" . $wpdb->prefix . "mltlngg_translate`.post_ID";
			}
		}
		return $join;
	}
}

if ( ! function_exists( 'mltlngg_search_where' ) ) {
	function mltlngg_search_where( $where ) {
		global $wpdb, $mltlngg_options, $mltlngg_get_default_language, $mltlngg_current_language;
		if ( ! is_admin() && is_main_query() && is_search() ) {
			if ( 'all' == $mltlngg_options['search'] ) {
				$where = preg_replace(
					"/\($wpdb->posts.post_title\s+LIKE\s*(\'[^\']+\')\s*\)/",
					"(post_title LIKE $1) OR (mltlngg_post_title LIKE $1) OR (mltlngg_post_content LIKE $1) OR (mltlngg_post_excerpt LIKE $1)",
					$where
				);
			} else if ( $mltlngg_get_default_language != $mltlngg_current_language ) {
				$where = preg_replace(
					"/\($wpdb->posts.post_title\s+LIKE\s*(\'[^\']+\')\s*\)/",
					"(mltlngg_post_title LIKE $1)",
					$where
				);
				$where = preg_replace(
					"/\($wpdb->posts.post_content\s+LIKE\s*(\'[^\']+\')\s*\)/",
					"(mltlngg_post_content LIKE $1)",
					$where
				);
				$where = preg_replace(
					"/\($wpdb->posts.post_excerpt\s+LIKE\s*(\'[^\']+\')\s*\)/",
					"(mltlngg_post_excerpt LIKE $1)",
					$where
				);
			}
		}
		return $where;
	}
}

/**
* Add language columns in the taxonomy wp_list_table
*
* @param array $columns list of terms table columns
* @return array modified list of columns
*/
if ( ! function_exists( 'mltlngg_add_term_column' ) ) {
	function mltlngg_add_term_column( $columns ) {
		return mltlngg_add_column( $columns, 'posts' );
	}
}

/**
* Add language columns in the posts wp_list_table
*
* @param array $columns list of posts table columns
* @return array modified list of columns
*/
if ( ! function_exists( 'mltlngg_add_post_column' ) ) {
	function mltlngg_add_post_column( $columns ) {
		return mltlngg_add_column( $columns );
	}
}

/**
* Fill language column in the taxonomy wp_list_table
*
* @param string $out
* @param string $column column name
* @param int term_id
*/
if ( ! function_exists( 'mltlngg_term_column' ) ) {
	function mltlngg_term_column( $out, $column, $term_id ) {
		if ( false === strpos( $column, 'mltlngg_language_' ) ) {
			return $out;
		}

		$post_type = isset( $GLOBALS['post_type'] ) ? $GLOBALS['post_type'] : $_REQUEST['post_type'];
		$taxonomy = isset( $GLOBALS['taxonomy'] ) ? $GLOBALS['taxonomy'] : $_REQUEST['taxonomy'];

		if ( ! post_type_exists( $post_type ) || ! taxonomy_exists( $taxonomy ) ) {
			return $out;
		}

		global $wpdb;

		$language = str_replace( 'mltlngg_language_', '', $column );

		/* Get term translation from database */
		$term_translation = $wpdb->get_var( $wpdb->prepare(
			"SELECT `name`
			 FROM `" . $wpdb->prefix . "mltlngg_terms_translate`
			 WHERE `term_ID` = %d AND `language` = %s
			", $term_id, $language
		) );

		if ( ! empty( $term_translation ) ) {
			$out .= '<span class="dashicons dashicons-yes"></span>';
		}

		return $out;
	}
}

/**
* Fill language column in the posts wp_list_table
*
* @param string $column column name
* @param int $post_id
*/
if ( ! function_exists( 'mltlngg_post_column' ) ) {
	function mltlngg_post_column( $column, $post_id ) {
		if ( defined( 'DOING_AJAX' ) || false === strpos( $column, 'mltlngg_language_' ) ) {
			return;
		}

		global $wpdb;

		$language = str_replace( 'mltlngg_language_', '', $column );

		/* Get post translation from database */
		$translation = $wpdb->get_var( $wpdb->prepare(
			"SELECT `post_content`
			 FROM `" . $wpdb->prefix . "mltlngg_translate`
			 WHERE `post_ID` = %d AND `language` = %s
			", $post_id, $language
		) );

		if ( ! empty( $translation ) ) {
			echo '<span class="dashicons dashicons-yes"></span>';
		}
	}
}

/**
 * adds languages and translations columns
 *
 * @param array $columns list of table columns
 * @param string $before the column before which we want to add our languages
 * @return array modified list of columns
 */
if ( ! function_exists( 'mltlngg_add_column' ) ) {
	function mltlngg_add_column( $columns, $before = false ) {
		global $mltlngg_enabled_languages, $mltlngg_get_default_language;

		if ( $before && $n = array_search( $before, array_keys( $columns ) ) ) {
			$end = array_slice( $columns, $n );
			$columns = array_slice( $columns, 0, $n );
		}

		foreach ( $mltlngg_enabled_languages as $language ) {
			/* don't add the column for default language */
			if ( $language['locale'] != $mltlngg_get_default_language ) {
				$flag = ( empty( $language['flag'] ) ) ? plugins_url( 'images/flags/' , __FILE__ ) . $language['locale'] . '.png' : $language['flag'];

				$columns[ 'mltlngg_language_' . $language['locale'] ] = '<img class="mltlngg-lang" src="' . $flag . '" alt="' . $language['name'] . '"><span class="screen-reader-text">' . $language['name'] . '</span>';
			}
		}
		return isset( $end ) ? array_merge( $columns, $end ) : $columns;
	}
}

/*
* 	Add language switcher metabox
*/
if ( ! function_exists( 'mltlngg_language_switcher_box' ) ) {
	function mltlngg_language_switcher_box() {
		global $_nav_menu_placeholder, $nav_menu_selected_id;
		$_nav_menu_placeholder = 0 > $_nav_menu_placeholder ? $_nav_menu_placeholder - 1 : -1; ?>

		<div id="posttype-mltlngg-switcher" class="posttypediv">
			<div id="tabs-panel-mltlngg-switcher" class="tabs-panel tabs-panel-active">
				<ul id="mltlngg-switcher-checklist" class="categorychecklist form-no-clear">
					<li>
						<label class="menu-item-title">
							<input type="checkbox" class="menu-item-checkbox" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-object-id]" value="-1"> <?php _e( 'Multilanguage switcher', 'multilanguage' ); ?>
						</label>
						<input type="hidden" class="menu-item-type" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-type]" value="custom">
						<input type="hidden" class="menu-item-title" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-title]" value="<?php _e( 'Multilanguage switcher', 'multilanguage' ); ?>">
						<input type="hidden" class="menu-item-url" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-url]" value="#mltlngg-switcher">
						<input type="hidden" class="menu-item-classes" name="menu-item[-1][menu-item-classes]" value="mltlngg-menu-switcher">
					</li>
				</ul>
			</div>
			<p class="button-controls">
				<span class="add-to-menu">
					<input type="submit" class="button-secondary submit-add-to-menu right" value="<?php esc_attr_e( 'Add to Menu' ); ?>" name="add-post-type-menu-item" id="submit-posttype-mltlngg-switcher">
					<span class="spinner"></span>
				</span>
			</p>
		</div>
	<?php }
}

/**
 * Save nav menu options
 *
 * @param int $menu_id not used
 * @param int $menu_item_db_id
 */
if ( ! function_exists( 'mltlngg_wp_update_nav_menu_item' ) ) {
	function mltlngg_wp_update_nav_menu_item( $menu_id = 0, $menu_item_db_id = 0 ) {
		if ( empty( $_POST['menu-item-url'][ $menu_item_db_id ] ) || '#mltlngg-switcher' != $_POST['menu-item-url'][ $menu_item_db_id ] ) {
			return;
		}

		if ( current_user_can( 'edit_theme_options' ) ) {
			check_admin_referer( 'update-nav_menu', 'update-nav-menu-nonce' );

			/* js form has not been displayed */
			if ( empty( $_POST['menu-item-mltlngg-nonce'][ $menu_item_db_id ] ) ) {
				if ( ! get_post_meta( $menu_item_db_id, '_mltlngg_menu_item', true ) ) {
					update_post_meta( $menu_item_db_id, '_mltlngg_menu_item', 'drop-down-list' );
				}
			} else {
				$value = (
					! empty( $_POST[ 'menu-item-mltlngg-switcher' ][ $menu_item_db_id ] ) &&
					in_array(
						$_POST[ 'menu-item-mltlngg-switcher' ][ $menu_item_db_id ],
						array(
							'drop-down-list',
							'drop-down-titles',
							'drop-down-icons',
							'aligned-icons',
							'aligned-list',
							'aligned-titles'
						)
					)
				) ? $_POST[ 'menu-item-mltlngg-switcher' ][ $menu_item_db_id ] : 'drop-down-list';
				update_post_meta( $menu_item_db_id, '_mltlngg_menu_item', $value );
			}
		}
	}
}

/**
 * Compatibility with PDF & Print by BestWebSoft plugin. Display content for current language.
 *
 * @since 1.1.9
 */
if ( ! function_exists( 'mltlngg_pdf_print_content_filter' ) ) {
	function mltlngg_pdf_print_content_filter( $content, $object ) {
		global $wpdb, $mltlngg_current_language, $mltlngg_get_default_language, $wp_current_filter;

		if ( $mltlngg_get_default_language != $mltlngg_current_language ) {
			/* If current post type enabled to translation */
			if ( $object->post_type == 'post' || $object->post_type == 'page' ) {

				$new_content = $wpdb->get_var( $wpdb->prepare( "SELECT `post_content` FROM `" . $wpdb->prefix . "mltlngg_translate` WHERE `post_ID` = %d AND `language` = %s ", $object->ID, $mltlngg_current_language ) );
				if ( ! empty( $new_content ) ) {
					$noteaser		= ( ( false !== strpos( $new_content, '<!--noteaser-->' ) ) ? true : false );
					$extends		= get_extended( $new_content );
					$extended		= $extends['extended'];
					$new_content	= $extends['main'];

					if ( ! empty( $mltlngg_options['video_providers'] ) ) {
						foreach ( $mltlngg_options['video_providers'] as $reg_exp ) {
							preg_match( $reg_exp, $new_content, $matches );
							if ( ! empty( $matches[0] ) ) {
								$string			= strstr( $matches[0], '<', true );
								$match			= $string ? '[(' . preg_quote( $string ) . ')]' : '[(' . preg_quote( $matches[0] ) . ')]';
								$new_content	= preg_replace_callback( $match, "mltlngg_videos_filter", $new_content );
							}
							preg_match( $reg_exp, $new_content, $matches );
							if ( ! empty( $matches[0] ) ) {
								$string		= strstr( $matches[0], '<', true );
								$match		= $string ? '[(' . preg_quote( $string ) . ')]' : '[(' . preg_quote( $matches[0] ) . ')]';
								$extended	= preg_replace_callback( $match, "mltlngg_videos_filter", $extended );
							}
						}
					}

					if ( ! is_single() && ! is_page() && ! is_search() ) {
						$more_link_text = __( '(more&hellip;)' );
						$more_link = apply_filters( 'the_content_more_link', ' <a href="' . get_permalink() . "#more-{$post->ID}\" class=\"more-link\">$more_link_text</a>", $more_link_text );

						if ( '' != $extended ) {
							$new_content .= $more_link;
						}
					} elseif ( is_page() ) {
						$new_content .= $extended;
					} else {
						if ( $noteaser ) {
							$new_content = '';
						}

						$new_content .= ( 0 != strlen( $new_content ) ) ? '<span id="more-' . $object->ID . '"></span>' . $extended : $extended;
					}

					/* if it is the_excerpt - remove shortcodes */
					if ( ! empty( $wp_current_filter ) && in_array( 'get_the_excerpt', $wp_current_filter ) ) {
						$new_content = strip_shortcodes( $new_content );
					}

					if ( 0 != strlen( $new_content ) ) {
						return force_balance_tags( $new_content );
					}
					return $new_content;
				}
			}
		}
		return $content;
	}
}

/**
 * Compatibility with PDF & Print by BestWebSoft plugin. Display title for current language.
 *
 * @since 1.1.9
 */
if ( ! function_exists( 'mltlngg_pdf_print_title_filter' ) ) {
	function mltlngg_pdf_print_title_filter( $title, $object ) {
		global $mltlngg_options, $wpdb, $mltlngg_current_language, $mltlngg_get_default_language;
		if ( $mltlngg_get_default_language != $mltlngg_current_language ) {
			/* If current post type enabled to translation */
			if ( $object->post_type == 'post' || $object->post_type == 'page' ) {
				$new_title = $wpdb->get_var( $wpdb->prepare( "SELECT `post_title` FROM `" . $wpdb->prefix . "mltlngg_translate` WHERE `post_ID` = %d AND `language` = %s", $object->ID, $mltlngg_current_language ) );

				if ( ! empty( $new_title ) ) {
					return $new_title;
				}
			}
		}
		return $title;
	}
}

/* Check if running in admin area. */
if ( ! function_exists( 'mltlngg_is_admin' ) ) {
	function mltlngg_is_admin() {
		return ( is_admin() && ! ( defined( 'DOING_AJAX' ) && ! isset( $_REQUEST['pagenow'] ) ) );
	}
}

/* add help tab */
if ( ! function_exists( 'mltlngg_add_tabs' ) ) {
	function mltlngg_add_tabs() {
		$screen = get_current_screen();
		$args = array(
			'id'			=> 'mltlngg',
			'section'		=> '200538779'
		);
		bws_help_tab( $screen, $args );
	}
}

/* add shortcode content */
if ( ! function_exists( 'mltlngg_shortcode_button_content' ) ) {
	function mltlngg_shortcode_button_content( $content ) {
		global $mltlngg_options; ?>
		<div id="mltlngg" style="display:none;">
			<h3><?php _e( 'Select Switcher Type', 'multilanguage' ); ?>:</h3>
			<fieldset class="mltlngg-shortcode">
				<label>
					<input name="mltlngg_language_switcher" type="radio" value="drop-down-list" <?php checked( $mltlngg_options['language_switcher'], 'drop-down-list' ); ?> />
					<?php _e( 'Drop-down list', 'multilanguage' ); ?> (<?php _e( 'flag', 'multilanguage' ); ?> + <?php _e( 'title', 'multilanguage' ); ?>)
				</label><br/>
				<label>
					<input name="mltlngg_language_switcher" type="radio" value="drop-down-titles" <?php checked( $mltlngg_options['language_switcher'], 'drop-down-titles' ); ?> />
					<?php _e( 'Drop-down list', 'multilanguage' ); ?> (<?php _e( 'title', 'multilanguage' ); ?>)
				</label><br/>
				<label>
					<input name="mltlngg_language_switcher" type="radio" value="drop-down-icons" <?php checked( $mltlngg_options['language_switcher'], 'drop-down-icons' ); ?> />
					<?php _e( 'Drop-down list', 'multilanguage' ); ?> (<?php _e( 'flag', 'multilanguage' ); ?>)
				</label><br/>
				<label>
					<input name="mltlngg_language_switcher" type="radio" value="flags-icons" <?php checked( $mltlngg_options['language_switcher'], 'flags-icons' ); ?> />
					<?php _e( 'Flag', 'multilanguage' ); ?>
				</label><br/>
				<label>
					<input name="mltlngg_language_switcher" type="radio" value="gt" <?php checked( $mltlngg_options['language_switcher'], 'gt' ); ?> />
					<?php _e( 'Google Auto Translate', 'multilanguage' ); ?> (<?php _e( 'drop-down only', 'multilanguage' ); ?>)
				</label><br/>
				<label>
					<input name="mltlngg_language_switcher" type="radio" value="gt-horizontal" <?php checked( $mltlngg_options['language_switcher'], 'gt-horizontal' ); ?> />
					<?php _e( 'Google Auto Translate', 'multilanguage' ); ?> (<?php _e( 'horizontal', 'multilanguage' ); ?>)
				</label><br/>
				<label>
					<input name="mltlngg_language_switcher" type="radio" value="gt-vertical" <?php checked( $mltlngg_options['language_switcher'], 'gt-vertical' ); ?> />
					<?php _e( 'Google Auto Translate', 'multilanguage' ); ?> (<?php _e( 'vertical', 'multilanguage' ); ?>)
				</label>
			</fieldset>
			<input class="bws_default_shortcode" type="hidden" name="default" value='[multilanguage_switcher]' />
			<div class="clear"></div>
			<script type="text/javascript">
				function mltlngg_shortcode_init() {
					(function($) {
						$( '.mce-reset [name="mltlngg_language_switcher"]' ).on( 'click', function() {
							var layout = $( this ).val(),
							shortcode = '[multilanguage_switcher layout="' + layout + '"]';
							$( '.mce-reset #bws_shortcode_display' ).text( shortcode );
						});
					})(jQuery);
				}
			</script>
		</div>
	<?php }
}

/* Add action links on plugin page in to Plugin Name block */
if ( ! function_exists( 'mltlngg_plugin_action_links' ) ) {
	function mltlngg_plugin_action_links( $links, $file ) {
		if ( ! is_network_admin() ) {
			/* Static so we don't call plugin_basename on every plugin row. */
			static $this_plugin;
			if ( ! $this_plugin ) $this_plugin = plugin_basename( __FILE__ );
			if ( $file == $this_plugin ) {
				$settings_link = '<a href="admin.php?page=mltlngg_settings">' . __( 'Settings', 'multilanguage' ) . '</a>';
				array_unshift( $links, $settings_link );
			}
		}
		return $links;
	}
}

/* Add action links on plugin page in to Plugin Description block */
if ( ! function_exists( 'mltlngg_register_plugin_links' ) ) {
	function mltlngg_register_plugin_links( $links, $file ) {
		$base = plugin_basename( __FILE__ );
		if ( $file == $base ) {
			if ( ! is_network_admin() ) {
				$links[] = '<a href="admin.php?page=mltlngg_settings">' . __( 'Settings', 'multilanguage' ) . '</a>';
			}
			$links[] = '<a href="http://wordpress.org/plugins/multilanguage/faq/" target="_blank">' . __( 'FAQ', 'multilanguage' ) . '</a>';
			$links[] = '<a href="https://support.bestwebsoft.com">' . __( 'Support', 'multilanguage' ) . '</a>';
		}
		return $links;
	}
}

/* Delete plugin options */
if ( ! function_exists( 'mltlngg_deactivation' ) ) {
	function mltlngg_deactivation() {
		global $mltlngg_options;

		$mltlngg_options['deactivation'] = current_time( 'mysql' );
		update_option( 'mltlngg_options', $mltlngg_options );
	}
}

/* Delete plugin blog */
if ( ! function_exists( 'mltlngg_delete_blog' ) ) {
	function mltlngg_delete_blog( $blog_id ) {
		global $wpdb;
		if ( is_plugin_active_for_network( 'multilanguage/multilanguage.php' ) ) {
			if ( ! function_exists( 'get_plugins' ) ) {
				require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			}
			$all_plugins = get_plugins();
			$pro_exist = array_key_exists( 'multilanguage-pro/multilanguage-pro.php', $all_plugins );

			$old_blog = $wpdb->blogid;
			switch_to_blog( $blog_id );
			_mltlngg_delete_options( $pro_exist );
			switch_to_blog( $old_blog );
		}
	}
}

/* Delete plugin for network */
if ( ! function_exists( 'mltlngg_delete_options' ) ) {
	function mltlngg_delete_options() {
		global $wpdb;

		if ( ! function_exists( 'get_plugins' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}
		$all_plugins = get_plugins();
		$pro_exist = array_key_exists( 'multilanguage-pro/multilanguage-pro.php', $all_plugins );

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {
			/* check if it is a network activation - if so, run the activation function for each blog id */
			$old_blog = $wpdb->blogid;
			/* Get all blog ids */
			$blogids = $wpdb->get_col( "SELECT `blog_id` FROM $wpdb->blogs" );
			foreach ( $blogids as $blog_id ) {
				switch_to_blog( $blog_id );
				_mltlngg_delete_options( $pro_exist );
			}
			switch_to_blog( $old_blog );
		} else {
			_mltlngg_delete_options( $pro_exist );
		}

		require_once( dirname( __FILE__ ) . '/bws_menu/bws_include.php' );
		bws_include_init( plugin_basename( __FILE__ ) );
		bws_delete_plugin( plugin_basename( __FILE__ ) );
	}
}

/* Delete plugin options */
if ( ! function_exists( '_mltlngg_delete_options' ) ) {
	function _mltlngg_delete_options( $pro_exist ) {
		global $wpdb;

		if ( ! $pro_exist ) {
			$wpdb->query( "DROP TABLE `" . $wpdb->prefix . "mltlngg_translate`, `" . $wpdb->prefix . "mltlngg_terms_translate`;" );

			/* Delete widget from database */
			if ( is_dynamic_sidebar() ) { /* If there is an active sidebar */
				$mltlngg_available_sidebars = get_option( 'sidebars_widgets' ); /* Get all active sidebars and widgets */
				foreach ( $mltlngg_available_sidebars as $key1 => $one_sidebar ) {
					if ( $key1 != 'array_version' && isset( $one_sidebar ) ) {
						$widget_exist = preg_grep( '/multi_language_widget-?\d*/', $one_sidebar );
						if ( isset( $widget_exist ) ) {
							foreach ( $widget_exist as $key2 => $one_widget ) {
								unset( $mltlngg_available_sidebars[ $key1 ][ $key2 ] );
							}
						}
					}
				}
				update_option( 'sidebars_widgets', $mltlngg_available_sidebars );
			}
			delete_option( 'widget_multi_language_widget' );
			delete_option( 'mltlngg_options' );
		}

		flush_rewrite_rules();
	}
}

if ( ! function_exists ( 'mltlngg_plugin_banner' ) ) {
	function mltlngg_plugin_banner() {
		global $hook_suffix, $mltlngg_plugin_info, $mltlngg_options;
		if ( 'plugins.php' == $hook_suffix ) {
			if ( isset( $mltlngg_options['first_install'] ) && strtotime( '-1 week' ) > $mltlngg_options['first_install'] ) {
				bws_plugin_banner( $mltlngg_plugin_info, 'mltlngg', 'multilanguage', '0419dafcc237fe35489c8db63c899a38', '143', '//ps.w.org/multilanguage/assets/icon-128x128.png' );
			}

			bws_plugin_banner_to_settings( $mltlngg_plugin_info, 'mltlngg_options', 'multilanguage', 'admin.php?page=mltlngg_settings' );
		}

		if ( isset( $_GET['page'] ) && 'mltlngg_settings' == $_GET['page'] ) {
			bws_plugin_suggest_feature_banner( $mltlngg_plugin_info, 'mltlngg_options', 'multilanguage' );
		}
	}
}

register_activation_hook( __FILE__, 'mltlngg_plugin_activate' );
add_action( 'wpmu_new_blog', 'mltlngg_new_blog', 10, 6 );
add_action( 'delete_blog', 'mltlngg_delete_blog', 10 );

add_action( 'plugins_loaded', 'mltlngg_plugin_load' );

/* Rewrite rules */
add_action( 'generate_rewrite_rules', 'mltlngg_rewrite_rules' );
add_filter( 'query_vars', 'mltlngg_add_query_vars' );

add_action( 'admin_menu', 'mltlngg_admin_menu' );
add_action( 'init', 'mltlngg_init', 9 );
add_action( 'admin_init', 'mltlngg_admin_init' );
add_filter( 'language_attributes', 'mltlngg_language_attributes', PHP_INT_MAX );
add_action( 'wp_head', 'mltlngg_wp_head', PHP_INT_MAX );
add_action( 'admin_enqueue_scripts', 'mltlngg_script_style' );
add_action( 'wp_enqueue_scripts', 'mltlngg_script_style' );
add_action( 'widgets_init', 'register_mltlngg_widget' );

add_shortcode( 'multilanguage_switcher', 'mltlngg_get_switcher_block' );

/* Links filters */
add_filter( 'home_url', 'mltlngg_get_url_translated' );

/* Actions for page/post editor */
//add_action( 'wp_print_scripts', 'mltlngg_disable_autosave_editor_script', 100 );
add_action( 'edit_form_top', 'mltlngg_showup_language_tabs_in_editor' );	/* Add languages tabs to post editor */
add_filter( 'title_edit_pre', 'mltlngg_the_title_filter', 10, 2 );
add_filter( 'excerpt_edit_pre', 'mltlngg_localize_excerpt', 10, 1 );

add_filter( 'vc_hooks_vc_post_settings', 'mltlngg_composer_settings_filter', 11, 3 );

add_action( 'edit_form_after_title', 'mltlngg_set_main_content_filter', 10, 1 );
add_action( 'edit_form_after_editor', 'mltlngg_unset_main_content_filter', 10, 1 );

add_action( 'save_post', 'mltlngg_save_post' );	/* Saving changes in posts translations */
add_action( 'deleted_post', 'mltlngg_delete_post' );	/* Delete posts translations from database */

/* add language name as class to body tag */
add_filter( 'body_class', 'mltlngg_add_body_classes' );

/* Filters for display frontend content language */
add_filter( 'the_title', 'mltlngg_the_title_filter', 10, 2 );
add_filter( 'single_post_title', 'mltlngg_the_title_filter', 10, 2 );
add_filter( 'wp_get_nav_menu_items', 'mltlngg_nav_menu_items_filter', 10 );
add_filter( 'the_content', 'mltlngg_the_content_filter', 1 );
add_filter( 'get_terms', 'mltlngg_terms_filter' );	/* Add filter categories list & tags cloud */
add_filter( 'get_the_terms', 'mltlngg_terms_filter' );	/* Add filter categories & tags of posts */
add_filter( 'get_term', 'mltlngg_term_filter' );	/* Add filter category & tags names in title of archive pages */
add_filter( 'get_pagenum_link', 'mltlngg_paginate_url_translate' );
add_filter( 'author_link', 'mltlngg_author_url_fix', 10, 3 );
add_filter( 'get_the_excerpt', 'mltlngg_localize_excerpt', 1, 1 );
/* Add screen options on Multilanguage language list Page */
add_filter( 'set-screen-option', 'mltlngg_table_set_option', 10, 3 );

/* extend search */
add_filter( 'posts_join', 'mltlngg_search_join' );
add_filter( 'posts_where', 'mltlngg_search_where' );

/* Compatibility with PDF & Print by BestWebSoft plugin. */
add_filter( 'bwsplgns_get_pdf_print_content', 'mltlngg_pdf_print_content_filter', 10, 2 );
add_filter( 'bwsplgns_get_pdf_print_title', 'mltlngg_pdf_print_title_filter', 10, 2 );

/* custom filter for bws button in tinyMCE */
add_filter( 'bws_shortcode_button_content', 'mltlngg_shortcode_button_content' );

/* Additional links on the plugin page */
add_filter( 'plugin_action_links', 'mltlngg_plugin_action_links', 10, 2 );
add_filter( 'plugin_row_meta', 'mltlngg_register_plugin_links', 10, 2 );

/* Add AJAX function */
add_action( 'wp_ajax_mltlngg_ajax_callback', 'mltlngg_ajax_callback' );

add_action( 'admin_notices', 'mltlngg_plugin_banner' );
register_deactivation_hook( __FILE__, 'mltlngg_deactivation' );