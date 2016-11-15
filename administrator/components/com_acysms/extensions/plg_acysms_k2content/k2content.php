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

class plgAcysmsk2Content extends JPlugin{

	function __construct(&$subject, $config){
		if(!file_exists(rtrim(JPATH_ADMINISTRATOR, DS).DS.'components'.DS.'com_k2')) return;
		parent::__construct($subject, $config);
	}


	function onACYSMSDisplayTagDropdown(&$dropdownContentData){
		$newContent = new stdClass();
		$dropdownContentData[] = JHTML::_('select.option', 'k2content', 'K2');
	}

	public function onACYSMSchooseArticle_k2content(&$pageInfo, &$rows, &$categoriesValues){
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
			$filters[] = "article.published = 1 AND article.`trash`=0";
		}else    $filters[] = "article.published != -2 AND article.`trash`=0";


		$whereQuery = '';
		if(!empty($filters)){
			$whereQuery = ' WHERE ('.implode(') AND (', $filters).')';
		}

		$query = 'SELECT SQL_CALC_FOUND_ROWS article.*, joomusers.*, article.title AS listingTitle, joomusers.name AS listingUsername, article.created AS listingCreatedDate, article.id AS listingId, joomusers.username,article.created_by ';
		$query .= 'FROM `#__k2_items` AS article';
		$query .= ' LEFT JOIN `#__users` AS joomusers ON article.created_by = joomusers.id';
		$query .= ' LEFT JOIN `#__k2_categories` AS categories ON article.catid = categories.id';
		if(!empty($pageInfo->filter_cat)) $filters[] = "article.catid = ".intval($pageInfo->filter_cat);

		if(!empty($filters)){
			$query .= ' WHERE ('.implode(') AND (', $filters).')';
		}

		if(!empty($pageInfo->filter->order->value)){
			$query .= ' ORDER BY '.$pageInfo->filter->order->value.' '.$pageInfo->filter->order->dir;
		}

		$db->setQuery($query, $pageInfo->limit->start, $pageInfo->limit->value);
		$rows = $db->loadObjectList();

		if(!empty($pageInfo->search)){
			$rows = ACYSMS::search($pageInfo->search, $rows);
		}

		$this->_categories($pageInfo);
		$categoriesValues = $this->catvalues;

