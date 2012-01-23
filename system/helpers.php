<?php
function wmp_get_popular( $args = array() ) {
	global $wpdb;
	
	// Default arguments
	$limit = 5;
	$post_type = 'post';
	$range = 'all_time';
	
	if ( isset( $args['limit'] ) ) {
		$limit = $args['limit'];
	}
	
	if ( isset( $args['post_type'] ) ) {
		$post_type = $args['post_type'];
	}
	
	if ( isset( $args['range'] ) ) {
		$range = $args['range'];
	}
	
	switch( $range ) {
		CASE 'all_time':
			$order = "ORDER BY all_time_stats DESC";
			break;
		CASE 'monthly':
			$order = "ORDER BY 30_day_stats DESC";
			break;
		CASE 'weekly':
			$order = "ORDER BY 7_day_stats DESC";
			break;
		CASE 'daily':
			$order = "ORDER BY 1_day_stats DESC";
			break;
		DEFAULT:
			$order = "ORDER BY all_time_stats DESC";
			break;
	}
	
	$result = $wpdb->get_results( $wpdb->prepare( "
		SELECT p.*
		FROM {$wpdb->prefix}most_popular mp
		INNER JOIN {$wpdb->prefix}posts p ON mp.post_id = p.ID
		WHERE
			p.post_type = '%s' AND
			p.post_status = 'publish'
		{$order}
		LIMIT %d
	", array( $post_type, $limit ) ), OBJECT );
	
	if ( ! $result) {
		return array();
	}
	return $result;
}