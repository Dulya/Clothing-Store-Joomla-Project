<?php
/**
* KeyCAPTCHA Plugin for Joomla 3.x
* @version $Id: keycaptcha.php 2014-05-14 $
* @package: KeyCAPTCHA
* ===================================================
* @author
* Name: Mersane, Ltd, www.keycaptcha.com
* Email: support@keycaptcha.com
* Url: https://www.keycaptcha.com
* ===================================================
* @copyright (C) 2011-2014 Mersane, Ltd (www.keycaptcha.com). All rights reserved.
* @license GNU GPL 2.0 (http://www.gnu.org/licenses/gpl-2.0.html)
**/
/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
version 2 as published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

defined('_JEXEC') or die();
jimport('joomla.html.parameter');

if ( !class_exists('KeyCAPTCHA_CLASS') ) {
	class KeyCAPTCHA_CLASS
	{
		var $c_kc_keyword = "accept";
		var $p_kc_visitor_ip;
		var $p_kc_session_id;
		var $p_kc_web_server_sign;
		var $p_kc_web_server_sign2;
		var $p_kc_js_code;
		var $p_kc_private_key;
		var $p_kc_userID;

		function get_web_server_sign($use_visitor_ip = 0)
		{
			return md5($this->p_kc_session_id . (($use_visitor_ip) ? ($this->p_kc_visitor_ip) :("")) . $this->p_kc_private_key);
		}

		function KeyCAPTCHA_CLASS($a_private_key='', $buttonIDs='')
		{
			if ( $a_private_key != '' ){
				$set = explode("0",trim($a_private_key),2);
				if (sizeof($set)>1){  // if new type of private key
					$this->p_kc_private_key = trim($set[0]);
					$this->p_kc_userID = (int)$set[1];
					$this->p_kc_js_code = 
"<!-- KeyCAPTCHA code (www.keycaptcha.com)-->
<div id='div_for_keycaptcha'/>
<script type=\"text/javascript\">
	var s_s_c_user_id = '".$this->p_kc_userID."';
	var s_s_c_session_id = '#KC_SESSION_ID#';
	var s_s_c_captcha_field_id = 'capcode';
	var s_s_c_submit_button_id = '".$buttonIDs."';
	var s_s_c_web_server_sign = '#KC_WSIGN#';
	var s_s_c_web_server_sign2 = '#KC_WSIGN2#';

	var scr = document.createElement( 'script');
	scr.setAttribute( 'type', 'text/javascript' );
	document.head.appendChild( scr );
	scr.setAttribute( 'src', 'http://backs.keycaptcha.com/swfs/cap.js' );
</script>
<!-- end of KeyCAPTCHA code-->";
				}
			}
			
			$this->p_kc_session_id = uniqid() . '-5.0.10.35';
			$this->p_kc_visitor_ip = $_SERVER["REMOTE_ADDR"];
			$this->p_kc_web_server_sign = "";
			$this->p_kc_web_server_sign2 = "";
		}

		function http_get($path)
		{
			$arr = parse_url($path);
			$host = $arr['host'];
			$page = $arr['path'];
			if ( $page=='' ) {
				$page='/';
			}
			if ( isset( $arr['query'] ) ) {
				$page.='?'.$arr['query'];
			}
			$errno = 0;
			$errstr = '';
			$fp = fsockopen ($host, 80, $errno, $errstr, 30);
			if (!$fp){ return ""; }
			$request = "GET $page HTTP/1.0\r\n";
			$request .= "Host: $host\r\n";
			$request .= "Connection: close\r\n";
			$request .= "Cache-Control: no-store, no-cache\r\n";
			$request .= "Pragma: no-cache\r\n";
			$request .= "User-Agent: KeyCAPTCHA\r\n";
			$request .= "\r\n";

			fwrite ($fp,$request);
			$out = '';

			while (!feof($fp)) $out .= fgets($fp, 250);
			fclose($fp);
			$ov = explode("close\r\n\r\n", $out);

			return $ov[1];
		}

		function check_result($response)
		{
			$kc_vars = explode("|", $response);
			if ( count( $kc_vars ) < 4 )
			{
				return false;
			}
			if ($kc_vars[0] == md5($this->c_kc_keyword . $kc_vars[1] . $this->p_kc_private_key . $kc_vars[2]))
			{
				if (strpos(strtolower($kc_vars[2]), "http://") !== 0)
				{
					$kc_current_time = time();
					$kc_var_time = preg_split('/\/| |:/', $kc_vars[2]);
					$kc_submit_time = gmmktime($kc_var_time[3], $kc_var_time[4], $kc_var_time[5], $kc_var_time[1], $kc_var_time[2], $kc_var_time[0]);
					if (($kc_current_time - $kc_submit_time) < 15)
					{
						return true;
					}
				}
				else
				{
					if ($this->http_get($kc_vars[2]) == "1")
					{
						return true;
					}
				}
			}
			return false;
		}

		function render_js ()
		{
			if ( strlen($this->p_kc_js_code) == 0 )
			{
				return ('<div style="color:#FF0000;">'.JText::_('KEYCAPTCHA_EMPTY_SETTINGS_MESSAGE').'</div>');
			}
			if ( isset($_SERVER['HTTPS']) && ( $_SERVER['HTTPS'] == 'on' ) )
			{
				$this->p_kc_js_code = str_replace ("http://","https://", $this->p_kc_js_code);
			}
			$this->p_kc_js_code = str_replace ("#KC_SESSION_ID#", $this->p_kc_session_id, $this->p_kc_js_code);
			$this->p_kc_js_code = str_replace ("#KC_WSIGN#", $this->get_web_server_sign(1), $this->p_kc_js_code);
			$this->p_kc_js_code = str_replace ("#KC_WSIGN2#", $this->get_web_server_sign(), $this->p_kc_js_code);
			return $this->p_kc_js_code;
		}
	}
}

