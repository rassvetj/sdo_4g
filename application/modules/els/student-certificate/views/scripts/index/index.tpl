<?/* ACHTUNG! Тут какая-то солянка накопилась.*/?>
<?php if (!$this->gridAjaxRequest):?>	
<style>
	fieldset		{ padding: 5px!important;  			}
	fieldset dl		{ margin: 0px 0px 0px 5px!important;}
	#file_c-label	{ margin-left: 18px!important;		}
	#fieldset-additional legend					{ pointer-events: none; }
	#fieldset-additional legend span.separator	{ display: none;		}
	
	.date_picker {
		width: 130px!important;
		max-width: 130px!important;
	}
	
	#date_from-label, #date_to-label {
		width: 20px;
		float: left;
		line-height: 27px;
	}
	
	#date_from, #date_to, .ui-datepicker-trigger {
		float: left;
		margin-bottom: 10px;
	}
	
	#file_c-label {		
		clear: both;
	}
	
	.ui-datepicker-trigger {
		padding: 5px;		
	}
</style>

		<div style="    margin-top: 16px; height: 40px; float: left; padding: 5px; display: inline-block;  margin-bottom: -16px;">
			
			<div class="_grid_gridswitcher" data-userway-font-size="11">
				<div class="ending _u_selected"><?=_('Мои заявки')?></div>
				<?/*
				<a href="<?=$this->baseUrl($this->url(array('module' => 'student-certificate', 'controller' => 'statement', 'action' => 'index')));?>">
					<div style="text-decoration: underline;" onclick="$('._grid_gridswitcher div').removeClass('_u_selected');$(this).addClass('_u_selected')" class="ending" data-userway-font-size="11">
						<?=_('Мои заявки')?>
					</div>
				</a>
				*/?>				
			</div>
		</div>

	<div class="area-form">
		<div class="area-ch-form-order">
			<table>
				<tr>
					<td>
						<input type="radio" id="rChangeForm1" name="changeForm" value="1" checked="checked"><label for="rChangeForm1"><?=_('Заказать справку/документ')?></label>
					</td>
					<td>
						<input type="radio" id="rChangeForm2" name="changeForm" value="2" ><label for="rChangeForm2"><?=_('Задать вопрос')?></label>
					</td>
				</tr>
				<tr>
					<td>
						<input type="radio" id="rChangeForm3" name="changeForm" value="3" ><label for="rChangeForm3"><?=_('Отправить документ')?></label>
					</td>					
				</tr>
			</table>				
		</div>	
	   
		<div class="error-box"></div>	
		
		<div id="block_1" class="block_form">
			<?php echo $this->content_form?>
		</div>
		<div id="block_2" class="hidden block_form">
			<?php echo $this->content_form_q?>
		</div>	
		<div id="block_3" class="hidden block_form">
			<?php echo $this->content_form_sd?>
		</div>			
	</div>
	<div class="area-grid">
		<?php echo $this->content_grid?>
	</div>
<?php else : ?>
	
	<?php echo $this->content_grid?>
	
