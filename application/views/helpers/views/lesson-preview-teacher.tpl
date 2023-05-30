<div class="lesson_min" id="<?php echo $this->lesson->SHEID ?>">
<a name="lesson_<?php echo $this->lesson->SHEID?>"></a>
<div <?php if ($this->lesson->getStudentScore($this->currentUserId) != -1 && $this->showScore):?> class="lesson_wrapper_1_score" <?php else:?> class="lesson_wrapper_1" <?php endif;?>>
<div class="lesson_wrapper_2">
<div <?php if ($this->lesson->getStudentScore($this->currentUserId) != -1 && $this->showScore):
?> id="lesson_block_active" <?php else:?> class="lesson_block" <?php endif;?>>
<div class="lesson_table">
<table border="0" cellpadding="0" cellspacing="0">
  <tr>
    <td width="88" align="center" valign="top" class="lesson_bg">
<div class="lesson_bg_img">
<?php
    if ($userIcon = $this->lesson->getUserIcon()) {
        $icon = $userIcon;
    } else {
        $icon = $this->lesson->getIcon(HM_Lesson_LessonModel::ICON_MEDIUM);
    }
?>
<?php if (!empty($icon)):?>
    <a href="<?php echo $this->lesson->getResultsUrl(array('user_id' => $this->currentUserId))?>">
        <img src="<?php echo $icon?>" alt="<?php echo $this->escape($this->lesson->title)?>" title="<?php echo $this->escape($this->lesson->title)?> - <?php echo ($this->isStudentPageForTeacher)? _('Просмотр результатов слушателя') : _('Просмотр общих результатов');?>"/>
	</a>
    <?php endif;?>
</div>
<div id="lesson_type"><?php echo $this->type;?></div>
</td>
<td width="415" class="lesson_options">
    <div id="lesson_title">
        <?php if ($this->lesson->typeID == HM_Event_EventModel::TYPE_EMPTY): ?>
            <span><?=$this->escape($this->lesson->title) ?></span>
        <?php else: ?>
            <?php $lessonAttribs = array('href' => $this->titleUrl) ?>
            <?php if(!empty($this->targetUrl)) $lessonAttribs['target'] = '_blank' ?>
                <a <?php if ($this->lesson->typeID ==HM_Event_EventModel::TYPE_COURSE) echo 'target="'.$this->lesson->isNewWindow().'"'?> href="<?php echo $this->lesson->getExecuteUrl(array('user_id' => $this->currentUserId))?>" title='<?php echo _('Предпросмотр занятия'); ?>'><?php echo $this->escape($this->lesson->title)?></a>
            <?php endif;?>

            <a class="edit" title="<? echo _('Редактировать')?>" href="<?php echo $this->url(array(
	                'module'     => 'lesson',
	                'controller' => 'list',
	                'action'     => 'edit',
	                'subject_id' => $this->lesson->CID,
	                'lesson_id'  => $this->lesson->SHEID,
	                'fromlist'   => 'y',
	                'user_id'    => ($this->isStudentPageForTeacher)? $this->currentUserId : null
	         ), null, true)?>">&nbsp;&nbsp;&nbsp;&nbsp;</a>

            <a class="els-iconEdit" title="<? echo _('Изменить иконку')?>" href="<?php echo $this->url(array(
                'module'     => 'lesson',
                'controller' => 'list',
                'action'     => 'edit-icon',
                'subject_id' => $this->lesson->CID,
                'lesson_id'  => $this->lesson->SHEID
            ), null, true)?>">&nbsp;&nbsp;&nbsp;&nbsp;</a>

            <a class="delete lesson_preview_teacher_delete_<?php echo $this->lesson->SHEID; ?>" title="<? echo _('Удалить')?>" href="<?php echo $this->url(array(
	                'module'     => 'lesson',
	                'controller' => 'list',
	                'action'     => 'delete',
	                'subject_id' => $this->lesson->CID,
	                'lesson_id'  => $this->lesson->SHEID,
	                'switcher'   => 'my',
	                'user_id'    => ($this->isStudentPageForTeacher)? $this->currentUserId : null
	         ), null, true)?>">&nbsp;&nbsp;&nbsp;&nbsp;</a>

	         <script type="text/javascript">
	           $('.lesson_preview_teacher_delete_<?php echo $this->lesson->SHEID; ?>').bind('click', function(e) {
		         if (!confirm('<?php echo _('Вы действительно хотите удалить?'); ?>')) {
			         e.preventDefault();
		         }
	           });
	         </script>
    </div>

    <div id="lesson_go">
    <div id="lesson_begin" class="<?php if ($this->lesson->recommend):?>recomended<?php endif;?>">
    <?php if ($this->lesson->recommend):?>
        <?php echo _('Рекомендуемое время выполнения');?>:
    <?php else:?>
        <?php echo _('Время выполнения');?>:
    <?php endif;?>
	<?php echo $this->datetime?>
    </div>
    </div>

    <?php if ($this->lesson->cond_sheid):?>
    	<?php $title = $this->titles[$this->lesson->cond_sheid];?>
	    <div class="lesson_cond_sheid"><?php echo _('Условие: выполнение занятия ')?><a href="#lesson_<?php echo $this->lesson->cond_sheid;?>"><?php echo ($title) ? $title : _('не найдено')?></a></div>
	    <?php if ($this->lesson->cond_mark):?>
	    	<div class="lesson_cond_mark"><?php echo _(' на оценку ');?><b><?php echo $this->lesson->cond_mark;?></b></div>
	    <?php endif;?>
    <?php endif;?>
	<?php if($this->lesson->cond_progress):?>
		<div class="lesson_cond_sheid"><?php echo _('Условие: процент выполнения ').$this->lesson->cond_progress.'%'?></div>
	<?php endif;?>
	<?php if($this->lesson->cond_avgbal):?>
		<div class="lesson_cond_sheid"><?php echo _('Условие: средний балл ').$this->lesson->cond_avgbal?></div>
	<?php endif;?>
	<?php if($this->lesson->cond_sumbal):?>
		<div class="lesson_cond_sheid"><?php echo _('Условие: суммарный балл ').$this->lesson->cond_sumbal?></div>
	<?php endif;?>

    <?php if ($this->lesson->getFormulaPenaltyId()):?>
        <div class="lesson_penalty" style="color: red;"><?php echo _('Установлен штраф за несвоевременную сдачу')?></div>
    <?php endif;?>

    <?php if ($this->teacher):?>
    <div class="lesson_teacher"><?php echo _('Преподаватель') . ': ' . $this->cardLink($this->url(array(
    																							'module' => 'user',
    																							'controller' => 'list',
    																							'action' => 'view',
    																							'user_id' => $this->teacher['user_id']))).$this->teacher['fio'];?></div>
    <?php endif;?>

    <?php if ($this->lesson->descript):?>
        <div class="lesson_main_desc"><?php echo $this->lesson->descript;?></div>
    <?php endif;?>