class plgSystemKeyCAPTCHA extends JPlugin
{

	var $kc_debug = false;


	function getParamsForForm( $aextname, $atask, $aview, $forcheck=false, $content='' ) {

		$viewmap = array( 
			// for common user functions
			// sample of format #1  view::form_name,task,KC_Option => additional html code
			"com_users" => array(				
				"registration,registration.register,KC_RegistrationForm" => "",
				"remind,remind.remind,KC_RemindForm" => "",
				"reset,reset.request,KC_ResetForm" => "",
			),
			"com_user" => array(				
				"register,register_save,KC_RegistrationForm" => "",
				"remind,remindusername,KC_RemindForm" => "",
				"reset,requestreset,KC_ResetForm" => "",
			),
			// for alpha registration
			"com_alpharegistration" => array(
				"register,register_save,KC_RegistrationForm" => "",
			),
			// for K2 comments
			"com_k2" => array(
				"item,comment,KC_Comments" => "",
			),
			// for virtuemart
			"com_virtuemart" => array(
				// sample of format #2 
				// in the begining of key string must be present "kc_find_form_by_fields" _N, KC_Option => array( form_field_1, form_field_2, form_field_3, form_field_4 )
				// now this format using only while checking captcha
				"kc_find_form_by_fields_1,KC_VirtueMartRegistration" => array (
					"email", "username", "password", "password2"
				),

				"kc_find_form_by_fields_2,KC_VirtueMartRegistration" => array (
					"first_name", "agreed", "address_1"
				),

				// VirtueMart 2.x
				"productdetails::askform,,KC_VirtueMartAsk" => "",
				"user::userForm,,KC_VirtueMartRegistration" => "",

				"kc_find_form_by_fields_4,KC_VirtueMartAsk" => array (
					"email", "comment"
				),

				"kc_find_form_by_fields_5,KC_VirtueMartRegistration" => array (
					"first_name", "address_1", "email",
				),
			),
			// for contact us
			"com_contact" => array(
				"contact,submit,KC_ContactUs" => "",	
				"kc_find_form_by_fields_1,KC_ContactUs" => array (
					"jform",
				),				
				
			),
			// df contact
			"com_dfcontact" => array(
				"::dfContactForm,,KC_ContactUs" => "",
				"kc_find_form_by_fields_1,KC_ContactUs" => array (
					"message", "email"
				),
			),
			// phoca guestbook
			"com_phocaguestbook" => array(
				"phocaguestbook,,KC_GuestBook" => "",
				"guestbook,,KC_GuestBook" => "",				
				"kc_find_form_by_fields_1,KC_GuestBook" => array (
					"email", "pgbcontent"
				),
				"kc_find_form_by_fields_2,KC_GuestBook" => array (
					"pgusername", "pgbcontent"
				),				
			),
			// comunity builder comprofiler
			"com_comprofiler" => array(
				"::adminForm@registers*saveregisters,,KC_RegistrationForm" => "",
				"::adminForm@userdetails,,KC_RegistrationForm" => "",
				"::adminForm@lostpassword*lostPassword,,KC_RemindForm" => "",
				"kc_find_form_by_fields_1,KC_RegistrationForm" => array (
					"email", "password", "password__verify"
				),
				"kc_find_form_by_fields_2,KC_RemindForm" => array (
					"checkemail",
				),
				"kc_find_form_by_fields_3,KC_RemindForm" => array (
					"checkusername",
				),
			),
			// JComments
			"com_jcomments" => array(
				"kc_find_form_by_fields_1,KC_Comments" => array (
					"name", "comment"
				),
			),
			"html#jcomments.saveComment,KC_Comments" => array ( // sample of format #3 find this string (after html#) in html
				"div::comments-form-buttons",	 // tag:: where insert the captcha
				"a::jcomments\.saveComment",	 // tag:: onclick content for hook				
				"<script type='text/javascript'>s_s_c_submit_button_id = 'kc_submit_but1-#-r';</script>"
			),
			// yvComment
			"com_yvcomment" => array(
				"EMPTYEMPTY,add,KC_Comments" => "",
			),
			"html#submitbuttonyvCommentForm,KC_Comments" => array ( // sample of format #3 find this string (after html#) in html
				"button::submitbuttonyvCommentForm", // tag:: where insert the captcha
				"button::submitbuttonyvCommentForm" // tag:: onclick content for hook				
			),
			// JoSocial
			"com_community" => array(				
				"register@*register_save,register_save,KC_RegistrationForm" => "", // sample of format #4 @ task or !task splitted by *
				"register@*register,register_save,KC_RegistrationForm" => "",
			),
			// JXtened comments  
			"com_comments" => array(
				"EMPTYEMPTY,comment.add,KC_Comments" => "",
			),
			"html#<h3 id=\"leave-response\",KC_Comments" => array(
				"input::submitter",
				"input::submitter",
				"<script type='text/javascript'>s_s_c_submit_button_id = 'submitter-#-r';</script>"
			),
			// ALFcontact
			"com_alfcontact" => array(
				",sendemail,KC_ContactUs" => "",
			),
			// FlexiContact
			"com_flexicontact" => array(
				",send,KC_ContactUs" => "",
			),
			// JoomlaDonation
			"com_jdonation" => array(
				"confirmation::jd_form|btnSubmit-#-fs,process_donation,KC_DonationConfirmForm" => "",
				"donation::jd_form|btnSubmit-#-fs,process_donation,KC_DonationForm" => "",
			),
			// JoomGallery			
			"com_joomgallery" => array(
				"detail::commentform|send-#-fs,,KC_Comments" => "",
				"kc_find_form_by_fields_1,KC_Comments" => array (
					"cmtname", "cmttext"
				),
			),
			// ChronoForms
			"com_chronocontact" => array(
				",,KC_ChronoForms" => "",
				"kc_find_form_by_fields_1,KC_ChronoForms" => array (
					"1cf1"
				),
			),
			//"com_chronoforms" => array(
			//	",,KC_ChronoForms" => "",
			//	"kc_find_form_by_fields_1,KC_ChronoForms" => array (
			//		"email"
			//	),
			//),
			// ADSManager
			"com_adsmanager" => array(
				"write_ad,,KC_ADSManager" => "",
				"write,,KC_ADSManager" => "",
				"edit,,KC_ADSManager" => "",
				"add,,KC_ADSManager" => "",
				"message,,KC_ADSManager" => "",		
				"kc_find_form_by_fields_1,KC_ADSManager" => array (
					"ad_headline"
				),
				"kc_find_form_by_fields_2,KC_ADSManager" => array (
					"email", "body"
				),
			),
			// QContacts
			"com_qcontacts" => array(
				"contact,submit,KC_ContactUs" => "",
			),
			// Job Board
			"com_jobboard" => array(
				"::applFRM|submit_application-#-s,,KC_JobBoard" => "",
				"kc_find_form_by_fields_1,KC_JobBoard" => array (
					"first_name", "last_name",
				),
			),
			// Mosets Tree review
			"com_mtree" => array(
				"::adminForm|addreview-#-r,addreview,KC_Comments" => "",				
			),
			// HikaShop
			"com_hikashop" => array(				
				"user::hikashop_registration_form,register,KC_RegistrationForm" => "",
				"checkout,step,KC_RegistrationForm" => "",
				"product::adminForm|send_email-#-r,,KC_ContactUs" => "",				
			),
			// JWHMCS Integrator
			"com_jwhmcs" => array(				
				"default::josForm,register_save,KC_RegistrationForm" => "",
				"signup::josForm,register_save,KC_RegistrationForm" => "",
			),
			// Appoitment Calendar
			"com_appointment" => array(				
				",,KC_AppCal" => "",
				"kc_find_form_by_fields_1,KC_AppCal" => array (
					"time", "message"
				),
			),
			// Easy Book Reloaded
			"com_easybookreloaded" => array(
				"entry,save,KC_GuestBook" => "",
			),
			// JoomShopping
			"com_jshopping" => array(
				"view::add_review,,KC_JoomShopping" => "",
				"register::loginForm,,KC_JoomShopping" => "",
				"kc_find_form_by_fields_1,KC_JoomShopping" => array (
					"user_name", "review"
				),
				"kc_find_form_by_fields_2,KC_JoomShopping" => array (
					"u_name", "password", "password_2"
				),
			),
			// aiContactSafe
			"com_aicontactsafe" =>array(
				"message,display,KC_ContactUs" => "",
				"kc_find_form_by_fields_1,KC_ContactUs" => array (
					"aics_email"
				)				
			),
			// Zoo
			"com_zoo"	=>array(
				"item,comment,KC_Comments"	=>"",
				"kc_find_form_by_fields_1,KC_Comments" => array (
					"author","email","content"
				),
			),			
		);		

		
		if ( ( $content != '' ) && ( $forcheck === false ) ) {
			foreach($viewmap as $m => $p) {
				if ( strpos( $m, 'html#' ) === false ) continue;
				$ap = explode( 'html#', $m );
				if ( count( $ap ) < 2 ) continue;
				$ap2 = explode( ',', $ap[1] );
				// 3.0
				//if ($this->params->get($ap2[1]) != 'Yes') continue;
				if ($this->params->$ap2[1] != 'Yes') continue;
				if ( strpos( $content, $ap2[0] ) === false ) continue;
				return array( "kc_byhtml", "", $p );
			}
		}
		
		if ( isset( $viewmap[ $aextname ] ) ) {
			$outarr = Array();
			foreach( $viewmap[ $aextname ] as $v => $p) {				
				if ( ( $forcheck === true ) && ( strpos( $v, "kc_find_form_by_fields" ) !== false ) ) {
					$av = explode( ',', $v );
					//3.0
					//if ($this->params->get($av[1]) == 'Yes') {
					if ($this->params->$av[1] == 'Yes') {
						$outarr[] = $p;
					}					
					continue;
				}
				$av = explode( ',', $v );
				
				if ( ( $forcheck === true ) && ( $atask != '' ) ) {					
					if ( ( $av[1] == $atask ) && ( $av[1] != '' ) ) {
						//3.0
						//if ($this->params->get($av[2]) == 'Yes') {
						if ($this->params->$av[2] == 'Yes') {
							return $av[0];
						};
					}					
				} else if ( $forcheck === false ) {					
					$av2 = explode( '@', $av[0] );
					$av3 = explode( '::', $av2[0] );
					if ( ( $av3[0] == $aview ) || ( $av3[0] == '' ) ) {						
						if ( isset( $av2[1] ) ) {
							$need_captcha = false;
							$av4 = explode( '*', $av2[1] );
							foreach($av4 as $i => $t) {
								if ( $t == $atask ) {
									$need_captcha = true;
									break;
								}
								if ( $t == '!'.$atask ) {
									break;
								}
							}
							if ( $need_captcha === false ) 
								continue;
						}
						// 3.0
						//if ($this->params->get($av[2]) == 'Yes') {
						if ($this->params->$av[2] == 'Yes') {
							if ( isset( $av3[1] ) ) {
								return array( $av3[1], $p );
							} else {
								return array( "", $p );
							}
						}
					}
				};
			}
		};



		if ( isset( $outarr ) ) {
			if ( count( $outarr ) > 0 ) {
				return $outarr;
			}
		}
		
		return false;
	}