<?php endif;?>

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
				//$('#'+block_id+' form #area-description').remove();
				$('#'+block_id+' #area-description').remove();
				
				if(data.length > 500){ //--особо длинные тексты выводим слева справа от формы.			
					$('#' + block_id +' form').after('<div id="area-description" class="area-description-right">'+data+'</div>');					
				}
				else {
					if(data.length > 4){													
						$('#' + block_id +' form fieldset').filter(':last').after('<div id="area-description">'+data+'</div>');	
					}
				}	
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
	
	
	function changeFormOrder(type_id){		
		if(type_id == '<?=HM_StudentCertificate_StudentCertificateModel::TYPE_GIA;?>'){
			$('#employer_c-label').show();
			$('#employer_c').show();
			
			$('#date_from-label').show();
			$('#date_from').closest('dd').show();
			
			$('#date_to-label').show();
			$('#date_to').closest('dd').show();
		} else {
			$('#employer_c-label').hide();
			$('#employer_c').hide();
			
			$('#date_from-label').hide();
			$('#date_from').closest('dd').hide();
			
			$('#date_to-label').hide();
			$('#date_to').closest('dd').hide();
		}
		
		if(type_id == '<?=HM_StudentCertificate_StudentCertificateModel::TYPE_LICENSE;?>'){
			$('#fieldset-additional_2').show();
		} else {
			$('#fieldset-additional_2').hide();
		}
		
		
		if(
			type_id == '<?=HM_StudentCertificate_StudentCertificateModel::TYPE_GIA;?>'
			||
			type_id == '<?=HM_StudentCertificate_StudentCertificateModel::TYPE_VALIDATION;?>'
		){
			$('#place_work').show();
			$('#place_work-label').show();
			$('#place_work').closest('dd.element').show();
		} else {
			$('#place_work').hide();
			$('#place_work-label').hide();
			$('#place_work').closest('dd.element').hide();
		}
		
		
		if(
			type_id == '<?=HM_StudentCertificate_StudentCertificateModel::TYPE_GRANT;?>'			
		){
			$('#period').show();
			$('#period-label').show();
			$('#period').closest('dd.element').show();
		} else {
			$('#period').hide();
			$('#period-label').hide();
			$('#period').closest('dd.element').hide();
		}
		
		
		if(
			type_id == '<?=HM_StudentCertificate_StudentCertificateModel::TYPE_GRANT_STATE_SOCIAL;?>'
			||
			type_id == '<?=HM_StudentCertificate_StudentCertificateModel::TYPE_GRANT_STATE_SOCIAL_INCREASED;?>'
			||
			type_id == '<?=HM_StudentCertificate_StudentCertificateModel::TYPE_GRANT_STATE_ACADEMIC_INCREASED;?>'
			||
			type_id == '<?=HM_StudentCertificate_StudentCertificateModel::TYPE_MATERIAL_HELP;?>'
		){
			$('#document_series').show();
			$('#document_series-label').show();
			$('#document_series').closest('dd.element').show();
			
			$('#document_number').show();
			$('#document_number-label').show();
			$('#document_number').closest('dd.element').show();
			
			$('#document_issue_date').show();
			$('#document_issue_date-label').show();
			$('#document_issue_date').closest('dd.element').show();
			
			$('#document_issue_by').show();
			$('#document_issue_by-label').show();
			$('#document_issue_by').closest('dd.element').show();
			
			$('#privilege_type').show();
			$('#privilege_type-label').show();
			$('#privilege_type').closest('dd.element').show();
			
			$('#privilege_date').show();
			$('#privilege_date-label').show();
			$('#privilege_date').closest('dd.element').show();
			
			$('#document_file').show();
			$('#document_file-label').show();
			$('#document_file').closest('dd.element').show();

			$('#destination').hide();
			$('#destination-label').hide();
			$('#destination').closest('dd.element').hide();
			
			
		} else {
			$('#document_series').hide();
			$('#document_series-label').hide();
			$('#document_series').closest('dd.element').hide();
			
			$('#document_number').hide();
			$('#document_number-label').hide();
			$('#document_number').closest('dd.element').hide();
			
			$('#document_issue_date').hide();
			$('#document_issue_date-label').hide();
			$('#document_issue_date').closest('dd.element').hide();
			
			$('#document_issue_by').hide();
			$('#document_issue_by-label').hide();
			$('#document_issue_by').closest('dd.element').hide();
			
			$('#privilege_type').hide();
			$('#privilege_type-label').hide();
			$('#privilege_type').closest('dd.element').hide();
			
			$('#privilege_date').hide();
			$('#privilege_date-label').hide();
			$('#privilege_date').closest('dd.element').hide();
			
			$('#document_file').hide();
			$('#document_file-label').hide();
			$('#document_file').closest('dd.element').hide();
			
			$('#destination').show();
			$('#destination-label').show();
			$('#destination').closest('dd.element').show();
			
		}
		
		
	}
	

<?php $this->inlineScript()->captureEnd()?>




