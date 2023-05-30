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
        <div class="chat-body">
            <?php include 'chat.tpl';?>
        </div>
        <div class="spacer"></div>
    </div>
    <?php include 'sidebar.tpl';?>
</div>
