<h2 style="font-size: 15px;">
	<a href="#" onClick="showBlock('block_<?=$this->lesson_id;?>'); return false;"><?=$this->lesson->title;?></a>
	(
	<?=($this->lesson_score > 0) ? round($this->lesson_score, 2) : ( ($this->lesson_score == 0) ? 0 : 'нет' ); ?> из <?=$this->lesson->max_ball;?>
	,
	<?=(		isset($this->lastMessage['type_name'])		) ? (	$this->lastMessage['type_name']	) 	: 	(		($this->issetTask)?($this->taskNameType):('<span style="color:red">Задание не прикреплено</span>')		) ;?>
	)	
</h2>
<div id="block_<?=$this->lesson_id;?>" class="lesson_area">
	<?php if(!$this->readOlny):?>
	<?php if($this->isShowAttemptButton) : ?>
		<form style="text-align: right;" id="newAttemptForm_<?=$this->lesson_id;?>" enctype="multipart/form-data" method="post"
			action="<?=$this->baseUrl($this->url(array('module' => 'interview', 'controller' => 'index', 'action' => 'add-attempt', 'user_id' => $this->user_id, 'lesson_id' => $this->lesson_id)));?>">
			<input type="hidden" name="interview_id" value="0" id="interview_id">
			<input type="hidden" name="lesson_id" 	 value="<?=$this->lesson_id;?>">
			<input type="submit" name="button" id="addNewAttempt_<?=$this->lesson_id;?>" class="addNewAttempt" value="Добавить попытку">
		</form>
	<?php endif; ?>
	<?php endif; ?>
	

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
		<?php if(!$this->isCanSetMark && $this->isTutor && !$this->isChangeMarkForm) : ?>
			<p style="color: red;"><?= _('Вы сможете выставить оценку только после того, как студент прикрепит решение на проверку.') ?></p><br>
			<script>
				$( document ).ready(function() {				
					$('#type_<?=$this->lesson_id;?> option[value=<?=HM_Interview_InterviewModel::MESSAGE_TYPE_BALL;?>]').attr('disabled', 'disabled');				
				});
			</script>
		<?php endif; ?>

		<?=$this->form;?>
		<hr style="border-top: 20px solid hsl(216, 25%, 58%)">
	<?php endif; ?>
	
</div>