<?php
/*
Contributors:		gelform
Plugin Name:		Kanban: Shortcodes
Plugin URI:			https://kanbanwp.com/addons/shortcodes/
Description:		Embed your Kanban board on another page, or display a filtered to-do list.
Requires at least:	4.0
Tested up to:		4.6.1
Version:			0.0.1
Release Date:		October 6, 2018
Author:				Gelform Inc
Author URI:			http://gelwp.com
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
if ( !defined( 'ABSPATH' ) ) exit;



class Kanban_Shortcodes
{
	static $views = array();



	static function init() {

		register_activation_hook( __FILE__, array( __CLASS__, 'check_for_core' ) );

		add_action( 'admin_init', array( __CLASS__, 'check_for_core' ) );



		// Build list of views
		$methods = get_class_methods( __CLASS__ );

		foreach ( $methods as $method ) {
			if ( strpos( $method, 'render_' ) !== FALSE ) {
				self::$views[] = str_replace( 'render_', '', $method );
			}
		}


		add_shortcode( 'kanban', array( __CLASS__, 'shortcode_parser' ) );
	}



	static function shortcode_parser( $atts ) {

		$view = 'board';

		foreach ( $atts as $key => $val ) {
			if ( is_numeric( $key ) ) {
				if ( in_array( $val, self::$views ) ) {
					$view = $val;
					break;
				}
			}
		}

		$func = 'render_' . $view;

		if ( !method_exists( __CLASS__, $func ) ) {
			return;
		}

		self::$func( $atts );
	}



	static function render_list_order_by_user( $atts ) {
	}



	static function render_list_order_by_board( $atts ) {


		// Get all tasks.
		$tasks = Kanban_Task::get_all();



		// Get all boards, by id.
		$boards = Kanban_Board::get_all();
		$boards_in_order = Kanban_Utils::order_array_of_objects_by_property( $boards, 'position', 'int' );



		// Get statuses for each board.
		foreach ( $boards as $board ) {
			$board->statuses = Kanban_Status::get_all( $board->id );
		}



		// Add tasks to boards -> statuses.
		foreach ( $tasks as $task ) {

			// Filter our tasks
			if ( !is_null( $atts[ 'user' ] ) && $task->user_id_assigned != $atts[ 'user' ] ) {
				continue;
			}

			if ( !is_null( $atts[ 'board' ] ) && $task->board_id != $atts[ 'board' ] ) {
				continue;
			}

			if ( !is_null( $atts[ 'status' ] ) && $task->status_id != $atts[ 'status' ] ) {
				continue;
			}

			if ( !is_null( $atts[ 'project' ] ) && $task->project_id != $atts[ 'project' ] ) {
				continue;
			}



			// If board doesn't exist, skip it.
			if ( !isset( $boards[ $task->board_id ] ) ) {
				continue;
			}

			// If status doesn't exist, skip it.
			if ( !isset( $boards[ $task->board_id ]->statuses[ $task->status_id ] ) ) {
				continue;
			}

			// Add the array of tasks.
			if ( !isset( $boards[ $task->board_id ]->statuses[ $task->status_id ]->tasks ) ) {
				$boards[ $task->board_id ]->statuses[ $task->status_id ]->tasks = array();
			}

			// Add the task to the status.
			$boards[ $task->board_id ]->statuses[ $task->status_id ]->tasks[] = $task;

			// Add a task count to each board.
			if ( !isset( $boards[ $task->board_id ]->task_count ) ) {
				$boards[ $task->board_id ]->task_count = 0;
			}

			// Count the task.
			$boards[ $task->board_id ]->task_count++;

			if ( !isset( $boards[ $task->board_id ]->statuses[ $task->status_id ]->task_count ) ) {
				$boards[ $task->board_id ]->statuses[ $task->status_id ]->task_count = 0;
			}

			$boards[ $task->board_id ]->statuses[ $task->status_id ]->task_count++;

		}



		include plugin_dir_path( __FILE__ ) . '/templates/list-board.php';
	}



	static function render_list_order_by_project( $atts ) {
	}



	static function render_list( $atts ) {

		$defaults = array(
			'user' => NULL,
			'board' => NULL,
			'status' => NULL,
			'project' => NULL,
			'order' => 'board'
		);

		$atts = shortcode_atts( $defaults, $atts );



		$func = 'render_list_order_by_' . $atts[ 'order' ];

		if ( !method_exists( __CLASS__, $func ) ) {
			return;
		}

		self::$func( $atts );
	}



	static function render_board( $atts ) {

		$defaults = array(
			'id' => NULL,
			'css' => NULL,
			'width' => '100%',
			'height' => '400px'
		);

		$atts = shortcode_atts( $defaults, $atts );



		$url = Kanban_Template::get_uri();

		if ( !is_null( $atts[ 'id' ] ) ) {
			$url = add_query_arg(
				array(
					'board_id' => $atts[ 'id' ]
				),
				$url
			);
		}



		include plugin_dir_path( __FILE__ ) . '/templates/board-iframe.php';
	}



	static function check_for_core() {
		if ( !self::_is_parent_loaded() ) {
			deactivate_plugins( plugin_basename( __FILE__ ) );

			wp_die(
				__(
					'<p>
						Whoops! This plugin requires the Kanban for WordPress plugin.
	            		Please download it here: <a href="https://wordpress.org/plugins/kanban/" target="_blank">https://wordpress.org/plugins/kanban/</a>.</p>
	            	<p>',
					'kanban'
				),
				__( 'Error', 'kanban' ),
				array( 'back_link' => TRUE )
			);
		}
	}



	static function _is_parent_loaded() {
		return class_exists( 'Kanban' );
	}



	static function _is_parent_activated() {
		$active_plugins_basenames = get_option( 'active_plugins' );
		foreach ( $active_plugins_basenames as $plugin_basename ) {
			if ( false !== strpos( $plugin_basename, '/kanban.php' ) ) {
				return true;
			}
		}

		return false;
	}


}



function kanban_shortcodes_addon() {
	Kanban_Shortcodes::init();
}



if ( Kanban_Shortcodes::_is_parent_loaded() ) {
	// If parent plugin already included, init add-on.
	kanban_shortcodes_addon();
} else if ( Kanban_Shortcodes::_is_parent_activated() ) {
	// Init add-on only after the parent plugins is loaded.
	add_action( 'kanban_loaded', 'kanban_shortcodes_addon' );
} else {
	// Even though the parent plugin is not activated, execute add-on for activation / uninstall hooks.
	kanban_shortcodes_addon();
}
