<style>
	#<?=$this->form_area_id;?> fieldset dt {
		padding-bottom: 3px;
		font-size: 12px;
		font-weight: bold;		
	}	
	#<?=$this->form_area_id;?> fieldset dd label  {
		font-size: 12px;		
		
	}
	#<?=$this->form_area_id;?> fieldset dd input[type="text"] {
		padding: 0px;
		height: 26px;
		font-size: 12px;		
	}
	
	#dp7_other, #dp7_other-label, #dp10_other, #dp10_other-label  {
		display: none;
	}
	
	.isHidden {
		display: none;
	}	
	
	.caption {
		font-size: 14px;
	}
	
	.lnk {
		font-size: 14px;
		padding-right: 5px;
	}
</style>
<a class="lnk" href="<?=$this->url(array('module' => 'disabled-people', 'controller' => 'resume',  'action' => 'index'));?>"><?=_('Резюме')?></a>
<a class="lnk" href="<?=$this->url(array('module' => 'disabled-people', 'controller' => 'request', 'action' => 'index'));?>"><?=_('Обращения')?></a>
<br>
<br>
<?php if($this->isVoted === true) : ?>
	<h3 class="caption"><?=_('Вы уже прошли анкетирование.')?></h3>
<?php else : ?>	
	<p class="caption"><?=_('Дорогие обучающиеся!')?></p>
	<p class="caption"><?=_('Для организации удобных для Вас условий обучения в нашем Университете ответьте на несколько вопросов:')?></p><br>
	<div id="<?=$this->form_area_id;?>">		
		<?=$this->form;?>	
		<?php $this->inlineScript()->captureStart()?>		
			$('input[name="dp7"]').change(function(){
				if($(this).val() == '78'){ 
					$('#dp7_other').show();
				} else {
					$('#dp7_other').hide();
				}			
			});	
			$('input[name="dp10"]').change(function(){
				if($(this).val() == '99'){ 
					$('#dp10_other').show();
				} else {
					$('#dp10_other').hide();
				}			
			});	
		<?php $this->inlineScript()->captureEnd()?>		
	</div>

	<?php $this->inlineScript()->captureStart()?>		
		$(document.body).delegate('#<?=$this->form_area_id;?> form', 'submit', _.debounce(function (event) {
			var form_id 	= $(this).attr('id');
			var form_action = $(this).attr('action');
			var form_data 	= $(this).serialize();	
			$('#'+form_id+' #submit').prop('disabled', true);	
			
			$.ajax(form_action, {
					type: 'POST',
					global: false,
					data: form_data,				
				}).done(function (data) {		
					_.defer(function () {					
						$('#<?=$this->form_area_id;?>').html('');
						$('#<?=$this->form_area_id;?>').append(data);									
						$('#'+form_id+' #submit').prop('disabled', false);											
					});
				}).fail(function () {				
					var $message = jQuery("<div><?= _('Произошла ошибка. Попробуйте ещё раз'); ?></div>").appendTo('#' + form_id);
					jQuery.ui.errorbox.clear($message);
					$message.errorbox({level: 'error'});
					$('#'+form_id+' #submit').prop('disabled', false);
				}).always(function () {				
			});
			
		}, 50));

		$(document.body).delegate('#<?=$this->form_area_id;?> form', 'submit', function(event) {
			event.preventDefault();
		});
	<?php $this->inlineScript()->captureEnd()?>
<?php endif; ?>
<br>
<br>