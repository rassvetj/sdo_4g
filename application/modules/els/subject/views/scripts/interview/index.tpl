<?php
$this->headLink()->appendStylesheet($this->baseUrl('css/content-modules/test.css'));
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
<br>
<?=$this->form;?>
<div id="dialog-empty-form" title="Информация">
    <p><span style="float: left; margin: 0 7px 20px 0;">Чтобы отправить сообщение, необходимо прикрепить файл или написать сообщение в поле "Текст" не менее 3 символов.</span></p>
</div>
<script>	
	$(function() {
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
			// проверка на непустое текстовое поле или прикрепленный файл.
			var mes = $('#message').val();
			var attachment = $('#subject_interview .cancel-upload');
			
			mes = mes.replace(/<\/?[^>]+>/g, '');
			mes = mes.replace(/&nbsp;/g,'');
			mes = mes.replace(/\r|\n/g, '');
			mes = mes.replace(/\s{2,}/g, ' ');
					
			if(mes.length < 3 && attachment.length < 1){
				$( "#dialog-empty-form" ).dialog( "open" );
				return false;	
			}
		});
	});
</script>