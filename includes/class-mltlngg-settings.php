<?php
/**
 * Displays the content on the plugin settings page
 */

require_once( dirname( dirname( __FILE__ ) ) . '/bws_menu/class-bws-settings.php' );

if ( ! class_exists( 'Mltlngg_Settings_Tabs' ) ) {
	class Mltlngg_Settings_Tabs extends Bws_Settings_Tabs {
		/**
		 * Constructor.
		 *
		 * @access public
		 *
		 * @see Bws_Settings_Tabs::__construct() for more information on default arguments.
		 *
		 * @param string $plugin_basename
		 */
		public function __construct( $plugin_basename ) {
			global $mltlngg_options, $mltlngg_plugin_info;


			$tabs = array(
				'settings' 		=> array( 'label' => __( 'Settings', 'multilanguage' ) ),
				'misc' 			=> array( 'label' => __( 'Misc', 'multilanguage' ) ),
				'custom_code' 	=> array( 'label' => __( 'Custom Code', 'multilanguage' ) ),
				'license'		=> array( 'label' => __( 'License Key', 'multilanguage' ) )
			);

			parent::__construct( array(
				'plugin_basename' 	 => $plugin_basename,
				'plugins_info'		 => $mltlngg_plugin_info,
				'prefix' 			 => 'mltlngg',
				'default_options' 	 => mltlngg_get_options_default(),
				'options' 			 => $mltlngg_options,
				'is_network_options' => is_network_admin(),
				'tabs' 				 => $tabs,
				'doc_link' 			 => 'https://docs.google.com/document/d/1y_c25pWDedi4FghjWj7W2Qleb-JsC10fGFinw4hy8T0/',
				'wp_slug'			 => 'multilanguage',
				'pro_page' 			 => 'admin.php?page=mltlnggpr_settings',
				'bws_license_plugin' => 'multilanguage-pro/multilanguage-pro.php',
				'link_key' 			 => 'fa164f00821ed3a87e6f78cb3f5c277b',
				'link_pn' 			 => '143'
			) );

			add_filter( get_parent_class( $this ) . '_additional_restore_options', array( $this, 'additional_restore_options' ) );
			add_action( get_parent_class( $this ) . '_display_metabox', array( $this, 'display_metabox' ) );
			add_action( get_parent_class( $this ) . '_additional_misc_options_affected', array( $this, 'additional_misc_options_affected' ) );
		}

		/**
		 * Save plugin options to the database
		 * @access public
		 * @param  void
		 * @return array    The action results
		 */
		public function save_options() {
			global $wpdb;

			$this->options['save_mode']					= isset( $_POST['mltlngg_save_mode'] ) && 'ajax' == $_POST['mltlngg_save_mode'] ? 'ajax' : 'manual';
			$this->options['display_alternative_link']	= isset( $_POST['mltlngg_display_alternative_link'] ) ? 1 : 0;
			$this->options['hide_link_slug']			= isset( $_POST['mltlngg_hide_link_slug'] ) ? 0 : 1;
			$this->options['wp_localization']			= isset( $_POST['mltlngg_wp_localization'] ) ? 1 : 0;
			$this->options['language_switcher']			= isset( $_POST['mltlngg_language_switcher'] ) ? $_POST['mltlngg_language_switcher'] : 'drop-down-list';
			$this->options['search']					= $_POST['mltlngg_search'];

			$message = __( 'Settings saved.', 'multilanguage' );

			update_option( 'mltlngg_options', $this->options );

			return compact( 'message', 'notice', 'error' );
		}

		/**
		 *
		 */
		public function tab_settings() { ?>
			<h3 class="bws_tab_label"><?php _e( 'Multilanguage Settings', 'multilanguage' ); ?></h3>
			<?php $this->help_phrase(); ?>
			<hr>
			<table class="form-table">
				<tr>
					<th><?php _e( 'Language Switcher Type', 'multilanguage' ); ?></th>
					<td>
						<fieldset>
							<label>
								<input name="mltlngg_language_switcher" type="radio" value="drop-down-list" <?php checked( $this->options['language_switcher'], 'drop-down-list' ); ?> />
								<?php _e( 'Drop-down list', 'multilanguage' ); ?> (<?php _e( 'flag', 'multilanguage' ); ?> + <?php _e( 'title', 'multilanguage' ); ?>)
							</label>
							<br/>
							<label>
								<input name="mltlngg_language_switcher" type="radio" value="drop-down-titles" <?php checked( $this->options['language_switcher'], 'drop-down-titles' ); ?> />
								<?php _e( 'Drop-down list', 'multilanguage' ); ?> (<?php _e( 'title', 'multilanguage' ); ?>)
							</label>
							<br/>
							<label>
								<input name="mltlngg_language_switcher" type="radio" value="drop-down-icons" <?php checked( $this->options['language_switcher'], 'drop-down-icons' ); ?> />
								<?php _e( 'Drop-down list', 'multilanguage' ); ?> (<?php _e( 'flag', 'multilanguage' ); ?>)
							</label>
							<br/>
							<label>
								<input name="mltlngg_language_switcher" type="radio" value="flags-icons" <?php checked( $this->options['language_switcher'], 'flags-icons' ); ?> />
								<?php _e( 'Flag', 'multilanguage' ); ?>
							</label>
						</fieldset>
					</td>
				</tr>
			</table>
			<?php if ( ! $this->hide_pro_tabs ) { ?>
				<div class="bws_pro_version_bloc">
					<div class="bws_pro_version_table_bloc">
						<button type="submit" name="bws_hide_premium_options" class="notice-dismiss bws_hide_premium_options" title="<?php _e( 'Close', 'multilanguage' ); ?>"></button>
						<div class="bws_table_bg"></div>
						<table class="form-table bws_pro_version">
							<tr>
								<th><?php _e( 'Switch Language Automatically', 'multilanguage' ); ?></th>
								<td>
									<input type="checkbox" <?php disabled( true ); ?> /> <span class="bws_info"><?php _e( "Enable to determine user IP and switch website language automatically based on the location.", 'multilanguage' ); ?></span>
								</td>
							</tr>
							<tr>
								<th><?php _e( "Slug Position in the URL", 'multilanguage' ); ?></th>
								<td>
									<fieldset class="mltlngg_slug_position">
										<?php global $mltlngg_current_language;
										$posts = get_posts( array( 'post_type' => 'page', 'posts_per_page' => 1 ) );
										$url = get_permalink( $posts[0] );
										$args = array(
											'url'			=> $url,
											'force'			=> 'display'
										); ?>
										<label>
											<input type="radio" <?php disabled( true ); checked( false ); ?> />
											<?php _e( 'Before', 'multilanguage' );
											printf(
												"&ensp;<code>%s</code>",
												preg_replace( '~(://)~', '$0<b>en.</b>', $url )
											); ?>
										</label>
										<br>
										<label>
											<input type="radio" <?php disabled( true ); checked( true ); ?> />
											<?php _e( 'After', 'multilanguage' );
											printf(
												"&ensp;<code>%s</code>",
												preg_replace( '~(?<=[/=])(' . $mltlngg_current_language . ')(?![\w\d-])~', '<b>en</b>', mltlngg_get_lang_link( $args ) )
											); ?>
										</label>
									</fieldset>
								</td>
							</tr>
						</table>
					</div>
					<?php $this->bws_pro_block_links(); ?>
				</div>
			<?php } ?>
			<table class="form-table">
				<tr>
					<th><?php _e( "Default Language Slug", 'multilanguage' ); ?></th>
					<td>
						<input type="checkbox" name="mltlngg_hide_link_slug" value="1" <?php checked( 0, $this->options['hide_link_slug'] ); ?> /> <span class="bws_info"><?php _e( "Enable to show default language slug in the URL.", 'multilanguage' ); ?></span>
					</td>
				</tr>
				<tr>
					<th><?php _e( 'WordPress Language', 'multilanguage' ); ?></th>
					<td>
						<input name="mltlngg_wp_localization" type="checkbox" value="1" <?php checked( 1, $this->options['wp_localization'] ); ?>> <span class="bws_info"><?php _e( "Enable to switch WordPress language automatically when the language is changed in the front-end (installed WordPress language packs are required).", 'multilanguage' ); ?></span>
					</td>
				</tr>
				<tr>
					<th><?php _e( 'hreflang Attribute', 'multilanguage' ); ?></th>
					<td>
						<input type="checkbox" name="mltlngg_display_alternative_link" value="1" <?php checked( 1, $this->options['display_alternative_link'] ); ?> /> <span class="bws_info"><?php printf( __( 'Enable to add hreflang attribute used by search engines and display the correct language or regional URL in search results. This option inserts automatically the respective link for each language within the %s section.', 'multilanguage' ), '&lt;head&gt;' ); ?></span>
							</div>
						</div>
					</td>
				</tr>
				<?php if ( ! defined( 'ET_BUILDER_THEME' ) && ! defined( 'WPB_VC_VERSION' ) ) { ?>
					<tr>
						<th><?php _e( 'Translation Saving Mode', 'multilanguage' ); ?></th>
						<td>
							<fieldset>
								<label>
									<input name="mltlngg_save_mode" type="radio" value="ajax" <?php checked( 'ajax', $this->options['save_mode'] ); ?> />
									<?php _e( 'Auto', 'multilanguage' ); ?> (AJAX)
								</label>
								<br>
								<label>
									<input name="mltlngg_save_mode" type="radio" value="manual" <?php checked( 'manual', $this->options['save_mode'] ); ?> />
									<?php _e( 'Manual (Save Changes button)', 'multilanguage' ); ?>
								</label>
							</fieldset>
							<span class="bws_info"><?php _e( "Enable Manual mode if you have some problems with saving translations using Auto mode.", 'multilanguage' ); ?></span>
						</td>
					</tr>
				<?php } ?>
				<tr>
					<th><?php _e( 'Default Search by', 'multilanguage' ); ?></th>
					<td>
						<fieldset>
							<label>
								<input type="radio" name="mltlngg_search" value="single" <?php checked( 'single', $this->options['search'] ); ?> /> <?php _e( 'Current language', 'multilanguage' ); ?>
							</label>
							<br>
							<label>
								<input type="radio" name="mltlngg_search" value="all" <?php checked( 'all', $this->options['search'] ); ?> /> <?php _e( 'All active languages', 'multilanguage' ); ?>
							</label>
						</fieldset>
					</td>
				</tr>
			</table><!-- .form-table -->
		<?php }

		/**
		 * Display custom options on the 'misc' tab
		 * @access public
		 */
		public function additional_misc_options_affected() { ?>
			</table>
			<?php if ( ! $this->hide_pro_tabs ) { ?>
				<div class="bws_pro_version_bloc">
					<div class="bws_pro_version_table_bloc">
						<button type="submit" name="bws_hide_premium_options" class="notice-dismiss bws_hide_premium_options" title="<?php _e( 'Close', 'limit-attempts' ); ?>"></button>
						<div class="bws_table_bg"></div>
						<table class="form-table bws_pro_version">
							<tr>
								<th><?php _e( 'Update GeoIP Every', 'multilanguage' ); ?></th>
								<td>
									<fieldset>
										<input disabled="disabled" type="number" min="0" max="12" step="1" name="bwscntrtbl_loading_country" value="3" style="width: 50px;"/>&nbsp;<?php _e( 'months', 'multilanguage' ); ?>
										<p id="bwscntrtbl_message" class="bws_info_small"><?php _e( 'Country table is not loaded yet.', 'multilanguage' ); ?></p>
										<p>
											<input disabled="disabled" type="submit" name="bwscntrtbl_button_import" class="button button-secondary bwsplgns_need_disable" value="<?php _e( 'Update Now', 'multilanguage' ); ?>" />
										</p>
										<div class="bws_info">
											<?php printf( __( 'Automatically download lists with registered IP addresses all over the world (from %s) to the database. Receive an information about each IP address, and to which country it belongs to.', 'multilanguage' ), '<a href="https://www.maxmind.com/" target="_blank">GeoIP</a>' ); ?>&nbsp;<a href="https://www.maxmind.com/en/geoip2-services-and-databases" target="_blank"><?php _e( 'Learn More', 'multilanguage' ); ?></a>
										</div>
									</fieldset>
								</td>
							</tr>
						</table>
					</div>
					<?php $this->bws_pro_block_links(); ?>
				</div>
			<?php } ?>
			<table class="form-table">
		<?php }

		/**
		 * Custom functions for "Restore plugin options to defaults"
		 * @access public
		 */
		public function additional_restore_options( $default_options ) {
			$default_options['list_of_languages'] = $this->options['list_of_languages'];
			return $default_options;
		}

		/**
		 * Display custom metabox
		 * @access public
		 * @param  void
		 * @return array    The action results
		 */
		public function display_metabox() { ?>
			<div class="postbox">
				<h3 class="hndle">
					<?php _e( 'Language Switcher', 'multilanguage' ); ?>
				</h3>
				<div class="inside">
					<p><?php _e( 'Add "Multilanguage Switcher" to a widget.', 'multilanguage' ); ?> <a href="widgets.php"><?php _e( 'Navigate to Widgets', 'multilanguage' ); ?></a></p>
					<p><?php _e( 'Add "Multilanguage Switcher" to a menu.', 'multilanguage' ); ?> <a href="nav-menus.php"><?php _e( 'Navigate to Menus', 'multilanguage' ); ?></a></p>
					<?php _e( "Add a language switcher to your posts, pages or custom post types by using the following shortcode:", 'multilanguage' ); ?>
					<?php bws_shortcode_output( '[multilanguage_switcher]' ); ?>
					<p><?php _e( 'Add a language switcher to PHP template files by using the following code', 'multilanguage' ); ?>:</p>
					<code>&#60;?php if ( function_exists( 'mltlngg_display_switcher' ) ) mltlngg_display_switcher(); ?&#62;</code>
				</div>
			</div>
		<?php }
	}
}