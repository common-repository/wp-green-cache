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
	//error_reporting(E_ALL);
	
	// Include external codes	
	include_once(dirname(__FILE__).'/def.php');
	include_once(dirname(__FILE__).'/lib.php');
	include_once(dirname(__FILE__).'/options.php');
	
	// Load wpgc settings
	$options = wpgc_get_options();
	
	// Check for cache clean
	wpgc_check_for_cache_cleaning();
		
	// if wpgc is enabled then load from cache file
	if ( $options['enabled'] && wpgc_is_cachable() )
	{		
		// include extra PHP script
		if ($include = $options['include_php'])
			include($include);
				
		$cache_file = wpgc_get_cache_filename();
		
		if ( wpgc_is_valid_cache( $cache_file ) ) 
		{
			// Send compressed data
			if ( $options['compress'] )
			{					
				$content = file_get_contents( $cache_file );
				if ( $options['perf_footer'] )
				{
					$content = wpgc_uncompress( $content );
					$content = wpgc_inject_footer( $content, wpgc_get_performance_footer() );
					$content = wpgc_compress( $content );
				}
								
				if ($http_compress = wpgc_browser_gz_compatibility())
				{				
					header( 'Content-Encoding: gzip' );
					header( 'Content-Length: ' . strlen($content) );								
					if ($http_compress == C_METHOD_2)
						$content = "\x1f\x8b\x08\x00\x00\x00\x00\x00" . $content; 
				}
				else
				$content = wpgc_uncompress( $content );
			}
			
			// Send uncompressed data
			else 
			{
				$content = file_get_contents( $cache_file );
				if ($options['perf_footer'])
					$content = wpgc_inject_footer( $content, wpgc_get_performance_footer() );				
			}
						
			# Debugging code
			if (DEBUG_MODE)
			wpgc_add_to_file(dirname(__FILE__).'/cache/url.txt', time().' - Load Cache: '.wpgc_get_url()."\n");				
			
			# Update performance statistics
			wpgc_update_performance_stat();
			
			die($content);
		}
	}

	// Manage output buffer of PHP
	ob_start('wpgc_ob_callback');
?>