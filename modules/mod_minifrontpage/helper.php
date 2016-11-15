<?php

// no direct access
defined('_JEXEC') or die;

require_once JPATH_SITE.'/components/com_content/helpers/route.php';

jimport('joomla.application.component.model');

JModelLegacy::addIncludePath(JPATH_SITE.'/components/com_content/models', 'ContentModel');

abstract class modMiniFrontPageHelper
{
	public static function getList($params)
	{
		// Get the dbo
		$database = JFactory::getDbo();

		//Get the config
		$config = JFactory::getConfig();
		$user	= JFactory::getUser();
		$tzoffset = $config->get('config.offset');

		// Get an instance of the generic articles model
		$model = JModelLegacy::getInstance('Articles', 'ContentModel', array('ignore_request' => true));

		// Set application parameters in model
		$app = JFactory::getApplication();
		$appParams = $app->getParams();
		$model->setState('params', $appParams);

		// Get Module Parameters
		$number_of_article = (int) $params->get('number_of_article', 5);
		$order		    = $params->get( 'order_by', 1);
		$order_type	  	= $params->get( 'order_type', 'asc');
		$period 		= intval( $params->get( 'period', 366 ) );

		// Set the filters based on the module params
		$model->setState('list.start', (int) $params->get('number_of_skip', 0));
		$model->setState('list.limit', (int) $params->get('number_of_article', 5));
		$model->setState('filter.published', 1);

		// Access filter
		$access = !JComponentHelper::getParams('com_content')->get('show_noauth');
		$authorised = JAccess::getAuthorisedViewLevels(JFactory::getUser()->get('id'));
		$model->setState('filter.access', $access);

		// Category filter
		$model->setState('filter.category_id', $params->get('catid', array()));

		// User filter
		$userId = JFactory::getUser()->get('id');
		switch ($params->get('user_id'))
		{
			case 'by_me':
				$model->setState('filter.author_id', (int) $userId);
				break;
			case 'not_me':
				$model->setState('filter.author_id', $userId);
				$model->setState('filter.author_id.include', false);
				break;

			case '0':
				break;

			default:
				$model->setState('filter.author_id', (int) $params->get('user_id'));
				break;
		}

		// Filter by language
		$model->setState('filter.language',$app->getLanguageFilter());

		//  Featured switch
		switch ($params->get('show_featured'))
		{
			case '0':
				$model->setState('filter.featured', 'hide');
				break;
			case '1':
			default:
				$model->setState('filter.featured', 'show');
				break;
			case '2':
				$model->setState('filter.featured', 'only');
				break;
		}

		// Set ordering
		$order_map = array(
			'0' => 'a.created',
			'1' => 'a.hits',
			'2' => 'a.ordering',
			'3' => 'a.modified',
			'4' => 'RAND()',
		);

		$ordering = JArrayHelper::getValue($order_map, $params->get('order_by'), 'a.publish_up');

		//help ordering system for DESC or ASC
		switch ($params->get( 'order_type' ))
		{
			case 1:
				$dir = 'DESC';
				break;
			case 0:
			default:
				$dir = 'ASC';
				break;
		}

		$model->setState('list.ordering', $ordering);
		$model->setState('list.direction', $dir);

		//Filter and Set Period (days)
		$model->setState('filter.date_filtering', 'relative');
		$model->setState('filter.relative_date', $period);

		$items = $model->getItems();

		foreach ($items as &$item) {
			$item->slug = $item->id.':'.$item->alias;
			$item->catslug = $item->catid.':'.$item->category_alias;
			$item->categtitle = $item->category_title;

			if ($access || in_array($item->access, $authorised)) {
				// We know that user has the privilege to view the article
				$item->link = JRoute::_(ContentHelperRoute::getArticleRoute($item->slug, $item->catslug));
			} else {
				$item->link = JRoute::_('index.php?option=com_users&view=login');
			}

			$item->link = JRoute::_(ContentHelperRoute::getArticleRoute($item->slug, $item->catslug));
			$item->text = htmlspecialchars( $item->title );
			$item->id = htmlspecialchars( $item->id );
			$item->introtext = JHtml::_('content.prepare', $item->introtext);
			$item->name = htmlspecialchars( $item->author );

			//Category List and Blog
			$item->categblog = JRoute::_(ContentHelperRoute::getCategoryRoute($item->catslug));
			$item->categlist = JRoute::_('index.php?option=com_content&view=category&id='.$item->catid);

		}

		return $items;

	}//end function

