<style>
	.read-only{
		pointer-events: none;
		
		background-image: none!important;		
		color: #484747!important; 
		background-color: #3a3a3a!important;		
		text-shadow: white 1px 1px 0!important; 
		opacity: 0.5!important; 
		filter: none!important; 
		-ms-filter: none!important; 
		cursor: default!important;
	}
</style>
<?php if(!$this->readOlny):?>
<?php if($this->isShowAttemptButton) : ?>
	<form style="text-align: right;" id="newAttemptForm" enctype="multipart/form-data" method="post" action="<?=$this->url(array('module' => 'interview', 'controller' => 'index', 'action' => 'add-attempt'), null, false);?>">
		<input type="hidden" name="interview_id" value="0" id="interview_id">
		<input type="submit" name="button" id="addNewAttempt" value="Добавить попытку">
	</form>

	<script>
	$( document ).ready(function() {
		
		$( "#dialog-confirm-attempt" ).dialog({
			resizable: false,
			autoOpen: false,
			height:180,
			modal: true,
			buttons:
			{
				<?php echo _('Да')?>: function() {
					$( this ).dialog( "close" );
					$("#newAttemptForm").submit();
				},
				<?php echo _('Нет')?>: function() {
					$( this ).dialog( "close" );
				}
			}
		});
			
		$('#addNewAttempt').click(function() {
			$( "#dialog-confirm-attempt" ).dialog( "open" );
			return false;			
		}); 
	});
	</script>
	<div id="dialog-confirm-attempt" title="Подтверждение действия">
		<p><span style="float: left; margin: 0 7px 20px 0;"><?= _('Вы действительно желаете добавить попытку за данное занятие? Студент сможет повторно прикрепить задание.') ?></span></p>
	</div>
<?php endif; ?>
<?php endif; ?>

