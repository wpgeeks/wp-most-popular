<?php
class WMP_track {
	private $post_id = NULL;
	
	public function __construct( $post_id ) {
		$this->post_id = $post_id;
		
		// Action to update stats
		$this->update_stats();
	}
	
	private function update_stats() {
		global $wpdb;
		
		if ( $this->post_id ) {
			// Get the existing raw stats
			$raw_stats = $wpdb->get_var( $wpdb->prepare( "SELECT raw_stats FROM {$wpdb->prefix}most_popular WHERE post_id = '%d'", array( $this->post_id ) ) );
			$date = gmdate('Y-m-d');
			
			if ( $raw_stats ) {
				$raw_stats = unserialize( $raw_stats );
			} else {
				// Create a entry for this post
				$wpdb->query( $wpdb->prepare( "INSERT INTO {$wpdb->prefix}most_popular (post_id, last_updated, 1_day_stats, 7_day_stats, 30_day_stats, all_time_stats, raw_stats) VALUES ('%d', NOW(), '0', '0', '0', '0', '')", array( $this->post_id ) ) );
			}
			
			$count_1 = $this->calculate_1_day_stats( $raw_stats, 1 );
			$count_7 = $this->calculate_7_day_stats( $raw_stats, 7 );
			$count_30 = $this->calculate_30_day_stats( $raw_stats, 30 );
			
			if ( isset( $row_stats ) && count( $raw_stats ) >= 30 ) {
				array_shift( $raw_stats );
				$raw_stats[$date] = 1;
			} else {
				if ( ! isset( $raw_stats[$date] ) ) {
					$raw_stats[$date] = 1;
				} else {
					$raw_stats[$date]++;
				}
			} 
			
			// Update our table with new figures
			$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}most_popular SET 1_day_stats = '{$count_1}', 7_day_stats = '{$count_7}', 30_day_stats = '{$count_30}', all_time_stats = all_time_stats + 1, raw_stats = '%s' WHERE post_id = '%d'", array( serialize( $raw_stats ), $this->post_id ) ) );
		}
	}
	
	/**
	 * Calculate stats for 'n' days from today's date
	 *
	 * @param [int] $existing_stats
	 * @param [int] $days
	 * @return int
	 */
	private function calculate_stats( $existing_stats, $days ) {
		if ( $existing_stats ) {
				$date = gmdate('Y-m-d');
				$current_stats = 0;

				if ( isset( $existing_stats[$date] ) ) {
						$current_stats = $existing_stats[$date];

						if ( $days == 1 ) {
								return $current_stats + 1;
						}
				}

				for ( $i = 1; $i < $days; $i++ ) {
						$old_date = date('Y-m-d', strtotime( "-{$i} days" ) );

						if ( isset($existing_stats[$old_date] ) ) {
								$current_stats += $existing_stats[$old_date];
						}
				}

				return $current_stats + 1;
		}

		return 1;
	}
}