<?php
/*
Plugin Name: Multilanguage by BestWebSoft
Plugin URI: http://bestwebsoft.com/products/
Description: This plugin allows you to display the content in different languages.
Author: BestWebSoft
Text Domain: multilanguage
Domain Path: /languages
Version: 1.1.2
Author URI: http://bestwebsoft.com/
License: GPLv3 or later
*/

/*  © Copyright 2015  BestWebSoft  ( http://support.bestwebsoft.com )

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

require_once( dirname( __FILE__ ) . '/include/table.php' );
require_once( dirname( __FILE__ ) . '/include/languages.php' );
require_once( dirname( __FILE__ ) . '/include/for_oembed.php' );

/* Function add menu pages */
if ( ! function_exists( 'mltlngg_admin_menu' ) ) {
	function mltlngg_admin_menu() {
		bws_add_general_menu( plugin_basename( __FILE__ ) );
		add_submenu_page( 'bws_plugins', 'Multilanguage', 'Multilanguage', 'manage_options', "mltlngg_settings", 'mltlngg_settings_page' );
		mltlngg_add_menu_items();
	}
}

/* Plugin initialisation in backend and frontend */
if ( ! function_exists( 'mltlngg_init' ) ) {
	function mltlngg_init() {
		global $wpdb, $mltlngg_options, $mltlngg_table_translate, $mltlngg_terms_table_translate, $mltlngg_plugin_info;

		require_once( dirname( __FILE__ ) . '/bws_menu/bws_include.php' );
		bws_include_init( plugin_basename( __FILE__ ) );

		if ( empty( $mltlngg_plugin_info ) ) {
			if ( ! function_exists( 'get_plugin_data' ) ) {
				require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			}
			$mltlngg_plugin_info = get_plugin_data( __FILE__ );
		}

		/* check WordPress version */
		bws_wp_min_version_check( plugin_basename( __FILE__ ), $mltlngg_plugin_info, '3.8', '3.7' );

		$mltlngg_table_translate = $wpdb->prefix . 'mltlngg_translate';
		$mltlngg_terms_table_translate = $wpdb->prefix . 'mltlngg_terms_translate';

		mltlngg_register_settings();

		/* If there have been changes in the list of languages do flush_rewrite_rules */
		if ( isset( $_GET['action'] ) || isset( $_POST['action'] ) || isset( $_POST['action2'] ) || ( isset( $_POST['mltlngg_add_new_language_form_was_send'] ) && true == $mltlngg_options['enabled_new_language'] ) ) {
			flush_rewrite_rules();
		}
	}
}

