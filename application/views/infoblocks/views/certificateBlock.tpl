<div class="area-ch-form-order">
	<input type="radio" name="changeForm" value="1" checked="checked">Заказать справку/документ&nbsp;
	<input type="radio" name="changeForm" value="2">Задать вопрос&nbsp;
	<input type="radio" name="changeForm" value="3">Отправить документ<br>	
</div>	

<div class="error-box"></div>
<div id="block_1" class="block_form">
	<?php echo $this->form?>	
</div>
<div id="block_2" class="hidden block_form">
	<?php echo $this->form_q?>	
</div>	
<div id="block_3" class="hidden block_form">
	<?php echo $this->form_sd?>		
</div>	

<div class="ajax-spinner-local"></div>

<?php $this->inlineScript()->captureStart()?>
function changeSendForm(value){ <?/* Скрывает ненужную форму загрузки фото в зависимости от селекта. */?>
	if(value == '<?=HM_StudentCertificate_StudentCertificateModel::TYPE_PHOTO;?>'){		
		$('#u_document').closest('dd.element').hide();
		$('#u_document-label').hide();
		
		$('#u_photo').closest('dd.element').show();
		$('#u_photo-label').show();
		
		

	} else {
		//if(value == '<?=HM_StudentCertificate_StudentCertificateModel::TYPE_PHOTO;?>'){
		$('#u_photo').closest('dd.element').hide();
		$('#u_photo-label').hide();
		
		$('#u_document').closest('dd.element').show();
		$('#u_document-label').show();
		//}
	}	
}

changeSendForm($('#block_3 #type').val());	

$('#block_3 #type').change(function(){
	changeSendForm($(this).val());	
});

	
	function getDescription(s_type, block_id) {
		
		var hwDetect = hm.core.ClassManager.require('hm.core.HardwareDetect').get();
		
		$.ajax(<?= Zend_Json::encode( $this->baseUrl($this->url(array('module' => 'student-certificate', 'controller' => 'index', 'action' => 'description'))) ) ?>, {
			type: 'POST',
			global: false,
			data: {            
				type: s_type,            
				
				systemInfo: hwDetect.getSystemInfo()
			}
		}).done(function (data) {		
			_.defer(function () {            
				$('#'+block_id+' form #area-description').remove();
				
				//if(data.length > 500){ //--особо длинные тексты выводим слева справа от формы.			
					//$('#' + formId).after('<div id="area-description" class="area-description-right">'+data+'</div>');					
				//}
				//else {
					if(data.length > 4){							
						$('#' + block_id +' form dd.element').filter(':last').after('<div id="area-description">'+data+'</div>');	
					}
				//}	
			});
		}).fail(function () {
			var $message = jQuery("<div><?= _('Произошла ошибка. Попробуйте ещё раз'); ?></div>").appendTo('#' + block_id);
			jQuery.ui.errorbox.clear($message);
			$message.errorbox({level: 'error'});
		}).always(function () {
			$('#' + block_id).closest('.ui-portlet').removeClass('ui-state-loading');
			$('#' + block_id)
				.prop('disabled', false)
				.find('input').prop('disabled', false);			
		});
	}

<?php $this->inlineScript()->captureEnd()?>