		$db->setQuery('SELECT FOUND_ROWS()');
		$pageInfo->elements->total = $db->loadResult();
		$pageInfo->elements->page = count($rows);
	}


	public function onACYSMSchooseCategory_k2content(&$pageInfo, &$categories){
		$db = JFactory::getDBO();
		$this->_categories($pageInfo);
		$categories = $this->catListing;

		$db->setQuery('SELECT FOUND_ROWS()');
		$pageInfo->elements->total = $db->loadResult();
		$pageInfo->elements->page = count($categories);
	}

	private function _categories($pageInfo){
		$db = JFactory::getDBO();
		$db->setQuery('SELECT id, id as catid, name, name AS title, 0 as secid, parent FROM `#__k2_categories` ORDER BY `ordering` ASC');
		$categories = $db->loadObjectList();

		$this->cats = array();
		foreach($categories as $oneCat){
			$this->cats[$oneCat->parent][] = $oneCat;
		}

		$this->catvalues = array();
		$this->catvalues[] = JHTML::_('select.option', 0, JText::_('SMS_ALL'));
		$this->_handleChildrens();
	}

	private function _handleChildrens($parentId = 0, $level = 0){
		if(!empty($this->cats[$parentId])){
			foreach($this->cats[$parentId] as $cat){
				$cat->title = str_repeat(" - - ", $level).$cat->name;
				$this->catListing[$cat->id] = $cat;
				$this->catvalues[] = JHTML::_('select.option', $cat->id, str_repeat(" - - ", $level).$cat->name);
				$this->_handleChildrens($cat->id, $level + 1);
			}
		}
	}


	function onACYSMSReplaceTags(&$message, $send = true){
		$return = $this->_generateAutoMessage($message);
		$this->_replaceAuto($message);

		if($send) $this->_replaceArticles($message);

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
		$results = $helperPlugin->extractTags($message, 'k2content');

		if(empty($results)) return;

		$tagToReplace = array();
		foreach($results as $tagString => $oneTag){
			$tagToReplace[$tagString] = $this->_replaceContent($oneTag);
		}
		$message->message_body = str_replace(array_keys($tagToReplace), $tagToReplace, $message->message_body);
	}

	private function _replaceContent($tag){
		if(empty($tag->type)) return;

		$tag->type = rtrim($tag->type, ',');

		$typesToDisplay = array_flip(explode(',', $tag->type));

		$query = 'SELECT article.*,categories.name as cattitle, categories.alias as catalias, joomusers.name as authorname FROM `#__k2_items` as article ';
		$query .= ' LEFT JOIN `#__k2_categories` AS categories ON categories.id = article.catid ';
		$query .= ' LEFT JOIN `#__users` AS joomusers ON joomusers.id = article.created_by ';
		$query .= 'WHERE article.id = '.intval($tag->id).' LIMIT 1';
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

		require_once(JPATH_SITE.DS.'components'.DS.'com_k2'.DS.'helpers'.DS.'route.php');
		$link = K2HelperRoute::getItemRoute($article->id.':'.urlencode($article->alias), $article->catid);
		if(!empty($tag->itemid)) $link .= (strpos($link, '?') ? '&' : '?').'Itemid='.$tag->itemid;
		if(!empty($tag->lang)){
			$lang = substr($tag->lang, 0, strpos($tag->lang, ','));
			if(empty($lang)) $lang = $tag->lang;
			$link .= (strpos($link, '?') ? '&' : '?').'lang='.$lang;
		}
		if(!empty($tag->autologin)) $link .= (strpos($link, '?') ? '&' : '?').'user={usertag:username|urlencode}&passw={usertag:password|urlencode}';
		if(array_key_exists('link', $typesToDisplay) && !empty($link)) $typesToDisplay['link'] = ACYSMS::frontendLink($link);

		if(strpos($article->introtext, 'jseblod') !== false AND file_exists(ACYSMS_ROOT.'plugins'.DS.'content'.DS.'cckjseblod.php')){
			global $mainframe;
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
			$result = strip_tags($body);
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

		return implode("\n", $typesToDisplay);
	}

	private function _generateAutoMessage(&$message){
		$helperPlugin = ACYSMS::get('helper.plugins');
		$time = time();
		$return = new stdClass();
		$return->generateNewOne = true;
		$return->message = '';

		$helperPlugin = ACYSMS::get('helper.plugins');
		$results = $helperPlugin->extractTags($message, 'k2contentauto');
		if(empty($results)){
			$return->generateNewOne = false;
			return;
		}

		$this->tags = array();
		$db = JFactory::getDBO();

		foreach($results as $tagString => $parameter){
			if(isset($this->tags[$tagString])) continue;
			$allcats = explode('-', $parameter->id);

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

			$query = 'SELECT article.id FROM `#__k2_items` as article';
			$where = array();

			if(!empty($parameter->tags)){
				$alltags = explode(',', $parameter->tags);
				$tagcond = array();
				foreach($alltags as $onetag){
					if(empty($onetag)) continue;
					$tagcond[] = $db->Quote(trim($onetag));
				}
				if(!empty($tagcond)){
					$db->setQuery('SELECT id FROM #__k2_tags WHERE name IN ('.implode(',', $tagcond).')');
					$allTagIds = acysms_loadResultArray($db);
					if(count($allTagIds) != count($tagcond)){
						ACYSMS::enqueueMessage(count($tagcond).' tags specified but we could only load '.count($allTagIds).' of them... Please make sure the tags you specified are valid', 'error');
					}
					foreach($allTagIds as $oneTagId){
						$query .= ' JOIN `#__k2_tags_xref` as tag'.$oneTagId.' ON item.id = tag'.$oneTagId.'.itemID AND tag'.$oneTagId.'.tagID = '.intval($oneTagId);
					}
				}
			}

			JArrayHelper::toInteger($selectedArea);

			if(!empty($selectedArea)){
				$where[] = '`catid` IN ('.implode(',', $selectedArea).')';
			}
			$generatingDate = '';
			if(!empty($message->message_receiver['auto']['content']['generatingdate'])){
				$generatingDate = $message->message_receiver['auto']['content']['generatingdate'];
				$lastGeneratingDate = ACYSMS::getTime($generatingDate['year'].'-'.$generatingDate['month'].'-'.$generatingDate['day'].' '.$generatingDate['hour'].':'.$generatingDate['min']);
			}

			$trigger = $message->message_receiver['auto']['content']['trigger'];

			if(!empty($generatingDate)){
				$condition = '`publish_up` >\''.date('Y-m-d H:i:s', $lastGeneratingDate - date('Z')).'\'';
				$condition .= ' OR `created` >\''.date('Y-m-d H:i:s', $lastGeneratingDate - date('Z')).'\'';
				if($trigger == 'modification'){
					$condition .= ' OR `modified` > \''.date('Y-m-d H:i:s', $lastGeneratingDate - date('Z')).'\'';
				}
				$where[] = $condition;
			}

			if(!empty($parameter->featured)){
				$where[] = '`featured` = 1';
			}elseif(!empty($parameter->nofeatured)){
				$where[] = '`featured` = 0';
			}

			$where[] = '`publish_up` < \''.date('Y-m-d H:i:s', $time - date('Z')).'\'';
			$where[] = '`publish_down` > \''.date('Y-m-d H:i:s', $time - date('Z')).'\' OR `publish_down` = 0';
			$where[] = '`published` = 1 AND `trash`=0';

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

			if(empty($parameter->max)) $parameter->max = 100;

			$query .= ' LIMIT '.$start.(int)$parameter->max;

			$db->setQuery($query);

			$allArticles = acysms_loadResultArray($db);

			if(!empty($parameter->max) AND count($allArticles) < $parameter->max){
				$return->generateNewOne = false;
				$return->message = 'Not enough articles for the tag '.$tagString.' : '.count($allArticles).' / '.$parameter->max.' between '.ACYSMS::getDate($lastGeneratingDate).' and '.ACYSMS::getDate($time);
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
						$args[] = 'k2content:'.$oneArticleId;
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
