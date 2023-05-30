<style type="text/css">
    .els-grid tr.filters_tr input{width:100%; min-width: 0}
	.grid-groups { min-width: 120px; }
	
	.tutor-role-area {
		display: inline-flex;
	}
	
	.tutor-role-area table, .tutor-role-area tr, .tutor-role-area td {
		border: none;
		padding: 0px;		
		margin: 0px;
	}
	
	.tutor-role-area td{
		text-align: right;
	}
	
	.tutor-role-area tr.ui-state-hover td, .tutor-role-area tr.ui-state-hover{
		background-color:    inherit!important;
		border-top-color:    inherit!important;
		border-bottom-color: inherit!important;
	}
	
	.tutor-role-area-description .arrow {
		font-size: 21px;
		line-height: 0px;
		vertical-align: middle;
		
	}
</style>
<?php if (!$this->gridAjaxRequest):?>
	<?=(!empty($this->issetDouble))?('<p style="color:red; padding-bottom: 10px; font-weight: bold;">'._('Обнаружены задвоения по следующим внешним ID').': '.implode(', ', $this->issetDouble).'</p>'):(''); ?>
	<div class="_grid_gridswitcher">		
		<div class="ending _u_selected"><?= _('Стандартное отображение') ?></div>		
		<a href="<?=$this->url(array('module' => 'subject', 'controller' => 'extended', 'action' => 'index', 'base' => $this->baseType));?>"><div style="text-decoration: underline;" onclick="$('._grid_gridswitcher div').removeClass('_u_selected');$(this).addClass('_u_selected')" class="ending"><?= _('Разделение по группам') ?></div></a>		
	</div>
	<div style="clear:both"></div>	
	<br>
<?php $this->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/content-modules/schedule_table.css'); ?>
<?php echo $this->headSwitcher(array('module' => 'subject', 'controller' => 'list', 'action' => 'index', 'switcher' => 'index'), null, ($this->baseType != HM_Subject_SubjectModel::BASETYPE_SESSION)? array('calendar') : array());?>
<?php if (Zend_Registry::get('serviceContainer')->getService('Acl')->isCurrentAllowed('mca:subject:list:new')):?>
    <?php echo $this->Actions('subject');?>
<?php endif;?>
<span><?=_('Для вывода данных нажмите кнопку "Искать"')?></span>
<?php endif;?>
<?php echo $this->grid?>
<?php if (!$this->gridAjaxRequest):?>
<?php echo $this->footnote();?>
<div style="padding-top: 10px;">
	<ul style="list-style: none;">
		<li><b>d1</b> &ndash; <?=_('Первое продление')?></li>
		<li><b>d2</b> &ndash; <?=_('Второе продление')?></li>
	</ul>
</div>
<?php endif;?>
<?php $this->inlineScript()->captureStart(); ?>    	
	jQuery(document).ready(function(){
        jQuery('#_fdiv [multiple]').attr('size','1');
        jQuery('#_fdiv [multiple]').removeAttr('multiple');
	
		$('img.multiple_toggle').click(function(){ 
			modifyTutorList();
		});
		
		$('#gridAction_grid').change(function(){ 
			modifyTutorList();			
		});	
	});
	
	function modifyTutorList()
	{
		var tutor_select = $('#tutorsId');
		if(tutor_select.is(':hidden')){
			if(tutor_select.hasClass('select2-hidden-accessible')){ tutor_select.select2('destroy'); }
		} else {				
			if(tutor_select.hasClass('select2-hidden-accessible')){ tutor_select.select2('destroy'); }
			
			var attr = tutor_select.attr('multiple');
			
			if (typeof attr !== typeof undefined && attr !== false) { tutor_select.select2({multiple:true}); }
			else 													{ tutor_select.select2(); 				 }
		}
		modifyTutorRoles();
	}
	
	function modifyTutorRoles()
	{
		
		$('.tutor-role-area').remove();
		$('.tutor-role-area-description').remove();
		
		var tutor_select = $('#tutorsId');
		if(tutor_select.is(':hidden')){ return false; }
		
		var btn = $('#send_grid');
		var tbl = $('#grid');
		
		var html  = '';
		html += '<table>'				
				+ '<tr>'
					+ '<td><label for="role_all">Все роли:	</label><input type="checkbox" id="role_all" name="roles[]" value="all" checked ></td>'
					+ '<td><label for="role_1"	>Лектор:	</label><input type="checkbox" id="role_1" 	 name="roles[]" value="1"></td>'
				+ '</tr>'
				+ '<tr>'
					+ '<td><label for="role_2">Семинарист: </label><input type="checkbox" id="role_2" name="roles[]" value="2"></td>'
					+ '<td><label for="role_4">Лаборант: </label><input type="checkbox" id="role_4" name="roles[]" value="4"></td>'
				+ '</tr>'
			+ '</table>';			
			html = '<div class="tutor-role-area">' + html + '</div>';
			
		html = '<div class="tutor-role-area">' + html + '</div>';
		btn.before(html); 
		
		var description = '';
			description += '<b>Назначить тьюторов:</b>'
							+ '<ul>'
								+ '<li>Выбрано "Все роли" <span class="arrow">&rArr;</span> прочие роли игнорируются, тьютор назначается на все занятия</li>'
								+ '<li>Выбрано "Все роли" и тьютор уже назначен на сессию <span class="arrow">&rArr;</span> назначение на сессию не изменится, назначения на роли удаляться, будут доступны все занятия</li>'
								+ '<li>Выбрана любая роль, кроме "Все роли" <span class="arrow">&rArr;</span> назначение на выбранные роли</li>'
							+ '</ul>'
					+ '<b>Отменить назначения тьюторов:</b>'
						+ '<ul>'
							+ '<li>Выбрано "Все роли" <span class="arrow">&rArr;</span> удаляется назначение на сессию и все роли этой сессии</li>'
							+ '<li>Выбрано "Все роли" и тьютор уже НЕ назначен на сессию <span class="arrow">&rArr;</span> назначения на роли удаляться</li>'
							+ '<li>Выбрана любая роль, кроме "Все роли" <span class="arrow">&rArr;</span> удаляется назначение на сессию и назначение на выбранные роли</li>'
						+ '</ul><br /><br />';
		description = '<div class="tutor-role-area-description">' + description + '</div>';
		tbl.after(description); 
	}

    
<?php $this->inlineScript()->captureEnd(); ?>


