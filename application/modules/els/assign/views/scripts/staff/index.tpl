<style>
.spinner-area-report {
	width: 100%;
    height: 100%;
}
</style>
<?php $form_area_id = 'form_area'; ?>
<?php $report_area_id = 'report_area'; ?>
<?php if (!$this->gridAjaxRequest):?>		
	<div id="<?=$form_area_id;?>" class ="<?=$form_area_id;?>"> 		<?=$this->form;?>	</div>	
	<div id="<?=$report_area_id;?>"> 	<?=$this->report;?>	</div>	
<?php else : ?>
	<div id="<?=$report_area_id;?>"> 	<?=$this->report;?>	</div>
<?php endif;?>

<?php $this->inlineScript()->captureStart(); ?>
	$('#group_id').select2();

	$(document.body).delegate('#<?=$form_area_id;?> form', 'submit', _.debounce(function (event) {
		var form_id 	= $(this).attr('id');
		var form_action = $(this).attr('action');
		var form_data 	= $(this).serialize();	
		$('#'+form_id+' #submit').prop('disabled', true);	
		$('#<?=$report_area_id;?>').addClass('ajax-spinner-local spinner-area-report');
		
		$.ajax(form_action, {
				type: 'POST',
				global: false,
				data: form_data
			}).done(function (data) {		
				_.defer(function () {
					$('#<?=$report_area_id;?>').removeClass('ajax-spinner-local');
					$('#<?=$report_area_id;?>').removeClass('spinner-area-report');
					$('#<?=$report_area_id;?>').html(data);
					$('#'+form_id+' #submit').prop('disabled', false);
				});
			}).fail(function () {
				var $message = jQuery("<div><?= _('Произошла ошибка. Попробуйте ещё раз'); ?></div>").appendTo('#' + form_id);
				jQuery.ui.errorbox.clear($message);
				$message.errorbox({level: 'error'});
			}).always(function () {				
		});	
		
	}, 50));

	$(document.body).delegate('#<?=$form_area_id;?> form', 'submit', function(event) {
		event.preventDefault();
	});
<?php $this->inlineScript()->captureEnd(); ?>


