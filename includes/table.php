<?php
if ( ! file_exists( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' ) )
	return;

if ( ! class_exists( 'WP_List_Table' ) )
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );

if ( ! class_exists( 'Mltlngg_List_Table' ) ) {
	class Mltlngg_List_Table extends WP_List_Table {

		public $hide_pro_options;
		/* conctructor */
		function __construct() {
			global $status, $page, $mltlngg_options;

			parent::__construct( array(
				'singular'	=> __( 'language', 'multilanguage' ),	/* singular name of the listed records */
				'plural'	=> __( 'languages', 'multilanguage' ),	/* plural name of the listed records */
				'ajax'		=> true	/* does this table support ajax? */
			) );
			$this->hide_pro_options = bws_hide_premium_options_check( $mltlngg_options );
		}

		function column_default( $item, $column_name ) {
			switch ( $column_name ) {
				case 'name':
				case 'locale':
				case 'flag':
				case 'status':
				case 'priority':
				case 'bydefault':
					return $item[ $column_name ];
				case 'pro_options_slug':
				case 'pro_options_blogname':
				case 'pro_options_blogdescription':
					return $this->hide_pro_options ? '' : $item[ $column_name ];
				default:
					return print_r( $item, true ) ; /* Show the whole array for troubleshooting purposes */
			}
		}

		/* function for columns */
		function get_columns() {
			$columns = array(
				'cb'		=> '<input type="checkbox" />',
				'name'		=> __( 'Name', 'multilanguage' ),
				'locale'	=> __( 'Locale', 'multilanguage' )
			);
			if ( ! $this->hide_pro_options )
				$columns['pro_options_slug'] = __( 'Link slug', 'multilanguage' );
			$columns['flag']      = __( 'Flag', 'multilanguage' );
			$columns['status']    = __( 'Status', 'multilanguage' );
			$columns['priority']  = __( 'Priority', 'multilanguage' );
			$columns['bydefault'] = __( 'Default', 'multilanguage' );

			if ( ! $this->hide_pro_options ) {
				$columns['pro_options_blogname'] = __( 'Site Title', 'multilanguage' );
				$columns['pro_options_blogdescription'] = __( 'Tagline', 'multilanguage' );
			}

			return $columns;
		}

		/* function for column cb */
		function column_cb( $item ) {
			global $mltlngg_options;
			return $item['locale'] == $mltlngg_options['default_language'] ? '' : sprintf( '<input type="checkbox"  name="mltlngg_language[]" value="%s" />', $item['locale'] );
		}

		function column_name( $item ) {
			global $mltlngg_options;
			$actions = array();

			if ( $item['locale'] == $mltlngg_options['default_language'] )
				return sprintf( '%1$s %2$s', $item['name'], $this->row_actions( $actions ) );

			if ( __( 'Disabled', 'multilanguage' ) == $item['status'] ) {
				$actions['enable'] = '<a href="' . wp_nonce_url( '?page=mltlngg_settings&action=enable&mltlngg_language=' . $item["locale"], 'mltlngg-action' ) . '">' . __( 'Enable', 'multilanguage' ) . '</a>';
			} else {
				$actions['disable'] = '<a href="' . wp_nonce_url( '?page=mltlngg_settings&action=disable&mltlngg_language=' . $item["locale"], 'mltlngg-action' ) . '">' . __( 'Disable', 'multilanguage' ) . '</a>';
			}
			$actions['delete'] = '<a href="' . wp_nonce_url( '?page=mltlngg_settings&action=delete&mltlngg_language=' . $item["locale"], 'mltlngg-action' ) . '">' . __( 'Delete', 'multilanguage' ) . '</a>';
			return sprintf( '%1$s %2$s', $item['name'], $this->row_actions( $actions ) );
		}

		/* function for bulk actions */
		function get_bulk_actions() {
			$actions = array(
				'enable'	=> __( 'Enable', 'multilanguage' ),
				'disable'	=> __( 'Disable', 'multilanguage' ),
				'delete'	=> __( 'Delete', 'multilanguage' )
			);
			return $actions;
		}

		function get_sortable_columns() {
			$sortable_columns = array(
				'name'		=> array( 'name', false ),
				'status'	=> array( 'status', false ),
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
			if ( __( 'Disabled', 'multilanguage' ) == $item['status'] ) {
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
			$this->_column_headers = $this->get_column_info();
			$action = $this->current_action();
			$per_page = $this->get_items_per_page( 'languages_per_page', 10 );
			$current_page = $this->get_pagenum();
			$total_items = count( $mltlngg_options['list_of_languages'] );
			$this->set_pagination_args( array(
				'total_items'	=> $total_items,
				'per_page'		=> $per_page
			) );
			$this->items = array_slice( mltlngg_table_data( $this->hide_pro_options ), ( ( $current_page - 1 ) * $per_page ), $per_page );
		}
	}
}

if ( ! function_exists( 'mltlngg_table_data' ) ) {
	function mltlngg_table_data( $hide_pro_options = false ) {
		global $mltlngg_options;
		$i = 0;
		$mltlngg_data = $mltlngg_options['list_of_languages'];
		foreach ( $mltlngg_data as $mltlngg_language ) {
			$mltlngg_status = ( true === $mltlngg_language['enable'] ) ? __( 'Enabled', 'multilanguage' ) : __( 'Disabled', 'multilanguage' );

			$mltlngg_flag = "<img src=" . plugins_url( 'images/flags/', dirname( __FILE__ ) ) . $mltlngg_language['locale'] . '.png' . " alt=" . $mltlngg_language['name'] . " title=" . $mltlngg_language['name'] . ">";
			$by_default   = '<input name="mltlngg_default_lang" type="radio" value="' . $mltlngg_language['locale'] . '"' . ( ( $mltlngg_language['locale'] == $mltlngg_options['default_language'] ) ? ' checked>' : '>' );
			$priority     = '<input value="' . $mltlngg_language['priority'] . '" name="' . $mltlngg_language['locale'] . '" type="number" min="1" />';
			
			$mltlngg_return[ $i ] = array(
				'name'		=> $mltlngg_language['name'],
				'locale'	=> $mltlngg_language['locale'],
				'flag'		=> $mltlngg_flag,
				'status'	=> $mltlngg_status,
				'priority'	=> $priority,
				'bydefault'	=> $by_default
			);
			if ( ! $hide_pro_options ) {
				$locale_array = explode( '_', $mltlngg_language['locale'] );
				$mltlngg_return[ $i ]['pro_options_slug'] = 
					'<label class="mltlngg_language_code"><input type="radio" disabled="disabled"/>&nbsp;' . $locale_array[0] .'</label>' .
					( isset( $locale_array[1] ) ? '<label><input type="radio" disabled="disabled"/>&nbsp;' . $mltlngg_language['locale'] .'</label>' : '' );


				if ( $mltlngg_language['locale'] == $mltlngg_options['default_language'] ) {
					$mltlngg_return[ $i ]['pro_options_blogname'] = esc_attr( get_option( 'blogname' ) );
					$mltlngg_return[ $i ]['pro_options_blogdescription'] = esc_attr( get_option( 'blogdescription' ) );
				} else {
					$mltlngg_return[ $i ]['pro_options_blogdescription'] = $mltlngg_return[ $i ]['pro_options_blogname'] = '<textarea class="mltlngg_table_pro_option" disabled="disabled"></textarea>';
				}
			}
			$i++;
		}
		if ( isset( $mltlngg_return ) ) {
			if ( isset( $_GET['orderby'] ) && isset( $_GET['order'] ) ) {
				$mltlngg_return = mltlngg_sort_data( $mltlngg_return );
			}
			return $mltlngg_return;
		} else {
			return false;
		}
	}
}

if ( ! function_exists( 'mltlngg_sort_data' ) ) {
	function mltlngg_sort_data( $mltlngg_data_to_sort ) {
		$mltlngg_order_by = ( ! empty( $_GET['orderby'] ) ) ? $_GET['orderby'] : 'name';
		$mltlngg_order = ( ! empty( $_GET['order'] ) ) ? $_GET['order'] : 'asc';
		foreach ( $mltlngg_data_to_sort as $mltlngg_key => $mltlngg_row ) {
			$mltlngg_name[ $mltlngg_key ]  = $mltlngg_row['name'];
			$mltlngg_status[ $mltlngg_key ] = $mltlngg_row['status'];
			$mltlngg_priority[ $mltlngg_key ] = $mltlngg_row['priority'];
		}
		$mltlngg_name_lowercase = array_map( 'strtolower', $mltlngg_name );
		switch ( $mltlngg_order_by ) {
			case 'name':
				if ( $mltlngg_order == 'asc' ) {
					array_multisort( $mltlngg_name_lowercase, SORT_ASC, $mltlngg_data_to_sort );
				} else {
					array_multisort( $mltlngg_name_lowercase, SORT_DESC, $mltlngg_data_to_sort );
				}
				break;

			case 'status':
				if ( $mltlngg_order == 'asc' ) {
					array_multisort( $mltlngg_status, SORT_ASC, $mltlngg_name_lowercase, SORT_ASC, $mltlngg_data_to_sort );
				} else {
					array_multisort( $mltlngg_status, SORT_DESC, $mltlngg_name_lowercase, SORT_ASC, $mltlngg_data_to_sort );
				}
				break;

			case 'priority':
				if ( $mltlngg_order == 'asc' ) {
					array_multisort( $mltlngg_priority, SORT_ASC, $mltlngg_name_lowercase, SORT_ASC, $mltlngg_data_to_sort );
				} else {
					array_multisort( $mltlngg_priority, SORT_DESC, $mltlngg_name_lowercase, SORT_ASC, $mltlngg_data_to_sort );
				}
				break;
		}
		return $mltlngg_data_to_sort;
	}
}

if ( ! function_exists( 'mltlngg_add_menu_items' ) ) {
	function mltlngg_add_menu_items() {
		add_action( "load-bws-plugins_page_mltlngg_settings", 'mltlngg_add_options' );
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
		global $mltlngg_list_table;
		$mltlngg_list_table = new mltlngg_list_table();
		$mltlngg_list_table->prepare_items();
		$mltlngg_list_table->display();
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
		
		if ( $flag )
			$result['success'] = sprintf( __( 'Selected languages have been %s', 'multilanguage' ), $action );
		else
			$result['error'] = sprintf( __( 'No one from selected languages has not been %s', 'multilanguage' ), $action );

		update_option( 'mltlngg_options', $mltlngg_options );
		return $result;
	}
}