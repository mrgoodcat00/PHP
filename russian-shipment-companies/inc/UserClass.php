<?php

class RSC_User{
	
	static private $system_name;	
	static private $system_option;
	static public  $user_logged;
	
	
	
	public function __construct() {
		self::$system_name = __("User Capabilities","RSC_plugin");
		self::$system_option = get_option("RSC_plugin_options");	
		add_shortcode("rsc_loginform",array( get_class($this), 'User_Register_Login_Logout' ));	
		if(!is_admin()){
			wp_enqueue_style('user_forms_style',RSC_BASE_URL.'css/forms.css', false, '1.0');
			wp_enqueue_script('front_tabs_form', RSC_BASE_URL.'js/front-tabs.js', false, '1.0' );		
		}		 
	}
	
	
	
	public function User_Register_Login_Logout() {
	
	// actions ----------- action=loggedout --------- 
	$came_from_register = ( isset($_SESSION['redirect_failed_errors']) ? true : false);
	$came_from_login    = ( isset($_SESSION['login_failed_errors'])    ? true : false);
	$logged_out_flag    = ( rsc_check_user() ? true : false);
	$login_active 		= '';
	$register_active	= '';
	$message 			= "";
	$login_form 		= "";
	$error_mass 		= array();	
	$current_user 		= (rsc_check_user() ? wp_get_current_user() : '');
	$current_user		= ( !empty($current_user) ? '<h3>'.__("Welcome ","RSC_plugin").$current_user->data->user_nicename.'!</h3>' : '' );
	
	
 //$user = new WP_User( get_current_user_id() );
	//var_dump();


	if(!empty($_SESSION['redirect_failed_errors'])){
		foreach($_SESSION['redirect_failed_errors'] as $errors => $data){		
			if($data == "username_exists"){$message_reg .= __("This user already exist","RSC_plugin").'<br>';}
			if($data == "email_exists"){$message_reg .= __("This Email already exist","RSC_plugin").'<br>';}
			if($data == "invalid_email"){$message_reg .= __("Invalid Email","RSC_plugin").'<br>';}	
			if($data == "register_true"){$message_reg .= __("Register almost done, check you mail.","RSC_plugin").'<br>';}		
			$error_mass[] = $errors;					
		}
		unset($_SESSION['redirect_failed_errors']);
	}
	if(!empty($_SESSION['login_failed_errors'])){
		foreach($_SESSION['login_failed_errors'] as $errorz => $dt){			
			if($dt == "login_loggedout"){$message_login .= __("You logged out","RSC_plugin").'<br>'; rsc_session_destroy();}				
			if($dt == "login_failed"){$message_login .= __("Wrong login or password","RSC_plugin").'<br>';}				
			$error_mass[] = $errorz;		
		}
		unset($_SESSION['login_failed_errors']);
	}
	
	 
	
	if(!rsc_check_user())$reg_form = '<div class="rsc_user_login_form">' . self::User_Build_Reistration_Form($message_reg) . '</div>';
	if(!rsc_check_user())$log_form = '<div class="rsc_user_login_form">' . self::User_Build_Login_Form($message_login) . '</div>';
	if(rsc_check_user()) $log_out_content = '
		<div class="rsc_user_login_form"> 
			'.$current_user.'
			<h3>Click <a href="/wp-admin/profile.php">here</a> for visit your profile </h3><br>
			<h3>Click <a href="'.wp_logout_url().'">here</a> for log out</h3>
		</div>';
 
	?>  <script>
			jQuery(document).ready(function($){
				$(".tabs").lightTabs();
			});
		</script> <?php	
		
		if($came_from_login){$login_active = 'class="active"';}
		if($came_from_register){$register_active = 'class="active"';}
		if($logged_out_flag){$logged_out = 'class="active"';}
		
		$login_form	.= '
		<div class="tabs">
			<ul style="margin-bottom: 25px;">';
			if(!rsc_check_user()) 	$login_form	.= '<li '.$login_active.'>'.__("Login","RSC_plugin").'</li>';
			if(!rsc_check_user()) 	$login_form	.= '<li '.$register_active.'>'.__("Register","RSC_plugin").'</li>';
			if(rsc_check_user()) 	$login_form	.= '<li '.$logged_out.'>'.__("Logged Out","RSC_plugin").'</li>';
				 
		$login_form	.= '</ul>
			<div>
				'.$log_form.'
				'.$reg_form.'
				'.$log_out_content.'
			</div>            
		</div> ';
						
		return $login_form;
	}

	
	
