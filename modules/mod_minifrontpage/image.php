<?php
/**
* @package  JB Library
* @copyright Copyright (C) 2006 - 2010 Joomla Bamboo. http://www.joomlabamboo.com  All rights reserved.
* @license  GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
*/
// Resize image options: exact, portrait, landscape, auto, crop, topleft, center
// no direct access
defined('_JEXEC') or die('Restricted access');

class mfpResizeImageHelper
{
	public static function getResizedImage($image, $newWidth, $newHeight, $option='crop', $quality='90')
	{
		// Import libraries
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');
		//windows or linux, set variables
		$full_site_dir = JURI::base();
		$site_dir = JURI::base(true).'/';
		$jpath = str_replace('\\', '/', JPATH_ROOT);
		$jpath_win = str_replace('/', '\\', JPATH_ROOT);
		$replace = array($full_site_dir, (($site_dir=='/') ? '' : $site_dir), (($jpath=='/') ? '' : $jpath), (($jpath_win=='\\') ? '' : $jpath_win));
		$image = str_replace($replace, '', $image);
		$cache_dir = $site_dir.'images/minifp/imagecache/';
		$local_image = str_replace('\\'.'\\', '\\', str_replace('//', '/', str_replace('\\', '/', $site_dir.$image)));
        if(strtolower(substr(PHP_OS, 0, 3)) === 'win') {//windows
			$site_root = $jpath_win.'\\';
			$cache_root = $site_root.'images\minifp\imagecache\\';
			$image = str_replace('\\'.'\\', '\\', str_replace('//', '/', $site_root.str_replace('/', '\\', $image)));
		} else {//linux
			$site_root = $jpath.'/';
			$cache_root = $site_root.'images/minifp/imagecache/';
			$image = str_replace('\\'.'\\', '\\', str_replace('//', '/', $site_root.str_replace('\\', '/', $image)));
		}
		$lastmod = filemtime($image);
		$image_file = JFile::getName($image);
		$extension = '.'.JFile::getExt($image_file);
		//open the image
			switch($extension)
			{
				case '.jpg':
				case '.jpeg':
					$img = imagecreatefromjpeg($image);
					break;
				case '.JPG':
				case '.JPEG':
					$img = imagecreatefromJPEG($image);
					break;
				case '.gif':
				case '.GIF':
					$img = imagecreatefromgif($image);
					break;
				case '.png':
				case '.PNG':
					$img = imagecreatefrompng($image);
					break;
				default:
					$img = false;
					break;
			}
			$extension = strtolower($extension);
		//Retrieve its width and Height
		$width  = imagesx($img);
		$height = imagesy($img);
		//name for our new image & path to save to
		$new_image = md5($image.'-'.$newWidth.'x'.$newHeight.'-'.$option.'-'.$lastmod);
		$savePath = $cache_root . $new_image . $extension;
		//if the original image is smaller than specified we just return the original
		if(($width<$newWidth)&&($height<$newHeight)){
			return $local_image;
		}
		//if we have already created the image once at the same size we just return that one
		if(file_exists($cache_root. $new_image . $extension)){
			return $cache_dir. $new_image . $extension;
		}
		//Make sure the cache exists. If it doesn't, then create it
		if (!JFolder::exists($cache_root)){
			JFolder::create($cache_root, 0755);
		}
		//set permissions if they are not correct
		if ((JFolder::exists($cache_root))&&(JPath::setPermissions($cache_root)!='0755')){
			JPath::setPermissions($cache_root, $filemode= '0755', $foldermode= '0755');
		}
		//Get optimal width and height - based on $option
		$optionArray = mfpResizeImageHelper::getDimensions($newWidth, $newHeight, $width, $height, $option);
		$optimalWidth  = $optionArray['optimalWidth'];
		$optimalHeight = $optionArray['optimalHeight'];
		//Resample - create image canvas of x, y size
		$imageResized = imagecreatetruecolor($optimalWidth, $optimalHeight);
		if(($extension=='.png')||($extension=='.gif')){
			mfpResizeImageHelper::setTransparency($imageResized,$img); 
		}
		imagecopyresampled($imageResized, $img, 0, 0, 0, 0, $optimalWidth, $optimalHeight, $width, $height);
		//if option is 'crop', then crop too
		if ($option == 'crop') {
			//Find center - this will be used for the crop
			$cropStartX = ( $optimalWidth / 2) - ( $newWidth /2 );
			$cropStartY = ( $optimalHeight/ 2) - ( $newHeight/2 );
			$crop = $imageResized;
			//Now crop from center to exact requested size
			$imageResized = imagecreatetruecolor($newWidth , $newHeight);
			if(($extension=='.png')||($extension=='.gif')){
				mfpResizeImageHelper::setTransparency($imageResized,$img); 
			}
			imagecopyresampled($imageResized, $crop , 0, 0, $cropStartX, $cropStartY, $newWidth, $newHeight , $newWidth, $newHeight);
		}
		//if option is 'topleft', then crop w/o resize
		if ($option == 'topleft') {
			$crop = $img;
			//Now crop from top left to exact requested size
			$imageResized = imagecreatetruecolor($newWidth , $newHeight);
			if(($extension=='.png')||($extension=='.gif')){
				mfpResizeImageHelper::setTransparency($imageResized,$img); 
			}
			imagecopyresampled($imageResized, $crop, 0, 0, 0, 0, $newWidth, $newHeight , $newWidth, $newHeight);
		}
		//if option is 'topleft', then crop w/o resize
		if ($option == 'center') {
			//Find center - this will be used for the crop
			$cropStartX = ( $width / 2)  - ( $optimalWidth / 2);
			$cropStartY = ( $height / 2) - ( $optimalHeight/ 2);
			$cropEndX = $cropStartX + $optimalWidth;
			$cropEndY = $cropStartY + $optimalHeight;
			$crop = $img;
			//Now crop from center to exact requested size
			$imageResized = imagecreatetruecolor($newWidth, $newHeight);
			if(($extension=='.png')||($extension=='.gif')){
				mfpResizeImageHelper::setTransparency($imageResized,$img); 
			}			
			imagecopyresampled($imageResized, $crop, 0, 0, $cropStartX, $cropStartY, $newWidth, $newHeight , $newWidth, $newHeight);
		}
		switch($extension)
		{
			case '.jpg':
			case '.jpeg':
				// Sharpen the image before we save it
				$sharpness = mfpResizeImageHelper::findSharp($width, $optimalWidth);
				$sharpenMatrix = array(
					array(-1, -2, -1),
					array(-2, $sharpness + 12, -2),
					array(-1, -2, -1)
				);
				$divisor = $sharpness;
				$offset = 0;
				if(function_exists('imageconvolution')){
					imageconvolution($imageResized, $sharpenMatrix, $divisor, $offset);
				} else {
					createImageConvolution::imageConvolution($imageResized, $sharpenMatrix, $divisor, $offset);
				}
				if (imagetypes() & IMG_JPG) {
					imagejpeg($imageResized, $savePath, $quality);
				}
				break;
			case '.gif':
				if (imagetypes() & IMG_GIF) {
					imagegif($imageResized, $savePath);
				}
				break;
			case '.png':
				//Scale quality from 0-100 to 0-9
				$scaleQuality = round(($quality/100) * 9);
				//Invert quality setting as 0 is best, not 9
				$invertScaleQuality = 9 - $scaleQuality;
				if (imagetypes() & IMG_PNG) {
					 imagepng($imageResized, $savePath, $invertScaleQuality);
				}
				break;
			default:
				break;
		}
		imagedestroy($imageResized);
		return $cache_dir . $new_image . $extension;			
	}
	private static function getDimensions($newWidth, $newHeight, $width, $height, $option="crop")
	{
	   switch ($option)
		{
			case 'exact':
				$optimalWidth = $newWidth;
				$optimalHeight= $newHeight;
				break;
			case 'portrait':
				$optimalWidth = mfpResizeImageHelper::getSizeByFixedHeight($newHeight, $width, $height);
				$optimalHeight= $newHeight;
				break;
			case 'landscape':
				$optimalWidth = $newWidth;
				$optimalHeight= mfpResizeImageHelper::getSizeByFixedWidth($newWidth, $width, $height);
				break;
			case 'auto':
				$optionArray = mfpResizeImageHelper::getSizeByAuto($newWidth, $newHeight, $width, $height);
				$optimalWidth = $optionArray['optimalWidth'];
				$optimalHeight = $optionArray['optimalHeight'];
				break;
			case 'crop':
				$optionArray = mfpResizeImageHelper::getOptimalCrop($newWidth, $newHeight, $width, $height);
				$optimalWidth = $optionArray['optimalWidth'];
				$optimalHeight = $optionArray['optimalHeight'];
				break;
			case 'topleft':
				$optimalWidth = $newWidth;
				$optimalHeight= $newHeight;
				break;		
			case 'center':
				$optimalWidth = $newWidth;
				$optimalHeight= $newHeight;
				break;	
			default:
				$optionArray = mfpResizeImageHelper::getOptimalCrop($newWidth, $newHeight, $width, $height);
				$optimalWidth = $optionArray['optimalWidth'];
				$optimalHeight = $optionArray['optimalHeight'];
				break;		
			}
		return array('optimalWidth' => $optimalWidth, 'optimalHeight' => $optimalHeight);
	}
	private static function getSizeByFixedHeight($newHeight, $width, $height)
	{
		$ratio = $width / $height;
		$newWidth = $newHeight * $ratio;
		return $newWidth;
	}
	private static function getSizeByFixedWidth($newWidth, $width, $height)
	{
		$ratio = $height / $width;
		$newHeight = $newWidth * $ratio;
		return $newHeight;
	}
	private static function getSizeByAuto($newWidth, $newHeight, $width, $height)
	{
		if ($height < $width)
		//Image to be resized is wider (landscape)
		{
			$optimalWidth = $newWidth;
			$optimalHeight= mfpResizeImageHelper::getSizeByFixedWidth($newWidth, $width, $height);
		}
		elseif ($height > $width)
		//Image to be resized is taller (portrait)
		{
			$optimalWidth = mfpResizeImageHelper::getSizeByFixedHeight($newHeight, $width, $height);
			$optimalHeight= $newHeight;
		}
		else
		//Image to be resizerd is a square
		{
			if ($newHeight < $newWidth) {
				$optimalWidth = $newWidth;
				$optimalHeight= mfpResizeImageHelper::getSizeByFixedWidth($newWidth, $width, $height);
			} else if ($newHeight > $newWidth) {
				$optimalWidth = mfpResizeImageHelper::getSizeByFixedHeight($newHeight, $width, $height);
				$optimalHeight= $newHeight;
			} else {
				//Sqaure being resized to a square
				$optimalWidth = $newWidth;
				$optimalHeight= $newHeight;
			}
		}
		return array('optimalWidth' => $optimalWidth, 'optimalHeight' => $optimalHeight);
	}
	private static function getOptimalCrop($newWidth, $newHeight, $width, $height)
	{
		$heightRatio = $height / $newHeight;
		$widthRatio  = $width /  $newWidth;
		if ($heightRatio < $widthRatio) {
			$optimalRatio = $heightRatio;
		} else {
			$optimalRatio = $widthRatio;
		}
		$optimalHeight = $height / $optimalRatio;
		$optimalWidth  = $width  / $optimalRatio;
		return array('optimalWidth' => $optimalWidth, 'optimalHeight' => $optimalHeight);
	}
	