	public static function showTagFP( $title = null, $link = null, $show = false, $show_link = false, $tag_open = null, $tag_close = null, $link_title = null )
	{
		$result = null;
		if ($show)
		{
			$result .= $tag_open;
			if ($show_link) {
				if ($link_title) {
					$result .= '<a href="'.$link.'" title="'.$link_title.'">'.$title.'</a>';
				} else {
					$result .= '<a href="'.$link.'">'.$title.'</a>';
				}
			} else {
				$result .= $title;
			}
			$result .= $tag_close;
		}
		return $result;
	}

	public static function fptn_thumb_size($file, $wdth, $hgth, &$image, &$xtra, $class, $aspect)
	{
		$pos = stripos($file, 'http://');

		if($class!='') $xtra .= ' class="'.$class.'"';

		if ($file =="") {
			$file = MOD_MINIFRONTPAGE_DEFAULT_IMAGE;
			$path = MOD_MINIFRONTPAGE_DEFAULT_BASE;
			$site = MOD_MINIFRONTPAGE_DEFAULT_BASEURL;
		} else {
			$path = MOD_MINIFRONTPAGE_THUMB_BASE;
			$site = MOD_MINIFRONTPAGE_THUMB_BASEURL;
		}

		// Find the extension of the file
		if ($pos === false) {
			$ext = mb_substr(strrchr(basename(JPATH_SITE.$file), '.'), 1);
		} else {
			$ext = mb_substr(strrchr(basename($file), '.'), 1);
		}

		$thumb = str_replace('.'.$ext, '_thumb.'.$ext, $file);
		$thumb = explode("/", $thumb);
		$thumb = $thumb[count($thumb)-1];
		$thumb = explode("\\", $thumb);
		$thumb = $thumb[count($thumb)-1];

		$image = '';

		$image_path = $path.TP_DS.$thumb;
		$image_site = $site."/".$thumb;

		$found = false;
		if (file_exists($image_path)) {
			$size = '';
			$wx = $hy = 0;
			if (function_exists( 'getimagesize' )) {
				$size = @getimagesize( $image_path );
				if (is_array( $size )) {
					$wx = $size[0];
					$hy = $size[1];
					$size = 'width="'.$wx.'" height="'.$hy.'"';
				}
			}

			if(!empty($size)) {
				if($wx == $wdth OR $hy == $hgth) {
					$found = true;
				}
			} else {
				$found = true;
			}

			$size = 'width="'.$wx.'" height="'.$hy.'"';
			$image= '<img src="'.$image_site.'" '.$size.$xtra.' />';
		}

		if (!$found)
		{
			$size = '';
			$wx = $hy = 0;
			if($pos === false) {
				if(file_exists(MOD_MINIFRONTPAGE_BASE.TP_DS.$file)) {
					$sFile = MOD_MINIFRONTPAGE_BASE.TP_DS.$file;
				} else {
					$sFile = MOD_MINIFRONTPAGE_DEFAULT_BASE.TP_DS.$file;
				}
			} else {
				if(file_exists($file)) {
					$sFile = $file;
				} else {
					$sFile = MOD_MINIFRONTPAGE_DEFAULT_BASE.TP_DS.MOD_MINIFRONTPAGE_DEFAULT_IMAGE;
				}
			}

			$size = @getimagesize( $sFile );

			if (is_array( $size )) {
				$wx = $size[0];
				$hy = $size[1];
			}

			self::fptn_calcsize($wx, $hy, $wdth, $hgth, $aspect);

			switch ($ext)
			{
				case 'jpg':
				case 'jpeg':
				case 'png':
					if($pos === false) {
						self::fptn_thumbIt($sFile,$image_path,$ext,$wdth,$hgth);
					} else {
						self::fptn_thumbIt($file,$image_path,$ext,$wdth,$hgth);
					}
					$size = 'width="'.$wdth.'" height="'.$hgth.'"';
					$image= '<img  src="'.$image_site.'" '.$size.$xtra.' />';
					break;
				case 'gif':
					if (function_exists("imagegif")) {
						if($pos === false) {
							self::fptn_thumbIt($sFile,$image_path,$ext,$wdth,$hgth);
						} else {
							self::fptn_thumbIt($file,$image_path,$ext,$wdth,$hgth);
						}
						$size = 'width="'.$wdth.'" height="'.$hgth.'"';
						$image= '<img src="'.$image_site.'" '.$size.$xtra.' />';
						break;
					}
				default:
					$size = 'width="'.$wdth.'" height="'.$hgth.'"';
					if($pos === false) {
						$image= '<img src="'.MOD_MINIFRONTPAGE_BASEURL."/".$file.'" '.$size.$xtra.' />';
					} else {
						$image= '<img src="'.$file.'" '.$size.$xtra.' />';
					}
					break;
			}
		}
	}