<!--<div id="lesson_edit">
<?php if ($this->allowEdit):?>
        <a href="<?php echo $this->url(array('action' => 'edit', 'lesson_id' => $this->lesson->SHEID))?>"><?php echo $this->icon('edit')?></a>
    <?php endif;?>
    <?php if ($this->allowDelete):?>
        <a href="<?php echo $this->url(array('action' => 'delete', 'lesson_id' => $this->lesson->SHEID))?>"><?php echo $this->icon('delete')?></a>
    <?php endif;?>
</div>
--></td>
<td align="center" valign="top" class="showscore">
<?php if ($this->showScore):?>
    <?php echo $this->score(array(
        'score' => $this->lesson->getStudentScore($this->currentUserId),
        'user_id' => $this->currentUserId,
        'lesson_id' => $this->lesson->SHEID,
        'mode' => HM_View_Helper_Score::MODE_FORSTUDENT,
        'scale_id' => $this->lesson->getScale(),
    ));?>
    <?//php echo $this->lesson->getStudentScore($this->currentUserId)?>
	<div class="student_comment">
    <?php
    $studentAssign = $this->lesson->getStudentAssign($this->currentUserId);
    if ($studentAssign && count($studentAssign->getScoreHistory()) > 1):
    ?>
        <p align="left" style="padding-top:10px;"><?php echo $this->dialogLink(_('История изменений'), $this->lesson->getStudentAssign($this->currentUserId)->getScoreHistoryTable(),_('История изменений'),array('width'=>450))?></p>
    <?php endif;?>
    </div>
<?php endif;?>
</td>

<td width="150" valign="top" class="lesson_descript_td" <?php   if ($this->isStudentPageForTeacher && $this->lesson->getStudentComment($this->currentUserId)):?> <?php  endif;?>>
<div class="lesson_descript">
    <?php if ($this->isStudentPageForTeacher):?>
    <textarea name="score[<?php echo $this->currentUserId; ?>_<?php echo $this->lesson->SHEID; ?>]" class="tComment" id="clId_<?php echo $this->currentUserId; ?>_<?php echo $this->lesson->SHEID; ?>" placeholder="<?php echo _('добавить комментарий к оценке');?>">
<?php
if ( $this->lesson->getStudentComment($this->currentUserId)):
                    echo $this->lesson->getStudentComment($this->currentUserId);
                endif;
?></textarea>
    <?php endif;?>
</div>
</td>
</tr>
</table>
</div>
</div>
</div>
</div>
</div>