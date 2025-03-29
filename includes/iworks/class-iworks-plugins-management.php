<?php
/*

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

if ( class_exists( 'iworks_iworks_plugins_management' ) ) {
	return;
}

require_once( dirname( __FILE__ ) . '/class-iworks-plugins-management-base.php' );

class iworks_iworks_plugins_management extends iworks_iworks_plugins_management_base {

	private $capability;

	/**
	 * Plugin Objects
	 *
	 * @since 1.0.0
	 */
	private array $objects = array();


	/**
	 * plugins page
	 *
	 * @since 1.0.0
	 */
	private string $meta_name_plugins_page_id = 'iwpm_plugins_page_id';

	public function __construct() {
		parent::__construct();
		$this->version    = 'PLUGIN_VERSION';
		$this->capability = apply_filters( 'iworks_iworks_plugins_management_capability', 'manage_options' );
		/**
		 * post types
		 */
		include_once 'class-iworks-plugins-management-posttypes.php';
		new iworks_wordpress_plugin_posttypes();
		/**
		 * WordPress Hooks
		 */
		add_action( 'admin_init', array( $this, 'action_admin_init' ) );
		add_action( 'init', array( $this, 'action_init_settings' ) );
		add_action( 'admin_init', array( $this, 'action_admin_init_add_settings_fields' ) );
		/**
		 * load github class
		 */
		$filename = $this->includes_directory . '/class-iworks-iworks-plugins-management-github.php';
		if ( is_file( $filename ) ) {
			include_once $filename;
			new iworks_iworks_plugins_management_github();
		}
		/**
		 * is active?
		 */
		add_filter( 'iworks-plugins-management/is_active', '__return_true' );
	}

	public function action_admin_init() {
		$this->check_option_object();
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );
	}

	public function admin_enqueue_scripts() {
		$screen = get_current_screen();
		/**
		 * off on not iworks-plugins-management pages
		 */
		$re = sprintf( '/%s_/', __CLASS__ );
		if ( ! preg_match( $re, $screen->id ) ) {
			return;
		}
		/**
		 * datepicker
		 */
		$file = 'assets/externals/datepicker/css/jquery-ui-datepicker.css';
		$file = plugins_url( $file, $this->base );
		wp_register_style( 'jquery-ui-datepicker', $file, false, '1.12.1' );
		/**
		 * select2
		 */
		$file = 'assets/externals/select2/css/select2.min.css';
		$file = plugins_url( $file, $this->base );
		wp_register_style( 'select2', $file, false, '4.0.3' );
		/**
		 * Admin styles
		 */
		$file    = sprintf( '/assets/styles/admin%s.css', $this->dev );
		$version = $this->get_version( $file );
		$file    = plugins_url( $file, $this->base );
		wp_register_style( 'admin-iworks-plugins-management', $file, array( 'jquery-ui-datepicker', 'select2' ), $version );
		wp_enqueue_style( 'admin-iworks-plugins-management' );
		/**
		 * select2
		 */
		wp_register_script(
			'select2',
			plugins_url(
				'assets/externals/select2/js/select2.full.min.js',
				$this->base
			),
			array(),
			'4.0.3',
			array(
				'in_footer' => true,
			)
		);
		/**
		 * Admin scripts
		 */
		$files = array(
			'iworks-plugins-management-admin' => sprintf( 'assets/scripts/admin/admin%s.js', $this->dev ),
		);
		if ( '' == $this->dev ) {
			$files = array(
				'iworks-plugins-management-admin-datepicker' => 'assets/scripts/admin/src/datepicker.js',
				'iworks-plugins-management-admin-select2' => 'assets/scripts/admin/src/select2.js',
				'iworks-plugins-management-admin-media-library' => 'assets/scripts/admin/src/media-library.js',
			);
		}
		$deps = array(
			'jquery-ui-datepicker',
			'select2',
		);
		foreach ( $files as $handle => $file ) {
			wp_register_script(
				$handle,
				plugins_url( $file, $this->base ),
				$deps,
				$this->get_version(),
				true
			);
			wp_enqueue_script( $handle );
		}
		/**
		 * JavaScript messages
		 *
		 * @since 1.0.0
		 */
		$data = array(
			'messages' => array(),
			'nonces'   => array(),
			'user_id'  => get_current_user_id(),
		);
		wp_localize_script(
			'iworks_plugins_management_admin',
			__CLASS__,
			apply_filters( 'wp_localize_script_iworks_plugins_management_admin', $data )
		);
	}

	/**
	 * Initialize plugin
	 *
	 * @since 1.0.0
	 */
	public function action_init_settings() {
		/**
		 * options
		 */
		$this->check_option_object();
		if ( is_admin() ) {
		} else {
			$file = 'assets/styles/iworks_plugins_management' . $this->dev . '.css';
			wp_enqueue_style( 'iworks-plugins-management', plugins_url( $file, $this->base ), array(), $this->get_version( $file ) );
		}
	}

	/**
	 * Plugin row data
	 */
	public function plugin_row_meta( $links, $file ) {
		if ( $this->dir . '/iworks-plugins-management.php' == $file ) {
			/* start:free */
			$links[] = sprintf(
				'<a href="%s">%s</a>',
				esc_url(
					add_query_arg(
						array(
							'utm_source' => 'iworks-plugins-management',
							'utm_medium' => 'plugin-row-donate-link',
						),
						'https://ko-fi.com/iworks'
					)
				),
				esc_html__( 'Donate', 'iworks-plugins-management' )
			);
			/* end:free */
			$links[] = sprintf(
				'<a href="%s">%s</a>',
				esc_url(
					add_query_arg(
						array(
							'utm_source' => 'iworks-plugins-management',
							'utm_medium' => 'plugin-row-donate-link',
						),
						'https://github.com/iworks.pl/iworks-plugins-management'
					)
				),
				esc_html__( 'GitHub', 'iworks-plugins-management' )
			);
		}
		return $links;
	}

	/**
	 * register_activation_hook
	 *
	 * @since 1.0.0
	 */
	public function register_activation_hook() {
		$this->check_option_object();
		$this->options->activate();
		do_action( 'iworks/iworks-plugins-management/register_activation_hook' );
	}

	/**
	 * register_deactivation_hook
	 *
	 * @since 1.0.0
	 */
	public function register_deactivation_hook() {
		$this->check_option_object();
		$this->options->deactivate();
		do_action( 'iworks/iworks-plugins-management/register_deactivation_hook' );
	}

	/**
	 * add settings
	 *
	 * @since 1.0.0
	 */
	public function action_admin_init_add_settings_fields() {
		add_settings_field(
			$this->meta_name_plugins_page_id,
			__( 'Plugins', 'iworks-plugins-management' ),
			array( $this, 'add_settings_meta_name_plugins_page_id' ),
			'reading',
			'default',
			array(
				'class' => 'opi-feng-plugins',
			),
		);
		register_setting( 'reading', $this->meta_name_plugins_page_id );
	}

	/**
	 * field content: plugins
	 *
	 * @since 1.0.0
	 */
	public function add_settings_meta_name_plugins_page_id( $args ) {
		wp_dropdown_pages(
			array(
				'show_option_none' => __( '&mdash; Select &mdash;', 'iworks-plugins-management' ),
				'selected'         => get_option( $this->meta_name_plugins_page_id ),
				'name'             => $this->meta_name_plugins_page_id,
			)
		);
	}

}