<script>
	//$("#ball").attr("disabled") = "";
 $(function() {
	$("#type").change(function(){
    	if($("#type option:selected").val() == <?php echo HM_Interview_InterviewModel::MESSAGE_TYPE_BALL?>)
    	{
    		$("#ball").prop("disabled",false);
			$("[name='range_mark']").prop("disabled",false);						
			if($("#ball").val() > 0) { $("#interview").prop("disabled",false); }
			else 					 { $("#interview").prop("disabled",true);  }
    	}
    	else {
    		$("#ball").prop("disabled",true);
			$("[name='range_mark']").prop("disabled",true);			
			$("#interview").prop("disabled",false);
		}
    	}).change();
		
	$("[name='range_mark']").change(function(){    	
		var el 			 = $("[name='range_mark']:checked");
		var markSelected = el.val();
		var id			 = el.attr('id');
		
		if (typeof markSelected != "undefined"){			
			var ballListScales = JSON.parse('<?=Zend_Json::encode(HM_Interview_InterviewModel::getBallListScales()); ?>');
			if (typeof ballListScales[markSelected] != "undefined"){
				$("#ball").empty();		
				var maxBall = 0;
				$.each(ballListScales[markSelected], function(i, value) {										
					if(maxBall < value) { maxBall = value; }
					$("#ball").append($('<option>').text(value).attr('value', value));
				});
				if(id == 'range_mark-2'){
					maxBall = 10;
				}
				$("#ball").val(maxBall);
				$("#interview").prop("disabled",false);
			}
		}		
    }).change();	
	
	
	
	$("#ball").change(function(){
		if($("#type option:selected").val() == <?php echo HM_Interview_InterviewModel::MESSAGE_TYPE_BALL?>) {
			if($("#ball").val() >= 0) { $("#interview").prop("disabled",false); }
			else 					 { $("#interview").prop("disabled",true);  }				
		} else {
			if($('#is_change_mark').val() == 1){
				if($("#ball").val() >= 0) { $("#interview").prop("disabled",false); }
				else 					 { $("#interview").prop("disabled",true);  }				
			}			
		}
    }).change();
			
	
    $( "#dialog-confirm" ).dialog({
		resizable: false,
		autoOpen: false,
		height:180,
		modal: true,
		buttons:
		{
			<?php echo _('Да')?>: function() {
				$( this ).dialog( "close" );
				$("#target").submit();
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
				$("#target").submit();
			},
			<?php echo _('Нет')?>: function() {
				$( this ).dialog( "close" );
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
	
	
	$( "#interview" ).click(function() {
		if ($("#type").attr("value") == <?php echo HM_Interview_InterviewModel::MESSAGE_TYPE_BALL?>)
		{
			$( "#dialog-confirm" ).dialog( "open" );
			return false;
		} else {
			if($('#is_change_mark').val() == 1){
				$( "#dialog-confirm-change-mark" ).dialog( "open" );
				return false;
			} else {
				// проверка на непустое текстовое поле или прикрепленный файл.
				//var mes = $('#message').val();
				var mes = $('#target textarea').val();
				
				var attachment = $('#target .cancel-upload');
				
				mes = mes.replace(/<\/?[^>]+>/g, '');
				mes = mes.replace(/&nbsp;/g,'');
				mes = mes.replace(/\r|\n/g, '');
				mes = mes.replace(/\s{2,}/g, ' ');
						
				if(mes.length < 3 && attachment.length < 1){ <?php /* поле текст пустое или не прикреплен файл */ ?>
					$( "#dialog-empty-form" ).dialog( "open" );
					return false;	
				}				
			}			
		}
		$(this).addClass('read-only');
		//$(this).prop('disabled', true);
		
	});
});
$( document ).ready(function() {
	<?php if(!$this->isCanSetMark && $this->isTutor  && !$this->isCanSetMarkLight) : ?>
			$('#type option[value=<?=HM_Interview_InterviewModel::MESSAGE_TYPE_BALL;?>]').attr('disabled', 'disabled');
	<?php endif; ?>
	
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
});

function mb_strlen(str) {
    var len = 0;
    for(var i = 0; i < str.length; i++) {
        len += str.charCodeAt(i) < 0 || str.charCodeAt(i) > 255 ? 2 : 1;
    }
    return len;
}
</script>
<div id="dialog-confirm" title="Подтверждение действия">
    <p><span style="float: left; margin: 0 7px 20px 0;"><?= _('Вы действительно желаете выставить оценку за данное занятие? Дальнейшее добавление сообщений будет невозможно.') ?></span></p>
</div>
<div id="dialog-confirm-change-mark" title="Подтверждение действия">
    <p><span style="float: left; margin: 0 7px 20px 0;"><?= _('Вы действительно желаете изменить оценку за данное занятие?') ?></span></p>
</div>
<div id="dialog-empty-form" title="Информация">
    <p><span style="float: left; margin: 0 7px 20px 0;"><?= _('Чтобы отправить сообщение, необходимо прикрепить файл или написать сообщение в поле "Текст" не менее 3 символов.') ?></span></p>
</div>
<?php
$this->headLink()->appendStylesheet($this->baseUrl('css/content-modules/test.css'));
$keyshowform = $kods = array();
foreach($this->messages as $message){
    
    if ($this->taskPreview && in_array($message->question_id, $kods)) continue; // при предпросмотре не показываем одни и те же назначенные варианты
    if($message->ball){
		$mark = $message->ball;
	} else {		
		$mark = 0.000000001; //--в шаблоне будет 0.
	}	

    echo $this->interviewMessage($message, $this->teacher, $this->lesson, $mark);
	$kods[] = $message->question_id;
}
?>

<div style="margin-top: 30px;"></div>
<?php if(!$this->readOlny):?>
<?php
if (!$this->taskPreview && $this->isShowForm){
	if(!$this->isCanSetMark && $this->isTutor && !$this->isChangeMarkForm && !$this->isCanSetMarkLight) {		
		echo '<p style="color: red;">'._('Вы сможете выставить оценку только после того, как студент прикрепит решение на проверку').'.</p><br>';
	}
	if($this->isCanSetMarkLight){
		echo $this->form_light;
	} else {
		echo $this->form;
	}
}
?>
<?php endif;?>