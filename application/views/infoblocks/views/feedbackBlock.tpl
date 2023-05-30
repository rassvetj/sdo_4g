<span id="infoblock-feedback">
<div align="center">
    <?php echo sprintf(_('Анкеты обратной связи за период ').'<div class="feedback-input-block">'._('с').' %s</div><div class="feedback-input-block">'._('по').' %s</div>', $this->DatePicker('infoblock-feedback-begin', $this->begin, array('showOn' => 'button','onSelect' => new Zend_Json_Expr('function() {reloadFeedback()}'))), $this->DatePicker('infoblock-feedback-end', $this->end, array('showOn' => 'button', 'onSelect' => new Zend_Json_Expr('function() {reloadFeedback()}'))))?>
</div>
<?php if ($this->subjects):?>
    <table border="0" widht="100%" class="infoblock-feedback-table">
    <?php foreach($this->subjects as $subjectId => $subject):?>
        <?php ksort($subject['lessons'])?>
        <tr><td class="infoblock-feedback-subject"><?php echo $subject['title']?></td></tr>
        <?php foreach($subject['lessons'] as $lesson):?>
            <tr>
                <td class="infoblock-feedback-dates">
                    <?php echo sprintf(_('%s - %s'), date('d.m.y', strtotime($lesson->begin)), date('d.m.y', strtotime($lesson->end)))?>
                </td>
                <td class="infoblock-feedback-lesson">
                    <a <?php if ($lesson->overdue):?>class="overdue"<?php endif;?> href="<?php echo $this->url(array('module' => 'lesson', 'controller' => 'execute', 'action' => 'index', 'lesson_id' => $lesson->SHEID, 'subject_id' => $subject['subject_id']), null, true)?>"><?php echo $lesson->title?></a>
                </td>
            </tr>
        <?php endforeach;?>
    <?php endforeach;?>
    </table>
<?php else:?>
    <div align="center"><?php echo _('Отсутствуют данные для отображения')?></div>
<?php endif;?>
</span>

<?php
if (!$this->ajax) {
    $this->inlineScript()->captureStart();
?>
function reloadFeedback() {
    $('#feedbackBlock #infoblock-feedback').load('/infoblock/feedback/index/begin/'+$('#feedbackBlock #infoblock-feedback-begin').val().replace('.','-').replace('.','-').replace('.','-')+'/end/'+$('#feedbackBlock #infoblock-feedback-end').val().replace('.','-').replace('.','-').replace('.','-'));
}

<?php
    $this->inlineScript()->captureEnd();
}
?>