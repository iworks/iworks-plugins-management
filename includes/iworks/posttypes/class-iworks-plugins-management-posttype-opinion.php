<?php
/**
 * Class for custom Post Type: OPINION
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

class iworks_iworks_plugins_management_posttype_opinion extends iworks_iworks_plugins_management_posttype_base {

	private $list = array();

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
		add_action( 'save_post_' . $this->posttypes_names[ $this->posttype_name ], array( $this, 'action_save_post' ), 10, 3 );
		add_filter( 'iworks_post_type_opinion_options_list', array( $this, 'get_options_list_array' ), 10, 2 );
		/**
		 * Shortcodes
		 */
		add_shortcode( 'iworks_opinions_list', array( $this, 'get_list' ) );
		/**
		 * Settings
		 */
		$this->meta_boxes[ $this->posttypes_names[ $this->posttype_name ] ] = array(
			'opinion-data' => array(
				'title'  => __( 'Opinion Data', 'iworks-plugins-management' ),
				'fields' => array(
					array(
						'name'    => 'stars',
						'type'    => 'select',
						'label'   => esc_html__( 'The Opinion Stars', 'iworks-plugins-management' ),
						'options' => array(
							'5' => '&bigstar;&bigstar;&bigstar;&bigstar;&bigstar;',
							'4' => '&bigstar;&bigstar;&bigstar;&bigstar;',
							'3' => '&bigstar;&bigstar;&bigstar;',
							'2' => '&bigstar;&bigstar;',
							'1' => '&bigstar;',
						),
					),
					array(
						'name'  => 'opinion_url',
						'type'  => 'url',
						'label' => esc_html__( 'The Opinion URL', 'iworks-plugins-management' ),
					),
					array(
						'name'  => 'author_url',
						'type'  => 'url',
						'label' => esc_html__( 'The Opinion Author URL', 'iworks-plugins-management' ),
					),
				),
			),
		);
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
				'posts_per_page' => 4,
			)
		);
		$args['post_type']   = $this->posttype_name['opinion'];
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
		get_template_part( 'template-parts/opinions/header' );
		$join = rand( 0, 2 );
		$i    = 0;
		while ( $the_query->have_posts() ) {
			$the_query->the_post();
			$args = array(
				'join' => $join,
				'i'    => $i++,
			);
			get_template_part( 'template-parts/opinions/one', get_post_type(), $args );
		}
		/* Restore original Post Data */
		wp_reset_postdata();
		get_template_part( 'template-parts/opinions/footer' );
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
			'name'                  => _x( 'Opinions', 'Post Type General Name', 'iworks-plugins-management' ),
			'singular_name'         => _x( 'Opinion', 'Post Type Singular Name', 'iworks-plugins-management' ),
			'menu_name'             => __( 'Opinions', 'iworks-plugins-management' ),
			'name_admin_bar'        => __( 'Opinions', 'iworks-plugins-management' ),
			'archives'              => __( 'Opinions', 'iworks-plugins-management' ),
			'all_items'             => __( 'Opinions', 'iworks-plugins-management' ),
			'add_new_item'          => __( 'Add New Opinion', 'iworks-plugins-management' ),
			'add_new'               => __( 'Add New', 'iworks-plugins-management' ),
			'new_item'              => __( 'New Opinion', 'iworks-plugins-management' ),
			'edit_item'             => __( 'Edit Opinion', 'iworks-plugins-management' ),
			'update_item'           => __( 'Update Opinion', 'iworks-plugins-management' ),
			'view_item'             => __( 'View Opinion', 'iworks-plugins-management' ),
			'view_items'            => __( 'View Opinion', 'iworks-plugins-management' ),
			'search_items'          => __( 'Search Opinion', 'iworks-plugins-management' ),
			'not_found'             => __( 'Not found', 'iworks-plugins-management' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'iworks-plugins-management' ),
			'items_list'            => __( 'Opinion list', 'iworks-plugins-management' ),
			'items_list_navigation' => __( 'Opinion list navigation', 'iworks-plugins-management' ),
			'filter_items_list'     => __( 'Filter items list', 'iworks-plugins-management' ),
		);
		$args   = array(
			'can_export'          => true,
			'capability_type'     => 'page',
			'description'         => __( 'Opinion', 'iworks-plugins-management' ),
			'exclude_from_search' => true,
			'has_archive'         => false,
			'hierarchical'        => false,
			'label'               => __( 'Opinions', 'iworks-plugins-management' ),
			'labels'              => $labels,
			'menu_icon'           => 'dashicons-businessopinion',
			'public'              => false,
			'show_in_admin_bar'   => false,
			'menu_position'       => 20,
			'show_in_nav_menus'   => false,
			'show_ui'             => true,
			'show_in_menu'        => 'adjc',
			'show_in_rest'        => false,
			'supports'            => array( 'title', 'thumbnail', 'editor', 'revisions' ),
			'rewrite'             => array(
				'slug' => _x( 'opinion', 'iWorks Post Type Opinion SLUG', 'iworks-plugins-management' ),
			),
		);
		register_post_type(
			$this->posttypes_names['opinion'],
			apply_filters( 'iworks_post_type_opinion_args', $args )
		);
	}

	/**
	 * Register Custom Taxonomy
	 *
	 * @since 1.0.0
	 */
	public function action_init_register_taxonomy() {
	}

	/**
	 * Get post list
	 *
	 * @param array $list options list
	 * @param array $atts WP_Query attributes
	 *
	 * @return string $content
	 */
	public function get_options_list_array( $list, $atts = array() ) {
		if ( ! empty( $this->list ) ) {
			return $this->list;
		}
		$list       = $this->get_select_array( $this->posttype_name['opinion'] );
		$this->list = $list;
		return $list;
	}

}