	private static function fptn_thumbIt ($file, $thumb, $ext, &$new_width, &$new_height)
	{
		$img_info = getimagesize ( $file );
		$orig_width = $img_info[0];
		$orig_height = $img_info[1];

		if($orig_width<$new_width || $orig_height<$new_height){
			$new_width = $orig_width;
			$new_height = $orig_height;
		}

		switch ($ext) {
			case 'jpg':
			case 'jpeg':
				$im  = imagecreatefromjpeg($file);
				$tim = imagecreatetruecolor ($new_width, $new_height);
				self::fptn_ImageCopyResampleBicubic($tim, $im, 0,0,0,0, $new_width, $new_height, $orig_width, $orig_height);
				imagedestroy($im);

				imagejpeg($tim, $thumb, 75);
				imagedestroy($tim);
				break;

			case 'png':
				$im  = imagecreatefrompng($file);
				$tim = imagecreatetruecolor ($new_width, $new_height);
				self::fptn_ImageCopyResampleBicubic($tim, $im, 0,0,0,0, $new_width, $new_height, $orig_width, $orig_height);
				imagedestroy($im);
				imagepng($tim, $thumb, 9);
				imagedestroy($tim);
				break;

			case 'gif':
				if (function_exists("imagegif")) {
					$im  = imagecreatefromgif($file);
					$tim = imagecreatetruecolor ($new_width, $new_height);
					self::fptn_ImageCopyResampleBicubic($tim, $im, 0,0,0,0, $new_width, $new_height, $orig_width, $orig_height);
					imagedestroy($im);
					imagegif($tim, $thumb, 75);
					imagedestroy($tim);
				}
				break;

				default:
					break;
		}
	}

	private static function fptn_calcsize($srcx, $srcy, &$forcedwidth, &$forcedheight, $aspect)
	{
		if ($forcedwidth > $srcx)  $forcedwidth = $srcx;
		if ($forcedheight > $srcy) $forcedheight = $srcy;
		if ( $forcedwidth <=0 && $forcedheight > 0) {
			$forcedwidth = round(($forcedheight * $srcx) / $srcy);
		} else if ( $forcedheight <=0 && $forcedwidth > 0) {
			$forcedheight = round(($forcedwidth * $srcy) / $srcx);
		} else if ( $forcedwidth/$srcx>1 && $forcedheight/$srcy>1) {
			//May not make an image larger!
			$forcedwidth = $srcx;
			$forcedheight = $srcy;
		}	else if ( $forcedwidth/$srcx<1 && $aspect) {
			//$forcedheight = round(($forcedheight * $forcedwidth) /$srcx);
			$forcedheight = round( ($srcy/$srcx) * $forcedwidth );
			$forcedwidth = $forcedwidth;
		}
	}