	function onAfterDispatch()
        {	
		if ( $this->kc_debug ) {		
			/*foreach($_POST as $f => $v) {
				echo( $f.'='.$v.'    ' );
			}*/			
		}		
		// 3.0
		//$app = &JFactory::getApplication();
		$app = JFactory::getApplication();
		// if admin panel, then exit
		if ( $app->isAdmin() ) return false;
		// 3.0
		//$document = &JFactory::getDocument();
		$document = JFactory::getDocument();
		if ($document->getType() !== 'html') return false;
		
		$full_content = $document->getBuffer('component'); 
				
		$extname = JRequest::getVar('option');
		$task = JRequest::getVar('task');
		$view = JRequest::getVar('view');

		if ( $view == "" ) $view = JRequest::getVar('page');
		
		if ( $this->kc_debug ) {
			echo( '*'.$extname.':task-'.$task.':view-'.$view.'*|' );
		}

		if ( ( $task != '' ) && ( $view == '' ) ) {
			$view = $task;
		}

		if ( ( $extname == 'com_adsmanager' ) && ( $view == 'message' ) ) {
			$full_content = str_replace( '<input type="button" value=Send onclick="submitbutton()" />', '<input type="submit" value=Send onclick="javascript: submitbutton(); return false;" />', $full_content );	
		}

		$kcpars = $this->getParamsForForm( $extname, $task, $view, false, $full_content );
		if ( !isset( $kcpars[0] ) ) {
			if ( $this->kc_debug ) {
				echo( '-kc_disabled' );
			}
			return false;
		}
		
		//3.0
		//$this->params = new JParameter(JPluginHelper::getPlugin('system', 'keycaptcha')->params);
		$this->params = json_decode(JPluginHelper::getPlugin('system', 'keycaptcha')->params);

		//3.0
		//$user = &JFactory::getUser();
		//if ($this->params->get('KC_DisableForLogged', 'Yes') == 'Yes') {
		$user = JFactory::getUser();
		if ($this->params->KC_DisableForLogged == 'Yes') {
			if (!$user->guest) return false;
		}
			
		$button_name = "";
		$insert_params = false;		
		
		if ( $kcpars[0]=='kc_byhtml' ) {
			$insert_params = $kcpars[2];
			if ( isset( $insert_params[2] ) ) {
				$kcpars[1] = $insert_params[2];
			}
			if ( $this->kc_debug ) {
				echo( 'byhtml' );
			}
		}

		if ( ( $kcpars[0] != "" ) && ( $insert_params === false ) ) {			
			$all_pars = explode( '|', $kcpars[0] );
			$form_name = $all_pars[0];
			if ( isset( $all_pars[1] ) ) {
				$button_name = $all_pars[1];
			}
			$all_forms = Array();
			$content = '';			
			if ( preg_match_all( '/(<form .+?<\/form)/is', ' '.$full_content, $all_forms ) ) {				
				foreach($all_forms[0] as $k => $f) {
					$all_tf = Array();
					if ( preg_match_all( '/(<form(.*?)(?=>)>)/i', ' '.$f, $all_tf ) ) {						
						$p = $all_tf[1][0];
						$atmp = Array();
						if ( preg_match_all( '( name=(["\'].+?["\'])[ >])', $p, $atmp ) || preg_match_all( '( name = (["\'].+?["\'])[ >])', $p, $atmp ) ) {
							if ( str_replace( "'", "", str_replace( '"', '', $atmp[1][0] ) ) == $form_name ) {								
								$content = $f;
							}
						}
					}
				}
			}
			if ( $content == "" ) {				
				return false;
			}			
		} else {
			$content = $full_content;
		}
		// find place in form for adding captcha		
		
		$all_submit_names = Array();
		$captcha_before = '';

		if ( $insert_params !== false ) {
			$at1 = explode( '::', $insert_params[0] );
			$at2 = explode( '::', $insert_params[1] );
			$all_tags = Array();
			if ( preg_match_all( '/(<'.$at1[0].'(.*?)(?=>)>)/i', ' '.$content, $all_tags ) ) {
				foreach($all_tags[0] as $k => $p) { 
					if ( preg_match( '/'.$at1[1].'/i', $p ) ) {
						if ( $captcha_before == '' ) $captcha_before = $p;
					}
				}
			}
			$all_tags = Array();
			if ( preg_match_all( '/(<'.$at2[0].'(.*?)(?=>)>)/i', ' '.$content, $all_tags ) ) {
				$butnum = 1;
				foreach($all_tags[0] as $k => $p) {					
					if ( preg_match( '/'.$at2[1].'/i', $p ) ) {						
						$atmp = Array();						
						if ( preg_match_all( '/ id=(["\'].+?["\'])/i', $p, $atmp ) ) {
							$all_submit_names[] = str_replace( "'", "", str_replace( '"', '', $atmp[1][0] ) );
						} else {							
							$np = str_replace('<'.$at2[0].' ','<'.$at2[0].' id="kc_submit_but'.$butnum.'" ', $p);
							if ( $captcha_before == $p ) {
								$captcha_before = $np;
							}
							$content = str_replace($p,$np,$content);
							$all_submit_names[] = 'kc_submit_but'.$butnum;
							$butnum++;
						}
					}
				}
			}
		}
		
		if ( $captcha_before == '' ) {
			$all_tags = Array();
			if ( preg_match_all( '/(<input(.*?)[^>]*>)/i', ' '.$content, $all_tags ) ) {
				$butnum = 1;
				foreach($all_tags[0] as $k => $p) {
					if ( $button_name == '' ) {
						if ( preg_match( '( type=submit| type=[\'"]submit["\'])', $p ) || preg_match( '( type = submit| type = [\'"]submit["\'])', $p ) ) {
							if ( $captcha_before == '' ) $captcha_before = $p;					
							$atmp = Array();
							if ( preg_match_all( '( name=(["\'].+?["\'])[ >])', $p, $atmp ) ) {
								$all_submit_names[] = str_replace( "'", "", str_replace( '"', '', $atmp[1][0] ) );
							}
						}
					} else {
						$abn = explode( '-#-', $button_name );
						if ( preg_match_all( '( name=(["\'].+?["\'])[ >])', $p, $atmp ) ) {
							$bn = str_replace( "'", "", str_replace( '"', '', $atmp[1][0] ) );
							if ( $bn == $abn[0] ) {
								if ( $captcha_before == '' ) $captcha_before = $p;
								$all_submit_names[] = $button_name;
							}
						}					
						if ( $captcha_before == '' ) {
							if ( preg_match_all( '( onclick=(["\'].+?["\'])[ >])', $p, $atmp ) ) {					
								$bn = str_replace( "'", "", str_replace( '"', '', $atmp[1][0] ) );
								if ( strpos( $bn, $abn[0] ) ) {								
									$np = str_replace('/>',' id=kc_submit_butIC'.$butnum.' >',$p);
									if ( $p == $np ) {
										$np = str_replace('>',' id=kc_submit_butIC'.$butnum.' >',$p);
									}
									$content = str_replace($p,$np,$content);
									$full_content = str_replace($p,$np,$full_content);
									if ( $captcha_before == '' ) $captcha_before = $np;
									$all_submit_names[] = 'kc_submit_butIC'.$butnum;
									$butnum++;
								}
							}
						}
					}
				}
			}
		}

		$all_tags = Array();
		if ( $captcha_before == '' ) {
			//if ( preg_match_all( '/(<button(.*?)(?=>)>)/i', ' '.$content, $all_tags ) ) {
			if ( preg_match_all( '/(<button(.*?)[^>]*>)/i', ' '.$content, $all_tags ) ) {
				$butnum = 1;
				foreach($all_tags[0] as $k => $p) {
					if ( $button_name == '' ) {
						if ( preg_match( '( type=submit| type=[\'"]submit["\'])', $p ) ) {					
							if ( $captcha_before == '' ) $captcha_before = $p;
							$atmp = Array();
							if ( preg_match_all( '( name=(["\'].+?["\'])[ >])', $p, $atmp ) ) {
								$all_submit_names[] = str_replace( "'", "", str_replace( '"', '', $atmp[1][0] ) );
							}
						}
					} else {
						$abn = explode( '-#-', $button_name );
						if ( preg_match_all( '( name=(["\'].+?["\'])[ >])', $p, $atmp ) ) {
							$bn = str_replace( "'", "", str_replace( '"', '', $atmp[1][0] ) );
							if ( $bn == $abn[0] ) {
								if ( $captcha_before == '' ) $captcha_before = $p;
								$all_submit_names[] = $button_name;
							}
						}
						if ( $captcha_before == '' ) {
							if ( preg_match_all( '( onclick=(["\'].+?["\'])[ >])', $p, $atmp ) ) {					
								$bn = str_replace( "'", "", str_replace( '"', '', $atmp[1][0] ) );
								if ( strpos( $bn, $abn[0] ) ) {								
									$np = str_replace('/>',' id=kc_submit_butBC'.$butnum.' >',$p);
									if ( $p == $np ) {
										$np = str_replace('>',' id=kc_submit_butBC'.$butnum.' >',$p);
									}
									$content = str_replace($p,$np,$content);
									$full_content = str_replace($p,$np,$full_content);
									if ( $captcha_before == '' ) $captcha_before = $np;
									$all_submit_names[] = 'kc_submit_butBC'.$butnum;
									$butnum++;
								}
							}
						}
					}
				}
			}
		}

		//---------------------------------------------------------------------------------------------------------
		// HikaShop
		if ( $extname == 'com_hikashop' ) {
			// Contact US
			if ( JRequest::getVar('layout') == 'contact' ) {
				$kcpars[0] = '1';
				$insert_params = '1';
				$full_content = str_replace( '<button type="button" onclick="submitform(\'send_email\');">', '<button type="button" onclick="submitform(\'send_email\');" id="kc_submit_butHS">', $full_content );
				$content = $full_content;
				$all_submit_names[] = 'kc_submit_butHS';
				$captcha_before = '<input type="hidden" name="ctrl" value="product"';
			}
			// Checkout
			if ( JRequest::getVar('ctrl') == 'checkout' ) {
				$captcha_before = '<div id="hikashop_checkout_cart" class="hikashop_checkout_cart">';
			}
		}
		//---------------------------------------------------------------------------------------------------------
		
		if ( $captcha_before == '' ) {
			if ( $this->kc_debug ) {
				echo( 'EMPTY captcha_before' );
			}
			return false;
		}

		if ( ( !$this->extensionIsEnabled($extname) ) && ( $insert_params === false ) ) return false;		

		// 3.0
		//if ($this->params->get('KC_RocketTheme') == 'Yes') {
		if ($this->params->KC_RocketTheme == 'Yes') {
			$cbns = array( '<div class="edit-user-button">', '<div class="readon"', '<div class="readon-wrap' );
			
			foreach($cbns as $i => $cbn) {
				if ( strpos( $content, $cbn ) !== false ) {
					$captcha_before = $cbn;
					break;
				}
			}
		};	
		
		JPlugin::loadLanguage( 'plg_system_keycaptcha', JPATH_ADMINISTRATOR );

		//3.0
		//$task_message = $this->params->get('keycaptcha_custom_task_text');
		$task_message = $this->params->keycaptcha_custom_task_text;

		if ( $task_message == '' ) {
			if ( $view == 'register' )
				$task_message = JText::_('KEYCAPTCHA_TASK_REGISTRATION_MESSAGE');
			else 
				$task_message = JText::_('KEYCAPTCHA_TASK_COMMON_MESSAGE');
		}
		if ($this->params->KC_AllowKCLink=='Yes'){
 		    $task_message = "$task_message<a target='_blank' href='https://www.keycaptcha.com/joomla-captcha/' style='margin-left:100px; font-size:8px;float:right;'>Joomla CAPTCHA</a>";
		}

		//3.0
		//$kc_template = str_replace( '(gt)', '>', str_replace( '(lt)', '<', $this->params->get('keycaptcha_html') ) );
		$kc_template = str_replace( '(gt)', '>', str_replace( '(lt)', '<', $this->params->keycaptcha_html ) );
		if ( strpos( $kc_template, '#keycaptcha#' ) == false ) {
			$kc_template = "<br><div id='keycaptcha_div' style='height:220px; padding:0; margin:0; display:table; border:none;'>#keycaptcha#</div>";
		}

		if ( $kc_template[ strlen( $kc_template ) - 1 ] == "'" ) {
			$kc_template = substr( $kc_template, 0, strlen( $kc_template ) - 1 );
		}
		if ( $task_message[ strlen( $task_message ) - 1 ] == "'" ) {
			$task_message = substr( $task_message, 0, strlen( $task_message ) - 1 );
		}

		//3.0
		//$kc_o = new KeyCAPTCHA_CLASS($this->params->get('keycaptcha_site_private_key'), implode( ',', $all_submit_names )) ;
		$kc_o = new KeyCAPTCHA_CLASS($this->params->keycaptcha_site_private_key, implode( ',', $all_submit_names )) ;
		
		$kc_html = str_replace( '#keycaptcha#', '<table style="padding-top:10px; padding-bottom:10px; border:none; " cellpadding="0" cellspacing="0">
				<tr style="border:none;"><td style="border:none;"><input type="hidden" id="capcode" name="capcode" value="false" />
				'.$task_message.'</td></tr><tr style="border:none;"><td style="border:none;" align="center">'.str_replace( '(gt)', '>', str_replace( '(lt)', '<', $kc_o->render_js() ) ).
				'<noscript><b style="color:red;">'.JText::_('KEYCAPTCHA_NOSCRIPT_MESSAGE').'</b></noscript></td></tr></table>', $kc_template );

		// ADSManager
		if ( $extname == 'com_adsmanager' ) {			
			$kc_html = '</td></tr><tr><td colspan=2>'.$kc_html.'</td></tr><tr><td>';
		}

		if ( ($kcpars[0] == "") || ($insert_params !== false) ) {
			$new_content = str_replace($captcha_before,$kc_html.$captcha_before,$content);
		} else {
			$new_content = str_replace( $content, str_replace($captcha_before,$kc_html.$captcha_before,$content),$full_content);
		}

		$new_content .= $kcpars[1];
		
		if ( $new_content !== "" ) {
			$document->setBuffer( $new_content, 'component' );
		}
		
		return true;
	}

