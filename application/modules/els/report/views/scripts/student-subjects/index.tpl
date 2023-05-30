<style>
	#report_form_area {
		width: 400px;
	}

	img.ui-datepicker-trigger {
		height: 25px;
	}
	
	.element .hasDatepicker{
		border: 1px solid #aaa;
		border-radius: 4px;	
		width: 86%;	
		padding: 3px 9px;		
	}
	
	.element .select2-container {
		width: 100%!important;
	}
	
	.element{
		padding-right: 20px;
	}
</style>

<br />	
<br />

<div id="report_form_area">	
	<?=$this->form;?>	
</div>
<br>
<br>
<div id='content_area'>	
	<div class='description-area-tutor'>		
		<?=$this->content;?>		
	</div>
</div>
<?php $this->inlineScript()->captureStart()?>
	
	$('#report_form_area #group_id').select2();
	
	$('#report_form_area #student_id').select2({
		minimumInputLength: 3, 
		ajax: {
			url: '<?=$this->url(array('module' => 'user', 'controller' => 'ajax', 'action' => 'students-list'));?>',		
			delay: 1000,
			dataType: 'json',
			data: function (params) {
				return {
					search: params.term 
				};
			},
			processResults: function (data) {
				return {
					results: data
				};
			},
		}		
	});
	
	
	
	
	$(document.body).delegate('#report_form_area form', 'submit', _.debounce(function (event) {
		var form_id 	= $(this).attr('id');
		var form_action = $(this).attr('action');
		var form_data 	= $(this).serialize();	
		$('#'+form_id+' #submit').prop('disabled', true);			
		$('#content_area').addClass('ajax-spinner-local spinner-area-tutor');
		$.ajax(form_action, {
				type: 'POST',
				global: false,
				data: form_data,				
			}).done(function (data) {						
				_.defer(function () {		
					$('#content_area').removeClass('ajax-spinner-local');
					$('#content_area').removeClass('spinner-area-tutor');				
					
					$('#content_area').html('');
					$('#content_area').append(data);									
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

	$(document.body).delegate('#report_form_area form', 'submit', function(event) {
		event.preventDefault();
	});	
<?php $this->inlineScript()->captureEnd()?>
