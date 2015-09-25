<?php

class JDE_Api{
	
	static private $system_name;	
	static private $system_option;
	
	
	public function __construct() {
		self::$system_name = __("Russian Railway Expedition","RSC_plugin");
		self::$system_option = get_option("RSC_plugin_options");
		add_shortcode("check_jde",array( get_class($this), 'JDE_Get_Location_Shortcode' ));
	}
	
	
	public function JDE_Get_Location_Shortcode( $atts ) {
		extract( shortcode_atts( array(
            'key' => '',
            'feed' => 'user',
		), $atts ) );
		
		$content = '<div class="checkout-form"> <h3>'.__("Track my order by JDE","RSC_plugin").'</h3>';
		$content .= '<form action="'.'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'" method="POST">';
			$content .=	'<p><label for="invoice_number"> '.__("Input invoice number","RSC_plugin").'</label></p>';
			$content .= '<p><input id="invoice_number" type="text" name="invoice_number" placeholder="'.__("Invoice Number","RSC_plugin").'"/></p>';
			$content .=	'<p><label for="invoice_pass"> '.__("Input invoice pass","RSC_plugin").'</label></p>';
			$content .= '<p><input id="invoice_pass" type="text" name="invoice_pass"/></p>';		
			$content .= '<p><input type="hidden" value="true" name="invoice_data_sended"/></p>';
			$content .= '<p><input type="submit" value="'.__("Submit","RSC_plugin").'"/></p>';
		$content .= '</form></div>';
		
		if(isset($_POST['invoice_data_sended'])){
			$content .= self::JDE_Send_Data($_POST);
		}
		
       return $content;
	}	
	
	
	public function JDE_Admin_Form(){
		$paragraph = __("For test, use invoice id=0000000000000457 and activate Test Mode","RSC_plugin");
		$paragraph .= '<br>'.__("Shortcode for track orders [check_jde]","RSC_plugin");
		$jde_opt = self::$system_option['RSC_plugin_options'];
		$form = '<tr><td colspan="2" style="border-bottom: 1px solid #cecece;"><h3><span style="display: inline-block;   vertical-align: middle;">'.__("Settings for ","RSC_plugin").self::$system_name.'</span><img src="'.RSC_BASE_URL.'img/jde_logo.gif" style="height: 60px;display: inline-block;vertical-align: middle; margin-left: 15px;"></h3></td></tr><tr><td>';
		$form .= '<p><label for="jde_url">'.__("JDE Server Url","RSC_plugin").'</label></p>';
		$form .= '<p><input id="jde_url" type="text" name="RSC_plugin_options[JDE_opt][jde_url]" value="'.$jde_opt['JDE_opt']['jde_url'].'"/></p>';
		$form .= '<p><label for="jde_uid">'.__("JDE User Id","RSC_plugin").'</label></p>';
		$form .= '<p><input id="jde_uid" type="text" name="RSC_plugin_options[JDE_opt][jde_uid]" value="'.$jde_opt['JDE_opt']['jde_uid'].'"/></p>';
		$form .= '<p><label for="jde_token">'.__("JDE Token","RSC_plugin").'</label></p>';
		$form .= '<p><input id="jde_token" type="text" name="RSC_plugin_options[JDE_opt][jde_token]" value="'.$jde_opt['JDE_opt']['jde_token'].'"/></p>';
		if($jde_opt["JDE_opt"]["jde_test"] == "on"){$jde_test ='checked="checked"';}
		$form .= '<p><label for="jde_test">'.__("Activate Test Mode ","RSC_plugin").'</label><input id="jde_test" type="checkbox" name="RSC_plugin_options[JDE_opt][jde_test]" '.$jde_test.'/></p>';

		
			$form .= '<p>'.__("Select blocks for showing: ","RSC_plugin");
			$form .= '<table style="width: 250px;">';
				if($jde_opt["JDE_opt"]["jde_show_path"] == "on"){$jde_show_path ='checked="checked"';}
				$form .= '<tr> <td><label for="jde_show_path">'.__("Show Cargo Path ","RSC_plugin").'</label></td><td><input id="jde_show_path" type="checkbox" name="RSC_plugin_options[JDE_opt][jde_show_path]" '.$jde_show_path.'/></td></tr> ';
				if($jde_opt["JDE_opt"]["jde_sender"] == "on"){$jde_sender ='checked="checked"';}
				$form .= '<tr> <td> <label for="jde_sender">'.__("Sender Info ","RSC_plugin").'</label></td><td><input id="jde_sender" type="checkbox" name="RSC_plugin_options[JDE_opt][jde_sender]" '.$jde_sender.'/></td></tr>  ';
				if($jde_opt["JDE_opt"]["jde_receiver"] == "on"){$jde_receiver ='checked="checked"';}
				$form .= '<tr> <td> <label for="jde_receiver">'.__("Receiver Info ","RSC_plugin").'</label></td><td><input id="jde_receiver" type="checkbox" name="RSC_plugin_options[JDE_opt][jde_receiver]" '.$jde_receiver.'/> </td></tr> ';
				if($jde_opt["JDE_opt"]["jde_cargo_info"] == "on"){$jde_cargo_info ='checked="checked"';}
				$form .= '<tr> <td> <label for="jde_cargo_info">'.__("Cargo info ","RSC_plugin").'</label></td><td><input id="jde_cargo_info" type="checkbox" name="RSC_plugin_options[JDE_opt][jde_cargo_info]" '.$jde_cargo_info.'/> </td></tr> ';
				 
			$form .= '</table></p>';
		$form .= '</td><td align="right">'.$paragraph.'</td></tr>';
		
		return $form;
	}
	
	
	public function JDE_Send_Data($post_data){
		
		$opt = self::$system_option['RSC_plugin_options'];
		$url = $opt['JDE_opt']['jde_url'];
		$token = $opt['JDE_opt']['jde_token'];
		$uid = $opt['JDE_opt']['jde_uid'];
		
		if($opt['JDE_opt']['jde_test'] != "on"){
			if(isset($post_data['invoice_pass'])){
				$parameters = "cargos/status/?user=".$uid."&token=".$token."&ttn=".$post_data['invoice_number']."&pin=".$post_data['invoice_pass']."";
			} else {
				$parameters = "cargos/status/?user=".$uid."&token=".$token."&ttn=".$post_data['invoice_number'];
			}
		} else {
			$parameters = "cargos/status/?user=".$uid."&token=".$token."&ttn=0000000000000457&test=1";
		}
		
		
		$ch = curl_init();
		if(strtolower((substr($url,0,5))=='https')) { 
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		}
		curl_setopt($ch, CURLOPT_URL, $url.$parameters);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
		curl_setopt($ch, CURLOPT_POST, 0);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (Windows; U; Windows NT 5.0; En; rv:1.8.0.2) Gecko/20070306 Firefox/1.0.0.4");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_COOKIEJAR, RSC_BASE_PATH.'/cookie.txt');
		$result = curl_exec($ch);

		if($result == "false" || $result == false){
			$result = __("Error: ","RSC_plugin").curl_error($ch)."</br>".__(" Check host settings, seems that closed port 8000","RSC_plugin");
		} else {
			$status_preload = (array)json_decode($result);
			if(isset($status_preload['errors'])) {
				$result = __("Error: ","RSC_plugin").$status_preload['errors'];
			} else {$result = self::JDE_build_request_results((array)json_decode($result));		}			
		}
		
		curl_close($ch);
		
		return '<div class="jde_results">'.$result.'</div>';		
	}
	
	
	
