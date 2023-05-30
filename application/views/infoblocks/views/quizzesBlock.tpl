<? if ($this->enabled): ?>
<div id="quizzes-question"><? echo $this->question?></div>
<div id="quizzes-answers">
	<form id="quizzes-answers-form">
		<input type="hidden" id="quizUrl" name="quizUrl" value="<? echo $this->url(array('module' => 'infoblock', 'controller' => 'quizzes', 'action' => 'answer'))?>">
		<input type="hidden" name="quiz_id" value="<? echo $this->quizId; ?>">
		<input type="hidden" name="question_id" value="<? echo $this->questionId; ?>">
		<input type="hidden" name="format" value="json">
		<? foreach($this->answers as $key => $answer): ?>
		<div class="quizzes-answer"><input name="answer[]" type="<? echo ($this->type == HM_Question_QuestionModel::TYPE_ONE) ? 'radio' : 'checkbox'?>" value="<? echo $key?>" <? echo (in_array($key, $this->userAnswers)) ? 'checked' : ''; ?>><label class="quizzes-answer-label" for="quizzes-answer-<? echo $key?>"><? echo $answer?></label></div>
		<? endforeach; ?>
		<input id="quizzes-answers-submit" type="submit" value="<? echo _('Ответить')?>" <? echo $this->answersDisabled ? 'disabled' : ''; ?>>
		<div id="quizzes-results-allow" <? echo !$this->resultsEnabled ? 'style="visibility: hidden"' : ''; ?>>
			<a href="javascript:void(0);"><? echo _('Результаты опроса')?></a>
		</div>
		<div style="clear: both"></div>
	</form>
</div>
<div id="quizzes-chart-container" style="display: none"><? echo $this->chart('quizzes', 'ampie', 100, 200);?></div>
<? else: ?>
<div id="quizzes-empty"><p><? echo _('Отсутствуют данные для отображения'); ?></p></div>
<? endif; ?>
<? if ($this->isModerator): ?>
<hr style="clear: both">
<div id="quizzes-moder">
	<a href="<? echo $this->url(array(
		'module' 		=> 'infoblock',
		'controller'	=> 'quizzes',
		'action'		=> 'edit',
	));?>" id="quizzes-moder-edit">
		<? echo _('Редактировать');?>
	</a>
</div>
<? endif; ?>