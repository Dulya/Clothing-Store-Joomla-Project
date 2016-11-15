<?php 
// no direct access 
defined('_JEXEC') or die('Restricted access'); 

$thumb_align = intval( $params->get( 'thumb_align', 0 ) );
$thumb_align = $thumb_align ? 'right':'left';

?>
<!-- MiniFrontPage Module - Another Quality Freebie from TemplatePlazza.com --> 
<div class="minifrontpage-default" id="minifrontpage-<?php echo $mfpid; ?>">
<div style="display:table;table-layout: fixed;width:100%;padding:0 !important; margin:0 !important;" class="minifrontpageid">
	<?php for($row=1; $row<=$number_of_row;$row++) { ?>
	<div class="mfp-table-row" style="display:table-row;width:<?php echo $column_percentage*$number_of_column; ?>%">
		<?php for($column=1; $column<=$number_of_column;$column++) { ?>
		<div class="mfp-table-cell" style="display:table-cell;width:<?php echo $column_percentage; ?>%;word-wrap:break-word !important">
			<div class="mfp-table-cell-inner">
			<?php
			$counting++;
			if($show_more_article && $counting == $number_of_row*$number_of_column ){
				?>
				<span class="mfp-other-article-title"><?php echo $header_title_links; ?></span>
				<div class="mfp-other-article-inner last-cell">
					<ul>
						<?php
						for($index = $counting; $index < count($list); $index++){
							$item = $list[$index];
							echo modMiniFrontPageHelper::showTagFP( $item->title, $item->link, true, $title_link, '<li>', '</li>' );
						}
						?>
					</ul>
				</div>
				<?php
			}else{
				$item_pos = ( $item_direction == 'down' ) ? ( ( ( $column - 1 ) * $number_of_row ) + $row ) - 1: $counting - 1;
				$item = ( !empty( $list[$item_pos] ) ) ? $list[$item_pos] : null;
				if(!empty($item)){
					
					if ( ($thumbnail_position == 1) && ($thumb_embed == 1) ) {
					
						if ( !empty($item->introimage) ) { ?>
							<a href="<?php echo $item->link; ?>">
								<img class="mfp-img-<?php echo $thumb_align; ?>" 
									src="<?php echo mfpResizeImageHelper::getResizedImage('/'.$item->introimage, $thumb_width, $thumb_height, 'crop'); ?>" 
									alt="<?php echo $item->imgalt; ?>" title="<?php echo $item->imgtitle; ?>"
									style="height:<?php echo $thumb_height; ?>px;width:<?php echo $thumb_width ?>px;" />
							</a>
						
						<?php } elseif ( empty($item->introimage) && !empty($item->fulltextimage) ) { ?>
							<a href="<?php echo $item->link; ?>">
								<img class="mfp-img-<?php echo $thumb_align; ?>" 
									src="<?php echo mfpResizeImageHelper::getResizedImage('/'.$item->fulltextimage, $thumb_width, $thumb_height, 'crop'); ?>" 
									alt="<?php echo $item->imgalt; ?>" title="<?php echo $item->imgtitle; ?>"
									style="height:<?php echo $thumb_height; ?>px;width:<?php echo $thumb_width ?>px;" />
							</a>
						<?php } else {
							
							echo modMiniFrontPageHelper::showTagFP( $item->thumb, $item->link, (($thumbnail_position == 1) && ($thumb_embed == 1)), true, null, null );
						}
					}
					
					if (!empty($show_title)) {
						echo modMiniFrontPageHelper::showTagFP( $item->title, $item->link, true, $title_link, '<span class="mfp-introtitle">', '</span><br/>' );
					}
					if( $show_date OR $show_author ){
						echo '<span class="mfp-date-author">';
						echo modMiniFrontPageHelper::showTagFP( $item->date, null, $show_date, false, null, null );
						echo modMiniFrontPageHelper::showTagFP( ' - ', null, ($show_date && $show_author), false, null, null );
						echo modMiniFrontPageHelper::showTagFP( $item->author, null, $show_author, false, null, null );
						echo '</span>';
					}

					if ( ($thumbnail_position == 0) && ($thumb_embed == 1) ) {
					
						if ( !empty($item->introimage) ) { ?>
							<a href="<?php echo $item->link; ?>">
								<img class="mfp-img-<?php echo $thumb_align; ?>" 
									src="<?php echo mfpResizeImageHelper::getResizedImage('/'.$item->introimage, $thumb_width, $thumb_height, 'crop'); ?>" 
									alt="<?php echo $item->imgalt; ?>" title="<?php echo $item->imgtitle; ?>"
									style="height:<?php echo $thumb_height; ?>px;width:<?php echo $thumb_width ?>px;" />
							</a>
						
						<?php } elseif ( empty($item->introimage) && !empty($item->fulltextimage) ) { ?>
							<a href="<?php echo $item->link; ?>">
								<img class="mfp-img-<?php echo $thumb_align; ?>" 
									src="<?php echo mfpResizeImageHelper::getResizedImage('/'.$item->fulltextimage, $thumb_width, $thumb_height, 'crop'); ?>" 
									alt="<?php echo $item->imgalt; ?>" title="<?php echo $item->imgtitle; ?>" 
									style="height:<?php echo $thumb_height; ?>px;width:<?php echo $thumb_width ?>px;" />
							</a>
						<?php } else {
							
							echo modMiniFrontPageHelper::showTagFP( $item->thumb, $item->link, (($thumbnail_position == 0) && ($thumb_embed == 1)), true, null, null );
						}
					}
							
					echo $item->introtext; 
					echo "<div class='clrfix'></div>";
					
					echo modMiniFrontPageHelper::showTagFP( $item->categtitle, $item->categblog, $cat_title, $cat_title_link, '<span class="mfp-categ">', '</span>' , "Show Category : ".  $item->categtitle ); 
					
					if ($fulllink !=""){
						echo '<a class="mfp-readon" href="'.$item->link.'">'.$fulllink.'</a>';
					}
				}
			}
			?>
			</div>
		</div>
		<?php } ?>
	</div>
	<?php } ?>
</div>
</div>