<?php
function wmp_get_popular( $args = array() ) {
	global $wpdb;
	
	// Default arguments
	$limit = 5;
	$post_type = 'post';
	$range = 'all_time';
	$terms = null;
	
	if ( isset( $args['limit'] ) ) {
		$limit = $args['limit'];
	}
	
	if ( isset( $args['post_type'] ) ) {
		$post_type = $args['post_type'];
	}
	
	if ( isset( $args['range'] ) ) {
		$range = $args['range'];
	}

	if ( isset( $args['terms'] ) && trim( $args['terms'] ) !== '' ) {
		$terms = explode( ',', $args['terms'] );
		array_walk( $terms, 'trim' );
		array_filter( $terms );
		$terms = implode( ',', $terms );
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

	if ( !$terms ) {
		$query = "
			SELECT p.*
			FROM {$wpdb->prefix}most_popular mp
			INNER JOIN {$wpdb->prefix}posts p ON mp.post_id = p.ID
			WHERE
				p.post_type = '%s' AND
				p.post_status = 'publish'
			{$order}
			LIMIT %d
		";
	} else {
		$query = "
			SELECT p.*
			FROM {$wpdb->prefix}most_popular mp
			INNER JOIN {$wpdb->prefix}posts p ON mp.post_id = p.ID
			INNER JOIN {$wpdb->prefix}term_relationships r ON p.ID = r.object_id
			WHERE
				p.post_type = '%s' AND
				p.post_status = 'publish' AND
				r.term_taxonomy_id IN ({$terms})
			GROUP BY p.ID
			{$order}
			LIMIT %d
		";
	}

	
	$result = $wpdb->get_results( $wpdb->prepare( $query, array( $post_type, $limit ) ), OBJECT );
	
	if ( ! $result) {
		return array();
	}
	return $result;
}