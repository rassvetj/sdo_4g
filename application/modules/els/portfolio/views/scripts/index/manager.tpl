<div id="form_area" style="float: left;">
	<?=$this->form;?>	
</div>
<div style="float: left; padding-left: 10px;">
	<button onClick="getCurrentFiles();" id="btn_get_files">Получить фото</button>
	<div id="file_area">
		<ul style="list-style-type: none; margin-left: 0;"></ul>
	</div>
	
</div>

<?/* отправка формы*/?>
<?php $this->inlineScript()->captureStart()?>



$(document.body).delegate('#portfolio', 'submit', _.debounce(function (event) {
		var form_id 	= $(this).attr('id');
		var form_action = $(this).attr('action');
		var form_data 	= $(this).serialize();	
		$('#portfolio #submit').prop('disabled', true);	
		
		$.ajax(form_action, {
				type: 'POST',
				global: false,
				data: form_data,				
			}).done(function (data) {		
				_.defer(function () {																		
					$('#form_area').html(data);
					$('#file_area ul').html('');
					$('#portfolio #submit').prop('disabled', false);
				});
			}).fail(function () {				
				var $message = jQuery("<div><?= _('Произошла ошибка. Попробуйте ещё раз'); ?></div>").appendTo('#' + form_id);
				jQuery.ui.errorbox.clear($message);
				$message.errorbox({level: 'error'});
				$('#portfolio #submit').prop('disabled', false);	
			}).always(function () {				
		});	
		
	}, 50));

	$(document.body).delegate('#portfolio', 'submit', function(event) {
		event.preventDefault();
	});
	
	
	function getCurrentFiles(){
		var student_id = $('#student_id_fcbkComplete').val();
		if(student_id != null){					
			$('#btn_get_files').prop('disabled', true);
			$('#file_area ul').html('');
			$.ajax(<?= Zend_Json::encode( $this->baseUrl($this->url(array('module' => 'portfolio', 'controller' => 'index', 'action' => 'get-files'))) ) ?>, {
					type: 'POST',
					global: false,
					dataType: 'json',
					data: {'student_id':student_id},				
				}).done(function (data) {		
					_.defer(function () {																		
						
						for(var key in data.urls){
							$('#file_area')
							$( '#file_area ul').append( '<li style="margin-bottom: 3px;"><img style="width: 100px;" src="/'+data.urls[key]+'" /> <button onClick="deleteFile(this.closest(\'li\'), \''+data.urls[key]+'\')">Удалить</button><li>' );							
						}
						
						$('#btn_get_files').prop('disabled', false);
					});
				}).fail(function () {				
					var $message = jQuery("<div><?= _('Произошла ошибка. Попробуйте ещё раз'); ?></div>").appendTo('#file_area');
					jQuery.ui.errorbox.clear($message);
					$message.errorbox({level: 'error'});
					$('#btn_get_files').prop('disabled', false);	
				}).always(function () {				
			});	
		} else {
			
		}		
	}
	
	
	function deleteFile(element, file_path){

		var student_id = $('#student_id_fcbkComplete').val();
		$.ajax(<?= Zend_Json::encode( $this->baseUrl($this->url(array('module' => 'portfolio', 'controller' => 'index', 'action' => 'delete-files'))) ) ?>, {
			type: 'POST',
			global: false,			
			data: {'student_id':student_id, 'file_path':file_path},				
		}).done(function (data) {		
			_.defer(function () {																		
				element.remove();								
			});
		}).fail(function () {				
			var $message = jQuery("<div><?= _('Произошла ошибка. Попробуйте ещё раз'); ?></div>").appendTo('#file_area');
			jQuery.ui.errorbox.clear($message);
			$message.errorbox({level: 'error'});			
		}).always(function () {				
		});	
	}
<?php $this->inlineScript()->captureEnd()?>