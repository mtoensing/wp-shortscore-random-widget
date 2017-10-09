<?php

/**
 * Plugin Name: SHORTSCORE Random Game Widget
 * Description: Displays a random SHORTSCORE-rated game
 * Plugin URI: https://marc.tv/shortscore-wp-plugin/
 * Version: 1.0
 */


class ShortscoreWidget extends WP_Widget {

	public function __construct() {
		$widget_ops = array( 'classname'   => 'shortscore-widget',
		                     'description' => ' Displays a random SHORTSCORE-rated game.'
		);
		/* Create the widget. */
		parent::__construct(
			'shortscore-widget',
			'SHORTSCORE Game',
			$widget_ops
		);
	}

	function widget( $args, $instance ) {
		// PART 1: Extracting the arguments + getting the values
		extract( $args, EXTR_SKIP );
		$title = empty( $instance['title'] ) ? ' ' : apply_filters( 'widget_title', $instance['title'] );
		$text  = empty( $instance['text'] ) ? '' : $instance['text'];

		// Before widget code, if any
		echo( isset( $before_widget ) ? $before_widget : '' );

		// PART 2: The title and the text output
		if ( ! empty( $title ) ) {
			echo $before_title . $title . $after_title;
		};
        
		// Get any existing copy of our transient data
		if ( false === ( $shortscore_transient_link = get_transient( 'shortscore_transient_link' ) ) ) {
			// It wasn't there, so regenerate the data and save the transient
			$shortscore_transient_link = $this->getGameLink();
			set_transient( 'shortscore_transient_link', $shortscore_transient_link,  HOUR_IN_SECONDS );
		}

		echo $shortscore_transient_link;

		// After widget code, if any
		echo( isset( $after_widget ) ? $after_widget : '' );
	}

	public function getGameLink() {

		$post = $this->getRandomGame();

		$post_id = $post->ID;

		if ( function_exists( 'get_post_meta' ) && get_post_meta( $post_id, '_shortscore_result', true ) != '' ) {

			$result = get_post_meta( $post_id, '_shortscore_result', true );

			$game_title = $result->game->title;

		} else {
			$game_title = __( 'Sorry, no SHORTSCORE-rated games found.' );
		}

		$link = '<a href="' . get_permalink( $post_id ) . '">' . $game_title . '</a>';

		return $link;
	}

	public function getRandomGame() {
		$args = array(
			'numberposts' => 1,
			'category'    => 58,
			'meta_key'    => '_shortscore_result',
			'orderby'     => 'rand'
		);

		$game = get_posts( $args );

		return $game[0];
	}

	public function form( $instance ) {

		// PART 1: Extract the data from the instance variable
		$instance = wp_parse_args( (array) $instance, array( 'title' => '' ) );
		$title    = $instance['title'];
		//$text     = $instance['text'];

		// PART 2-3: Display the fields
		?>
        <!-- PART 2: Widget Title field START -->
        <p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>">Title:
                <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>"
                       name="<?php echo $this->get_field_name( 'title' ); ?>" type="text"
                       value="<?php echo esc_attr( $title ); ?>"/>
            </label>
        </p>
        <!-- Widget Title field END -->

        <!-- PART 3: Widget Text field START -->
        <!--
        <p>
            <label for="<?php echo $this->get_field_id( 'text' ); ?>">Text:
                <input class="widefat" id="<?php echo $this->get_field_id( 'text' ); ?>"
                       name="<?php echo $this->get_field_name( 'text' ); ?>" type="text"
                       value="<?php echo esc_attr( $text ); ?>" />
            </label>
        </p>
        -->
        <!-- Widget Text field END -->
		<?php

	}

	function update( $new_instance, $old_instance ) {
		$instance          = $old_instance;
		$instance['title'] = $new_instance['title'];
		$instance['text']  = $new_instance['text'];

		return $instance;
	}

}

// register Shortscore widget

function register_shortscore_widget() {
	register_widget("ShortscoreWidget");
}

add_action( 'widgets_init', 'register_shortscore_widget' );

?>