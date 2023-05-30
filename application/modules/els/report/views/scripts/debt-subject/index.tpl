<?php $content_area_id 	= 'content-area'; ?>
<?php $form_area_id 	= 'form-area'; ?>
<?php if ($this->gridAjaxRequest):?>
	<?=$this->grid?>	
<?php else : ?>
	<style>
		.no_students {
			color: pink;
			font-weight: bold;
		}
		.btn-rules{
			cursor: pointer;
			font-weight:bold;			
		}
		
		.content-rules{
			padding-bottom: 15px;
		}
	</style>
	<div style="font-size: 13px;">
		<div class="item-rules" >
			<span  class="btn-rules">Условия формирования</span>
			<div class="hidden content-rules">
				<ol>
					<li>Активные учетные записи студентов</li>
					<li>Дата продления 1 или 2 в сессии и назначении студента больше указанной даты. Т.е. находится в текущих на указанную дату</li>
					<li>Студент находится в группе программы сессии</li>
				</ol>
			</div>
		</div>
			
	</div>
	<br />
	<div class="default-form-area form-report-conditions"  id="<?=$form_area_id;?>">
		<?=$this->form;?>
	</div>	
	<br>
	<br>
	<div id='content-area'>		
		<?=$this->grid;?>			
	</div>
	<?php $this->inlineScript()->captureStart()?>
		
		$('.btn-rules').click(function(){			
			var content = $(this).closest('.item-rules').find('.content-rules');
			if(content.hasClass('hidden')){
				content.removeClass('hidden');
			} else {
				content.addClass('hidden');				
			}
			return false;
		});
	
		$('#tutor_id').select2();				
		$('#faculty_name').select2();				
		$('#chair_name').select2();	
	
		$(document.body).delegate('#<?=$form_area_id;?> form', 'submit', _.debounce(function (event) {
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
						$('#<?=$content_area_id;?>').html('');
						$('#<?=$content_area_id;?>').append(data);									
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

		$(document.body).delegate('#<?=$form_area_id;?> form', 'submit', function(event) {
			event.preventDefault();
		});	
	<?php $this->inlineScript()->captureEnd()?>
<?php endif;?>