	function onAfterRoute()
        {
		global $mainframe;						
		
		// 3.0
		//$app = &JFactory::getApplication();
		$app = JFactory::getApplication();
		// if admin panel, then exit
		if ( $app->isAdmin() ) return false;

		// 3.0
		//$this->params = new JParameter(JPluginHelper::getPlugin('system', 'keycaptcha')->params);
		$this->params = json_decode(JPluginHelper::getPlugin('system', 'keycaptcha')->params);

		$extname = JRequest::getVar('option');
		$task = JRequest::getVar('task');
		$view = JRequest::getVar('view');

		// aiContactSafe
		if ($extname =="com_aicontactsafe"){
			$mail = JRequest::getVar('aics_email');
			if (empty($mail)){
				return false;
			}
		}		
		
		if ( $view == "" ) $view = JRequest::getVar('page');
		if ( $view == "" ) $view = JRequest::getVar('ctrl');
		
		if ( $this->kc_debug ) {
			echo( '|'.$extname.':'.$task.':'.$view.'|' );
		}

		if ( !$this->extensionIsEnabled($extname) ) return false;
		
		// 3.0
		//$user = &JFactory::getUser();
		$user=JFactory::getUser();
		//3.0
		//if ($this->params->get('KC_DisableForLogged', 'Yes') == 'Yes') {
		if ($this->params->KC_DisableForLogged == 'Yes') {
			if (!$user->guest) return false;
		}
		
		$kcpars = $this->getParamsForForm( $extname, $task, $view, true );

		//$app->redirect($redirect_url, JTEXT::_($extname.'|'.$task.'|'.$view), 'error');

		//---------------------------------------------------------------------------------------------------------
		// HikaShop
		if ( $extname == 'com_hikashop' ) {
			$d = JRequest::getVar('data');
			if ( isset( $d['register'] ) ) {
				//3.0
				//if ( (isset($d['register']['altbody']) && ($this->params->get('KC_ContactUs')=='Yes') ) ||
				//( (isset( $d['register']['password2'] ) && ($d['register']['password2'] != '') && ($this->params->get('KC_Register')=='Yes') )) )  
				if ( (isset($d['register']['altbody']) && ($this->params->KC_ContactUs=='Yes') ) || 
				   ( (isset( $d['register']['password2'] ) && ($d['register']['password2'] != '') && ($this->params->KC_Register=='Yes') )) )  
				{
					$kcpars = true;
				}
			}
		}
		//---------------------------------------------------------------------------------------------------------

		//if ( isset($kcpars[0]) && isset($kcpars[0][0]) ) {
		if (is_array($kcpars)){
			foreach($kcpars as $fn => $form) {
				$all_fields_present = true;
				foreach($form as $i => $field) {
					if ( JRequest::getVar($field,false) == false ) {
						$all_fields_present = false;
						break;
					}
				}
				if ( $all_fields_present === true ) {
					$kcpars = $fn;
					break;
				}
			}
			if ( !$all_fields_present ) {
				return false;
			}
		} else {
			if ( $kcpars === false ) {
				return false;
			}		
		}

		$capcode = JRequest::getVar('capcode',''); 
		//3.0
		//$kc_o = new KeyCAPTCHA_CLASS($this->params->get('keycaptcha_site_private_key'));
		$kc_o = new KeyCAPTCHA_CLASS($this->params->keycaptcha_site_private_key);
		if ($kc_o->check_result($capcode)) {
			return false;
		}		

		JPlugin::loadLanguage( 'plg_system_keycaptcha', JPATH_ADMINISTRATOR );
		
		if ($_SERVER['HTTP_REFERER']) {
			$redirect_url = $_SERVER['HTTP_REFERER'];
		} else {
			$redirect_url = 'index.php';
		}
			
		// special code for varios components
		if ( $extname == 'com_community' ) {
			if ( $task == 'register_save' ) {
				$ses = & JFactory::getSession();
				$ses->restart();
			};
		};

		$app->redirect($redirect_url, JTEXT::_('KEYCAPTCHA_WRONG_MESSAGE'), 'error');
	}	


