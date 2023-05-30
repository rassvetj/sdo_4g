<div class="lesson_min">
<a name="lesson_<?php echo $this->lesson->SHEID?>"></a>
<div class="lesson_wrapper_1">
<div class="lesson_wrapper_2">
<div class="lesson_block">
<div class="lesson_table">
<table border="0" height="55" cellpadding="0" cellspacing="0" >
  <tr>
    <td width="55" height="55" align="center" valign="middle" class="lesson_bg lesson-cell">
<div class="lesson_bg_img">
<?php if ($this->lesson->getIcon()):?>

    <a <?php if ($this->lesson->typeID ==HM_Event_EventModel::TYPE_COURSE) echo 'target="'.$this->lesson->isNewWindow().'"'?> href="<?php echo $this->url(array('action' => 'index', 'controller' => 'execute', 'module' => 'lesson', 'lesson_id' => $this->lesson->SHEID, 'subject_id' => $this->lesson->CID), false, true)?>">
        <img src="<?php echo ($this->lesson->getUserIcon()) ? $this->lesson->getUserIcon() : $this->lesson->getIcon(HM_Lesson_LessonModel::ICON_MEDIUM)?>" alt="<?php echo $this->escape($this->lesson->title)?>" title="<?php echo $this->escape($this->lesson->title)?>" <?php if (strpos($this->lesson->getIcon(), 'test') !== false):?>class="sup"<?php endif;?>/>
    </a>
    <?php endif;?>
</div>
</td>
<td class="lesson_options">
    <div id="lesson_title">
        <?php if ($this->lesson->typeID == HM_Event_EventModel::TYPE_EMPTY): ?>
            <span><?php echo $this->escape($this->lesson->title) ?></span>
        <?php else: ?>
            <?php $lessonAttribs = array('href' => $this->titleUrl, 'target' => ($this->lesson->typeID == HM_Event_EventModel::TYPE_WEBINAR) ? '_blank' : '_self') ?>
            <?php if(!empty($this->targetUrl)) $lessonAttribs['target'] = '_blank' ?>
            <?php if ($this->lesson->typeID ==HM_Event_EventModel::TYPE_COURSE) $lessonAttribs['target'] = $this->lesson->isNewWindow();?>
            <a <?php echo $this->HtmlAttribs($lessonAttribs) ?>><?php echo $this->escape($this->lesson->title) ?></a>
        <?php endif ?>
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
        <?php if (/*$this->lesson->isRelative() && */isset($this->lesson->students)):?>
            <?php foreach($this->lesson->students as $student):?>
                <?php echo $student['fio']?> <?php if ($this->lesson->isRelative()):?><?php echo $this->lesson->date($student['beginRelative'])?> <?php if ($this->lesson->date($student['beginRelative']) != $this->lesson->date($student['endRelative'])):?>- <?php echo $this->lesson->date($student['endRelative']);?><?php endif;?><?php endif;?><br/>
            <?php endforeach;?>
        <?php endif;?>
        <div>
            <?php if(strlen($this->lesson->teacher['fio']) > 0):?>
                <?php echo(_('Преподаватель').": ".$this->lesson->teacher['fio']);?>
            <?php endif?>
        </div>
    </div>


</td>
</tr>
</table>

</div>
</div>
</div>
</div>

</div>
