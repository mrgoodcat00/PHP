<?php

class DL_Api{
	
	static private $system_name;	
	static private $system_option;
	static private $stylecss_url;
	
	public function __construct() {
		self::$system_name = __("Business Lines","RSC_plugin");
		self::$system_option = get_option("RSC_plugin_options");
		self::$stylecss_url = RSC_BASE_PATH."/css/dl-frontend-css.css";		
		add_shortcode("check_dl",array( get_class($this), 'DL_Get_Location_Shortcode' ));
		
	}
	
	
	public function DL_Get_Location_Shortcode( $atts ) {
		extract( shortcode_atts( array(
            'key' => '',
            'feed' => 'user',
		), $atts ) );
		
		
		$content = '<div class="checkout-form"> <h3>'.__("Track my order by DL","RSC_plugin").'</h3>';
		$content .= '<form action="'.'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'" method="POST">';
			$content .=	'<p><label for="invoice_number_dl"> '.__("Input invoice number","RSC_plugin").'</label></p>';
			$content .= '<p><input id="invoice_number_dl" type="text" name="invoice_number_dl" placeholder="'.__("Invoice Number","RSC_plugin").'"/></p>';	
			$content .= '<p><input type="hidden" value="true" name="invoice_data_sended_dl"/></p>';
			$content .= '<p><input type="submit" value="'.__("Submit","RSC_plugin").'"/></p>';
		$content .= '</form></div>';
		
		if(isset($_POST['invoice_data_sended_dl'])){
			$content .= self::DL_Get_Results(self::DL_Send_Data($_POST));			 
		}
		
       return $content;
	}	
	
	
	public function DL_Admin_Form(){
		$paragraph = __("For test, use invoice id=14-00213136738","RSC_plugin");
		$paragraph .= '<br>'.__("Shortcode track orders [check_dl]","RSC_plugin");
		$dl_id = self::$system_option['RSC_plugin_options'];
		$form = '<tr><td colspan="2" style="border-bottom: 1px solid #cecece;"><h3><span style="display: inline-block;   vertical-align: middle;">'.__("Settings for ","RSC_plugin").self::$system_name.'</span><img src="'.RSC_BASE_URL.'img/dl_logo.png" style="height: 60px;display: inline-block;vertical-align: middle; margin-left: 15px;"></h3></td></tr><tr><td>';
		$form .= '<p><label for="dl_id">'.__("DL App ID","RSC_plugin").'</label></p>';
		$form .= '<p><input id="dl_id" type="text" name="RSC_plugin_options[DL_opt][dl_id]" value="'.$dl_id['DL_opt']['dl_id'].'"/></p>';
		$form .= '<p><label for="dl_r_url">'.__("DL Request Url","RSC_plugin").'</label></p>';
		$form .= '<p><input id="dl_r_url" type="text" name="RSC_plugin_options[DL_opt][dl_r_url]" value="'.$dl_id['DL_opt']['dl_r_url'].'"/></p>';
		$form .= '</td><td align="right">'.$paragraph.'</td></tr>';
		
		return $form;
	}
	
	public function DL_Send_Data($post_data){		
		
		$app_id = self::$system_option['RSC_plugin_options']['DL_opt']['dl_id'];
		
		$r_url  = self::$system_option['RSC_plugin_options']['DL_opt']['dl_r_url'];
		
		 
		$body = $params;
		$body["appKey"] = $app_id;
		$body["docId"]  = $post_data["invoice_number_dl"];
		$opts = array(
			'http' => array(
				'header' => "Content-Type: application/json",
				'content' => json_encode($body)
			)
		);
		$result = file_get_contents($r_url, false, stream_context_create($opts));
		
		return (array)json_decode($result);
		
	}
	 
	public function DL_Get_Results($results) {	
		
		$html = '<div class="dl_invoice_results"><h3>'.__("Invoice Status: ","RSC_plugin");
		
		if($results['errors']->docid == "Требуется") { return $html .= __("Empty invoice number!","RSC_plugin").'</h3></div>'; }
		
		if($results['errors'] == "Накладная не найдена"){			
			return $html .= $results['errors'].'</h3></div>';
		} else {
			$html .= $results['state'].'</h3>';			
			
			$html .= '<span>'.__("Receiver info: ").'</span>';
			$html .= '<ul style="float:none;">';			
				$html .= '<li> <span class="receiver-info-item"> '.__("Terminal: ","RSC_plugin").' </span> '.$results['receive']->terminal.' </li>';
				$html .= '<li> <span class="receiver-info-item"> '.__("City: ","RSC_plugin").' </span> '.$results['receive']->city.' </li>';
				$html .= '<li> <span class="receiver-info-item"> '.__("Address: ","RSC_plugin").' </span> '.$results['receive']->address.' </li>';			
			$html .= '</ul>';
			
			$html .= '<span>'.__("Giveout info: ").'</span>';
			$html .= '<ul style="float:none;">';
				$html .= '<li> <span class="giveout-info-item"> '.__("Terminal: ","RSC_plugin").' </span> '.$results['giveout']->terminal .' </li>';
				$html .= '<li> <span class="giveout-info-item"> '.__("City: ","RSC_plugin").' </span> '.$results['giveout']->city .' </li>';
				$html .= '<li> <span class="giveout-info-item"> '.__("Address: ","RSC_plugin").' </span> '.$results['giveout']->address.' </li>';					
			$html .= '</ul>';
			
		}
		$html .= '</div>';

		return $html;
	}
	
	 
	
}

?>