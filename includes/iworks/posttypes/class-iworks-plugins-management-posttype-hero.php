<?php
/**
 * Class for custom Post Type: HERO
 *
 * @since 1.0.0

Copyright 2025-PLUGIN_TILL_YEAR Marcin Pietrzak (marcin@iworks.pl)

this program is free software; you can redistribute it and/or modify
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

defined( 'ABSPATH' ) || exit;

require_once 'class-iworks-plugins-management-posttype.php';

class iworks_iworks_plugins_management_posttype_hero extends iworks_iworks_plugins_management_posttype_base {

	public function __construct() {
		parent::__construct();
		/**
		 * Post Type Name
		 *
		 * @since 1.0.0
		 */
		$this->posttype_name = preg_replace( '/^iworks_iworks_plugins_management_posttype_/', '', __CLASS__ );
		$this->register_class_custom_posttype_name( $this->posttype_name, 'iw' );
		/**
		 * WordPress Hooks
		 */
		add_action( 'add_meta_boxes_' . $this->posttypes_names[ $this->posttype_name ], array( $this, 'add_meta_boxes' ) );
		add_shortcode( 'opi_heroes', array( $this, 'get_list' ) );
	}

	/**
	 * class settings
	 *
	 * @since 1.0.0
	 */
	public function action_init_settings() {
	}

	/**
	 * Get post list
	 *
	 * @since 1.0.0
	 *
	 * @param array $atts Shortcode attributes
	 * @param string $content current content
	 *
	 * @return string $content
	 */
	public function get_list( $atts, $content = '' ) {
		$args      = array(
			'post_type'      => $this->posttype_name,
			'orderby'        => 'rand',
			'posts_per_page' => 2,
			'post_status'    => 'publish',
		);
		$the_query = new WP_Query( $args );
		/**
		 * No data!
		 */
		if ( ! $the_query->have_posts() ) {
			return $content;
		}
		/**
		 * Content
		 */
		$content .= '<div class="wp-block-group alignfull work-with-us work-with-us-heroes">';
		$content .= '<div class="wp-block-group__inner-container">';
		$content .= sprintf(
			'<h2>%s</h2>',
			esc_html__( 'Learn what our employees are saying.', 'iworks-plugins-management' )
		);
		$content .= sprintf(
			'<p class="become-one-of-them">%s</p>',
			esc_html__( 'Become one of them!', 'iworks-plugins-management' )
		);
		$content .= '<ul>';
		while ( $the_query->have_posts() ) {
			$the_query->the_post();
			$content .= sprintf( '<li class="%s">', implode( ' ', get_post_class() ) );
			$content .= sprintf( '<h3>%s</h3>', get_the_title() );
			$content .= '<div class="post-inner">';
			$content .= '<blockquote class="post-content">';
			$content .= get_the_content();
			$content .= '</blockquote>';
			$content .= '</div>';
			$content .= get_the_post_thumbnail( get_the_ID(), 'full' );
			$content .= '<div class="post-excerpt">';
			$content .= get_the_excerpt();
			$content .= '</div>';
			$content .= '</li>';
		}
		/* Restore original Post Data */
		wp_reset_postdata();
		$content .= '</ul>';
		$content .= '</div>';
		$content .= '</div>';
		return $content;
	}

	public function action_init_register_taxonomy() {}

	/**
	 * Register Custom Post Type
	 *
	 * @since 1.0.0
	 */
	public function action_init_register_post_type() {
		$labels = array(
			'name'                  => _x( 'Heroes', 'Post Type General Name', 'iworks-plugins-management' ),
			'singular_name'         => _x( 'Hero', 'Post Type Singular Name', 'iworks-plugins-management' ),
			'menu_name'             => __( 'Heroes', 'iworks-plugins-management' ),
			'name_admin_bar'        => __( 'Heroes', 'iworks-plugins-management' ),
			'archives'              => __( 'Heroes', 'iworks-plugins-management' ),
			'all_items'             => __( 'Heroes', 'iworks-plugins-management' ),
			'add_new_item'          => __( 'Add New Hero', 'iworks-plugins-management' ),
			'add_new'               => __( 'Add New', 'iworks-plugins-management' ),
			'new_item'              => __( 'New Hero', 'iworks-plugins-management' ),
			'edit_item'             => __( 'Edit Hero', 'iworks-plugins-management' ),
			'update_item'           => __( 'Update Hero', 'iworks-plugins-management' ),
			'view_item'             => __( 'View Hero', 'iworks-plugins-management' ),
			'view_items'            => __( 'View Hero', 'iworks-plugins-management' ),
			'search_items'          => __( 'Search Hero', 'iworks-plugins-management' ),
			'not_found'             => __( 'Not found', 'iworks-plugins-management' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'iworks-plugins-management' ),
			'items_list'            => __( 'Hero list', 'iworks-plugins-management' ),
			'items_list_navigation' => __( 'Hero list navigation', 'iworks-plugins-management' ),
			'filter_items_list'     => __( 'Filter items list', 'iworks-plugins-management' ),
		);
		$args   = array(
			'can_export'          => true,
			'capability_type'     => 'page',
			'description'         => __( 'Hero', 'iworks-plugins-management' ),
			'exclude_from_search' => true,
			'has_archive'         => false,
			'hierarchical'        => false,
			'label'               => __( 'Heroes', 'iworks-plugins-management' ),
			'labels'              => $labels,
			'menu_icon'           => 'dashicons-businessperson',
			'public'              => false,
			'show_in_admin_bar'   => false,
			'show_in_menu'        => apply_filters( 'opi_post_type_show_in_menu' . $this->posttype_name, 'edit.php' ),
			'show_in_nav_menus'   => false,
			'show_ui'             => true,
			'show_in_rest'        => true,
			'supports'            => array( 'title', 'thumbnail', 'editor', 'excerpt', 'page-attributes' ),
		);
		register_post_type( $this->posttype_name, $args );
	}

}

