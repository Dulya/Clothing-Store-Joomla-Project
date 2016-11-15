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

class plgAcysmsJoomlaContent extends JPlugin{

	function __construct(&$subject, $config){
		parent::__construct($subject, $config);
	}


	function onACYSMSDisplayTagDropdown(&$dropdownContentData){
		$dropdownContentData[] = JHTML::_('select.option', 'joomlacontent', JText::_('SMS_JOOMLA'));
	}

	public function onACYSMSchooseArticle_joomlacontent(&$pageInfo, &$rows, &$categoriesValues){
		$db = JFactory::getDBO();
		$searchFields = array('article.id', 'article.title', 'article.alias', 'article.created_by', 'joomuser.name', 'joomuser.username');
		if(!empty($pageInfo->search)){
			$searchVal = '\'%'.acysms_getEscaped($pageInfo->search, true).'%\'';
			$filters[] = implode(" LIKE $searchVal OR ", $searchFields)." LIKE $searchVal";
		}

		if(!empty($pageInfo->filter_cat)){
			$filters[] = "article.catid = ".intval($pageInfo->filter_cat);
		}


		if($this->params->get('showOnlyPublished', 0)){
			$filters[] = "article.state = 1";
		}else    $filters[] = "article.state != -2";


		$whereQuery = '';
		if(!empty($filters)){
			$whereQuery = ' WHERE ('.implode(') AND (', $filters).')';
		}

		$query = 'SELECT SQL_CALC_FOUND_ROWS article.*, article.title AS listingTitle, joomuser.name AS listingUsername, article.created AS listingCreatedDate, article.id AS listingId, joomuser.username,article.created_by FROM '.ACYSMS::table('content', false).' as article';
		$query .= ' LEFT JOIN `#__users` AS joomuser ON joomuser.id = article.created_by';
		if(!empty($whereQuery)) $query .= $whereQuery;
		if(!empty($pageInfo->filter->order->value)){
			$query .= ' ORDER BY '.$pageInfo->filter->order->value.' '.$pageInfo->filter->order->dir;
		}

		$db->setQuery($query, $pageInfo->limit->start, $pageInfo->limit->value);
		$rows = $db->loadObjectList();

		if(!empty($pageInfo->search)){
			$rows = ACYSMS::search($pageInfo->search, $rows);
		}

		$db->setQuery('SELECT FOUND_ROWS()');
		$pageInfo->elements->total = $db->loadResult();
		$pageInfo->elements->page = count($rows);


		if(!ACYSMS_J16){
			$query = 'SELECT categories.id, categories.id as catid, categories.title as category, sections.title as section, sections.id as secid from #__categories as a ';
			$query .= 'INNER JOIN #__sections as sections on categories.section = sections.id ORDER BY sections.ordering,categories.ordering';

			$db->setQuery($query);
			$categories = $db->loadObjectList('id');
			$categoriesValues = array();
			$categoriesValues[] = JHTML::_('select.option', '', JText::_('SMS_ALL'));
			$currentSec = '';
			foreach($categories as $catid => $oneCategorie){
				if($currentSec != $oneCategorie->section){
					if(!empty($currentSec)) $this->values[] = JHTML::_('select.option', '</OPTGROUP>');
					$categoriesValues[] = JHTML::_('select.option', '<OPTGROUP>', $oneCategorie->section);
					$currentSec = $oneCategorie->section;
				}
				$categoriesValues[] = JHTML::_('select.option', $catid, $oneCategorie->category);
			}
		}else{
			$query = "SELECT * from #__categories WHERE `extension` = 'com_content' ORDER BY lft ASC";

			$db->setQuery($query);
			$categories = $db->loadObjectList('id');
			$categoriesValues = array();
			$categoriesValues[] = JHTML::_('select.option', '', JText::_('SMS_ALL'));
			foreach($categories as $catid => $oneCategorie){
				$categories[$catid]->title = str_repeat('- - ', $categories[$catid]->level).$categories[$catid]->title;
				$categoriesValues[] = JHTML::_('select.option', $catid, $categories[$catid]->title);
			}
		}
	}


