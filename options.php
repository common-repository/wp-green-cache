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

function wpgc_options() 
{
	if (is_admin()) 
	{				
		// Print warnings about directory permisions
		$wpgc_ok = wpgc_is_cache_writable() && wpgc_is_config_writable() && wpgc_check_configuration();
		
		if ( $wpgc_ok )
		{				
			if (isset($_POST['save'])) 
			{
				// Clear inbound parameters
				$old_options = wpgc_get_options();							
				$options = stripslashes_deep($_POST['options']);
				
				// Clean cache switching on compressed/uncompressed modes
				if ( $options['compress'] != $old_options['compress'] )
					wpgc_clean_cache();
					
				wpgc_update_options($options);	
				
				// Activate/deactivate cache mechanism
				if ( $options['enabled'] != $old_options['enabled'] )
				if ( $options['enabled'] )
					wpgc_activate();			
				else
					wpgc_deactivate();				
			}
			
			if (isset($_GET['clean_cache']))
				wpgc_clean_cache();			
			
			$options = wpgc_get_options();
		}
		?>
		<br>
		<table width='100%' border="0">
			<tr >
				<td valign='top' height='100'>
					<a href='http://www.tankado.com/wp-green-cache/' target='_blank'>
					<img src="/wp-content/plugins/wp-green-cache/images/WPGC-banner.png">
					</a>
				</td>
				<td valign='top' width='100%'>
					<iframe height='100' width='95%' scrolling='no' frameborder='0' src='http://www.tankado.com/projects/my_wp_plugins/plugin_head_right.php'></iframe>
				</td>
			</tr>
			<tr>
				<td colspan='2' valign='top' height='70'>
					<iframe height='95%' width='95%' scrolling='no' frameborder='0' src='http://www.tankado.com/projects/my_wp_plugins/plugin_head_bottom.php'></iframe>				
				</td>
			</tr>
		</table>
		
		<div class="wrap">
		<div id="poststuff" class="metabox-holder">
		<div class="meta-box-sortables">
		<script>
			jQuery(document).ready(function($) {
				$('.postbox').children('h3, .handlediv').click(function(){
					$(this).siblings('.inside').toggle();
				});
			});
		</script>
		
		<?php if( !$wpgc_ok )
			echo "<div style='background-color:#d0d0d0;z-index:1000;opacity:0.85'>";
		?>
			
		<form method="post">
		
			<!-- #################################################################################### -->
			<div class="postbox" id="dashboard_right_now">
			<div class="handlediv" title="Click to open/close"><br /></div>
				<h3 class='hndle'><?php _e('Cache status', 'wpgc'); ?></h3>
				<div class="inside">
					<table class="form-table">
					<tr valign="top">
						<td>
							<?php _e('Enable WP Green Cache', 'wpgc'); ?>
							<input type="hidden" name="options[enabled]" value="0">&nbsp;&nbsp;&nbsp;&nbsp;
							<input type="checkbox" name="options[enabled]" value="1" <?php echo $options['enabled']?'checked':''; ?>/> <br />
						</td>
					</tr>
					<tr valign="top">						
						<td><?php echo wpgc_get_cache_status(); ?></td>
					</tr>
					<tr valign="top">						
						<td><?php 
								echo wpgc_get_cache_gains(); 
								echo "<input name='options[normal_sent_size]' value='{$options['normal_sent_size']}' type='hidden'>";
								echo "<input name='options[compressed_sent_size]' value='{$options['compressed_sent_size']}' type='hidden'>";
								echo "<input name='options[request_count]' value='{$options['request_count']}' type='hidden'>";
								echo "<input name='options[normal_exec_time]' value='{$options['normal_exec_time']}' type='hidden'>";
								echo "<input name='options[active_exec_time]' value='{$options['active_exec_time']}' type='hidden'>";
							?>
						</td>
					</tr>				
					<tr>
						<td>							
							<input class="button" type="submit" name="save" value="<?php _e('Save'); ?>" <?php echo (!$wpgc_ok) ? 'disabled':''; ?>>							
						</td>
					</tr>
					</table>
				</div>
			</div>
			
			<!-- #################################################################################### -->
			<div class="postbox closed" id="dashboard_right_now">
			<div class="handlediv" title="Click to open/close"><br /></div>
			<h3 class='hndle'><?php _e('Cache settings', 'wpgc'); ?></h3>
			<div class="inside">			
			
			<table class="form-table">			
			<tr valign="top">
				<th><?php _e('Cached pages timeout', 'wpgc'); ?></th>
				<td>
					<input type="text" size="5" name="options[cache_ttl]" value="<?php echo htmlspecialchars($options['cache_ttl']); ?>"/> (<?php _e('minutes', 'wpgc'); ?>) <br />
					<font color='gray'>
					<?php _e('Minutes a cached page is valid and served to users. A zero value means a cached page is valid forever.', 'wpgc'); ?>
					<?php _e('If a cached page is older than specified value (expired) it is no more used and will be regenerated on next request of it.', 'wpgc'); ?>
					<?php _e('720 minutes is half a day, 1440 is a full day and so on.', 'wpgc'); ?>
					</font>
				</td>
			</tr>
			<tr valign="top">
				<th><?php _e('Cache autoclean', 'wpgc'); ?></th>
				<td>
					<input type="hidden" name="options[last_cleaning]" value="<?php echo $options['last_cleaning']; ?>">
					<input type="text" size="5" name="options[clean_interval]" value="<?php echo htmlspecialchars($options['clean_interval']); ?>"/> (<?php _e('minutes', 'wpgc'); ?>) <br />
					<font color='gray'>
					<?php _e('Frequency of the autoclean process which removes to expired cached pages to free disk space.', 'wpgc'); ?>
					<?php _e('If Cache autoclean is set to zero, autoclean never runs.', 'wpgc'); ?>
					</font>
				</td>
			</tr>
			<tr valign="top">
				<th><?php _e('Compression', 'wpgc'); ?></th>
				<td>
				<?php if (!wpgc_check_gz_funcs()) { ?>
				<p><?php _e('<i>Your hosting server has not the gzcompress/gzuncompress and gzencode/gzdecode functions, so no compression options are available.</i>', 'wpgc'); ?></p>
				<?php } else { ?>
					<input type="hidden" name="options[compress]" value="0">
					<input type="checkbox" name="options[compress]" value="<?php echo wpgc_check_gz_funcs(); ?>" <?php echo $options['compress']?'checked':''; ?> /> <br />
					<font color='gray'>
					<?php _e('If possible the page will be sent and stored as compressed to save bandwidth and storege quate.', 'wpgc'); ?>
					<?php _e('If you switch this option then the cache will be cleaned.', 'wpgc'); ?>
					</font>
				<?php } ?>		
				</td>
			</tr>
			<tr valign="top">
				<th><?php _e('Show performance box', 'wpgc'); ?></th>
				<td>
					<input type="hidden" name="options[perf_footer]" value="0">
					<input type="checkbox" name="options[perf_footer]" value="1" <?php echo $options['perf_footer']?'checked':''; ?>/> <br />
					<font color='gray'>
					<?php _e('When activated the performance box it will be attached to top-right of every page.', 'wpgc'); ?>
					<?php _e('Performance box views SQL query count and page generation time that you inform about blog performance.', 'hyper-cache'); ?>
					<?php _e('I suggest you to see advantage of wpgc compare the values when wpgc is enabled and disabled.', 'wpgc'); ?>
					</font>
				</td>
			</tr>
			<tr>
				<td>
					<p class="submit">
					<input class="button" type="submit" name="save" value="<?php _e('Update'); ?>" <?php echo (!$wpgc_ok) ? 'disabled':''; ?>>
					</p>
				</td>
			</tr>				
			</table>
			</div>
			</div>
			
			<!-- #################################################################################### -->
			<div class="postbox closed" id="dashboard_right_now">
			<div class="handlediv" title="Click to open/close"><br /></div><h3 class='hndle'><?php _e('Extra options', 'wpgc'); ?></h3>
			<div class="inside">	
				<table class="form-table">
				<tr valign="top">
					<th><?php _e('Include php', 'wpgc'); ?></th>
					<td>
						<input type="text" size="70" name="options[include_php]" value="<?php echo htmlspecialchars($options['include_php']); ?>"/><br>
						<font color='gray'>
						<?php _e('When wpgc is active if you run any php script write the path of script starting from server root.', 'wpgc'); ?><br>
						<?php _e('Example: ', 'wpgc'); ?>
						<?php echo '<i>'.dirname(__FILE__).'/example.php'.'</i>'; ?>
						</font>
					</td>
				</tr>
				<tr>
					<td>
						<p class="submit">
						<input class="button" type="submit" name="save" value="<?php _e('Update'); ?>" <?php echo (!$wpgc_ok) ? 'disabled':''; ?>>
						</p>
					</td>
				</tr>	
				</table>				
			</div>
			</div>
			
		</form>
		<?php if (!$wpgc_ok)
			echo "</div>";
		?>
		</div>
		</div>
		</div>
			<a title='To send bu report you must login the WordPress.org' href='http://wordpress.org/support/bb-login.php?redirect_to=http://wordpress.org/tags/wp-green-cache#postform' target='_blank'>Send bug report about WP Green Cache</a>
		</div>		
		<?php
	}
}
?>