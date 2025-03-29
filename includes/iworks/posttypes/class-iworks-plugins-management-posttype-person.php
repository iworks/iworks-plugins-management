<?php
/**
 * Class for custom Post Type: PERSON
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

class iworks_iworks_plugins_management_posttype_person extends iworks_iworks_plugins_management_posttype_base {

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
		 * Taxonomy name
		 */
		$this->taxonomy_name = preg_replace( '/^iworks_iworks_plugins_management_posttype_/', '', __CLASS__ );
		$this->register_class_custom_taxonomy_name( $this->taxonomy_name, 'iw', 'role' );
		/**
		 * WordPress Hooks
		 */
		add_action( 'add_meta_boxes_' . $this->posttypes_names[ $this->posttype_name ], array( $this, 'add_meta_boxes' ) );
		add_shortcode( 'iworks_persons_list', array( $this, 'get_list' ) );
		add_filter( 'og_og_type_value', array( $this, 'filter_og_og_type_value' ) );
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
		$args                = wp_parse_args(
			$atts,
			array(
				'orderby'        => 'rand',
				'posts_per_page' => -1,
			)
		);
		$args['post_type']   = $this->posttype_name;
		$args['post_status'] = 'publish';
		$the_query           = new WP_Query( $args );
		/**
		 * No data!
		 */
		if ( ! $the_query->have_posts() ) {
			return $content;
		}
		/**
		 * Content
		 */
		ob_start();
		get_template_part( 'template-parts/heroes/header' );
		$join = rand( 0, 2 );
		$i    = 0;
		while ( $the_query->have_posts() ) {
			$the_query->the_post();
			$args = array(
				'join' => $join,
				'i'    => $i++,
			);
			get_template_part( 'template-parts/heroes/one', get_post_type(), $args );
		}
		/* Restore original Post Data */
		wp_reset_postdata();
		get_template_part( 'template-parts/heroes/footer' );
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}

	/**
	 * Register Custom Post Type
	 *
	 * @since 1.0.0
	 */
	public function action_init_register_post_type() {
		$labels = array(
			'name'                  => _x( 'Persons', 'Post Type General Name', 'iworks-plugins-management' ),
			'singular_name'         => _x( 'Person', 'Post Type Singular Name', 'iworks-plugins-management' ),
			'menu_name'             => __( 'Persons', 'iworks-plugins-management' ),
			'name_admin_bar'        => __( 'Persons', 'iworks-plugins-management' ),
			'archives'              => __( 'Persons', 'iworks-plugins-management' ),
			'all_items'             => __( 'Persons', 'iworks-plugins-management' ),
			'add_new_item'          => __( 'Add New Person', 'iworks-plugins-management' ),
			'add_new'               => __( 'Add New', 'iworks-plugins-management' ),
			'new_item'              => __( 'New Person', 'iworks-plugins-management' ),
			'edit_item'             => __( 'Edit Person', 'iworks-plugins-management' ),
			'update_item'           => __( 'Update Person', 'iworks-plugins-management' ),
			'view_item'             => __( 'View Person', 'iworks-plugins-management' ),
			'view_items'            => __( 'View Person', 'iworks-plugins-management' ),
			'search_items'          => __( 'Search Person', 'iworks-plugins-management' ),
			'not_found'             => __( 'Not found', 'iworks-plugins-management' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'iworks-plugins-management' ),
			'items_list'            => __( 'Person list', 'iworks-plugins-management' ),
			'items_list_navigation' => __( 'Person list navigation', 'iworks-plugins-management' ),
			'filter_items_list'     => __( 'Filter items list', 'iworks-plugins-management' ),
		);
		$args   = array(
			'can_export'          => true,
			'capability_type'     => 'page',
			'description'         => __( 'Person', 'iworks-plugins-management' ),
			'exclude_from_search' => true,
			'has_archive'         => false,
			'hierarchical'        => false,
			'label'               => __( 'Persons', 'iworks-plugins-management' ),
			'labels'              => $labels,
			'menu_icon'           => 'dashicons-businessperson',
			'public'              => true,
			'show_in_admin_bar'   => false,
			'menu_position'       => 20,
			'show_in_nav_menus'   => false,
			'show_ui'             => true,
			'show_in_rest'        => false,
			'supports'            => array( 'title', 'thumbnail', 'editor', 'revisions' ),
			'rewrite'             => array(
				'slug' => defined( 'ICL_SITEPRESS_VERSION' ) ? 'person' : _x( 'person', 'iWorks Post Type Person SLUG', 'iworks-plugins-management' ),
			),
		);
		register_post_type(
			$this->posttype_name,
			apply_filters( 'iworks_post_type_person_args', $args )
		);
	}

	/**
	 * Register Custom Taxonomy
	 *
	 * @since 1.0.0
	 */
	public function action_init_register_taxonomy() {
		$labels = array(
			'name'                       => _x( 'Roles', 'Role General Name', 'iworks-plugins-management' ),
			'singular_name'              => _x( 'Role', 'Role Singular Name', 'iworks-plugins-management' ),
			'menu_name'                  => __( 'Roles', 'iworks-plugins-management' ),
			'all_items'                  => __( 'All Roles', 'iworks-plugins-management' ),
			'parent_item'                => __( 'Parent Role', 'iworks-plugins-management' ),
			'parent_item_colon'          => __( 'Parent Role:', 'iworks-plugins-management' ),
			'new_item_name'              => __( 'New Role Name', 'iworks-plugins-management' ),
			'add_new_item'               => __( 'Add New Role', 'iworks-plugins-management' ),
			'edit_item'                  => __( 'Edit Role', 'iworks-plugins-management' ),
			'update_item'                => __( 'Update Role', 'iworks-plugins-management' ),
			'view_item'                  => __( 'View Role', 'iworks-plugins-management' ),
			'separate_items_with_commas' => __( 'Separate items with commas', 'iworks-plugins-management' ),
			'add_or_remove_items'        => __( 'Add or remove items', 'iworks-plugins-management' ),
			'choose_from_most_used'      => __( 'Choose from the most used', 'iworks-plugins-management' ),
			'popular_items'              => __( 'Popular Roles', 'iworks-plugins-management' ),
			'search_items'               => __( 'Search Roles', 'iworks-plugins-management' ),
			'not_found'                  => __( 'Not Found', 'iworks-plugins-management' ),
			'no_terms'                   => __( 'No items', 'iworks-plugins-management' ),
			'items_list'                 => __( 'Roles list', 'iworks-plugins-management' ),
			'items_list_navigation'      => __( 'Roles list navigation', 'iworks-plugins-management' ),
		);
		$args   = array(
			'labels'            => $labels,
			'hierarchical'      => false,
			'public'            => true,
			'show_admin_column' => true,
			'show_tagcloud'     => false,
			'rewrite'           => array(
				'slug' => defined( 'ICL_SITEPRESS_VERSION' ) ? 'role' : _x( 'role', 'iWorks Post Type Person SLUG', 'iworks-plugins-management' ),
			),
		);
		register_taxonomy( $this->taxonomy_name, array( $this->posttype_name ), $args );
	}

	public function filter_og_og_type_value( $value ) {
		if ( is_singular( $this->posttype_name ) ) {
			return 'profile';
		}
		return $value;
	}

}

