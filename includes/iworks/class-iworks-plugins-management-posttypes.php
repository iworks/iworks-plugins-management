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

if ( class_exists( 'iworks_wordpress_plugin_posttypes' ) ) {
	return;
}

class iworks_wordpress_plugin_posttypes {
	protected $posttype_objects = array();

	public function __construct() {
		/**
		 * load post types
		 */
		$posttypes_classes_dir = __DIR__ . '/posttypes/';
		foreach ( glob( $posttypes_classes_dir . 'class*.php' ) as $filename_with_path ) {
			$filename = basename( $filename_with_path );
			if ( ! preg_match( '/^class-iworks-plugins-management-posttype-([a-z]+).php$/', $filename, $matches ) ) {
				continue;
			}
			$posttype_name = $matches[1];
			$filter        = sprintf(
				'iworks-plugins-management/load/posttype/%s',
				$posttype_name
			);
			if ( apply_filters( $filter, false ) ) {
				include_once $posttypes_classes_dir . $filename;
				/**
				 * Class Name
				 */
				$class_name = sprintf( 'iworks_iworks_plugins_management_posttype_%s', $posttype_name );
				/**
				 * Init Class Object
				 */
				$this->posttype_objects[ $posttype_name ] = new $class_name();
			}
		}
	}

}
