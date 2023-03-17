<?php
/**
 * Plugin Name: Post Type Author
 * Plugin URI: https://github.com/cr0ybot/post-type-author
 * Description: Force selected post types to always have the same chosen author.
 * Version: 0.1.0
 * Requires at least: 4.7
 * Requires PHP: 5.6
 * Author: Cory Hughart
 * Author URI: https://coryhughart.com
 * License: GPL-3.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: post-type-author
 *
 * @package post-type-author
 */

namespace cr0ybot\PostTypeAuthor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the plugin settings.
 */
function register_settings() {
	add_settings_section(
		'post_type_author_settings_section',
		__( 'Post Type Author Settings', 'post-type-author' ),
		__NAMESPACE__ . '\settings_section_callback',
		'writing',
		array(
			// Add an anchor for the plugin action link to land on.
			'before_section' => '<section id="post-type-author">',
			'after_section'  => '</section>',
		)
	);

	$post_types = get_post_types( array( 'public' => true ), 'objects' );

	foreach ( $post_types as $post_type ) {
		add_settings_field(
			"post_type_author_{$post_type->name}",
			// translators: %s is the post type label.
			sprintf( __( 'Default author for %s', 'post-type-author' ), $post_type->label ),
			__NAMESPACE__ . '\settings_field_callback',
			'writing',
			'post_type_author_settings_section',
			array(
				'post_type' => $post_type,
			)
		);

		register_setting(
			'writing',
			"post_type_author_{$post_type->name}",
			array(
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
			)
		);
	}
}

add_action( 'admin_init', __NAMESPACE__ . '\register_settings' );

/**
 * Settings section callback.
 */
function settings_section_callback() {
	echo '<p>' . esc_html( __( 'Choose a default author for any post type below:', 'post-type-author' ) ) . '</p>';
}

/**
 * Settings field callback.
 *
 * @param array $args An array of arguments.
 */
function settings_field_callback( $args ) {
	$post_type         = $args['post_type'];
	$default_author_id = get_option( "post_type_author_{$post_type->name}" );
	$users             = get_users( array( 'capability' => 'edit_posts' ) );

	echo '<select name="post_type_author_' . esc_attr( $post_type->name ) . '">';
	echo '<option value="">' . esc_html( __( 'No author selected', 'post-type-author' ) ) . '</option>';

	foreach ( $users as $user ) {
		echo '<option value="' . esc_attr( $user->ID ) . '"' . selected( $default_author_id, $user->ID, false ) . '>' . esc_html( $user->display_name ) . '</option>';
	}

	echo '</select>';
}

/**
 * Add a plugin action link to the settings page.
 *
 * @param array $links An array of plugin action links.
 */
function plugin_action_links( $links ) {
	$links[] = '<a href="' . admin_url( 'options-writing.php#post-type-author' ) . '">' . __( 'Settings', 'post-type-author' ) . '</a>';
	return $links;
}

add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), __NAMESPACE__ . '\plugin_action_links' );

/**
 * Flash an animated highlight around the post type author settings section on
 * options-writing screen if window.hash is #post-type-author.
 */
function highlight_settings_section() {
	$screen = get_current_screen();

	if ( 'options-writing' !== $screen->id ) {
		return;
	}

	?>
	<script>
		( function() {
			var hash = window.location.hash;

			if ( '#post-type-author' === hash ) {
				var section = document.querySelector( '#post-type-author' );

				if ( section ) {
					section.classList.add( 'flash' );
				}
			}
		} )();
	</script>
	<style>
		#post-type-author.flash {
			animation: 0.5s ease-in-out 3 flash;
		}

		@keyframes flash {
			0% {
				background-color: transparent;
				outline: 0px solid transparent;
			}
			50% {
				background-color: #f0f6fc;
				outline: 10px solid #f0f6fc;
			}
			100% {
				background-color: transparent;
				outline: 0px solid transparent;
			}
		}
	</style>
	<?php
}

add_action( 'admin_footer', __NAMESPACE__ . '\highlight_settings_section' );

/**
 * Set the post author based on the default author for the post type.
 *
 * @param int     $post_id The post ID.
 * @param WP_Post $post    The post object.
 */
function set_post_author( $post_id, $post ) {
	if ( 'revision' === $post->post_type ) {
		return;
	}

	$default_author_id = get_option( "post_type_author_{$post->post_type}" );

	if ( ! empty( $default_author_id ) ) {
		if ( $default_author_id === $post->post_author ) {
			return;
		}

		$user = get_user_by( 'id', $default_author_id );

		if ( $user && ! is_wp_error( $user ) ) {
			// Unhook this function so it doesn't loop infinitely.
			remove_action( 'save_post', __NAMESPACE__ . '\set_post_author', 10, 2 );

			wp_update_post(
				array(
					'ID'          => $post_id,
					'post_author' => $default_author_id,
				)
			);

			// Re-hook this function.
			add_action( 'save_post', __NAMESPACE__ . '\set_post_author', 10, 2 );
		} else {
			error_log( "Post Type Author: Invalid user ID {$default_author_id} for post type {$post->post_type}." );
		}
	}
}

add_action( 'save_post', __NAMESPACE__ . '\set_post_author', 10, 2 );