	private static function findSharp($orig, $final) // function from Ryan Rud (http://adryrun.com)
	{
		$final	= $final * (750.0 / $orig);
		$a		= 52;
		$b		= -0.27810650887573124;
		$c		= .00047337278106508946;
		
		$result = $a + $b * $final + $c * $final * $final;
		
		return max(round($result), 0);
	} // findSharp()
	private static function setTransparency($new_image,$image_source)
    {
            $transparencyIndex = imagecolortransparent($image_source);
            $transparencyColor = array('red' => 255, 'green' => 255, 'blue' => 255);
            if ($transparencyIndex >= 0) {
                $transparencyColor    = imagecolorsforindex($image_source, $transparencyIndex);   
            }
            $transparencyIndex  = imagecolorallocate($new_image, $transparencyColor['red'], $transparencyColor['green'], $transparencyColor['blue']);
            imagefill($new_image, 0, 0, $transparencyIndex);
             imagecolortransparent($new_image, $transparencyIndex);
    } 
}

if(!function_exists('imageconvolution')){
	class createImageConvolution
	{
		function ImageConvolution($src, $filter, $filter_div, $offset){
			if ($src==NULL) {
				return 0;
			}
			$sx = imagesx($src);
			$sy = imagesy($src);
			$srcback = ImageCreateTrueColor ($sx, $sy);
			ImageAlphaBlending($srcback, false);
			ImageAlphaBlending($src, false);
			ImageCopy($srcback, $src,0,0,0,0,$sx,$sy);		 
			if($srcback==NULL){
				return 0;
			}
			for ($y=0; $y<$sy; ++$y){
				for($x=0; $x<$sx; ++$x){
					$new_r = $new_g = $new_b = 0;
					$alpha = imagecolorat($srcback, @$pxl[0], @$pxl[1]);
					$new_a = ($alpha >> 24);
		 
					for ($j=0; $j<3; ++$j) {
						$yv = min(max($y - 1 + $j, 0), $sy - 1);
						for ($i=0; $i<3; ++$i) {
								$pxl = array(min(max($x - 1 + $i, 0), $sx - 1), $yv);
							$rgb = imagecolorat($srcback, $pxl[0], $pxl[1]);
							$new_r += (($rgb >> 16) & 0xFF) * $filter[$j][$i];
							$new_g += (($rgb >> 8) & 0xFF) * $filter[$j][$i];
							$new_b += ($rgb & 0xFF) * $filter[$j][$i];
							$new_a += ((0x7F000000 & $rgb) >> 24) * $filter[$j][$i];
						}
					}
					$new_r = ($new_r/$filter_div)+$offset;
					$new_g = ($new_g/$filter_div)+$offset;
					$new_b = ($new_b/$filter_div)+$offset;
					$new_a = ($new_a/$filter_div)+$offset;
					$new_r = ($new_r > 255)? 255 : (($new_r < 0)? 0:$new_r);
					$new_g = ($new_g > 255)? 255 : (($new_g < 0)? 0:$new_g);
					$new_b = ($new_b > 255)? 255 : (($new_b < 0)? 0:$new_b);
					$new_a = ($new_a > 127)? 127 : (($new_a < 0)? 0:$new_a);
					$new_pxl = ImageColorAllocateAlpha($src, (int)$new_r, (int)$new_g, (int)$new_b, $new_a);
					if ($new_pxl == -1) {
						$new_pxl = ImageColorClosestAlpha($src, (int)$new_r, (int)$new_g, (int)$new_b, $new_a);
					}
					if (($y >= 0) && ($y < $sy)) {
						imagesetpixel($src, $x, $y, $new_pxl);
					}
				}
			}
			imagedestroy($srcback);
			return 1;
		}
	}
}
?>