	public function onACYSMSchooseCategory_joomlacontent(&$pageInfo, &$content){
		$db = JFactory::getDBO();
		if(!ACYSMS_J16){
			$query = 'SELECT categories.id, categories.id AS catid, categories.title AS category, sections.title AS section, sections.id AS secid from #__categories AS categories ';
			$query .= 'INNER JOIN #__sections AS sections on categories.section = sections.id ORDER BY sections.ordering,categories.ordering';

			$db->setQuery($query);
			$content = $db->loadObjectList();

			$currentSection = '';
			$contentListing = array();
			foreach($content as $catid => $oneCategorie){
				$newContent = new stdClass();
				if($currentSection != $oneCategorie->section){
					$newContent->secid = $oneCategorie->secid;
					$newContent->catid = 0;
					$newContent->title = $oneCategorie->section;
					$contentListing[] = $newContent;
					$currentSection = $oneCategorie->section;
					continue;
				}
				$newContent->secid = $oneCategorie->secid;
				$newContent->catid = $oneCategorie->catid;
				$newContent->title = '- - '.$oneCategorie->category;
				$contentListing[] = $newContent;
			}
		}else{
			$query = "SELECT * from #__categories WHERE `extension` = 'com_content' ORDER BY lft ASC";
			$db->setQuery($query);
			$content = $db->loadObjectList('id');

			foreach($content as $catid => $oneCategorie){
				$newContent = new stdClass();
				$newContent->catid = $oneCategorie->id;
				$newContent->secid = 0;
				$newContent->title = str_repeat('- - ', $content[$catid]->level).$content[$catid]->title;
				$contentListing[] = $newContent;
			}
		}

		$content = $contentListing;
		$db->setQuery('SELECT FOUND_ROWS()');
		$pageInfo->elements->total = $db->loadResult();
		$pageInfo->elements->page = count($content);
	}


	function onACYSMSReplaceTags(&$message, $send = true){
		$return = $this->_generateAutoMessage($message);
		$this->_replaceAuto($message);
		$this->_replaceArticles($message);
		return $return;
	}

	private function _replaceAuto(&$message){
		if(empty($this->tags)) return;
		$message->message_body = str_replace(array_keys($this->tags), $this->tags, $message->message_body);
		foreach($this->tags as $tag => $result){
			$message->message_body = str_replace($tag, $result, $message->message_body);
		}
	}


	private function _replaceArticles(&$message){
		$helperPlugin = ACYSMS::get('helper.plugins');
		$results = $helperPlugin->extractTags($message, 'joomlacontent');
		if(empty($results)) return;
		$tagToReplace = array();
		foreach($results as $tagString => $oneTag){
			if($this->_replaceContent($oneTag) === false){
				break;
			}
			$tagToReplace[$tagString] = $this->_replaceContent($oneTag);
		}
		$message->message_body = str_replace(array_keys($tagToReplace), $tagToReplace, $message->message_body);
	}

