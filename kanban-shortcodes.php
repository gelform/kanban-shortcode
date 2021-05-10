<?php
/*
Contributors:		gelform, kkoppenhaver
Plugin Name:		Kanban: Shortcodes
Plugin URI:			https://kanbanwp.com/
Description:		Embed your Kanban board on another page, or display a filtered to-do list.
Requires at least:	4.0
Tested up to:		4.9
Version:			0.0.6
Release Date:		March 21, 2017
Author:				Kanban for WordPress
Author URI:			https://kanbanwp.com/
License:			GPLv2 or later
License URI:		http://www.gnu.org/licenses/gpl-2.0.html
Text Domain:		kanban-shortcodes
Domain Path: 		/languages/
*/



// Kanban Shortcodes is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 2 of the License, or
// any later version.
//
// Kanban Shortcodes is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Kanban Shortcodes. If not, see {URI to Plugin License}.



// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}



class Kanban_Shortcodes {
	static $slug = '';
	static $friendlyname = '';
	static $plugin_basename = '';
	static $plugin_data;



	static $views = array();



	static function init() {
		self::$slug = basename( __FILE__, '.php' );
		self::$plugin_basename = plugin_basename( __FILE__ );
		self::$friendlyname = trim( str_replace( array( 'Kanban', '_' ), ' ', __CLASS__ ) );



		if ( !function_exists( 'get_plugin_data' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}

		self::$plugin_data = get_plugin_data( __FILE__ );



		$is_core = self::check_for_core();
		if ( !$is_core ) return false;



		// Build list of views
		$methods = get_class_methods( __CLASS__ );

		foreach ( $methods as $method ) {
			if ( strpos( $method, 'render_' ) !== false ) {
				self::$views[] = str_replace( 'render_', '', $method );
			}
		}


		add_shortcode( 'kanban', array( __CLASS__, 'shortcode_parser' ) );
	}



	static function shortcode_parser( $atts = array(), $content ) {

		$view = 'board';

		// View is passed as val with int key e.g. [kanban board]
		if ( is_array( $atts ) ) {
			foreach ( $atts as $key => $val ) {

				// If the key is 0, 1, etc.
				if ( is_numeric( $key ) ) {

					// And in the views, then it must be the view.
					if ( in_array( $val, self::$views ) ) {
						$view = $val;
						break;
					}
				}
			}
		}

		// Determine which method to use.
		$func = 'render_' . $view;

		if ( ! method_exists( __CLASS__, $func ) ) {
			return;
		}

		self::$func( $atts, $content );
	}



	static function render_list_order_by_user( $atts, $content ) {
	}



	static function render_list_order_by_board( $atts, $content ) {


		// Get all tasks.
		$tasks = Kanban_Task::get_all();



		// Get all boards, by id.
		$boards          = Kanban_Board::get_all();
		$boards_in_order = Kanban_Utils::order_array_of_objects_by_property( $boards, 'position', 'int' );



		// Get statuses for each board.
		foreach ( $boards as $board ) {
			$board->statuses = Kanban_Status::get_all( $board->id );
		}



		// Add tasks to boards -> statuses.
		foreach ( $tasks as $task ) {

			// Filter our tasks
			if ( ! is_null( $atts[ 'user' ] ) && $task->user_id_assigned != $atts[ 'user' ] ) {
				continue;
			}

			if ( ! is_null( $atts[ 'board' ] ) && $task->board_id != $atts[ 'board' ] ) {
				continue;
			}

			if ( ! is_null( $atts[ 'status' ] ) && $task->status_id != $atts[ 'status' ] ) {
				continue;
			}

			if ( ! is_null( $atts[ 'project' ] ) && $task->project_id != $atts[ 'project' ] ) {
				continue;
			}



			// If board doesn't exist, skip it.
			if ( ! isset( $boards[ $task->board_id ] ) ) {
				continue;
			}

			// If status doesn't exist, skip it.
			if ( ! isset( $boards[ $task->board_id ]->statuses[ $task->status_id ] ) ) {
				continue;
			}

			// Add the array of tasks.
			if ( ! isset( $boards[ $task->board_id ]->statuses[ $task->status_id ]->tasks ) ) {
				$boards[ $task->board_id ]->statuses[ $task->status_id ]->tasks = array();
			}

			// Add the task to the status.
			$boards[ $task->board_id ]->statuses[ $task->status_id ]->tasks[] = $task;

			// Add a task count to each board.
			if ( ! isset( $boards[ $task->board_id ]->task_count ) ) {
				$boards[ $task->board_id ]->task_count = 0;
			}

			// Count the task.
			$boards[ $task->board_id ]->task_count ++;

			if ( ! isset( $boards[ $task->board_id ]->statuses[ $task->status_id ]->task_count ) ) {
				$boards[ $task->board_id ]->statuses[ $task->status_id ]->task_count = 0;
			}

			$boards[ $task->board_id ]->statuses[ $task->status_id ]->task_count ++;

		}



		include plugin_dir_path( __FILE__ ) . '/templates/list-board.php';
	}



	static function render_list_order_by_project( $atts ) {
	}



	static function render_list( $atts, $content ) {

		$defaults = array(
			'user'    => null,
			'board'   => null,
			'status'  => null,
			'project' => null,
			'order'   => 'board'
		);

		$atts = shortcode_atts( $defaults, $atts );



		$func = 'render_list_order_by_' . $atts[ 'order' ];

		if ( ! method_exists( __CLASS__, $func ) ) {
			return;
		}

		self::$func( $atts, $content );
	}



	static function render_board( $atts, $content ) {

		$defaults = array(
			'id'     => null,
			'css'    => null,
			'width'  => '100%',
			'height' => '400px'
		);

		$atts = shortcode_atts( $defaults, $atts );



		$atts[ 'url' ] = Kanban_Template::get_uri();

		if ( ! is_null( $atts[ 'id' ] ) ) {
			$atts[ 'url' ] = add_query_arg(
				array(
					'board_id' => $atts[ 'id' ]
				),
				$atts[ 'url' ]
			);
		}



		include plugin_dir_path( __FILE__ ) . '/templates/board-iframe.php';
	}



	static function render_uri( $atts, $content ) {

		$defaults = array(
			'id' => null
		);

		$atts = shortcode_atts( $defaults, $atts );



		$atts[ 'url' ] = Kanban_Template::get_uri();

		if ( ! is_null( $atts[ 'id' ] ) ) {
			$atts[ 'url' ] = add_query_arg(
				array(
					'board_id' => $atts[ 'id' ]
				),
				$atts[ 'url' ]
			);
		}



		echo $atts[ 'url' ];
	}



	static function render_link( $atts, $content ) {

		$defaults = array(
			'id'         => null,
			'class'      => null,
			'target'     => null,
			'attributes' => array()
		);

		$atts = shortcode_atts( $defaults, $atts );



		$atts[ 'url' ] = Kanban_Template::get_uri();

		if ( ! is_null( $atts[ 'id' ] ) ) {
			$atts[ 'url' ] = add_query_arg(
				array(
					'board_id' => $atts[ 'id' ]
				),
				$atts[ 'url' ]
			);
		}



		// Build attributes.
		$attributes = array();

		if ( ! empty( $atts[ 'class' ] ) ) {
			$attributes[] = sprintf( 'class="%s"', esc_html( $atts[ 'class' ] ) );
		}

		if ( ! empty( $atts[ 'target' ] ) ) {
			$attributes[] = sprintf( 'target="%s"', esc_html( $atts[ 'target' ] ) );
		}

		$atts[ 'attributes' ] = implode( ' ', $attributes );



		include plugin_dir_path( __FILE__ ) . '/templates/board-link.php';
	}




	static function check_for_core() {
		if ( class_exists( 'Kanban' ) ) {
			return TRUE;
		}

		if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
			require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
		}

		if ( is_plugin_active_for_network( self::$plugin_basename ) ) {
			add_action( 'network_admin_notices',  array( __CLASS__, 'admin_deactivate_notice' ) );
		}
		else {
			add_action( 'admin_notices', array( __CLASS__, 'admin_deactivate_notice' ) );
		}



		deactivate_plugins( self::$plugin_basename );

		return FALSE;
	}



	static function admin_deactivate_notice() {
		if ( !is_admin() ) {
			return;
		}
		?>
		<div class="error below-h2">
			<p>
				<?php
				echo sprintf(
					__('Whoops! This plugin %s requires the <a href="https://wordpress.org/plugins/kanban/" target="_blank">Kanban for WordPress</a> plugin.
	            		Please make sure it\'s installed and activated.'
					),
					self::$friendlyname
				);
				?>
			</p>
		</div>
		<?php
	}
}



function Kanban_Shortcodes() {
	Kanban_Shortcodes::init();
}



add_action( 'plugins_loaded', 'Kanban_Shortcodes', 20, 0 );