<?php $this->inlineScript()->captureStart()?>

	(function () {
		
		var block_1 = 'block_1';
		var block_2 = 'block_2';
		var block_3 = 'block_3';
		
		getDescription($('#'+block_1+' #type').val(), block_1);
		getDescription($('#'+block_3+' #type').val(), block_3);
		
		$('#'+block_1+' #type').change(function() {		
			getDescription($(this).val(), block_1);
		});
		
		$('#'+block_3+' #type').change(function() {		
			getDescription($(this).val(), block_3);
		});
		
		function showBlock(id){
			$('.error-box').html('');
			$('.block_form').hide();
			$('.block_form').addClass('hidden');
			
			$('#block_'+id).show();
			$('#block_'+id).removeClass('hidden');			
		}
		
		$('input[name="changeForm"]').click(function(){		
			var r = $('input[name="changeForm"]:checked').val();			
			showBlock(r);			
		});	

		function getFieldsForm(form_id){
			var obj = new Object();

			$('#'+form_id+' form input, #'+form_id+' form textarea, #'+form_id+' form select').each(function(){
				
				var tag_name = $(this)[0].tagName;
				var item_id = $(this).attr('id');
				var item_name = $(this).attr('name');
				var item_type = $(this).attr('type');
				
				if(tag_name == 'SELECT'){
					obj[item_id] = $('#'+form_id+' '+tag_name+'[id="'+item_id+'"]').val();
					obj[item_id+'_name'] = $('#'+form_id+' '+ tag_name +'[id="'+item_id+'"] option:selected').text(); //--получаем текст выбранного селекта. Если надо.		
				} else {
					if(item_type == 'checkbox'){
						obj[item_id] = $(tag_name+'[id="'+item_id+'"]:checked').val();
					} else { 
						if(item_type == 'radio'){				
							obj[item_name] = $(tag_name+'[name="'+item_name+'"]:checked').val();
						} else {
							obj[item_id] = $('#'+form_id+' '+tag_name+'[id="'+item_id+'"]').val();		
						}			
					}		
				}
			});	
			
			return obj;
		}
		

		function sendForm (id, url) {
			
			$('#' + id).closest('.ui-portlet').addClass('ui-state-loading');

			var hwDetect = hm.core.ClassManager.require('hm.core.HardwareDetect').get();
			
			$.ajax( url , {
				type: 'POST',
				global: false,
				data: getFieldsForm(id)		    
			}).done(function (data) {		
				_.defer(function () {
					
					$('#' + id).html(data);
										
					$('#'+ id +' select[id="type"]').attr('onChange', 'changeSendForm($(\'#'+ id +' select[id="type"]\').val()); getDescription($(\'#'+ id +' select[id="type"]\').val(), "'+id+'")');
					getDescription($('#'+id+' form #type').val(), id);
					changeSendForm($('#'+ id +' select[id="type"]').val());
					
				});
			}).fail(function () {
				var $message = jQuery("<div><?= _('Произошла ошибка. Попробуйте ещё раз'); ?></div>").appendTo('#' + id);
				jQuery.ui.errorbox.clear($message);
				$message.errorbox({level: 'error'});
			}).always(function () {
				$('#' + id).closest('.ui-portlet').removeClass('ui-state-loading');
				$('#' + id)
					.prop('disabled', false)
					.find('input').prop('disabled', false);
			});
		}





		$(document.body).delegate('#' + block_1 + ' form', 'submit', _.debounce(function (event) {
			$('#' + block_1)
				.prop('disabled', true)
				.find('input').prop('disabled', true);
			var $portletContent = $(this).closest('.ui-portlet-content');
			if ($portletContent.length) {
				$portletContent.find('.ajax-spinner-local').appendTo($portletContent.parent());
			}	
			sendForm(block_1, <?= Zend_Json::encode( $this->baseUrl($this->url(array('module' => 'student-certificate', 'controller' => 'index', 'action' => 'send'))) ) ?>);
		}, 50));

		$(document.body).delegate('#' + block_1 + ' form', 'submit', function(event) {
			event.preventDefault();
		});




		$(document.body).delegate('#' + block_2 + ' form', 'submit', _.debounce(function (event) {
			$('#' + block_2)
				.prop('disabled', true)
				.find('input').prop('disabled', true);
			var $portletContent = $(this).closest('.ui-portlet-content');
			if ($portletContent.length) {
				$portletContent.find('.ajax-spinner-local').appendTo($portletContent.parent());
			}	
			sendForm(block_2, <?= Zend_Json::encode( $this->baseUrl($this->url(array('module' => 'student-certificate', 'controller' => 'index', 'action' => 'questionsend'))) ) ?>);
		}, 50));

		$(document.body).delegate('#' + block_2 + ' form', 'submit', function(event) {
			event.preventDefault();
		});



		
		$(document.body).delegate('#' + block_3 + ' form', 'submit', _.debounce(function (event) {
			
			$('#' + block_3)
				.prop('disabled', true)
				.find('input').prop('disabled', true);
			var $portletContent = $(this).closest('.ui-portlet-content');
			if ($portletContent.length) {
				$portletContent.find('.ajax-spinner-local').appendTo($portletContent.parent());
			}	
			sendForm(block_3, <?= Zend_Json::encode( $this->baseUrl($this->url(array('module' => 'student-certificate', 'controller' => 'index', 'action' => 'send-document'))) ) ?>);
		}, 50));

		$(document.body).delegate('#' + block_3 + ' form', 'submit', function(event) {
			event.preventDefault();
		});


	})();
<?php $this->inlineScript()->captureEnd()?>







