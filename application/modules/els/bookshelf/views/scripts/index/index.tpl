<?php if($this->gridAjaxRequest):?>
	<?=$this->grid?>
<?php else: ?>
	<div style="padding-bottom: 10px; font-size: 15px;">
		<a href="\upload\files\manuals\bookshelf\Инструкция по работе с Виртуальной книжной полкой для студента.docx" target="_blank" >
			<?=_('Инструкция')?>
		</a>
	</div>
	<div>
		<div style="float: left; padding-right: 10px; padding-bottom: 10px;">
			<a href="<?=$this->url(array('module' => 'library', 'controller' => 'biblioclub', 'action' => 'create-auth-link'), 'default', true);?>" target="_blank" >
				<img src="\images\logo\biblioklub.ru_220x215.png" alt="biblioklub.ru logo" height="80" >
			</a>
		</div>
		<div style="float: left; padding-right: 10px; padding-bottom: 10px;">
			<a href="<?=$this->url(array('module' => 'library', 'controller' => 'urait', 'action' => 'create-auth-link'), 'default', true);?>" target="_blank" >
				<img src="\images\logo\urait.ru_logo_206x56.svg" alt="urait.ru logo" height="80" >
			</a>
		</div>
	</div>
	<div style="clear:both;"></div>
	<div>
		<?=$this->grid?>
	</div>
<?php endif;?>