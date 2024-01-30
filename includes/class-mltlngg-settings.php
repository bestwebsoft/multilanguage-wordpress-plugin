<?php
/**
 * Displays the content on the plugin settings page
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Mltlngg_Settings_Tabs' ) ) {
	class Mltlngg_Settings_Tabs extends Bws_Settings_Tabs {
		/**
		 * Name for language switcher
		 *
		 * @var string
		 */
		private $language_switcher_name;
		/**
		 * Constructor.
		 *
		 * @access public
		 *
		 * @see Bws_Settings_Tabs::__construct() for more information on default arguments.
		 *
		 * @param string $plugin_basename Plugin basename.
		 */
		public function __construct( $plugin_basename ) {
			global $mltlngg_options, $mltlngg_plugin_info;

			$tabs = array(
				'settings'    => array( 'label' => esc_html__( 'Settings', 'multilanguage' ) ),
				'misc'        => array( 'label' => esc_html__( 'Misc', 'multilanguage' ) ),
				'custom_code' => array( 'label' => esc_html__( 'Custom Code', 'multilanguage' ) ),
				'license'     => array( 'label' => esc_html__( 'License Key', 'multilanguage' ) ),
			);

			parent::__construct(
				array(
					'plugin_basename'    => $plugin_basename,
					'plugins_info'       => $mltlngg_plugin_info,
					'prefix'             => 'mltlngg',
					'default_options'    => mltlngg_get_options_default(),
					'options'            => $mltlngg_options,
					'is_network_options' => is_network_admin(),
					'tabs'               => $tabs,
					'doc_link'           => 'https://bestwebsoft.com/documentation/multilanguage/multilanguage-user-guide/',
					'wp_slug'            => 'multilanguage',
					'link_key'           => 'fa164f00821ed3a87e6f78cb3f5c277b',
					'link_pn'            => '143',
				)
			);

			$this->language_switcher_name = array(
				'drop-down-list'   => esc_html__( 'Drop-down list (flag + title)', 'multilanguage' ),
				'drop-down-titles' => esc_html__( 'Drop-down list (title)', 'multilanguage' ),
				'drop-down-icons'  => esc_html__( 'Drop-down list (flag)', 'multilanguage' ),
				'flags-icons'      => esc_html__( 'Flag', 'multilanguage' ),
				'gt'               => esc_html__( 'Google Auto Translate (drop-down only)', 'multilanguage' ),
				'gt-horizontal'    => esc_html__( 'Google Auto Translate (horizontal)', 'multilanguage' ),
				'gt-vertical'      => esc_html__( 'Google Auto Translate (vertical)', 'multilanguage' ),
			);

			add_filter( get_parent_class( $this ) . '_additional_restore_options', array( $this, 'additional_restore_options' ) );
			add_action( get_parent_class( $this ) . '_display_metabox', array( $this, 'display_metabox' ) );
			add_action( get_parent_class( $this ) . '_display_custom_messages', array( $this, 'display_custom_messages' ) );
			add_action( get_parent_class( $this ) . '_additional_misc_options_affected', array( $this, 'additional_misc_options_affected' ) );
		}

		/**
		 * Save plugin options to the database
		 *
		 * @access public
		 * @return array    The action results
		 */
		public function save_options() {
			global $wpdb;

			$message = '';
			$notice  = '';
			$error   = '';
		error_log( print_r( $_POST, true ) . PHP_EOL, 3, dirname( __FILE__ ) . '/error.log' );
			if ( isset( $_POST['mltlngg_nonce_admin'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mltlngg_nonce_admin'] ) ), plugin_basename( __FILE__ ) ) ) {

				$this->options['language_switcher'] = ( isset( $_POST['mltlngg_language_switcher'] ) &&
					array_key_exists( sanitize_text_field( wp_unslash( $_POST['mltlngg_language_switcher'] ) ), $this->language_switcher_name )
				) ? sanitize_text_field( wp_unslash( $_POST['mltlngg_language_switcher'] ) ) : 'drop-down-list';

				$this->options['hide_link_slug']           = isset( $_POST['mltlngg_hide_link_slug'] ) ? 0 : 1;
				$this->options['wp_localization']          = isset( $_POST['mltlngg_wp_localization'] ) ? 1 : 0;
				$this->options['google_auto_translate']    = isset( $_POST['mltlngg_google_auto_translate'] ) ? 1 : 0;
				$this->options['translate_open_graph']     = isset( $_POST['mltlngg_translate_open_graph'] ) ? 1 : 0;
				$this->options['display_alternative_link'] = isset( $_POST['mltlngg_display_alternative_link'] ) ? 1 : 0;
				$this->options['save_mode']                = isset( $_POST['mltlngg_save_mode'] ) && 'ajax' === sanitize_text_field( wp_unslash( $_POST['mltlngg_save_mode'] ) ) ? 'ajax' : 'manual';
				$this->options['search']                   = isset( $_POST['mltlngg_search'] ) && in_array( sanitize_text_field( wp_unslash( $_POST['mltlngg_search'] ) ), array( 'single', 'all' ), true ) ? sanitize_text_field( wp_unslash( $_POST['mltlngg_search'] ) ) : 'single';

				$message = esc_html__( 'Settings saved.', 'multilanguage' );

				update_option( 'mltlngg_options', $this->options );
			}

			return compact( 'message', 'notice', 'error' );
		}

		/**
		 * Display Settings tab
		 */
		public function tab_settings() { ?>
			<h3 class="bws_tab_label"><?php esc_html_e( 'Multilanguage Settings', 'multilanguage' ); ?></h3>
			<?php $this->help_phrase(); ?>
			<hr>
			<table class="form-table">
				<tr>
					<th><?php esc_html_e( 'Google Auto Translate', 'multilanguage' ); ?></th>
					<td>
						<input type="checkbox" name="mltlngg_google_auto_translate"  value="1" <?php checked( 1, $this->options['google_auto_translate'] ); ?> /> <span class="bws_info"><?php esc_html_e( 'Enable to activate webpages auto translate. When google auto translate is enabled, a Google language switcher is displayed on the page allowing visitors to translate the page content automatically.', 'multilanguage' ); ?></span>
					</td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Default Language Switcher Type (for shortcode only)', 'multilanguage' ); ?></th>
						<td>
							<select name="mltlngg_language_switcher">
								<?php foreach ( $this->language_switcher_name as $key => $value ) { ?>
										<option <?php selected( $this->options['language_switcher'], $key ); ?> value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $value ); ?></option>
								<?php } ?>
							</select>
						</td>
				</tr>
			</table>
			<?php if ( ! $this->hide_pro_tabs ) { ?>
				<div class="bws_pro_version_bloc">
					<div class="bws_pro_version_table_bloc">
						<button type="submit" name="bws_hide_premium_options" class="notice-dismiss bws_hide_premium_options" title="<?php esc_html_e( 'Close', 'multilanguage' ); ?>"></button>
						<div class="bws_table_bg"></div>
						<table class="form-table bws_pro_version">
							<tr>
								<th><?php esc_html_e( 'Switch Language Automatically', 'multilanguage' ); ?></th>
								<td>
									<input type="checkbox" <?php disabled( true ); ?> /> <span class="bws_info"><?php esc_html_e( 'Enable to identify user IP and switch website language automatically based on the location.', 'multilanguage' ); ?></span>
								</td>
							</tr>
							<tr>
								<th><?php esc_html_e( 'Slug Position in the URL', 'multilanguage' ); ?></th>
								<td>
									<fieldset class="mltlngg_slug_position">
										<?php
										global $mltlngg_current_language;
										$posts = get_posts(
											array(
												'post_type' => 'page',
												'posts_per_page' => 1,
											)
										);
										$url   = ( ! empty( $posts ) ) ? get_permalink( $posts[0] ) : home_url();
										$args  = array(
											'url'   => $url,
											'force' => 'display',
										);
										?>
										<label>
											<input type="radio" 
											<?php
											disabled( true );
											checked( false );
											?>
											 />
											<?php
											esc_html_e( 'Before', 'multilanguage' );
											printf(
												'&ensp;<code>%s</code>',
												esc_url( preg_replace( '~(://)~', '$0<b>en.</b>', $url ) )
											);
											?>
										</label>
										<br>
										<label>
											<input type="radio" 
											<?php
											disabled( true );
											checked( true );
											?>
											/>
											<?php
											esc_html_e( 'After', 'multilanguage' );
											printf(
												'&ensp;<code>%s</code>',
												esc_url( preg_replace( '~(?<=[/=])(' . $mltlngg_current_language . ')(?![\w\d-])~', '<b>en</b>', mltlngg_get_lang_link( $args ) ) )
											);
											?>
										</label>
									</fieldset>
								</td>
							</tr>
							<tr>
								<th><?php esc_html_e( 'Permalink Translation', 'multilanguage' ); ?></th>
								<td>
									<input type="checkbox" <?php disabled( true ); ?> /> <span class="bws_info"><?php esc_html_e( 'Enable to translate pages, posts, and custom posts slugs in different languages.', 'multilanguage' ); ?></span>
								</td>
							</tr>
						</table>
					</div>
					<?php $this->bws_pro_block_links(); ?>
				</div>
			<?php } ?>
			<table class="form-table">
				<tr>
					<th><?php esc_html_e( 'Default Language Slug', 'multilanguage' ); ?></th>
					<td>
						<input type="checkbox" name="mltlngg_hide_link_slug" value="1" <?php checked( 0, $this->options['hide_link_slug'] ); ?> /> <span class="bws_info"><?php esc_html_e( 'Enable to show default language slug in the URL.', 'multilanguage' ); ?></span>
					</td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'WordPress Language', 'multilanguage' ); ?></th>
					<td>
						<input name="mltlngg_wp_localization" type="checkbox" value="1" <?php checked( 1, $this->options['wp_localization'] ); ?>> <span class="bws_info"><?php esc_html_e( 'Enable to switch WordPress language automatically when the language is changed in the front-end. Installed WordPress language packs are required. To install a new language simply go to the Settings > General and choose it in the Site Language option.', 'multilanguage' ); ?></span>
					</td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Open Graph Markup', 'multilanguage' ); ?></th>
					<td>
						<input type="checkbox" name="mltlngg_translate_open_graph" value="1" <?php checked( $this->options['translate_open_graph'] ); ?> />
						<span class="bws_info">
							<?php
							printf(
								esc_html__( 'Enable to add the ability to translate %s.', 'multilanguage' ) . ' ',
								'<a href="http://ogp.me/" target="_blank">' . esc_html__( 'open graph meta tags', 'multilanguage' ) . '</a>'
							);
							printf(
								esc_html__( 'This option automatically adds metadata for each language in the %s section. Facebook and other social networks use this data when your pages are shared.', 'multilanguage' ),
								'&lt;head&gt;'
							);
							?>
						</span>
					</td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Hreflang Attribute', 'multilanguage' ); ?></th>
					<td>
						<input type="checkbox" name="mltlngg_display_alternative_link" value="1" <?php checked( 1, $this->options['display_alternative_link'] ); ?> /> <span class="bws_info"><?php printf( esc_html__( 'Enable to add hreflang attribute used by search engines and display the correct language or regional URL in search results. This option inserts automatically the respective link for each language within the %s section.', 'multilanguage' ), '&lt;head&gt;' ); ?></span>
					</td>
				</tr>
				<?php if ( ! defined( 'ET_BUILDER_THEME' ) && ! defined( 'WPB_VC_VERSION' ) ) { ?>
					<tr>
						<th><?php esc_html_e( 'Translation Saving Mode for Classic Editor', 'multilanguage' ); ?></th>
						<td>
							<fieldset>
								<label>
									<input name="mltlngg_save_mode" type="radio" value="ajax" <?php checked( 'ajax', $this->options['save_mode'] ); ?> />
									<?php esc_html_e( 'Auto', 'multilanguage' ); ?> (AJAX) <span class="bws_info"><?php esc_html_e( 'If this option is selected, there may be a problem with custom post types.', 'multilanguage' ); ?></span>
								</label>
								<br>
								<label>
									<input name="mltlngg_save_mode" type="radio" value="manual" <?php checked( 'manual', $this->options['save_mode'] ); ?> />
									<?php esc_html_e( 'Manual (Save Changes button)', 'multilanguage' ); ?> (<?php esc_html_e( 'Recommended', 'multilanguage' ); ?>)
								</label>
							</fieldset>
							<span class="bws_info"><?php esc_html_e( 'Enable Manual mode if you have some problems with translations saving using Auto mode.', 'multilanguage' ); ?></span>
						</td>
					</tr>
				<?php } ?>
				<tr>
					<th><?php esc_html_e( 'Default Search by', 'multilanguage' ); ?></th>
					<td>
						<fieldset>
							<label>
								<input type="radio" name="mltlngg_search" value="single" <?php checked( 'single', $this->options['search'] ); ?> /> <?php esc_html_e( 'Current language', 'multilanguage' ); ?>
							</label>
							<br>
							<label>
								<input type="radio" name="mltlngg_search" value="all" <?php checked( 'all', $this->options['search'] ); ?> /> <?php esc_html_e( 'All active languages', 'multilanguage' ); ?>
							</label>
						</fieldset>
					</td>
				</tr>
			</table><!-- .form-table -->
			<?php
			wp_nonce_field( plugin_basename( __FILE__ ), 'mltlngg_nonce_admin', true, true );
		}

		/**
		 * Display custom options on the 'misc' tab
		 *
		 * @access public
		 */
		public function additional_misc_options_affected() {
			do_action( 'mltlngg_settings_page_misc_action', $this->options );
			?>
			</table>
			<!-- pls -->
			<?php if ( ! $this->hide_pro_tabs ) { ?>
				<div class="bws_pro_version_bloc">
					<div class="bws_pro_version_table_bloc">
						<button type="submit" name="bws_hide_premium_options" class="notice-dismiss bws_hide_premium_options" title="<?php esc_html_e( 'Close', 'limit-attempts' ); ?>"></button>
						<div class="bws_table_bg"></div>
						<table class="form-table bws_pro_version">
							<tr>
								<th><?php esc_html_e( 'Import Database', 'multilanguage' ); ?></th>
								<td>
									<fieldset>
										<?php if ( $this->is_multisite && ! is_network_admin() ) { ?>
											<p class="update-nag" style="padding: 3px;font-size: 12px;"><?php printf( esc_html__( 'This option is common to all subdomains.%sWe recommend you do not change this option without the consent of the administrator of your network.', 'multilanguage' ), '<br/>' ); ?></p>
										<?php } ?>
										<p id="bwscntrtbl_message" class="bws_info_small"><?php printf( esc_html__( 'Please upload the files manually using the following link %s.', 'multilanguage' ), '<a href="https://dev.maxmind.com/geoip/geolite2-free-geolocation-data" target="_blank">http://geolite.maxmind.com</a>' ); ?>                                        	
										</p>
										<div>
											<input <?php disabled( true ); ?> type="file" name="bwscntrtbl_upload_file" />
											<br>
											<input type="submit" name="bwscntrtbl_button_import" <?php disabled( true ); ?> class="button button-secondary" value="<?php esc_html_e( 'Import Now', 'multilanguage' ); ?>" />
										</div>
										<div class="bws_info">
											<?php esc_html_e( 'Receive an information about each IP address, and to which country it belongs to.', 'multilanguage' ); ?>
										</div>
										<div class="bws_info">
											<?php echo esc_html__( 'Follow this instruction to download or update the country table', 'multilanguage' ) . '&nbsp;-&nbsp;<a href="https://bestwebsoft.com/documentation/admin-panel-issues/how-to-import-or-update-the-country-table/" target="_blank">' . esc_html__( 'How to import or update the Country Table', 'multilanguage' ) . '</a>'; ?>
										</div>
									</fieldset>
								</td>
							</tr>
						</table>
					</div>
					<?php $this->bws_pro_block_links(); ?>
				</div>
			<?php } ?>
			<!-- end pls -->
			<table class="form-table">
			<?php
		}

		/**
		 * Custom functions for "Restore plugin options to defaults"
		 *
		 * @access public
		 * @param array $default_options Default options.
		 */
		public function additional_restore_options( $default_options ) {
			$default_options['list_of_languages'] = $this->options['list_of_languages'];
			return $default_options;
		}

		/**
		 * Display custom metabox
		 *
		 * @access public
		 */
		public function display_metabox() {
			?>
			<div class="postbox">
				<h3 class="hndle">
					<?php esc_html_e( 'Language Switcher', 'multilanguage' ); ?>
				</h3>
				<div class="inside">
					<p><?php esc_html_e( 'Add "Multilanguage Switcher" to a widget.', 'multilanguage' ); ?> <a href="widgets.php"><?php esc_html_e( 'Navigate to Widgets', 'multilanguage' ); ?></a></p>
					<p><?php esc_html_e( 'Add "Multilanguage Switcher" to a menu.', 'multilanguage' ); ?> <a href="nav-menus.php"><?php esc_html_e( 'Navigate to Menus', 'multilanguage' ); ?></a></p>
					<?php esc_html_e( 'Add a language switcher to your posts, pages or custom post types by using the following shortcode:', 'multilanguage' ); ?>
					<?php bws_shortcode_output( '[multilanguage_switcher]' ); ?>
					<p><?php esc_html_e( 'Add a language switcher to PHP template files by using the following code', 'multilanguage' ); ?>:</p>
					<code>&#60;?php if ( function_exists( 'mltlngg_display_switcher' ) ) mltlngg_display_switcher(); ?&#62;</code>
				</div>
			</div>
			<?php
		}

		/**
		 * Display custom message
		 *
		 * @access public
		 */
		public function display_custom_messages() {
			?>
			<noscript>
				<div class="error below-h2">
					<p><strong><?php esc_html_e( 'Please enable JavaScript in your browser.', 'multilanguage' ); ?></strong></p>
				</div>
			</noscript>
			<?php
		}
	}
}
