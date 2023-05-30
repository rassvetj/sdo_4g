<? if ($this->enabled): ?>
    <div class="overflow">
        <ul class="holder">
        <?php foreach($this->questions as $question):?>
        <li class="kod_<? echo $question->kod?>">
	        <form class="many-quizzes-answers-form">
	            <input type="hidden" name="format" value="json">
	            <input type="hidden" name="quiz_id" value="<? echo $this->quizId; ?>">
	            <input type="hidden" name="question_id" value="<? echo $question->kod?>">
	            <input type="hidden" id="quizUrl" name="quizUrl" value="<? echo $this->url(array('module' => 'infoblock', 'controller' => 'quizzes', 'action' => 'many-answer'))?>">
                <div id="many-quizzes-question"><? echo $this->question[$question->kod]?></div>
                <div class="many-quizzes-answers">
                        <? foreach($this->answers[$question->kod] as $key => $answer): ?>
                        <div class="many-quizzes-answer"><input name="answer[<?php echo $question->kod?>][]" type="<? echo ($question->qtype == HM_Question_QuestionModel::TYPE_ONE) ? 'radio' : 'checkbox'?>" value="<? echo $key?>" <? echo (is_array($this->userAnswers[$question->kod]) && in_array($key, $this->userAnswers[$question->kod])) ? 'checked' : ''; ?>><label class="many-quizzes-answer-label" for="many-quizzes-answer-<? echo $key?>"><? echo $answer?></label></div>
                        <? endforeach; ?>
                        <input id="many-quizzes-answers-submit" type="submit" value="<? echo _('Ответить')?>" <? echo $this->answersDisabled[$question->kod] ? 'disabled' : ''; ?>>
                        <div class="many-quizzes-results-allow" <? echo !$this->resultsEnabled[$question->kod] ? 'style="visibility: hidden"' : ''; ?>>
                            <a href="javascript:void(0);" data-kod="<? echo $question->kod?>"><? echo _('Результаты опроса')?></a>
                        </div>
                        <div style="clear: both"></div>
                </div>
                <div id="many-quizzes-chart-container-<? echo $question->kod?>" style="display: none"><? echo $this->chart('quizzes' . mt_rand(0, 99999), 'ampie', 100, 200, array('controller' => 'quizzes', 'kod' => $question->kod));?></div>
            </form>
                
        </li>
        <?php endforeach;?>    
        </ul>
    </div>
<? else: ?>
<div id="many-quizzes-empty"><p><? echo _('Отсутствуют данные для отображения'); ?></p></div>
<? endif; ?>
<? if ($this->isModerator): ?>
<div style="clear: both"></div>
<hr>
<div class="bottom-links">
    <a href="<? echo $this->url(array(
        'module'         => 'infoblock',
        'controller'    => 'quizzes',
        'action'        => 'many-edit',
    ));?>" id="many-quizzes-moder-edit">
        <? echo _('Редактировать');?>
    </a>
</div>
<? endif; ?>