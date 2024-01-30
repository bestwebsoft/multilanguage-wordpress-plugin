<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! file_exists( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' ) ) {
	return;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

if ( ! class_exists( 'Mltlngg_List_Table' ) ) {
	class Mltlngg_List_Table extends WP_List_Table {
		/**
		 * Conctructor
		 */
		public function __construct() {
			global $mltlngg_options;

			parent::__construct(
				array(
					'singular'  => esc_html__( 'language', 'multilanguage' ),   /* singular name of the listed records */
					'plural'    => esc_html__( 'languages', 'multilanguage' ),  /* plural name of the listed records */
					'ajax'      => true, /* does this table support ajax? */
				)
			);
		}

		/**
		 * Display all columns
		 *
		 * @param array  $item        Item array.
		 * @param string $column_name Column name.
		 * @return string Value.
		 */
		public function column_default( $item, $column_name ) {
			switch ( $column_name ) {
				case 'title':
				case 'locale':
				case 'flag':
				case 'priority':
					return $item[ $column_name ];
				default:
					return print_r( $item, true ); /* Show the whole array for troubleshooting purposes */
			}
		}

		/**
		 * Get all columns
		 */
		public function get_columns() {
			$columns = array(
				'cb'        => '<input type="checkbox" />',
				'title'     => esc_html__( 'Title', 'multilanguage' ),
				'locale'    => esc_html__( 'URL Slug', 'multilanguage' ),
				'flag'      => esc_html__( 'Flag', 'multilanguage' ),
				'priority'  => esc_html__( 'Order', 'multilanguage' ),
			);
			return $columns;
		}

		/**
		 * Function for column cb
		 *
		 * @param array $item Item info.
		 * @return string Column checkbox.
		 */
		public function column_cb( $item ) {
			global $mltlngg_options;
			return $item['locale'] === $mltlngg_options['default_language'] ? '' : sprintf( '<input type="checkbox" name="mltlngg_language[]" value="%s" />', esc_html( $item['locale'] ) );
		}

		/**
		 * Function for column title
		 *
		 * @param array $item Item info.
		 * @return string Column checkbox.
		 */
		public function column_title( $item ) {
			global $mltlngg_options;
			$actions = array();

			$name = $item['title'];

			if ( $item['bydefault'] ) {
				$name .= ' — <span class="post-state">' . esc_html__( 'Default', 'multilanguage' ) . '</span>';
			}

			$name = '<strong>' . $name . '</strong>';

			$actions['edit'] = '<a href="' . wp_nonce_url( '?page=multilanguage-languages.php&action=edit&mltlngg_language=' . $item['locale'], 'mltlngg-action' ) . '">' . esc_html__( 'Edit', 'multilanguage' ) . '</a>';

			if ( $item['locale'] === $mltlngg_options['default_language'] ) {
				return sprintf( '%1$s %2$s', $name, $this->row_actions( $actions ) );
			}

			if ( ! $item['is_enabled'] ) {
				$actions['enable'] = '<a href="' . wp_nonce_url( '?page=multilanguage-languages.php&action=enable&mltlngg_language=' . $item['locale'], 'mltlngg-action' ) . '">' . esc_html__( 'Enable', 'multilanguage' ) . '</a>';
			} else {
				$actions['disable'] = '<a href="' . wp_nonce_url( '?page=multilanguage-languages.php&action=disable&mltlngg_language=' . $item['locale'], 'mltlngg-action' ) . '">' . esc_html__( 'Disable', 'multilanguage' ) . '</a>';
			}
			$actions['delete'] = '<a href="' . wp_nonce_url( '?page=multilanguage-languages.php&action=delete&mltlngg_language=' . $item['locale'], 'mltlngg-action' ) . '">' . esc_html__( 'Delete', 'multilanguage' ) . '</a>';
			return sprintf( '%1$s %2$s', $name, $this->row_actions( $actions ) );
		}

		/**
		 * Function for bulk actions
		 */
		public function get_bulk_actions() {
			if ( ! isset( $_GET['status'] ) ) {
				$actions = array(
					'enable'    => esc_html__( 'Enable', 'multilanguage' ),
					'disable'   => esc_html__( 'Disable', 'multilanguage' ),
					'delete'    => esc_html__( 'Delete', 'multilanguage' ),
				);
			} elseif ( 'active' === $_GET['status'] ) {
				$actions = array(
					'disable'   => esc_html__( 'Disable', 'multilanguage' ),
					'delete'    => esc_html__( 'Delete', 'multilanguage' ),
				);
			} else {
				$actions = array(
					'enable'    => esc_html__( 'Enable', 'multilanguage' ),
					'delete'    => esc_html__( 'Delete', 'multilanguage' ),
				);
			}
			return $actions;
		}

		/**
		 * Function for return sortable columns
		 */
		public function get_sortable_columns() {
			$sortable_columns = array(
				'title'     => array( 'title', false ),
				'priority'  => array( 'priority', false ),
			);
			return $sortable_columns;
		}

		/**
		 * Function for display single row
		 *
		 * @param array $item Item info.
		 */
		public function single_row( $item ) {
			static $mltlngg_count_row = 1;
			$mltlngg_classes = '';
			if ( ( $mltlngg_count_row % 2 ) ) {
				$mltlngg_classes = 'alternate';
			}
			if ( ! $item['is_enabled'] ) {
				$mltlngg_classes .= ' mltlngg-disabled';
			}
			$row_class = ' class="' . $mltlngg_classes . '"';
			echo '<tr' . esc_html( $row_class ) . '>';
			$this->single_row_columns( $item );
			echo '</tr>';
			$mltlngg_count_row++;
		}

		/**
		 * Function for prepare items
		 */
		public function prepare_items() {
			global $mltlngg_options;
			$this->_column_headers = array(
				$this->get_columns(),
				array(),
				$this->get_sortable_columns(),
			);
			$action = $this->current_action();
			$per_page = $this->get_items_per_page( 'languages_per_page', 10 );
			$current_page = $this->get_pagenum();
			$total_items = count( $mltlngg_options['list_of_languages'] );
			$this->set_pagination_args(
				array(
					'total_items'   => $total_items,
					'per_page'      => $per_page,
				)
			);

			$return = array();

			foreach ( $mltlngg_options['list_of_languages'] as $language ) {
				if ( isset( $_GET['status'] ) ) {
					if ( 'active' === sanitize_text_field( wp_unslash( $_GET['status'] ) ) && ! $language['enable'] ) {
						continue;
					}
					if ( 'inactive' === sanitize_text_field( wp_unslash( $_GET['status'] ) ) && $language['enable'] ) {
						continue;
					}
				}

				$flag_src = ( empty( $language['flag'] ) ) ? plugins_url( 'images/flags/', dirname( __FILE__ ) ) . $language['locale'] . '.png' : $language['flag'];
				$flag = '<img src=' . $flag_src . ' alt=' . $language['name'] . ' title=' . $language['name'] . '>';

				$return[] = array(
					'title'         => $language['name'],
					'locale'        => $language['locale'],
					'flag'          => $flag,
					'is_enabled'    => $language['enable'],
					'priority'      => $language['priority'],
					'bydefault'     => ( $language['locale'] === $mltlngg_options['default_language'] ),
				);
			}
			if ( ! empty( $return ) ) {
				if ( isset( $_GET['orderby'] ) && isset( $_GET['order'] ) ) {
					$return = mltlngg_sort_data( $return );
				}
			}

			$this->items = array_slice( $return, ( ( $current_page - 1 ) * $per_page ), $per_page );
		}
	}
}

if ( ! function_exists( 'mltlngg_sort_data' ) ) {
	/**
	 * Function for sort data
	 *
	 * @param array $data_to_sort Data for sort.
	 */
	function mltlngg_sort_data( $data_to_sort ) {
		$order_by = ( ! empty( $_GET['orderby'] ) ) ? sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) : 'title';
		$order = ( ! empty( $_GET['order'] ) ) ? sanitize_text_field( wp_unslash( $_GET['order'] ) ) : 'asc';
		foreach ( $data_to_sort as $key => $row ) {
			$name[ $key ] = $row['title'];
			$priority[ $key ] = $row['priority'];
		}
		$name_lowercase = array_map( 'strtolower', $name );
		switch ( $order_by ) {
			case 'title':
				if ( 'asc' === $order ) {
					array_multisort( $name_lowercase, SORT_ASC, $data_to_sort );
				} else {
					array_multisort( $name_lowercase, SORT_DESC, $data_to_sort );
				}
				break;
			case 'priority':
				if ( 'asc' === $order ) {
					array_multisort( $priority, SORT_ASC, $name_lowercase, SORT_ASC, $data_to_sort );
				} else {
					array_multisort( $priority, SORT_DESC, $name_lowercase, SORT_ASC, $data_to_sort );
				}
				break;
		}
		return $data_to_sort;
	}
}

