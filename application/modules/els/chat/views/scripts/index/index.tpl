<?php if (!$this->gridAjaxRequest):?>
    <?php if (!$this->channel->lesson_id && !$this->isCallFromLesson):?>
    <?php if (!$this->channel->lesson_id):?>
        <?php if ($this->isModerator):?>
        <?php
            echo $this->ViewType('actions', array(
                'url' => $this->url(array('module' => 'chat', 'controller' => 'index', 'action' => 'index', 'subject' => $this->subjectName, 'subject_id' => $this->subjectId), null, true)
            ));
        ?>
        <?php endif;?>
        <?php echo $this->Actions('blog', array(
            array(
                    'title' => _('Создать канал'), 
                    'url' => $this->url(array(
                        'module' => 'chat',
                        'controller' => 'index',
                        'action' => 'new',
                        'subject' => $this->subjectName,
                        'subject_id' => $this->subjectId
                    ), null, true)
            )
            ), null); ?>
    <?php elseif($this->canCreate):?>
        <?php echo $this->Actions('blog', array(
            array(
                'title' => _('Создать канал'), 
                'url' => $this->url(array(
                    'module' => 'chat', 
                    'controller' => 'index', 
                    'action' => 'new', 
                    'subject' => $this->subjectName, 
                    'subject_id' => $this->subjectId
                ), null, true)
            ))
        );?>
    <?php endif;?>
    <?php endif;?>
    <?php echo $this->headScript()?>
<?php endif;?>


<?php if ($this->viewType != 'table'):?>
<div class="chat">
    <div class="chat-channel">
        <div class="channel-header">
            <div class="channel-title"><?php echo $this->channel->name?></div>
            <div class="channel-controls">
                <?php if($this->canEdit):?>
                <a class="edit" href="<?php echo $this->url(array(
                    'module' => 'chat',
                    'controller' => 'index',
                    'action' => 'edit',
                    'subject' => $this->subjectName, 
                    'subject_id' => $this->subjectId,
                    'channel_id' => $this->channel->id
                ))?>"></a>
                <?php endif;?>
                <?php if($this->canDelete):?>
                <a class="delete" href="<?php echo $this->url(array(
                    'module' => 'chat',
                    'controller' => 'index',
                    'action' => 'delete',
                    'subject' => $this->subjectName, 
                    'subject_id' => $this->subjectId,
                    'channel_id' => $this->channel->id
                ))?>"></a>
                <?php endif;?>
            </div>
        </div>
        <div class="chat-controls">
            <?php echo $this->chat('HMChat', array(
                'channel' => $this->channel
            ))?>
        </div>
        <div class="chat-body">
            <?php include 'chat.tpl';?>
            <a href="<?php echo $this->url(array(
                'module' => 'chat',
                'controller' => 'index',
                'action' => 'view',
                'subject' => $this->subjectName, 
                'subject_id' => $this->subjectId,
                'channel_id' => $this->channel->id
            ))?>"><?php echo _('Все сообщения');?></a>
        </div>
        <div class="spacer"></div>
    </div>
    <?php include 'sidebar.tpl';?>
</div>
<?php else: ?>
    <?php echo $this->grid?>
<?php endif;?>
