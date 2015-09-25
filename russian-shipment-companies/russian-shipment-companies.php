<?php
/*  Plugin Name: Russian Delivery Companies.
	Description: Gives you abilities to check out your freight locations through the delivery company API. Working with <a href="http://jde.ru">"ЖелДорЭкспедиция"</a> and <a href="http://www.dellin.ru/">"Деловые Линии"</a>.
	Author: Mykhailiuk Nazar netmaster87@mail.ru
	Text Domain: RSC_plugin
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
	
	define("RSC_BASE_PATH",plugin_dir_path(__FILE__));
	define("RSC_BASE_URL",plugin_dir_url(__FILE__));
	define("RSC_PLUGIN_VER","1.0");
	include(RSC_BASE_PATH."inc/JdeApiClass.php");
	include(RSC_BASE_PATH."inc/DlApiClass.php");
	include(RSC_BASE_PATH."inc/UserClass.php");
	
	
	
	function create_objects(){
		if(rsc_check_user()) $JDE_object = new JDE_Api(); 
		if(rsc_check_user()) $DL_object = new DL_Api();
		$User_object = new RSC_User();		
	}
	
	function rsc_admin_page(){
		
		$JDE_object = new JDE_Api();
		$DL_object = new DL_Api();
		 
		$post_data = $_POST['RSC_plugin_options'];
		
		$params = array( 
					'RSC_plugin_options' => 
						array( 
							'DL_opt' => array(
								'dl_id' => $post_data['DL_opt']['dl_id'],
								'dl_r_url' => $post_data['DL_opt']['dl_r_url']
							),
							'JDE_opt' => array(
								'jde_url' => $post_data['JDE_opt']['jde_url'],
								'jde_uid' => $post_data['JDE_opt']['jde_uid'],
								'jde_token' => $post_data['JDE_opt']['jde_token'],
								'jde_test' => $post_data['JDE_opt']['jde_test'],
								'jde_show_path' => $post_data['JDE_opt']['jde_show_path'],
								'jde_sender' => $post_data['JDE_opt']['jde_sender'],
								'jde_receiver' => $post_data['JDE_opt']['jde_receiver'],
								'jde_cargo_info' => $post_data['JDE_opt']['jde_cargo_info']
							)
						)
					);
			 
		if(isset($_POST['save_parameters']) && $_POST['save_parameters'] == "true"){
			update_option("RSC_plugin_options",$params);
			?><script> location.reload() </script><?php
		}
		
		echo '<div class="wrap">';
			echo '<h1>' .__("Delivery Plugin Settings","RSC_plugin"). '</h1> <hr>';		
			echo '
				<table class="wp-list-table widefat fixed striped pages" style="width: 70%;">
					<tbody id="the-list">
						<form method="POST" action="http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'">
							'.$JDE_object->JDE_Admin_Form().'
							'.$DL_object->DL_Admin_Form().'			
							<tfoot>
								<tr>
									<td colspan="2">													
										<p style="text-align:right;">
											<input type="hidden" name="save_parameters" value="true"/>
											<input type="submit" class="button action" value="'.__("Submit","RSC_plugin").'"/>
										</p>
									</td>						
								</tr>
							</tfoot>
						</form>
					</tbody>
				</table>';			
		echo '</div>';
	}
	
	function rsc_check_user(){
		if(is_user_logged_in()){
			$role_name = '';
			$user = new WP_User( get_current_user_id() );
			foreach($user->caps as $role_name => $name){}
			if($role_name == 'client' || $role_name == 'administrator'){return true;}			
		} else {return false;}		
	}
	
	function rsc_user_login_failed( $username ){
		$referrer = wp_get_referer();
		if ( $referrer && ! strstr($referrer, 'wp-login') && ! strstr($referrer, 'wp-admin') )
		{			
			if ( empty($_GET['loggedout']) ){
				$_SESSION['login_failed_errors'][] = "login_failed";
				wp_redirect( $referrer );
			} else {
				$_SESSION['login_failed_errors'][] = "login_loggedout";
				wp_redirect($referrer );
			}
			exit;
		}
	}
	
	function rsc_user_authenticate_username_password( $user, $username, $password )
	{
		if ( is_a($user, 'WP_User') ) { $_SESSION['login_failed_errors'][] = "login_true";  return $user; }
		if ( empty($username) || empty($password) ){
			$_SESSION['login_failed_errors'][] = "login_false";
			$error = new WP_Error();
			$user  = new WP_Error('authentication_failed', __('<strong>ERROR</strong>: Invalid username or incorrect password.','RSC_plugin'));

			return $error;
		}
	}
	
	function rsc_user_register_fail_redirect( $sanitized_user_login, $user_email, $errors ){
		$errors = apply_filters( 'registration_errors', $errors, $sanitized_user_login, $user_email );
		$redirect_url = $_REQUEST['redirect_to'];
		if ( $errors->get_error_code() ){			
			foreach ( $errors->errors as $e => $m ){	
				if ( session_id() ){
					$_SESSION['redirect_failed_errors'][] = $e; 
				} else {
					$redirect_url = add_query_arg( $e, '1', $redirect_url );   
				}				
			}
				if ( session_id() ){
					$_SESSION['redirect_failed_errors'][] = 'register_false'; 
				} else {
					$redirect_url = add_query_arg( 'register', 'false', $redirect_url );    
				}						
			wp_redirect( $redirect_url );
			exit;   
		} else {
			$_SESSION['redirect_failed_errors'][] = "register_true"; 
			$redirect_url = add_query_arg( 'register', 'true', $redirect_url );    
			wp_redirect( $redirect_url );
		}
	}	
	
	function rsc_plugin_activation(){
		
		$my_post = array(
			'post_title' => __("Check out my cargos by invoice number","RSC_plugin"),
			'post_content' => '[check_jde]<br>[check_dl]',
			'post_status' => 'publish',
			'post_author' => 1,
			'post_type' => 'page'
		);
		wp_insert_post( $my_post );
		
		add_role( 'client', __("Client","RSC_plugin"), 
				array(
				'read'         => true,  
				'edit_posts'   => false,
				'delete_posts' => false,
			) 
		);
		
		if(get_option('users_can_register') == '0'){update_option( 'users_can_register', '1');}
		
		update_option( 'default_role', 'client');	
		
		$params = array( 
					'RSC_plugin_options' => 
						array( 
							'DL_opt' => array(
								'dl_r_url' => 'https://api.dellin.ru/v1/public/tracker.json'
							),
							'JDE_opt' => array(
								'jde_url' => 'http://apitest.jde.ru:8000/'																								
							)
						)
					);
		
		add_option( 'RSC_plugin_options', array('empty'=>'empty'));
	}
	
	
	
	function rsc_plugin_deactivation(){
		
	}
	
	
	function rsc_plugin_uninstall(){
		delete_option( 'RSC_plugin_options');
		if(get_option('users_can_register') == '1'){update_option( 'users_can_register', '0');}		
		remove_role('client');
	}
	
	function rsc_menu_page() {
		add_menu_page( __( "RSC Adjustments", "RSC_plugin" ), __( "RSC Adjustments", "RSC_plugin" ),0, "rscpage", "rsc_admin_page", RSC_BASE_URL.'img/railways_logo.png', $position );
	} 
	
	function rsc_session_destroy(){
		 if ( session_id() ) {						
			session_unset();
			session_destroy();
		}
	}
	
	function rsc_load_langpack(){
		global $l10n;
		
		load_plugin_textdomain('RSC_plugin',false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
		
		$domain = 'RSC_plugin';
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
	
	function rsc_user_login_hooks(){
		if ( !session_id() ) {session_start();}
		add_action('register_post', 'rsc_user_register_fail_redirect', 99, 3);
		add_action( 'wp_login_failed', 'rsc_user_login_failed', 10, 2 );
		add_filter( 'authenticate', 'rsc_user_authenticate_username_password', 30, 3);
		
	}
	
	add_action( 'wp_head', 'create_objects' );
	add_action( 'init', 'rsc_load_langpack' );
	add_action( 'init', 'rsc_user_login_hooks' );
	
	
	
	add_action( 'admin_menu', 'rsc_menu_page' );
	register_uninstall_hook(__FILE__, "rsc_plugin_uninstall");
	register_activation_hook(__FILE__, "rsc_plugin_activation");
	register_deactivation_hook(__FILE__, "rsc_plugin_deactivation");
	
?>