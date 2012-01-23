<?php
class WMP_Widget extends WP_Widget {
	public function __construct() {
		parent::WP_Widget( 'wmp_widget', 'WP Most Popular', array( 'description' => 'Display your most popular blog posts on your sidebar' ) );
	}
	
	public function form( $instance ) {
		$defaults = $this->default_options( $instance );
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>">Title:</label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $defaults['title']; ?>">
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'number' ); ?>">Number of posts to show:</label>
			<input id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" type="text" value="<?php echo $defaults['number']; ?>" size="3">
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'timeline' ); ?>">Timeline:</label>
			<select id="<?php echo $this->get_field_id( 'timeline' ); ?>" name="<?php echo $this->get_field_name( 'timeline' ); ?>">
				<option value="all_time"<?php if ( $defaults['timeline'] == 'all_time' ) echo "selected"; ?>>All time</option>
				<option value="monthly"<?php if ( $defaults['timeline'] == 'monthly' ) echo "selected"; ?>>Past month</option>
				<option value="weekly"<?php if ( $defaults['timeline'] == 'weekly' ) echo "selected"; ?>>Past week</option>
				<option value="daily"<?php if ( $defaults['timeline'] == 'daily' ) echo "selected"; ?>>Today</option>
			</select>
		</p>
		<?php
	}
	
	private function default_options( $instance ) {
		if ( isset( $instance[ 'title' ] ) )
			$options['title'] = esc_attr( $instance[ 'title' ] );
		else
			$options['title'] = 'Popular posts';
			
		if ( isset( $instance[ 'number' ] ) )
			$options['number'] = (int) $instance[ 'number' ];
		else
			$options['number'] = 5;
			
		if ( isset( $instance[ 'timeline' ] ) )
			$options['timeline'] = esc_attr( $instance[ 'timeline' ] );
		else
			$options['timeline'] = 'all';
		
		return $options;
	}
	
	public function update( $new, $old ) {
		$instance = wp_parse_args( $new, $old );
		return $instance;
	}
	
	public function widget( $args, $instance ) {
		// Find default args
		extract( $args );
		
		// Get our posts
		$defaults	= $this->default_options( $instance );
		$posts		= wmp_get_popular( array( 'limit' => (int) $defaults[ 'number' ], 'range' => $defaults['timeline'] ) );
		
		// Display the widget
		echo $before_widget;
		if ( $defaults['title'] ) echo $before_title . $defaults['title'] . $after_title;
		echo '<ul>';
		global $post;
		foreach ( $posts as $post ):
			setup_postdata( $post );
			?>
			<li><a href="<?php the_permalink() ?>" title="<?php echo esc_attr(get_the_title() ? get_the_title() : get_the_ID()); ?>"><?php if ( get_the_title() ) the_title(); else the_ID(); ?></a></li>
			<?php
		endforeach;
		echo '</ul>';
		echo $after_widget;
		
		// Reset post data
		wp_reset_postdata();
	}
}