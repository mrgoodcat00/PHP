<?php
/*  Plugin Name: Check Out Gmail
	Description: Make a projects list from Gmail letters.
	Author: Mykhailiuk Nazar netmaster87@mail.ru
	Text Domain: check_out_emails
	Version: 1.0
	License: GPL2
	*/
/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

Copyright 2005-2015 Mykhailiuk Nazar.
*/
	defined( 'ABSPATH' ) or die( 'Hacker?' );
	
	define("CHOP_BASE_PATH",plugin_dir_path(__FILE__));
	define("CHOP_BASE_URL",plugin_dir_url(__FILE__));
	define("CHOP_PLUGIN_VER","1.0");
	include(CHOP_BASE_PATH."includes/GmailCheckClass.php");

	
	function chop_create_objects(){
		$Gmail_object = new GmailCheck();	
	}
	
	function chop_admin_page(){
		
		$Gmail_object = new GmailCheck();						
		$post_data = $_POST['check_out_emails_options'];
		$params = array( 
				'CHOP_plugin_options' => 
					array( 
						'check_mail_opt' => array(
							'client_id' => $post_data['check_mail_opt']['client_id'],
							'client_secret_key' => $post_data['check_mail_opt']['client_secret_key'],
							'redirect_url' => $post_data['check_mail_opt']['redirect_url'],
							'search_from' => $post_data['check_mail_opt']['search_from'],
						)							
					)
				);
			 
		if(isset($_POST['save_parameters']) && $_POST['save_parameters'] == "true"){
			update_option("CHOP_plugin_options",$params);
			?><script> location.reload() </script><?php
		}
		
		echo '<div class="wrap">';
			echo '<h1>' .__("Che out Emails Plugin Settings","check_out_emails"). '</h1> <hr>';		
			echo '
				<table class="wp-list-table widefat fixed striped pages" style="width: 70%;">
					<tbody id="the-list">
						<form method="POST" action="http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'">
							'.$Gmail_object->Check_Gmail_Form().'								
							<tfoot>
								<tr>
									<td colspan="2">													
										<p style="text-align:right;">
											<input type="hidden" name="save_parameters" value="true"/>
											<input type="submit" class="button action" value="'.__("Submit","check_out_emails").'"/>
										</p>
									</td>						
								</tr>
							</tfoot>
						</form>
					</tbody>
				</table>';			
		echo '</div>';
	}

/*	
	function rsc_session_destroy(){
		 if ( session_id() ) {						
			session_unset();
			session_destroy();
		}
	}
/*	
	function rsc_load_langpack(){
		global $l10n;
		
		load_plugin_textdomain('check_out_emails',false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
		
		$domain = 'check_out_emails';
		$locale = get_locale();		
		
		$mo_orig = $l10n[$domain];
		unload_textdomain( $domain );
		
		$mofile = $domain . '-' . $locale . '.mo';
		$path = RSC_BASE_PATH . 'languages';
	
		if ( $loaded = load_textdomain( $domain, $path . '/'. $mofile ) ) {
			return $loaded;
		} else {
			$mofile = WP_LANG_DIR . '/plugins/' . $mofile;
			return load_textdomain( $domain, $mofile );
		}

		$l10n[$domain] = $mo_orig;
	}
*/	


	add_action( 'init', 'chop_create_objects' );
	
	function chop_plugin_activation(){				
	}
	
	function chop_plugin_deactivation(){	
	}
	
	function chop_plugin_uninstall(){
		delete_option( 'CHOP_plugin_options');		
	}
	
	function chop_menu_page() {
		add_menu_page( __( "CHOP Adjustments", "check_out_emails" ), __( "CHOP Adjustments", "check_out_emails" ),0, "chop-page", "chop_admin_page", CHOP_BASE_URL.'img/gmail_icon.png', $position );
	} 
	
	add_action( 'admin_menu', 'chop_menu_page' );
	register_uninstall_hook(__FILE__, "chop_plugin_uninstall");
	register_activation_hook(__FILE__, "chop_plugin_activation");
	register_deactivation_hook(__FILE__, "chop_plugin_deactivation");
	
?>