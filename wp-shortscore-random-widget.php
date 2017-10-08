<?php

/**
 * Plugin Name: SHORTSCORE Random Game Widget
 * Description: Displays a random SHORTSCORE-rated game
 * Plugin URI: https://marc.tv/shortscore-wp-plugin/
 * Version: 1.0
 */


class My_SHORTSCORE_Widget extends WP_Widget {

	public function __construct() {
		$widget_ops = array('classname' => 'My_SHORTSCORE_Widget', 'description' => ' Displays a random SHORTSCORE-rated game.' );
		$this->WP_Widget('My_SHORTSCORE_Widget', 'SHORTSCORE Random Game Widget', $widget_ops);
	}

	function widget($args, $instance) {
		// PART 1: Extracting the arguments + getting the values
		extract($args, EXTR_SKIP);
		$title = empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);
		$text = empty($instance['text']) ? '' : $instance['text'];

		// Before widget code, if any
		echo (isset($before_widget)?$before_widget:'');

		// PART 2: The title and the text output
		if (!empty($title))
			echo $before_title . $title . $after_title;;
		if (!empty($text))
			echo $text;

		$game = $this->getRandomGame();
		$game_id = $game[0]->ID;

		if ( function_exists('get_post_meta') && get_post_meta($game_id, '_shortscore_result', true) != '' ) {

			$result = get_post_meta( $game_id, '_shortscore_result', true );

			$game_title = $result->game->title;

		} else {
			$game_title = _('Sorry, no SHORTSCORE-rated games found.');
		}

		echo '<a href="' . get_permalink($game_id) . '">' . $game_title . '</a>';

		// After widget code, if any
		echo (isset($after_widget)?$after_widget:'');
	}

	public function getRandomGame () {
		$args = array(
			'numberposts' => 1,
			'category'   => 58,
			'meta_key' => '_shortscore_result',
			'orderby' => 'rand'
		);

		$game = get_posts( $args );

		return $game;
	}

	public function form( $instance ) {

		// PART 1: Extract the data from the instance variable
		$instance = wp_parse_args( (array) $instance, array( 'title' => '' ) );
		$title = $instance['title'];
		$text = $instance['text'];

		// PART 2-3: Display the fields
		?>
        <!-- PART 2: Widget Title field START -->
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>">Title:
                <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>"
                       name="<?php echo $this->get_field_name('title'); ?>" type="text"
                       value="<?php echo attribute_escape($title); ?>" />
            </label>
        </p>
        <!-- Widget Title field END -->

        <!-- PART 3: Widget Text field START -->
        <p>
            <label for="<?php echo $this->get_field_id('text'); ?>">Text:
                <input class="widefat" id="<?php echo $this->get_field_id('text'); ?>"
                       name="<?php echo $this->get_field_name('text'); ?>" type="text"
                       value="<?php echo attribute_escape($text); ?>" />
            </label>
        </p>
        <!-- Widget Text field END -->
		<?php

	}

	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = $new_instance['title'];
		$instance['text'] = $new_instance['text'];
		return $instance;
	}

}

add_action( 'widgets_init', create_function('', 'return register_widget("My_SHORTSCORE_Widget");') );
?>