<?php
/*
Plugin Name: Multilanguage
Plugin URI:  http://bestwebsoft.com/products/
Description: This plugin allows you to display the content in different languages.
Author: BestWebSoft
Version: 1.0.0
Author URI: http://bestwebsoft.com/
License: GPLv3 or later
*/

/*  © Copyright 2014  BestWebSoft  ( http://support.bestwebsoft.com )

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

require_once( __DIR__ . '/include/table.php' );
require_once( __DIR__ . '/include/languages.php' );

/* Function add menu pages */
if ( ! function_exists( 'mltlngg_admin_menu' ) ) {
	function mltlngg_admin_menu() {
		global $bstwbsftwppdtplgns_options, $bstwbsftwppdtplgns_added_menu;
		$bws_menu_info = get_plugin_data( plugin_dir_path( __FILE__ ) . "bws_menu/bws_menu.php" );
		$bws_menu_version = $bws_menu_info["Version"];
		$base = plugin_basename( __FILE__ );

		if ( ! isset( $bstwbsftwppdtplgns_options ) ) {
			if ( ! get_option( 'bstwbsftwppdtplgns_options' ) )
				add_option( 'bstwbsftwppdtplgns_options', array(), '', 'yes' );
			$bstwbsftwppdtplgns_options = get_option( 'bstwbsftwppdtplgns_options' );
		}

		if ( isset( $bstwbsftwppdtplgns_options['bws_menu_version'] ) ) {
			$bstwbsftwppdtplgns_options['bws_menu']['version'][ $base ] = $bws_menu_version;
			unset( $bstwbsftwppdtplgns_options['bws_menu_version'] );
			update_option( 'bstwbsftwppdtplgns_options', $bstwbsftwppdtplgns_options, '', 'yes' );
			require_once( dirname( __FILE__ ) . '/bws_menu/bws_menu.php' );
		} else if ( ! isset( $bstwbsftwppdtplgns_options['bws_menu']['version'][ $base ] ) || $bstwbsftwppdtplgns_options['bws_menu']['version'][ $base ] < $bws_menu_version ) {
			$bstwbsftwppdtplgns_options['bws_menu']['version'][ $base ] = $bws_menu_version;
			update_option( 'bstwbsftwppdtplgns_options', $bstwbsftwppdtplgns_options, '', 'yes' );
			require_once( dirname( __FILE__ ) . '/bws_menu/bws_menu.php' );
		} else if ( ! isset( $bstwbsftwppdtplgns_added_menu ) ) {
			$plugin_with_newer_menu = $base;
			foreach ( $bstwbsftwppdtplgns_options['bws_menu']['version'] as $key => $value ) {
				if ( $bws_menu_version < $value && is_plugin_active( $base ) ) {
					$plugin_with_newer_menu = $key;
				}
			}
			$plugin_with_newer_menu = explode( '/', $plugin_with_newer_menu );
			$wp_content_dir = defined( 'WP_CONTENT_DIR' ) ? basename( WP_CONTENT_DIR ) : 'wp-content';
			if ( file_exists( ABSPATH . $wp_content_dir . '/plugins/' . $plugin_with_newer_menu[0] . '/bws_menu/bws_menu.php' ) )
				require_once( ABSPATH . $wp_content_dir . '/plugins/' . $plugin_with_newer_menu[0] . '/bws_menu/bws_menu.php' );
			else
				require_once( dirname( __FILE__ ) . '/bws_menu/bws_menu.php' );
			$bstwbsftwppdtplgns_added_menu = true;
		}
		add_menu_page( 'BWS Plugins', 'BWS Plugins', 'manage_options', 'bws_plugins', 'bws_add_menu_render', plugins_url( 'images/px.png', __FILE__ ), 1001 );
		add_submenu_page( 'bws_plugins', 'Multilanguage', 'Multilanguage', 'manage_options', "mltlngg_settings", 'mltlngg_settings_page' );
		mltlngg_add_menu_items();
	}
}

/* Plugin initialisation in backend and frontend */
if ( ! function_exists( 'mltlngg_init' ) ) {
	function mltlngg_init() {
		global $wpdb, $mltlngg_options, $mltlngg_table_translate, $mltlngg_terms_table_translate;

		/* check WordPress version */
		mltlngg_version_check();

		$mltlngg_table_translate = $wpdb->prefix . 'mltlngg_translate';
		$mltlngg_terms_table_translate = $wpdb->prefix . 'mltlngg_terms_translate';

		mltlngg_register_settings();

		/* If there have been changes in the list of languages do flush_rewrite_rules */
		if ( isset( $_GET['action'] ) || isset( $_POST['action'] ) || isset( $_POST['action2'] ) || ( isset( $_POST['mltlngg_add_new_language_form_was_send'] ) && true == $mltlngg_options['enabled_new_language'] ) ) {
			flush_rewrite_rules();
		}
	}
}

/* Plugin initialisation in backend  */
if ( ! function_exists( 'mltlngg_admin_init' ) ) {
	function mltlngg_admin_init() {
		global $bws_plugin_info, $mltlngg_plugin_info;
		/* Add variable for bws_menu */
		if ( ! $mltlngg_plugin_info )
			$mltlngg_plugin_info = get_plugin_data( __FILE__ );

		if ( ! isset( $bws_plugin_info ) || empty( $bws_plugin_info ) ) {
			$bws_plugin_info = array( 'id' => '143', 'version' => $mltlngg_plugin_info["Version"] );
		}		

		/* Actions for categories & tags translation */
		mltlngg_taxonomies();
	}
}

/* Function check if plugin is compatible with current WP version */
if ( ! function_exists( 'mltlngg_version_check' ) ) {
	function mltlngg_version_check() {
		global $wp_version, $mltlngg_plugin_info;
		$require_wp = "3.7"; /* Wordpress at least requires version */
		$plugin = plugin_basename( __FILE__ );
		if ( version_compare( $wp_version, $require_wp, "<" ) ) {
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			if ( is_plugin_active( $plugin ) ) {
				deactivate_plugins( $plugin );
				if ( ! $mltlngg_plugin_info )
					$mltlngg_plugin_info = get_plugin_data( __FILE__ );
				$admin_url = ( function_exists( 'get_admin_url' ) ) ? get_admin_url( null, 'plugins.php' ) : esc_url( '/wp-admin/plugins.php' );
				wp_die( "<strong>" . $mltlngg_plugin_info['Name'] . " </strong> " . __( 'requires', 'multilanguage' ) . " <strong>WordPress " . $require_wp . "</strong> " . __( 'or higher, that is why it has been deactivated! Please upgrade WordPress and try again.', 'multilanguage') . "<br /><br />" . __( 'Back to the WordPress', 'multilanguage') . " <a href='" . $admin_url . "'>" . __( 'Plugins page', 'multilanguage') . "</a>." );
			}
		}
	}
}

/* Actions for categories & tags translation */
if ( ! function_exists( 'mltlngg_taxonomies' ) ) {
	function mltlngg_taxonomies() {
		$mltlngg_taxonomies = get_object_taxonomies( 'post' );
		if ( is_array( $mltlngg_taxonomies ) ) {
			foreach ( $mltlngg_taxonomies as $mltlngg_taxonomy ) {
				add_action( $mltlngg_taxonomy . '_add_form_fields', 'mltlngg_new_terms_translate' );
				add_action( 'created_' . $mltlngg_taxonomy, 'mltlngg_new_terms_created' );
				add_action( $mltlngg_taxonomy . '_edit_form_fields', 'mltlngg_terms_translate' );
				add_action( 'edited_' . $mltlngg_taxonomy, 'mltlngg_terms_update' );
				add_action( 'delete_' . $mltlngg_taxonomy, 'mltlngg_delete_term' );
			}
		}
	}
}

