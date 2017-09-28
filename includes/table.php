<?php
if ( ! file_exists( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' ) )
	return;

if ( ! class_exists( 'WP_List_Table' ) )
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );

if ( ! class_exists( 'Mltlngg_List_Table' ) ) {
	class Mltlngg_List_Table extends WP_List_Table {
		/* conctructor */
		function __construct() {
			global $mltlngg_options;

			parent::__construct( array(
				'singular'	=> __( 'language', 'multilanguage' ),	/* singular name of the listed records */
				'plural'	=> __( 'languages', 'multilanguage' ),	/* plural name of the listed records */
				'ajax'		=> true	/* does this table support ajax? */
			) );
		}

		function column_default( $item, $column_name ) {
			switch ( $column_name ) {
				case 'title':
				case 'locale':
				case 'flag':
				case 'priority':
					return $item[ $column_name ];
				default:
					return print_r( $item, true ) ; /* Show the whole array for troubleshooting purposes */
			}
		}

		/* function for columns */
		function get_columns() {
			$columns = array(
				'cb'		=> '<input type="checkbox" />',
				'title'		=> __( 'Title', 'multilanguage' ),
				'locale'	=> __( 'URL Slug', 'multilanguage' ),
				'flag'      => __( 'Flag', 'multilanguage' ),
				'priority'  => __( 'Order', 'multilanguage' )
			);
			return $columns;
		}

		/* function for column cb */
		function column_cb( $item ) {
			global $mltlngg_options;
			return $item['locale'] == $mltlngg_options['default_language'] ? '' : sprintf( '<input type="checkbox"  name="mltlngg_language[]" value="%s" />', $item['locale'] );
		}

		function column_title( $item ) {
			global $mltlngg_options;
			$actions = array();

			$name = $item['title'];

			if ( $item['bydefault'] )
				$name .= ' — <span class="post-state">' . __( 'Default', 'multilanguage' ) . '</span>';

			$name = '<strong>' . $name . '</strong>';

			$actions['edit'] = '<a href="' . wp_nonce_url( '?page=multilanguage-languages.php&action=edit&mltlngg_language=' . $item["locale"], 'mltlngg-action' ) . '">' . __( 'Edit', 'multilanguage' ) . '</a>';

			if ( $item['locale'] == $mltlngg_options['default_language'] )
				return sprintf( '%1$s %2$s', $name, $this->row_actions( $actions ) );

			if ( ! $item['is_enabled'] ) {
				$actions['enable'] = '<a href="' . wp_nonce_url( '?page=multilanguage-languages.php&action=enable&mltlngg_language=' . $item["locale"], 'mltlngg-action' ) . '">' . __( 'Enable', 'multilanguage' ) . '</a>';
			} else {
				$actions['disable'] = '<a href="' . wp_nonce_url( '?page=multilanguage-languages.php&action=disable&mltlngg_language=' . $item["locale"], 'mltlngg-action' ) . '">' . __( 'Disable', 'multilanguage' ) . '</a>';
			}
			$actions['delete'] = '<a href="' . wp_nonce_url( '?page=multilanguage-languages.php&action=delete&mltlngg_language=' . $item["locale"], 'mltlngg-action' ) . '">' . __( 'Delete', 'multilanguage' ) . '</a>';
			return sprintf( '%1$s %2$s', $name, $this->row_actions( $actions ) );
		}

		/* function for bulk actions */
		function get_bulk_actions() {
			if ( ! isset( $_GET['status'] ) ) {
				$actions = array(
					'enable'	=> __( 'Enable', 'multilanguage' ),
					'disable'	=> __( 'Disable', 'multilanguage' ),
					'delete'	=> __( 'Delete', 'multilanguage' )
				);
			} elseif ( 'active' == $_GET['status'] ) {
				$actions = array(
					'disable'	=> __( 'Disable', 'multilanguage' ),
					'delete'	=> __( 'Delete', 'multilanguage' )
				);
			} else {
				$actions = array(
					'enable'	=> __( 'Enable', 'multilanguage' ),
					'delete'	=> __( 'Delete', 'multilanguage' )
				);
			}
			return $actions;
		}

		function get_sortable_columns() {
			$sortable_columns = array(
				'title'		=> array( 'title', false ),
				'priority'	=> array( 'priority', false )
			);
			return $sortable_columns;
		}

		function single_row( $item ) {
			static $mltlngg_count_row = 1;
			$mltlngg_classes = "";
			if ( ( $mltlngg_count_row % 2 ) ) {
				$mltlngg_classes = "alternate" ;
			}
			if ( ! $item['is_enabled'] ) {
				$mltlngg_classes .= " mltlngg-disabled";
			}
			$row_class = ' class="' . $mltlngg_classes . '"';
			echo '<tr' . $row_class . '>';
			$this->single_row_columns( $item );
			echo '</tr>';
			$mltlngg_count_row++;
		}

		/* function for prepairing items */
		function prepare_items() {
			global $mltlngg_options;
			$this->_column_headers = array(
				$this->get_columns(),
				array(),
				$this->get_sortable_columns()
			);
			$action = $this->current_action();
			$per_page = $this->get_items_per_page( 'languages_per_page', 10 );
			$current_page = $this->get_pagenum();
			$total_items = count( $mltlngg_options['list_of_languages'] );
			$this->set_pagination_args( array(
				'total_items'	=> $total_items,
				'per_page'		=> $per_page
			) );

			$return = array();

			foreach ( $mltlngg_options['list_of_languages'] as $language ) {
				if ( isset( $_GET['status'] ) ) {
					if ( 'active' == $_GET['status'] && ! $language['enable'] )
						continue;
					if ( 'inactive' == $_GET['status'] && $language['enable'] )
						continue;
				}

				$flag_src = ( empty( $language['flag'] ) ) ? plugins_url( 'images/flags/', dirname( __FILE__ ) ) . $language['locale'] . '.png' : $language['flag'];
				$flag = "<img src=" . $flag_src . " alt=" . $language['name'] . " title=" . $language['name'] . ">";

				$return[] = array(
					'title'			=> $language['name'],
					'locale'		=> $language['locale'],
					'flag'			=> $flag,
					'is_enabled'	=> $language['enable'],
					'priority'		=> $language['priority'],
					'bydefault'		=> ( $language['locale'] == $mltlngg_options['default_language'] )
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
	function mltlngg_sort_data( $data_to_sort ) {
		$order_by = ( ! empty( $_GET['orderby'] ) ) ? $_GET['orderby'] : 'title';
		$order = ( ! empty( $_GET['order'] ) ) ? $_GET['order'] : 'asc';
		foreach ( $data_to_sort as $key => $row ) {
			$name[ $key ]  = $row['title'];
			$priority[ $key ] = $row['priority'];
		}
		$name_lowercase = array_map( 'strtolower', $name );
		switch ( $order_by ) {
			case 'title':
				if ( $order == 'asc' ) {
					array_multisort( $name_lowercase, SORT_ASC, $data_to_sort );
				} else {
					array_multisort( $name_lowercase, SORT_DESC, $data_to_sort );
				}
				break;
			case 'priority':
				if ( $order == 'asc' ) {
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
	function mltlngg_add_menu_items() {
		add_action( "load-multilanguage_page_multilanguage-languages", 'mltlngg_add_options' );
	}
}

if ( ! function_exists( 'mltlngg_add_options' ) ) {
	function mltlngg_add_options() {
		global $mltlngg_list_table;
		$option = 'per_page';
		$args = array(
			'label'		=> __( 'Languages', 'multilanguage' ),
			'default'	=> 10,
			'option'	=> 'languages_per_page'
		);
		add_screen_option( $option, $args );
		$mltlngg_list_table = new mltlngg_list_table();
	}
}

if ( ! function_exists( 'mltlngg_table_set_option' ) ) {
	function mltlngg_table_set_option( $status, $option, $value ) {
		return $value;
	}
}

/* function to display table of links */
if ( ! function_exists( 'mltlngg_table' ) ) {
	function mltlngg_table() {
		global $mltlngg_options, $wp_version, $mltlngg_plugin_info;

		$error = $message = '';

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
			if ( preg_match( '/^([a-z]{2,3}|[a-z]{2,3}[_][A-Z]{2,3})-(.+?)$/u', $_POST['mltlngg_lang_list'], $matches ) ) { /* If language data is correct */
				mltlngg_add_language( $matches[1], $matches[2] ); /* Add new language (locale, name) */
				$message = __( 'Language added', 'multilanguage' );
			} else { /* If language data is incorrect */
				$error = __( 'Incorrect language data.', 'multilanguage' );
			}
		}
		/* Actions for table of languages */
		if ( isset( $_POST['mltlngg_language'] ) && check_admin_referer( 'mltlngg_current_languages_form', 'mltlngg_current_languages_field' ) ) {
			if ( isset( $_POST['action'] ) && -1 != $_POST['action'] ) {
				$action   = $_POST['action'];
				$language = $_POST['mltlngg_language'];
			} elseif ( isset( $_POST['action2'] ) && -1 != $_POST['action2'] ) {
				$action   = $_POST['action2'];
				$language = $_POST['mltlngg_language'];
			}
		} elseif ( isset( $_GET['mltlngg_language'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'mltlngg-action' ) ) {
			$action   = $_GET['action'];
			$language = $_GET['mltlngg_language'];
		}

		if ( isset( $action ) ) {
			$action_result = in_array( $action, array( 'enable', 'disable', 'delete' ) ) ? mltlngg_actions( $action, $language ) : array();
			if ( isset( $action_result['error'] ) )
				$error .= $action_result['error'];
			if ( isset( $action_result['success'] ) )
				$message .= $action_result['success'];
		}

		if ( isset( $action ) && 'edit' == $action ) {
			foreach ( $mltlngg_options['list_of_languages'] as $language_key => $value ) {
				/* if language data have been finded */
				if ( array_search( $language, $value, true ) ) {
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

				if ( 'custom' != $_POST['mltlngg_flag_option'] ) {
					unset( $language_data['flag'] );
				} elseif ( ! empty( $_FILES['mltlngg_upload_flag']['tmp_name'] ) ) {
					$upload_dir = wp_upload_dir();

					if ( ! $upload_dir["error"] ) {
						$custom_folder = $upload_dir['basedir'] . '/multilanguage';
						if ( ! is_dir( $custom_folder ) ) {
							wp_mkdir_p( $custom_folder, 0755 );
						}
					}
					$max_image_width	= 50;
					$max_image_height	= 50;
					$max_image_size		= 32 * 1024;
					$valid_types 		= array( 'jpg', 'jpeg', 'png' );

					/* Checking is allowed download file given parameters */
					if ( is_uploaded_file( $_FILES['mltlngg_upload_flag']['tmp_name'] ) ) {
						$filename	=	$_FILES['mltlngg_upload_flag']['tmp_name'];
						$ext		=	substr( $_FILES['mltlngg_upload_flag']['name'], 1 + strrpos( $_FILES['mltlngg_upload_flag']['name'], '.' ) );
						if ( filesize( $filename ) > $max_image_size ) {
							$error	= sprintf( __( "Error: File size > %s.", 'multilanguage' ), '32K' );
						} elseif ( ! in_array( strtolower( $ext ), $valid_types ) ) {
							$error	= __( "Error: Invalid file type.", 'multilanguage' );
						} else {
							$size	= GetImageSize( $filename );
							if ( $size && $size[0] <= $max_image_width && $size[1] <= $max_image_height ) {
								/* Construction to rename downloading file */
								$new_ext	= wp_check_filetype( $_FILES['mltlngg_upload_flag']['name'] );
								$namefile	= $language_data['locale'] . '.' . $new_ext['ext'];
								$uploadfile	= $custom_folder . '/' . $namefile;

								if ( move_uploaded_file( $_FILES['mltlngg_upload_flag']['tmp_name'], $uploadfile ) ) {
									$language_data['flag'] = $upload_dir['baseurl'] . '/multilanguage/' . $namefile;
								} else {
									$error = __( "Error: failed to move file.", 'multilanguage' );
								}
							} else {
								$error = sprintf( __( "Error: image width and height should not exceed %s.", 'multilanguage' ), '50px' );
							}
						}
					} else {
						$error = __( "Uploading Error: check image properties.", 'multilanguage' );
					}
				}

				$language_data['priority'] = intval( $_POST['mltlngg_priority'] );

				$mltlngg_options['list_of_languages'][ $language_key ] = $language_data;

				/* Sorting list of language by priority */
				foreach ( $mltlngg_options['list_of_languages'] as $key => $language_to_action ) {
					$mltlngg_priority[ $key ] = $language_to_action['priority'];
				}
				array_multisort( $mltlngg_priority, SORT_ASC, $mltlngg_options['list_of_languages'] );

				$mltlngg_options['flush_rewrite_rules'] = 1;

				update_option( 'mltlngg_options', $mltlngg_options );
				$message = __( 'Settings saved.', 'multilanguage' );
			}

			echo ( version_compare( $wp_version, '4.0', '<' ) ) ? "<h2>" : "<h1>";
				_e( 'Edit Language', 'multilanguage' ); ?>: <?php echo $language_data["name"];
			echo ( version_compare( $wp_version, '4.0', '<' ) ) ? "</h2>" : "</h1>";

			if ( ! empty( $error ) ) { ?>
				<div class="error below-h2"><p><?php echo $error; ?></p></div>
			<?php } elseif ( ! empty( $message ) ) { ?>
				<div class="updated fade below-h2"><p><?php echo $message; ?></p></div>
			<?php } ?>
			<form class="bws_form" method="post" action="" enctype="multipart/form-data">
				<table class="form-table">
					<tr>
						<th><?php _e( 'Default', 'multilanguage' ); ?></th>
						<td>
							<input type="checkbox" name="mltlngg_default_lang" value="1" <?php checked( $language_data['locale'], $mltlngg_options['default_language'] ); ?> />
						</td>
					</tr>
					<tr>
						<th><?php _e( 'Flag Image', 'multilanguage' ); ?></th>
						<td>
							<fieldset>
								<label><input name="mltlngg_flag_option" type="radio" value="standart" <?php checked( empty( $language_data['flag'] ) ); ?> class="bws_option_affect" data-affect-hide=".mltlngg_flag_option_custom" />
								&nbsp;<?php _e( 'Default', 'multilanguage' ); ?></label>
								<br>
								<label><input name="mltlngg_flag_option" type="radio" value="custom" <?php checked( ! empty( $language_data['flag'] ) ); ?> class="bws_option_affect" data-affect-show=".mltlngg_flag_option_custom" />
								&nbsp;<?php _e( 'Custom', 'multilanguage' ); ?></label>
							</fieldset>
						</td>
					</tr>
					<tr>
						<th></th>
						<td>
							<?php _e( 'Current flag', 'multilanguage' ); ?>:&nbsp;&nbsp;
							<img src="<?php echo ( empty( $language_data['flag'] ) ) ? plugins_url( 'images/flags/', dirname( __FILE__ ) ) . $language_data['locale'] . '.png' : $language_data['flag']; ?>" />
						</td>
					</tr>
					<tr class="mltlngg_flag_option_custom">
						<th></th>
						<td>
							<input type="file" name="mltlngg_upload_flag">
							<div class="bws_info"><?php printf( __( 'Upload image of %s maximum dimensions in JPG, JPEG or PNG formats (maximum file size - %s).', 'multilanguage' ), '50×50px', '32kb' ); ?></div>
						</td>
					</tr>
					<tr>
						<th><?php _e( 'Order', 'multilanguage' ); ?></th>
						<td>
							<input type="number" min="1" max="200" name="mltlngg_priority" value="<?php echo $language_data["priority"]; ?>" />
						</td>
					</tr>
				</table>
				<?php if ( ! bws_hide_premium_options_check( $mltlngg_options ) ) { ?>
					<div class="bws_pro_version_bloc">
						<div class="bws_pro_version_table_bloc">
							<button type="submit" name="bws_hide_premium_options" class="notice-dismiss bws_hide_premium_options" title="<?php _e( 'Close', 'multilanguage' ); ?>"></button>
							<div class="bws_table_bg"></div>
							<table class="form-table bws_pro_version">
								<tr>
									<th><?php _e( 'URL Slug', 'multilanguage' ); ?></th>
									<td>
										<fieldset>
											<?php $exploded_locale = explode( '_', $language_data['locale'] );
											if ( 1 == count( $exploded_locale ) ) { ?>
												<label>
													<input disabled="disabled" type="radio" checked="checked" />
													&nbsp;<?php echo $language_data['locale']; ?>
												</label>
											<?php } else { ?>
												<label>
													<input disabled="disabled" type="radio" />
													&nbsp;<?php echo $exploded_locale[0]; ?>
												</label>
												<br>
												<label>
													<input checked="checked" disabled="disabled" type="radio" />
													&nbsp;<?php echo $language_data['locale']; ?>
												</label>
											<?php } ?>
										</fieldset>
									</td>
								</tr>
								<tr>
									<th><?php _e( 'Site Title', 'multilanguage' ); ?></th>
									<td>
										<textarea disabled name="mltlngg_blogname"></textarea>
									</td>
								</tr>
								<tr>
									<th><?php _e( 'Tagline', 'multilanguage' ); ?></th>
									<td>
										<textarea disabled name="mltlngg_blogdescription"></textarea>
									</td>
								</tr>
							</table>
						</div>
						<div class="bws_pro_version_tooltip">
							<a class="bws_button" href="<?php echo $mltlngg_plugin_info['PluginURI']; ?>?k=fa164f00821ed3a87e6f78cb3f5c277b&amp;pn=143&amp;v=<?php echo $mltlngg_plugin_info["Version"]; ?>&amp;wp_v=<?php echo $wp_version; ?>" target="_blank" title="<?php echo $mltlngg_plugin_info["Name"]; ?>"><?php _e( 'Upgrade to Pro', 'multilanguage' ); ?></a>
							<div class="clear"></div>
						</div>
					</div>
				<?php } ?>
				<p class="submit">
					<input type="submit" name="mltlngg_edit_language_form" class="button-primary" value="<?php _e( 'Save changes', 'multilanguage' ); ?>">
					<?php wp_nonce_field( 'mltlngg_edit_language_form', 'mltlngg_edit_language_form' ); ?>
				</p>
			</form>
		<?php } else { ?>
			<h1>
				<?php _e( 'Languages', 'multilanguage' ); ?>
				<a class="page-title-action add-new-h2 hide-if-no-js" href="#" id="mltlngg-add-lang-link"><?php _e( 'Add New', 'multilanguage' ); ?></a>
			</h1>
			<?php if ( ! empty( $error ) ) { ?>
				<div class="error below-h2"><p><?php echo $error; ?></p></div>
			<?php } elseif ( ! empty( $message ) ) { ?>
				<div class="updated fade below-h2"><p><?php echo $message; ?></p></div>
			<?php }
			/* Form for adding new language */
			mltlngg_add_language_form();

			$count_active_languages = $count_inactive_languages = 0;
			foreach ( $mltlngg_options['list_of_languages'] as $language ) {
				if ( $language['enable'] )
					$count_active_languages++;
				else
					$count_inactive_languages++;
			} ?>
			<ul class="subsubsub">
				<li class="all"><a href="admin.php?page=multilanguage-languages.php" <?php if ( ! isset( $_GET['status'] ) ) echo 'class="current"'; ?>><?php _e( 'All', 'multilanguage' ); ?> <span class="count">(<?php echo $count_active_languages + $count_inactive_languages; ?>)</span></a> |</li>
				<li class="active"><a href="admin.php?page=multilanguage-languages.php&status=active" <?php if ( isset( $_GET['status'] ) && 'active' == $_GET['status'] ) echo 'class="current"'; ?>><?php _e( 'Active', 'multilanguage' ); ?> <span class="count">(<?php echo $count_active_languages; ?>)</span></a> |</li>
				<li class="inactive"><a href="admin.php?page=multilanguage-languages.php&status=inactive" <?php if ( isset( $_GET['status'] ) && 'inactive' == $_GET['status'] ) echo 'class="current"'; ?>><?php _e( 'Disabled', 'multilanguage' ); ?> <span class="count">(<?php echo $count_inactive_languages; ?>)</span></a></li>
			</ul>
			<form class="bws_form" method="post" action="">
				<!-- display table of languages, source - table.php -->
				<?php global $mltlngg_list_table;
				$mltlngg_list_table = new mltlngg_list_table();
				$mltlngg_list_table->prepare_items();
				$mltlngg_list_table->display();
				wp_nonce_field( 'mltlngg_current_languages_form', 'mltlngg_current_languages_field' ); ?>
				<!-- /table of languages -->
			</form>
			<div><p>&nbsp;</p></div>
		<?php }
	}
}

/* function for actions part on table of links tab */
if ( ! function_exists( 'mltlngg_actions' ) ) {
	function mltlngg_actions( $action, $array_locale ) {
		global $mltlngg_options;
		$result = array();
		$flag   = false;

		switch ( $action ) {

			case 'enable':
				foreach( (array)$array_locale as $locale ) {
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
				foreach( (array)$array_locale as $locale ) {
					foreach ( $mltlngg_options['list_of_languages'] as $key => $lang ) {
						if (
							array_search( $locale, $lang, true ) &&
							$mltlngg_options['list_of_languages'][ $key ]['enable'] &&
							$lang['locale'] != $mltlngg_options['default_language']
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
				foreach( (array)$array_locale as $locale ) {
					foreach ( $mltlngg_options['list_of_languages'] as $key => $lang ) {
						if (
							array_search( $locale, $lang, true ) &&
							$lang['locale'] != $mltlngg_options['default_language']
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
				return array( 'error' => __( 'Unknown action', 'multilanguage' ) );
		}

		if ( $flag ) {
			$result['success'] = sprintf( __( 'Selected languages have been %s', 'multilanguage' ), $action );
			$mltlngg_options['flush_rewrite_rules'] = 1;
		} else {
			$result['error'] = sprintf( __( 'None of the selected languages has been %s', 'multilanguage' ), $action );
		}

		update_option( 'mltlngg_options', $mltlngg_options );
		return $result;
	}
}

/* Form for adding new language on settings page */
if ( ! function_exists( 'mltlngg_add_language_form' ) ) {
	function mltlngg_add_language_form() {
		global $mltlngg_languages, $mltlngg_options;
		$list_of_added_languages = array(); /* Array with codes of all added languages */
		foreach ( $mltlngg_options['list_of_languages'] as $mltlngg_added_language ) {
			$list_of_added_languages[] = $mltlngg_added_language['locale'];
		} ?>
		<!-- Form for adding new language -->
		<form name="mltlngg_add_new_language_form" method="post" action="admin.php?page=multilanguage-languages.php" id="mltlngg-add-new-language-form">
			<table class="form-table">
				<tr valign="top">
					<th scope="row">
						<?php _e( 'Choose a language', 'multilanguage' ); ?>
					</th>
					<td>
						<select name="mltlngg_lang_list" id="mltlngg_lang_list">
							<option value=""></option>
							<?php foreach ( $mltlngg_languages as $lg ) {
								if ( ! in_array( $lg[1], $list_of_added_languages ) ) { /* Do not display option if the language was added */
									printf(
										'<option value="%2$s-%3$s">%3$s - %2$s</option>',
										esc_attr( $lg[0] ),
										esc_attr( $lg[1] ),
										esc_html( $lg[2] )
									);
								}
							} ?>
						</select>
						<br/>
						<input type="hidden" name="mltlngg_add_new_language_form_was_send" value="send">
						<p class="submit">
							<input class="button button-primary action" name="mltlngg_add_lang" id="mltlngg_add_lang" type="submit" value="<?php _e( 'Add Language', 'multilanguage' ); ?>">
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
	function mltlngg_add_language( $locale, $lang_name ) {
		global $mltlngg_options;
		$new_lang = array(
			'locale'	=> $locale,
			'name'		=> $lang_name,
			'enable'	=> false,
			'priority'	=> count( $mltlngg_options['list_of_languages'] ) + 1
		);
		if ( ! empty( $mltlngg_options['list_of_languages'] ) ) {
			foreach ( $mltlngg_options['list_of_languages'] as $item ) {
				if ( $item['locale'] == $locale )
					return false;
			}
		}
		$mltlngg_options['list_of_languages'][] = $new_lang;
		update_option( 'mltlngg_options', $mltlngg_options );
	}
}