<?php
/*
	Plugin Name: WP Most Popular
	Plugin URI: https://wpgeeks.com/product/wp-most-popular/
	Description: Flexible plugin to show your most popular posts based on views
	Version: 0.3.1
	Author: WP Geeks
	Author URI: http://wpgeeks.com
	License: GPL2

	Copyright 2011 WP Geeks (email: support@wpgeeks.com)

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

if ( phpversion() > 5 ) {
	// Setup our path
	define( 'WMP_PATH', dirname(__FILE__) . '/' );

	// Setup activation and deactivation
	register_activation_hook( WP_PLUGIN_DIR . '/wp-most-popular/wp-most-popular.php', 'WMP_system::install' );
	register_deactivation_hook( WP_PLUGIN_DIR . '/wp-most-popular/wp-most-popular.php', 'WMP_system::uninstall' );

	// Include our helpers
	include_once( WMP_PATH . 'system/helpers.php' );

	// Class for installation and uninstallation
	class WMP_system{
		public static function actions() {
			// Check for token
			if ( ! wp_verify_nonce( $_POST['token'], 'wmp_token' ) ) die();

			include_once( WMP_PATH . 'system/track.php' );
			$track = new WMP_track( intval( $_POST['id'] ) );
		}

		public static function install() {
			include_once( WMP_PATH . 'system/setup.php' );
			WMP_setup::install();
		}

		public static function javascript() {
			global $wp_query;
			wp_reset_query();
			wp_print_scripts('jquery');
			$token = wp_create_nonce( 'wmp_token' );
			if ( ! is_front_page() && ( is_page() || is_single() ) ) {
				echo '<!-- WordPress Most Popular --><script type="text/javascript">/* <![CDATA[ */ jQuery.post("' . admin_url('admin-ajax.php') . '", { action: "wmp_update", id: ' . $wp_query->post->ID . ', token: "' . $token . '" }); /* ]]> */</script><!-- /WordPress Most Popular -->';
			}
		}

		public static function uninstall() {
			include_once( WMP_PATH . 'system/setup.php' );
			WMP_setup::uninstall();
		}

		public static function widget() {
			register_widget( 'WMP_Widget' );
		}
	}

	// Use ajax for tracking popular posts
	add_action( 'wp_head', 'WMP_system::javascript' );
	add_action( 'wp_ajax_wmp_update', 'WMP_system::actions' );
	// Comment out to stop logging stats for admin and logged in users
	add_action( 'wp_ajax_nopriv_wmp_update', 'WMP_system::actions' );

	// Widget
	include_once( WMP_PATH . 'system/widget.php' );
	add_action( 'widgets_init', 'WMP_system::widget' );
	add_action( 'wp_most_popular_list_item', 'WMP_Widget::list_items', 10, 2 );
}

/**
 * Callback function that embeds our resource in a WP_REST_Response
 */
function wp_most_popular_get_statistics( $request ) {
	$limit = 5;
	$range = 'all_time';
	$query_params = $request->get_params();

	if ( isset( $query_params['limit'] ) ) {
		$limit = $query_params['limit'];
	}

	if ( isset( $query_params['range'] ) ) {
		$range = $query_params['range'];
	}

	$args = array(
		'limit' => $limit,
		'post_type' => 'post',
		'range' => $range,
	);

	$posts = wp_most_popular_get_popular( $args );

	if ( empty($posts) ) {
		return new WP_Error( 'wp-most-popular', __( 'No data could be found.', 'wp-most-popular' ), rest_ensure_response( $query_params ) );
	}

	return rest_ensure_response( $posts );
}

/**
 * Validates our request argument based on details registered to the route.
 *
 * @param  mixed            $value   Value of the 'limit' argument.
 * @param  WP_REST_Request  $request The current request object.
 * @param  string           $param   Key of the parameter. In this case it is 'limit'.
 * @return WP_Error|boolean
 */
function wp_most_popular_validate_limit( $value, $request, $param ) {
	$attributes = $request->get_attributes();

	if ( isset( $attributes['args'][$param] ) ) {
		$argument = $attributes['args'][$param];

		// Check that our argument is an int
		if ( 'integer' === $argument['type'] && ! is_int($value) ) {
				return new WP_Error( 'rest_invalid_param', sprintf( esc_html__( '%1$s is not of type %2$s', 'wp-most-popular' ), $param, 'integer' ), array( 'status' => 400 ) );
		}
	}

	return true;
}

function wp_most_popular_sanitize_limit( $value, $request, $param ) {
	return absint( intval( $value ) );
}

/**
 * Validates our request argument based on details registered to the route.
 *
 * @param  mixed            $value   Value of the 'range' argument.
 * @param  WP_REST_Request  $request The current request object.
 * @param  string           $param   Key of the parameter. In this case it is 'range'.
 * @return WP_Error|boolean
 */
function wp_most_popular_validate_range( $value, $request, $param ) {
	$attributes = $request->get_attributes();

	if ( isset( $attributes['args'][$param] ) ) {
		$argument = $attributes['args'][$param];

		if ( 'string' === $argument['type'] && ! is_string( $value ) ) {
				return new WP_Error( 'rest_invalid_param', sprintf( esc_html__( '%1$s is not of type %2$s', 'wp-most-popular' ), $param, 'string' ), array( 'status' => 400 ) );
		}
	}

	// Grab the range param schema.
	$args = $attributes['args'][ $param ];

	// If the range param is not a value in our enum then we should return an error as well.
	if ( ! in_array( $value, $args['enum'], true ) ) {
		return new WP_Error( 'rest_invalid_param', sprintf( __( '%s is not one of %s' ), $param, implode( ', ', $args['enum'] ) ), array( 'status' => 400 ) );
	}

	return true;
}

function wp_most_popular_sanitize_range( $value, $request, $param ) {
	return sanitize_text_field( $value );
}

/**
 * Arguments for the posts endpoint
 */
function wp_most_popular_get_statistics_arguments() {
	$args = array();

	// Schema for the limit argument
	$args['limit'] = array(
		'description' => esc_html__( 'Number of posts to be returned.', 'wp-most-popular' ),
		'type' => 'int',
		'default' => 5,
		'validate_callback' => 'wp_most_popular_validate_limit',
		'sanitize_callback' => 'wp_most_popular_sanitize_limit',
	);

	// Scheam for the range argument
	$args['range'] = array(
		'description' => esc_html__( 'Obtain statistics based upon a time range', 'wp-most-popular' ),
		'type' => 'string',
		'enum' => array( 'all_time', 'daily', 'weekly', 'monthly' ),
		'default' => 'all_time',
		'validate_callback' => 'wp_most_popular_validate_range',
		'sanitize_callback' => 'wp_most_popular_sanitize_range',
	);

	return $args;
}

function wp_most_popular_register_routes() {
	$NAMESPACE = 'wp-most-popular/v1';

	register_rest_route( $NAMESPACE, '/posts', array(
			array('methods'  => WP_REST_Server::READABLE,
			'callback' => 'wp_most_popular_get_statistics',
			'args' => wp_most_popular_get_statistics_arguments(),
			),
	) );
}

add_action( 'rest_api_init', 'wp_most_popular_register_routes' );