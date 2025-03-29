<?php
/**
 * Class for Post Type: PAGE
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

class iworks_iworks_plugins_management_posttype_page extends iworks_iworks_plugins_management_posttype_base {

	public function __construct() {
		parent::__construct();
		/**
		 * Post Type Name
		 *
		 * @since 1.0.0
		 */
		$this->posttype_name = preg_replace( '/^iworks_iworks_plugins_management_posttype_/', '', __CLASS__ );
		$this->register_class_custom_posttype_name( $this->posttype_name );
		/**
		 * WordPress Hooks
		 */
		add_action( 'add_meta_boxes_' . $this->posttypes_names[ $this->posttype_name ], array( $this, 'add_meta_boxes' ) );
		add_action( 'load-post-new.php', array( $this, 'admin_enqueue' ) );
		add_action( 'load-post.php', array( $this, 'admin_enqueue' ) );
		/**
		 * iWorks Plugins Management Hooks
		 */
		/**
		 * settings
		 */
		$this->meta_boxes[ $this->posttypes_names[ $this->posttype_name ] ] = array(
			'page-data' => array(
				'title'  => __( 'Opinion Data', 'iworks-plugins-management' ),
				'fields' => array(
					array(
						'name'  => 'icon',
						'type'  => 'image',
						'label' => esc_html__( 'Icon', 'iworks-plugins-management' ),
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
	public function action_init_settings() {}

	public function action_init_register_post_type() {}
	public function action_init_register_taxonomy() {}

}

