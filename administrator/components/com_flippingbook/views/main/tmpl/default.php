<?php
/**********************************************
* 	FlippingBook Gallery Component.
*	© Mediaparts Interactive. All rights reserved.
* 	Released under Commercial License.
*	www.page-flip-tools.com
**********************************************/
defined('_JEXEC') or die;

$db = JFactory::getDBO();
$query = "SELECT value FROM #__flippingbook_config WHERE name = 'version'";
$db->setQuery( $query );
$rows = $db->loadObjectList();
$version = $rows[0]->value;

$query = 'SELECT * FROM #__flippingbook_books ORDER BY id DESC LIMIT 5';
$db->setQuery( $query );
$rows = $db->loadObjectList();
?>
<div class="span4">
	<h2>FlippingBook Gallery Component <?php echo $version; ?></h2>
	<table width="384" border="0" cellpadding="0" cellspacing="0">
		<tr>
			<td width="128" height="150" align="center"><a href="index.php?option=com_flippingbook&view=categories" style="text-decoration:none"><img src="components/com_flippingbook/images/m_categories.png" alt="Categories Manager" align="top" border="0"><br />
			<?php echo JText::_( 'COM_FLIPPINGBOOK_CATEGORIES' );?></a>
			</td>
			<td width="128" height="150" align="center"><a href="index.php?option=com_flippingbook&view=books" style="text-decoration:none"><img src="components/com_flippingbook/images/m_books.png" alt="Books Manager" align="top" border="0"><br />
			<?php echo JText::_( 'COM_FLIPPINGBOOK_BOOKS' );?></a>
			</td>
			<td width="128" height="150" align="center"><a href="index.php?option=com_flippingbook&view=pages" style="text-decoration:none"><img src="components/com_flippingbook/images/m_pages.png" alt="Pages Manager" align="top" border="0" /><br />
			<?php echo JText::_( 'COM_FLIPPINGBOOK_PAGES' );?></a>
			</td>
		</tr>
		<tr>
			<td width="128" height="150" align="center"><a href="index.php?option=com_flippingbook&view=configuration" style="text-decoration:none"><img src="components/com_flippingbook/images/m_config.png" alt="Configuration" align="top" border="0"><br />
			<?php echo JText::_( 'COM_FLIPPINGBOOK_CONFIGURATION' );?></a>
			</td>
			<td width="128" height="150" align="center"><a href="index.php?option=com_flippingbook&view=filemanager" style="text-decoration:none"><img src="components/com_flippingbook/images/m_filemanager.png" alt="File Manager" align="top" border="0" /><br />
			<?php echo JText::_( 'COM_FLIPPINGBOOK_FILE_MANAGER' );?></a>
			</td>
			<td width="128" height="150" align="center"><a href="index.php?option=com_flippingbook&view=batchaddingpages" style="text-decoration:none"><img src="components/com_flippingbook/images/m_batch.png" alt="Batch Add Pages" align="top" border="0" /><br />
			<?php echo JText::_( 'COM_FLIPPINGBOOK_BATCH_ADDING_PAGES' );?></a>
			</td>
		</tr>
	</table>
	<div class="span12" style="font-size: 80%; line-height: 120%; margin: 20px 0 0 0;">
		FlippingBook Gallery Component for Joomla CMS v. <?php echo FBComponentVersion; ?><br />
		<a href="http://page-flip-tools.com" target="_blank">Check for latest version</a> | <a href="http://page-flip-tools.com/faqs/" target="_blank">FAQ</a> | <a href="http://page-flip-tools.com/support/" target="_blank">Technical support</a><br />
		<div style="padding: 10px 0 20px 0;">Copyright &copy; 2012 Mediaparts Interactive. All rights reserved.</div>
	</div>
