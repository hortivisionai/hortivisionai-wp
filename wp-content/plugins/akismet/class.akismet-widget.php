<?php
/**
 * @package Akismet
 */
<<<<<<< HEAD

// We plan to gradually remove all of the disabled lint rules below.
// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped

/**
 * Akismet Widget Class
 */
class Akismet_Widget extends WP_Widget {

	/**
	* Constructor
	*/
	function __construct() {
		parent::__construct(
			'akismet_widget',
			__( 'Akismet Widget', 'akismet' ),
			array( 'description' => __( 'Display the number of spam comments Akismet has caught', 'akismet' ) )
		);
	}

	/**
	 * Outputs the widget settings form
	 *
	 * @param array $instance The widget options
	 */
	public function form( $instance ) {
		if ( $instance && isset( $instance['title'] ) ) {
			$title = $instance['title'];
		} else {
			$title = __( 'Spam Blocked', 'akismet' );
		}
		?>

		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php esc_html_e( 'Title:', 'akismet' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>

		<?php
	}

	/**
	 * Updates the widget settings
	 *
	 * @param array $new_instance New widget instance
	 * @param array $old_instance Old widget instance
	 * @return array Updated widget instance
	 */
	public function update( $new_instance, $old_instance ) {
		$instance          = array();
		$instance['title'] = sanitize_text_field( $new_instance['title'] );
		return $instance;
	}

	/**
	 * Outputs the widget content
	 *
	 * @param array $args Widget arguments
	 * @param array $instance Widget instance
	 */
	public function widget( $args, $instance ) {
		$count = get_option( 'akismet_spam_count' );

		if ( ! isset( $instance['title'] ) ) {
			$instance['title'] = __( 'Spam Blocked', 'akismet' );
		}

=======
class Akismet_Widget extends WP_Widget {

	function __construct() {
		load_plugin_textdomain( 'akismet' );
		
		parent::__construct(
			'akismet_widget',
			__( 'Akismet Widget' , 'akismet'),
			array( 'description' => __( 'Display the number of spam comments Akismet has caught' , 'akismet') )
		);

		if ( is_active_widget( false, false, $this->id_base ) ) {
			add_action( 'wp_head', array( $this, 'css' ) );
		}
	}

	function css() {
?>

<style type="text/css">
.a-stats {
	width: auto;
}
.a-stats a {
	background: #7CA821;
	background-image:-moz-linear-gradient(0% 100% 90deg,#5F8E14,#7CA821);
	background-image:-webkit-gradient(linear,0% 0,0% 100%,from(#7CA821),to(#5F8E14));
	border: 1px solid #5F8E14;
	border-radius:3px;
	color: #CFEA93;
	cursor: pointer;
	display: block;
	font-weight: normal;
	height: 100%;
	-moz-border-radius:3px;
	padding: 7px 0 8px;
	text-align: center;
	text-decoration: none;
	-webkit-border-radius:3px;
	width: 100%;
}
.a-stats a:hover {
	text-decoration: none;
	background-image:-moz-linear-gradient(0% 100% 90deg,#6F9C1B,#659417);
	background-image:-webkit-gradient(linear,0% 0,0% 100%,from(#659417),to(#6F9C1B));
}
.a-stats .count {
	color: #FFF;
	display: block;
	font-size: 15px;
	line-height: 16px;
	padding: 0 13px;
	white-space: nowrap;
}
</style>

<?php
	}

	function form( $instance ) {
		if ( $instance ) {
			$title = $instance['title'];
		}
		else {
			$title = __( 'Spam Blocked' , 'akismet');
		}
?>

		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php esc_html_e( 'Title:' , 'akismet'); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>

<?php
	}

	function update( $new_instance, $old_instance ) {
		$instance['title'] = strip_tags( $new_instance['title'] );
		return $instance;
	}

	function widget( $args, $instance ) {
		$count = get_option( 'akismet_spam_count' );

>>>>>>> 5a18ec73027fbaf1a0c22718fce45ac95a328f94
		echo $args['before_widget'];
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'];
			echo esc_html( $instance['title'] );
			echo $args['after_title'];
		}
<<<<<<< HEAD
		?>

		<style>
			.a-stats {
				--akismet-color-mid-green: #357b49;
				--akismet-color-white: #fff;
				--akismet-color-light-grey: #f6f7f7;

				max-width: 350px;
				width: auto;
			}

			.a-stats * {
				all: unset;
				box-sizing: border-box;
			}

			.a-stats strong {
				font-weight: 600;
			}

			.a-stats a.a-stats__link,
			.a-stats a.a-stats__link:visited,
			.a-stats a.a-stats__link:active {
				background: var(--akismet-color-mid-green);
				border: none;
				box-shadow: none;
				border-radius: 8px;
				color: var(--akismet-color-white);
				cursor: pointer;
				display: block;
				font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen-Sans', 'Ubuntu', 'Cantarell', 'Helvetica Neue', sans-serif;
				font-weight: 500;
				padding: 12px;
				text-align: center;
				text-decoration: none;
				transition: all 0.2s ease;
			}

			/* Extra specificity to deal with TwentyTwentyOne focus style */
			.widget .a-stats a.a-stats__link:focus {
				background: var(--akismet-color-mid-green);
				color: var(--akismet-color-white);
				text-decoration: none;
			}

			.a-stats a.a-stats__link:hover {
				filter: brightness(110%);
				box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06), 0 0 2px rgba(0, 0, 0, 0.16);
			}

			.a-stats .count {
				color: var(--akismet-color-white);
				display: block;
				font-size: 1.5em;
				line-height: 1.4;
				padding: 0 13px;
				white-space: nowrap;
			}
		</style>

		<div class="a-stats">
			<a href="https://akismet.com?utm_source=akismet_plugin&amp;utm_campaign=plugin_static_link&amp;utm_medium=in_plugin&amp;utm_content=widget_stats" class="a-stats__link" target="_blank" rel="noopener" style="background-color: var(--akismet-color-mid-green); color: var(--akismet-color-white);">
				<?php

				echo wp_kses(
					sprintf(
					/* translators: The placeholder is the number of pieces of spam blocked by Akismet. */
						_n(
							'<strong class="count">%1$s spam</strong> blocked by <strong>Akismet</strong>',
							'<strong class="count">%1$s spam</strong> blocked by <strong>Akismet</strong>',
							$count,
							'akismet'
						),
						number_format_i18n( $count )
					),
					array(
						'strong' => array(
							'class' => true,
						),
					)
				);

				?>
			</a>
		</div>

		<?php
=======
?>

	<div class="a-stats">
		<a href="http://akismet.com" target="_blank" title=""><?php printf( _n( '<strong class="count">%1$s spam</strong> blocked by <strong>Akismet</strong>', '<strong class="count">%1$s spam</strong> blocked by <strong>Akismet</strong>', $count , 'akismet'), number_format_i18n( $count ) ); ?></a>
	</div>

<?php
>>>>>>> 5a18ec73027fbaf1a0c22718fce45ac95a328f94
		echo $args['after_widget'];
	}
}

<<<<<<< HEAD
/**
 * Register the Akismet widget
 */
=======
>>>>>>> 5a18ec73027fbaf1a0c22718fce45ac95a328f94
function akismet_register_widgets() {
	register_widget( 'Akismet_Widget' );
}

add_action( 'widgets_init', 'akismet_register_widgets' );