	public function User_Build_Login_Form($message){
						
		$args = array(
			'redirect' => ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], 
			'form_id' => 'rsc-loginform',
			'label_username' => __( "User Name","RSC_plugin" ),
			'label_password' => __( "Password","RSC_plugin" ),
			'label_remember' => __( "Remember Me","RSC_plugin" ),
			'label_log_in'   => __( "LogIn","RSC_plugin" ),
			'remember' 		 => true,
			'id_username' => 'user_login',
			'id_password' => 'user_pass',
			'id_remember' => 'rememberme',
			'id_submit' => 'wp-submit',
			'value_username' => '',
			'value_remember' => false, // Set this to true to default the "Remember me" checkbox to checked
		);
		
		$login_form .= '	
			<form name="' . $args['form_id'] . '" id="' . $args['form_id'] . '" action="' . esc_url( site_url( 'wp-login.php', 'login_post' ) ) . '" method="post">
			<table class="rsc_user_form_table">
				<tr> 
					<td><label for="' . esc_attr( $args['id_username'] ) . '">' . esc_html( $args['label_username'] ) . '</label> </td>
					<td style="text-align: right;"><input type="text" name="log" id="' . esc_attr( $args['id_username'] ) . '" class="input" value="' . esc_attr( $args['value_username'] ) . '" size="20" /></td>
				</tr>
				
				<tr>
					<td><label for="' . esc_attr( $args['id_password'] ) . '">' . esc_html( $args['label_password'] ) . '</label></td>
					<td style="text-align: right;"><input type="password" name="pwd" id="' . esc_attr( $args['id_password'] ) . '" class="input" value="" size="20" /></td>
				</tr>
				<tr> <td>
				' . ( $args['remember'] ? '<p class="login-remember"><label><input name="rememberme" type="checkbox" id="' . esc_attr( $args['id_remember'] ) . '" value="forever"' . ( $args['value_remember'] ? ' checked="checked"' : '' ) . ' /> ' . esc_html( $args['label_remember'] ) . '</label></p>' : '' ) . '</td>
				<td style="text-align: right;"> <h3 style="color:red;">'.$message.' </h3></td>
				</tr>
				
				<tr> 
					<td><input type="submit" name="wp-submit" id="' . esc_attr( $args['id_submit'] ) . '" class="button-primary" value="' . esc_attr( $args['label_log_in'] ) . '" /></td>
					<td><input type="hidden" name="redirect_to" value="' . esc_url( $args['redirect'] ) . '" /></td>
				</tr>
			</table>
			</form> ';
		return $login_form;
	}
	
	
	
	public function User_Build_Reistration_Form($message){
		
		
		$form = "";
		
		if(empty($message)){$message = __("A password will be e-mailed to you.","RSC_plugin");} else{ $message = '<h3 style="color:red;">'.$message.'</h3>';}
		
		if ( $attributes['show_title'] ) :  
			$title = '<h3> __( "Register", "RSC_plugin" ); </h3>';
		endif;
		$form .= '
			<form id="signupform" action="'.site_url('wp-login.php?action=register', 'login_post').'" method="post">		
				<table class="rsc_user_form_table">
					<tr> 
						<td><label for="user_login"> '.__("User Name","RSC_plugin").' </label></td>
						<td style="text-align: right;"><input type="text" name="user_login" value="Username" id="user_login" class="input" /></td>
					</tr>
					<tr> 
						<td><label for="user_email">'.__("E-Mail","RSC_plugin").'</label></td>
						<td style="text-align: right;"><input type="text" name="user_email" value="E-Mail" id="user_email" class="input"  /></td>
					</tr>
					'.do_action("register_form").'
					<tr> 
						<td><input type="submit" value="Register" id="register" /><input type="hidden" name="redirect_to" value="' .'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . '" /></td>
						<td style="text-align: right;"><p class="statement">'.$message.'</p></td>
					</tr>								
				</table>						
			</form>';
		return $form;
	}
	
}

?>