	function extensionIsEnabled($extension_name = '') {
		if (!$extension_name) return false;
		
		$extension_type = substr($extension_name, 0, 3);
		
		$setNewBody = false;
		
		switch($extension_type) {
			case 'com':
				jimport( 'joomla.application.component.helper' );
				
				if (JComponentHelper::isEnabled($extension_name, true)) return true;
				break;
				
			case 'mod':
				jimport( 'joomla.application.module.helper' );
				
				// name has to be without "mod_" string at start
				if (JModuleHelper::isEnabled(substr($extension_name, 4))) return true;
				
				break;
				
			case 'plg':
				jimport( 'joomla.plugin.helper' );
				
				$extname = substr($extension_name, 4);
				
				// name has to be without "plg_" at start
				$plgname = substr($extname, strpos($extname, '_') + 1);
				
				$plgtype = substr($extname, 0, strlen($plgname) * -1 - 1);
				
				if (JPluginHelper::isEnabled($plgtype, $plgname)) {
					return true;
				}
				
				break;
				
			default: break;
		}
		
		return false;
	}
	
	/**
	 * This event is being triggered by application
	 * during rendering of the HTML form to be protected by CAPTCHA (Step 1)
	 * @param $Ok boolean output. true if Captcha application successfully
	 * 			processed the event.
	 * @param $captchaHtmlBlock string output. HTML block to be inserted into the
	 * 			HTML form. It MAY contain the 'secretword' form field,
	 * 			to be returned to the CAPTCHA during confirmation step
	 * 			(i.e. during Step 2, see #onCaptcha_Confirm)
	 */
	function onCaptcha_DisplayHtmlBlock( &$Ok, &$captchaHtmlBlock, $submit_ids='' ) {
		JPlugin::loadLanguage( 'plg_system_keycaptcha', JPATH_ADMINISTRATOR );
		$this->params = new JParameter(JPluginHelper::getPlugin('system', 'keycaptcha')->params);
		// 3.0
		//$task_message = $this->params->get('keycaptcha_custom_task_text');
		$task_message = $this->params->keycaptcha_custom_task_text;
		$view = JRequest::getVar('view');
		if ( $task_message == '' ) {
			if ( $view == 'register' )
				$task_message = JText::_('KEYCAPTCHA_TASK_REGISTRATION_MESSAGE');
			else 
				$task_message = JText::_('KEYCAPTCHA_TASK_COMMON_MESSAGE');
		}
		//3.0
		//$kc_code = $this->params->get('keycaptcha_code');
		$kc_code = $this->params->keycaptcha_code;
		if ( $kc_code[ strlen( $kc_code ) - 1 ] == "'" ) {
			$kc_code = substr( $kc_code, 0, strlen( $kc_code ) - 1 );
		}
		// 3.0
		//$kc_o = new KeyCAPTCHA_CLASS($this->params->get('keycaptcha_site_private_key'), str_replace( '#BUTTONS#', $submit_ids, $kc_code ) );
		$kc_o = new KeyCAPTCHA_CLASS($this->params->keycaptcha_site_private_key, str_replace( '#BUTTONS#', $submit_ids, $kc_code ) );
		$Ok = true;
		//3.0
		//$kc_template = str_replace( '(gt)', '>', str_replace( '(lt)', '<', $this->params->get('keycaptcha_html') ) );
		$kc_template = str_replace( '(gt)', '>', str_replace( '(lt)', '<', $this->params->keycaptcha_html ) );		
		if ( strpos( $kc_template, '#keycaptcha#' ) == false ) {
			$kc_template = "<br><div id='keycaptcha_div' style='height:220px; padding:0; margin:0; display:table; border:none;'>#keycaptcha#</div>";
		}
		if ( $kc_template[ strlen( $kc_template ) - 1 ] == "'" ) {
			$kc_template = substr( $kc_template, 0, strlen( $kc_template ) - 1 );
		}
		if ( $task_message[ strlen( $task_message ) - 1 ] == "'" ) {
			$task_message = substr( $task_message, 0, strlen( $task_message ) - 1 );
		}		
		$captchaHtmlBlock = str_replace( '#keycaptcha#', '<table style="padding-top:10px; padding-bottom:10px; border:none; " cellpadding="0" cellspacing="0">
				<tr style="border:none;"><td style="border:none;"><input type="hidden" id="capcode" name="capcode" value="false" />
				'.$task_message.'</td></tr><tr style="border:none;"><td style="border:none;" align="center">'.str_replace( '(gt)', '>', str_replace( '(lt)', '<', $kc_o->render_js() ) ).
				'<noscript><b style="color:red;">'.JText::_('KEYCAPTCHA_NOSCRIPT_MESSAGE').'</b></noscript></td></tr></table>', $kc_template );
		return $Ok;
	}

	/**
	 * This event is being triggered by application
	 * on receiveing of the User submitted form, protected by CAPTCHA (Step 2)
	 * @param $secretword string input. - Not used
	 *
	 * @param $Ok boolean output. true if Captcha application successfully
	 * 			processed the event.
	 */
	function onCaptcha_Confirm( $secretword, &$Ok ) {
		$Ok = false;
		$capcode = JRequest::getVar('capcode','');
		$this->params = new JParameter(JPluginHelper::getPlugin('system', 'keycaptcha')->params);
		// 3.0
		//$kc_o = new KeyCAPTCHA_CLASS($this->params->get('keycaptcha_site_private_key'));
		$kc_o = new KeyCAPTCHA_CLASS($this->params->keycaptcha_site_private_key);
		if ($kc_o->check_result($capcode)) {
			$Ok = true;
		}
		return $Ok;
	}
}
?>
