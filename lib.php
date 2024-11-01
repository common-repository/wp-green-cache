<?php
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

	// Include external codes	
	include_once(dirname(__FILE__).'/def.php');	
	include_once(dirname(__FILE__).'/options.php');		
	
	// Called whenever the page generation is ended	
	function wpgc_ob_callback($ob_buffer)
	{		
		$options = wpgc_get_options();

		// Cache the output buffer (ob) of php
		if ( $options['enabled'] && wpgc_is_cachable( $ob_buffer ) )
		{				
			$content = $ob_buffer;
			
			// Remove performance footer (attaching from the wp_footer-filter)
			if ($options['perf_footer'])
				$content = wpgc_remove_footer( $content );

			// Compress HTML
			if ($options['compress'])
				$content = wpgc_compress( $content );
			
			// Create cache			
			wpgc_create_cache_file( $content, $ob_buffer );
			
			# Debugging code
			if (DEBUG_MODE)
			wpgc_add_to_file(dirname(__FILE__).'/cache/url.txt', time().' - MakCache: '.wpgc_get_url()."\n");
			
			// If newly cached content
			if ($options['perf_footer'] && current_user_can('manage_options') )
				return wpgc_inject_footer( $ob_buffer, wpgc_get_performance_footer( true ) );	
		}		
		
		if ($options['perf_footer'] && current_user_can('manage_options'))
			return wpgc_inject_footer( $ob_buffer, wpgc_get_performance_footer( false ) );	
		else 
			return $ob_buffer;
	}	
	
	function wpgc_delete_cache_trashed($post_id)
	{	
		wpgc_delete_cache_file($post_id);
	}
	
	function wpgc_handle_user_interactions()
	{
		add_action('publish_post', 	'wpgc_post_changed', 0);
		add_action('edit_post', 	'wpgc_post_changed', 0);
		add_action('delete_post', 	'wpgc_post_changed', 0);
		add_action('publish_phone', 'wpgc_post_changed', 0); //Runs just after a post is added via email.			
		add_action('trackback_post', 'wpgc_post_changed', 0);
		add_action('pingback_post', 'wpgc_post_changed', 0);
		add_action('comment_post', 	'wpgc_post_changed', 0);
		add_action('edit_comment', 	'wpgc_post_changed', 0);		
		add_action('delete_comment', 'wpgc_post_changed', 0);
		add_action('wp_cache_gc',	'wpgc_post_changed', 0);
		add_action('switch_theme', 	'wpgc_post_changed', 100); //**
		add_action('wp_set_comment_status', 'wpgc_post_changed', 0);
		add_action('edit_user_profile_update', 'wpgc_post_changed', 100);
		add_action('trash_post', 'wpgc_delete_cache_trashed', 10);			
	}
	
	function wpgc_get_domain()
	{
		$ret = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
		$ret .= $_SERVER['HTTP_HOST'];
		return $ret;
	}
	
	function wpgc_get_url()
	{
		return wpgc_get_domain().$_SERVER['REQUEST_URI'];
	}
	
	function wpgc_get_cache_filename()
	{	
		// AUTH_KEY uniqly modifies file name for guessing attempts
		// otherwise someone on the server can modify the content of cache file.
		return dirname(__FILE__).'/cache/'.md5( AUTH_KEY . wpgc_get_url() ).'.dat';			
	}
	
	// Delete from disk
	function wpgc_delete_cache_file($delete_me)
	{
		# Delete with post_id = delete_me
		if (is_numeric($delete_me))
		{
			$permalink = get_permalink($delete_me);
			@unlink( dirname(__FILE__).'/cache/'.md5( AUTH_KEY . $permalink ).'.dat' );
			@unlink( dirname(__FILE__).'/cache/'.md5( AUTH_KEY . wpgc_get_domain() . '/').'.dat' ); //index.php
			return;
		}
		
		# Delete with permalink = delete_me
		
	}
	
	// Delete all category pages
	function wpgc_delete_category_pages()
	{
		$cats = get_categories();
		foreach($cats as $cat) {					
			$category_id = get_cat_ID($cat->name);
			$category_link = get_category_link( $category_id );
			wpgc_delete_cache_file($category_link);
		}
	}
	
	// Delete cached page on change
	function wpgc_post_changed($post_id)
	{		
		// Delete cache file from disk
		wpgc_delete_cache_file($post_id);
		
		// Delete all category pages
		wpgc_delete_category_pages();
		
		# Debugging code
		if (DEBUG_MODE)
		{
			$permalink = get_permalink($post_id);
			if (!empty($permalink))
			wpgc_add_to_file(dirname(__FILE__).'/cache/url.txt', time().' - DelCache: '.$permalink."\n");
			wpgc_add_to_file(dirname(__FILE__).'/cache/url.txt', time().' - DelCache: '.wpgc_get_domain()."/\n");			
		}
	}
	
	function wpgc_check_gz_funcs()
	{
		if (function_exists('gzencode') && function_exists('gzdecode'))
			return C_METHOD_1;
		
		if (function_exists('gzcompress') && function_exists('gzuncompress'))
			return C_METHOD_2;
		
		return 0;
	}
	
	function wpgc_compress($data)
	{
		global $options;
		if (function_exists('gzencode') && ($options['compress'] == C_METHOD_1) )
			return gzencode( $data, 4, FORCE_GZIP  );
		
		if (function_exists('gzcompress') && ($options['compress'] == C_METHOD_2) )
			return gzcompress( $data, 4 );
		
		return $data;
	}
	
	function wpgc_uncompress($data)
	{
		global $options;		
		if (function_exists('gzdecode') && ($options['compress'] == C_METHOD_1) )
			return gzdecode( $data );
		
		if (function_exists('gzuncompress') && ($options['compress'] == C_METHOD_2) )
			return gzuncompress( $data );
		
		return $data;
	}
	
	function wpgc_add_to_file($file_name, $text)
	{
		file_put_contents( $file_name, file_get_contents( $file_name ).$text);
	}
		
	function wpgc_activate()
	{
		if ( wpgc_is_config_writable() )
		{
			// Add WP_CACHE define to wp-config.php
			if ( wpgc_add_define(WPC_ENABLED, ABSPATH . 'wp-config.php') )
			{	
				if ( file_exists( ABSPATH.'wp-content/advanced-cache.php' ) 
					&& ( strpos(@file_get_contents( ABSPATH.'wp-content/advanced-cache.php' ), WPGC_SIGN) == false  ))
				{
					@unlink(ABSPATH.'wp-content/plugins/wp-green-cache/advanced-cache-bck.php');
					
					//move existing 3rd party advanced-cache.php
					@rename( ABSPATH.'wp-content/advanced-cache.php', 
						ABSPATH.'wp-content/plugins/wp-green-cache/advanced-cache-bck.php');
				}
				
				copy( ABSPATH.'wp-content/plugins/wp-green-cache/advanced-cache.php',
					ABSPATH.'wp-content/advanced-cache.php');
			}
		}
	}
	
	function wpgc_deactivate()
	{
		if ( wpgc_is_config_writable() )
		{
			// Remove WP_CACHE define from wp-config.php
			wpgc_remove_define(WPC_ENABLED, ABSPATH . 'wp-config.php');
			
			@unlink(ABSPATH.'wp-content/advanced-cache.php');
			
			if (file_exists( ABSPATH.'wp-content/plugins/wp-green-cache/advanced-cache-bck.php' ))			
				@rename( ABSPATH.'wp-content/plugins/wp-green-cache/advanced-cache-bck.php',
					ABSPATH.'wp-content/advanced-cache.php');
			else
				@unlink( ABSPATH.'wp-content/advanced-cache.php' );
		}
	}
	
	// Uninstall wpgc plugin
	function wpgc_uninstall()
	{
		// Clean cache directory
		wpgc_clean_cache();
			
		// De activate WP cache system
		wpgc_deactivate();
	}
	
	// Install wpgc plugin
	function wpgc_install()
	{
		if ( is_admin() )
		{ 
			if ( wpgc_is_config_writable() && wpgc_is_cache_writable() )
			{
				if (!isset($options['cache_ttl'])) 
				{	
					// Default values
					wpgc_set_defaults();
				}
				wpgc_activate();
			}
		} 
	}	
	
	function wpgc_set_defaults()
	{
		$options = wpgc_get_options();
		$options['cache_ttl'] = 8*60;
		$options['enabled'] = false;
		$options['clean_interval'] = 7*24*60;
		$options['last_cleaning'] = time();
		$options['compress'] = wpgc_check_gz_funcs();
		$options['perf_footer'] = false;
		wpgc_update_options($options);	
	}
	
	// Add menu item to wp manager for options
	function wpgc_add_options() 
	{
		add_options_page('WP Green Cache Options', 'WP Green Cache', 'manage_options', 'wpgc_options_id', 'wpgc_options');
	}

	function wpgc_notice_box( $notice )
	{		
		echo '<div class="error fade" style="background-color:red;color:white;"><p>' . $notice . '</p></div>';		
	}	
	
	function wpgc_is_cache_writable() 
	{			
		$wpgc_notice = '';
		
		if ( !is_dir( dirname(__FILE__).'/cache' ) )
		if ( !($dir = @mkdir( dirname(__FILE__).'/cache', 0777) ) ) 
			$wpgc_notice .= '<b>Warning:</b> WP Green Cache plugin was not able to create the dir "cache" '
				.'in its installation dir("'.dirname(__FILE__).'"). '
				.'Create it by hand and make it writable or check permissions.<br />';

		if ( !is_writable( dirname(__FILE__) . '/cache' )) 			
			$wpgc_notice .= '<b>Warning:</b> WP Green Cache plugin was not able to write cache directory "'
				.dirname(__FILE__).'/cache/". Please make the cache dir is writable.<br />';

		if (!empty($wpgc_notice))
		{
			wpgc_notice_box( $wpgc_notice );
			return false;
		}
		
		return true;
	}
	
	function wpgc_check_configuration()
	{
		$ret = True;
		
		$options = wpgc_get_options();
		if (!$options['enabled'])
			return $ret;
		
		# Check WP_CACHE define
		if (!defined('WP_CACHE'))
		{
			$ret = False;
			$message = sprintf("Warning: The plugin not functional because WP_CACHE not defined in <b>%s</b> file. You can add manually or first deactivate the plugin and activate again.",
				'/wp-config.php');
			wpgc_notice_box($message);
		}
		
		# Check advanced-cache.php if exists
		if (!file_exists( ABSPATH.'wp-content/advanced-cache.php' ))
		{
			$ret = False;
			$message = sprintf("Warning: Plugin not funcitonal because the <b>%s</b> file not exists. You can manually copy <b>%s</b> to <b>%s</b> or deactivate the plugin and activate again.", 
				ABSPATH.'wp-content/advanced-cache.php',
				ABSPATH."wp-content/plugins/wp-green-cache/advanced-cache.php",
				ABSPATH.'wp-content/advanced-cache.php');			
			wpgc_notice_box($message);
			
		} 
		else 
		{		
			# Validate the advanced-cache.php file
			if (strpos(file_get_contents( ABSPATH.'wp-content/advanced-cache.php' ), WPGC_SIGN) === false) 
			{
				$ret = False;
				$message = sprintf("Warning: Plugin not funcitonal because the <b>%s</b> file not valid. You can manually copy <b>%s</b> to <b>%s</b> or deactivate the plugin and activate again.", 
					ABSPATH.'wp-content/advanced-cache.php',
					ABSPATH."wp-content/plugins/wp-green-cache/advanced-cache.php",
					ABSPATH.'wp-content/advanced-cache.php');				
				wpgc_notice_box($message);
			}
		}
			
		return $ret;
	}
	
	function wpgc_is_config_writable()
	{
		$wpgc_notice = '';
		
		if (!is_writable( ABSPATH . 'wp-config.php' ))
			$wpgc_notice .= _('<b>Warning:</b> Wordpress config file (wp-config.php) is not writable by server.'
				.'Check its permissions. (Dont forget to restore original value [ex: 640])');
			
		if (!is_writable( ABSPATH . 'wp-content/' ))
			$wpgc_notice .= _('<b>Warning:</b> Wordpress content directory (wp-content/) is not writable by server.'
			.'Check its permissions. (Dont forget to restore original value.)');
		
		if (!empty($wpgc_notice))
		{
			wpgc_notice_box( $wpgc_notice );
			return false;
		}
		
		return true;		
	}
	
	function wpgc_get_performance_footer($newly_cached = false)
	{
		global $options;
		
		if ( $options['perf_footer'] ) 
		{			
			if (function_exists( 'get_num_queries' ))
				$queries = '<b>'.get_num_queries().'</b> Queries';
			else 
				$queries = '<b>No Query</b>';
		
			if (function_exists( 'timer_stop' ))
			$exec_time = '<b>'.timer_stop(0).'</b> sec.';
			
			$state = $newly_cached ? '<b><font color=green>Newly Cached</font></b>' : ($options['enabled'] ? '<b>Enabled</b>' : '<b>Disabled</b>');
			
			$ret = '';
			$ret .= "\n<div style='width:420px;position:fixed;margin:0;margin-top:29px;opacity:0.8;padding:1px 0;right:0;top:0;z-index:10001;background-color:#DDDDDD;background:-moz-linear-gradient(center bottom , #D7D7D7, #E4E4E4) repeat scroll 0 0 transparent;font-family:Verdana,Arial,Helvetica,sans-serif;font-size:12px;'>";
			$ret .= "\n	<div style='background-color:#DDDDDD;background:-moz-linear-gradient(center bottom , #D7D7D7, #E4E4E4) repeat scroll 0 0 transparent;float:left;text-align:center;width:120px;border-right:1px solid #AAAAAA;margin:0;padding:0;opacity:0.8;color:#404040;'>";
			$ret .= "\n		<a href='http://www.tankado.com/wp-green-cache/' style='text-decoration:none;color:#404040;'>WP Green Cache</a><br>";
			$ret .= "\n		$state";
			$ret .= "\n	</div>";
			$ret .= "\n	<div style='float:left;list-style:none outside none;opacity:0.8;border-right:1px solid #AAAAAA;margin-top:5px;padding:0 5px;'>";
			$ret .= "\n		<img src='/wp-content/plugins/wp-green-cache/images/database.png' style='vertical-align:middle;'> $queries";
			$ret .= "\n	</div>";
			$ret .= "\n	<div style='float:left;list-style:none outside none;opacity:0.8;border-right:1px solid #AAAAAA;margin-top:5px;padding:0 5px;'>";
			$ret .= "\n		<img src='/wp-content/plugins/wp-green-cache/images/time.png' style='vertical-align:middle;'> $exec_time";
			$ret .= "\n	</div>";
			$ret .= "\n	<div style='float:left;list-style:none outside none;opacity:0.8;margin-top:5px;padding:0 5px;'>";
			$ret .= "\n		<img src='/wp-content/plugins/wp-green-cache/images/options.png' style='vertical-align:middle;'> <a href='/wp-admin/options-general.php?page=wpgc_options_id'>Options</a>";
			$ret .= "\n	</div>";
			$ret .= "\n</div>";
			
			$ret = FOOTER_START . $ret . FOOTER_END ;
			
			return $ret;
		}
	}
	
	function wpgc_remove_footer($content)
	{
		global $options;
		
		$ret = $content;		
		
		if ((strpos($content, FOOTER_START) !== false) and (strpos($content, FOOTER_END) !== false))
		{		
			$ret = substr($content, 0 , strpos($content, FOOTER_START))
				. substr($content, strpos($content, FOOTER_END) + strlen(FOOTER_END), strlen($content));
		}		
		return $ret;
	}
	
	function wpgc_inject_footer($content, $footer)
	{
		$ret = $content;
		
		if ((strpos($content, 'body>') !== false) or (strpos($content, 'BODY>') !== false))			
		{
			$body = strpos($content, 'body>');
			if ($body !== false)
				$body = strpos($content, 'BODY>');
			
			$ret = substr($content, 0, $body - 1) . $footer . substr($content, $body - 1, strlen($content));			
		}
		return $ret;
	}
	
	function wpgc_show_performance_footer()
	{
		if ( current_user_can('manage_options') )
		echo wpgc_get_performance_footer();
	}
	
	function wpgc_formatBytes($bytes, $precision = 2) 
	{
		$units = array('B', 'KB', 'MB', 'GB', 'TB');
		$bytes = max($bytes, 0);
		$pow = floor(($bytes ? log($bytes) : 0) / log(1024));
		$pow = min($pow, count($units) - 1);	  
		$bytes /= pow(1024, $pow);
	  
		return round($bytes, $precision) . ' ' . $units[$pow];
	}
	
	function wpgc_get_cache_status()
	{
		$count = 0;
		$size = 0;
		if ($handle = @opendir(dirname(__FILE__) . '/cache'))
		{
			while (false !== ($file = readdir($handle)))
			{
				if ($file != '.' && $file != '..' && $file != 'options.dat')
				{
					$count++;
					$size += filesize( dirname(__FILE__) . '/cache/' . $file );
				}
			}
			closedir($handle);
			$ret = 'Files count: '.$count . ',  Total size: ' . wpgc_formatBytes($size, 2).'';
		
		} else
		$ret = 'Couldnt open dir: ' . dirname(__FILE__) . '/cache';
		
		$ret .= " (<a href='".$_SERVER['REQUEST_URI']."&clean_cache=true' title='"._('Delete all cached files')."'>"._('Clear cache')."</a>)";
		
		return $ret;
	}
	
	function wpgc_clean_cache() 
	{
		if ($handle = @opendir(dirname(__FILE__) . '/cache'))
			while (false !== ($file = readdir($handle)))
				if ($file != '.' && $file != '..' && $file != 'options.dat')
				@unlink(dirname(__FILE__) . '/cache/' . $file);
			
		closedir($handle);	
	}
	
	function wpgc_clean_expired_cache() 
	{
		global $options;
		
		if ($handle = @opendir(dirname(__FILE__) . '/cache'))
			while (false !== ($file = readdir($handle)))
				if ( ($file != '.' && $file != '..' && $file != 'options.dat')
					&& ((time() - filemtime($file)) >= ($options['cache_ttl'] * 60)) )
				@unlink(dirname(__FILE__) . '/cache/' . $file);
			
		closedir($handle);	
	}	
	
	function wpgc_check_for_cache_cleaning() 
	{
		global $options;
		
		if ( $options['clean_interval'] > 0 )
		if ((time() - $options['last_cleaning']) >= ($options['clean_interval'] * 60) )
		{
			wpgc_clean_expired_cache();
			$options['last_cleaning'] = time();
			wpgc_update_options($options);
		}
	}
	
	function wpgc_is_valid_cache($fname)
	{
		global $options;
		
		if (file_exists( $fname ))
		{
			if ((time() - filemtime($fname)) >= ($options['cache_ttl'] * 60))
			{
				@unlink( $fname );
				
				# Debug Code
				if (DEBUG_MODE)
				wpgc_add_to_file(dirname(__FILE__).'/cache/url.txt', time().' - DelCache: '.wpgc_get_url()."\n");				
				
				return false;
			} 			
			return true;
		}
		return false;
	}
	
	// Dont cache WP Manager pages
	function wpgc_is_cachable($ob_buffer = 'something')
	{
		if ( strlen( trim($ob_buffer) ) == 0 )
			return false;
		
		if ( (strpos($_SERVER['REQUEST_URI'], '/wp-content/') !== false) ||
			(strpos($_SERVER['REQUEST_URI'], '/wp-includes/') !== false) ||
			(strpos($_SERVER['REQUEST_URI'], '/wp-admin') !== false) ||
			(strpos($_SERVER['REQUEST_URI'], '/wp-login') !== false) ||
			(strpos($_SERVER['REQUEST_URI'], 'wp-cron.php') !== false) ||
			(strpos($_SERVER['REQUEST_URI'], 'wp-comments-post.php') !== false) )
		return false;
		
		return true;
	}
	
	function wpgc_add_define($new, $my_file)
	{
		if (!is_writable($my_file)) 
		{
			wpgc_notice_box( "Error: file $my_file is not writeable.<br />\n" );
			return false;
		}
		
		$found = false;
		$lines = file($my_file);
		foreach($lines as $line) 
		{
			if ( strpos($line, $new) !== false ) 
			{
				$found = true;
				break;
			}
		}
		
		if (!$found) 
		{
			$fd = fopen($my_file, 'w');
			$done = false;
			foreach($lines as $line) 
			{
				if ( $done || !(strpos(strtolower($line), 'define') !== false))
					fputs($fd, $line);
				else {
					fputs($fd, "$new");
					fputs($fd, $line);
					$done = true;
				}
			}
			fclose($fd);
			return true;
		}
		return false;
	}
	
	function wpgc_remove_define($new, $my_file)
	{
		if (!is_writable($my_file)) 
		{
			wpgc_notice_box( "Error: file $my_file is not writeable.<br />\n" );
			return false;
		}
		
		$found = false;
		$lines = file($my_file);
		foreach($lines as $line) 
		{
			if ( strpos($line, $new) !== false) 
			{
				$found = true;
				break;
			}
		}
		
		if ($found) 
		{
			$fd = fopen($my_file, 'w');
			$done = false;
			foreach($lines as $line) 
			{
				if ( strpos($line, $new) === false )
					fputs($fd, $line);
			}
			fclose($fd);
			return true;
		}
	}
	
	function wpgc_update_options($arr)
	{
		file_put_contents(ABSPATH.'wp-content/plugins/wp-green-cache/cache/options.dat',
			 serialize( $arr ) );
	}
	
	function wpgc_get_options()
	{
		$ret = unserialize( file_get_contents(
			ABSPATH.'wp-content/plugins/wp-green-cache/cache/options.dat'));
		
		if ( empty($ret) )
			wpgc_set_defaults();
		
		$ret = unserialize( file_get_contents(
			ABSPATH.'wp-content/plugins/wp-green-cache/cache/options.dat'));
		
		return $ret;
	}
	
	function wpgc_create_cache_file( $content, $ob_buffer )
	{
		$cache_file = wpgc_get_cache_filename();
		file_put_contents( $cache_file, $content );

		// Create cache meta file
		$compressed_size = strlen($content);
		$normal_size = strlen($ob_buffer);
		if (!($compressed_size <= ($normal_size * 0.5)))
			$compressed_size = strlen(wpgc_compress($content));
		
		$meta_arr = Array();
		$meta_arr['exec_time'] = (double) timer_stop(0, 10);
		$meta_arr['normal_size'] = $normal_size;
		$meta_arr['compressed_size'] = $compressed_size;
		$meta_arr['creation_time'] = time();
		
		$meta_file = $cache_file . '.meta';
		file_put_contents( $meta_file, serialize($meta_arr) );		
	}	
	
	function wpgc_update_performance_stat()
	{
		$meta_arr = unserialize( file_get_contents( wpgc_get_cache_filename() . '.meta' ) );
		if (is_array($meta_arr))
		{
			$options = wpgc_get_options();
			
			# Get compressed size
			if ($options['compress'] and wpgc_browser_gz_compatibility()) 
			$compressed_size = $meta_arr['compressed_size'];
			else $compressed_size = $meta_arr['normal_size'];
			
			$options['normal_sent_size'] += $meta_arr['normal_size'];
			$options['compressed_sent_size'] += $compressed_size;
			$options['request_count'] += 1;
			
			$e_time = (double) timer_stop(0, 10);			
			$options['normal_exec_time'] += (double) $meta_arr['exec_time'] + (double) $e_time;
			$options['active_exec_time'] += (double) $e_time;
			
			wpgc_update_options($options);
		}
	}
	
	function wpgc_determineOS() {

		list($os) = explode('_', PHP_OS, 2);

		// This magical constant knows all
		switch ($os) {

			// These are supported
			case 'Linux':
			case 'FreeBSD':
			case 'DragonFly':
			case 'OpenBSD':
			case 'NetBSD':
			case 'Minix':
			case 'Darwin':
			case 'SunOS':
				return 'nix';
			break;
			case 'WINNT':				
				return 'win';
			break;
			case 'CYGWIN':				
				return 'cygwin';
			break;

			// So anything else isn't
			default:
				return false;	
			break;
		}
	}	
	
	/* linfo destekleniyormu kontrolu */
	function wpgc_linfo_support(&$hw)
	{
		# PHP 5+
		if (intval(substr(PHP_VERSION, 0, 1)) < 5) {
			$hw['linfo_err'] = 'Linfo requires PHP 5+.';
			return false;
		}
		
		$os = wpgc_determineOS();
		
		# On *nix system /proc and /sys must be accesseble 
		if (($os == 'nix') or ($os == 'cygwin'))
		if (!is_dir('/sys') || !is_dir('/proc')) {
			$hw['linfo_err'] = 'Linfo needs access to /proc or/and /sys to work.';
			return false;
		}
		
		# On Windows, needs access to WMI
		if (($os == 'win')) {
			$wmi = new COM('winmgmts:{impersonationLevel=impersonate}//./root/cimv2');		
			if (!is_object($wmi)) {
				$hw['linfo_err'] = 'Linfo needs access to WMI. Please enable DCOM in php.ini and allow the current user to access the WMI DCOM object.';
				return false;
			}
		}		
				
		if (isset($hw['linfo_err']))
			return false;
		
		return True;
	}
	
	
	function wpgc_get_system_electricity_consumption(&$hw)
	{
		# Get hardware information such as
		# cpu_count, hdd_count, eth_count, main_board_devices_count
		wpgc_linfo_support($hw);
		if (!empty($hw['linfo_err']))
			return 0;		
		
		include_once('lib/linfo/lib.php');
		wpgc_get_hardware_info($hw);
		if (!empty($hw['linfo_err']))
			return 0;
	
		# calculating CPU comsumption		
		# http://en.wikipedia.org/wiki/List_of_CPU_power_dissipation		
		$cpu_watts = array(1=>55, 2=>70, 3=>90, 4=>100, 8=>130, 16=>200);
		$cpu_c = $hw['cpu_count'];
		$cpu_pw = (array_key_exists($cpu_c, $cpu_watts) ? $cpu_watts[$cpu_c] : $cpu_c * 25) * 0.8;
			
		# hdd using in full capacity comsupt 12 watt
		$hdd_pw = 2;
		
		# mainboard using
		$mb_pw = $hw['mb_dev_count'] * 1.5 * 0.075;
		
		# ethernet card using
		$eth_power = 0.5;
		
		return $cpu_pw + $hdd_pw + $mb_pw + $eth_power;
	}
	
	
	function wpgc_get_cache_gains()
	{	
		function c($str, $color) { 
			return "<font color='$color'>$str</font>"; 
		}
		$options = wpgc_get_options();
		
		# CPU runtime values
		$a_exec = sprintf("%.2f", $options['active_exec_time']);
		$n_exec = sprintf("%.2f", $options['normal_exec_time']);
		
		# zero division on first install
		if ($options['normal_exec_time'])
		$diff_exec_percent = (int) (100 - ($options['active_exec_time'] * 100 / $options['normal_exec_time']));
		$diff_exec_seconds = sprintf("%.2f", $options['normal_exec_time'] - $options['active_exec_time']);
	
		# Bandwidth values
		$n_sent = $options['normal_sent_size'];
		$c_sent = $options['compressed_sent_size'];		
		$n_sent = wpgc_formatBytes($n_sent, 0);
		$c_sent = wpgc_formatBytes($c_sent, 0);
		
		$mes .= "Total code run time decreased to %s which was %s before plugin, ";
		$mes .= 'so you saved %s (%s) of code run time. ';
		$mes .= "This plugin also enabling the bandwidth compression which ends up with ";
		$mes .= "reduced total bandwidth from %s to %s. ";		
	
		$mes = sprintf($mes, 
				c($a_exec.' seconds', 'green'),
				c($n_exec.' seconds', '#CD5C5C'),
				c($diff_exec_percent.'&percnt;', 'green'),
				$diff_exec_seconds.' seconds',
				c($n_sent, '#CD5C5C'),
				c($c_sent, 'green')
				);
				
		# ----------------------------------------
		# Calculate electricity comsumption values
		# ----------------------------------------
		if ($sys_watt = wpgc_get_system_electricity_consumption($hw)) {
	
			# Info: a tree 592,39163522 gr (0,6kg US Average) gr CO2 per kWh
			$profited_watt = sprintf("%.3f", $sys_watt * (($n_exec- $a_exec) / (60 * 60)) / 1000);
			$profited_tree = sprintf("%.3f", $profited_watt * 0.59239163522);
			
			# Power consumption message
			$pc_mes = "In this way, according to the current %s saved about %s energy and saved %s ";
			$server_conf = sprintf("(Your server has %s CPU cores, %s HD, %s network interfaces and %s mainboard devices.)",
					$hw['cpu_count'],
					$hw['hdd_count'],
					$hw['eth_count'],
					$hw['main_board_devices_count']);
					
			$pc_mes = sprintf($pc_mes,		
					c($profited_watt.' kWh', 'green'),
					'<span title="'.$server_conf.'"><u>server configuration you</u></span>',
					c($profited_tree.' trees being shorn, thank you.', 'green')
					);
		} else {
			$pc_mes = "<br><font color='gray'>Notice: Electricity compsumption could not calculated because "
				."<a href='http://linfo.sourceforge.net/' target='_blank'>Linfo</a> not supported by your server. "
				.$hw['linfo_err']."</font>";
		}
		
				
		$mes .= $pc_mes;
		$mes = _($mes);		
		return $mes;
	}	
	
	function wpgc_browser_gz_compatibility()
	{
		return strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false;
	}
?>