	public  function JDE_build_request_results($content) {
		$result = '';
		$opt = self::$system_option['RSC_plugin_options'];
		
		if($opt['JDE_opt']['jde_sender'] == "on"){
			$result .= '<h3>' .__("Sender info:","RSC_plugin"). '</h3>';
			$result .= '<ul class="jde_sender_info_list" style="float:none;">';			
				if(isset($content["sender"]->branch) && $content["sender"]->branch != "") {
					$result .= '<li> <span class="jde_sender_info_item">'.__("Branch Number: ","RSC_plugin").'</span>' .$content["sender"]->branch. '</li>';
				}
				if(isset($content["sender"]->branch_title) && $content["sender"]->branch_title != "") {
					$result .= '<li> <span class="jde_sender_info_item">'.__("Branch Title: ","RSC_plugin").'</span>' .$content["sender"]->branch_title. '</li>';
				}
				if(isset($content["sender"]->sender) && $content["sender"]->sender != "") {
					$result .= '<li> <span class="jde_sender_info_item">'.__("Sender: ","RSC_plugin").'</span>' .$content["sender"]->sender. '</li>';
				}
				if(isset($content["sender"]->person) && $content["sender"]->person != "") {
					$result .= '<li> <span class="jde_sender_info_item">'.__("Bailee: ","RSC_plugin").'</span>' .$content["sender"]->person. '</li>';
				}
				if(isset($content["sender"]->phone) && $content["sender"]->phone != "") {
					$result .= '<li> <span class="jde_sender_info_item">'.__("Phone: ","RSC_plugin").'</span>' .$content["sender"]->phone. '</li>';
				}
				if(isset($content["sender"]->email) && $content["sender"]->email != "") {
					$result .= '<li> <span class="jde_sender_info_item">'.__("Email: ","RSC_plugin").'</span>' .$content["sender"]->email. '</li>';
				}
			$result .= '</ul>';
		}
		
		if($opt['JDE_opt']['jde_receiver'] == "on"){
			$result .= '<h3>' .__("Receiver info:","RSC_plugin"). '</h3>';
			$result .= '<ul class="jde_receiver_info_list" style="float:none;">';			
				if(isset($content["receiver"]->branch) && $content["receiver"]->branch != "") {
					$result .= '<li> <span class="jde_receiver_info_item">'.__("Branch Number: ","RSC_plugin").'</span>' .$content["receiver"]->branch. '</li>';
				}
				if(isset($content["receiver"]->branch_title) && $content["receiver"]->branch_title != "") {
					$result .= '<li> <span class="jde_receiver_info_item">'.__("Branch Title: ","RSC_plugin").'</span>' .$content["receiver"]->branch_title. '</li>';
				}
				if(isset($content["receiver"]->receiver) && $content["receiver"]->receiver != "") {
					$result .= '<li> <span class="jde_receiver_info_item">'.__("Receiver: ","RSC_plugin").'</span>' .$content["receiver"]->receiver. '</li>';
				}
				if(isset($content["receiver"]->person) && $content["receiver"]->person != "") {
					$result .= '<li> <span class="jde_receiver_info_item">'.__("Bailee: ","RSC_plugin").'</span>' .$content["receiver"]->person. '</li>';
				}
				if(isset($content["receiver"]->phone) && $content["receiver"]->phone != "") {
					$result .= '<li> <span class="jde_receiver_info_item">'.__("Phone: ","RSC_plugin").'</span>' .$content["receiver"]->phone. '</li>';
				}
				if(isset($content["receiver"]->email) && $content["receiver"]->email != "") {
					$result .= '<li> <span class="jde_receiver_info_item">'.__("Email: ","RSC_plugin").'</span>' .$content["receiver"]->email. '</li>';
				}
			$result .= '</ul>';
		}
		
		if($opt['JDE_opt']['jde_cargo_info'] == "on"){
			$result .= '<h3>' .__("Cargo info:","RSC_plugin"). '</h3>';
			$result .= '<ul class="jde_cargo_info_list" style="float:none;">';
			
				if(isset($content["cargo"]->code) && $content["cargo"]->code != "") {
					$result .= '<li> <span class="jde_cargo_info_item">'.__("Code: ","RSC_plugin").'</span>' .$content["cargo"]->code. '</li>';
				}
				if(isset($content["cargo"]->amount) && $content["cargo"]->amount != "") {
					$result .= '<li> <span class="jde_cargo_info_item">'.__("Amount of Places: ","RSC_plugin").'</span>' .$content["cargo"]->amount. '</li>';
				}
				if(isset($content["cargo"]->weight) && $content["cargo"]->weight != "") {
					$result .= '<li> <span class="jde_cargo_info_item">'.__("Weight: ","RSC_plugin").'</span>' .$content["cargo"]->weight. '</li>';
				}
				if(isset($content["cargo"]->volume) && $content["cargo"]->volume != "") {
					$result .= '<li> <span class="jde_cargo_info_item">'.__("Volume: ","RSC_plugin").'</span>' .$content["cargo"]->volume. '</li>';
				}
				if(isset($content["cargo"]->description) && $content["cargo"]->description != "") {
					$result .= '<li> <span class="jde_cargo_info_item">'.__("Description: ","RSC_plugin").'</span>' .$content["cargo"]->description. '</li>';
				}
				if(isset($content["cargo"]->orderNumber) && $content["cargo"]->orderNumber != "") {
					$result .= '<li> <span class="jde_cargo_info_item">'.__("Order Number: ","RSC_plugin").'</span>' .$content["cargo"]->orderNumber. '</li>';
				}	
				
				if(isset($content["info"]->cargoStatus) && $content["info"]->cargoStatus != "") {
					$result .= '<li> <span class="jde_cargo_info_item">'.__("Status: ","RSC_plugin").'</span>' .$content["info"]->cargoStatus. '</li>';
				}
				if(isset($content["info"]->takeOnStockDateTime) && $content["info"]->takeOnStockDateTime != "") {
					$result .= '<li> <span class="jde_cargo_info_item">'.__("Take On Stock: ","RSC_plugin").'</span>' .$content["info"]->takeOnStockDateTime. '</li>';
				}
				if(isset($content["info"]->sendingDateTime) && $content["info"]->sendingDateTime != "") {
					$result .= '<li> <span class="jde_cargo_info_item">'.__("Sending Date Time: ","RSC_plugin").'</span>' .$content["info"]->sendingDateTime. '</li>';
				}
				if(isset($content["info"]->arrivalDateTime) && $content["info"]->arrivalDateTime != "") {
					$result .= '<li> <span class="jde_cargo_info_item">'.__("Arrival Date Time: ","RSC_plugin").'</span>' .$content["info"]->arrivalDateTime. '</li>';
				}
				if(isset($content["info"]->giveOutDateTime) && $content["info"]->giveOutDateTime != "") {
					$result .= '<li> <span class="jde_cargo_info_item">'.__("Give Out Date Time: ","RSC_plugin").'</span>' .$content["info"]->giveOutDateTime. '</li>';
				}
				if(isset($content["info"]->arrivalPlanDateTime) && $content["info"]->arrivalPlanDateTime != "") {
					$result .= '<li> <span class="jde_cargo_info_item">'.__("Arrival Plan Date Time: ","RSC_plugin").'</span>' .$content["info"]->arrivalPlanDateTime. '</li>';
				}
								
				if(isset($content["services"]->sum) && $content["services"]->sum != "") {
					$result .= '<li> <span class="jde_cargo_info_item">'.__("Sum: ","RSC_plugin").'</span>' .$content["services"]->sum. '</li>';
				}				
				if(isset($content["services"]->discount) && $content["services"]->discount != "") {
					$result .= '<li> <span class="jde_cargo_info_item">'.__("Discount: ","RSC_plugin").'</span>' .$content["services"]->discount. '</li>';
				}				
				if(count($content["services"]->items) > 0 ) {
					$result .= '<ul>';										
					if(isset($content["services"]->items[0]->branch) && $content["services"]->items[0]->branch != "") {
						$result .= '<li> <span class="jde_cargo_info_editional_item">'.__("Branch Number: ","RSC_plugin").'</span>' .$content["services"]->items[0]->branch. '</li>';
					}
					if(isset($content["services"]->items[0]->description) && $content["services"]->items[0]->description != "") {
						$result .= '<li> <span class="jde_cargo_info_editional_item">'.__("Description: ","RSC_plugin").'</span>' .$content["services"]->items[0]->description. '</li>';
					}
					if(isset($content["services"]->items[0]->price) && $content["services"]->items[0]->price != "") {
						$result .= '<li> <span class="jde_cargo_info_editional_item">'.__("Price: ","RSC_plugin").'</span>' .$content["services"]->items[0]->price. '</li>';
					}					
					$result .= '</ul>';
				}								
			$result .= '</ul>';
		}
		
		if($opt['JDE_opt']['jde_show_path'] == "on"){
			$result .= '<h3>' .__("Show Cargo Path: ","RSC_plugin"). '</h3>';
			$result .= '<ul style="float:none;">';
				if(count($content["states"])>0){
					foreach($content["states"] as $states){
						$result .= '<li>'; 
							$result .= '<ul class="jde_cargo_state_list" style="float:none;padding-left:15px;">';
								$status = self::JDE_translate_cargo_status($states->status);
								$result .= '<li> <span class="jde_cargo_state_item">'.__("Status: ","RSC_plugin").'</span>'.$status.'</li>'; 							
								if(isset($states->date)){$result .= '<li> <span class="jde_cargo_state_item">'.__("Date: ","RSC_plugin").'</span>'.$states->date.'</li>';}
								if($states->city != "") {$result .= '<li> <span class="jde_cargo_state_item">'.__("City: ","RSC_plugin").'</span>'.$states->city.'</li>';}
							$result .= '</ul>';						
						$result .= '</li>';
					}
				} else { $result .= __("Cargo way is empty","RSC_plugin");}			
			$result .= '</ul>';
		}
		
		return $result;
	
	}
	
	public  function JDE_translate_cargo_status($status) {
		if(get_locale() == "ru_RU"){
			switch (str_replace(" ","",$status)) {
				case "NewOrderByClient":
					$return = "Оформлен новый заказ по инициативе клиента";
					break;
				case "NotDone":
					$return = "Заказ отменен";
					break;
				case "OnTerminal":
					$return = "Посылка находится на терминале";
					break;
				case "OnTerminalPickup":
					$return = "Посылка находится на терминале приема отправления";
					break;
				case "OnRoad":
					$return = "Посылка находится в пути";
					break;
				case "OnTerminalDelivery":
					$return = "Посылка находится на терминале доставки";
					break;
				case "Delivering":
					$return = "Посылка выведена на доставку";
					break;
				case "Delivered":
					$return = "Посылка доставлена получателю";
					break;
				case "Lost":
					$return = "Посылка утеряна";
					break;
				case "Problem":
					$return = "С посылкой возникла проблемная ситуация";
					break;
				case "ReturnedFromDelivery":
					$return = "Посылка возвращена с доставки";
					break;
			}
		}
		return $return;
	}
}

?>