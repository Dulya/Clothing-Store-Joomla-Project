<?php
/**
 * @package	AcySMS for Joomla!
 * @version	3.1.0
 * @author	acyba.com
 * @copyright	(C) 2009-2016 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php

class ACYSMScountryType{



	public $phonewidth = 50;
	public $phonemaxwidth = 80;
	public $readOnly = false;

	public $field;
	public $inside;
	public $idtag = '';

	public $placeholder = '';

	var $autofocus = false;

	function __construct(){
		$this->flagPosition = array();
		$this->flagPosition['93'] = array('x' => -48, 'y' => 0);
		$this->flagPosition['355'] = array('x' => -96, 'y' => 0);
		$this->flagPosition['213'] = array('x' => -160, 'y' => -33);
		$this->flagPosition['1684'] = array('x' => -176, 'y' => 0);
		$this->flagPosition['376'] = array('x' => -16, 'y' => 0);
		$this->flagPosition['244'] = array('x' => -144, 'y' => 0);
		$this->flagPosition['1264'] = array('x' => -80, 'y' => 0);
		$this->flagPosition['672'] = array('x' => 0, 'y' => -176); //antartica
		$this->flagPosition['1268'] = array('x' => -64, 'y' => 0);
		$this->flagPosition['54'] = array('x' => -160, 'y' => 0);
		$this->flagPosition['374'] = array('x' => -112, 'y' => 0);
		$this->flagPosition['297'] = array('x' => -224, 'y' => 0);
		$this->flagPosition['247'] = array('x' => -16, 'y' => -176); //ascenscion island
		$this->flagPosition['61'] = array('x' => -208, 'y' => 0);
		$this->flagPosition['43'] = array('x' => -192, 'y' => 0);
		$this->flagPosition['994'] = array('x' => -240, 'y' => 0);
		$this->flagPosition['1242'] = array('x' => -208, 'y' => -11);
		$this->flagPosition['973'] = array('x' => -96, 'y' => -11);
		$this->flagPosition['880'] = array('x' => -32, 'y' => -11);
		$this->flagPosition['1246'] = array('x' => -16, 'y' => -11);
		$this->flagPosition['375'] = array('x' => -16, 'y' => -22);
		$this->flagPosition['32'] = array('x' => -48, 'y' => -11);
		$this->flagPosition['501'] = array('x' => -32, 'y' => -22);
		$this->flagPosition['229'] = array('x' => -128, 'y' => -11);
		$this->flagPosition['1441'] = array('x' => -144, 'y' => -11);
		$this->flagPosition['975'] = array('x' => -224, 'y' => -11);
		$this->flagPosition['591'] = array('x' => -176, 'y' => -11);
		$this->flagPosition['387'] = array('x' => 0, 'y' => -11);
		$this->flagPosition['267'] = array('x' => 0, 'y' => -22);
		$this->flagPosition['55'] = array('x' => -192, 'y' => -11);
		$this->flagPosition['1284'] = array('x' => -240, 'y' => -154);
		$this->flagPosition['673'] = array('x' => -160, 'y' => -11);
		$this->flagPosition['359'] = array('x' => -80, 'y' => -11);
		$this->flagPosition['226'] = array('x' => -64, 'y' => -11);
		$this->flagPosition['257'] = array('x' => -112, 'y' => -11);
		$this->flagPosition['855'] = array('x' => -64, 'y' => -77);
		$this->flagPosition['237'] = array('x' => -192, 'y' => -22);
		$this->flagPosition['1'] = array('x' => -48, 'y' => -22);
		$this->flagPosition['238'] = array('x' => -16, 'y' => -33);
		$this->flagPosition['1345'] = array('x' => -192, 'y' => -77);
		$this->flagPosition['236'] = array('x' => -96, 'y' => -22);
		$this->flagPosition['235'] = array('x' => -112, 'y' => -143);
		$this->flagPosition['56'] = array('x' => -176, 'y' => -22);
		$this->flagPosition['86'] = array('x' => -208, 'y' => -22);
		$this->flagPosition['6724'] = array('x' => -32, 'y' => -176); //christmas island
		$this->flagPosition['6722'] = array('x' => -48, 'y' => -176); //coco keeling island
		$this->flagPosition['57'] = array('x' => -224, 'y' => -22);
		$this->flagPosition['269'] = array('x' => -96, 'y' => -77);
		$this->flagPosition['243'] = array('x' => -80, 'y' => -22);
		$this->flagPosition['242'] = array('x' => -112, 'y' => -22);
		$this->flagPosition['682'] = array('x' => -160, 'y' => -22);
		$this->flagPosition['506'] = array('x' => -240, 'y' => -22);
		$this->flagPosition['225'] = array('x' => -144, 'y' => -22);
		$this->flagPosition['385'] = array('x' => 0, 'y' => -66);
		$this->flagPosition['53'] = array('x' => 0, 'y' => -33);
		$this->flagPosition['357'] = array('x' => -48, 'y' => -33);
		$this->flagPosition['420'] = array('x' => -64, 'y' => -33);
		$this->flagPosition['45'] = array('x' => -112, 'y' => -33);
		$this->flagPosition['253'] = array('x' => -96, 'y' => -33);
		$this->flagPosition['1767'] = array('x' => -128, 'y' => -33);
		$this->flagPosition['1809'] = array('x' => -144, 'y' => -33);
		$this->flagPosition['593'] = array('x' => -176, 'y' => -33);
		$this->flagPosition['20'] = array('x' => -208, 'y' => -33);
		$this->flagPosition['503'] = array('x' => -32, 'y' => -143);
		$this->flagPosition['240'] = array('x' => -96, 'y' => -55);
		$this->flagPosition['291'] = array('x' => 0, 'y' => -44);
		$this->flagPosition['372'] = array('x' => -192, 'y' => -33);
		$this->flagPosition['251'] = array('x' => -32, 'y' => -44);
		$this->flagPosition['500'] = array('x' => -96, 'y' => -44);
		$this->flagPosition['298'] = array('x' => -128, 'y' => -44);
		$this->flagPosition['679'] = array('x' => -80, 'y' => -44);
		$this->flagPosition['358'] = array('x' => -64, 'y' => -44);
		$this->flagPosition['33'] = array('x' => -144, 'y' => -44);
		$this->flagPosition['596'] = array('x' => -80, 'y' => -99);
		$this->flagPosition['594'] = array('x' => -128, 'y' => -176); //french guiana
		$this->flagPosition['689'] = array('x' => -224, 'y' => -110);
		$this->flagPosition['241'] = array('x' => -160, 'y' => -44);
		$this->flagPosition['220'] = array('x' => -48, 'y' => -55);
		$this->flagPosition['995'] = array('x' => -208, 'y' => -44);
		$this->flagPosition['49'] = array('x' => -80, 'y' => -33);
		$this->flagPosition['233'] = array('x' => 0, 'y' => -55);
		$this->flagPosition['350'] = array('x' => -16, 'y' => -55);
		$this->flagPosition['30'] = array('x' => -112, 'y' => -55);
		$this->flagPosition['299'] = array('x' => -32, 'y' => -55);
		$this->flagPosition['1473'] = array('x' => -192, 'y' => -44);
		$this->flagPosition['590'] = array('x' => -80, 'y' => -55);
		$this->flagPosition['1671'] = array('x' => -160, 'y' => -55);
		$this->flagPosition['502'] = array('x' => -144, 'y' => -55);
		$this->flagPosition['224'] = array('x' => -64, 'y' => -55);
		$this->flagPosition['245'] = array('x' => -176, 'y' => -55);
		$this->flagPosition['592'] = array('x' => -192, 'y' => -55);
		$this->flagPosition['509'] = array('x' => -16, 'y' => -66);
		$this->flagPosition['504'] = array('x' => -240, 'y' => -55);
		$this->flagPosition['852'] = array('x' => -208, 'y' => -55);
		$this->flagPosition['36'] = array('x' => -32, 'y' => -66);
		$this->flagPosition['354'] = array('x' => -192, 'y' => -66);
		$this->flagPosition['91'] = array('x' => -128, 'y' => -66);
		$this->flagPosition['62'] = array('x' => -64, 'y' => -66);
		$this->flagPosition['964'] = array('x' => -160, 'y' => -66);
		$this->flagPosition['98'] = array('x' => -176, 'y' => -66);
		$this->flagPosition['353'] = array('x' => -80, 'y' => -66);
		$this->flagPosition['972'] = array('x' => -96, 'y' => -66);
		$this->flagPosition['39'] = array('x' => -208, 'y' => -66);
		$this->flagPosition['1876'] = array('x' => -240, 'y' => -66);
		$this->flagPosition['81'] = array('x' => -16, 'y' => -77);
		$this->flagPosition['962'] = array('x' => 0, 'y' => -77);
		$this->flagPosition['254'] = array('x' => -32, 'y' => -77);
		$this->flagPosition['686'] = array('x' => -80, 'y' => -77);
		$this->flagPosition['3774'] = array('x' => -64, 'y' => -176); //kosovo
		$this->flagPosition['965'] = array('x' => -176, 'y' => -77);
		$this->flagPosition['996'] = array('x' => -48, 'y' => -77);
		$this->flagPosition['856'] = array('x' => -224, 'y' => -77);
		$this->flagPosition['371'] = array('x' => -112, 'y' => -88);
		$this->flagPosition['961'] = array('x' => -240, 'y' => -77);
		$this->flagPosition['266'] = array('x' => -64, 'y' => -88);
		$this->flagPosition['231'] = array('x' => -48, 'y' => -88);
		$this->flagPosition['218'] = array('x' => -128, 'y' => -88);
		$this->flagPosition['423'] = array('x' => -16, 'y' => -88);
		$this->flagPosition['370'] = array('x' => -80, 'y' => -88);
		$this->flagPosition['352'] = array('x' => -96, 'y' => -88);
		$this->flagPosition['853'] = array('x' => -48, 'y' => -99);
		$this->flagPosition['389'] = array('x' => -240, 'y' => -88);
		$this->flagPosition['261'] = array('x' => -208, 'y' => -88);
		$this->flagPosition['265'] = array('x' => -176, 'y' => -99);
		$this->flagPosition['60'] = array('x' => -208, 'y' => -99);
		$this->flagPosition['960'] = array('x' => -160, 'y' => -99);
		$this->flagPosition['223'] = array('x' => 0, 'y' => -99);
		$this->flagPosition['356'] = array('x' => -128, 'y' => -99);
		$this->flagPosition['692'] = array('x' => -224, 'y' => -88);
		$this->flagPosition['222'] = array('x' => -96, 'y' => -99);
		$this->flagPosition['230'] = array('x' => -144, 'y' => -99);
		$this->flagPosition['52'] = array('x' => -192, 'y' => -99);
		$this->flagPosition['521'] = array('x' => -192, 'y' => -99);
		$this->flagPosition['691'] = array('x' => -112, 'y' => -44);
		$this->flagPosition['373'] = array('x' => -176, 'y' => -88);
		$this->flagPosition['377'] = array('x' => -160, 'y' => -88);
		$this->flagPosition['976'] = array('x' => -32, 'y' => -99);
		$this->flagPosition['382'] = array('x' => -192, 'y' => -88);
		$this->flagPosition['1664'] = array('x' => -112, 'y' => -99);
		$this->flagPosition['212'] = array('x' => -144, 'y' => -88);
		$this->flagPosition['258'] = array('x' => -224, 'y' => -99);
		$this->flagPosition['95'] = array('x' => -16, 'y' => -99);
		$this->flagPosition['264'] = array('x' => -240, 'y' => -99);
		$this->flagPosition['674'] = array('x' => -128, 'y' => -110);
		$this->flagPosition['977'] = array('x' => -112, 'y' => -110);
		$this->flagPosition['31'] = array('x' => -80, 'y' => -110);
		$this->flagPosition['599'] = array('x' => -128, 'y' => 0);
		$this->flagPosition['687'] = array('x' => 0, 'y' => -110);
		$this->flagPosition['64'] = array('x' => -160, 'y' => -110);
		$this->flagPosition['505'] = array('x' => -64, 'y' => -110);
		$this->flagPosition['227'] = array('x' => -16, 'y' => -110);
		$this->flagPosition['234'] = array('x' => -48, 'y' => -110);
		$this->flagPosition['683'] = array('x' => -144, 'y' => -110);
		$this->flagPosition['6723'] = array('x' => -32, 'y' => -110);
		$this->flagPosition['850'] = array('x' => -128, 'y' => -77);
		$this->flagPosition['47'] = array('x' => -96, 'y' => -110);
		$this->flagPosition['968'] = array('x' => -176, 'y' => -110);
		$this->flagPosition['92'] = array('x' => -16, 'y' => -121);
		$this->flagPosition['680'] = array('x' => -80, 'y' => -176); //palau
		$this->flagPosition['970'] = array('x' => -96, 'y' => -121);
		$this->flagPosition['507'] = array('x' => -192, 'y' => -110);
		$this->flagPosition['675'] = array('x' => -240, 'y' => -110);
		$this->flagPosition['595'] = array('x' => -144, 'y' => -121);
		$this->flagPosition['51'] = array('x' => -208, 'y' => -110);
		$this->flagPosition['63'] = array('x' => 0, 'y' => -121);
		$this->flagPosition['48'] = array('x' => -32, 'y' => -121);
		$this->flagPosition['351'] = array('x' => -112, 'y' => -121);
		$this->flagPosition['1787'] = array('x' => -80, 'y' => -121);
		$this->flagPosition['974'] = array('x' => -160, 'y' => -121);
		$this->flagPosition['262'] = array('x' => -144, 'y' => -176); //reunion island
		$this->flagPosition['40'] = array('x' => -192, 'y' => -121);
		$this->flagPosition['7'] = array('x' => -224, 'y' => -121);
		$this->flagPosition['250'] = array('x' => -240, 'y' => -121);
		$this->flagPosition['1670'] = array('x' => -96, 'y' => -176); //marianne
		$this->flagPosition['378'] = array('x' => -176, 'y' => -132);
		$this->flagPosition['239'] = array('x' => -16, 'y' => -143);
		$this->flagPosition['966'] = array('x' => 0, 'y' => -132);
		$this->flagPosition['221'] = array('x' => -192, 'y' => -132);
		$this->flagPosition['381'] = array('x' => -208, 'y' => -121);
		$this->flagPosition['248'] = array('x' => -32, 'y' => -132);
		$this->flagPosition['232'] = array('x' => -160, 'y' => -132);
		$this->flagPosition['65'] = array('x' => -96, 'y' => -132);
		$this->flagPosition['421'] = array('x' => -144, 'y' => -132);
		$this->flagPosition['386'] = array('x' => -128, 'y' => -132);
		$this->flagPosition['677'] = array('x' => -16, 'y' => -132);
		$this->flagPosition['252'] = array('x' => -208, 'y' => -132);
		$this->flagPosition['685'] = array('x' => -112, 'y' => -176); //somoa
		$this->flagPosition['27'] = array('x' => -128, 'y' => -165);
		$this->flagPosition['82'] = array('x' => -144, 'y' => -77);
		$this->flagPosition['34'] = array('x' => -16, 'y' => -44);
		$this->flagPosition['94'] = array('x' => -32, 'y' => -88);
		$this->flagPosition['290'] = array('x' => -112, 'y' => -132);
		$this->flagPosition['1869'] = array('x' => -112, 'y' => -77);
		$this->flagPosition['1758'] = array('x' => 0, 'y' => -88);
		$this->flagPosition['508'] = array('x' => -48, 'y' => -121);
		$this->flagPosition['1784'] = array('x' => -208, 'y' => -154);
		$this->flagPosition['249'] = array('x' => -64, 'y' => -132);
		$this->flagPosition['597'] = array('x' => -240, 'y' => -132);
		$this->flagPosition['268'] = array('x' => -80, 'y' => -143);
		$this->flagPosition['46'] = array('x' => -80, 'y' => -132);
		$this->flagPosition['41'] = array('x' => -128, 'y' => -22);
		$this->flagPosition['963'] = array('x' => -64, 'y' => -143);
		$this->flagPosition['886'] = array('x' => -64, 'y' => -154);
		$this->flagPosition['992'] = array('x' => -176, 'y' => -143);
		$this->flagPosition['255'] = array('x' => -80, 'y' => -154);
		$this->flagPosition['66'] = array('x' => -160, 'y' => -143);
		$this->flagPosition['228'] = array('x' => -144, 'y' => -143);
		$this->flagPosition['690'] = array('x' => -192, 'y' => -143);
		$this->flagPosition['676'] = array('x' => 0, 'y' => -154);
		$this->flagPosition['1868'] = array('x' => -32, 'y' => -154);
		$this->flagPosition['216'] = array('x' => -240, 'y' => -143);
		$this->flagPosition['90'] = array('x' => -16, 'y' => -154);
		$this->flagPosition['993'] = array('x' => -224, 'y' => -143);
		$this->flagPosition['1649'] = array('x' => -96, 'y' => -143);
		$this->flagPosition['688'] = array('x' => -48, 'y' => -154);
		$this->flagPosition['256'] = array('x' => -112, 'y' => -154);
		$this->flagPosition['380'] = array('x' => -96, 'y' => -154);
		$this->flagPosition['971'] = array('x' => -32, 'y' => 0);
		$this->flagPosition['44'] = array('x' => -176, 'y' => -44);
		$this->flagPosition['598'] = array('x' => -160, 'y' => -154);
		$this->flagPosition['1 '] = array('x' => -144, 'y' => -154);
		$this->flagPosition['998'] = array('x' => -176, 'y' => -154);
		$this->flagPosition['678'] = array('x' => -32, 'y' => -165);
		$this->flagPosition['3966'] = array('x' => -192, 'y' => -154);
		$this->flagPosition['58'] = array('x' => -224, 'y' => -154);
		$this->flagPosition['84'] = array('x' => -16, 'y' => -165);
		$this->flagPosition['1340'] = array('x' => 0, 'y' => -165);
		$this->flagPosition['681'] = array('x' => -64, 'y' => -165);
		$this->flagPosition['967'] = array('x' => -96, 'y' => -165);
		$this->flagPosition['260'] = array('x' => -160, 'y' => -165);
		$this->flagPosition['263'] = array('x' => -176, 'y' => -165);


		$this->country = array();
		$this->country['93'] = 'Afghanistan';
		$this->country['355'] = 'Albania';
		$this->country['213'] = 'Algeria';
		$this->country['1684'] = 'American Samoa';
		$this->country['376'] = 'Andorra';
		$this->country['244'] = 'Angola';
		$this->country['1264'] = 'Anguilla';
		$this->country['672'] = 'Antarctica';
		$this->country['1268'] = 'Antigua & Barbuda';
		$this->country['54'] = 'Argentina';
		$this->country['374'] = 'Armenia';
		$this->country['297'] = 'Aruba';
		$this->country['247'] = 'Ascension Island';
		$this->country['61'] = 'Australia';
		$this->country['43'] = 'Austria';
		$this->country['994'] = 'Azerbaijan';
		$this->country['1242'] = 'Bahamas';
		$this->country['973'] = 'Bahrain';
		$this->country['880'] = 'Bangladesh';
		$this->country['1246'] = 'Barbados';
		$this->country['375'] = 'Belarus';
		$this->country['32'] = 'Belgium';
		$this->country['501'] = 'Belize';
		$this->country['229'] = 'Benin';
		$this->country['1441'] = 'Bermuda';
		$this->country['975'] = 'Bhutan';
		$this->country['591'] = 'Bolivia';
		$this->country['387'] = 'Bosnia/Herzegovina';
		$this->country['267'] = 'Botswana';
		$this->country['55'] = 'Brazil';
		$this->country['1284'] = 'British Virgin Islands';
		$this->country['673'] = 'Brunei';
		$this->country['359'] = 'Bulgaria';
		$this->country['226'] = 'Burkina Faso';
		$this->country['257'] = 'Burundi';
		$this->country['855'] = 'Cambodia';
		$this->country['237'] = 'Cameroon';
		$this->country['1'] = 'Canada/USA';
		$this->country['238'] = 'Cape Verde Islands';
		$this->country['1345'] = 'Cayman Islands';
		$this->country['236'] = 'Central African Republic';
		$this->country['235'] = 'Chad Republic';
		$this->country['56'] = 'Chile';
		$this->country['86'] = 'China';
		$this->country['6724'] = 'Christmas Island';
		$this->country['6722'] = 'Cocos Keeling Island';
		$this->country['57'] = 'Colombia';
		$this->country['269'] = 'Comoros';
		$this->country['243'] = 'Congo Democratic Republic';
		$this->country['242'] = 'Congo, Republic of';
		$this->country['682'] = 'Cook Islands';
		$this->country['506'] = 'Costa Rica';
		$this->country['225'] = 'Cote D\'Ivoire';
		$this->country['385'] = 'Croatia';
		$this->country['53'] = 'Cuba';
		$this->country['357'] = 'Cyprus';
		$this->country['420'] = 'Czech Republic';
		$this->country['45'] = 'Denmark';
		$this->country['253'] = 'Djibouti';
		$this->country['1767'] = 'Dominica';
		$this->country['1809'] = 'Dominican Republic';
		$this->country['593'] = 'Ecuador';
		$this->country['20'] = 'Egypt';
		$this->country['503'] = 'El Salvador';
		$this->country['240'] = 'Equatorial Guinea';
		$this->country['291'] = 'Eritrea';
		$this->country['372'] = 'Estonia';
		$this->country['251'] = 'Ethiopia';
		$this->country['500'] = 'Falkland Islands';
		$this->country['298'] = 'Faroe Island';
		$this->country['679'] = 'Fiji Islands';
		$this->country['358'] = 'Finland';
		$this->country['33'] = 'France';
		$this->country['596'] = 'French Antilles/Martinique';
		$this->country['594'] = 'French Guiana';
		$this->country['689'] = 'French Polynesia';
		$this->country['241'] = 'Gabon Republic';
		$this->country['220'] = 'Gambia';
		$this->country['995'] = 'Georgia';
		$this->country['49'] = 'Germany';
		$this->country['233'] = 'Ghana';
		$this->country['350'] = 'Gibraltar';
		$this->country['30'] = 'Greece';
		$this->country['299'] = 'Greenland';
		$this->country['1473'] = 'Grenada';
		$this->country['590'] = 'Guadeloupe';
		$this->country['1671'] = 'Guam';
		$this->country['502'] = 'Guatemala';
		$this->country['224'] = 'Guinea Republic';
		$this->country['245'] = 'Guinea-Bissau';
		$this->country['592'] = 'Guyana';
		$this->country['509'] = 'Haiti';
		$this->country['504'] = 'Honduras';
		$this->country['852'] = 'Hong Kong';
		$this->country['36'] = 'Hungary';
		$this->country['354'] = 'Iceland';
		$this->country['91'] = 'India';
		$this->country['62'] = 'Indonesia';
		$this->country['964'] = 'Iraq';
		$this->country['98'] = 'Iran';
		$this->country['353'] = 'Ireland';
		$this->country['972'] = 'Israel';
		$this->country['39'] = 'Italy';
		$this->country['1876'] = 'Jamaica';
		$this->country['81'] = 'Japan';
		$this->country['962'] = 'Jordan';
		$this->country['254'] = 'Kenya';
		$this->country['686'] = 'Kiribati';
		$this->country['3774'] = 'Kosovo';
		$this->country['965'] = 'Kuwait';
		$this->country['996'] = 'Kyrgyzstan';
		$this->country['856'] = 'Laos';
		$this->country['371'] = 'Latvia';
		$this->country['961'] = 'Lebanon';
		$this->country['266'] = 'Lesotho';
		$this->country['231'] = 'Liberia';
		$this->country['218'] = 'Libya';
		$this->country['423'] = 'Liechtenstein';
		$this->country['370'] = 'Lithuania';
		$this->country['352'] = 'Luxembourg';
		$this->country['853'] = 'Macau';
		$this->country['389'] = 'Macedonia';
		$this->country['261'] = 'Madagascar';
		$this->country['265'] = 'Malawi';
		$this->country['60'] = 'Malaysia';
		$this->country['960'] = 'Maldives';
		$this->country['223'] = 'Mali Republic';
		$this->country['356'] = 'Malta';
		$this->country['692'] = 'Marshall Islands';
		$this->country['222'] = 'Mauritania';
		$this->country['230'] = 'Mauritius';
		$this->country['52'] = 'Mexico';
		$this->country['521'] = 'Mexico';
		$this->country['691'] = 'Micronesia';
		$this->country['373'] = 'Moldova';
		$this->country['377'] = 'Monaco';
		$this->country['976'] = 'Mongolia';
		$this->country['382'] = 'Montenegro';
		$this->country['1664'] = 'Montserrat';
		$this->country['212'] = 'Morocco';
		$this->country['258'] = 'Mozambique';
		$this->country['95'] = 'Myanmar (Burma)';
		$this->country['264'] = 'Namibia';
		$this->country['674'] = 'Nauru';
		$this->country['977'] = 'Nepal';
		$this->country['31'] = 'Netherlands';
		$this->country['599'] = 'Netherlands Antilles';
		$this->country['687'] = 'New Caledonia';
		$this->country['64'] = 'New Zealand';
		$this->country['505'] = 'Nicaragua';
		$this->country['227'] = 'Niger Republic';
		$this->country['234'] = 'Nigeria';
		$this->country['683'] = 'Niue Island';
		$this->country['6723'] = 'Norfolk';
		$this->country['850'] = 'North Korea';
		$this->country['47'] = 'Norway';
		$this->country['968'] = 'Oman Dem Republic';
		$this->country['92'] = 'Pakistan';
		$this->country['680'] = 'Palau Republic';
		$this->country['970'] = 'Palestine';
		$this->country['507'] = 'Panama';
		$this->country['675'] = 'Papua New Guinea';
		$this->country['595'] = 'Paraguay';
		$this->country['51'] = 'Peru';
		$this->country['63'] = 'Philippines';
		$this->country['48'] = 'Poland';
		$this->country['351'] = 'Portugal';
		$this->country['1787'] = 'Puerto Rico';
		$this->country['974'] = 'Qatar';
		$this->country['262'] = 'Reunion Island';
		$this->country['40'] = 'Romania';
		$this->country['7'] = 'Russia';
		$this->country['250'] = 'Rwanda Republic';
		$this->country['1670'] = 'Saipan/Mariannas';
		$this->country['378'] = 'San Marino';
		$this->country['239'] = 'Sao Tome/Principe';
		$this->country['966'] = 'Saudi Arabia';
		$this->country['221'] = 'Senegal';
		$this->country['381'] = 'Serbia';
		$this->country['248'] = 'Seychelles Island';
		$this->country['232'] = 'Sierra Leone';
		$this->country['65'] = 'Singapore';
		$this->country['421'] = 'Slovakia';
		$this->country['386'] = 'Slovenia';
		$this->country['677'] = 'Solomon Islands';
		$this->country['252'] = 'Somalia Republic';
		$this->country['685'] = 'Somoa';
		$this->country['27'] = 'South Africa';
		$this->country['82'] = 'South Korea';
		$this->country['34'] = 'Spain';
		$this->country['94'] = 'Sri Lanka';
		$this->country['290'] = 'St. Helena';
		$this->country['1869'] = 'St. Kitts';
		$this->country['1758'] = 'St. Lucia';
		$this->country['508'] = 'St. Pierre';
		$this->country['1784'] = 'St. Vincent';
		$this->country['249'] = 'Sudan';
		$this->country['597'] = 'Suriname';
		$this->country['268'] = 'Swaziland';
		$this->country['46'] = 'Sweden';
		$this->country['41'] = 'Switzerland';
		$this->country['963'] = 'Syria';
		$this->country['886'] = 'Taiwan';
		$this->country['992'] = 'Tajikistan';
		$this->country['255'] = 'Tanzania';
		$this->country['66'] = 'Thailand';
		$this->country['228'] = 'Togo Republic';
		$this->country['690'] = 'Tokelau';
		$this->country['676'] = 'Tonga Islands';
		$this->country['1868'] = 'Trinidad & Tobago';
		$this->country['216'] = 'Tunisia';
		$this->country['90'] = 'Turkey';
		$this->country['993'] = 'Turkmenistan';
		$this->country['1649'] = 'Turks & Caicos Island';
		$this->country['688'] = 'Tuvalu';
		$this->country['256'] = 'Uganda';
		$this->country['380'] = 'Ukraine';
		$this->country['971'] = 'United Arab Emirates';
		$this->country['44'] = 'United Kingdom';
		$this->country['598'] = 'Uruguay';
		$this->country['1 '] = 'USA/Canada';
		$this->country['998'] = 'Uzbekistan';
		$this->country['678'] = 'Vanuatu';
		$this->country['3966'] = 'Vatican City';
		$this->country['58'] = 'Venezuela';
		$this->country['84'] = 'Vietnam';
		$this->country['1340'] = 'Virgin Islands (US)';
		$this->country['681'] = 'Wallis/Futuna Islands';
		$this->country['967'] = 'Yemen Arab Republic';
		$this->country['260'] = 'Zambia';
		$this->country['263'] = 'Zimbabwe';
	}

	function displayPhone($value, $map){
		$value = trim($value, ',');

		$mycountry = '';
		if(strpos($value, ',')){
			$mycountry = substr($value, 0, strpos($value, ','));
			$num = substr($value, strlen($mycountry) + 1);
		}elseif(strpos($value, ' ') > 1 && strpos($value, ' ') < 7){
			$mycountry = substr($value, 0, strpos($value, ' '));
			$num = substr($value, strlen($mycountry) + 1);
		}else{
			$num = $value;
			if(strpos($value, '+') === 0){
				$numChar = 4;
				while($numChar > 0){
					if(isset($this->country[substr($value, 1, $numChar)])){
						$mycountry = substr($value, 0, $numChar + 1);
						$num = substr($value, $numChar + 1);
						break;
					}
					$numChar--;
				}
			}
		}

		if(strpos($mycountry, '+') !== 0 && substr($mycountry, 0, 2) == '00'){
			$mycountry = str_replace('00', '+', $mycountry);
		}

		$style = array();

		$class = empty($this->field->fields_required) ? ' class="phoneNumberField inputbox"' : ' class="phoneNumberField inputbox required"';
		if(!empty($this->field->fields_options['size'])){
			$style[] = 'width:'.(is_numeric($this->field->fields_options['size']) ? ($this->field->fields_options['size'].'%') : $this->field->fields_options['size']);
		}else{
			$style[] = 'width:'.$this->phonewidth.'%';
		}
		$style[] = 'max-width:'.$this->phonemaxwidth.'%';
		$styleline = empty($style) ? '' : ' style=" '.implode($style, ';').'"';

		if(!isset($this->country[trim($mycountry, '+')])){
			$mycountry = '';
			$num = $value;
		}
		$readOnly = $this->readOnly ? 'readonly' : "";
		$countrycode = $this->displayCountry($mycountry, $map.'[phone_country]', 0);
		$placeholder = empty($this->placeholder) ? '' : 'placeholder="'.htmlspecialchars($this->placeholder, ENT_COMPAT, 'UTF-8').'"';

		$autofocus = '';
		if($this->autofocus) $autofocus = 'autofocus';

		$inputphone = '<input type="text" name="'.htmlspecialchars($map, ENT_COMPAT, 'UTF-8').'[phone_num]" '.$class.' '.$styleline.' '.$readOnly.' '.$autofocus.' value="'.htmlspecialchars($num, ENT_COMPAT, 'UTF-8').'" '.$placeholder.' />';



		$preventTags = '<!--googleoff: all--><noindex>';
		$preventTagsEnd = '</noindex> <!--googleon: all-->';
		$preventClass = 'class="robots-nocontent"';
		return '<div '.$preventClass.' style="white-space:nowrap">'.$preventTags.$countrycode.' '.$inputphone.$preventTagsEnd.'</div>';
	}

	function displayCountry($value, $map, $displayCountry = 1){

		static $id = 0;
		$id++;
		$divCountryCode = '';
		if($id === 1){
			$divCountryCode .= '
			<style rel="stylesheet" type="text/css">
			.acysms_divCountryCode{
				width:260px;
				height:200px;
				position:absolute;
				padding:20px !important;
				border:1px solid #aaaaaa;
				box-shadow: 2px 5px 10px #666	;
				background-color:white;
				overflow:hidden;
				z-index: 4;
				margin: 0;
				white-space:initial;
			}

			.acysms_buttonCountryCode{
				height:25px;
				padding:1px 2px 0 5px;
				margin-bottom:1px;
				cursor:pointer;
			}

			.acysms_buttonCountryCode table,tr,td {
				border:0;
			}

			.acysms_divCountryCode .acysms_countryLine{
				background-color: #ffffff;
				cursor:pointer;
				height:23px;
			}

			.acysms_divCountryCode .acysms_countryLine:hover, .acysms_divCountryCode .acysms_lineselected td{
				background-color: #ededed;
			}

			.acysms_divCountryCode .acysms_countryLine td{
				padding-right:10px;
			}

			.acysms_countryLine{
				border:0;
			}

			.acysms_module .acysms_numbers .acysms_divCountryCode .acysms_countryLine table td
			{
				border :solid 0 !important;
			}
			.acysms_module .acysms_numbers table
			{
				border :solid 0 !important;
			}

			.acysms_module .acysms_divCountryCode
			{
				padding:20px !important;
			}

			.acysms_buttonCountryCode img.flag, .acysms_divCountryCode img.flag
			{
				max-width:none;
				margin-right:5px;
				vertical-align:baseline;
			}

			.acysms_buttonCountryCode td img
			{
				margin-top:0 !important;
				margin-bottom:0 !important;
				vertical-align:baseline;
			}


			</style>

			<script language="javascript" type="text/javascript">
				var acysms_result = [];
				var acysms_index = [];
				var acysms_idbutton = 1; //1 is the minimum id

				window.addEvent("load", function(){
					allDivCountryCode = document.getElementsByClassName("acysms_divCountryCode");
					for (i=0; i<allDivCountryCode.length;i++) {
						allDivCountryCode[i].addEventListener("keydown", function(e){
							var previousSelected = document.getElementsByClassName("acysms_lineselected");
							var listenedKey = [38,9,40,13];
							var kCode = e.keyCode ? e.keyCode : e.charCode;
														if(previousSelected.length != 0) previousSelected[0].className = previousSelected[0].className.replace(" acysms_lineselected", "");

							if(kCode == 38) {
									e.preventDefault();
									acysms_stopPropagationDivCountryCode(e);
								if(acysms_index[acysms_idbutton] != 0) acysms_index[acysms_idbutton]--;
							}
							if(kCode == 9 || kCode == 40) {
									e.preventDefault();
									acysms_stopPropagationDivCountryCode(e);
								if(acysms_index[acysms_idbutton] < acysms_result[acysms_idbutton].length-1) acysms_index[acysms_idbutton]++;
								else acysms_index[acysms_idbutton] = 0;
							}
							if(kCode == 13) {
									e.preventDefault();
									acysms_stopPropagationDivCountryCode(e);
								acysms_result[acysms_idbutton][acysms_index[acysms_idbutton]].click();
							}

							if(listenedKey.indexOf(kCode)==-1) {
									var searchField = document.getElementById("acysms_searchACountry"+acysms_idbutton);
									searchField.focus();
									acysms_index[acysms_idbutton] = 0;
							}

														if(acysms_result[acysms_idbutton][acysms_index[acysms_idbutton]] != undefined) {
									acysms_result[acysms_idbutton][acysms_index[acysms_idbutton]].className += " acysms_lineselected";
									divCountryCode.scrollTo(0, acysms_result[acysms_idbutton][acysms_index[acysms_idbutton]].offsetTop);
														}
						}, false);
					}


					document.onclick = function() {
						for (i=0; i<allDivCountryCode.length;i++)
						{
						 	allDivCountryCode[i].style.display="none";
						}
					};
				});

				function acysms_loadResultArray(id) {
					if(acysms_result[id] != undefined) return;
					acysms_result[id] = [];
					divCountryCode = document.getElementById("acysms_divCountryCode"+id);
					htmlcollectionResult = divCountryCode.getElementsByClassName("acysms_countryLine");
					for(i=0; htmlcollectionResult.length > i ; i++) {
						acysms_result[id].push(htmlcollectionResult[i]);
					}
				}


				function acysms_displayDivCountryCode(id,e)
				{
					acysms_index[id] = 0;
					acysms_idbutton = id;
					acysms_loadResultArray(id);
					divCountryCode = document.getElementById("acysms_divCountryCode"+id);
					styleDivBefore = divCountryCode.style.display;
					allDivCountryCode = document.getElementsByClassName("acysms_divCountryCode");
					for (i=0; i<allDivCountryCode.length;i++)
					{
						allDivCountryCode[i].style.display="none";
					}

					if(styleDivBefore=="block")
						divCountryCode.style.display ="none"
					else
						divCountryCode.style.display ="block"

					document.getElementById("acysms_searchACountry"+id).focus();

					if(e) {e.stopPropagation();}
					else {window.event.cancelBubble = true;}
				}

				function acysms_selectACountry(countryCode,countrySelected,positionX,positionY,id,displayCountry)
				{
					document.getElementById("acysms_buttonCountryCodeImage"+id).style.backgroundPosition=positionX+"px "+positionY+"px";
					if(displayCountry == 1)
						document.getElementById("acysms_buttonCountryValue"+id).innerHTML = countrySelected;
					document.getElementById("acysms_divCountryCode"+id).style.display = "none";
					document.getElementById("acysms_valueSelectedCountryCode"+id).value = "+"+countryCode;
				}

				function acysms_searchACountry(idDivCountry)
				{
					divCountry = document.getElementById("acysms_divCountryCode"+idDivCountry);
					filter = document.getElementById("acysms_searchACountry"+idDivCountry).value.toLowerCase();
					countries = divCountry.getElementsByClassName("acysms_countryLine");
					resultAfterSearch = [];
					for(i=0;i<countries.length;i++)
					{
						countryName = countries[i].childNodes[1].innerHTML.toLowerCase();
						countryPrefix = countries[i].childNodes[2].innerHTML;
						if(countryName.indexOf(filter)>-1||countryPrefix.indexOf(filter)>-1) {
							countries[i].style.display = "table-row";
							resultAfterSearch.push(countries[i]);
						} else {
							countries[i].style.display = "none";
						}
					}
					acysms_result[idDivCountry] = resultAfterSearch;
				}

				function acysms_stopPropagationDivCountryCode(e) {
							if(e) { e.stopPropagation(); }
							else { window.event.cancelBubble = true; }
				};

			</script>
		';
		}
		$CountryCode = "";
		if($displayCountry == 1){
			if(!(empty($value))){
				$CountryCode .= '<td id="acysms_buttonCountryValue'.$id.'">'.$this->country[substr($value, 1)].'</td>';
			}else{
				$CountryCode .= '<td id="acysms_buttonCountryValue'.$id.'"></td>';
			}
		}

		if(empty($value)){
			$flagSelectedCountry = '<img id="acysms_buttonCountryCodeImage'.$id.'" class="flag" src="'.ACYSMS_IMAGES.'blank.png" style="background:url('.ACYSMS_IMAGES.'flags2.png)-160px -176px">';
		}else{
			$flagSelectedCountry = '<img id="acysms_buttonCountryCodeImage'.$id.'" class="flag" src="'.ACYSMS_IMAGES.'blank.png" style="background:url('.ACYSMS_IMAGES.'flags2.png)'.$this->flagPosition[substr($value, 1)]['x'].'px '.$this->flagPosition[substr($value, 1)]['y'].'px;">';
		}

		$divCountryCode .= '<input type="hidden" id="acysms_valueSelectedCountryCode'.$id.'" name="'.$map.'" value="'.htmlspecialchars($value, ENT_COMPAT, 'UTF-8').'">';
		$divCountryCode .= '
		<button type="button" class="acysms_buttonCountryCode" onclick="acysms_displayDivCountryCode('.$id.',event)" value="'.$id.'">
		<table>
			<tr>
				<td>
					'.$flagSelectedCountry.'
				</td>
				<td>
					<img class="arrow" src="'.ACYSMS_IMAGES.'arrow.png">
				</td>
				'.$CountryCode.'
			</tr>
		</table>
		</button>';

		$divCountryCode .= '<div onclick="acysms_stopPropagationDivCountryCode(event)" class="acysms_divCountryCode" id="acysms_divCountryCode'.$id.'" style="display:none; overflow-y:scroll !important;">';
		$divCountryCode .= '<div style="position:relative; margin-bottom:10px;"><input onkeyup="acysms_searchACountry('.$id.')" type="text" style="width:100%; margin-bottom:10px ;" placeholder="'.JText::_('SMS_SEARCH').'" id="acysms_searchACountry'.$id.'" class="acysms_searchACountry" autocomplete="off"></div>';

		$divCountryCode .= '<table cellspacing="0">';
		foreach($this->country as $code => $country){
			if(isset($this->flagPosition[$code])){
				$image = '<img class="flag" src="'.ACYSMS_IMAGES.'blank.png" style="background:url('.ACYSMS_IMAGES.'flags2.png)'.$this->flagPosition[$code]['x'].'px '.$this->flagPosition[$code]['y'].'px;">';
				$divCountryCode .= '<tr class="acysms_countryLine" onclick="acysms_selectACountry('.$code.',\''.str_replace("'", "\'", $country).'\','.$this->flagPosition[$code]['x'].','.$this->flagPosition[$code]['y'].','.$id.','.$displayCountry.')"><td>'.$image.'</td><td>'.$country.'</td><td style="color:#666">+'.$code.'</td></tr>';
			}
		}
		$divCountryCode .= '</table></div>';
		return $divCountryCode;
	}
}