/* Plugin initialisation in backend */
if ( ! function_exists( 'mltlngg_admin_init' ) ) {
	function mltlngg_admin_init() {
		global $bws_plugin_info, $mltlngg_plugin_info, $bws_shortcode_list;

		if ( ! isset( $bws_plugin_info ) || empty( $bws_plugin_info ) ) {
			$bws_plugin_info = array( 'id' => '143', 'version' => $mltlngg_plugin_info["Version"] );
		}		

		/* Actions for categories & tags translation */
		mltlngg_taxonomies();

		/* add Multilanguage to global $bws_shortcode_list  */
		$bws_shortcode_list['mltlngg'] = array( 'name' => 'Multilanguage' );
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
		global $mltlngg_plugin_info, $mltlngg_options, $mltlngg_languages, $mltlngg_language_default, $mltlngg_list_of_languages, $mltlngg_default_options;
		$mltlngg_db_version = '0.2';
		
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
		$mltlngg_default_options = array(
			'plugin_option_version'		=> $mltlngg_plugin_info["Version"],
			'plugin_db_version' 		=> $mltlngg_db_version,
			'default_language'			=> $mltlngg_language_default[1],
			'list_of_languages'			=> $mltlngg_list_of_languages,
			'enabled_new_language'		=> false,
			'autosave_editor_content'	=> true,
			'wp_localization'			=> true,
			'language_switcher'			=> 'drop-down-list',
			'first_install'				=> strtotime( "now" ),
			'display_settings_notice'	=> 1,
			'search'		 			=> 'single'
		);

		/* Add options to database */
		if ( ! get_option( 'mltlngg_options' ) )
			add_option( 'mltlngg_options', $mltlngg_default_options );

		$mltlngg_options = get_option( 'mltlngg_options' );

		/* Array merge incase this version has added new options */
		if ( ! isset( $mltlngg_options['plugin_option_version'] ) || $mltlngg_options['plugin_option_version'] != $mltlngg_plugin_info["Version"] ) {
			$mltlngg_default_options['display_settings_notice'] = 0;
			$mltlngg_options = array_merge( $mltlngg_default_options, $mltlngg_options );
			$mltlngg_options['plugin_option_version'] = $mltlngg_plugin_info["Version"];
			$update_option = true;
		}

		if ( ! isset( $mltlngg_options['plugin_db_version'] ) || $mltlngg_options['plugin_db_version'] != $mltlngg_db_version ) {
			global $wpdb;

			$column_exists = $wpdb->query( "SHOW COLUMNS FROM `" . $wpdb->prefix . "mltlngg_translate` LIKE 'post_excerpt';" );
			if ( 0 < $column_exists )
				$wpdb->query( 'ALTER TABLE `' . $wpdb->prefix . 'mltlngg_translate` ADD `post_excerpt` TEXT NOT NULL AFTER `post_content`' );
			
			$mltlngg_options['plugin_db_version'] = $mltlngg_db_version;
			$update_option = true;
		}

		if ( isset( $update_option ) )
			update_option( 'mltlngg_options', $mltlngg_options );
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
		global $wpdb, $mltlngg_table_translate, $mltlngg_terms_table_translate, $mltlngg_options;
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		$mltlngg_table_translate = $wpdb->prefix . 'mltlngg_translate';
		$mltlngg_terms_table_translate = $wpdb->prefix . 'mltlngg_terms_translate';

		$mltlngg_sql =
			"CREATE TABLE IF NOT EXISTS `" . $mltlngg_table_translate . "` (
			`ID` INT(6) UNSIGNED NOT NULL AUTO_INCREMENT,
			`post_ID` INT(6) NOT NULL,
			`post_content` longtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
			`post_excerpt` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
			`post_title` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
			`language` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
			PRIMARY KEY (`ID`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
		dbDelta( $mltlngg_sql );

		$mltlngg_sql =
			"CREATE TABLE IF NOT EXISTS `" . $mltlngg_terms_table_translate . "` (
			`ID` INT(6) UNSIGNED NOT NULL AUTO_INCREMENT,
			`term_ID` INT(6) NOT NULL,
			`name` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
			`language` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
			PRIMARY KEY (`ID`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
		dbDelta( $mltlngg_sql );

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
		if ( empty( $mltlngg_options ) )
			$mltlngg_options = get_option( 'mltlngg_options' );

		if ( isset( $mltlngg_options['deactivation'] ) ) {
			/* update content */
			$posts = $wpdb->get_results( 
				"SELECT $wpdb->posts.`ID`, $wpdb->posts.`post_content`, $wpdb->posts.`post_title`, $wpdb->posts.`post_excerpt`
					FROM $wpdb->posts, $mltlngg_table_translate
					WHERE 
						`post_type` IN ('page', 'post') AND
						$wpdb->posts.`ID` = $mltlngg_table_translate.`post_ID` AND
						`post_modified` > '" . $mltlngg_options['deactivation'] . "'"
			);

			foreach ( $posts as $key => $value ) {
				$wpdb->update(
					$mltlngg_table_translate,
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
			update_option( 'mltlngg_options', $mltlngg_options );
		}
	}
}

if ( ! function_exists( 'mltlngg_plugin_load' ) ) {
	function mltlngg_plugin_load() {
		if ( session_id() == "" ) {
			session_start();
		}
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
			if ( ! is_admin() && ! preg_match( "/\/?\w+\.{1}[a-z]{3,4}/", $_SERVER['REQUEST_URI'] ) ) {
				/* Redirect to URL with language code */
				mltlngg_redirect();				
			}
		}
	}
}

/* check is wordpress in subfolder */
if ( ! function_exists( 'mltlngg_is_subfolder' ) ) {
	function mltlngg_is_subfolder() {
		if ( is_multisite() )
			return false;
		$homeurl = get_option( 'home' );
		$homeurl = str_replace( 'http://', '', $homeurl );
		$homeurl = str_replace( 'https://', '', $homeurl );
		if ( false == strpos( $homeurl, '/' ) )
			return false;
		return true;
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
		global $current_blog, $mltlngg_current_language, $mltlngg_old_language, $mltlngg_enabled_languages;
		if ( ! function_exists( 'is_plugin_active' ) )
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		if ( ( isset( $_POST['wp_customize'] ) && $_POST['wp_customize'] == 'on' ) || ( is_plugin_active('nextgen-gallery/nggallery.php') && isset( $_REQUEST['photocrati_ajax'] ) ) ) {
			return;
		}

		$home = get_option( 'home' );
		$mltlngg_permalink = get_option( 'permalink_structure' );

		$mltlngg_is_subfolder = mltlngg_is_subfolder();
		if ( ( is_multisite() && ! is_subdomain_install() ) || $mltlngg_is_subfolder ) {
			if ( ! $mltlngg_is_subfolder )	{		
				if ( $current_blog ) {
					$home_main = ( ( isset( $_SERVER["HTTPS"] ) && $_SERVER["HTTPS"] == "on" ) ? "https://" : "http://" ) . $current_blog->domain;
					$home_main = esc_url( $home_main );
				}
			} else {
				$home_dir = str_replace( 'http://www.', '', $home );
				$home_dir = str_replace( 'https://www.', '', $home_dir );
				$home_dir = str_replace( 'http://', '', $home_dir );
				$home_dir = str_replace( 'https://', '', $home_dir );
				$server_name = $_SERVER['SERVER_NAME'];
				if ( 'www.' == substr( $server_name, 0, 4 ) )
					$server_name = substr( $server_name, 4 );
				$home_dir = str_replace( $server_name . ':' . $_SERVER['SERVER_PORT'], '', $home_dir );
				$home_dir = str_replace( $server_name, '', $home_dir );
				$home_dir = rtrim( $home_dir, '/ ' );
				$home_dir_count = strlen( $home_dir );
				$home_main = substr( $home, 0, - $home_dir_count );
			}

			if ( ! empty( $mltlngg_old_language ) && ! empty( $mltlngg_current_language ) && $mltlngg_old_language != $mltlngg_current_language ) {
				if ( false === strpos( $_SERVER['REQUEST_URI'], $mltlngg_old_language ) ) {
					if ( $mltlngg_permalink != '' ) {
						$href = str_replace( $home, ( $home . '/' . $mltlngg_current_language ), ( $home_main . $_SERVER['REQUEST_URI'] ) );
					} else {
						$href = $home_main . $_SERVER['REQUEST_URI'];
						if ( 'page' == get_option( 'show_on_front' ) && false === strpos( $_SERVER['REQUEST_URI'], '?' ) ) {
							$front_page_id = get_option( 'page_on_front' );
							$href .= ( false === strpos( str_replace( 'lang=' . $mltlngg_old_language, '', $_SERVER['REQUEST_URI'] ), '=' ) ) ? '?page_id=' . $front_page_id : '&page_id=' . $front_page_id;
							$href .= '&lang=' . $mltlngg_current_language;
						} else {
							$href .= ( false === strpos( $_SERVER['REQUEST_URI'], '?' ) ) ? '?lang=' . $mltlngg_current_language : '&lang=' . $mltlngg_current_language;
						}
					}
				} else {
					$href = $home_main . str_replace( $mltlngg_old_language, $mltlngg_current_language, $_SERVER['REQUEST_URI'] );
				}
				wp_redirect( $href );
				exit();			
			} elseif ( empty( $mltlngg_old_language ) && count( $mltlngg_enabled_languages ) > 1 && false === strpos( $_SERVER['REQUEST_URI'], $mltlngg_current_language ) ) {
				if ( $mltlngg_permalink != '' ) {
					$href = str_replace( $home, ( $home . '/' . $mltlngg_current_language ), ( $home_main . $_SERVER['REQUEST_URI'] ) );
				} else {
					$href = $home_main . $_SERVER['REQUEST_URI'];
					if ( 'page' == get_option( 'show_on_front' ) && false === strpos( $_SERVER['REQUEST_URI'], '?' ) ) {
						$front_page_id = get_option( 'page_on_front' );
						$href .= ( false === strpos( $_SERVER['REQUEST_URI'], '?' ) ) ? '?page_id=' . $front_page_id : '&page_id=' . $front_page_id;
						$href .= '&lang=' . $mltlngg_current_language;
					} else {
						$href .= ( false === strpos( $_SERVER['REQUEST_URI'], '?' ) ) ? '?lang=' . $mltlngg_current_language : '&lang=' . $mltlngg_current_language;
					}
				}
				wp_redirect( $href );
				exit();
			} elseif ( $mltlngg_permalink == '' ) {	
				if ( 'page' == get_option( 'show_on_front' ) && false === strpos( str_replace( 'lang=' . $mltlngg_current_language, '', $_SERVER['REQUEST_URI'] ), '=' ) ) {
					$front_page_id = get_option( 'page_on_front' );
					$href = trim( $home_main . $_SERVER['REQUEST_URI'] , '/' );
					$href .= ( false === strpos( $_SERVER['REQUEST_URI'], '?' ) ) ? '?page_id=' . $front_page_id : '&page_id=' . $front_page_id;
					if ( false === strpos( $_SERVER['REQUEST_URI'], $mltlngg_current_language ) )
						$href .= '&lang=' . $mltlngg_current_language;
					wp_redirect( $href );
					exit();
				} 
			}
		} else {
			if ( ! empty( $mltlngg_old_language ) && ! empty( $mltlngg_current_language ) && $mltlngg_old_language != $mltlngg_current_language ) {
				if ( false === strpos( $_SERVER['REQUEST_URI'], $mltlngg_old_language ) ) {
					if ( $mltlngg_permalink != '' ) {
						$href = str_replace( $home, ( $home . '/' . $mltlngg_current_language ), ( $home . $_SERVER['REQUEST_URI'] ) );
					} else {
						$href = $home . $_SERVER['REQUEST_URI'];
						if ( 'page' == get_option( 'show_on_front' ) && false === strpos( $_SERVER['REQUEST_URI'], '?' ) ) {
							$front_page_id = get_option( 'page_on_front' );
							$href .= ( false === strpos( str_replace( 'lang=' . $mltlngg_old_language, '', $_SERVER['REQUEST_URI'] ), '=' ) ) ? '?page_id=' . $front_page_id : '&page_id=' . $front_page_id;
							$href .= '&lang=' . $mltlngg_current_language;
						} else {
							$href .= ( false === strpos( $_SERVER['REQUEST_URI'], '?' ) ) ? '?lang=' . $mltlngg_current_language : '&lang=' . $mltlngg_current_language;
						}
					}
				} else {
					$href = $home . mltlngg_str_replace_once( $mltlngg_old_language, $mltlngg_current_language, $_SERVER['REQUEST_URI'] );
				}
				wp_redirect( $href );
				exit();			
			} elseif ( empty( $mltlngg_old_language ) && count( $mltlngg_enabled_languages ) > 1 && false === strpos( $_SERVER['REQUEST_URI'], $mltlngg_current_language ) ) {
				if ( $mltlngg_permalink != '' ) {
					$href = str_replace( $home, ( $home . '/' . $mltlngg_current_language ), ( $home . $_SERVER['REQUEST_URI'] ) );
				} else {
					$href = $home . $_SERVER['REQUEST_URI'];
					if ( 'page' == get_option( 'show_on_front' ) && false === strpos( $_SERVER['REQUEST_URI'], '?' ) ) {
						$front_page_id = get_option( 'page_on_front' );
						$href .= ( false === strpos( $_SERVER['REQUEST_URI'], '?' ) ) ? '?page_id=' . $front_page_id : '&page_id=' . $front_page_id;
						$href .= '&lang=' . $mltlngg_current_language;
					} else {
						$href .= ( false === strpos( $_SERVER['REQUEST_URI'], '?' ) ) ? '?lang=' . $mltlngg_current_language : '&lang=' . $mltlngg_current_language;
					}
				}
				wp_redirect( $href );
				exit();
			} elseif ( $mltlngg_permalink == '' ) {
				if ( 'page' == get_option( 'show_on_front' ) && false === strpos( str_replace( 'lang=' . $mltlngg_current_language, '', $_SERVER['REQUEST_URI'] ), '=' ) ) {
					$front_page_id = get_option( 'page_on_front' );
					$href = $home . $_SERVER['REQUEST_URI'];
					$href .= ( false === strpos( $_SERVER['REQUEST_URI'], '?' ) ) ? '?page_id=' . $front_page_id : '&page_id=' . $front_page_id;
					if ( false === strpos( $_SERVER['REQUEST_URI'], $mltlngg_current_language ) )
						$href .= '&lang=' . $mltlngg_current_language;
					wp_redirect( $href );
					exit();
				} 
			}
		}
	}
}

/* to search for the first occurrence of slug language */
if ( ! function_exists( 'mltlngg_str_replace_once' ) ) {
	function mltlngg_str_replace_once( $needle , $replace , $haystack ) { 
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
		global $wp_rewrite;
		$mltlngg_options = get_option( 'mltlngg_options' );
		$mltlngg_enabled_languages_locale = array(); /* Array with codes of all enabled languages */
		foreach ( $mltlngg_options['list_of_languages'] as $mltlngg_one_language ) {
			if ( true === $mltlngg_one_language['enable'] ) {
				$mltlngg_enabled_languages_locale[] = $mltlngg_one_language['locale'];
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
			$is_content = strpos( $url, content_url() );
			$is_includes = strpos( $url, includes_url() );
			$mltlngg_permalink = get_option( 'permalink_structure' );

			if ( false == $is_content && false === $is_includes ) {
				if ( $mltlngg_permalink == '' ) {
					$url .= ( false === strpos( $url, '?' ) ) ? '?lang=' . $mltlngg_current_language : '&amp;lang=' . $mltlngg_current_language;
				} else {
					$homeurl = get_option( 'home' );
					$url = str_replace( $homeurl, $homeurl . '/' . $mltlngg_current_language, $url );
				}
			}
		}
		return $url;
	}
}

/* Load scripts and styles */
if ( ! function_exists( 'mltlngg_script_style' ) ) {
	function mltlngg_script_style() {
		wp_enqueue_style( 'mltlngg_stylesheet', plugins_url( 'css/style.css', __FILE__ ) );
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
		 * @param array $args	Widget arguments.
		 * @param array $instance Saved values from database.
		 */
		public function widget( $args, $instance ) {
			global $mltlngg_options;
			$mltlngg_language_switcher = ( isset( $instance['mltlngg_language_switcher'] ) ) ? $instance['mltlngg_language_switcher'] : $mltlngg_options['language_switcher'];
			$title = ( ! empty( $instance['title'] ) ) ? apply_filters( 'widget_title', $instance['title'] ) : '';
			echo $args['before_widget'];
			echo $args['before_title'];
			echo $title;
			echo $args['after_title'];
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
			global $wp_version, $mltlngg_options;
			$title = ( isset( $instance['title'] ) ) ? $instance['title'] : __( 'Localization', 'multilanguage' );
			$mltlngg_language_switcher = ( isset( $instance['mltlngg_language_switcher'] ) ) ? $instance['mltlngg_language_switcher'] : $mltlngg_options['language_switcher']; ?>
			<p>
				<label><?php _e( 'Title:' ); ?>
					<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
				</label>
			</p>
			<div style="clear: both;">
				<label style="float: left">
					<input id="drop-down-list" name="<?php echo $this->get_field_name( 'mltlngg_language_switcher' ); ?>" type="radio" value="drop-down-list" <?php echo ( ( $mltlngg_language_switcher == 'drop-down-list' ) ? 'checked' : '' ); ?>><?php _e( 'Drop-down languages list', 'multilanguage' ) ?>
				</label>
				<div class="bws_help_box<?php if ( $wp_version >= '3.9' ) echo ' dashicons dashicons-editor-help'; ?>">
					<div class="bws_hidden_help_text"><img title="" src="<?php echo plugins_url( 'images/tooltip_drop_down_list.png', __FILE__ ); ?>" alt=""></div>
				</div>
			</div>
			<div style="clear: both;">
				<label style="float: left">
					<input id="drop-down-icons" name="<?php echo $this->get_field_name( 'mltlngg_language_switcher' ); ?>" type="radio" value="drop-down-icons" <?php echo ( ( $mltlngg_language_switcher == 'drop-down-icons' ) ? 'checked' : '' ); ?>><?php _e( 'Drop-down flag icons', 'multilanguage' ) ?>
				</label>
				<div class="bws_help_box<?php if ( $wp_version >= '3.9' ) echo ' dashicons dashicons-editor-help'; ?>">
					<div class="bws_hidden_help_text"><img title="" src="<?php echo plugins_url( 'images/tooltip_drop_down_icons.png', __FILE__ ); ?>" alt=""></div>
				</div>
			</div>
			<div style="clear: both;">
				<label style="float: left">
					<input id="flags-icons" name="<?php echo $this->get_field_name( 'mltlngg_language_switcher' ); ?>" type="radio" value="flags-icons" <?php echo ( ( $mltlngg_language_switcher == 'flags-icons' ) ? 'checked' : '' ); ?>><?php _e( 'Flag icons', 'multilanguage' ) ?>
				</label>
				<div class="bws_help_box<?php if ( $wp_version >= '3.9' ) echo ' dashicons dashicons-editor-help'; ?>">
					<div class="bws_hidden_help_text"><img title="" src="<?php echo plugins_url( 'images/tooltip_flags_icons.png', __FILE__ ); ?>" alt=""></div>
				</div>
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
		global $mltlngg_current_language, $mltlngg_enabled_languages, $current_blog, $mltlngg_options;

		$switcher = '<div class="mltlngg_switcher"><noscript><p style="color:red;">' . __( 'Please enable JavaScript to use the option', 'multilanguage' ) . '</p></noscript>';

		if ( ! $mltlngg_language_switcher )
			$mltlngg_language_switcher = $mltlngg_options['language_switcher'];

		/* Language switcher style */
		switch ( $mltlngg_language_switcher ) {
			case 'drop-down-list':
				$switcher .= '<select style="background-image: url(' . plugins_url( 'images/flags/' , __FILE__ ) . $mltlngg_current_language . '.png ); background-repeat: no-repeat; background-position: left center; padding-left: 20px; display: block !important;" name="mltlngg_change_display_lang" onchange="document.location.href=this.options[this.selectedIndex].value;">';
					foreach ( $mltlngg_enabled_languages as $mltlngg_one_language ) {
						$mltlngg_selected = ( $mltlngg_one_language['locale'] == $mltlngg_current_language ) ? 'selected="selected"' : '';
						$mltlngg_option_display = ( $mltlngg_one_language['locale'] == $mltlngg_current_language && count( $mltlngg_enabled_languages ) > 1 ) ? ' display: none;' : '';
						$home = get_option( 'home' );
						$mltlngg_is_subfolder = mltlngg_is_subfolder(); 
						if ( ( is_multisite() && ! is_subdomain_install() ) || $mltlngg_is_subfolder ) {
							if ( ! $mltlngg_is_subfolder ) {
								if ( $current_blog ) {
									$home_main = ( ( isset( $_SERVER["HTTPS"] ) && $_SERVER["HTTPS"] == "on" ) ? "https://" : "http://" ) . $current_blog->domain;
									$home_main = esc_url( $home_main );
								}
							} else {
								$home_dir = str_replace( 'http://www.', '', $home );
								$home_dir = str_replace( 'https://www.', '', $home_dir );
								$home_dir = str_replace( 'http://', '', $home_dir );
								$home_dir = str_replace( 'https://', '', $home_dir );
								$server_name = $_SERVER['SERVER_NAME'];
								if ( 'www.' == substr( $server_name, 0, 4 ) )
									$server_name = substr( $server_name, 4 );
								$home_dir = str_replace( $server_name . ':' . $_SERVER['SERVER_PORT'], '', $home_dir );
								$home_dir = str_replace( $server_name, '', $home_dir );
								$home_dir = rtrim( $home_dir, '/ ' );
								$home_dir_count = strlen( $home_dir );
								$home_main = substr( $home, 0, - $home_dir_count );
							}
							$language_link = $home_main . mltlngg_str_replace_once( $mltlngg_current_language, $mltlngg_one_language['locale'], $_SERVER['REQUEST_URI'] );
						} else
							$language_link = $home . mltlngg_str_replace_once( $mltlngg_current_language, $mltlngg_one_language['locale'], $_SERVER['REQUEST_URI'] );
						$switcher .= '<option ' . $mltlngg_selected . ' value="' . $language_link . '" style="background-image: url(' . plugins_url( 'images/flags/' , __FILE__ ) . $mltlngg_one_language['locale'] . '.png); background-repeat: no-repeat; background-position: left center; padding-left: 20px;' . $mltlngg_option_display . '">' . $mltlngg_one_language['name'] . '</option>';
					}
				$switcher .= '</select>';
				break;
			case 'drop-down-icons':
				$switcher .= '<form name="mltlngg_change_language" method="post" action="">
					<ul class="mltlngg-lang-switch">
						<li>
							<img src="' . plugins_url( 'images/flags/' , __FILE__ ) . $mltlngg_current_language . '.png">
							<ul>';
								foreach ( $mltlngg_enabled_languages as $mltlngg_one_lang ) {
									if ( $mltlngg_one_lang['locale'] != $mltlngg_current_language ) {
										$switcher .= '<li>
											<button class="mltlngg-lang-button" name="mltlngg_change_display_lang" value="' . $mltlngg_one_lang['locale'] . '" title="' . $mltlngg_one_lang['name'] . '">
												<img class="mltlngg-lang" src="' . plugins_url( 'images/flags/' , __FILE__ ) . $mltlngg_one_lang['locale'] . '.png" alt="' . $mltlngg_one_lang['name'] . '">
											</button>
										</li>';
									}
								}
							$switcher .= '</ul>
						</li>
					</ul>
				</form>';
				break;
			case 'flags-icons':
				$switcher .= '<form name="mltlngg_change_language" method="post" action="">';
					foreach ( $mltlngg_enabled_languages as $mltlngg_one_lang ) {
						$switcher .= '<button class="mltlngg-lang-button-icons" name="mltlngg_change_display_lang" value="' . $mltlngg_one_lang['locale'] . '" title="' . $mltlngg_one_lang['name'] . '">
							<img class="';
							$switcher .= ( $mltlngg_one_lang['locale'] == $mltlngg_current_language ) ? 'mltlngg-current-lang' : 'mltlngg-lang';
							$switcher .= '" src="' . plugins_url( 'images/flags/' , __FILE__ ) . $mltlngg_one_lang['locale'] . '.png" alt="' . $mltlngg_one_lang['name'] . '">
						</button>';
					}
				$switcher .= '</form>';
				break;
		}
		$switcher .= '</div>';
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
		global $mltlngg_options, $mltlngg_message_value, $mltlngg_go_pro_result, $mltlngg_default_options;
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		/* Change settings options */
		if ( isset( $_POST['mltlngg_settings_form_was_send'] ) && check_admin_referer( 'mltlngg_settings_form', 'mltlngg_settings_form_field' ) ) {
			if ( isset( $_POST['mltlngg_settings_form_submit'] ) ) {
				$mltlngg_options['enabled_new_language']	= ( ! isset( $_POST['mltlngg_new_language_enable'] ) ) ? false : true;
				$mltlngg_options['autosave_editor_content'] = ( ! isset( $_POST['mltlngg_autosave_editor_content'] ) ) ? false : true;
				$mltlngg_options['wp_localization']			= ( ! isset( $_POST['mltlngg_wp_localization'] ) ) ? false : true;
				$mltlngg_options['language_switcher']		= ( isset( $_POST['mltlngg_language_switcher'] ) ) ? $_POST['mltlngg_language_switcher'] : 'drop-down-list';
				$mltlngg_options['search']					= $_POST['mltlngg_search'];
				update_option( 'mltlngg_options', $mltlngg_options );
				
				$mltlngg_message_value['success'] = __( 'Settings saved', 'multilanguage' );
			}
		}
		/* Adding language */
		if ( isset( $_POST['mltlngg_add_new_language_form_was_send'] ) && ! empty( $_POST['mltlngg_lang_list'] ) && check_admin_referer( 'mltlngg_add_new_language_form', 'mltlngg_add_new_language_field' ) ) {
			if ( preg_match( '/^([a-z]{2,3}|[a-z]{2}[_][A-Z]{2})-(.+?)$/u', $_POST['mltlngg_lang_list'], $matches ) ) { /* If language data is correct */
				mltlngg_add_language( $matches[1], $matches[2] ); /* Add new language (locale, name) */
				$mltlngg_message_value['success'] = __( 'Language added', 'multilanguage' );
			} else { /* If language data is incorrect */
				$mltlngg_message_value['error'] = __( 'Incorrect language data', 'multilanguage' );
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
				$mltlngg_message_value['success'] = __( 'Settings saved', 'multilanguage' );
		
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

		if ( isset( $_REQUEST['bws_restore_confirm'] ) && check_admin_referer( plugin_basename( __FILE__ ), 'bws_settings_nonce_name' ) ) {
			$mltlngg_options = $mltlngg_default_options;
			update_option( 'mltlngg_options', $mltlngg_options );
			$mltlngg_message_value['success'] =  __( 'All plugin settings were restored.', 'multilanguage' );
		}

		/* GO PRO */
		if ( isset( $_GET['action'] ) && 'go_pro' == $_GET['action'] ) {
			$mltlngg_go_pro_result = bws_go_pro_tab_check( plugin_basename( __FILE__ ) );
			if ( ! empty( $mltlngg_go_pro_result['error'] ) )
				$mltlngg_message_value['error'] = $mltlngg_go_pro_result['error'];
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
		global $mltlngg_message_value, $mltlngg_plugin_info, $mltlngg_go_pro_result; ?>
		<div class="wrap" id="mltlngg-settings">
			<div class="icon32 icon32-bws" id="icon-options-general"></div>
			<h2><?php _e( 'Multilanguage Settings', 'multilanguage' ); ?>
				<a class="add-new-h2" href="#" id="mltlngg-add-lang-link"><?php _e( 'Add&nbsp;language', 'multilanguage' ); ?></a>
			</h2>
			<!-- Display language tab on setting page -->
			<h2 class="nav-tab-wrapper">				
				<a class="nav-tab<?php if ( ! isset( $_GET['action'] ) || isset( $_GET['action'] ) && 'go_pro' != $_GET['action'] ) echo ' nav-tab-active'; ?>" href="<?php echo admin_url( 'admin.php?page=mltlngg_settings', '' ); ?>"><?php _e( 'Languages', 'multilanguage' ); ?></a>
				<a class="nav-tab" href="<?php echo admin_url( 'admin.php?page=mltlngg_settings&tab=settings', '' ); ?>"><?php _e( 'Settings', 'multilanguage' ); ?></a>
				<a class="nav-tab" href="http://bestwebsoft.com/products/multilanguage/faq/" target="_blank"><?php _e( 'FAQ', 'multilanguage' ); ?></a>
				<a class="nav-tab bws_go_pro_tab<?php if ( isset( $_GET['action'] ) && 'go_pro' == $_GET['action'] ) echo ' nav-tab-active'; ?>" href="admin.php?page=mltlngg_settings&amp;action=go_pro"><?php _e( 'Go PRO', 'multilanguage' ); ?></a>
			</h2><!-- .nav-tab-wrapper -->
			<!-- /end language tab on setting page -->
			<?php if ( isset( $_POST['mltlngg_language_form_was_send'] ) || isset( $_POST['mltlngg_add_new_language_form_was_send'] ) || isset( $_GET['action'] ) ) {
				if ( ! empty( $mltlngg_message_value['error'] ) ) { ?>
					<div class="error below-h2"><p><?php echo $mltlngg_message_value['error']; ?></p></div>
				<?php } elseif ( ! empty( $mltlngg_message_value['success'] ) ) { ?>
					<div class="updated fade below-h2"><p><?php echo $mltlngg_message_value['success']; ?></p></div>
				<?php }
			}
			bws_show_settings_notice();
			/* Form for adding new language */
			if ( ! isset( $_GET['action'] ) || isset( $_GET['action'] ) && 'go_pro' != $_GET['action'] ) {
				mltlngg_add_language_form(); ?>
				<!-- /form for adding new language -->
				<form class="bws_form" name="mltlngg_current_languages_form" method="post" action="" id="mltlngg-current-languages-form">
					<!-- display table of languages, source - table.php -->
					<?php mltlngg_table();
					wp_nonce_field( 'mltlngg_current_languages_form', 'mltlngg_current_languages_field' ); ?>
					<!-- /table of languages -->
					<br>
					<input id="bws-submit-button" type="submit" name="mltlngg_language_form_submit" class="button-primary" value="<?php _e( 'Save changes', 'multilanguage' ); ?>">
					<input type="hidden" name="mltlngg_language_form_was_send" value="1">
				</form><!-- #mltlngg_current_languages_form -->
				<div><p>&nbsp;</p></div>
			<?php } elseif ( 'go_pro' == $_GET['action'] ) {
				bws_go_pro_tab( $mltlngg_plugin_info, plugin_basename( __FILE__ ), 'mltlngg_settings', 'mltlnggpr_settings', 'multilanguage-pro/multilanguage-pro.php', 'multilanguage', 'fa164f00821ed3a87e6f78cb3f5c277b', '143', isset( $mltlngg_go_pro_result['pro_plugin_is_activated'] ) );
			} 
			bws_plugin_reviews_block( $mltlngg_plugin_info['Name'], 'multilanguage' ); ?>
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
		global $mltlngg_message_value, $mltlngg_options, $mltlngg_plugin_info, $wp_version; ?>
		<div class="wrap">
			<div class="icon32 icon32-bws" id="icon-options-general"></div>
			<h2>
				<?php _e( 'Multilanguage Settings', 'multilanguage' ); ?>
				<a class="add-new-h2" href="#" id="mltlngg-add-lang-link"><?php _e( 'Add language', 'multilanguage' ); ?></a>
			</h2>
			<!-- Display settings tab on setting page -->
			<h2 class="nav-tab-wrapper">
				<a class="nav-tab" href="<?php echo admin_url( 'admin.php?page=mltlngg_settings', '' ); ?>"><?php _e( 'Languages', 'multilanguage' ); ?></a>
				<a class="nav-tab<?php if ( ! isset( $_GET['action'] ) || isset( $_GET['action'] ) && 'go_pro' != $_GET['action'] ) echo ' nav-tab-active'; ?>" href="<?php echo admin_url( 'admin.php?page=mltlngg_settings&tab=settings', '' ); ?>"><?php _e( 'Settings', 'multilanguage' ); ?></a>
				<a class="nav-tab" href="http://bestwebsoft.com/products/multilanguage/faq/" target="_blank"><?php _e( 'FAQ', 'multilanguage' ); ?></a>
				<a class="nav-tab bws_go_pro_tab<?php if ( isset( $_GET['action'] ) && 'go_pro' == $_GET['action'] ) echo ' nav-tab-active'; ?>" href="admin.php?page=mltlngg_settings&amp;action=go_pro"><?php _e( 'Go PRO', 'multilanguage' ); ?></a>
			</h2><!-- .nav-tab-wrapper -->
			<!-- /end settings tab on setting page -->
			<?php if ( isset( $_REQUEST['bws_restore_default'] ) && check_admin_referer( plugin_basename( __FILE__ ), 'bws_settings_nonce_name' ) ) {
				bws_form_restore_default_confirm( plugin_basename( __FILE__ ) );
			} else {
				if ( ! empty( $mltlngg_message_value['error'] ) ) { ?>
					<div class="error below-h2"><p><?php echo $mltlngg_message_value['error']; ?></p></div>
				<?php } elseif ( ! empty( $mltlngg_message_value['success'] ) ) { ?>
					<div class="updated fade below-h2"><p><?php echo $mltlngg_message_value['success']; ?></p></div>
				<?php } 
				bws_show_settings_notice(); ?>
				<p><?php _e( 'If you would like to display Language switcher with a widget, you need to add the widget "Multilanguage" in the Widgets tab.', 'multilanguage' ); ?></p>
				<p><?php _e( 'If you would like to add Language switcher to your website, just copy and paste this shortcode into your post or page', 'multilanguage' ); ?>: <code>[multilanguage_switcher]</code></p>
				<p><?php _e( 'Also, you can paste the following strings into the template source code', 'multilanguage' ); ?> <code>&#60;?php if ( function_exists( 'mltlngg_display_switcher' ) ) mltlngg_display_switcher(); ?&#62;</code>
				<!-- form for adding new language -->
				<?php mltlngg_add_language_form(); ?>
				<!-- /form for adding new language -->
				<!-- Table with options form -->
				<form class="bws_form" name="mltlngg_settings_form" method="post" action="" id="mltlngg-current-languages-form">
					<table class="form-table" style="max-width: 700px;">
						<tr valign="middle">
							<th scope="row"><?php _e( 'Enable new language', 'multilanguage' ); ?></th>
							<td>
								<input id="mltlngg_new_language_enable" name="mltlngg_new_language_enable" type="checkbox" value="true" <?php echo ( ( true == $mltlngg_options['enabled_new_language'] ) ? ' checked' : '' ); ?>> <span style="color: #888888;font-size: 10px;"><?php _e( "The newly added language will be enabled automatically", 'multilanguage' ); ?></span>
							</td>
						</tr>
						<tr valign="middle">
							<th scope="row"><?php _e( 'Autosave translation in the editor', 'multilanguage' ); ?></th>
							<td>
								<input name="mltlngg_autosave_editor_content" type="checkbox" value="true" <?php echo ( ( true == $mltlngg_options['autosave_editor_content'] ) ? ' checked' : '' ); ?>> <span style="color: #888888;font-size: 10px;"><?php _e( "When switching edit posts/pages translation tab, the changes made in the previous tab will be saved automatically (only when the Javascript is enabled)", 'multilanguage' ); ?></span>
							</td>
						</tr>
						<tr valign="middle">
							<th scope="row"><?php _e( 'Switch Wordpress localization', 'multilanguage' ); ?></th>
							<td><input name="mltlngg_wp_localization" type="checkbox" value="true" <?php echo ( ( true == $mltlngg_options['wp_localization'] ) ? ' checked' : '' ); ?>> <span style="color: #888888;font-size: 10px;"><?php _e( "When changing the language in the frontend, WordPress localization will also be changed (only in case additional WordPress language packs are installed)", 'multilanguage' ); ?></span></td>
						</tr>
						<tr valign="middle">
							<th scope="row"><?php _e( 'Language switcher', 'multilanguage' ); ?></th>
							<td>
								<fieldset>
									<div style="clear: both;">
										<label>
											<input name="mltlngg_language_switcher" type="radio" value="drop-down-list" <?php if ( $mltlngg_options['language_switcher'] == 'drop-down-list' ) echo 'checked'; ?> />
											<?php _e( 'Drop-down languages list', 'multilanguage' ); ?>
										</label>
										<div class="bws_help_box<?php if ( $wp_version >= '3.9' ) echo ' dashicons dashicons-editor-help'; ?> mltlngg_thumb_block">
											<div class="bws_hidden_help_text"><img title="" src="<?php echo plugins_url( 'images/tooltip_drop_down_list.png', __FILE__ ); ?>" alt="" /></div>
										</div>
									</div>
									<div>
										<label>
											<input name="mltlngg_language_switcher" type="radio" value="drop-down-icons" <?php if ( $mltlngg_options['language_switcher'] == 'drop-down-icons' ) echo 'checked'; ?> /> 
											<?php _e( 'Drop-down flag icons', 'multilanguage' ); ?>
										</label>
										<div class="bws_help_box<?php if ( $wp_version >= '3.9' ) echo ' dashicons dashicons-editor-help'; ?> mltlngg_thumb_block">
											<div class="bws_hidden_help_text"><img title="" src="<?php echo plugins_url( 'images/tooltip_drop_down_icons.png', __FILE__ ); ?>" alt="" /></div>
										</div>
									</div>
									<div>
										<label>
											<input name="mltlngg_language_switcher" type="radio" value="flags-icons" <?php if ( $mltlngg_options['language_switcher'] == 'flags-icons' ) echo 'checked'; ?> /> 
											<?php _e( 'Flag icons', 'multilanguage' ); ?>
										</label>
										<div class="bws_help_box<?php if ( $wp_version >= '3.9' ) echo ' dashicons dashicons-editor-help'; ?> mltlngg_thumb_block">
											<div class="bws_hidden_help_text"><img title="" src="<?php echo plugins_url( 'images/tooltip_flags_icons.png', __FILE__ ); ?>" alt="" /></div>
										</div>
									</div>
								</fieldset>
							</td>
						</tr>	
						<tr valign="middle">						
							<th scope="row"><?php _e( 'Default searching by', 'multilanguage' ); ?></th>
							<td>
								<fieldset>
									<label>
										<input type="radio" name="mltlngg_search" value="single" <?php if ( 'single' == $mltlngg_options['search'] ) echo ' checked'; ?> /> 
										<?php _e( 'selected language', 'multilanguage' ); ?>
									</label><br>
									<label>
										<input type="radio" name="mltlngg_search" value="all" <?php if ( 'all' == $mltlngg_options['search'] ) echo ' checked'; ?> /> 
										<?php _e( 'all available languages', 'multilanguage' ); ?>
									</label>
								</fieldset>
							</td>
						</tr>				
					</table><!-- .form-table -->
					<div class="bws_pro_version_bloc">
						<div class="bws_pro_version_table_bloc">	
							<div class="bws_table_bg"></div>											
							<table class="form-table bws_pro_version">
								<tr valign="middle">						
									<th scope="row"><?php _e( "Determining the locale using the user's IP", 'multilanguage' ); ?></th>
									<td>
										<input type="checkbox" disabled id="mltlngg_determining_locale" name="mltlngg_determining_locale" value="true" />
									</td>
								</tr>
								<tr valign="middle" class="mltlngg_display_add_block" >
									<th scope="row"><?php _e( 'Automatic country table update every', 'multilanguage' ); ?></th>	
									<td>
										<input type="number" disabled min="1" max="370" name="mltlngg_loading_country" value="<?php echo '30';?>" />
										<?php _e( 'day', 'multilanguage' ); ?>
										<div class="clear"></div>
										<div><span class="bws_info"><?php _e( 'on the developers site the data is updated on the first Tuesday of every month', 'multilanguage' ); ?> </span></div>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row" colspan="2">
										* <?php _e( 'If you upgrade to Pro version all your settings will be saved.', 'multilanguage' ); ?>
									</th>
								</tr>
							</table>	
						</div>
						<div class="bws_pro_version_tooltip">
							<div class="bws_info">
								<?php _e( 'Unlock premium options by upgrading to Pro version', 'multilanguage' ); ?>
							</div>
							<a class="bws_button" href="http://bestwebsoft.com/products/multilanguage/?k=fa164f00821ed3a87e6f78cb3f5c277b&pn=143&v=<?php echo $mltlngg_plugin_info["Version"]; ?>&wp_v=<?php echo $wp_version; ?>" target="_blank" title="Multilanguage Pro"><?php _e( 'Learn More', 'multilanguage' ); ?></a>
							<div class="clear"></div>
						</div>
					</div>				
					<p>
						<input id="bws-submit-button" type="submit" name="mltlngg_settings_form_submit" class="button-primary" value="<?php _e( 'Save changes', 'multilanguage' ); ?>">
						<input type="hidden" name="mltlngg_settings_form_was_send" value="1">
						<?php wp_nonce_field( 'mltlngg_settings_form', 'mltlngg_settings_form_field' ); ?>
					</p>
				</form><!-- name="mltlngg_settings_form" -->
				<!-- /table with options form -->
				<?php bws_form_restore_default_settings( plugin_basename( __FILE__ ) );
			}
			bws_plugin_reviews_block( $mltlngg_plugin_info['Name'], 'multilanguage' ); ?>
		</div><!-- .wrap -->
	<?php }
}

/* Adding to post/page editor tabs in enabled languages */
if ( ! function_exists( 'mltlngg_showup_language_tabs_in_editor' ) ) {
	function mltlngg_showup_language_tabs_in_editor() {
		global $wpdb, $post, $mltlngg_options, $mltlngg_language, $mltlngg_active_language, $mltlngg_current_language, $mltlngg_table_translate, $mltlngg_enabled_languages, $mltlngg_get_default_language, $mltlngg_enabled_languages_locale;
		$mltlngg_post_type = get_post_type( $post->ID );
		if ( $mltlngg_post_type == 'post' || $mltlngg_post_type == 'page' ) {
			$mltlngg_sql = $wpdb->prepare(
				"SELECT `post_content`, `post_title`, `post_excerpt`
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
				<a href="admin.php?page=mltlngg_settings"><?php _e( 'Add language', 'multilanguage' ); ?></a>
			</div> <!-- #get-lang-content .nav-tab-wrapper -->
			<h2>
				<?php _e( 'Edit for language', 'multilanguage' ); ?>:
				<span id="mltlngg-current-lang"><?php echo $mltlngg_active_language['name']; ?></span>
			</h2>
			<?php wp_nonce_field( 'mltlngg_translate_form', 'mltlngg_translate_form_field' ); ?>
			<!-- Hidden fields with original Title, Content & Active language -->
			<?php if ( is_admin() )
				$mltlngg_current_language = ( isset( $_GET['lang'] ) ) ? $_GET['lang'] : ( ( isset( $mltlngg_active_language['locale'] ) ) ? $mltlngg_active_language['locale'] : $mltlngg_options['default_language'] );
			if ( $mltlngg_get_default_language != $mltlngg_current_language ) {
				$excerpt = $wpdb->get_var( $wpdb->prepare( "SELECT `post_excerpt` FROM $mltlngg_table_translate WHERE `post_ID` = %d AND `language` = '%s'", $post->ID, $mltlngg_active_language['locale'] ) );
			} else
				$excerpt = $mltlngg_original_data['post_excerpt']; ?>
			<input id="title-<?php echo $mltlngg_options['default_language']; ?>" type="hidden" value="<?php echo $mltlngg_original_data['post_title']; ?>" name="title_<?php echo $mltlngg_options['default_language']; ?>">
			<textarea id="content-<?php echo $mltlngg_options['default_language']; ?>" style="display: none;" name="content_<?php echo $mltlngg_options['default_language']; ?>"><?php echo $mltlngg_original_data['post_content']; ?></textarea>
			<input id="excerpt-<?php echo $mltlngg_active_language['locale'] ?>" type="hidden" value="<?php echo $excerpt ?>" name="excerpt_<?php echo $mltlngg_active_language['locale'] ?>">
			<input id="mltlngg-active-language" type="hidden" value="<?php echo $mltlngg_active_language['locale']; ?>" name="mltlngg_active_language">
		<?php }
	}
}

/* Function for save post/page changes to translations tables */
if ( ! function_exists( 'mltlngg_save_post' ) ) {
	function mltlngg_save_post( $post_id ) {
		global $wpdb, $mltlngg_table_translate, $mltlngg_options;
		$mltlngg_post_type = get_post_type( $post_id );
		if ( $mltlngg_post_type == 'post' || $mltlngg_post_type == 'page' ) {
			if ( ( isset( $_POST['save'] ) || isset( $_POST['publish'] ) ) && check_admin_referer( 'mltlngg_translate_form', 'mltlngg_translate_form_field' ) ) {
				$_SESSION['current_language'] = $_POST['mltlngg_active_language'];
				$mltlngg_post_id = $_POST['post_ID'];
				/* If autosave option is disabled save all changes */
				if ( true != $mltlngg_options['autosave_editor_content'] ) {
					/* Formation of a new array with the translation data from all hidden fields */
					$mltlngg_translate_data = array();
					foreach ( $_POST as $key => $value ) {
						if ( preg_match( '/^(title|content|excerpt)[_]([a-z]{2,3}|[a-z]{2}[_][A-Z]{2})$/', $key, $matches ) ) { /* Search POST with Title or Content */
							$mltlngg_translate_data[ $matches[2] ]['lang'] = $matches[2]; /* Language code */
							$mltlngg_translate_data[ $matches[2] ][ $matches[1] ] = $value; /* Title or Content or Excerpt*/
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
							$excerpt = isset( $mltlngg_translate['excerpt'] ) ? $mltlngg_translate['excerpt'] : '';
							if ( isset( $mltlngg_result['post_content'] ) && isset( $mltlngg_result['post_title'] ) ) { /* If translation is exist in database, update translation */
								if ( $mltlngg_translate['content'] != $mltlngg_result['post_content'] || $mltlngg_translate['title'] != $mltlngg_result['post_title'] || $excerpt != $mltlngg_result['post_excerpt'] ) {
									$wpdb->update(
										$mltlngg_table_translate,
										array(
											'post_content'	=> wp_unslash( sanitize_post_field( 'post_content', $mltlngg_translate['content'], 0, 'db' ) ),
											'post_title'	=> wp_unslash( $mltlngg_translate['title'] ),
											'post_excerpt'	=> wp_unslash( $excerpt ),
										),
										array(
											'post_ID'	=> $mltlngg_post_id,
											'language'	=> $mltlngg_translate['lang']
										),
										array( '%s', '%s', '%s' ),
										array( '%d', '%s' )
									);
								}
							} elseif ( $mltlngg_translate['content'] != "" || $mltlngg_translate['title'] != "" ) { /* If translation is not exist in database, create translation */
								$wpdb->insert(
									$mltlngg_table_translate,
									array(
										'post_ID'		=> $mltlngg_post_id,
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
				$mltlngg_sql = $wpdb->prepare(
					"SELECT *
					 FROM $mltlngg_table_translate
					 WHERE `post_ID` = %d AND `language` = '%s'
					", $mltlngg_post_id, $_POST['mltlngg_active_language']
				);
				$mltlngg_result = $wpdb->get_row( $mltlngg_sql, 'ARRAY_A' ); /* Get translation data for current language from database */
				$excerpt = isset( $_POST['post_excerpt'] ) ? $_POST['post_excerpt'] : '';
				if ( isset( $mltlngg_result['post_content'] ) && isset( $mltlngg_result['post_title'] ) ) { /* If translation is exist in database, update translation */
					if ( $_POST['content'] != $mltlngg_result['post_content'] || $_POST['post_title'] != $mltlngg_result['post_title'] || $_POST['post_excerpt'] != $mltlngg_result['post_excerpt'] ) {
						$wpdb->update(
							$mltlngg_table_translate,
							array(
								'post_content'	=> wp_unslash( sanitize_post_field( 'post_content', $_POST['content'] , 0, 'db' ) ),
								'post_title'	=> wp_unslash( $_POST['post_title'] ),
								'post_excerpt'	=> wp_unslash( $excerpt ),
							),
							array(
								'post_ID'	=> $mltlngg_post_id,
								'language'	=> $_POST['mltlngg_active_language']
							),
							array( '%s', '%s', '%s' ),
							array( '%d', '%s' )
						);
					}
				} else { /* If translation is not exist in database, create translation */
					$wpdb->insert(
						$mltlngg_table_translate,
						array(
							'post_ID'		=> $mltlngg_post_id,
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
						'post_title'	=> wp_unslash( $_POST['title_' . $mltlngg_options['default_language'] ] ),
						'post_excerpt'	=> wp_unslash( $default_excerpt ),
						'post_content'	=> wp_unslash( sanitize_post_field( 'post_content', $_POST['content_' . $mltlngg_options['default_language'] ], 0, 'db' ) )
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
					$( '#excerpt' ).val( $( 'input#excerpt-' + getLangContentDiv.find( 'a.nav-tab-active' ).data( 'lang' ) ).val() );/* show excerpt loaded language */
					$( 'li[id^="category-"]' ).each( function( i,elem ) {
						var old_cat = $( elem ).find( 'label' ).text();
						old_cat = old_cat.trim();
						var old_key = $( elem ).find( 'input' ).text();
						$( elem ).append(
							$( '<input/>' ) /* Create hidden field with categories */
							.attr( 'type', 'hidden' )
							.attr( 'id', 'cat-' + getLangContentDiv.find( 'a.nav-tab-active' ).data( 'lang' ) )
							.attr( 'name', 'cat_' + getLangContentDiv.find( 'a.nav-tab-active' ).data( 'lang' ) )
							.val( old_cat )
						);	 
					});

					/* Function for click on another language tab to edit translation */
					getLangContentDiv.on( 'click', 'a.nav-tab', function() {
						var inputTitle = $( '#title' ), /* Find INPUT id=title */
							inputContent = $( '#content' ), /* Find INPUT id=content */
							inputExcerpt = $( '#excerpt' ),
							mltlnggPostId = $( '#post_ID' ).attr( 'value' ), /* Get current post ID */
							cat_id = [],/* Array for category */
							newLang = ( $( this ).data( 'lang' ) ), /* Get language code from current language tab */
							newLangName = ( $( this ).attr( 'value' ) ), /* Get language name from current language tab */
							inputTitleNewLang = $( 'input#title-' + newLang ), /* Find INPUT id=title-{new lang code} */
							oldLang = getLangContentDiv.find( 'a.nav-tab-active' ).data( 'lang' ), /* Get language code from previous language tab */
							inputTitleOldLang = $( 'input#title-' + oldLang ), /* Find INPUT id=title-{old lang code} */
							mltlnggOldTitle = inputTitle.val(), /* Get title from previous language tab */
							mltlnggOldExcerpt = $( '#excerpt' ).val(),
							mltlnggOldContent, data;
						$( 'input[id^="in-category-"]' ).each( function( i,elem ) { 
							cat_id[i] = $( elem ).val();
						});
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
						$( 'input#excerpt-' + oldLang ).val( mltlnggOldExcerpt );
						if ( inputTitleNewLang.length > 0 && $( '#after-save-' + newLang ).length > 0 ) { /* If hidden blocks is exist, get Title & Content from them */
							inputTitle.val( inputTitleNewLang.val() ); /* Set title to current language tab */
							/* Set content to current language tab */
							if ( inputContent.is( ":hidden" ) ) { /* If TinyMCE editor is active */
								tinymce.activeEditor.setContent( $( 'textarea#content-' + newLang ).val() ); /* Set content to TinyMCE */
							} else { /* If Text editor is active */
								inputContent.val( $( 'textarea#content-' + newLang ).val() ); /* Set content to Text editor */
							}
							inputExcerpt.val( $( 'input#excerpt-' + newLang ).val() );
							$( 'li[id^="category-"]' ).each(function( i, elem ) { 
								var html_object = $( elem ).find( 'label' );
								var old_cat = html_object.text();
								var content = html_object.html();
								if ( $( elem ).find( 'input[id^="cat-' + newLang +'"]' ).length > 0 ) {
									content = content.replace( old_cat, ' ' + $( elem ).find( 'input[id^="cat-' + newLang +'"]' ).val() )
									$( html_object ).html( content );
								}	
							} );
							/* If autoupdate translation is enabled, save changes */
							if ( 1 == autoSaveContent ) {
								data = {
									'action': 'mltlngg_ajax_callback',
									'old_lang': oldLang,
									'cat_id' : cat_id,
									'mltlngg_post_id': mltlnggPostId,
									'mltlngg_old_title': mltlnggOldTitle,
									'mltlngg_old_content': mltlnggOldContent,
									'mltlngg_old_excerpt': mltlnggOldExcerpt,
									'security': '<?php echo $ajax_nonce; ?>'
								};
								$.post( ajaxurl, data );
							}
						} else { /* If hidden blocks is not exist, get Title & Content from database, then create hidden blocks */
							data = {
								'action': 'mltlngg_ajax_callback',
								'get_data': 'get_data',
								'cat_id' : cat_id,
								'new_lang': newLang,
								'old_lang': oldLang,
								'mltlngg_post_id': mltlnggPostId,
								'mltlngg_old_title': mltlnggOldTitle,
								'mltlngg_old_content': mltlnggOldContent,
								'mltlngg_old_excerpt': mltlnggOldExcerpt,
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
										.val( mltlnggNew.post_content ),
									$( '<input/>' ) /* Create hidden field with Excerpt */
										.attr( 'type', 'hidden' )
										.attr( 'id', 'excerpt-' + newLang )
										.attr( 'name', 'excerpt_' + newLang )
										.val( mltlnggNew.post_excerpt )	
								);
								inputTitle.val( mltlnggNew.post_title ); /* Set title to current language tab */
								/* Set content to current language tab */
								if ( inputContent.is( ":hidden" ) ) { /* If TinyMCE editor is active */
									tinymce.activeEditor.setContent( mltlnggNew.post_content );
								} else { /* If Text editor is active */
									inputContent.val( mltlnggNew.post_content );
								}
								$( '#excerpt' ).val( mltlnggNew.post_excerpt );
								for( var key in mltlnggNew.cat_translate ) {
									var html_object = $( '#category-' + key + ' label' );
									var old_cat = html_object.text();
									var content = html_object.html();
									content = content.replace( old_cat, ' ' + mltlnggNew.cat_translate[key] )
									$( 'li#category-' + key + ' label' ).html( content );
									$( 'li#category-' + key + '' ).append(
										$( '<input/>' ) /* Create hidden field with Categories */
										.attr( 'type', 'hidden' )
										.attr( 'id', 'cat-' + newLang + "-" + key )
										.attr( 'name', 'cat_' + newLang + "_" + key )
										.val( mltlnggNew.cat_translate[key] )
									);
								}
								if ( $( '#after-save-' + newLang ).length == 0 ) {
									$( getLangContentDiv ).append(
										$( '<input/>' ) /* Create hidden field with mark on the save */
										.attr( 'type', 'hidden' )
										.attr( 'id', 'after-save-' + newLang )
										.val( newLang )
										);
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
		global $wpdb, $mltlngg_table_translate, $mltlngg_options, $mltlngg_terms_table_translate;
		check_ajax_referer( 'mltlngg-ajax-nonce', 'security' );
		$mltlngg_old_excerpt = isset( $_POST['mltlngg_old_excerpt'] ) ? $_POST['mltlngg_old_excerpt'] :'';
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
				if ( $_POST['mltlngg_old_content'] != $mltlngg_result_old['post_content'] || $_POST['mltlngg_old_title'] != $mltlngg_result_old['post_title'] || $mltlngg_old_excerpt != $mltlngg_result_old['post_excerpt'] ) {
					$wpdb->update(
						$mltlngg_table_translate,
						array(
							'post_content'	=> wp_unslash( sanitize_post_field( 'post_content', $_POST['mltlngg_old_content'] , 0, 'db' ) ),
							'post_title'	=> wp_unslash( $_POST['mltlngg_old_title'] ),
							'post_excerpt'	=> wp_unslash( $mltlngg_old_excerpt ),
						),
						array(
							'post_ID'	=> $mltlngg_id,
							'language'	=> $_POST['old_lang']
						),
						array( '%s', '%s', '%s' ),
						array( '%d', '%s' )
					);
				}
			} elseif ( "" != $_POST['mltlngg_old_content'] || "" != $_POST['mltlngg_old_title'] ) { /* If translation not exist in database, create translation */
				$wpdb->insert(
					$mltlngg_table_translate,
					array(
						'post_ID'		=> $mltlngg_id,
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
			
			$mltlngg_sql = $wpdb->prepare(
				"SELECT *
				 FROM $mltlngg_table_translate
				 WHERE `post_ID` = %d AND `language` = '%s'
				", $mltlngg_id, $_POST['new_lang']
			);
			$mltlngg_post_data = $wpdb->get_row( $mltlngg_sql, 'ARRAY_A' ); /* Get translation data for current language from database */
			if ( isset( $_POST['cat_id'] ) ) {
				$mltlngg_sql = $wpdb->prepare(
					"SELECT *
					 FROM $mltlngg_terms_table_translate
					 WHERE `language` = '%s'
					", $_POST['new_lang']
				);
				$mltlngg_cat_data = $wpdb->get_results( $mltlngg_sql, 'ARRAY_A' ); /* Get translation data for current language from database */
			}

			if ( ! empty( $mltlngg_cat_data ) ) {
				foreach ( $mltlngg_cat_data as $value ) {
					if ( in_array( $value['term_ID'], $_POST['cat_id'] ) )
						$mltlngg_new_cat_mame[ $value['term_ID'] ] = $value['name'];				
				}		
				if ( ! empty( $mltlngg_new_cat_mame ) )
					$mltlngg_new_cat_data = array( "cat_translate" => $mltlngg_new_cat_mame );
			}

			if ( isset( $mltlngg_post_data['post_content'] ) && isset( $mltlngg_post_data['post_title'] ) ) { /* If translation is exist, send translation to ajax */
				echo json_encode( array_merge( $mltlngg_post_data, $mltlngg_new_cat_data ) );
			} else { /* If translation is not exist, send empty translation to ajax */
				$mltlngg_post_data = array(
					'post_content'	=> "",
					'post_title'	=> ""
				);
				echo json_encode( array_merge( $mltlngg_post_data, $mltlngg_new_cat_data ) );
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
					array( '%d', '%s', '%s' )
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
		$local_name = get_locale();
		if ( isset( $_POST['action'] ) && 'inline-save-tax' == $_POST['action'] ) {
			$wpdb->update(
				$mltlngg_terms_table_translate,
				array(
					'name'	=> $_POST['name'],
				),
				array(
					'term_ID'	=> $_POST['tax_ID'],
					'language'	=> $local_name
				),
				array( '%s' ),
				array( '%d', '%s' )
			);
		}	
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
				$cat_name = ( $mltlngg_result['language'] == $local_name ) ? $_POST['name'] : $_POST[ $cat_lang ];
				if ( isset( $mltlngg_result['name'] ) && ( ( $_POST[ $cat_lang ] != $mltlngg_result['name'] ) || ( $mltlngg_result['language'] == $local_name ) ) ) { /* If translation is exist in database, update translation */
					$wpdb->update(
						$mltlngg_terms_table_translate,
						array(
							'name'	=> $cat_name,
						),
						array(
							'term_ID'	=> $tag_ID,
							'language'	=> $one_language['locale']
						),
						array( '%s' ),
						array( '%d', '%s' )
					);
				} elseif ( ! isset( $mltlngg_result['name'] ) && "" != $_POST[ $cat_lang ] ) { /* If translation is not exist in database, create translation */
					$wpdb->insert(
						$mltlngg_terms_table_translate,
						array(
							'term_ID'	=> $tag_ID,
							'name'		=> $cat_name,
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
		global $wpdb, $mltlngg_table_translate;
		$wpdb->query( $wpdb->prepare( "DELETE FROM $mltlngg_table_translate WHERE `post_ID` = %d", $postid ) );
	}
}

/* Function for delete terms translations */
if ( ! function_exists( 'mltlngg_delete_term' ) ) {
	function mltlngg_delete_term( $term ) {
		global $wpdb, $mltlngg_terms_table_translate;
		$wpdb->query( $wpdb->prepare( "DELETE FROM $mltlngg_terms_table_translate WHERE `term_ID` = %d", $term ) );
	}
}

/* Display post_title in the selected language */
if ( ! function_exists( 'mltlngg_the_title_filter' ) ) {
	function mltlngg_the_title_filter( $title, $id = null ) {
		global $mltlngg_options, $wpdb, $mltlngg_table_translate, $mltlngg_current_language, $mltlngg_active_language, $post;
		if ( ! empty( $id ) ) {
			if ( ! is_nav_menu_item( $id ) ) { /* Do not filter, if a navigation menu */
				$mltlngg_post_type = get_post_type( $id );
				/* If current post type enabled to translation */
				if ( $mltlngg_post_type == 'post' || $mltlngg_post_type == 'page' ) {
					if ( is_admin() )
						$mltlngg_current_language = ( ! empty( $_SESSION['current_language'] ) ) ? $_SESSION['current_language'] : ( ( isset( $_GET['lang'] ) ) ? $_GET['lang'] : ( ( ! isset( $_GET['message'] ) ) ? $mltlngg_options['default_language'] : $mltlngg_active_language['locale'] ) );

					$new_title = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $mltlngg_table_translate WHERE `post_ID` = %d AND `language` = '%s'", $id, $mltlngg_current_language ), ARRAY_A );
					/* If translation is exist and not empty, filter title */
					if ( isset( $new_title['post_title'] ) && "" != $new_title['post_title'] ) {
						$title = $new_title['post_title'];
						if ( ! is_admin() ) {
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
		global $hook_suffix, $post, $wpdb, $mltlngg_table_translate, $mltlngg_current_language, $mltlngg_active_language, $mltlngg_options, $mltlngg_wp_providers;
		if ( is_admin() && ! ( 'post.php' == $hook_suffix || 'post-new.php' == $hook_suffix ) )
			return $content;
		$mltlngg_post_type = get_post_type( $post->ID );
		/* If current post type enabled to translation */
		if ( $mltlngg_post_type == 'post' || $mltlngg_post_type == 'page' ) {
			if ( is_admin() )
				$mltlngg_current_language = ( isset( $_GET['lang'] ) ) ? $_GET['lang'] : ( ( isset( $mltlngg_active_language['locale'] ) ) ? $mltlngg_active_language['locale'] : $mltlngg_options['default_language'] );
			
			$new_content = $wpdb->get_var( $wpdb->prepare( "SELECT `post_content` FROM $mltlngg_table_translate WHERE `post_ID` = %d AND `language` = '%s' ", $post->ID, $mltlngg_current_language ) );
			if ( ! empty( $new_content ) ) {
				if ( ! is_admin() ) {
					if ( ! post_password_required() ) {
						$noteaser = ( ( false !== strpos( $new_content, '<!--noteaser-->' ) ) ? true : false );
						$extends = get_extended( $new_content );
						$extended = $extends['extended'];
						$new_content = $extends['main'];
						if ( ! empty( $mltlngg_wp_providers ) ) {
							foreach ( $mltlngg_wp_providers as $template ) {
								if ( false !== preg_match( $template, $extends['extended'] ) ) {
									$extended = preg_replace_callback( $template, 
										"mltlngg_videos_filter",
										$extended
									);
								}
								if ( false !== preg_match( $template, $extends['main'] ) ) {
									$new_content = preg_replace_callback( $template, 
										"mltlngg_videos_filter",
										$new_content
									);
								}
							}
						}				
						if ( ! is_single() && ! is_page() && ! is_search() ) {					
							$more_link_text = __( '(more&hellip;)' );
							$more_link = apply_filters( 'the_content_more_link', ' <a href="' . get_permalink() . "#more-{$post->ID}\" class=\"more-link\">$more_link_text</a>", $more_link_text );
							
							if ( '' != $extended )
								$new_content .= $more_link;
						} elseif ( is_page() ) {
							$new_content .= $extended;							
						} else {
							if ( $noteaser )
								$new_content = '';

							$new_content .= ( 0 != strlen( $new_content ) ) ? '<span id="more-' . $post->ID . '"></span>' . $extended : $extended;
						}

						if ( 0 != strlen( $new_content ) ) {
							return $new_content;
						}
					} else {
						$content = get_the_password_form();
					}
				/* If translation is exist and not empty, filter content */
				} else
					$content = $new_content;
			}
		}
		return $content;
	}
}

/* filter for video on content */
if ( ! function_exists( 'mltlngg_videos_filter' ) ) {
	function mltlngg_videos_filter( $matches ) {
		return "\n" . $matches[0] . "\n";
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

/* Fixed pagination for default permalinks */
if ( ! function_exists( 'mltlngg_paginate_url_translate' ) ) {
	function mltlngg_paginate_url_translate( $link ) {
		global $mltlngg_current_language;
		if ( strpos( $link, '?lang=' . $mltlngg_current_language . '/' ) )
			$link = str_replace( '?lang=' . $mltlngg_current_language . '/', '/', $link );
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
		global $wpdb, $post, $mltlngg_current_language, $mltlngg_get_default_language, $mltlngg_table_translate;
		if ( $mltlngg_get_default_language != $mltlngg_current_language ) {
			/* If current post type enabled to translation */
			$mltlngg_post_type = get_post_type( $post->ID );
			if ( $mltlngg_post_type == 'post' || $mltlngg_post_type == 'page' ) {	
				$excerpt_new = $wpdb->get_var( $wpdb->prepare( "SELECT `post_excerpt` FROM $mltlngg_table_translate WHERE `post_ID` = %d AND `language` = '%s'", $post->ID, $mltlngg_current_language ) );
				if ( ! empty( $excerpt_new ) ) {
					return $excerpt_new;
				}
			}
		}
		return $excerpt;
	}
}

if ( ! function_exists( 'mltlngg_search_join' ) ) {
	function mltlngg_search_join( $join ) {
		global $mltlngg_table_translate, $wpdb, $mltlngg_current_language, $mltlngg_options, $mltlngg_enabled_languages_locale;
		if ( ! is_admin() && is_main_query() && is_search() ) {
			if ( 'single' == $mltlngg_options['search'] )
				$join .= "LEFT JOIN (SELECT post_ID, post_content AS mltlngg_post_content, post_title AS mltlngg_post_title FROM $mltlngg_table_translate WHERE `language` = '$mltlngg_current_language' ) $mltlngg_table_translate ON " . $wpdb->posts . ".ID = " . $mltlngg_table_translate . ".post_ID";
			else {
				global $wpdb;
				$wpdb->query( "SET SESSION group_concat_max_len = 100000000;" );
				$join .= "LEFT JOIN (SELECT post_ID, GROUP_CONCAT( DISTINCT post_content ORDER BY post_content ASC SEPARATOR ', ') AS mltlngg_post_content, GROUP_CONCAT( DISTINCT post_title ORDER BY post_title ASC SEPARATOR ', ') AS mltlngg_post_title FROM $mltlngg_table_translate WHERE `language` IN ('" . implode( "','", $mltlngg_enabled_languages_locale ) . "') AND ( `post_content` != '' OR `post_title` != '' ) GROUP BY `post_ID` ) $mltlngg_table_translate ON " . $wpdb->posts . ".ID = " . $mltlngg_table_translate . ".post_ID";
			}
		}
		return $join;
	}
}

if ( ! function_exists( 'mltlngg_search_where' ) ) {
	function mltlngg_search_where( $where ) {
		global $wpdb, $mltlngg_options;
		if ( ! is_admin() && is_main_query() && is_search() ) {
			if ( 'single' == $mltlngg_options['search'] ) {
				$where = preg_replace(
				"/\($wpdb->posts.post_title\s+LIKE\s*(\'[^\']+\')\s*\)/",
				"(mltlngg_post_title LIKE $1)", $where );
				
				$where = preg_replace(
				"/\($wpdb->posts.post_content\s+LIKE\s*(\'[^\']+\')\s*\)/",
				"(mltlngg_post_content LIKE $1)", $where );
			} else {
				$where = preg_replace(
				"/\($wpdb->posts.post_title\s+LIKE\s*(\'[^\']+\')\s*\)/",
				"(post_title LIKE $1) OR (mltlngg_post_title LIKE $1) OR (mltlngg_post_content LIKE $1)", $where );
			}
		}
		return $where;
	}
}

/* add shortcode content  */
if ( ! function_exists( 'mltlngg_shortcode_button_content' ) ) {
	function mltlngg_shortcode_button_content( $content ) {
		global $wp_version, $post; ?>
		<div id="mltlngg" style="display:none;">			
			<input class="bws_default_shortcode" type="hidden" name="default" value="[multilanguage_switcher]" />
			<div class="clear"></div>
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
			if ( ! is_network_admin() )
				$links[]	= '<a href="admin.php?page=mltlngg_settings">' . __( 'Settings', 'multilanguage' ) . '</a>';
			$links[]	= '<a href="http://wordpress.org/plugins/multilanguage/faq/" target="_blank">' . __( 'FAQ', 'multilanguage' ) . '</a>';
			$links[]	= '<a href="http://support.bestwebsoft.com">' . __( 'Support', 'multilanguage' ) . '</a>';
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
			$blogids = $wpdb->get_col( "SELECT `blog_id` FROM $wpdb->blogs" );
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
		if ( ! function_exists( 'get_plugins' ) )
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );		
		$all_plugins = get_plugins();
		if ( ! array_key_exists( 'multilanguage-pro/multilanguage-pro.php', $all_plugins ) ) {
			$mltlngg_sql = "DROP TABLE `" . $mltlngg_table_translate . "`, `" . $mltlngg_terms_table_translate . "`;";
			$wpdb->query( $mltlngg_sql );

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
		}
		delete_option( 'mltlngg_options' );		
		flush_rewrite_rules();
	}
}

if ( ! function_exists ( 'mltlngg_plugin_banner' ) ) {
	function mltlngg_plugin_banner() {
		global $hook_suffix;	
		if ( 'plugins.php' == $hook_suffix ) {
			global $mltlngg_plugin_info, $mltlngg_options;
			if ( isset( $mltlngg_options['first_install'] ) && strtotime( '-1 week' ) > $mltlngg_options['first_install'] )
				bws_plugin_banner( $mltlngg_plugin_info, 'mltlngg', 'multilanguage', '0419dafcc237fe35489c8db63c899a38', '143', '//ps.w.org/multilanguage/assets/icon-128x128.png' );
			
			bws_plugin_banner_to_settings( $mltlngg_plugin_info, 'mltlngg_options', 'multilanguage', 'admin.php?page=mltlngg_settings' );
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
add_action( 'admin_enqueue_scripts', 'mltlngg_script_style' );
add_action( 'wp_enqueue_scripts', 'mltlngg_script_style' );
add_action( 'widgets_init', 'register_mltlngg_widget' );

add_shortcode( 'multilanguage_switcher', 'mltlngg_get_switcher_block' );

/* Links filters */
add_filter( 'home_url', 'mltlngg_get_url_translated' );

/* Actions for page/post editor */
add_action( 'wp_print_scripts', 'mltlngg_disable_autosave_editor_script', 100 );
add_action( 'edit_form_top', 'mltlngg_showup_language_tabs_in_editor' );	/* Add languages tabs to post editor */
add_filter( 'title_edit_pre', 'mltlngg_the_title_filter', 10, 2 );
if ( is_admin() )
	add_action( 'the_editor_content', 'mltlngg_the_content_filter' );
add_action( 'save_post', 'mltlngg_save_post' );	/* Saving changes in posts translations */
add_action( 'deleted_post', 'mltlngg_delete_post' );	/* Delete posts translations from database */

/* Filters for display frontend content language */
add_filter( 'the_title', 'mltlngg_the_title_filter', 10, 2 );
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

/* custom filter for bws button in tinyMCE */
add_filter( 'bws_shortcode_button_content', 'mltlngg_shortcode_button_content' );

/* Additional links on the plugin page */
add_filter( 'plugin_action_links', 'mltlngg_plugin_action_links', 10, 2 );
add_filter( 'plugin_row_meta', 'mltlngg_register_plugin_links', 10, 2 );

/* Add AJAX function */
add_action( 'admin_footer', 'mltlngg_ajax_languages_tab' );
add_action( 'wp_ajax_mltlngg_ajax_callback', 'mltlngg_ajax_callback' );

add_action( 'admin_notices', 'mltlngg_plugin_banner' );
register_deactivation_hook( __FILE__, 'mltlngg_deactivation' );
register_uninstall_hook( __FILE__, 'mltlngg_delete_options' );