/* Default Plugin settings */
if ( ! function_exists( 'mltlngg_register_settings' ) ) {
	function mltlngg_register_settings() {
		global $mltlngg_plugin_info, $mltlngg_options, $mltlngg_languages, $mltlngg_language_default, $mltlngg_list_of_languages;
		$mltlngg_db_version = '0.1';
		
		if ( empty( $mltlngg_plugin_info ) ) {
			if ( ! function_exists( 'get_plugin_data' ) ) {
				require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			}
			$mltlngg_plugin_info = get_plugin_data( __FILE__ );
		}

		/* Set the default language is the same as the language of the Wordpress localization */
		foreach ( $mltlngg_languages as $one_lang ) { /* Search locale in the array of standard languages, source - languages.php */
			$mltlngg_is_lang_exist = array_search( get_locale(), $one_lang );
			if ( false != $mltlngg_is_lang_exist ) { /* If the locale is found set the default language */
				$mltlngg_language_default = $one_lang;
				break;
			}
		}

		/* If the language is not found or is not a standard, set English ('en_US') as the default language */
		if ( ! isset( $mltlngg_language_default ) || NULL == $mltlngg_language_default )
			$mltlngg_language_default = array( 'en', 'en_US', 'English', '' );
		
		$mltlngg_list_of_languages = array(
			array(
				'locale'	=> $mltlngg_language_default[1],
				'name'		=> $mltlngg_language_default[2],
				'enable'	=> true,
				'priority'	=> 1
			),
		);

		/* Set the default options */
		$mltlngg_default_options = array (
			'plugin_option_version'		=> $mltlngg_plugin_info["Version"],
			'plugin_db_version' 		=> $mltlngg_db_version,
			'default_language'			=> $mltlngg_language_default[1],
			'list_of_languages'			=> $mltlngg_list_of_languages,
			'enabled_new_language'		=> false,
			'autosave_editor_content'	=> true,
			'wp_localization'			=> true
		);

		/* Add options to database */
		if ( ! get_option( 'mltlngg_options' ) )
			add_option( 'mltlngg_options', $mltlngg_default_options );

		$mltlngg_options = get_option( 'mltlngg_options' );

		/* Array merge incase this version has added new options */
		if ( ! isset( $mltlngg_options['plugin_option_version'] ) || $mltlngg_options['plugin_option_version'] != $mltlngg_plugin_info["Version"] ) {
			$mltlngg_options = array_merge( $mltlngg_default_options, $mltlngg_options );
			$mltlngg_options['plugin_option_version'] = $mltlngg_plugin_info["Version"];
			update_option( 'mltlngg_options', $mltlngg_options );
		}
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
				$blogids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
				foreach ( $blogids as $blog_id ) {
					switch_to_blog( $blog_id );
					_mltlngg_plugin_activate();
				}
				switch_to_blog( $old_blog );
				return;
			}
		}
		_mltlngg_plugin_activate();
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
		global $wpdb, $mltlngg_table_translate, $mltlngg_terms_table_translate;
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		$mltlngg_table_translate = $wpdb->prefix . 'mltlngg_translate';
		$mltlngg_terms_table_translate = $wpdb->prefix . 'mltlngg_terms_translate';

		$mltlngg_sql =
			"CREATE TABLE IF NOT EXISTS `" . $mltlngg_table_translate . "` (
			`ID` INT(6) UNSIGNED NOT NULL AUTO_INCREMENT,
			`post_ID` INT(6) NOT NULL,
			`post_content` longtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
			`post_title` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
			`language` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
			PRIMARY KEY  (`ID`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
		dbDelta( $mltlngg_sql );

		$mltlngg_sql =
			"CREATE TABLE IF NOT EXISTS `" . $mltlngg_terms_table_translate . "` (
			`ID` INT(6) UNSIGNED NOT NULL AUTO_INCREMENT,
			`term_ID` INT(6) NOT NULL,
			`name` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
			`language` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
			PRIMARY KEY  (`ID`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
		dbDelta( $mltlngg_sql );

		/* Auto-add Multilanguage widget to active sidebar */
		if ( is_dynamic_sidebar() ) { /* If there is an active sidebar */
			$mltlngg_available_sidebars = get_option( 'sidebars_widgets' ); /* Get all active sidebars */

			/* Delete from database old information about widget */
			foreach ( $mltlngg_available_sidebars as $key1 => $one_sidebar ) {
				if ( $key1 != 'array_version' ) {
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
					if ( $key != 'wp_inactive_widgets' && $key != 'array_version' && ! preg_match( '/multi_language_widget-?\d*/', implode( ',', $val ) ) && preg_match( '/search.*/', implode( ',', $val ) ) ) { /* If there is no Multilanguage Widget and exist Search Widget in this sidebar */
						array_unshift( $val, 'multi_language_widget-2' ); /* Adding Multilanguage Widget to the top of sidebar */
						$mltlngg_available_sidebars[ $key ] = $val;
						update_option( 'sidebars_widgets', $mltlngg_available_sidebars );
					}
				}
				/* If only one sidebar */
			} elseif ( count( $mltlngg_available_sidebars ) == 3 ) {
				foreach ( $mltlngg_available_sidebars as $key => $val ) {
					if ( $key != 'wp_inactive_widgets' && $key != 'array_version' && ! preg_match( '/multi_language_widget-?\d*/', implode( ',', $val ) ) ) { /* If there is no Multilanguage Widget in this sidebar */
						array_unshift( $val, 'multi_language_widget-2' ); /* Adding Multilanguage Widget to the top of sidebar */
						$mltlngg_available_sidebars[ $key ] = $val;
						update_option( 'sidebars_widgets', $mltlngg_available_sidebars );
					}
				}
			}
			$widget_options = get_option( 'widget_multi_language_widget' ); /* Get widget options */
			if ( ! isset( $widget_options[2] ) ) {
				$widget_options[2] = array( 'wiget_title' =>  '', 'mltlngg_language_switcher' => 'drop-down-list' ); /* Set widget options */
				update_option( 'widget_multi_language_widget', $widget_options );
			}
		}
	}
}

