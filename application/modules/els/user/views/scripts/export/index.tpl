<?=$this->form;?>
<?php if ($this->isSupervisor) : ?>
	<?php $this->inlineScript()->captureStart(); ?>
		$('#groups').select2();				
		$('#groups').on("select2:close", function (e) { 			
			getSessionList($(this).val());			
		});
		$('#sessions').select2();
		
		
		function getSessionList(groups){			
			$('#sessions').attr('disabled','disabled');
			$("#sessions").select2("val", "");
			$.ajax(<?= Zend_Json::encode( $this->baseUrl($this->url(array('module' => 'user', 'controller' => 'export', 'action' => 'get-session-list'))) ) ?>, {
					type: 'POST',
					global: false,
					data: {"groups":groups},
					dataType: 'json'
				}).done(function (data) {		
					_.defer(function () {
						$('#sessions').empty();							
						$.each(data, function(i, value) {							
							$('#sessions').append($('<option>').text(value.value).attr('value', value.key));
						});
						$('#sessions').removeAttr('disabled');						
					});
				}).fail(function () {				
					var $message = jQuery("<div><?= _('Произошла ошибка. Попробуйте ещё раз'); ?></div>").appendTo('#export');
					jQuery.ui.errorbox.clear($message);
					$message.errorbox({level: 'error'});					
				}).always(function () {				
			});
		}
	<?php $this->inlineScript()->captureEnd(); ?>
<?php endif; ?>
