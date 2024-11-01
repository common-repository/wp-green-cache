<?php
/**
 Plugin Name: WP Green Cache
 Plugin URI: http://www.tankado.com/wp-green-cache/
 Version: 0.1.6
 Description: WP Green Cache plugin is a cache system for WordPress Blogs to improve performance and save the World.
 Author: Özgür Koca
 Author URI: http://www.facebook.com/zerostoheroes
*/

/*  Copyright 2010 Ozgur Koca  (email : ozgur.koca@linux.org.tr)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/
	// Include WP codes
	include_once(ABSPATH . 'wp-includes/plugin.php');
	include_once(ABSPATH . 'wp-includes/functions.php');
	
	// Include external codes
	include_once(dirname(__FILE__).'/def.php');
	include_once(dirname(__FILE__).'/lib.php');
	include_once(dirname(__FILE__).'/options.php');
	
	// Load wpgc settings
	$options = wpgc_get_options();

	// Install plugin
	register_activation_hook(__FILE__, 'wpgc_install');
	
	// Install plugin
	register_deactivation_hook(__FILE__, 'wpgc_uninstall');	
	
	// Add options page
	add_action('admin_menu', 'wpgc_add_options');
	
	// Performance box
	add_filter('wp_footer', 'wpgc_show_performance_footer');	
	add_action('admin_footer', 'wpgc_show_performance_footer');
	
	// Post, comment etc. updates
	wpgc_handle_user_interactions();
?>