if ( ! function_exists( 'mltlngg_plugin_load' ) ) {
	function mltlngg_plugin_load() {
		session_start();
		global $mltlngg_get_default_language, $mltlngg_enabled_languages_locale, $mltlngg_enabled_languages;
		$mltlngg_options = get_option( 'mltlngg_options' );
		if ( ! empty( $mltlngg_options ) ) {
			$mltlngg_get_default_language = $mltlngg_options['default_language'];
			$mltlngg_enabled_languages = array(); /* Array with all enabled languages */
			$mltlngg_enabled_languages_locale = array(); /* Array with codes of all enabled languages */
			foreach ( $mltlngg_options['list_of_languages'] as $mltlngg_one_language ) {
				if ( true === $mltlngg_one_language['enable'] ) {
					$mltlngg_enabled_languages[] = $mltlngg_one_language;
					$mltlngg_enabled_languages_locale[] = $mltlngg_one_language['locale'];
				}
			}

			/* Switch display language function */
			mltlngg_get_display_language();

			/* Change Wordpress localization if the option is enabled */
			if ( ! is_admin() && ! empty( $mltlngg_options['wp_localization'] ) && true == $mltlngg_options['wp_localization'] ) {
				add_filter( 'locale', 'mltlngg_switch_wp_locale' );
			}

			/* Internationalization */
			load_plugin_textdomain( 'multilanguage', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

			/* Do not redirect if is admin/login/logout/ page */
			if ( ! is_admin() && ! preg_match( "/wp-login.php|wp-comments-post.php/", $_SERVER['REQUEST_URI'] ) )
				/* Redirect to URL with language code */
				mltlngg_redirect();
		}
	}
}

/* Function for change display language */
if ( ! function_exists( 'mltlngg_get_display_language' ) ) {
	function mltlngg_get_display_language() {
		global $mltlngg_current_language, $mltlngg_old_language, $mltlngg_enabled_languages_locale, $mltlngg_enabled_languages, $mltlngg_get_default_language;
		/* Do not change the language on the admin page if language changed in the frontend */
		if ( is_admin() ) {
			$wp_locale = get_locale();
			$mltlngg_current_language = ( ! empty( $wp_locale ) && in_array( $wp_locale, $mltlngg_enabled_languages_locale ) ) ? $wp_locale : 'en_US';
		} else {
			/* The language is changed via widget */
			if ( isset( $_POST['mltlngg_change_display_lang'] ) ) {
				if ( empty( $mltlngg_old_language ) && isset( $_SESSION['language'] ) )
					$mltlngg_old_language = $_SESSION['language']; /* Language before changing */
				$mltlngg_current_language = $_POST['mltlngg_change_display_lang']; /* Language after changing */
				$_SESSION['language'] = $mltlngg_current_language;
				/* The language is entered in the url */
			} elseif ( preg_match( '(' . implode( '|', $mltlngg_enabled_languages_locale ) . ')', $_SERVER['REQUEST_URI'], $matches ) ) {
				$mltlngg_current_language = $matches[0];
				$_SESSION['language'] = $mltlngg_current_language;
				/* The language is not changed, leave the current language from session */
			} elseif ( isset( $_SESSION['language'] ) && NULL != $_SESSION['language'] && in_array( $_SESSION['language'], $mltlngg_enabled_languages_locale ) && count( $mltlngg_enabled_languages ) > 1 ) {
				$mltlngg_current_language = $_SESSION['language'];
				/* The language is never chosen, assign the default language from options */
			} else {
				$mltlngg_current_language = $mltlngg_get_default_language;
				$_SESSION['language'] = $mltlngg_current_language;
			}
		}
	}
}

/* Function for change Wordpress localization when change display language */
if ( ! function_exists( 'mltlngg_switch_wp_locale' ) ) {
	function mltlngg_switch_wp_locale( $lang ) {
		global $mltlngg_current_language;
		return $mltlngg_wp_local = ( ! empty( $mltlngg_current_language ) ) ? $mltlngg_current_language : $lang;
	}
}

/* Function for redirect when display language is changed */
if ( ! function_exists( 'mltlngg_redirect' ) ) {
	function mltlngg_redirect() {
		global $mltlngg_current_language, $mltlngg_old_language, $mltlngg_enabled_languages;
		if ( ! empty( $mltlngg_old_language ) && ! empty( $mltlngg_current_language ) && $mltlngg_old_language != $mltlngg_current_language ) {
			if ( false === strpos( $_SERVER['REQUEST_URI'], $mltlngg_old_language ) ) {
				$home = get_option( 'home' );
				$mltlngg_permalink = get_option( 'permalink_structure' );
				$href = ( $mltlngg_permalink != '' ) ? ( str_replace( $home, $home . '/' . $mltlngg_current_language , ( isset( $_SERVER["HTTPS"] ) && $_SERVER["HTTPS"] == "on" ) ? "https://" : "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ) ) : ( ( ( isset( $_SERVER["HTTPS"] ) && $_SERVER["HTTPS"] == "on" ) ? "https://" : "http://" ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . ( ( false === strpos( $_SERVER['REQUEST_URI'], '?' ) ) ? '?lang=' . $mltlngg_current_language : '&lang=' . $mltlngg_current_language ) );
			} else {
				$href = str_replace( $mltlngg_old_language, $mltlngg_current_language, ( isset( $_SERVER["HTTPS"] ) && $_SERVER["HTTPS"] == "on" ) ? "https://" : "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
			}
			wp_redirect( $href );
			exit();
		} elseif ( empty( $mltlngg_old_language ) && count( $mltlngg_enabled_languages ) > 1 && false === strpos( $_SERVER['REQUEST_URI'], $mltlngg_current_language ) ) {
			$home = get_option( 'home' );
			$mltlngg_permalink = get_option( 'permalink_structure' );
			$href = ( $mltlngg_permalink != '' ) ? ( str_replace( $home, $home . '/' . $mltlngg_current_language , ( isset( $_SERVER["HTTPS"] ) && $_SERVER["HTTPS"] == "on" ) ? "https://" : "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ) ) : ( ( ( isset( $_SERVER["HTTPS"] ) && $_SERVER["HTTPS"] == "on" ) ? "https://" : "http://" ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . ( ( false === strpos( $_SERVER['REQUEST_URI'], '?' ) ) ? '?lang=' . $mltlngg_current_language : '&lang=' . $mltlngg_current_language ) );
			wp_redirect( $href );
			exit();
		}
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
		global $wp_rewrite;
		$mltlngg_options = get_option( 'mltlngg_options' );
		$mltlngg_enabled_languages_locale = array(); /* Array with codes of all enabled languages */
		foreach ( $mltlngg_options['list_of_languages'] as $mltlngg_one_language ) {
			if ( true === $mltlngg_one_language['enable'] ) {
				$mltlngg_enabled_languages_locale[] =  $mltlngg_one_language['locale'];
			}
		}
		$mltlngg_lang_regular = '(' . implode( '|', $mltlngg_enabled_languages_locale ) . ')';
		add_rewrite_tag( '%lang%', $mltlngg_lang_regular, 'lang=' );
		$new_rules = array( $mltlngg_lang_regular . '/?$' => 'index.php?lang=$matches[1]' );
		foreach ( $wp_rewrite->rules as $left => $right ) {
			$new_rules[ $mltlngg_lang_regular . '/' . $left ] = preg_replace_callback( '/matches\[(\d{1,2})\]/', 'mltlngg_replace_matched_rule', $right ) . '&lang=$matches[1]';
		}
		$wp_rewrite->rules = $new_rules + $wp_rewrite->rules;
		return $wp_rewrite->rules;
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
		global $mltlngg_current_language, $mltlngg_enabled_languages;
		if ( count( $mltlngg_enabled_languages ) > 1 && ! is_admin() && false === strpos( $_SERVER['REQUEST_URI'], 'wp-login.php' ) ) {
			$mltlngg_permalink = get_option( 'permalink_structure' );
			if ( $mltlngg_permalink != '' ) {
				$home = get_option( 'home' );
				$url = str_replace( $home, $home . '/' . $mltlngg_current_language, $url );
			} else {
				$url .= ( false === strpos( $url, '?' ) ) ? '?lang=' . $mltlngg_current_language : '&lang=' . $mltlngg_current_language;
			}
		}
		return $url;
	}
}

/* Load scripts and styles */
if ( ! function_exists( 'mltlngg_script_style' ) ) {
	function mltlngg_script_style() {
		global $wp_version;
		if ( 3.8 > $wp_version ) {
			wp_enqueue_style( 'mltlngg_stylesheet', plugins_url( 'css/style_wp_before_3.8.css', __FILE__ ) );
		} else {
			wp_enqueue_style( 'mltlngg_stylesheet', plugins_url( 'css/style.css', __FILE__ ) );
		}
		wp_enqueue_script( 'mltlngg_script', plugins_url( 'js/script.js', __FILE__ ), array( 'jquery' ) );
		wp_localize_script( 'mltlngg_script', 'mltlngg_vars',
			array(
				'update_post_error' => __( 'Attention!!! The changes will not be saved because Title and Content fields are empty on the current tab! It is recommended to fill in at least one field or switch to the tab with the fields that are already filled.', 'multilanguage' )
			)
		);
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
				array( 'description' => __( 'Content language switch', 'multilanguage' ), ) /* Args */
			);
		}

		/**
		 * Front-end display of widget.
		 *
		 * @see WP_Widget::widget()
		 *
		 * @param array $args     Widget arguments.
		 * @param array $instance Saved values from database.
		 */
		public function widget( $args, $instance ) {
			global $mltlngg_current_language, $mltlngg_enabled_languages;
			$mltlngg_language_switcher = ( isset( $instance['mltlngg_language_switcher'] ) ) ? $instance['mltlngg_language_switcher'] : 'drop-down-list';
			$title = ( isset( $instance['title'] ) ) ? apply_filters( 'widget_title', $instance['title'] ) : NULL;
			echo $args['before_widget'];
			echo $args['before_title'];
			echo ( NULL != $title ) ? $title : __( 'Localization', 'multilanguage' );
			echo $args['after_title'];
			/* Language switcher style */
			switch ( $mltlngg_language_switcher ) {
				case 'drop-down-list': ?>
					<select style="background-image: url(<?php echo plugins_url( 'images/flags/' , __FILE__ ) . $mltlngg_current_language . '.png'; ?>); background-repeat: no-repeat; background-position: left center; padding-left: 20px; display: block !important;" name="mltlngg_change_display_lang" onchange="document.location.href=this.options[this.selectedIndex].value;">
						<?php foreach ( $mltlngg_enabled_languages as $mltlngg_one_language ) {
							$mltlngg_selected = ( $mltlngg_one_language['locale'] == $mltlngg_current_language ) ? 'selected="selected"' : '';
							$mltlngg_option_display = ( $mltlngg_one_language['locale'] == $mltlngg_current_language && count( $mltlngg_enabled_languages ) > 1 ) ? ' display: none;' : '';
							$language_link = str_replace( $mltlngg_current_language, $mltlngg_one_language['locale'], ( isset( $_SERVER["HTTPS"] ) && $_SERVER["HTTPS"] == "on" ) ? "https://" : "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
							echo '<option ' . $mltlngg_selected . ' value="' . $language_link . '" style="background-image: url(' . plugins_url( 'images/flags/' , __FILE__ ) . $mltlngg_one_language['locale'] . '.png' . '); background-repeat: no-repeat; background-position: left center; padding-left: 20px;' . $mltlngg_option_display . '">' . $mltlngg_one_language['name'] . '</option>';
						} ?>
					</select>
					<?php break;

				case 'drop-down-icons': ?>
					<form name="mltlngg_change_language" method="post" action="">
						<ul class="mltlngg-lang-switch">
							<li>
								<img src="<?php echo plugins_url( 'images/flags/' , __FILE__ ) . $mltlngg_current_language . '.png'; ?>">
								<ul>
									<?php foreach ( $mltlngg_enabled_languages as $mltlngg_one_lang ) {
										if ( $mltlngg_one_lang['locale'] != $mltlngg_current_language ) { ?>
											<li>
												<button class="mltlngg-lang-button" name="mltlngg_change_display_lang" value="<?php echo $mltlngg_one_lang['locale']; ?>" title="<?php echo $mltlngg_one_lang['name']; ?>">
													<img class="mltlngg-lang" src="<?php echo plugins_url( 'images/flags/' , __FILE__ ) . $mltlngg_one_lang['locale'] . '.png'; ?>" alt="<?php echo $mltlngg_one_lang['name']; ?>">
												</button>
											</li>
										<?php }
									} ?>
								</ul>
							</li>
						</ul>
					</form>
					<?php break;

				case 'flags-icons': ?>
					<form name="mltlngg_change_language" method="post" action="">
						<?php foreach ( $mltlngg_enabled_languages as $mltlngg_one_lang ) { ?>
							<button class="mltlngg-lang-button-icons" name="mltlngg_change_display_lang" value="<?php echo $mltlngg_one_lang['locale']; ?>" title="<?php echo $mltlngg_one_lang['name']; ?>">
								<img <?php echo ( $mltlngg_one_lang['locale'] == $mltlngg_current_language ) ? 'class="mltlngg-current-lang"' : 'class="mltlngg-lang"' ?> src="<?php echo plugins_url( 'images/flags/' , __FILE__ ) . $mltlngg_one_lang['locale'] . '.png'; ?>" alt="<?php echo $mltlngg_one_lang['name']; ?>">
							</button>
						<?php } ?>
					</form>
					<?php break;
			}
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
			global $wp_version;
			$title = ( isset( $instance['title'] ) ) ? $instance['title'] : NULL;
			$mltlngg_language_switcher = ( isset( $instance['mltlngg_language_switcher'] ) ) ? $instance['mltlngg_language_switcher'] : 'drop-down-list'; ?>
			<p>
				<label><?php _e( 'Title:' ); ?>
					<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
				</label>
			</p>
			<div style="clear: both;">
				<label style="float: left">
					<input id="drop-down-list" name="<?php echo $this->get_field_name( 'mltlngg_language_switcher' ); ?>" type="radio" value="drop-down-list" <?php echo ( ( $mltlngg_language_switcher == 'drop-down-list' ) ? 'checked' : '' ); ?>><?php _e( 'Drop-down languages list', 'multilanguage' ) ?>
				</label>
				<?php if ( version_compare( $wp_version, '3.8', ">=" ) ) { ?>
					<div class="mltlngg-help-box">
						<div class="mltlngg-hidden-help-text"><img title="" src="<?php echo plugins_url( 'images/tooltip_drop_down_list.png', __FILE__ ); ?>" alt=""></div>
					</div>
				<?php } ?>
			</div>
			<div style="clear: both;">
				<label style="float: left">
					<input id="drop-down-icons" name="<?php echo $this->get_field_name( 'mltlngg_language_switcher' ); ?>" type="radio" value="drop-down-icons" <?php echo ( ( $mltlngg_language_switcher == 'drop-down-icons' ) ? 'checked' : '' ); ?>><?php _e( 'Drop-down flag icons', 'multilanguage' ) ?>
				</label>
				<?php if ( version_compare( $wp_version, '3.8', ">=" ) ) { ?>
					<div class="mltlngg-help-box">
						<div class="mltlngg-hidden-help-text"><img title="" src="<?php echo plugins_url( 'images/tooltip_drop_down_icons.png', __FILE__ ); ?>" alt=""></div>
					</div>
				<?php } ?>
			</div>
			<div style="clear: both;">
				<label style="float: left">
					<input id="flags-icons" name="<?php echo $this->get_field_name( 'mltlngg_language_switcher' ); ?>" type="radio" value="flags-icons" <?php echo ( ( $mltlngg_language_switcher == 'flags-icons' ) ? 'checked' : '' ); ?>><?php _e( 'Flag icons', 'multilanguage' ) ?>
				</label>
				<?php if ( version_compare( $wp_version, '3.8', ">=" ) ) { ?>
					<div class="mltlngg-help-box">
						<div class="mltlngg-hidden-help-text"><img title="" src="<?php echo plugins_url( 'images/tooltip_flags_icons.png', __FILE__ ); ?>" alt=""></div>
					</div>
				<?php } ?>
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
			$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : NULL;
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

/* Display settings page of plugin */
if ( ! function_exists( 'mltlngg_settings_page' ) ) {
	function mltlngg_settings_page() {
		global $mltlngg_options, $mltlngg_message_value;
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		/* Change settings options */
		if ( isset( $_POST['mltlngg_settings_form_was_send'] ) && check_admin_referer( 'mltlngg_settings_form', 'mltlngg_settings_form_field' ) ) {
			if ( isset( $_POST['mltlngg_settings_form_submit'] ) ) {
				$mltlngg_options['enabled_new_language'] = ( ! isset( $_POST['mltlngg_new_language_enable'] ) ) ? false : true;
				$mltlngg_options['autosave_editor_content'] = ( ! isset( $_POST['mltlngg_autosave_editor_content'] ) ) ? false : true;
				$mltlngg_options['wp_localization'] = ( ! isset( $_POST['mltlngg_wp_localization'] ) ) ? false : true;
				update_option( 'mltlngg_options', $mltlngg_options );
				$mltlngg_message_value = __( 'Settings saved', 'multilanguage' );
			}
		}
		/* Adding language */
		if ( isset( $_POST['mltlngg_add_new_language_form_was_send'] ) && ! empty( $_POST['mltlngg_lang_list'] ) && check_admin_referer( 'mltlngg_add_new_language_form', 'mltlngg_add_new_language_field' ) ) {
			if ( preg_match( '/^([a-z]{2,3}|[a-z]{2}[_][A-Z]{2})-([\D]+?)$/', $_POST['mltlngg_lang_list'], $matches ) ) { /* If language data is correct */
				mltlngg_add_language( $matches[1], $matches[2] ); /* Add new language (locale, name) */
				$mltlngg_message_value = __( 'Language added', 'multilanguage' );
			} else { /* If language data is incorrect */
				$mltlngg_message_value = __( 'Incorrect language data', 'multilanguage' );
			}
		}
		/* Change priority and default language options */
		if ( isset( $_POST['mltlngg_language_form_was_send'] ) && check_admin_referer( 'mltlngg_current_languages_form', 'mltlngg_current_languages_field' ) ) {
			if ( isset( $_POST['mltlngg_language_form_submit'] ) ) {
				foreach ( $mltlngg_options['list_of_languages'] as $key => $mltlngg_language_to_action ) {
					if ( isset( $_POST[ $mltlngg_language_to_action['locale'] ] ) && NULL != $_POST[ $mltlngg_language_to_action['locale'] ] )
						$mltlngg_options['list_of_languages'][ $key ]['priority'] = $_POST[ $mltlngg_language_to_action['locale'] ];
					$mltlngg_priority[ $key ] = $mltlngg_options['list_of_languages'][ $key ]['priority'];
				}
				if ( isset( $_POST['mltlngg_default_lang'] ) && NULL != $_POST['mltlngg_default_lang'] )
					$mltlngg_options['default_language'] = $_POST['mltlngg_default_lang'];
				/* Sorting list of language by priority */
				array_multisort( $mltlngg_priority, SORT_ASC, $mltlngg_options['list_of_languages'] );
				update_option( 'mltlngg_options', $mltlngg_options );
				$mltlngg_message_value = __( 'Settings saved', 'multilanguage' );
			}
		}
		/* Actions for table */
		if ( isset( $_GET['action'] ) && isset( $_GET['locale'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'mltlngg-action' ) ) {
			if ( preg_match( '/[a-z]{2,3}|[a-z]{2}[_][A-Z]{2}/', $_GET['locale'] ) && preg_match( '(enable|disable|delete)', $_GET['action'], $matches ) )
				mltlngg_actions( $matches[0], $_GET['locale'] );
		}
		/* Bulk actions part */
		if ( ( ( isset( $_POST['action'] ) && -1 != $_POST['action'] ) || ( isset( $_POST['action2'] ) && -1 != $_POST['action2'] ) ) && isset( $_POST['language'] ) && ! isset( $_POST['mltlngg_language_form_submit'] ) && check_admin_referer( 'mltlngg_current_languages_form', 'mltlngg_current_languages_field' ) ) {
			foreach ( $_POST['language'] as $mltlngg_language_to_action ) {
				if ( -1 != $_POST['action'] ) {
					mltlngg_actions( $_POST['action'], $mltlngg_language_to_action );
				} elseif ( -1 != $_POST['action2'] ) {
					mltlngg_actions( $_POST['action2'], $mltlngg_language_to_action );
				}
			}
		}
		/* Display tab of setting page */
		if ( ! isset( $_GET['tab'] ) ) {
			mltlngg_languages_tab();
		} else {
			switch ( $_GET['tab'] ) {
				case 'settings':
					mltlngg_settings_tab();
					break;
			}
		}
	}
}

/* Display Languages Tab on settings page */
if ( ! function_exists( 'mltlngg_languages_tab' ) ) {
	function mltlngg_languages_tab() {
		global $mltlngg_message_value; ?>
		<div class="wrap" id="mltlngg-settings">
			<div class="icon32 icon32-bws" id="icon-options-general"></div>
			<h2><?php _e( 'Multilanguage Settings', 'multilanguage' ); ?>
				<a class="add-new-h2" href="#" id="mltlngg-add-lang-link"><?php _e( 'Add&nbsp;language', 'multilanguage' ); ?></a>
			</h2>
			<!-- Display language tab on setting page -->
			<h2 class="nav-tab-wrapper">
				<a class="nav-tab nav-tab-active" href="<?php echo admin_url( 'admin.php?page=mltlngg_settings', '' ); ?>"><?php _e( 'Languages', 'multilanguage' ); ?></a>
				<a class="nav-tab" href="<?php echo admin_url( 'admin.php?page=mltlngg_settings&tab=settings', '' ); ?>"><?php _e( 'Settings', 'multilanguage' ); ?></a>
				<a class="nav-tab" href="http://bestwebsoft.com/products/multilanguage/faq/" target="_blank"><?php _e( 'FAQ', 'multilanguage' ); ?></a>
			</h2><!-- .nav-tab-wrapper -->
			<!-- /end language tab on setting page -->
			<?php if ( isset( $_POST['mltlngg_language_form_was_send'] ) || isset( $_POST['mltlngg_add_new_language_form_was_send'] ) || isset( $_GET['action'] ) ) : ?>
				<div class="updated fade below-h2"><p><?php echo $mltlngg_message_value; ?></p></div>
			<?php endif; ?>
			<div id="mltlngg-settings-notice" class="updated fade" style="display:none"><p><strong><?php _e( "Notice:", 'multilanguage' ); ?></strong> <?php _e( "The plugin's settings have been changed. In order to save them please don't forget to click the 'Save Changes' button.", 'multilanguage' ); ?></p></div>
			<!-- Form for adding new language -->
			<?php mltlngg_add_language_form(); ?>
			<!-- /form for adding new language -->
			<form name="mltlngg_current_languages_form" method="post" action="" id="mltlngg-current-languages-form">
				<!-- display table of languages, source - table.php -->
				<?php mltlngg_table();
				wp_nonce_field( 'mltlngg_current_languages_form', 'mltlngg_current_languages_field' ); ?>
				<!-- /table of languages -->
				<br>
				<input type="submit" name="mltlngg_language_form_submit" class="button-primary" value="<?php _e( 'Save changes', 'multilanguage' ); ?>">
				<input type="hidden" name="mltlngg_language_form_was_send" value="1">
			</form><!-- #mltlngg_current_languages_form -->
			<div><p>&nbsp;</p></div>
			<div class="bws-plugin-reviews">
				<div class="bws-plugin-reviews-rate">
					<?php _e( 'If you enjoy our plugin, please give it 5 stars on WordPress', 'multilanguage' ); ?>:
					<a href="http://wordpress.org/support/view/plugin-reviews/multilanguage/" target="_blank" title="Multi&nbsp;Language"><?php _e( 'Rate the plugin', 'multilanguage' ); ?></a>
				</div><!-- .bws-plugin-reviews-rate -->
				<div class="bws-plugin-reviews-support">
					<?php _e( 'If there is something wrong about it, please contact us', 'multilanguage' ); ?>:
					<a href="http://support.bestwebsoft.com">http://support.bestwebsoft.com</a>
				</div><!-- .bws-plugin-reviews-support -->
			</div><!-- .bws-plugin-reviews -->
		</div><!-- .wrap -->
	<?php }
}

/* Form for adding new language on settings page */
if ( ! function_exists( 'mltlngg_add_language_form' ) ) {
	function mltlngg_add_language_form() {
		global $mltlngg_languages, $mltlngg_options;
		$mltlngg_list_of_added_languages = array(); /* Array with codes of all added languages */
		foreach ( $mltlngg_options['list_of_languages'] as $mltlngg_added_language ) {
			$mltlngg_list_of_added_languages[] = $mltlngg_added_language['locale'];
		} ?>
		<!-- Form for adding new language -->
		<form name="mltlngg_add_new_language_form" method="post" action="admin.php?page=mltlngg_settings" id="mltlngg-add-new-language-form">
			<table class="form-table">
				<tr valign="top">
					<th scope="row">
						<?php _e( 'Choose a language', 'multilanguage' ); ?>
					</th>
					<td>
						<select name="mltlngg_lang_list" id="mltlngg_lang_list">
							<option value=""></option>
							<?php foreach ( $mltlngg_languages as $lg ) {
								if ( ! in_array( $lg[1], $mltlngg_list_of_added_languages ) ) { /* Do not display option if the language was added */
									printf(
										'<option value="%2$s-%3$s">%3$s - %2$s</option>',
										esc_attr( $lg[0] ),
										esc_attr( $lg[1] ),
										esc_html( $lg[2] )
									);
								}
							} ?>
						</select><br/>
						<input type="hidden" name="mltlngg_add_new_language_form_was_send" value="send">
						<p class="submit">
							<input class="button button-primary action" name="mltlngg_add_lang" id="mltlngg_add_lang" type="submit" value="<?php _e( 'Add&nbsp;language', 'multilanguage' ); ?>">
						</p>
					</td>
				</tr>
			</table>
			<?php wp_nonce_field( 'mltlngg_add_new_language_form', 'mltlngg_add_new_language_field' ); ?>
		</form>
		<!-- /form for adding new language -->
	<?php }
}

/* Function for adding new language */
if ( ! function_exists( 'mltlngg_add_language' ) ) {
	function mltlngg_add_language( $mltlngg_locale, $mltlngg_lang_name ) {
		global $mltlngg_options;
		$mltlngg_new_lang = array(
			'locale'	=> $mltlngg_locale,
			'name'		=> $mltlngg_lang_name,
			'enable'	=> ( true == $mltlngg_options['enabled_new_language'] ) ? true : false,
			'priority'	=> count( $mltlngg_options['list_of_languages'] ) + 1
		);
		$mltlngg_options['list_of_languages'][] = $mltlngg_new_lang;
		update_option( 'mltlngg_options', $mltlngg_options );
	}
}

/* Display Settings Tab on settings page */
if ( ! function_exists( 'mltlngg_settings_tab' ) ) {
	function mltlngg_settings_tab() {
		global $mltlngg_message_value, $mltlngg_options; ?>
		<div class="wrap">
			<div class="icon32 icon32-bws" id="icon-options-general"></div>
			<h2><?php _e( 'Multilanguage Settings', 'multilanguage' ); ?>
				<a class="add-new-h2" href="#" id="mltlngg-add-lang-link"><?php _e( 'Add&nbsp;language', 'multilanguage' ); ?></a>
			</h2>
			<!-- Display settings tab on setting page -->
			<h2 class="nav-tab-wrapper">
				<a class="nav-tab" href="<?php echo admin_url( 'admin.php?page=mltlngg_settings', '' ); ?>"><?php _e( 'Languages', 'multilanguage' ); ?></a>
				<a class="nav-tab nav-tab-active" href="<?php echo admin_url( 'admin.php?page=mltlngg_settings&tab=settings', '' ); ?>"><?php _e( 'Settings', 'multilanguage' ); ?></a>
				<a class="nav-tab" href="http://bestwebsoft.com/products/multilanguage/faq/" target="_blank"><?php _e( 'FAQ', 'multilanguage' ); ?></a>
			</h2><!-- .nav-tab-wrapper -->
			<!-- /end settings tab on setting page -->
			<?php if ( isset( $_POST['mltlngg_settings_form_was_send'] ) ) : ?>
				<div class="updated fade below-h2"><p><?php echo $mltlngg_message_value; ?></p></div>
			<?php endif; ?>
			<div id="mltlngg-settings-notice" class="updated fade" style="display:none"><p><strong><?php _e( "Notice:", 'multilanguage' ); ?></strong> <?php _e( "The plugin's settings have been changed. In order to save them please don't forget to click the 'Save Changes' button.", 'multilanguage' ); ?></p></div>
			<!-- form for adding new language -->
			<?php mltlngg_add_language_form(); ?>
			<!-- /form for adding new language -->
			<!-- Table with options form -->
			<form name="mltlngg_settings_form" method="post" action="" id="mltlngg-current-languages-form">
				<table class="form-table" style="max-width: 700px;">
					<tr valign="middle">
						<th scope="row">
							<label for="mltlngg_new_language_enable"><?php _e( 'Enable new language', 'multilanguage' ); ?></label>
						</th>
						<td>
							<input id="mltlngg_new_language_enable" name="mltlngg_new_language_enable" type="checkbox" value="true" <?php echo ( ( true == $mltlngg_options['enabled_new_language'] ) ? ' checked' : '' ); ?>> <label for="mltlngg_new_language_enable"><span style="color: #888888;font-size: 10px;"><?php _e( "The newly added language will be enabled automatically", 'multilanguage' ); ?></span></label>
						</td>
					</tr>
					<tr valign="middle">
						<th scope="row">
							<label for="mltlngg_autosave_editor_content"><?php _e( 'Autosave translation in the editor', 'multilanguage' ); ?></label>
						</th>
						<td>
							<input id="mltlngg_autosave_editor_content" name="mltlngg_autosave_editor_content" type="checkbox" value="true" <?php echo ( ( true == $mltlngg_options['autosave_editor_content'] ) ? ' checked' : '' ); ?>> <label for="mltlngg_autosave_editor_content"><span style="color: #888888;font-size: 10px;"><?php _e( "When switching edit posts/pages translation tab, the changes made in the previous tab will be saved automatically (only when the Javascript is enabled)", 'multilanguage' ); ?></span></label>
						</td>
					</tr>
					<tr valign="middle">
						<th scope="row">
							<label for="mltlngg_wp_localization"><?php _e( 'Switch Wordpress localization', 'multilanguage' ); ?></label>
						</th>
						<td>
							<input id="mltlngg_wp_localization" name="mltlngg_wp_localization" type="checkbox" value="true" <?php echo ( ( true == $mltlngg_options['wp_localization'] ) ? ' checked' : '' ); ?>> <label for="mltlngg_wp_localization"><span style="color: #888888;font-size: 10px;"><?php _e( "When changing the language in the frontend, WordPress localization will also be changed (only in case additional WordPress language packs are installed)", 'multilanguage' ); ?></span></label>
						</td>
					</tr>
				</table><!-- .form-table -->
				<br>
				<input type="submit" name="mltlngg_settings_form_submit" class="button-primary" value="<?php _e( 'Save changes', 'multilanguage' ); ?>">
				<input type="hidden" name="mltlngg_settings_form_was_send" value="1">
				<?php wp_nonce_field( 'mltlngg_settings_form', 'mltlngg_settings_form_field' ); ?>
			</form><!-- name="mltlngg_settings_form" -->
			<!-- /table with options form -->
			<div><p>&nbsp;</p></div>
			<div class="bws-plugin-reviews">
				<div class="bws-plugin-reviews-rate">
					<?php _e( 'If you enjoy our plugin, please give it 5 stars on WordPress', 'multilanguage' ); ?>:
					<a href="http://wordpress.org/support/view/plugin-reviews/multilanguage/" target="_blank" title="Multi&nbsp;Language"><?php _e( 'Rate the plugin', 'multilanguage' ); ?></a>
				</div><!-- .bws-plugin-reviews-rate -->
				<div class="bws-plugin-reviews-support">
					<?php _e( 'If there is something wrong about it, please contact us', 'multilanguage' ); ?>:
					<a href="http://support.bestwebsoft.com">http://support.bestwebsoft.com</a>
				</div><!-- .bws-plugin-reviews-support -->
			</div><!-- .bws-plugin-reviews -->
		</div><!-- .wrap -->
	<?php }
}

/* Adding to post/page editor tabs in enabled languages */
if ( ! function_exists( 'mltlngg_showup_language_tabs_in_editor' ) ) {
	function mltlngg_showup_language_tabs_in_editor() {
		global $wpdb, $post, $mltlngg_options, $mltlngg_language, $mltlngg_active_language, $mltlngg_enabled_languages;
		$mltlngg_post_type = get_post_type( $post->ID );
		if ( $mltlngg_post_type == 'post' || $mltlngg_post_type == 'page' ) {
			$mltlngg_sql = $wpdb->prepare(
				"SELECT `post_content`, `post_title`
						 FROM $wpdb->posts
						 WHERE `ID` = %d
						", $post->ID
			);
			$mltlngg_original_data = $wpdb->get_row( $mltlngg_sql, 'ARRAY_A' ); /* Get original Content & Title for save to hidden fields */ ?>
			<!-- Display tabs in editor for all enabled languages -->
			<div id="get-lang-content" class="mltlngg-nav-tab-wrapper">
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
				<a href="admin.php?page=mltlngg_settings"><?php _e( 'Add&nbsp;language', 'multilanguage' ); ?></a>
			</div> <!-- #get-lang-content .nav-tab-wrapper -->
			<h2>
				<?php echo __( 'Edit for language:', 'multilanguage' ); ?>
				<span id="mltlngg-current-lang"><?php echo $mltlngg_active_language['name']; ?></span>
			</h2>
			<?php wp_nonce_field( 'mltlngg_translate_form', 'mltlngg_translate_form_field' ); ?>
			<!-- Hidden fields with original Title, Content & Active language -->
			<input id="title-<?php echo $mltlngg_options['default_language']; ?>" type="hidden" value="<?php echo $mltlngg_original_data['post_title']; ?>" name="title_<?php echo $mltlngg_options['default_language']; ?>">
			<textarea id="content-<?php echo $mltlngg_options['default_language']; ?>" style="display: none;" name="content_<?php echo $mltlngg_options['default_language']; ?>"><?php echo $mltlngg_original_data['post_content']; ?></textarea>
			<input id="mltlngg-active-language" type="hidden" value="<?php echo $mltlngg_active_language['locale']; ?>" name="mltlngg_active_language">
		<?php }
	}
}

/* Function for save post/page changes to translations tables */
if ( ! function_exists( 'mltlngg_save_post' ) ) {
	function mltlngg_save_post( $post_id ) {
		global $wpdb, $mltlngg_table_translate, $mltlngg_options;
		if ( ( isset( $_POST['save'] ) || isset( $_POST['publish'] ) ) && check_admin_referer( 'mltlngg_translate_form', 'mltlngg_translate_form_field' ) ) {
			$_SESSION['current_language'] = $_POST['mltlngg_active_language'];
			$mltlngg_post_id = $_POST['post_ID'];
			/* If autosave option is disabled save all changes */
			if ( true != $mltlngg_options['autosave_editor_content'] ) {
				/* Formation of a new array with the translation data from all hidden fields */
				$mltlngg_translate_data = array();
				foreach ( $_POST as $key => $value ) {
					if ( preg_match( '/^(title|content)[_]([a-z]{2,3}|[a-z]{2}[_][A-Z]{2})$/', $key, $matches ) ) { /* Search POST with Title or Content */
						$mltlngg_translate_data[ $matches[2] ]['lang'] = $matches[2]; /* Language code */
						$mltlngg_translate_data[ $matches[2] ][ $matches[1] ] = $value; /* Title or Content */
					}
				}
				/* Save the translation data from new array */
				foreach ( $mltlngg_translate_data as $mltlngg_translate ) {
					if ( $mltlngg_translate['lang'] != $_POST['mltlngg_active_language'] ) { /* If active language tab same as default language do not save changes */
						$mltlngg_sql = $wpdb->prepare(
							"SELECT *
							 FROM $mltlngg_table_translate
							 WHERE `post_ID` = %d AND `language` = '%s'
							", $mltlngg_post_id, $mltlngg_translate['lang']
						);
						$mltlngg_result = $wpdb->get_row( $mltlngg_sql, 'ARRAY_A' ); /* Get translation data for current language from database */
						if ( isset( $mltlngg_result['post_content'] ) && isset( $mltlngg_result['post_title'] ) ) { /* If translation is exist in database, update translation */
							if ( $mltlngg_translate['content'] != $mltlngg_result['post_content'] || $mltlngg_translate['title'] != $mltlngg_result['post_title'] ) {
								$wpdb->update(
									$mltlngg_table_translate,
									array(
										'post_content'	=> wp_unslash( $mltlngg_translate['content'] ),
										'post_title'	=> wp_unslash( $mltlngg_translate['title'] )
									),
									array(
										'post_ID'	=> $mltlngg_post_id,
										'language'	=> $mltlngg_translate['lang']
									),
									array(
										'%s',
										'%s'
									),
									array(
										'%d',
										'%s'
									)
								);
							}
						} elseif ( $mltlngg_translate['content'] != "" || $mltlngg_translate['title'] != "" ) { /* If translation is not exist in database, create translation */
							$wpdb->insert(
								$mltlngg_table_translate,
								array(
									'post_ID'		=> $mltlngg_post_id,
									'post_content'	=> wp_unslash( $mltlngg_translate['content'] ),
									'post_title'	=> wp_unslash( $mltlngg_translate['title'] ),
									'language'		=> $mltlngg_translate['lang']
								),
								array(
									'%d',
									'%s',
									'%s',
									'%s'
								)
							);
						}
					}
				}
			}
			/* Save the translation data from active language tab */
			$mltlngg_sql = $wpdb->prepare(
				"SELECT *
				 FROM $mltlngg_table_translate
				 WHERE `post_ID` = %d AND `language` = '%s'
				", $mltlngg_post_id, $_POST['mltlngg_active_language']
			);
			$mltlngg_result = $wpdb->get_row( $mltlngg_sql, 'ARRAY_A' ); /* Get translation data for current language from database */
			if ( isset( $mltlngg_result['post_content'] ) && isset( $mltlngg_result['post_title'] ) ) { /* If translation is exist in database, update translation */
				if ( $_POST['content'] != $mltlngg_result['post_content'] || $_POST['post_title'] != $mltlngg_result['post_title'] ) {
					$wpdb->update(
						$mltlngg_table_translate,
						array(
							'post_content'	=> wp_unslash( $_POST['content'] ),
							'post_title'	=> wp_unslash( $_POST['post_title'] )
						),
						array(
							'post_ID'	=> $mltlngg_post_id,
							'language'	=> $_POST['mltlngg_active_language']
						),
						array(
							'%s',
							'%s'
						),
						array(
							'%d',
							'%s'
						)
					);
				}
			} else { /* If translation is not exist in database, create translation */
				$wpdb->insert(
					$mltlngg_table_translate,
					array(
						'post_ID'		=> $mltlngg_post_id,
						'post_content'	=> wp_unslash( $_POST['content'] ),
						'post_title'	=> wp_unslash( $_POST['post_title'] ),
						'language'		=> $_POST['mltlngg_active_language']
					),
					array(
						'%d',
						'%s',
						'%s',
						'%s'
					)
				);
			}
			/* Save Title & Content to original post */
			if ( $mltlngg_options['default_language'] != $_POST['mltlngg_active_language'] && isset( $_POST['title_' . $mltlngg_options['default_language']] ) && isset( $_POST['content_' . $mltlngg_options['default_language']] ) ) {
				$post = array(
					'ID'			=> $post_id,
					'post_title'	=> wp_unslash( $_POST['title_' . $mltlngg_options['default_language'] ] ),
					'post_content'	=> wp_unslash( $_POST['content_' . $mltlngg_options['default_language'] ] )
				);
			} else {
				$post = array(
					'ID'			=> $post_id,
					'post_title'	=> wp_unslash( $_POST['post_title'] ),
					'post_content'	=> wp_unslash( $_POST['content'] )
				);
			}
			remove_action( 'save_post', 'mltlngg_save_post' );
			wp_update_post( $post );
			add_action( 'save_post', 'mltlngg_save_post' );
		}
	}
}

/* AJAX function for switch language and auto-save changes to translations tables */
if ( ! function_exists( 'mltlngg_ajax_languages_tab' ) ) {
	function mltlngg_ajax_languages_tab() {
		$ajax_nonce = wp_create_nonce( "mltlngg-ajax-nonce" ); ?>
		<script type="text/javascript">
			(function( $ ) {
				$( document ).ready(function() {
					/* Get option how to update translation */
					var autoSaveContent = '<?php $autosave = get_option( 'mltlngg_options' ); echo $autosave['autosave_editor_content']; ?>', /* Get autosave option */
						getLangContentDiv = $( '#get-lang-content' ); /* Find DIV id=get-lang-content */
					/* Function for click on another language tab to edit translation */
					getLangContentDiv.on( 'click', 'a.nav-tab', function() {
						var inputTitle = $( '#title' ), /* Find INPUT id=title */
							inputContent = $( '#content' ), /* Find INPUT id=content */
							mltlnggPostId = $( '#post_ID' ).attr( 'value' ), /* Get current post ID */
							newLang = ( $( this ).data( 'lang' ) ), /* Get language code from current language tab */
							newLangName = ( $( this ).attr( 'value' ) ), /* Get language name from current language tab */
							inputTitleNewLang = $( 'input#title-' + newLang ), /* Find INPUT id=title-{new lang code} */
							oldLang = getLangContentDiv.find( 'a.nav-tab-active' ).data( 'lang' ), /* Get language code from previous language tab */
							inputTitleOldLang = $( 'input#title-' + oldLang ), /* Find INPUT id=title-{old lang code} */
							mltlnggOldTitle = inputTitle.val(), /* Get title from previous language tab */
							mltlnggOldContent, data;
						getLangContentDiv.find( 'a.nav-tab-active' ).removeClass( 'nav-tab-active' ); /* Change previous language tab from active to inactive */
						$( this ).addClass( 'nav-tab-active' ); /* Change current language tab from inactive to active */
						/* Get content from previous language tab */
						if ( inputContent.is( ":hidden" ) ) { /* If TinyMCE editor is active */
							mltlnggOldContent = tinymce.activeEditor.getContent(); /* Get content from TinyMCE */
						} else { /* If Text editor is active */
							mltlnggOldContent = inputContent.val(); /* Get content from Text editor */
						}
						inputTitleOldLang.val( mltlnggOldTitle ); /* Insert Title from previous language tab to hidden field */
						$( 'textarea#content-' + oldLang ).val( mltlnggOldContent ); /* Insert Content from previous language tab to hidden field */
						if ( inputTitleNewLang.length > 0 ) { /* If hidden blocks is exist, get Title & Content from them */
							inputTitle.val( inputTitleNewLang.val() ); /* Set title to current language tab */
							/* Set content to current language tab */
							if ( inputContent.is( ":hidden" ) ) { /* If TinyMCE editor is active */
								tinymce.activeEditor.setContent( $( 'textarea#content-' + newLang ).val() ); /* Set content to TinyMCE */
							} else { /* If Text editor is active */
								inputContent.val( $( 'textarea#content-' + newLang ).val() ); /* Set content to Text editor */
							}
							/* If autoupdate translation is enabled, save changes */
							if ( 1 == autoSaveContent ) {
								data = {
									'action': 'mltlngg_ajax_callback',
									'old_lang': oldLang,
									'mltlngg_post_id': mltlnggPostId,
									'mltlngg_old_title': mltlnggOldTitle,
									'mltlngg_old_content': mltlnggOldContent,
									'security': '<?php echo $ajax_nonce; ?>'
								};
								$.post( ajaxurl, data );
							}
						} else { /* If hidden blocks is not exist, get Title & Content from database, then create hidden blocks */
							data = {
								'action': 'mltlngg_ajax_callback',
								'get_data': 'get_data',
								'new_lang': newLang,
								'old_lang': oldLang,
								'mltlngg_post_id': mltlnggPostId,
								'mltlngg_old_title': mltlnggOldTitle,
								'mltlngg_old_content': mltlnggOldContent,
								'security': '<?php echo $ajax_nonce; ?>'
							};
							$.post( ajaxurl, data, function ( response ) {
								var mltlnggNew = eval( "(" + response + ")" );
								$( '#post-body-content' ).append(
									$( '<input/>' ) /* Create hidden field with Title */
										.attr( 'type', 'hidden' )
										.attr( 'id', 'title-' + newLang )
										.attr( 'name', 'title_' + newLang )
										.val( mltlnggNew.post_title ),
									$( '<textarea/>' ) /* Create hidden field with Content */
										.css( 'display', 'none' )
										.attr( 'id', 'content-' + newLang )
										.attr( 'name', 'content_' + newLang )
										.val( mltlnggNew.post_content )
								);
								inputTitle.val( mltlnggNew.post_title ); /* Set title to current language tab */
								/* Set content to current language tab */
								if ( inputContent.is( ":hidden" ) ) { /* If TinyMCE editor is active */
									tinymce.activeEditor.setContent( mltlnggNew.post_content );
								} else { /* If Text editor is active */
									inputContent.val( mltlnggNew.post_content );
								}
							});
						}
						$( '#mltlngg-current-lang' ).text( newLangName );
						$( '#mltlngg-active-language' ).val( newLang ); /* Set new active language */
						return false;
					});
				});
			})( jQuery );
		</script>
	<?php }
}

/* Callback function for AJAX function */
if ( ! function_exists( 'mltlngg_ajax_callback' ) ) {
	function mltlngg_ajax_callback() {
		global $wpdb, $mltlngg_table_translate, $mltlngg_options;
		check_ajax_referer( 'mltlngg-ajax-nonce', 'security' );
		
		$mltlngg_id = $_POST['mltlngg_post_id'];
		/* Auto-update translation if it has been changed when autosave option is enabled */
		if ( true == $mltlngg_options['autosave_editor_content'] && ! empty( $_POST['old_lang'] ) ) {
			$mltlngg_sql_old = $wpdb->prepare(
				"SELECT *
				 FROM $mltlngg_table_translate
				 WHERE `post_ID` = %d AND `language` = '%s'
				", $mltlngg_id, $_POST['old_lang']
			);
			$mltlngg_result_old = $wpdb->get_row( $mltlngg_sql_old, 'ARRAY_A' ); /* Get translation data for previous language from database */
			if ( isset( $mltlngg_result_old['post_content'] ) && isset( $mltlngg_result_old['post_title'] ) ) { /* If translation is exist in database, update translation */
				if ( $_POST['mltlngg_old_content'] != $mltlngg_result_old['post_content'] || $_POST['mltlngg_old_title'] != $mltlngg_result_old['post_title'] ) {
					$wpdb->update(
						$mltlngg_table_translate,
						array(
							'post_content'	=> wp_unslash( $_POST['mltlngg_old_content'] ),
							'post_title'	=> wp_unslash( $_POST['mltlngg_old_title'] )
						),
						array(
							'post_ID'	=> $mltlngg_id,
							'language'	=> $_POST['old_lang']
						),
						array(
							'%s',
							'%s'
						),
						array(
							'%d',
							'%s'
						)
					);
				}
			} elseif ( "" != $_POST['mltlngg_old_content'] || "" != $_POST['mltlngg_old_title'] ) { /* If translation not exist in database, create translation */
				$wpdb->insert(
					$mltlngg_table_translate,
					array(
						'post_ID'		=> $mltlngg_id,
						'post_content'	=> wp_unslash( $_POST['mltlngg_old_content'] ),
						'post_title'	=> wp_unslash( $_POST['mltlngg_old_title'] ),
						'language'		=> $_POST['old_lang']
					),
					array(
						'%d',
						'%s',
						'%s',
						'%s'
					)
				);
			}
		}
		/* If received request for a translation for current language */
		if ( isset( $_POST['get_data'] ) && ! empty( $_POST['new_lang'] ) ) {
			$mltlngg_sql = $wpdb->prepare(
				"SELECT *
				 FROM $mltlngg_table_translate
				 WHERE `post_ID` = %d AND `language` = '%s'
				", $mltlngg_id, $_POST['new_lang']
			);
			$mltlngg_post_data = $wpdb->get_row( $mltlngg_sql, 'ARRAY_A' ); /* Get translation data for current language from database */
			if ( isset( $mltlngg_post_data['post_content'] ) && isset( $mltlngg_post_data['post_title'] ) ) { /* If translation is exist, send translation to ajax */
				echo json_encode( $mltlngg_post_data );
			} else { /* If translation is not exist, send empty translation to ajax */
				$mltlngg_post_data = array(
					'post_content'	=> "",
					'post_title'	=> ""
				);
				echo json_encode( $mltlngg_post_data );
			}
		}
		die();
	}
}

/* Add translations form in enabled languages to adding new category/tag */
if ( ! function_exists( 'mltlngg_new_terms_translate' ) ) {
	function mltlngg_new_terms_translate() {
		global $mltlngg_enabled_languages; ?>
		<div class="form-field">
			<?php _e( 'Translations', 'multilanguage' ); ?>
			<!-- Translations form for all enabled languages -->
			<table class="form-table" border="0" style="background-color: #fff; width: 95%;">
				<tbody>
				<?php foreach ( $mltlngg_enabled_languages as $one_language ) { ?>
					<tr class="form-field">
						<td>
							<label for="mltlngg_translate_<?php echo $one_language['locale']; ?>"><img style="margin: auto 5px;" src="<?php echo plugins_url( 'images/flags/' , __FILE__ ) . $one_language['locale'] . '.png' . '"' . 'alt="' . $one_language['name'] . '"' . ' title="' . $one_language['name'] . '"'; ?>"><?php echo $one_language['name']; ?></label>
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
		global $wpdb, $mltlngg_terms_table_translate, $mltlngg_enabled_languages;
		foreach ( $mltlngg_enabled_languages as $one_language ) {
			$cat_lang = 'mltlngg_translate_' . $one_language['locale'];
			if ( isset( $_POST[ $cat_lang ] ) && "" != $_POST[ $cat_lang ] ) { /* If translation is empty, do not create translation in database */
				$wpdb->insert(
					$mltlngg_terms_table_translate,
					array(
						'term_ID'	=> $term_id,
						'name'		=> $_POST[ $cat_lang ],
						'language'	=> $one_language['locale']
					),
					array(
						'%d',
						'%s',
						'%s'
					)
				);
			}
		}
	}
}

/* Adding translations form for all enabled languages to edit category/tag */
if ( ! function_exists( 'mltlngg_terms_translate' ) ) {
	function mltlngg_terms_translate() {
		global $tag_ID, $wpdb, $mltlngg_terms_table_translate, $mltlngg_enabled_languages;
		$mltlngg_term = array();
		foreach ( $mltlngg_enabled_languages as $one_language ) {
			/* Get translation data from database */
			$mltlngg_sql = $wpdb->prepare(
				"SELECT *
				 FROM $mltlngg_terms_table_translate
				 WHERE `term_ID` = %d AND `language` = %s
				", $tag_ID, $one_language['locale']
			);
			$mltlngg_term_result = $wpdb->get_row( $mltlngg_sql, 'ARRAY_A' ); /* Get terms translations from database */
			$mltlngg_term[ $one_language['locale'] ] = $mltlngg_term_result['name'];
		} ?>
		<table class="form-table">
			<tbody>
			<tr class="form-field">
				<th scope="row"><?php _e( 'Translations', 'multilanguage' ); ?></th>
				<td>
					<table class="form-table" border="0" style="background-color: #fff; width: 95%;">
						<tbody>
						<?php foreach ( $mltlngg_enabled_languages as $one_language ) { ?>
							<tr class="form-field">
								<td>
									<label for="mltlngg_translate_<?php echo $one_language['locale']; ?>"><img style="margin: auto 5px;" src="<?php echo plugins_url( 'images/flags/' , __FILE__ ) . $one_language['locale'] . '.png' . '"' . 'alt="' . $one_language['name'] . '"' . ' title="' . $one_language['name'] . '"'; ?>"><?php echo $one_language['name']; ?></label>
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
		global $tag_ID, $wpdb, $mltlngg_terms_table_translate, $mltlngg_enabled_languages;
		if ( isset( $_POST['submit'] ) && check_admin_referer( 'mltlngg_translate_terms_form', 'mltlngg_translate_terms_form_field' ) ) {
			foreach ( $mltlngg_enabled_languages as $one_language ) {
				$cat_lang = 'mltlngg_translate_' . $one_language['locale'];
				$mltlngg_sql = $wpdb->prepare(
					"SELECT *
					 FROM $mltlngg_terms_table_translate
					 WHERE `term_ID` = %d AND `language` = '%s'
					", $tag_ID, $one_language['locale']
				);
				$mltlngg_result = $wpdb->get_row( $mltlngg_sql, 'ARRAY_A' ); /* Get terms translations from database */
				if (  isset( $mltlngg_result['name'] ) && $_POST[ $cat_lang ] != $mltlngg_result['name'] ) { /* If translation is exist in database, update translation */
					$wpdb->update(
						$mltlngg_terms_table_translate,
						array(
							'name'	=> $_POST[ $cat_lang ],
						),
						array(
							'term_ID'	=> $tag_ID,
							'language'	=> $one_language['locale']
						),
						array(
							'%s'
						),
						array(
							'%d',
							'%s'
						)
					);
				} elseif ( ! isset( $mltlngg_result['name'] ) && "" != $_POST[ $cat_lang ] ) { /* If translation is not exist in database, create translation */
					$wpdb->insert(
						$mltlngg_terms_table_translate,
						array(
							'term_ID'	=> $tag_ID,
							'name'		=> $_POST[ $cat_lang ],
							'language'	=> $one_language['locale']
						),
						array(
							'%d',
							'%s',
							'%s'
						)
					);
				}
			}
		}
	}
}

/* Function for delete post translations */
if ( ! function_exists( 'mltlngg_delete_post' ) ) {
	function mltlngg_delete_post( $postid ) {
		global $wpdb, $mltlngg_table_translate;
		$wpdb->query(
			$wpdb->prepare(
				"DELETE
				 FROM $mltlngg_table_translate
				 WHERE `post_ID` = %d
				", $postid
			)
		);
	}
}

/* Function for delete terms translations */
if ( ! function_exists( 'mltlngg_delete_term' ) ) {
	function mltlngg_delete_term( $term ) {
		global $wpdb, $mltlngg_terms_table_translate;
		$wpdb->query(
			$wpdb->prepare(
				"DELETE
 				 FROM $mltlngg_terms_table_translate
				 WHERE `term_ID` = %d
				", $term
			)
		);
	}
}

/* Display post_title in the selected language */
if ( ! function_exists( 'mltlngg_the_title_filter' ) ) {
	function mltlngg_the_title_filter( $title, $id ) {
		global $mltlngg_options, $wpdb, $mltlngg_table_translate, $mltlngg_current_language, $mltlngg_active_language;
		if ( ! is_nav_menu_item( $id ) ) { /* Do not filter, if a navigation menu */
			$mltlngg_post_type = get_post_type( $id );
			/* If current post type enabled to translation */
			if ( $mltlngg_post_type == 'post' || $mltlngg_post_type == 'page' ) {
				if ( is_admin() )
					$mltlngg_current_language = ( ! empty( $_SESSION['current_language'] ) ) ? $_SESSION['current_language'] : ( ( isset( $_GET['lang'] ) ) ? $_GET['lang'] : ( ( ! isset( $_GET['message'] ) ) ? $mltlngg_options['default_language'] : $mltlngg_active_language['locale'] ) );
				$mltlngg_sql = $wpdb->prepare(
					"SELECT *
					 FROM $mltlngg_table_translate
					 WHERE `post_ID` = %d AND `language` = '%s'
				", $id, $mltlngg_current_language
				);
				$new_title = $wpdb->get_row( $mltlngg_sql, 'ARRAY_A' );
				/* If translation is exist and not empty, filter title */
				if ( isset( $new_title['post_title'] ) && "" != $new_title['post_title'] )
					$title = $new_title['post_title'];
			}
		}
		return $title;
	}
}

/* Display nav_menu title in the selected language */
if ( ! function_exists( 'mltlngg_nav_menu_items_filter' ) ) {
	function mltlngg_nav_menu_items_filter( $items ) {
		global $mltlngg_options, $wpdb, $mltlngg_table_translate, $mltlngg_current_language, $mltlngg_terms_table_translate;
		foreach ( $items as $key => $item ) {
			if ( $item->type == 'taxonomy' ) {
				$new_title = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT `name`
						 FROM $mltlngg_terms_table_translate
						 WHERE `term_ID` = %d AND `language` = '%s'
						", $item->object_id, $mltlngg_current_language
					)
				);
				if ( isset( $new_title ) && "" != $new_title ) /* If translation is exist and not empty, filter menu item */
					$item->title = $new_title;
			} elseif ( $item->type == 'post_type' && ( $item->object == 'post' || $item->object == 'page' ) ) {
				$new_title = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT `post_title`
						 FROM $mltlngg_table_translate
						 WHERE `post_ID` = %d AND `language` = '%s'
						", $item->object_id, $mltlngg_current_language
					)
				);
				if ( isset( $new_title ) && "" != $new_title ) /* If translation is exist and not empty, filter menu item */
					$item->title = $new_title;
			}
		}
		return $items;
	}
}

/* Display post_content in the selected language */
if ( ! function_exists( 'mltlngg_the_content_filter' ) ) {
	function mltlngg_the_content_filter( $content ) {
		global $post, $wpdb, $mltlngg_table_translate, $mltlngg_current_language, $mltlngg_active_language, $mltlngg_options;
		$mltlngg_post_type = get_post_type( $post->ID );
		/* If current post type enabled to translation */
		if ( $mltlngg_post_type != 'attachment' && ( $mltlngg_post_type == 'post' || $mltlngg_post_type == 'page' ) ) {
			if ( is_admin() )
				$mltlngg_current_language = ( isset( $_GET['lang'] ) ) ? $_GET['lang'] : ( ( isset( $mltlngg_active_language['locale'] ) ) ? $mltlngg_active_language['locale'] : $mltlngg_options['default_language'] );
			$mltlngg_sql = $wpdb->prepare(
				"SELECT *
				 FROM $mltlngg_table_translate
				 WHERE `post_ID` = %d AND `language` = '%s'
				", $post->ID, $mltlngg_current_language
			);
			$new_content = $wpdb->get_row( $mltlngg_sql, 'ARRAY_A' );
			/* If translation is exist and not empty, filter content */
			if ( isset( $new_content['post_content'] ) && "" != $new_content['post_content'] )
				$content = $new_content['post_content'];
		}
		return $content;
	}
}

/* Display categories list & tags cloud & categories/tags of posts in the selected language */
if ( ! function_exists( 'mltlngg_terms_filter' ) ) {
	function mltlngg_terms_filter( $terms ) {
		global $mltlngg_terms_table_translate, $wpdb, $mltlngg_current_language;
		foreach ( $terms as $mltlngg_one_term ) {
			if ( isset( $mltlngg_one_term->term_id ) ) {
				$mltlngg_one_term_name = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT `name`
						 FROM $mltlngg_terms_table_translate
						 WHERE `term_ID` = %d AND `language` = '%s'
						", $mltlngg_one_term->term_id, $mltlngg_current_language
					)
				);
				if ( isset( $mltlngg_one_term_name ) && "" != $mltlngg_one_term_name ) /* If translation is exist and not empty, filter terms */
					$mltlngg_one_term->name = $mltlngg_one_term_name;
			}
		}
		return $terms;
	}
}

/* Display category & tags names in the title of archive pages in the selected language */
if ( ! function_exists( 'mltlngg_term_filter' ) ) {
	function mltlngg_term_filter( $term ) {
		global $mltlngg_terms_table_translate, $wpdb, $mltlngg_current_language;
		$mltlngg_term_name = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT `name`
				 FROM $mltlngg_terms_table_translate
				 WHERE `term_ID` = %d AND `language` = '%s'
				", $term->term_id, $mltlngg_current_language
			)
		);
		if ( isset( $mltlngg_term_name ) && "" != $mltlngg_term_name ) /* If translation is exist and not empty, filter term */
			$term->name = $mltlngg_term_name;
		return $term;
	}
}

/* Add action links on plugin page in to Plugin Name block */
if ( ! function_exists( 'mltlngg_plugin_action_links' ) ) {
	function mltlngg_plugin_action_links( $links, $file ) {
		/* Static so we don't call plugin_basename on every plugin row. */
		static $this_plugin;
		if ( ! $this_plugin ) $this_plugin = plugin_basename( __FILE__ );
		if ( $file == $this_plugin ) {
			$settings_link = '<a href="admin.php?page=mltlngg_settings">' . __( 'Settings', 'multilanguage' ) . '</a>';
			array_unshift( $links, $settings_link );
		}
		return $links;
	}
}

/* Add action links on plugin page in to Plugin Description block */
if ( ! function_exists( 'mltlngg_register_plugin_links' ) ) {
	function mltlngg_register_plugin_links( $links, $file ) {
		$base = plugin_basename( __FILE__ );
		if ( $file == $base ) {
			$links[]	= '<a href="admin.php?page=mltlngg_settings">' . __( 'Settings', 'multilanguage' ) . '</a>';
			$links[]	= '<a href="http://wordpress.org/plugins/multilanguage/faq/" target="_blank">' . __( 'FAQ', 'multilanguage' ) . '</a>';
			$links[]	= '<a href="http://support.bestwebsoft.com">' . __( 'Support', 'multilanguage' ) . '</a>';
		}
		return $links;
	}
}

/* Delete plugin  blog  */
if ( ! function_exists( 'mltlngg_delete_blog' ) ) {
	function mltlngg_delete_blog( $blog_id ) {
		global $wpdb;
		if ( is_plugin_active_for_network( 'multilanguage/multilanguage.php' ) ) {
			$old_blog = $wpdb->blogid;
			switch_to_blog( $blog_id );
			_mltlngg_delete_options();
			switch_to_blog( $old_blog );
		}
	}
}

/* Delete plugin for network */
if ( ! function_exists( 'mltlngg_delete_options' ) ) {
	function mltlngg_delete_options() {
		global $wpdb;
		if ( function_exists( 'is_multisite' ) && is_multisite() ) {
			/* check if it is a network activation - if so, run the activation function for each blog id */
			$old_blog = $wpdb->blogid;
			/* Get all blog ids */
			$blogids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
			foreach ( $blogids as $blog_id ) {
				switch_to_blog( $blog_id );
				_mltlngg_delete_options();
			}
			switch_to_blog( $old_blog );
			return;
		}
		_mltlngg_delete_options();
	}
}

/* Delete plugin options */
if ( ! function_exists( '_mltlngg_delete_options' ) ) {
	function _mltlngg_delete_options() {
		global $wpdb;
		$mltlngg_table_translate = $wpdb->prefix . 'mltlngg_translate';
		$mltlngg_terms_table_translate = $wpdb->prefix . 'mltlngg_terms_translate';
		$mltlngg_sql = "DROP TABLE `" . $mltlngg_table_translate . "`, `" . $mltlngg_terms_table_translate . "`;";
		$wpdb->query( $mltlngg_sql );

		/* Delete widget from database */
		if ( is_dynamic_sidebar() ) { /* If there is an active sidebar */
			$mltlngg_available_sidebars = get_option( 'sidebars_widgets' ); /* Get all active sidebars and widgets */
			foreach ( $mltlngg_available_sidebars as $key1 => $one_sidebar ) {
				if ( $key1 != 'array_version' ) {
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

		delete_option( 'mltlngg_options' );
		delete_option( 'widget_multi_language_widget' );
		flush_rewrite_rules();
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
add_action( 'admin_enqueue_scripts', 'mltlngg_script_style' );
add_action( 'wp_enqueue_scripts', 'mltlngg_script_style' );
add_action( 'widgets_init', 'register_mltlngg_widget' );

/* Links filters */
add_filter( 'home_url', 'mltlngg_get_url_translated' );

/* Actions for page/post editor */
add_action( 'wp_print_scripts', 'mltlngg_disable_autosave_editor_script', 100 );
add_action( 'edit_form_top', 'mltlngg_showup_language_tabs_in_editor' );	/* Add languages tabs to post editor */
add_filter( 'title_edit_pre', 'mltlngg_the_title_filter', 10, 2 );
add_action( 'the_editor_content', 'mltlngg_the_content_filter' );
add_action( 'save_post', 'mltlngg_save_post' );	/* Saving changes in posts translations */
add_action( 'deleted_post', 'mltlngg_delete_post' );	/* Delete posts translations from database */

/* Filters for display frontend content language */
add_filter( 'the_title', 'mltlngg_the_title_filter', 10, 2 );
add_filter( 'wp_get_nav_menu_items', 'mltlngg_nav_menu_items_filter', 10 );
add_filter( 'the_content', 'mltlngg_the_content_filter', 9 );
add_filter( 'get_terms', 'mltlngg_terms_filter' );	/* Add filter categories list & tags cloud */
add_filter( 'get_the_terms', 'mltlngg_terms_filter' );	/* Add filter categories & tags of posts */
add_filter( 'get_term', 'mltlngg_term_filter' );	/* Add filter category & tags names in title of archive pages */

/* Add screen options on Multilanguage language list Page */
add_filter( 'set-screen-option', 'mltlngg_table_set_option', 10, 3 );

/* Additional links on the plugin page */
add_filter( 'plugin_action_links', 'mltlngg_plugin_action_links', 10, 2 );
add_filter( 'plugin_row_meta', 'mltlngg_register_plugin_links', 10, 2 );

/* Add AJAX function */
add_action( 'admin_footer', 'mltlngg_ajax_languages_tab' );
add_action( 'wp_ajax_mltlngg_ajax_callback', 'mltlngg_ajax_callback' );

register_uninstall_hook( __FILE__, 'mltlngg_delete_options' );
?>