if ( ! function_exists( 'mltlngg_add_menu_items' ) ) {
	/**
	 * Add new item to menu
	 */
	function mltlngg_add_menu_items() {
		add_action( 'load-multilanguage_page_multilanguage-languages', 'mltlngg_add_options' );
	}
}

if ( ! function_exists( 'mltlngg_add_options' ) ) {
	/**
	 * Add new item to screen option
	 */
	function mltlngg_add_options() {
		global $mltlngg_list_table;
		$option = 'per_page';
		$args = array(
			'label'     => esc_html__( 'Languages', 'multilanguage' ),
			'default'   => 10,
			'option'    => 'languages_per_page',
		);
		add_screen_option( $option, $args );
		$mltlngg_list_table = new mltlngg_list_table();
	}
}

if ( ! function_exists( 'mltlngg_table_set_option' ) ) {
	/**
	 * Set value for new item
	 *
	 * @param string $status Status.
	 * @param string $option Options.
	 * @param string $value  Value.
	 * @return $value
	 */
	function mltlngg_table_set_option( $status, $option, $value ) {
		return $value;
	}
}

if ( ! function_exists( 'mltlngg_table' ) ) {
	/**
	 * Function to display table of links
	 */
	function mltlngg_table() {
		global $mltlngg_options, $wp_version, $mltlngg_plugin_info;

		$error   = '';
		$message = '';

		if ( isset( $_POST['bws_hide_premium_options'] ) ) {
			$hide_result = bws_hide_premium_options( $mltlngg_options );
			$mltlngg_options = $hide_result['options'];
		}

		/* Adding language */
		if (
			isset( $_POST['mltlngg_add_new_language_form_was_send'] ) &&
			! empty( $_POST['mltlngg_lang_list'] ) &&
			check_admin_referer( 'mltlngg_add_new_language_form', 'mltlngg_add_new_language_field' )
		) {
			if ( preg_match( '/^([a-z]{2,3}|[a-z]{2,3}[_][A-Z]{2,3})-(.+?)$/u', sanitize_text_field( wp_unslash( $_POST['mltlngg_lang_list'] ) ), $matches ) ) { /* If language data is correct */
				mltlngg_add_language( $matches[1], $matches[2] ); /* Add new language (locale, name) */
				$message = esc_html__( 'Language added', 'multilanguage' );
			} else { /* If language data is incorrect */
				$error = esc_html__( 'Incorrect language data.', 'multilanguage' );
			}
		}
		/* Actions for table of languages */
		if ( isset( $_POST['mltlngg_language'] ) && check_admin_referer( 'mltlngg_current_languages_form', 'mltlngg_current_languages_field' ) ) {
			if ( isset( $_POST['action'] ) && '-1' !== sanitize_text_field( wp_unslash( $_POST['action'] ) ) ) {
				$action     = sanitize_text_field( wp_unslash( $_POST['action'] ) );
				$language   = sanitize_text_field( wp_unslash( $_POST['mltlngg_language'] ) );
			} elseif ( isset( $_POST['action2'] ) && '-1' !== sanitize_text_field( wp_unslash( $_POST['action2'] ) ) ) {
				$action     = sanitize_text_field( wp_unslash( $_POST['action2'] ) );
				$language   = sanitize_text_field( wp_unslash( $_POST['mltlngg_language'] ) );
			}
		} elseif ( isset( $_GET['mltlngg_language'] ) && isset( $_GET['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'mltlngg-action' ) ) {
			$action     = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : '';
			$language   = sanitize_text_field( wp_unslash( $_GET['mltlngg_language'] ) );
		}

		if ( isset( $action ) ) {
			$action_result = in_array( $action, array( 'enable', 'disable', 'delete' ) ) ? mltlngg_actions( $action, $language ) : array();
			if ( isset( $action_result['error'] ) ) {
				$error .= $action_result['error'];
			}
			if ( isset( $action_result['success'] ) ) {
				$message .= $action_result['success'];
			}
		}

		if ( isset( $action ) && 'edit' === $action ) {
			foreach ( $mltlngg_options['list_of_languages'] as $language_key => $value ) {
				/* if language data have been finded */
				if ( array_search( $language, $value, true ) ) {
					if ( isset( $_POST['mltlngg_title'] ) ) {
						$mltlngg_options['list_of_languages'][ $language_key ]['name'] = trim( sanitize_text_field( wp_unslash( $_POST['mltlngg_title'] ) ) );
					}
					$language_data = $mltlngg_options['list_of_languages'][ $language_key ];
					break;
				}
			}

			/* Change language options */
			if ( isset( $_POST['mltlngg_edit_language_form'] ) && check_admin_referer( 'mltlngg_edit_language_form', 'mltlngg_edit_language_form' ) ) {

				/* change default language */
				if ( isset( $_POST['mltlngg_default_lang'] ) ) {
					$mltlngg_options['default_language'] = $language_data['locale'];
					$language_data['enable'] = true;
				}

				if ( isset( $_POST['mltlngg_flag_option'] ) && 'custom' !== sanitize_text_field( wp_unslash( $_POST['mltlngg_flag_option'] ) ) ) {
					unset( $language_data['flag'] );
				} elseif ( ! empty( $_FILES['mltlngg_upload_flag']['tmp_name'] ) ) {
					$upload_dir = wp_upload_dir();

					if ( ! $upload_dir['error'] ) {
						$custom_folder = $upload_dir['basedir'] . '/multilanguage';
						if ( ! is_dir( $custom_folder ) ) {
							wp_mkdir_p( $custom_folder, 0755 );
						}
					}
					$max_image_width    = 50;
					$max_image_height   = 50;
					$max_image_size     = 32 * 1024;
					$valid_types        = array( 'jpg', 'jpeg', 'png' );

					/* Checking is allowed download file given parameters */
					if ( is_uploaded_file( sanitize_text_field( $_FILES['mltlngg_upload_flag']['tmp_name'] ) ) ) {
						$filename   = sanitize_text_field( $_FILES['mltlngg_upload_flag']['tmp_name'] );
						$ext        = isset( $_FILES['mltlngg_upload_flag']['name'] ) ? substr( sanitize_text_field( wp_unslash( $_FILES['mltlngg_upload_flag']['name'] ) ), 1 + strrpos( sanitize_text_field( wp_unslash( $_FILES['mltlngg_upload_flag']['name'] ) ), '.' ) ) : '';
						if ( filesize( $filename ) > $max_image_size ) {
							$error  = sprintf( esc_html__( 'Error: File size > %s.', 'multilanguage' ), '32K' );
						} elseif ( ! in_array( strtolower( $ext ), $valid_types ) ) {
							$error  = esc_html__( 'Error: Invalid file type.', 'multilanguage' );
						} else {
							$size   = GetImageSize( $filename );
							if ( $size && $size[0] <= $max_image_width && $size[1] <= $max_image_height ) {
								/* Construction to rename downloading file */
								$new_ext    = wp_check_filetype( sanitize_text_field( wp_unslash( $_FILES['mltlngg_upload_flag']['name'] ) ) );
								$namefile   = $language_data['locale'] . '.' . $new_ext['ext'];
								$uploadfile = $custom_folder . '/' . $namefile;

								if ( move_uploaded_file( sanitize_text_field( $_FILES['mltlngg_upload_flag']['tmp_name'] ), $uploadfile ) ) {
									$language_data['flag'] = $upload_dir['baseurl'] . '/multilanguage/' . $namefile;
								} else {
									$error = esc_html__( 'Error: Failed to move file.', 'multilanguage' );
								}
							} else {
								$error = sprintf( esc_html__( 'Error: Image width and height should not exceed %s.', 'multilanguage' ), '50px' );
							}
						}
					} else {
						$error = esc_html__( 'Uploading Error: Check image properties.', 'multilanguage' );
					}
				}

				$language_data['priority'] = isset( $_POST['mltlngg_priority'] ) ? absint( $_POST['mltlngg_priority'] ) : 1;

				$mltlngg_options['list_of_languages'][ $language_key ] = $language_data;

				/* Sorting list of language by priority */
				foreach ( $mltlngg_options['list_of_languages'] as $key => $language_to_action ) {
					$mltlngg_priority[ $key ] = $language_to_action['priority'];
				}
				array_multisort( $mltlngg_priority, SORT_ASC, $mltlngg_options['list_of_languages'] );

				$mltlngg_options['flush_rewrite_rules'] = 1;

				update_option( 'mltlngg_options', $mltlngg_options );
				$message = esc_html__( 'Settings saved.', 'multilanguage' );
			}

			echo ( version_compare( $wp_version, '4.0', '<' ) ) ? '<h2>' : '<h1>';
			esc_html_e( 'Edit Language', 'multilanguage' );
			echo ( version_compare( $wp_version, '4.0', '<' ) ) ? '</h2>' : '</h1>';

			if ( ! empty( $error ) ) { ?>
				<div class="error below-h2"><p><?php echo wp_kses_post( $error ); ?></p></div>
			<?php } elseif ( ! empty( $message ) ) { ?>
				<div class="updated fade below-h2"><p><?php echo wp_kses_post( $message ); ?></p></div>
			<?php } ?>
			<form class="bws_form" method="post" action="" enctype="multipart/form-data">
				<table class="form-table">
					<tr>
						<th><?php esc_html_e( 'Title', 'multilanguage' ); ?></th>
						<td>
							<input type="text" name="mltlngg_title" value="<?php echo esc_attr( $language_data['name'] ); ?>"/>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Default', 'multilanguage' ); ?></th>
						<td>
							<input type="checkbox" name="mltlngg_default_lang" value="1" <?php checked( $language_data['locale'], $mltlngg_options['default_language'] ); ?> /> <span class="bws_info"><?php esc_html_e( 'Enable to use it as a default language.', 'multilanguage' ); ?></span>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Flag Image', 'multilanguage' ); ?></th>
						<td>
							<fieldset>
								<label><input name="mltlngg_flag_option" type="radio" value="standart" <?php checked( empty( $language_data['flag'] ) ); ?> class="bws_option_affect" data-affect-hide=".mltlngg_flag_option_custom" />
								&nbsp;<?php esc_html_e( 'Default', 'multilanguage' ); ?></label>
								<br>
								<label><input name="mltlngg_flag_option" type="radio" value="custom" <?php checked( ! empty( $language_data['flag'] ) ); ?> class="bws_option_affect" data-affect-show=".mltlngg_flag_option_custom" />
								&nbsp;<?php esc_html_e( 'Custom', 'multilanguage' ); ?></label>
							</fieldset>
						</td>
					</tr>
					<tr>
						<th></th>
						<td>
							<?php esc_html_e( 'Current flag', 'multilanguage' ); ?>:&nbsp;&nbsp;
							<img src="<?php echo esc_url( ( empty( $language_data['flag'] ) ) ? plugins_url( 'images/flags/', dirname( __FILE__ ) ) . $language_data['locale'] . '.png' : $language_data['flag'] ); ?>" />
						</td>
					</tr>
					<tr class="mltlngg_flag_option_custom">
						<th></th>
						<td>
							<input type="file" name="mltlngg_upload_flag">
							<div class="bws_info"><?php printf( esc_html__( 'Upload image of %1$s maximum dimensions in JPG, JPEG or PNG formats (maximum file size - %2$s).', 'multilanguage' ), '50×50px', '32kb' ); ?></div>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Order', 'multilanguage' ); ?></th>
						<td>
							<input type="number" min="1" max="200" name="mltlngg_priority" value="<?php echo esc_attr( $language_data['priority'] ); ?>" />
						</td>
					</tr>
				</table>
				<!-- pls -->
				<?php if ( ! bws_hide_premium_options_check( $mltlngg_options ) ) { ?>
					<div class="bws_pro_version_bloc">
						<div class="bws_pro_version_table_bloc">
							<button type="submit" name="bws_hide_premium_options" class="notice-dismiss bws_hide_premium_options" title="<?php esc_html_e( 'Close', 'multilanguage' ); ?>"></button>
							<div class="bws_table_bg"></div>
							<table class="form-table bws_pro_version">
								<tr>
									<th><?php esc_html_e( 'URL Slug', 'multilanguage' ); ?></th>
									<td>
										<fieldset>
											<?php
											$exploded_locale = explode( '_', $language_data['locale'] );
											if ( 1 === count( $exploded_locale ) ) {
												?>
												<label>
													<input disabled="disabled" type="radio" checked="checked" />
													&nbsp;<?php echo esc_html( $language_data['locale'] ); ?>
												</label>
											<?php } else { ?>
												<label>
													<input disabled="disabled" type="radio" />
													&nbsp;<?php echo esc_html( $exploded_locale[0] ); ?>
												</label>
												<br>
												<label>
													<input checked="checked" disabled="disabled" type="radio" />
													&nbsp;<?php echo esc_html( $language_data['locale'] ); ?>
												</label>
											<?php } ?>
										</fieldset>
									</td>
								</tr>
								<tr>
									<th><?php esc_html_e( 'Site Title', 'multilanguage' ); ?></th>
									<td>
										<textarea disabled name="mltlngg_blogname"></textarea>
									</td>
								</tr>
								<tr>
									<th><?php esc_html_e( 'Tagline', 'multilanguage' ); ?></th>
									<td>
										<textarea disabled name="mltlngg_blogdescription"></textarea>
									</td>
								</tr>
							</table>
						</div>
						<div class="bws_pro_version_tooltip">
							<a class="bws_button" href="<?php echo esc_url( $mltlngg_plugin_info['PluginURI'] ); ?>?k=fa164f00821ed3a87e6f78cb3f5c277b&amp;pn=143&amp;v=<?php echo esc_attr( $mltlngg_plugin_info['Version'] ); ?>&amp;wp_v=<?php echo esc_attr( $wp_version ); ?>" target="_blank" title="<?php echo esc_attr( $mltlngg_plugin_info['Name'] ); ?>"><?php esc_html_e( 'Upgrade to Pro', 'multilanguage' ); ?></a>
							<div class="clear"></div>
						</div>
					</div>
				<?php } ?>
				<!-- end pls -->
				<p class="submit">
					<input type="submit" name="mltlngg_edit_language_form" class="button-primary" value="<?php esc_html_e( 'Save changes', 'multilanguage' ); ?>">
					<?php wp_nonce_field( 'mltlngg_edit_language_form', 'mltlngg_edit_language_form' ); ?>
				</p>
			</form>
		<?php } else { ?>
			<h1>
				<?php esc_html_e( 'Languages', 'multilanguage' ); ?>
				<a class="page-title-action add-new-h2 hide-if-no-js" href="#" id="mltlngg-add-lang-link"><?php esc_html_e( 'Add New', 'multilanguage' ); ?></a>
			</h1>
			<?php
			/* Functionality for adding notice when user choose same order for two languages */
			$lang_order = array();
			foreach ( $mltlngg_options['list_of_languages'] as $array_value ) {
				$lang_order[] = $array_value['priority'];
			}
			if ( count( array_unique( $lang_order ) ) !== count( $lang_order ) ) {
				?>
				<div class="notice notice-warning below-h2"><p><?php esc_html_e( 'You have the same order for two or more languages.', 'multilanguage' ); ?></p></div>
				<?php
			}
			if ( ! empty( $error ) ) {
				?>
				<div class="error below-h2"><p><?php echo wp_kses_post( $error ); ?></p></div>
			<?php } elseif ( ! empty( $message ) ) { ?>
				<div class="updated fade below-h2"><p><?php echo wp_kses_post( $message ); ?></p></div>
				<?php
			}
			/* Form for adding new language */
			mltlngg_add_language_form();

			$count_active_languages   = 0;
			$count_inactive_languages = 0;
			foreach ( $mltlngg_options['list_of_languages'] as $language ) {
				if ( $language['enable'] ) {
					$count_active_languages++;
				} else {
					$count_inactive_languages++;
				}
			}
			?>
			<ul class="subsubsub">
				<li class="all"><a href="admin.php?page=multilanguage-languages.php" 
				<?php
				if ( ! isset( $_GET['status'] ) ) {
					echo 'class="current"';
				}
				?>
				><?php esc_html_e( 'All', 'multilanguage' ); ?> <span class="count">(<?php echo esc_html( $count_active_languages + $count_inactive_languages ); ?>)</span></a> |</li>
				<li class="active"><a href="admin.php?page=multilanguage-languages.php&status=active" 
				<?php
				if ( isset( $_GET['status'] ) && 'active' === sanitize_text_field( wp_unslash( $_GET['status'] ) ) ) {
					echo 'class="current"';
				}
				?>
				><?php esc_html_e( 'Active', 'multilanguage' ); ?> <span class="count">(<?php echo esc_html( $count_active_languages ); ?>)</span></a> |</li>
				<li class="inactive"><a href="admin.php?page=multilanguage-languages.php&status=inactive" 
				<?php
				if ( isset( $_GET['status'] ) && 'inactive' === sanitize_text_field( wp_unslash( $_GET['status'] ) ) ) {
					echo 'class="current"';
				}
				?>
				><?php esc_html_e( 'Disabled', 'multilanguage' ); ?> <span class="count">(<?php echo esc_html( $count_inactive_languages ); ?>)</span></a></li>
			</ul>
			<form class="bws_form" method="post" action="">
				<!-- display table of languages, source - table.php -->
				<?php
				global $mltlngg_list_table;
				$mltlngg_list_table = new mltlngg_list_table();
				$mltlngg_list_table->prepare_items();
				$mltlngg_list_table->display();
				wp_nonce_field( 'mltlngg_current_languages_form', 'mltlngg_current_languages_field' );
				?>
				<!-- /table of languages -->
			</form>
			<div><p>&nbsp;</p></div>
			<?php
		}
	}
}

if ( ! function_exists( 'mltlngg_actions' ) ) {
	/**
	 * Function for actions part on table of links tab
	 *
	 * @param string $action       Action name.
	 * @param array  $array_locale Locale array.
	 */
	function mltlngg_actions( $action, $array_locale ) {
		global $mltlngg_options;
		$result = array();
		$flag   = false;

		switch ( $action ) {

			case 'enable':
				foreach ( (array) $array_locale as $locale ) {
					foreach ( $mltlngg_options['list_of_languages'] as $key => $lang ) {
						if (
							array_search( $locale, $lang, true ) &&
							! $mltlngg_options['list_of_languages'][ $key ]['enable']
						) {
							$mltlngg_options['list_of_languages'][ $key ]['enable'] = true;
							$flag = true;
							break;
						}
					}
				}
				$action = $flag ? _x( 'enabled', 'plural', 'multilanguage' ) : _x( 'enabled', 'singular', 'multilanguage' );
				break;

			case 'disable':
				foreach ( (array) $array_locale as $locale ) {
					foreach ( $mltlngg_options['list_of_languages'] as $key => $lang ) {
						if (
							array_search( $locale, $lang, true ) &&
							$mltlngg_options['list_of_languages'][ $key ]['enable'] &&
							$lang['locale'] !== $mltlngg_options['default_language']
						) {
							$mltlngg_options['list_of_languages'][ $key ]['enable'] = false;
							$flag = true;
							break;
						}
					}
				}
				$action = $flag ? _x( 'disabled', 'plural', 'multilanguage' ) : _x( 'disabled', 'singular', 'multilanguage' );
				break;

			case 'delete':
				foreach ( (array) $array_locale as $locale ) {
					foreach ( $mltlngg_options['list_of_languages'] as $key => $lang ) {
						if (
							array_search( $locale, $lang, true ) &&
							$lang['locale'] !== $mltlngg_options['default_language']
						) {
							unset( $mltlngg_options['list_of_languages'][ $key ] );
							$flag = true;
							break;
						}
					}
				}
				$action = $flag ? _x( 'deleted', 'plural', 'multilanguage' ) : _x( 'deleted', 'singular', 'multilanguage' );
				break;

			default:
				return array( 'error' => esc_html__( 'Unknown action', 'multilanguage' ) );
		}

		update_option( 'mltlngg_options', $mltlngg_options );
		return $result;
	}
}

if ( ! function_exists( 'mltlngg_add_language_form' ) ) {
	/**
	 * Function for adding new language on settings page
	 */
	function mltlngg_add_language_form() {
		global $mltlngg_languages, $mltlngg_options;
		$list_of_added_languages = array(); /* Array with codes of all added languages */
		foreach ( $mltlngg_options['list_of_languages'] as $mltlngg_added_language ) {
			$list_of_added_languages[] = $mltlngg_added_language['locale'];
		}
		?>
		<!-- Form for adding new language -->
		<form name="mltlngg_add_new_language_form" method="post" action="admin.php?page=multilanguage-languages.php" id="mltlngg-add-new-language-form">
			<table class="form-table">
				<tr valign="top">
					<th scope="row">
						<?php esc_html_e( 'Choose a language', 'multilanguage' ); ?>
					</th>
					<td>
						<select name="mltlngg_lang_list" id="mltlngg_lang_list">
							<option value="" selected disabled hidden><?php echo esc_html__( 'Choose language from list below', 'multilanguage' ); ?></option>
							<?php
							foreach ( $mltlngg_languages as $lg ) {
								if ( ! in_array( $lg[1], $list_of_added_languages ) ) { /* Do not display option if the language was added */
									printf(
										'<option value="%2$s-%3$s">%3$s - %2$s</option>',
										esc_attr( $lg[0] ),
										esc_attr( $lg[1] ),
										esc_html( $lg[2] )
									);
								}
							}
							?>
						</select>
						<br/>
						<div class="bws_info">
						<?php
						printf(
							esc_html__( 'In order to add a language that is not in the list use %1$s the instruction. %2$s', 'multilanguage' ),
							'<a target="_blank" href="https://support.bestwebsoft.com/hc/en-us/articles/360018257597">',
							'</a>'
						);
						?>
												</div>
						<input type="hidden" name="mltlngg_add_new_language_form_was_send" value="send">
						<p class="submit">
							<input class="button button-primary action" name="mltlngg_add_lang" id="mltlngg_add_lang" type="submit" value="<?php esc_html_e( 'Add Language', 'multilanguage' ); ?>">
						</p>
					</td>
				</tr>
			</table>
			<?php wp_nonce_field( 'mltlngg_add_new_language_form', 'mltlngg_add_new_language_field' ); ?>
		</form>
		<!-- /form for adding new language -->
		<?php
	}
}

if ( ! function_exists( 'mltlngg_add_language' ) ) {
	/**
	 * Function for adding new language
	 *
	 * @param string $locale    Locale name.
	 * @param string $lang_name Lang name.
	 */
	function mltlngg_add_language( $locale, $lang_name ) {
		global $mltlngg_options;
		$new_lang = array(
			'locale'    => $locale,
			'name'      => $lang_name,
			'enable'    => false,
			'priority'  => count( $mltlngg_options['list_of_languages'] ) + 1,
		);
		if ( ! empty( $mltlngg_options['list_of_languages'] ) ) {
			foreach ( $mltlngg_options['list_of_languages'] as $item ) {
				if ( $item['locale'] === $locale ) {
					return false;
				}
			}
		}
		$mltlngg_options['list_of_languages'][] = $new_lang;
		update_option( 'mltlngg_options', $mltlngg_options );
	}
}