	private static function fix_tags($html)
	{
		$result = "";
		$tag_stack = array();

		// these corrections can simplify the regexp used to parse tags
		// remove whitespaces before '/' and between '/' and '>' in autoclosing tags
		$html = preg_replace("#\s*/\s*>#is","/>",$html);
		// remove whitespaces between '<', '/' and first tag letter in closing tags
		$html = preg_replace("#<\s*/\s*#is","</",$html);
		// remove whitespaces between '<' and first tag letter
		$html = preg_replace("#<\s+#is","<",$html);

		while (preg_match("#(.*?)(<([a-z\d]+)[^>]*/>|<([a-z\d]+)[^>]*(?<!/)>|</([a-z\d]+)[^>]*>)#is",$html,$matches))
		{
			$result .= $matches[1];
			//$html = mb_substr($html, strlen($matches[0]));
			$html = mb_substr($html, strlen(utf8_decode($matches[0])));

			// Closing tag
			if (isset($matches[5]))
			{
				$tag = $matches[5];

				if ($tag == $tag_stack[0]) {
					// Matched the last opening tag (normal state)
					// Just pop opening tag from the stack
					array_shift($tag_stack);
					$result .= $matches[2];
				} elseif (array_search($tag, $tag_stack)) {
					// We'll never should close 'table' tag such way, so let's check if any 'tables' found on the stack
					$no_critical_tags = !array_search('table',$tag_stack);
					if (!$no_critical_tags) {
						$no_critical_tags = (array_search('table',$tag_stack) >= array_search($tag, $tag_stack));
					};

					if ($no_critical_tags) {
						// Corresponding opening tag exist on the stack (somewhere deep)
						// Note that we can forget about 0 value returned by array_search, becaus it is handled by previous 'if'
						// Insert a set of closing tags for all non-matching tags
						$i = 0;
						while ($tag_stack[$i] != $tag) {
							$result .= "</{$tag_stack[$i]}> ";
							$i++;
						};

						// close current tag
						$result .= "</{$tag_stack[$i]}> ";
						// remove it from the stack
						array_splice($tag_stack, $i, 1);
						// if this tag is not "critical", reopen "run-off" tags
						$no_reopen_tags = array("tr","td","table","marquee","body","html");
						if (array_search($tag, $no_reopen_tags) === false) {
							while ($i > 0) {
								$i--;
								$result .= "<{$tag_stack[$i]}> ";
							};
						} else {
							array_splice($tag_stack, 0, $i);
						};
					};
				} else {
					// No such tag found on the stack, just remove it (do nothing in out case, as we have to explicitly
					// add things to result
				};
			} elseif (isset($matches[4])) {
				// Opening tag
				$tag = $matches[4];
				array_unshift($tag_stack, $tag);
				$result .= $matches[2];
			} else {
				// Autoclosing tag; do nothing specific
				$result .= $matches[2];
			};
		};

		// Close all tags left
		while (count($tag_stack) > 0) {
			$tag = array_shift($tag_stack);
			$result .= "</".$tag.">";
		}

		return $result;
	}

	public static function fptn_limittext($text,$allowed_tags,$limit)
	{
		$strip = strip_tags($text);
		$endText = (strlen($strip) > $limit) ? "&nbsp;[&nbsp;...&nbsp;]" : "";
		if ($limit == 0) $endText = "";
		$strip = mb_substr($strip, 0, $limit);
		$striptag = strip_tags($text, $allowed_tags);
		$lentag = strlen($striptag);

		$display = "";

		$x = 0;
		$ignore = true;
		for($n = 0; $n < $limit; $n++) {
			for($m = $x; $m < $lentag; $m++) {
				$x++;
				$striptag_m = (!empty($striptag[$m])) ? $striptag[$m] : null;
				if($striptag[$m] == "<") {
					$ignore = false;
				} else if($striptag[$m] == ">") {
					$ignore = true;
				}
				if($ignore == true) {
					$strip_n = (!empty($strip[$n])) ? $strip[$n] : null;
					if($strip[$n] != $striptag[$m]) {
						$display .= $striptag[$m];
					} else {
						$display .= $strip[$n];
						break;
					}
				} else {
					$display .= $striptag[$m];
				}
			}
		}
		if ($limit == 0)  return self::fix_tags ('');
		else return self::fix_tags ('<p>'.$display.$endText.'</p>');
	}

	private static function fptn_ImageCopyResampleBicubic (&$dst_img, &$src_img, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h)
	{
		if ($dst_w==$src_w && $dst_h==$src_h) {
			$dst_img = $src_img;
			return;
		}

		ImagePaletteCopy ($dst_img, $src_img);
		$rX = $src_w / $dst_w;
		$rY = $src_h / $dst_h;
		$w = 0;
		for ($y = $dst_y; $y < $dst_h; $y++) {
			$ow = $w; $w = round(($y + 1) * $rY);
			$t = 0;
			for ($x = $dst_x; $x < $dst_w; $x++) {
				$r = $g = $b = 0; $a = 0;
				$ot = $t; $t = round(($x + 1) * $rX);
				for ($u = 0; $u < ($w - $ow); $u++) {
					for ($p = 0; $p < ($t - $ot); $p++) {
						$c = ImageColorsForIndex ($src_img, ImageColorAt ($src_img, $ot + $p, $ow + $u));
						$r += $c['red'];
					$g += $c['green'];
					$b += $c['blue'];
					$a++;
				}
				}

				if(!$a) $a = 1; {
					ImageSetPixel ($dst_img, $x, $y, ImageColorClosest ($dst_img, $r / $a, $g / $a, $b / $a));
				}
			}
		}
	}

}//end of class