	private function _replaceContent($tag){
		if(empty($tag->type)) return;
		$tag->type = rtrim($tag->type, ',');
		$typesToDisplay = array();
		$typesToDisplay = array_flip(explode(',', $tag->type));

		if(!ACYSMS_J16){
			$query = 'SELECT article.*,joomusers.name as authorname, categories.alias as catalias, categories.title as cattitle, categories.alias as secalias, categories.title as sectitle FROM '.ACYSMS::table('content', false).' as article ';
			$query .= 'LEFT JOIN '.ACYSMS::table('users', false).' as joomusers ON article.created_by = joomusers.id ';
			$query .= ' LEFT JOIN '.ACYSMS::table('categories', false).' AS categories ON categories.id = article.catid ';
			$query .= ' LEFT JOIN '.ACYSMS::table('sections', false).' AS sections ON sections.id = article.sectionid ';
			$query .= 'WHERE article.id = '.intval($tag->id).' LIMIT 1';
		}else{
			$query = 'SELECT article.*,joomusers.name as authorname, categories.alias as catalias, categories.title as cattitle FROM '.ACYSMS::table('content', false).' as article ';
			$query .= 'LEFT JOIN '.ACYSMS::table('users', false).' as joomusers ON article.created_by = joomusers.id ';
			$query .= ' LEFT JOIN '.ACYSMS::table('categories', false).' AS categories ON categories.id = article.catid ';
			$query .= 'WHERE article.id = '.intval($tag->id).' LIMIT 1';
		}
		$db = JFactory::getDBO();
		$db->setQuery($query);
		$article = $db->loadObject();


		if(empty($article)){
			$app = JFactory::getApplication();
			if($app->isAdmin()){
				ACYSMS::enqueueMessage('The article "'.intval($tag->id).'" could not be loaded', 'notice');
			}
			return;
		}

		if(!empty($tag->lang)){
			$langid = (int)substr($tag->lang, strpos($tag->lang, ',') + 1);
			if(!empty($langid) && file_exists(JPATH_SITE.DS.'components'.DS.'com_joomfish'.DS.'helpers'.DS.'defines.php')){
				$query = "SELECT reference_field, value FROM ".(ACYSMS_J16 ? '`#__falang_content`' : '`#__jf_content`')." WHERE `published` = 1 AND `reference_table` = 'content' AND `language_id` = $langid AND `reference_id` = ".intval($tag->id);
				$db->setQuery($query);
				$translations = $db->loadObjectList();
				if(!empty($translations)){
					foreach($translations as $oneTranslation){
						if(!empty($oneTranslation->value)){
							$translatedfield = $oneTranslation->reference_field;
							$article->$translatedfield = $oneTranslation->value;
						}
					}
				}
			}
		}

		$completeId = $article->id;
		$completeCat = $article->catid;

		if(!empty($article->alias)) $completeId .= ':'.$article->alias;
		if(!empty($article->catalias)) $completeCat .= ':'.$article->catalias;


		require_once JPATH_SITE.DS.'components'.DS.'com_content'.DS.'helpers'.DS.'route.php';

		if(empty($tag->itemid)){
			if(!ACYSMS_J16){
				$completeSec = $article->sectionid;
				if(!empty($article->secalias)) $completeSec .= ':'.$article->secalias;
				if($this->params->get('integration') == 'flexicontent' && class_exists('FlexicontentHelperRoute')){
					$link = FlexicontentHelperRoute::getItemRoute($completeId, $completeCat, $completeSec);
				}else{
					$link = ContentHelperRoute::getArticleRoute($completeId, $completeCat, $completeSec);
				}
			}else{
				if($this->params->get('integration') == 'flexicontent' && class_exists('FlexicontentHelperRoute')){
					$link = FlexicontentHelperRoute::getItemRoute($completeId, $completeCat);
				}else{
					$link = ContentHelperRoute::getArticleRoute($completeId, $completeCat);
				}
			}
		}else{
			$link = 'index.php?option=com_content&view=article&id='.$completeId.'&catid='.$completeCat;
		}

		if($this->params->get('integration') == 'flexicontent' && !class_exists('FlexicontentHelperRoute')){
			$link = 'index.php?option=com_flexicontent&view=items&id='.$completeId;
		}elseif($this->params->get('integration') == 'jaggyblog'){
			$link = 'index.php?option=com_jaggyblog&task=viewpost&id='.$completeId;
		}
		if(!empty($tag->itemid)) $link .= '&Itemid='.$tag->itemid;

		if(!empty($tag->lang)) $link .= (strpos($link, '?') ? '&' : '?').'lang='.substr($tag->lang, 0, strpos($tag->lang, ','));
		if(!empty($tag->autologin)) $link .= (strpos($link, '?') ? '&' : '?').'user={usertag:username|urlencode}&passw={usertag:password|urlencode}';

		if(empty($tag->lang) && !empty($article->language) && $article->language != '*'){
			if(!isset($this->langcodes[$article->language])){
				$db->setQuery('SELECT sef FROM #__languages WHERE lang_code = '.$db->Quote($article->language).' ORDER BY `published` DESC LIMIT 1');
				$this->langcodes[$article->language] = $db->loadResult();
				if(empty($this->langcodes[$article->language])) $this->langcodes[$article->language] = $article->language;
				$link .= (strpos($link, '?') ? '&' : '?').'lang='.$this->langcodes[$article->language];
			}
		}
		if(array_key_exists('link', $typesToDisplay) && !empty($link)) $typesToDisplay['link'] = ACYSMS::frontendLink($link);

		if(strpos($article->introtext, 'jseblod') !== false AND file_exists(ACYSMS_ROOT.'plugins'.DS.'content'.DS.'cckjseblod.php')){
			include_once(ACYSMS_ROOT.'plugins'.DS.'content'.DS.'cckjseblod.php');
			if(function_exists('plgContentCCKjSeblod')){
				$paramsContent = JComponentHelper::getParams('com_content');
				$article->text = $article->introtext.$article->fulltext;
				plgContentCCKjSeblod($article, $paramsContent);
				$article->introtext = $article->text;
				$article->fulltext = '';
			}
		}


		if(!empty($tag->wrap)){
			$tagToKeep = '<br>';
			$newtext = strip_tags($article->introtext, $tagToKeep);
			$numChar = strlen($newtext);
			if($numChar > $tag->wrap){
				$stop = strlen($newtext);
				for($i = intval($tag->wrap); $i < $numChar; $i++){
					if($newtext[$i] == " "){
						$stop = $i;
						break;
					}
				}
				$body = substr($newtext, 0, $stop).'...';
			}else $body = $newtext;
		}else $body = $article->introtext;

		if(!empty($tag->maxchar) && strlen(strip_tags($body)) > $tag->maxchar){
			for($i = $tag->maxchar; $i > 0; $i--){
				if($body[$i] == ' ') break;
			}
			if(!empty($i)) $body = substr($body, 0, $i).@$tag->textafter;
		}

		if(array_key_exists('body', $typesToDisplay) && !empty($body)) $typesToDisplay['body'] = strip_tags($body);
		if(!empty($tag->created)){
			$dateFormat = empty($tag->dateformat) ? JText::_('DATE_FORMAT_LC2') : $tag->dateformat;
			$article->title .= $article->title.JHTML::_('date', $article->created, $dateFormat);
		}
		if(array_key_exists('title', $typesToDisplay) && !empty($article->title)) $typesToDisplay['title'] = strip_tags($article->title);

		$config = ACYSMS::config();
		$useShortURL = $config->get('use_short_url', 0);
		$apiKeyShortUrl = $config->get('api_key_short_url', '');

		if(!empty($useShortURL) && !empty($apiKeyShortUrl)) $typesToDisplay['link'] = $this->_shortenURL($typesToDisplay['link'], $apiKeyShortUrl);

		if($typesToDisplay['link'] === false) return false;

		return implode("\n", $typesToDisplay);
	}


