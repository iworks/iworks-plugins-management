<?php
/**
 * Class for custom Post Type: plugin
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

class iworks_iworks_plugins_management_posttype_plugin extends iworks_iworks_plugins_management_posttype_base {

	// private string $posttype_name;

	private $fields               = array();
	private $option_name_partners = '_partners';
	/**
	 * partners types
	 *
	 * @since 1.0.0
	 */
	private $partners_types;

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
		add_filter( 'the_content', array( $this, 'the_content' ) );
		add_action( 'iworks/iworks-plugins-management/' . $this->posttypes_names[ $this->posttype_name ] . '/meta/updated', array( $this, 'action_maybe_get_github_data' ) );
	}

	/**
	 * class settings
	 *
	 * @since 1.0.0
	 */
	public function action_init_settings() {
		$this->load_plugin_admin_assets                                     = true;
		$this->meta_boxes[ $this->posttypes_names[ $this->posttype_name ] ] = array(
			'plugin-data' => array(
				'title'  => __( 'Plugin Data', 'iworks-plugins-management' ),
				'fields' => array(
					array(
						'name'             => 'release_date',
						'type'             => 'date',
						'label'            => esc_html__( 'Release Date', 'iworks-plugins-management' ),
						'git_map_releases' => 'published_at',
					),
					array(
						'name'    => 'url_git',
						'type'    => 'url',
						'label'   => esc_html__( 'Git URL', 'iworks-plugins-management' ),
						'git_map' => 'html_url',
					),
					array(
						'name'             => 'version',
						'type'             => 'text',
						'label'            => esc_html__( 'Version', 'iworks-plugins-management' ),
						'td_classes'       => array( 'has-text-align-center' ),
						'git_map_releases' => 'tag_name',
					),
					array(
						'name'  => 'url_main',
						'type'  => 'url',
						'label' => esc_html__( 'URL', 'iworks-plugins-management' ),
					),
					array(
						'name'  => 'url_issues',
						'type'  => 'url',
						'label' => esc_html__( 'Issues URL', 'iworks-plugins-management' ),
					),
					array(
						'name'  => 'url_i18n',
						'type'  => 'url',
						'label' => esc_html__( 'i18n URL', 'iworks-plugins-management' ),
					),
					array(
						'name'       => 'v_options',
						'type'       => 'text',
						'label'      => esc_html__( 'Options', 'iworks-plugins-management' ),
						'td_classes' => array( 'has-text-align-center' ),
					),
					array(
						'name'       => 'v_rate',
						'type'       => 'text',
						'label'      => esc_html__( 'Rate', 'iworks-plugins-management' ),
						'td_classes' => array( 'has-text-align-center' ),
					),
					array(
						'name'       => 'v_tested',
						'type'       => 'text',
						'label'      => esc_html__( 'Tested', 'iworks-plugins-management' ),
						'td_classes' => array( 'has-text-align-center' ),
					),
					array(
						'name'       => 'free',
						'type'       => 'checkbox',
						'label'      => esc_html__( 'Free', 'iworks-plugins-management' ),
						'td_classes' => array( 'has-text-align-center' ),
					),
					array(
						'name'       => 'blueprint',
						'type'       => 'checkbox',
						'label'      => esc_html__( 'blueprint.json', 'iworks-plugins-management' ),
						'td_classes' => array( 'has-text-align-center' ),
					),
				),
			),
		);
	}

	public function action_init_register_taxonomy() {}

	/**
	 * Set default order
	 *
	 * @since 1.0.0
	 */
	public function set_default_order( $query ) {
		if ( is_admin() ) {
			return;
		}
		if ( $this->posttype_name !== $query->get( 'post_type' ) ) {
			return;
		}
		$query->set( 'meta_key', 'release_date' );
		$query->set(
		);
	}

	/**
	 * get content
	 *
	 * @since 1.0.0
	 */
	public function the_content( $content ) {
		if ( ! is_page( get_option( $this->option_name_plugins_page_id ) ) ) {
			return $content;
		}
		$group = 'plugin-data';

		$content .= '<figure class="wp-block-table is-style-stripes alignwide"><table class="has-fixed-layout">';
		$content .= '<thead>';
		$content .= sprintf( '<th>%s</th>', esc_html__( 'Plugin Name', 'iworks-plugins-management' ) );
		foreach ( $this->meta_boxes[ $this->posttypes_names[ $this->posttype_name ] ][ $group ]['fields'] as $field ) {
			$content .= sprintf( '<th>%s</th>', esc_html( $field['label'] ) );
		}
		$content      .= '</thead>';
		$content      .= '<tbody>';
		$wp_query_args = array(
			'post_type'      => $this->posttypes_names[ $this->posttype_name ],
			'posts_per_page' => -1,
			'meta_key'       => $this->get_post_meta_name( 'release_date', $group ),
			'orderby'        => array(
				'meta_value' => 'ASC',
				'title'      => 'ASC',
			),
		);
		$wp_query      = new WP_Query( $wp_query_args );
		if ( $wp_query->have_posts() ) {
			while ( $wp_query->have_posts() ) {
				$wp_query->the_post();
				$content .= '<tr>';
				$content .= sprintf( '<td>%s</td>', get_the_title() );
				foreach ( $this->meta_boxes[ $this->posttypes_names[ $this->posttype_name ] ][ $group ]['fields'] as $field ) {
					$value    = get_post_meta( get_the_ID(), $this->get_post_meta_name( $field['name'], $group ), true );
					$content .= sprintf(
						'<td class="%s">',
						esc_attr( isset( $field['td_classes'] ) ? implode( ' ', $field['td_classes'] ) : '' )
					);
					if ( $value ) {
						switch ( $field['type'] ) {
							case 'text':
								$content .= $value;
								break;
							case 'date':
								$content .= substr( $value, 0, 10 );
								break;
							case 'checkbox':
								$content .= $value;
								break;
							case 'url':
								$content .= sprintf(
									'<a href="%s">%s</a>',
									esc_url( $value ),
									esc_html( $field['label'] )
								);
								break;
						}
					} else {
						$content .= '&mdash;';
					}
					$content .= '</td>';
				}
				$content .= '</tr>';
			}
		}
		// Restore original Post Data.
		wp_reset_postdata();
		$content .= '</tbody>';
		$content .= '</table>';
		$content .= '</figure>';
		return $content;
	}

	/**
	 * Register Custom Post Type
	 *
	 * @since 1.0.0
	 */
	public function action_init_register_post_type() {
		$labels = array(
			'name'                  => _x( 'Plugins', 'Post Type General Name', 'iworks-plugins-management' ),
			'singular_name'         => _x( 'Plugin', 'Post Type Singular Name', 'iworks-plugins-management' ),
			'menu_name'             => __( 'Plugins', 'iworks-plugins-management' ),
			'name_admin_bar'        => __( 'Plugins', 'iworks-plugins-management' ),
			'archives'              => __( 'Plugins', 'iworks-plugins-management' ),
			'all_items'             => __( 'Plugins', 'iworks-plugins-management' ),
			'add_new_item'          => __( 'Add New plugin', 'iworks-plugins-management' ),
			'add_new'               => __( 'Add New', 'iworks-plugins-management' ),
			'new_item'              => __( 'New plugin', 'iworks-plugins-management' ),
			'edit_item'             => __( 'Edit plugin', 'iworks-plugins-management' ),
			'update_item'           => __( 'Update plugin', 'iworks-plugins-management' ),
			'view_item'             => __( 'View plugin', 'iworks-plugins-management' ),
			'view_items'            => __( 'View plugin', 'iworks-plugins-management' ),
			'search_items'          => __( 'Search plugin', 'iworks-plugins-management' ),
			'not_found'             => __( 'Not found', 'iworks-plugins-management' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'iworks-plugins-management' ),
			'items_list'            => __( 'Plugin list', 'iworks-plugins-management' ),
			'items_list_navigation' => __( 'Plugin list navigation', 'iworks-plugins-management' ),
			'filter_items_list'     => __( 'Filter items list', 'iworks-plugins-management' ),
		);
		$args   = array(
			'can_export'          => true,
			'capability_type'     => 'page',
			'description'         => __( 'Plugin', 'iworks-plugins-management' ),
			'exclude_from_search' => true,
			'has_archive'         => true,
			'hierarchical'        => false,
			'label'               => __( 'Plugins', 'iworks-plugins-management' ),
			'labels'              => $labels,
			'public'              => true,
			'show_in_admin_bar'   => true,
			'show_in_menu'        => apply_filters( $this->get_register_post_filter_name( 'show_in_menu' ), 'edit.php' ),
			'show_in_nav_menus'   => true,
			'show_ui'             => true,
			'show_in_rest'        => true,
			'supports'            => array( 'title', 'editor', 'excerpt', 'thumbnail' ),
			'rewrite'             => array(
				'slug' => _x( 'plugin', 'slug for single plugin', 'iworks-plugins-management' ),
			),
		);
		if ( defined( 'ICL_SITEPRESS_VERSION' ) ) {
			unset( $args['rewrite'] );
		}
		register_post_type( $this->posttypes_names[ $this->posttype_name ], $args );
	}

	public function action_maybe_get_github_data( $post_id ) {
		$group = 'plugin-data';
		$url   = get_post_meta( get_the_ID(), $this->get_post_meta_name( 'url_git', $group ), true );
		if ( preg_match( '/^https:\/\/github.com\/([^\/]+)\/([^\/]+)\/?/', $url, $matches ) ) {
			$repository = $matches[1] . '/' . $matches[2];
			$data       = $this->get_github_info( $repository, 'releases' );
			if ( ! empty( $data ) ) {
				foreach ( $this->meta_boxes[ $this->posttypes_names[ $this->posttype_name ] ][ $group ]['fields'] as $field ) {
					if (
						isset( $field['git_map_releases'] )
						&& isset( $data[ $field['git_map_releases'] ] )
						&& $data[ $field['git_map_releases'] ]
					) {
						$key   = $this->get_post_meta_name( $field['name'], $group );
						$value = $data[ $field['git_map_releases'] ];
						l( array( $post_id, $key, $value ) );
						update_post_meta( $post_id, $key, $value );
					}
				}
			}
			/**
			 * repositiry data
			 */
			$data = $this->get_github_info( $repository );
			if ( ! empty( $data ) ) {
				foreach ( $this->meta_boxes[ $this->posttypes_names[ $this->posttype_name ] ][ $group ]['fields'] as $field ) {
					if (
						isset( $field['git_map'] )
						&& isset( $data[ $field['git_map'] ] )
						&& $data[ $field['git_map'] ]
					) {
						$key = $this->get_post_meta_name( $field['name'], $group );
						update_post_meta( $post_id, $key, $data[ $field['git_map'] ] );
					}
				}
			}
		}
	}

	/**
	 * Get the latest release from the selected repository
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 * @return array
	 */
	private function get_github_info( $repository, $action = null ) : array {
		// Create the request URI
		$request_uri = sprintf(
			'https://api.github.com/repos/%s',
			$repository,
		);
		if ( $action ) {
			$request_uri = sprintf(
				'https://api.github.com/repos/%s/%s',
				$repository,
				$action
			);
		}
		// Get the response from the API
		$request = wp_remote_get( $request_uri );
		// If the API response has an error code, stop
		$response_codes = wp_remote_retrieve_response_code( $request );
		if ( $response_codes < 200 || $response_codes >= 300 ) {
			return array();
		}
		// Decode the response body
		$response = json_decode( wp_remote_retrieve_body( $request ), true );
		// If the response is an array, return the first item
		if ( is_array( $response ) && ! empty( $response[0] ) ) {
			$response = $response[0];
		}
		return $response;
	}
}

