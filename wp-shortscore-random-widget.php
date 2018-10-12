<?php

/**
 * Plugin Name: SHORTSCORE Random Game Widget
 * Description: Displays a random SHORTSCORE-rated game
 * Plugin URI: https://marc.tv/shortscore-wp-plugin/
 * GitHub Plugin URI: mtoensing/wp-shortscore-random-widget
 * Version: 1.4
 */


class ShortscoreWidget extends WP_Widget {

    private $cache = false;

	public function __construct() {
		$widget_ops = array(
			'classname'   => 'shortscore-widget',
			'description' => ' Displays a random SHORTSCORE-rated game.'
		);

		/* Create the widget. */
		parent::__construct( 'shortscore-widget', 'SHORTSCORE Game', $widget_ops );
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
        if($this->cache == true){
            if ( false === ( $shortscore_transient_link = get_transient( 'shortscore_transient_link' ) ) ) {
                // It wasn't there, so regenerate the data and save the transient
                $shortscore_transient_link = $this->getGameLink();
                set_transient( 'shortscore_transient_link', $shortscore_transient_link, 300 );
            }
        } else {
	        $shortscore_transient_link = $this->getGameLink();
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

			if(! is_object($result) ){
				$result = json_decode(json_encode($result));
            }

			$game_title = $result->game->title;
		}

		if ( $game_title == '' ) {
			$game_title = get_the_title( $post_id );
		}

		$link = '<a href="' . get_permalink( $post_id ) . '">' . $game_title . '</a>';

		return $link;
	}

	public function getRandomGame() {

		$args = array(
			'numberposts' => 1,
			'meta_query'  => [
				[
					'key'     => '_shortscore_user_rating',
					'value'   => 2,
					'type'    => 'numeric',
					'compare' => '>',
				],
			],
			'orderby'     => 'rand'
		);

		$games = get_posts( $args );

		return $games[0];
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
	register_widget( "ShortscoreWidget" );
}

add_action( 'widgets_init', 'register_shortscore_widget' );

?>