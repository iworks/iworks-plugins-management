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

if ( class_exists( 'iworks_iworks_plugins_management_base' ) ) {
	return;
}

class iworks_iworks_plugins_management_base {

	protected $dev;
	protected $meta_prefix = '_iw';
	protected $base;
	protected $dir;
	protected $url;
	protected $plugin_file;
	protected $plugin_file_path;

	/**
	 * plugin settings capability
	 */
	private string $capability = 'manage_options';

	/**
	 * plugin version
	 */
	protected string $version = 'PLUGIN_VERSION';

	/**
	 * plugin includes directory
	 *
	 * @since 1.0.0
	 */
	protected string $includes_directory;

	/**
	 * DEBUG
	 *
	 * @since 1.0.0
	 */
	protected $debug = false;

	/**
	 * EOL?
	 *
	 * @since 1.0.0
	 */
	protected string $eol = '';

	/**
	 * iWorks Options Class Object
	 *
	 * @since 1.0.0
	 */
	protected $options;

	/**
	 * plugins page id option name
	 *
	 * @since 1.0.0
	 */
	protected string $option_name_plugins_page_id = 'iwpm_plugins_page_id';

	public function __construct() {
		/**
		 * static settings
		 */
		$this->debug = ( defined( 'WP_DEBUG' ) && WP_DEBUG ) || ( defined( 'IWORKS_DEV_MODE' ) && IWORKS_DEV_MODE );
		/**
		 * use minimized scripts if not debug
		 */
		$this->dev = $this->debug ? '' : '.min';
		/**
		 * add EOL if debug
		 */
		$this->eol = $this->debug ? PHP_EOL : '';
		/**
		 * directories and urls
		 */
		$this->base = dirname( __FILE__ );
		$this->dir  = basename( dirname( dirname( $this->base ) ) );
		$this->url  = plugins_url( $this->dir );
		/**
		 * plugin ID
		 */
		$this->plugin_file_path = $this->base . '/simple-consent-mode.php';
		$this->plugin_file      = plugin_basename( $this->plugin_file_path );
		/**
		 * plugin includes directory
		 */
		$this->includes_directory = __DIR__ . '/iworks-plugins-management';
		/**
		 * WordPress Hooks
		 */
	}

	public function get_version( $file = null ) {
		if ( defined( 'IWORKS_DEV_MODE' ) && IWORKS_DEV_MODE ) {
			if ( null != $file ) {
				$file = dirname( $this->base ) . $file;
				if ( is_file( $file ) ) {
					return md5_file( $file );
				}
			}
			return time();
		}
		return $this->version;
	}

	protected function get_meta_name( $name ) {
		return sprintf( '%s_%s', $this->meta_prefix, sanitize_title( $name ) );
	}

	public function get_post_type() {
		return $this->post_type;
	}

	public function get_this_capability() {
		return $this->capability;
	}

	private function slug_name( $name ) {
		return preg_replace( '/[_ ]+/', '-', strtolower( __CLASS__ . '_' . $name ) );
	}

	public function get_post_meta( $post_id, $meta_key ) {
		return get_post_meta( $post_id, $this->get_meta_name( $meta_key ), true );
	}

	protected function print_table_body( $post_id, $fields ) {
		echo '<table class="widefat striped"><tbody>';
		foreach ( $fields as $name => $data ) {
			$key   = $this->get_meta_name( $name );
			$value = $this->get_post_meta( $post_id, $name );
			/**
			 * extra
			 */
			$extra = isset( $data['placeholder'] ) ? sprintf( ' placeholder="%s" ', esc_attr( $data['placeholder'] ) ) : '';
			foreach ( array( 'placeholder', 'style', 'class', 'id' ) as $extra_key ) {
				if ( isset( $data[ $extra_key ] ) ) {
					$extra .= sprintf( ' min="%d" ', esc_attr( $data[ $extra_key ] ) );
				}
			}
			/**
			 * start row
			 */
			echo '<tr>';
			printf( '<th scope="row" style="width: 130px">%s</th>', $data['title'] );
			echo '<td>';
			switch ( $data['type'] ) {
				case 'number':
					foreach ( array( 'min', 'max', 'step' ) as $extra_key ) {
						if ( isset( $data[ $extra_key ] ) ) {
							$extra .= sprintf( ' min="%d" ', intval( $data[ $extra_key ] ) );
						}
					}
					printf(
						'<input type="number" name="%s" value="%d" %s />',
						esc_attr( $key ),
						intval( $value ),
						// data string escaped few lines above
						$extra
					);
					break;
				case 'date':
					$date = intval( $this->get_post_meta( $post_id, $name ) );
					if ( empty( $date ) ) {
						$date = strtotime( 'now' );
					}
					printf(
						'<input type="text" class="datepicker" name="%s" value="%s" />',
						esc_attr( $this->get_meta_name( $name ) ),
						esc_attr( $date )
					);
					break;
			}
			echo '</td>';
			echo '</tr>';
		}
		echo '</tbody></table>';
	}

	protected function get_module_file( $filename, $vendor = 'iworks' ) {
		return realpath(
			sprintf(
				'%s/%s/%s/%s.php',
				$this->base,
				$vendor,
				$this->dir,
				$filename
			)
		);
	}

	protected function html_title( $text ) {
		printf( '<h1 class="wp-heading-inline">%s</h1>', esc_html( $text ) );
	}

	/**
	 * check option object
	 *
	 * @since 1.0.0
	 */
	protected function check_option_object() {
		if ( is_a( $this->options, 'iworks_options' ) ) {
			return;
		}
		$this->options = iworks_iworks_plugins_management_get_options();
	}
}
