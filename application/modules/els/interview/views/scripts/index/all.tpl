<style>
	.lesson_area {
		display: none;
	}
</style>
<h2><?=_('Студент:')?> <?=$this->user->LastName.' '.$this->user->FirstName.' '.$this->user->Patronymic;?></h2> 
<hr>
<?php if(!empty($this->lessonsContent)) : ?>
	<?php foreach($this->lessonsContent as $lesson_id => $i) : ?>				
		<div><?=$i;?></div>		
	<?php endforeach; ?>
<?php endif; ?>
<?php $this->inlineScript()->captureStart()?>
	function showBlock(id){
		el = $('#'+id);
		if(el.is(":visible")){
			$('#'+id).hide();	
		} else {
			$('#'+id).show();	
		}		
	}
<?php $this->inlineScript()->captureEnd()?>

<script>
	$( document ).ready(function() {
		var attempt_id = false;
		var lesson_id = false;
		
		$( "#dialog-confirm-attempt" ).dialog({
			resizable: false,
			autoOpen: false,
			height:180,
			modal: true,
			buttons:
			{
				<?php echo _('Да')?>: function() {
					$( this ).dialog( "close" );					
					$('#'+attempt_id).closest('form').submit();
				},
				<?php echo _('Нет')?>: function() {
					$( this ).dialog( "close" );
					return false;
				}
			}
		});
		
		$( "#dialog-empty-form" ).dialog({
			resizable: false,
			autoOpen: false,
			height:180,
			modal: true,
			buttons:
			{			
				<?php echo _('Закрыть')?>: function() {
					$( this ).dialog( "close" );
				}
			}
		});
		
		$( "#dialog-confirm" ).dialog({
			resizable: false,
			autoOpen: false,
			height:180,
			modal: true,
			buttons:
			{
				<?php echo _('Да')?>: function() {
					$( this ).dialog( "close" );					
					$('#lesson_'+lesson_id).submit();
				},
				<?php echo _('Нет')?>: function() {
					$( this ).dialog( "close" );
				}
			}
		});
		
		$( "#dialog-confirm-change-mark" ).dialog({
			resizable: false,
			autoOpen: false,
			height:180,
			modal: true,
			buttons:
			{
				<?php echo _('Да')?>: function() {
					$( this ).dialog( "close" );
					$('#lesson_'+lesson_id).submit();
				},
				<?php echo _('Нет')?>: function() {
					$( this ).dialog( "close" );
				}
			}
		});
			
		$('.addNewAttempt').click(function() {				
			attempt_id =  $(this).attr('id');			
			$( "#dialog-confirm-attempt" ).dialog( "open");			
			return false;			
		});

		$( ".submit_btn" ).click(function() {
			lesson_id = $(this).attr('id').replace(/[^.\d]+/g,"").replace( /^([^\.]*\.)|\./g, '$1' );
			
			
			if ($('#type_'+lesson_id).attr('value') == <?=HM_Interview_InterviewModel::MESSAGE_TYPE_BALL?>) {
				$('#dialog-confirm').dialog('open');
				return false;
			} else {
				if($('#is_change_mark_'+lesson_id).val() == 1){
					$( "#dialog-confirm-change-mark" ).dialog( "open" );
					return false;
				} else {
					// проверка на непустое текстовое поле или прикрепленный файл.
					var mes = $('#message_'+lesson_id).val();
					var attachment = $('#lesson_'+lesson_id+' .cancel-upload');
					
					mes = mes.replace(/<\/?[^>]+>/g, '');
					mes = mes.replace(/&nbsp;/g,'');
					mes = mes.replace(/\r|\n/g, '');
					mes = mes.replace(/\s{2,}/g, ' ');
							
					if(mes.length < 3 && attachment.length < 1){ <?php /* поле текст пустое или не прикреплен файл */ ?>
						$('#dialog-empty-form').dialog('open');
						return false;	
					}				
				}			
			}
		});
		

	
	});
	
	
	$('.type').change(function(){
		lesson_id = $(this).attr('id').replace(/[^.\d]+/g,"").replace( /^([^\.]*\.)|\./g, '$1' );		
    	if($('#type_'+lesson_id+' option:selected').val() == <?php echo HM_Interview_InterviewModel::MESSAGE_TYPE_BALL?>) {
    		$('#ball_'+lesson_id).prop('disabled', false);
			$("#lesson_"+lesson_id+" [name='range_mark']").prop('disabled', false);									
			if($('#ball_'+lesson_id).val() > 0) { $('#submit_'+lesson_id).prop('disabled', false); }
			else 					 			{ $('#submit_'+lesson_id).prop('disabled', true);  }
    	} else {
    		$('#ball_'+lesson_id).prop('disabled', true);
			$("#lesson_"+lesson_id+" [name='range_mark']").prop('disabled', true);			
			$('#submit_'+lesson_id).prop('disabled', false);
		}
    }).change();
	
	$('.ball').change(function(){
		lesson_id = $(this).attr('id').replace(/[^.\d]+/g,"").replace( /^([^\.]*\.)|\./g, '$1' );		
		if($('#type_'+lesson_id+' option:selected').val() == <?php echo HM_Interview_InterviewModel::MESSAGE_TYPE_BALL?>) {
			if($('#ball_'+lesson_id).val() > 0) { $('#submit_'+lesson_id).prop('disabled', false); }
			else 					 			{ $('#submit_'+lesson_id).prop('disabled', true);  }				
		} else {
			if($('#is_change_mark_'+lesson_id).val() == 1){
				if($('#ball_'+lesson_id).val() > 0) { $('#submit_'+lesson_id).prop('disabled', false); }
				else 					 			{ $('#submit_'+lesson_id).prop('disabled', true);  }				
			}			
		}
    }).change();
	
	$("[name='range_mark']").change(function(){ 		
		lesson_id = $(this).attr('data_id').replace(/[^.\d]+/g,"").replace( /^([^\.]*\.)|\./g, '$1' );		
		
		var el           = $("#lesson_"+lesson_id+" [name='range_mark']:checked");
		var markSelected = el.val();
		var id           = el.attr('id');

		if (typeof markSelected != "undefined"){			
			var ballListScales = JSON.parse('<?=Zend_Json::encode(HM_Interview_InterviewModel::getBallListScales()); ?>');
			if (typeof ballListScales[markSelected] != "undefined"){
				$('#ball_'+lesson_id).empty();		
				var maxBall = 0;
				$.each(ballListScales[markSelected], function(i, value) {										
					if(maxBall < value) { maxBall = value; }
					$('#ball_'+lesson_id).append($('<option>').text(value).attr('value', value));
				});
				
				if(id == 'range_mark-2'){
					maxBall = 10;
				}
				$('#ball_'+lesson_id).val(maxBall);
				$('#submit_'+lesson_id).prop('disabled', false);
			}
		}		
    }).change();
	
	$('input[type=file]').change(function() {			
		var idf = $(this).attr('id')+'-list';
		var len = mb_strlen($(this).val()) - 12;				
		$('#'+idf+'-error').remove();				
		if(len > 166){		
			$('#'+idf).before('<div id="'+idf+'-error" style="color:red;"><?= _('Имя файла слишком длинное.<br>Максимальная длинна имени 85 символ.'); ?></div>');		
		} else {
			$('#'+idf+'-error').hide();
		}
	});
	
	function mb_strlen(str) {
		var len = 0;
		for(var i = 0; i < str.length; i++) {
			len += str.charCodeAt(i) < 0 || str.charCodeAt(i) > 255 ? 2 : 1;
		}
		return len;
	}
</script>
<div id="dialog-confirm-attempt" title="Подтверждение действия">
	<p><span style="float: left; margin: 0 7px 20px 0;"><?= _('Вы действительно желаете добавить попытку за данное занятие? Студент сможет повторно прикрепить задание.') ?></span></p>
</div>
<div id="dialog-empty-form" title="Информация">
    <p><span style="float: left; margin: 0 7px 20px 0;"><?= _('Чтобы отправить сообщение, необходимо прикрепить файл или написать сообщение в поле "Текст" не менее 3 символов.') ?></span></p>
</div>
<div id="dialog-confirm" title="Подтверждение действия">
    <p><span style="float: left; margin: 0 7px 20px 0;"><?= _('Вы действительно желаете выставить оценку за данное занятие? Дальнейшее добавление сообщений будет невозможно.') ?></span></p>
</div>
<div id="dialog-confirm-change-mark" title="Подтверждение действия">
    <p><span style="float: left; margin: 0 7px 20px 0;"><?= _('Вы действительно желаете изменить оценку за данное занятие?') ?></span></p>
</div>