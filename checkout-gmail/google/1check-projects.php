<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>

<head>
  <title>simple_light</title>
  <meta name="description" content="website description" />
  <meta name="keywords" content="website keywords, website keywords" />
  <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
  <link rel="stylesheet" type="text/css" href="/styles/style.css" />
</head>

<body>


<?php

//include_once "templates/base.php";
session_start();

require_once realpath(dirname(__FILE__) . '/src/Google/autoload.php');

/************************************************
  ATTENTION: Fill in these values! Make sure
  the redirect URI is to this page, e.g:
  http://localhost:8080/user-example.php
 ************************************************/
 $client_id = '878061410234-vinno7kpn190p0v0itf0fhfonnot0qvb.apps.googleusercontent.com';
 $client_secret = 'y1CtcPyd-XBpn-icpbWqd2Co';
 $redirect_uri = 'http://test.board-shop.com.ua/google/check-projects.php';

/************************************************
  Make an API request on behalf of a user. In
  this case we need to have a valid OAuth 2.0
  token for the user, so we need to send them
  through a login flow. To do this we need some
  information from our API console project.
 ************************************************/
$client = new Google_Client();
$client->setClientId($client_id);
$client->setClientSecret($client_secret);
$client->setRedirectUri($redirect_uri);
$client->addScope("https://www.googleapis.com/auth/gmail.readonly");

/************************************************
  When we create the service here, we pass the
  client to it. The client then queries the service
  for the required scopes, and uses that when
  generating the authentication URL later.
 ************************************************/
 


/************************************************
  If we're logging out we just need to clear our
  local access token in this case
 ************************************************/
if (isset($_REQUEST['logout'])) {
  unset($_SESSION['access_token']);
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
  header('Location: ' . filter_var($redirect, FILTER_SANITIZE_URL));
}

/************************************************
  If we have an access token, we can make
  requests, else we generate an authentication URL.
 ************************************************/
if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
  $client->setAccessToken($_SESSION['access_token']);
} else {
  $authUrl = $client->createAuthUrl();
}



if ($client->getAccessToken() && isset($_GET['get_mails']) && $_GET['get_mails'] == "true") {
 
 $service = new Google_Service_Gmail($client);
 
 $msg = listMessages($service,"me");
 //var_dump($service);
 
 
 
 /*
 foreach($list->getMessages() as $item){
	$message = $service->users_messages->get("me",$item->id);
	
	var_dump($message->getPayload());
	
	
	
	$message_current = $message->getPayload()->current();	
	var_dump($message->current());
	if(!empty($message_current['modelData']["body"]["data"])){
		$mes_body = $message_current['modelData']["body"]["data"];
	} else {$mes_body = $message_current['modelData']['parts'][0]['body']['data'];}
	$encoded_message = mb_convert_encoding($mes_body,"UTF-8",mb_detect_encoding($encoded_message));
	$encoded_message = base64_decode($encoded_message);	
	
	var_dump($encoded_message);
	
	
 }
 
 */
 //var_dump(get_class_methods('Google_Service_Gmail_ListMessagesResponse') );
 //var_dump(get_class_methods('Google_Service_Gmail_UsersMessages_Resource') );

}





function getMessage($service, $userId, $messageId) {
  try {
    $message = $service->users_messages->get($userId, $messageId,['format'=>'full']);
    return $message;
  } catch (Exception $e) {
    print 'An error occurred: ' . $e->getMessage();
  }
}





function listMessages($service, $userId) {
  $pageToken = NULL;
  $messages = array();
  $msg = array();
  $error = array();
  $opt_param = array("maxResults" => 4, "q"=>"in:unread category:primary ");
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
		$error[] = $e->getMessage();		
		print 'An error occurred: ' . $e->getMessage();
    }
  } while ($pageToken);

  $count = 0;
  foreach ($messages as $message) {
	$message = getMessage($service, 'me', $message->getId());
	$msg[] = $message['modelData']['payload']['headers']; 
  }

  return $msg;
}























