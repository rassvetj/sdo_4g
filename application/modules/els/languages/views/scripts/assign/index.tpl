<style>
	.ln_form_area .ln_modified {
		color: #f44336;
	}
	
	.ln_modified_demo {
		background-color:  #f44336;
	}
	
	.ln_not_assign {
		color: #ff9800;
	}
	
	.ln_not_assign_demo {
		background-color:  #ff9800;
	}
	
	.ln_not_available {
		background-color: #c1c1c1;
		pointer-events: none;
	}
	
	.ln_area{
		font-size: 13px;
	}
	
	.ln_form_area table {
		border: 1px solid #ccc;
		border-collapse: collapse;
	}
	
	.ln_form_area td {
		border: 1px solid #ccc;
		padding: 5px;
	}
	
	.in-progress {
		pointer-events: none;
		opacity: 0.5;
	}
	
	.lvl-high {
		background-color: #8bc34a63;
	}
	
	.lvl-middle {
		background-color: #ffc10759;
	}
	
	.lvl-begin {
		background-color: #f4433640;
	}
	
	.tbl-caption {
		text-align: center;		
	}
</style>

<?php if($this->isCanEdit):?>
<div>
	<p>
		После нажатия кнопки "Сохранить" изменить уровень языка нельзя.
	</p>
</div>
<?php endif;?>

<div class="ln_message_container"></div>
<br />

<span class="ln_not_available">&nbsp;&nbsp;&nbsp;&nbsp;</span> - <?=_('Недоступно для редактирования')?>
<br />
<span class="lvl-high">&nbsp;&nbsp;&nbsp;&nbsp;</span> - <?=_('Продвинутый уровень')?>
<br />
<span class="lvl-middle">&nbsp;&nbsp;&nbsp;&nbsp;</span> - <?=_('Нормальный уровень')?>
<br />
<span class="lvl-begin">&nbsp;&nbsp;&nbsp;&nbsp;</span> - <?=_('Начальный уровень')?>


<?=$this->hasNotAvailableStudents ? $this->render('assign/partials/other_students.tpl') : '';?>

<br />
<br />

<div class="ln_area">
	<div>
		<?=_('Ваши студенты')?>
	</div>
	 
	<div class="ln_form_area">
		<form method="<?=$this->form->getMethod()?>" action="<?=$this->form->getAction()?>">
			<div class="hidden"><?=$this->form->getElement('language_code')?></div>
			<table>
				<tr class="tbl-caption">
					<td><?=_('ФИО студента')?></td>
					<td><?=_('Группа')?></td>
					<?php foreach($this->lessons as $lesson):?>
						<td><?=$lesson->title?></td>
					<?php endforeach;?>
					<td><?=_('Итог')?></td>
					<td><?=_('Уровень языка')?></td>
				</tr>				
				<?php foreach($this->students as $student): ?>
				<?php 
					if(!$student->available){ continue; }
					$isBlocked 		= ($student->assign && $student->assign->isBlocked()) ? true : false;
					$language_code	= $student->assign->language->code ? $student->assign->language->code : $student->recommended->language->code;
					$language_name	= $student->assign->language->code ? $student->assign->language->name : $student->recommended->language->name;
					$class 			=  $language_code == HM_Languages_Assign_AssignModel::LEVEL_HIGH   ? 'lvl-high'   : $class;
					$class 			=  $language_code == HM_Languages_Assign_AssignModel::LEVEL_MIDDLE ? 'lvl-middle' : $class;
					$class 			=  $language_code == HM_Languages_Assign_AssignModel::LEVEL_BEGIN  ? 'lvl-begin'  : $class;
				?>
				<tr class="<?=$isBlocked ? 'ln_not_available' : ''?> <?=$class?>">
					<?php if(!$isBlocked): ?>
						<input type="hidden" name="current_language_code[<?=$student->MID?>]" id="current_language_code_<?=$student->MID?>" value="<?=$language_code?>" >
					<?php endif;?>
					<td><?=$student->getName()?></td>
					<td><?=$student->group?></td>
					<?php foreach($this->lessons as $lesson):?>						
						<?php
						$assigns = $lesson->getAssigns();
						$assign  = $assigns->exists('MID', $student->MID);	
						?>
						<td><?=$assign ? $assign->getScoreFormatted() : ''?></td>
					<?php endforeach;?>
					<td><?=$student->ball?></td>
					<td><?=$language_name ? $language_name : _('нет')?></td>
				</tr>	
				<?php endforeach; ?>				
			</table>
			<?php if($this->isCanEdit):?>
				<?=$this->form->getElement('save_button')?>
			<?php else: ?>
				<br />
				<p><?=_('Данные уже сохранены')?></p>
			<?php endif;?>
		</form>
	</div>
</div>
<br />
<br />

<script>
$( document ).ready(function() {
    $('.ln_caption').click(function(){
		$('.ln_caption').removeClass('hidden');
		$('.ln_list_area').addClass('hidden');
		
		var el_list 				= $('#language_code');
		var user_id 				= $(this).data('user_id');
		var current_language_code 	= $('#current_language_code_' + user_id).val();
		
		el_list.val(current_language_code);
		
		$(this).closest('.ln_area').find('.ln_list_area').html(el_list);
		
		$(this).addClass('hidden');
		$(this).closest('.ln_area').find('.ln_list_area').removeClass('hidden');		
	});
	
	$('#language_code').change(function(){
		var item_text 	 = $(this).find('option:selected').text();
		var item_code	 = $(this).val();		
		var user_id   	 = $(this).closest('.ln_area').find('.ln_caption').data('user_id');
		
		$(this).closest('.ln_area').find('.ln_caption').html(item_text).addClass('ln_modified');
		$('#current_language_code_' + user_id).val(item_code);
		
	});
	
	$('.ln_form_area form').submit(function( event ) {
		event.preventDefault();
		
		var form 				= $(this);
		var btn 				= $(this).find('[type="submit"]');
		var message_container 	= $('.ln_message_container');
		message_container.html('');
		btn.addClass('in-progress');
		
		$('.error-box').remove();
		
		$.ajax(form.attr('action'), {
			type: 'POST',
			global: false,
			dataType:'json',
			data   : form.serialize()
			
		}).done(function (res) {
			var $message = jQuery(res.data).appendTo(message_container);
			jQuery.ui.errorbox.clear($message);
				
			if(res.error == 1){
				$message.errorbox({level: 'error'});
			} else {
				$message.errorbox({level: 'success'});
				$('.ln_caption').removeClass('ln_not_assign ln_modified');
			}
			$("html, body").animate({ scrollTop: 0 }, "slow");
			btn.removeClass('in-progress');
			btn.remove();
			
		}).fail(function () {				
			var $message = jQuery("<div><?= _('Произошла ошибка. Попробуйте ещё раз'); ?></div>").appendTo(message_container);
			jQuery.ui.errorbox.clear($message);
			$message.errorbox({level: 'error'});
			$("html, body").animate({ scrollTop: 0 }, "slow");
			btn.removeClass('in-progress');
				
		}).always(function () {				
		});
	});
	
	$('#language_code').blur(function(){
		$('.ln_caption').removeClass('hidden');
		$('.ln_list_area').addClass('hidden');
	});
	
	
	
});
</script>



