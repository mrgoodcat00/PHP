<?php

class GmailCheck{
	
	static private $system_option;
	static private $system_name;
	static private $google_library_path;
	
	static private $client_id;
	static private $client_secret;
	static private $redirect_uri;
	
	static private $error_message;
	static private $authUrl;
	
	static private $search_from;
	
	public function __construct() {
		
		session_start();	
		
		require_once(CHOP_BASE_PATH.'google/src/Google/autoload.php');
		
		self::$system_option = get_option("CHOP_plugin_options");
		self::$system_name = __("Fill the fields and enjoy the plugin","check_out_emails");
		add_shortcode("check_gmail_list",array( get_class($this), 'Check_Gmail_Shortcode' ));			
		
		self::$client_id = self::$system_option['CHOP_plugin_options']['check_mail_opt']['client_id'];
		self::$client_secret = self::$system_option['CHOP_plugin_options']['check_mail_opt']['client_secret_key'];
		self::$redirect_uri = self::$system_option['CHOP_plugin_options']['check_mail_opt']['redirect_url'];	
		self::$search_from = self::$system_option['CHOP_plugin_options']['check_mail_opt']['search_from'];	
	}
	
	
	public function Check_Gmail_Shortcode( $atts ) {
		 
		extract( shortcode_atts( array(), $atts ) );			
				
		$client = self::Check_Gmail_Connect();
		
		if(!self::Check_Gmail_Expired_Token($client->getAccessToken(),time())){
			$err = '<h2 style="color:red;">'.__("Seems your key expired, try reconnect.","check_out_emails").'</h2><br>';
		} else { 
			$result = self::Check_Gmail_Build_Result($client);
		}
		
		
		
		
	
		
		if (isset(self::$authUrl)) {
			$form = "<a class='login' href='" . self::$authUrl . "'>Connect!</a>";
		} else {
			$form = $err.'
			<form style="float: right;" id="url" method="GET" action="'.self::$redirect_uri.'">
			<input name="get_mails" class="url" value="true" type="hidden">
			<input type="submit" value="Get Mails"></form>
			<a style="float: left;" class="logout" href="?logout">Disconnect!</a> <span style="clear:both;"></span>';
		}
		
			
		
		$content = '
		<table>
			<tr> 
				<td colspan="3"> '.__("Active Projects:","check_out_emails").'</td>				
			</tr>
			<tr><td>Number Of Project</td><td>Project URL</td><td>Short Description</td></tr>
			'.$result.'
		</table>
		
		'.$form.'';
		
       return $content;
	}	
	
	

	

	 
	public function Check_Gmail_Connect(){
		
		/************************************************
		Make an API request on behalf of a user. In
		this case we need to have a valid OAuth 2.0
		token for the user, so we need to send them
		through a login flow. To do this we need some
		information from our API console project.
		************************************************/
		$client = new Google_Client();
		$client->setClientId(self::$client_id);
		$client->setClientSecret(self::$client_secret);
		$client->setRedirectUri(self::$redirect_uri);
		$client->addScope("https://www.googleapis.com/auth/gmail.readonly");

		/************************************************
		If we're logging out we just need to clear our
		local access token in this case
		************************************************/
		if (isset($_REQUEST['logout'])) {			
			unset($_SESSION['access_token']);
			echo '<script>window.location.href = "http://test.board-shop.com.ua/check-out-projects";</script>';
			
		}

		/************************************************
		If we have a code back from the OAuth 2.0 flow,
		we need to exchange that with the authenticate()
		function. We store the resultant access token
		bundle in the session, and redirect to ourself.
		************************************************/
		if (isset($_GET['code'])) {			
			$client->authenticate($_GET['code']);			
			$_SESSION['access_token'] = $client->getAccessToken();
			$redirect = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
			echo '<script>window.location.href = "http://test.board-shop.com.ua/check-out-projects";</script>';
			//header('Location: ' . filter_var($redirect, FILTER_SANITIZE_URL),true);
		}

		/************************************************
		If we have an access token, we can make
		requests, else we generate an authentication URL.
		************************************************/

		if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {			
			$client->setAccessToken($_SESSION['access_token']);		
		} else {
			self::$authUrl = $client->createAuthUrl();
		}
		return $client;
	}
	
	
	public function  Check_Gmail_Build_Result($client){
		if ($client->getAccessToken() && isset($_GET['get_mails']) && $_GET['get_mails'] == "true") {
			$cntr=0;
			$result = "";
			$service = new Google_Service_Gmail($client);

			$msg = self::Check_Gmail_listMessages($service,"me");
			
			
			foreach($msg as $message ){
				if(self::Check_Gmail_find_from($message,self::$search_from) == true){			
					$target_messages[$cntr] = $message;
					$cntr++;
				}
			}
			
			if(count($target_messages) > 0){
				
				foreach($target_messages as $number => $target_message){
					$matches = "";
					$site_url = "";
					$number = $number+1;	
					
					$parse_subject = self::Check_Gmail_find_subject($target_message,'Subject');
													
					preg_match('{\b(?:http://)?(www\.)?([^\s]+)(\.com|\.org|\.net)\b}mi', $parse_subject, $matches);
					
					if(isset($matches[0])){												
						$site_url = "<a href='http://".$matches[0]."'> ".$matches[0]." </a>";						 
						$subject = str_replace( $matches[0], "", $parse_subject );
					
					} else {
						$subject = self::Check_Gmail_find_subject($target_message,'Subject');
						$site_url = "-";
					}
					
					
					
					$result .= "
						<tr>
							<td>
								".$number."
							</td>
							<td>
								".$site_url."
							</td>
							<td>
								".$subject."
							</td>
						</tr>
					";					
					
				}

				$result .= '
					<tr>
						<td colspan="3">
							<H1 style="color:red;">
								Seems you got a job for today, so move your ass!
							</H1>
						</td>
					</tr>
				';
							
			} else {
				$result .= '
					<tr>
						<td colspan="3">
							<H1 style="color:green;">
								Take a rest, found 0 projects.
							</H1>
						</td>
					</tr>
				';				
			}

		}
		return $result;
	}
	 
	 
	function Check_Gmail_getMessage($service, $userId, $messageId) {
		try {
			$message = $service->users_messages->get($userId, $messageId,['format'=>'full']);
			return $message;
		} catch (Exception $e) {
			self::$error_message = $e->getMessage();		
			print '2An error occurred: ' . $e->getMessage();
		}
	}
	 
