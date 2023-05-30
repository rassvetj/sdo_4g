<? $form_area_id = 'report_form_area';?>
<? $content_area_id = 'content_area';?>
<div id="<?=$form_area_id;?>">
	<form action = "<?=$this->baseUrl($this->url(array('module' => 'report', 'controller' => 'ball-tutors', 'action' => 'get')));?>" id="report_form" method="POST">
		<input type="hidden" name="report_type" value="<?=HM_Report_ReportModel::REPORT_TYPE_BALL;?>">				
		<p><?=_('Выберите тьютора')?></p>
		<select id="tutor_id" name="tutor_id">
			<?php foreach($this->tutors as $key => $value) {		
				echo '<option style="color: black;" value="'.$key.'">'._($value).'</option>';		
			}
			?>
		</select>
		<br>
		<input type="submit" id="submit" value="<?=_('Сформировать')?>" >
	</form>
</div>
<br>
<br>

<div id='<?=$content_area_id;?>'>	
	<div class='description-area-tutor'>		
		<?=$this->content;?>		
	</div>
</div>
<?php $this->inlineScript()->captureStart()?>
	$('#tutor_id').select2();

	$(document.body).delegate('#<?=$form_area_id;?> form', 'submit', _.debounce(function (event) {
		var form_id 	= $(this).attr('id');
		var form_action = $(this).attr('action');
		var form_data 	= $(this).serialize();	
		$('#'+form_id+' #submit').prop('disabled', true);			
		$('#<?=$content_area_id;?>').addClass('ajax-spinner-local spinner-area-tutor');
		$.ajax(form_action, {
				type: 'POST',
				global: false,
				data: form_data,				
			}).done(function (data) {						
				_.defer(function () {		
					$('#<?=$content_area_id;?>').removeClass('ajax-spinner-local');
					$('#<?=$content_area_id;?>').removeClass('spinner-area-tutor');				
					
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