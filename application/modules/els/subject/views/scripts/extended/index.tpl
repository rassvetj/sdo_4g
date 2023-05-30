<style type="text/css">
    .els-grid tr.filters_tr input{width:100%; min-width: 0}
	.grid-groups { min-width: 120px; }
</style>
<?php if (!$this->gridAjaxRequest):?>
	<?=(!empty($this->issetDouble))?('<p style="color:red; padding-bottom: 10px; font-weight: bold;">'._('Обнаружены задвоения по следующим внешним ID').': '.implode(', ', $this->issetDouble).'</p>'):(''); ?>
	<div class="_grid_gridswitcher">						
		<a href="<?=$this->url(array('module' => 'subject', 'controller' => 'list', 'action' => 'index', 'base' => $this->baseType));?>"><div style="text-decoration: underline;" onclick="$('._grid_gridswitcher div').removeClass('_u_selected');$(this).addClass('_u_selected')" class="ending"><?=_('Стандартное отображение')?></div></a>		
		<div class="ending _u_selected"><?=_('Разделение по группам')?></div>
	</div>
	<div style="clear:both"></div>	
	<br>
<?php $this->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/content-modules/schedule_table.css'); ?>
<?php echo $this->headSwitcher(array('module' => 'subject', 'controller' => 'list', 'action' => 'index', 'switcher' => 'index'), null, ($this->baseType != HM_Subject_SubjectModel::BASETYPE_SESSION)? array('calendar') : array());?>
<?php if (Zend_Registry::get('serviceContainer')->getService('Acl')->isCurrentAllowed('mca:subject:list:new')):?>
    <?php echo $this->Actions('subject');?>
<?php endif;?>
<span><?=_('Для вывода данных нажмите кнопку "Искать"')?></span>
<p>Тьюторам в колонке "Тьюторы (группа)", <span style="color:green;">выделенные зеленым</span>, доступны все группы сессии.</p>
<?php endif;?>
<?php echo $this->grid?>
<?php if (!$this->gridAjaxRequest):?>
<?php echo $this->footnote();?>
*Для записей без группы назначение/открепление работать не будет.
<?php endif;?>
<?php $this->inlineScript()->captureStart(); ?>
    jQuery(document).ready(function(){
        jQuery('#_fdiv [multiple]').attr('size','1');
        jQuery('#_fdiv [multiple]').removeAttr('multiple');
    });
<?php $this->inlineScript()->captureEnd(); ?>