</div>
<div class="span8">
	<ul class="nav nav-tabs">
		<li class="active"><a href="#latest_books" data-toggle="tab"><?php echo JText::_('COM_FLIPPINGBOOK_LATEST_BOOKS');?></a></li>
		<li><a href="#faq" data-toggle="tab"><?php echo JText::_('COM_FLIPPINGBOOK_FAQ');?></a></li>
		<li><a href="#updates" data-toggle="tab"><?php echo JText::_('COM_FLIPPINGBOOK_CHECK_FOR_UPDATES');?></a></li>
		<li><a href="#contact" data-toggle="tab"><?php echo JText::_('COM_FLIPPINGBOOK_SUPPORT_CONTACTS');?></a></li>
	</ul>
	
	<div class="tab-content">
		<div class="tab-pane active" id="latest_books">
			<table class="table table-striped" id="articleList">
				<thead>
					<tr>
						<th>
							<?php echo JText::_( 'COM_FLIPPINGBOOK_TITLE' );?>
						</th>
						<th width="50">
							<?php echo JText::_( 'COM_FLIPPINGBOOK_HITS' );?>
						</th>
						<th width="150">
							<?php echo JText::_( 'COM_FLIPPINGBOOK_MODIFIED' );?>
						</th>
					</tr>
				</thead>
				<tbody>
				<?php
				for ($i=0, $n=count( $rows ); $i < $n; $i++) {
					$row = &$rows[$i];
				?>
					<tr>
						<td>
							<a href="index.php?option=com_flippingbook&view=book&layout=edit&id=<?php echo $row->id; ?>" title="<?php echo JText::_( 'Edit Book' );?>"><?php echo $row->title; ?></a>
						</td>
						<td align="center">
							<?php echo $row->hits; ?>
						</td>
						<td align="center">
							<?php echo $row->modified; ?>
						</td>
					</tr>
				<?php
				}
				?>
				</tbody>
			</table>
		</div>
		<div class="tab-pane" id="faq">
			<h2><?php echo JText::_('COM_FLIPPINGBOOK_FAQ'); ?></h2>
			<div style="padding:10px">
				<strong>How to remove ad blocks?</strong><br />
				The commercial version has no ad blocks. You can purchase it on our site: 
				<a href="http://page-flip-tools.com/buy-now/">http://page-flip-tools.com/buy-now/</a>
				(click Buy Now link and fill all required fields). The payment service provided by 
				Cleverbridge.com company that guarantees absolute safety. <br />
				Once your payment processed, you'll get the download link to your email address. 
				You can also access purchased software from 
				<a href="http://page-flip-tools.com/my_account/">your account</a>.<br />
				There are two ways to upgrade the demo version to commercial:<br />
				1. Uninstall the demo version and install the commercial version downloaded 
				from <a href="http://page-flip-tools.com/my_account/">your account</a>. 
				All component data will be lost in this case.<br />
				2. You can upgrade the demo version to commercial without losing data. Download the 
				commercial version from <a href="http://page-flip-tools.com/my_account/">your account</a>
				and replace files on the server according the instruction in readme.txt file 
				inside the commercial version package.<br />
				Clear the browser cache after upgrading.<br />
				<br />
				<strong>What is the maximum size of images and number of pages?</strong><br />
				Images should not be larger than 2800x2800px, as Flash Player 
				can't work properly with very large images. Loading and flipping pages with large 
				images may take a long time on a PC with a slow CPU and Internet connection. 
				1000-1600px usually enough for most publications. <br />
				The number of pages in a book is not limited, and the component works perfectly 
				with hundreds of pages. The component does not store all pages in memory so that 
				30 pages will not consume 300 Mb of RAM. Usually, performance is affected when 
				the number of pages exceeds 1000.<br />
				<br />
				<strong>What are the usage rights for this component?</strong><br />
				The product may be used as part of a personal or commercial site. You may not 
				sell the product to third parties. The source code of the product may be 
				modified for personal use only. But in this case you will no longer be able 
				to receive technical support.<br />
				<br />
				<strong>May I share the installation file with my colleagues?</strong><br />
				No. It is not in your interests to share this product with third parties. 
				We keep a record of technical support requests. There may be a situation 
				where a person with whom you shared the component sends a technical support 
				request from a wrong address. We will consider it a transfer of the component 
				to a third party, and your license will be revoked. As a result, you will no 
				longer be able to receive free updates and technical support.<br />
				<br />
				<strong>A client for whom I developed a site has requested the original 
				installation file of the component. May I hand it over?</strong><br />
				Yes. The installation file must be handed over to the client.<br />
				<br />
				<strong>I experience problems while using the component.</strong><br />
				Please describe the nature of your problem in a letter and e-mail it along with 
				screenshots (if available) to the technical support service. The letter must be 
				sent from the e-mail address you specified upon registration. Please be sure to 
				include the license number you received at the time of purchase and the direct 
				URL of the problem. The technical support service normally replies to e-mails 
				within 24 hours.<br />
				<br />
				More FAQ: <a href="http://page-flip-tools.com/faqs/" target="_blank">http://page-flip-tools.com/faqs/</a>
			</div>
		</div>
		<div class="tab-pane" id="updates">
			<h2><?php echo JText::_('COM_FLIPPINGBOOK_CHECK_FOR_UPDATES'); ?></h2>
			<div style="padding:10px">
				Check for updates: <a href="http://page-flip-tools.com/" target="_blank">http://page-flip-tools.com/</a>
			</div>
		</div>
		<div class="tab-pane" id="contact">
			<h2><?php echo JText::_('COM_FLIPPINGBOOK_SUPPORT_CONTACTS'); ?></h2>
			<div style="padding:10px">
				Please, don't forget to provide us with your <span style="color: red;">ORDER NUMBER</span> and the direct URL of the problem.<br>Technical support: <a href="http://page-flip-tools.com/support/" target="_blank">http://page-flip-tools.com/support/</a>
			</div>
		</div>
	</div>
</div>