<style>
	#<?=$this->form_area_id;?> fieldset dt {
		padding-bottom: 3px;
		font-size: 12px;
	}	
	#<?=$this->form_area_id;?> fieldset dd label {
		font-size: 12px;	
		
	}
	#<?=$this->form_area_id;?> fieldset dd input[type="text"] {
		padding: 0px;
		height: 26px;
		font-size: 12px;		
	}
	
	#see_in_club_other, #see_in_club_other-label, #criteria_quality_training_other, #criteria_quality_training_other-label {
		display: none;
	}
	
	.isHidden {
		display: none;
	}

	.disabled {
		pointer-events: none;
		background-color: #c9d2d8!important;
	}
</style>
<?php if($this->isVoted === true) : ?>
	<h3>Вы уже прошли анкетирование.</h3>
<?php else : ?>
	<div id="<?=$this->form_area_id;?>">
		<?php if (!$this->gridAjaxRequest):?>	
			<?=$this->form;?>
		<?php else : ?>	
			<?=$this->form;?>		
		<?php endif;?>
	</div>
	<?php $this->inlineScript()->captureStart()?>
		function initMaskPhones(){
			jQuery(function($){
				$("#Phone").mask("+7(999) 999-9999",{placeholder:"+7(xxx) xxx-xxxx"});	
				$("#actual_work_place_phone").mask("+7(999) 999-9999",{placeholder:"+7(xxx) xxx-xxxx"});	
				$("#planned_work_place_phone").mask("+7(999) 999-9999",{placeholder:"+7(xxx) xxx-xxxx"});	
			});			
		}
	
		initMaskPhones();
	
		$('input[name="see_in_club"]').change(function(){
			if($(this).val() == '31'){ <? /*заменить на переменную из модели.Другое (укажите)*/ ?>
				$('#see_in_club_other').show();
			} else {
				$('#see_in_club_other').hide();
			}			
		});
		
		$('input[name="criteria_quality_training"]').change(function(){
			if($(this).val() == '41'){ <? /*заменить на переменную из модели.Другое (укажите)*/ ?>
				$('#criteria_quality_training_other').show();
			} else {
				$('#criteria_quality_training_other').hide();
			}			
		});
	
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
						initMaskPhones();								
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
<a href="/">На главную</a>
<br>
<br>

