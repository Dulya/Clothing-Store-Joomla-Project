<?php
//SLIDESHOW

$Image[]= $this->params->get( '!', "" );
$Slidetext[]= $this->params->get( '!', "" );
$Link[]= $this->params->get( '!', "" );
for ($j=1; $j<=10; $j++){
$Image[$j]      = $this->params->get ("image".$j,"" );
$Slidetext[$j]    = $this->params->get ("slidetext".$j , "" );
$Link[$j]       = $this->params->get ("link".$j , "" );
}


$st_image1 = ''.$slideshow.'header1.jpg';
$st_image2 = ''.$slideshow.'header2.jpg';
$info1 = $this->params->get('slidetext1');
$info2 = $this->params->get('slidetext2');
$img1 = $this->params->get('image1');
$img2 = $this->params->get('image2');
?>



<?php if ($img1 == null) : ?>

<div class="slideshow">
<div id="wowslider-container1">
<div class="ws_images">
<ul>
<li>
<img src="<?php echo $st_image1; ?>" title="<?php echo $info1 ?>"/> 					   
</li>
<li>
<img src="<?php echo $st_image2; ?>" title="<?php echo $info2 ?>"/> 					   
</li>
</ul>
</div>
</div>
<div class="clear"></div>
</div>

<script type="text/javascript" src="<?php echo $this->baseurl ?>/templates/<?php echo $this->template; ?>/images/slideshow/effects/wowslider.js"></script>
<script type="text/javascript" src="<?php echo $this->baseurl ?>/templates/<?php echo $this->template; ?>/images/slideshow/effects/fade/script.js"></script>

<script>
jQuery("#wowslider-container1").wowSlider
({
effect:"fade",
prev:"",
next:"",
duration:20*100,
delay:20*100,
width:960,
height:100,
autoPlay:true,
playPause:false,
stopOnHover:false,
bullets:true,
caption:true,
captionEffect:"slide",
controls:true,
onBeforeStep:0,
images:0
});
</script>

<?php elseif (is_array($menuid) && is_object($menu) && isset($menu->getActive()->id) && in_array($menu->getActive()->id, $menuid, true)) : ?> 

<div class="slideshow">
<div id="wowslider-container1">
<div class="ws_images">
<ul>
<?php for ($i=1; $i<=10; $i++){ if ($Image[$i] != null) { ?>   															
<li>
<?php if ($Link[$i] == null) : ?>                                                 														
<img src="<?php echo $Image[$i] ?>" alt="" title="<?php echo $Slidetext[$i] ?>"/> 					   
<?php else: ?>	                                      																						
<a href="<?php echo $Link[$i] ?>"><img src="<?php echo $Image[$i] ?>" alt="" title="<?php echo $Slidetext[$i] ?>"/></a>	
<?php endif; ?>                                                                                        
</li>
<?php }};  ?>	                                          		   																
</ul>
</div>
</div>
<div class="clear"></div>
</div>

<script type="text/javascript" src="<?php echo $this->baseurl ?>/templates/<?php echo $this->template; ?>/images/slideshow/effects/wowslider.js"></script>
<script type="text/javascript" src="<?php echo $this->baseurl ?>/templates/<?php echo $this->template; ?>/images/slideshow/effects/fade/script.js"></script>

<script>
jQuery("#wowslider-container1").wowSlider
({
effect:"fade",
prev:"",
next:"",
duration:20*100,
delay:20*100,
width:960,
height:100,
autoPlay:true,
playPause:false,
stopOnHover:false,
bullets:true,
caption:true,
captionEffect:"slide",
controls:true,
onBeforeStep:0,
images:0
});
</script>

<?php else: ?>
<?php endif; ?>