	private function _shortenURL($url, $apiKey){

		$ch = curl_init();

		if($ch === false){
			ACYSMS::enqueueMessage("curl_init() function has failed", "error");
			return false;
		}

		curl_setopt($ch, CURLOPT_URL, "https://www.googleapis.com/urlshortener/v1/url?key=".$apiKey);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, '{"longUrl": "'.$url.'"}');
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$res = curl_exec($ch);

		if(empty($res)){
			ACYSMS::enqueueMessage("An error appeared with the connection to the Google API", "error");
			return false;
		}

		curl_close($ch);

		$arrayJson = json_decode($res, true);

		if(array_key_exists("error", $arrayJson)){
			$errorCode = $arrayJson['error']['code'];
			$errorMessage = $arrayJson['error']['message'];
			if($errorCode == 400){
				$error = JText::_('SMS_WRONG_API_KEY');
				$error .= ' <a href="http://www.acyba.com/acysms/528-how-to-configure-the-shortened-urls-option.html" target="_blank">'.JText::_('SMS_CLICK_HERE').'</a>';
			}else $error = "Error ".$errorCode." : ".$errorMessage;
			ACYSMS::enqueueMessage($error, 'error');
			return false;
		}

		$shortUrl = $arrayJson['id'];

		return $shortUrl;
	}

	private function _generateAutoMessage(&$message){
		$helperPlugin = ACYSMS::get('helper.plugins');
		$time = time();
		$return = new stdClass();
		$return->generateNewOne = true;
		$return->message = '';

		$results = $helperPlugin->extractTags($message, 'joomlacontentauto');
		if(empty($results)){
			$return->generateNewOne = false;
			return;
		}
		$this->tags = array();
		$db = JFactory::getDBO();

		foreach($results as $tagString => $parameter){
			if(isset($this->tags[$tagString])) continue;


			$selectedArea = array();
			if(!empty($parameter->sec)){
				$allsects = explode('-', $parameter->sec);
				foreach($allsects as $onesect){
					if(empty($onesect)) continue;
					$selectedArea[] = (int)$onesect;
				}
			}
			if(!empty($parameter->cat)){
				$allcats = explode('-', $parameter->cat);
				foreach($allcats as $onecat){
					if(empty($onecat)) continue;
					$selectedArea[] = (int)$onecat;
				}
			}
			$query = 'SELECT article.id FROM `#__content` as article ';
			$where = array();

			if(!empty($parameter->featured)){
				if(ACYSMS_J16){
					$where[] = 'article.featured = 1';
				}else{
					$query .= 'JOIN `#__content_frontpage` as b ON article.id = b.content_id ';
					$where[] = 'b.content_id IS NOT NULL';
				}
			}

			if(!empty($parameter->nofeatured)){
				if(ACYSMS_J16){
					$where[] = 'article.featured = 0';
				}else{
					$query .= 'LEFT JOIN `#__content_frontpage` as b ON article.id = b.content_id ';
					$where[] = 'b.content_id IS NULL';
				}
			}
			JArrayHelper::toInteger($selectedArea);

			if(!empty($selectedArea)){
				if(!ACYSMS_J16){
					$where[] = implode(' OR ', $selectedArea);
				}else{
					$where[] = '`catid` IN ('.implode(',', $selectedArea).')';
				}
			}

			if(!empty($parameter->excludedcats)){
				$excludedCats = explode('-', $parameter->excludedcats);
				JArrayHelper::toInteger($excludedCats);
				$where[] = '`catid` NOT IN ("'.implode('","', $excludedCats).'")';
			}
			if(!empty($message->message_receiver['auto']['content']['generatingdate'])){
				$generatingDate = $message->message_receiver['auto']['content']['generatingdate'];
				$lastGeneratingDate = ACYSMS::getTime($generatingDate['year'].'-'.$generatingDate['month'].'-'.$generatingDate['day'].' '.$generatingDate['hour'].':'.$generatingDate['min']);
			}
			if(!empty($lastGeneratingDate)){
				$condition = '(`publish_up` > "'.date('Y-m-d H:i:s', $lastGeneratingDate - date('Z')).'" AND `publish_up` <= "'.date('Y-m-d H:i:s', $time - date('Z')).'")';
				$condition .= ' OR (`created` > "'.date('Y-m-d H:i:s', $lastGeneratingDate - date('Z')).'" AND `created` < "'.date('Y-m-d H:i:s', $time - date('Z')).'")';
				$where[] = $condition;
			}

			if(!empty($parameter->maxcreated)){
				$date = strtotime($parameter->maxcreated);
				if(empty($date)){
					ACYSMS::display('Wrong date format ('.$parameter->maxcreated.' in '.$tagString.'), please use YYYY-MM-DD', 'warning');
				}
				$where[] = '`created` < '.$db->Quote(date('Y-m-d H:i:s', $date));
			}

			if(!empty($parameter->mincreated)){
				$date = strtotime($parameter->mincreated);
				if(empty($date)){
					ACYSMS::display('Wrong date format ('.$parameter->mincreated.' in '.$tagString.'), please use YYYY-MM-DD', 'warning');
				}
				$where[] = '`created` > '.$db->Quote(date('Y-m-d H:i:s', $date));
			}


			if(!empty($parameter->meta)){
				$allMetaTags = explode(',', $parameter->meta);
				$metaWhere = array();
				foreach($allMetaTags as $oneMeta){
					if(empty($oneMeta)) continue;
					$metaWhere[] = "`metakey` LIKE '%".acysms_getEscaped($oneMeta, true)."%'";
				}
				if(!empty($metaWhere)) $where[] = implode(' OR ', $metaWhere);
			}

			$where[] = '`publish_up` <= \''.date('Y-m-d H:i:s', $time - date('Z')).'\'';
			$where[] = '`publish_down` > \''.date('Y-m-d H:i:s', $time - date('Z')).'\' OR `publish_down` = 0';
			$where[] = 'state = 1';
			if(!ACYSMS_J16){
				if(isset($parameter->access)){
					$where[] = 'access <= '.intval($parameter->access);
				}else{
					if($this->params->get('contentaccess', 'registered') == 'registered'){
						$where[] = 'access <= 1';
					}elseif($this->params->get('contentaccess', 'registered') == 'public') $where[] = 'access = 0';
				}
			}elseif(isset($parameter->access)){
				$where[] = 'access = '.intval($parameter->access);
			}

			if(!empty($parameter->language)){
				$allLanguages = explode(',', $parameter->language);
				$langWhere = 'language IN (';
				foreach($allLanguages as $oneLanguage){
					$langWhere .= $db->Quote(trim($oneLanguage)).',';
				}
				$where[] = trim($langWhere, ',').')';
			}
			$query .= ' WHERE ('.implode(') AND (', $where).')';
			if(!empty($parameter->order)){
				if($parameter->order == 'rand'){
					$query .= ' ORDER BY rand()';
				}else{
					$ordering = explode(',', $parameter->order);
					$query .= ' ORDER BY `'.ACYSMS::secureField($ordering[0]).'` '.ACYSMS::secureField($ordering[1]).' , article.`id` DESC';
				}
			}

			$start = '';
			if(!empty($parameter->start)) $start = intval($parameter->start).',';

			if(empty($parameter->min)) $parameter->min = 1;
			if(empty($parameter->max)) $parameter->max = 100;

			$query .= ' LIMIT '.$start.(int)$parameter->max;
			$db->setQuery($query);
			$allArticles = acysms_loadResultArray($db);

			if((!empty($parameter->min) && count($allArticles) < $parameter->min)){
				$return->generateNewOne = false;
				$return->message = 'Not enough articles for the tag '.$tagString.' : '.count($allArticles).' / '.$parameter->max.' between '.ACYSMS::getDate().' and '.ACYSMS::getDate($time);
				return $return;
			}

			$stringTag = '';
			if(!empty($allArticles)){
				if(file_exists(ACYSMS_MEDIA.'plugins'.DS.'autocontent.php')){
					ob_start();
					require(ACYSMS_MEDIA.'plugins'.DS.'autocontent.php');
					$stringTag = ob_get_clean();
				}else{
					$arrayElements = array();
					$numArticle = 1;
					foreach($allArticles as $oneArticleId){
						$args = array();
						$args[] = 'joomlacontent:'.$oneArticleId;
						$args[] = 'num:'.$numArticle++;
						if(!empty($parameter->type)) $args[] = 'type:'.$parameter->type;
						if(!empty($parameter->link)) $args[] = 'link';
						if(!empty($parameter->autologin)) $args[] = 'autologin';
						if(!empty($parameter->cattitle)) $args[] = 'cattitle';
						if(!empty($parameter->lang)) $args[] = 'lang:'.$parameter->lang;
						if(!empty($parameter->notitle)) $args[] = 'notitle';
						if(!empty($parameter->created)) $args[] = 'created';
						if(!empty($parameter->itemid)) $args[] = 'itemid:'.$parameter->itemid;
						if(!empty($parameter->wrap)) $args[] = 'wrap:'.$parameter->wrap;
						if(!empty($parameter->readmore)) $args[] = 'readmore:'.$parameter->readmore;
						if(!empty($parameter->dateformat)) $args[] = 'dateformat:'.$parameter->dateformat;
						if(!empty($parameter->maxchar)) $args[] = 'maxchar:'.$parameter->maxchar;
						$arrayElements[] = '{'.implode('|', $args).'}';
					}
					$stringTag = implode(" ", $arrayElements);
				}
			}
			$this->tags[$tagString] = $stringTag;
		}
		return $return;
	}
}//endclass