	function Check_Gmail_listMessages($service, $userId) {
		 
		$pageToken = NULL;
		$messages = array();
		$msg = array();
		$error = array();
		$opt_param = array(
			"maxResults" => 15, 
			"q"=>"in:unread category:primary ",
		);
		do {
		try {
		  if ($pageToken) {
			$opt_param['pageToken'] = $pageToken;
		  }
		  $messagesResponse = $service->users_messages->listUsersMessages($userId, $opt_param);
		  	
		  if ($messagesResponse->getMessages()) {
			$messages = array_merge($messages, $messagesResponse->getMessages());
			$pageToken = $messagesResponse->getNextPageToken();
		  }
		} catch (Exception $e) {
			self::$error_message = $e->getMessage();		
			//print '1An error occurred: ' . $e->getMessage();
		}
		} while ($pageToken);

		$count = 0;
		foreach ($messages as $message) {
			$message = self::Check_Gmail_getMessage($service, 'me', $message->getId());
			$msg[] = $message['modelData']['payload']['headers']; 
		}

		return $msg;
	}
	
	
	public function Check_Gmail_Expired_Token($token,$time){
		$decoded = json_decode($token);
		$the_time = $decoded->created + $decoded->expires_in;
		$res = $the_time - $time;	
		return ($res > 0 ? true : false);		
	}
	
	
	public function Check_Gmail_Form(){

		$paragraph = __("How to get following parameters, please read README.txt","check_out_emails");
		$admin_opt = self::$system_option['CHOP_plugin_options']['check_mail_opt'];
		
		$form = '<tr><td colspan="2" style="border-bottom: 1px solid #cecece;"><h3><span style="display: inline-block;   vertical-align: middle;">'.self::$system_name.'</span><img src="'.CHOP_BASE_URL.'img/gmail_logo.png" style="height: 60px;display: inline-block;vertical-align: middle; margin-left: 15px;"></h3></td></tr><tr><td>';
		
		$form .= '<p><label for="client_id">'.__("Client ID","check_out_emails").'</label></p>';
		$form .= '<p><input id="client_id" type="text" name="check_out_emails_options[check_mail_opt][client_id]" value="'.$admin_opt['client_id'].'"/></p>';
		
		$form .= '<p><label for="client_secret_key">'.__("Client Secret Key","check_out_emails").'</label></p>';
		$form .= '<p><input id="client_secret_key" type="text" name="check_out_emails_options[check_mail_opt][client_secret_key]" value="'.$admin_opt['client_secret_key'].'"/></p>';
		
		$form .= '<p><label for="redirect_url">'.__("Redirect Url After Request(must be same like in google App)","check_out_emails").'</label></p>';
		$form .= '<p><input id="redirect_url" type="text" name="check_out_emails_options[check_mail_opt][redirect_url]" value="'.$admin_opt['redirect_url'].'"/></p>';
		
		$form .= '<p><label for="search_from">'.__("Max message for check","check_out_emails").'</label></p>';
		$form .= '<p><input id="search_from" type="text" name="check_out_emails_options[check_mail_opt][search_from]" value="'.$admin_opt['search_from'].'"/></p>';
		
		$form .= '</td><td align="right">'.$paragraph.'</td></tr>';
		
		return $form;
	}
	
	
	/************************************************
	**This function find special letter from sender**
	*************************************************/
	public function Check_Gmail_find_from($array,$login){
		$i=0;
		do
			$array[$i]['name'] == "From" && $array[$i]['value'] === $login ? $result = true : false;
			
		while(++$i<count($array));
		
		return $result;
	}
	
	
	/**************************************
	**This function get specified Subject**
	**************************************/
	public function Check_Gmail_find_subject($array,$login){
		$i=0;
		do 
			$array[$i]['name'] == $login  ? $result = $array[$i]['value'] : '';
		while(++$i<count($array));
			return $result;
	}
	
	
	
	
}

?>