/*
echo pageHeader("get the mails list");
if (strpos($client_id, "googleusercontent") == false) {
  echo missingClientSecretsWarning();
  exit;
}*/
/*?>
<div class="box">
  <div class="request">
<?php 
if (isset($authUrl)) {
  echo "<a class='login' href='" . $authUrl . "'>Connect Me!</a>";
} else {
  echo <<<END
    <form id="url" method="GET" action="{$_SERVER['PHP_SELF']}">
      <input name="get_mails" class="url" value="true" type="hidden">
      <input type="submit" value="Get Mails">
    </form>
    <a class='logout' href='?logout'>Logout</a>
END;
}
?>
  </div>

  <div class="shortened">
<?php
if (isset($short)) {
  var_dump($short);
}
?>
  </div>
</div>



<?php ?>





























  <div id="main">
    <div id="header">
      <div id="logo">
        <!-- class="logo_colour", allows you to change the colour of the text -->
        <h1><a href="index.html">simple<span class="logo_colour">_light</span></a></h1>
        <h2>Simple. Contemporary. Website Template.</h2>
      </div>
      <div id="menubar">
        <ul id="menu">
          <!-- put class="selected" in the li tag for the selected page - to highlight which page you're on -->
          <li class="selected"><a href="index.html">Home</a></li>
          <li><a href="examples.html">Examples</a></li>
          <li><a href="page.html">A Page</a></li>
          <li><a href="another_page.html">Another Page</a></li>
          <li><a href="contact.html">Contact Us</a></li>
        </ul>
      </div>
    </div>
    <div id="site_content">
      <div class="sidebar">
        <h1>Latest News</h1>
        <h4>New Website Launched</h4>
        <h5>January 1st, 2010</h5>
        <p>2010 sees the redesign of our website. Take a look around and let us know what you think.<br /><a href="#">Read more</a></p>
        <h1>Useful Links</h1>
        <ul>
          <li><a href="#">link 1</a></li>
          <li><a href="#">link 2</a></li>
          <li><a href="#">link 3</a></li>
          <li><a href="#">link 4</a></li>
        </ul>
        <h1>Search</h1>
        <form method="post" action="#" id="search_form">
          <p>
            <input class="search" type="text" name="search_field" value="Enter keywords....." />
            <input name="search" type="image" style="border: 0; margin: 0 0 -9px 5px;" src="style/search.png" alt="Search" title="Search" />
          </p>
        </form>
      </div>
      <div id="content">
        <h1>Welcome to the simple_light template</h1>
        <p>This standards compliant, simple, fixed width website template is released as an 'open source' design (under a <a href="http://creativecommons.org/licenses/by/3.0">Creative Commons Attribution 3.0 Licence</a>), which means that you are free to download and use it for anything you want (including modifying and amending it). All I ask is that you leave the 'design from HTML5webtemplates.co.uk' link in the footer of the template, but other than that...</p>
        <p>This template is written entirely in <strong>HTML5</strong> and <strong>CSS</strong>, and can be validated using the links in the footer.</p>
        <p>You can view more free HTML5 web templates <a href="http://www.html5webtemplates.co.uk">here</a>.</p>
        <p>This template is a fully functional 5 page website, with an <a href="examples.html">examples</a> page that gives examples of all the styles available with this design.</p>
        <h2>Browser Compatibility</h2>
        <p>This template has been tested in the following browsers:</p>
        <ul>
          <li>Internet Explorer 8</li>
          <li>FireFox 3</li>
          <li>Google Chrome 13</li>
        </ul>
      </div>
    </div>
    <div id="footer">
      <p><a href="index.html">Home</a> | <a href="examples.html">Examples</a> | <a href="page.html">A Page</a> | <a href="another_page.html">Another Page</a> | <a href="contact.html">Contact Us</a></p>
      <p>Copyright &copy; simple_light | <a href="http://validator.w3.org/check?uri=referer">HTML5</a> | <a href="http://jigsaw.w3.org/css-validator/check/referer">CSS</a> | <a href="http://www.html5webtemplates.co.uk">design from HTML5webtemplates.co.uk</a></p>
    </div>
  </div>
</body>
</html>







<?php
//echo pageFooter(__FILE__);
*/?>