<?php $this->inlineScript()->captureStart()?>

	(function () {
		
		
		
		
		var block_1 = 'block_1';
		var block_2 = 'block_2';
		var block_3 = 'block_3';
		
		
		<?php if(isset($_GET['utm_source']) && $_GET['utm_source'] == 'mail') : /* Если перешли по ссылке из письма */ ?>
			$( "#rChangeForm3" ).click();
			showBlock('3');		
			$("select").find("option:contains('Фотография')").attr("selected", "selected");				
		<?php endif; ?>
		
		
		getDescription($('#'+block_1+' #type').val(), block_1);
		getDescription($('#'+block_3+' #type').val(), block_3);
		changeFormOrder($('#'+block_1+' #type').val());
		changeFormDoc($('#'+block_3+' #type').val());
		
		$('#'+block_1+' #type').change(function() {		
			getDescription($(this).val(), block_1);
			changeFormOrder($(this).val());
		});
		
		$('#'+block_3+' #type').change(function() {					
			getDescription($(this).val(), block_3);
			changeFormDoc($(this).val());
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
			
			var btn = $('#'+id+' input[type="submit"]');
			btn.prop('disabled', true);
			
			
			$('#' + id).closest('.ui-portlet').addClass('ui-state-loading');

			var hwDetect = hm.core.ClassManager.require('hm.core.HardwareDetect').get();
			
			$.ajax( url , {
				type: 'POST',
				global: false,
				data: getFieldsForm(id)		    
			}).done(function (data) {		
				_.defer(function () {
					
					$('#' + id).html(data);
					
					$('#'+ id +' select[id="type"]').attr('onChange',    'changeSendForm($(\'#'+ id +' select[id="type"]\').val()); '
																	   + 'getDescription($(\'#'+ id +' select[id="type"]\').val(), "'+id+'"); '
																	   + 'changeFormOrder($(\'#'+ id +' select[id="type"]\').val()); '
																	   + 'changeFormDoc($(\'#'+ id +' select[id="type"]\').val()); '
																	);
					getDescription($('#'+id+' form #type').val(), id);
					changeSendForm($('#'+ id +' select[id="type"]').val());					
					changeFormOrder($('#'+ id +' select[id="type"]').val());
					changeFormDoc($('#'+ id +' select[id="type"]').val());					
					btn.prop('disabled', false);
					
				});
			}).fail(function () {
				var $message = jQuery("<div><?= _('Произошла ошибка. Попробуйте ещё раз'); ?></div>").appendTo('#' + id);
				jQuery.ui.errorbox.clear($message);
				$message.errorbox({level: 'error'});
				btn.prop('disabled', false);
			}).always(function () {
				$('#' + id).closest('.ui-portlet').removeClass('ui-state-loading');
				$('#' + id)
					.prop('disabled', false)
					.find('input').prop('disabled', false);
				btn.prop('disabled', false);
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
	
		
	$('input[type=file]').change(function() { 		
		var idf = $(this).attr('id')+'-list';
		if($(this).val().length > 181){		
			$('#'+idf).before('<div id="'+idf+'-error" style="color:red;"><?= _('Имя файла слишком длинное.<br>Максимальная длинна имени 160 символов.'); ?></div>');		
		} else {
			$('#'+idf+'-error').hide();
		}
	});	
	
	
	
	function changeFormDoc(type_id)
	{
		if(
			type_id == '<?=HM_StudentCertificate_StudentCertificateModel::TYPE_GRANT_STATE_SOCIAL;?>'
			||
			type_id == '<?=HM_StudentCertificate_StudentCertificateModel::TYPE_GRANT_STATE_SOCIAL_INCREASED;?>'
			||
			type_id == '<?=HM_StudentCertificate_StudentCertificateModel::TYPE_GRANT_STATE_ACADEMIC_INCREASED;?>'			
		){
			$('#fieldset-d_additional').show();
			console.log('Показать');
		} else {
			$('#fieldset-d_additional').hide();
			console.log('Скрыть');
		}		
	}
	
<?php $this->inlineScript()->captureEnd()?>





