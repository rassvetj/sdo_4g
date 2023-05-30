<div class="chat-side-bar">
    <?php if(!$this->channel->lesson_id):?>
    <div class="channels-list">
        <div class="label"><?php echo _('Каналы чата')?></div>
        <div class="spacer"></div>
        <input type="hidden" id="curChannel" value="<?php echo $this->channel->id ?>"/>
        <div id="channels-list">
        <?php foreach($this->channels as $channel):?>
        <a class="channel <?php if($channel->id == $this->channel->id):?>current<?php endif;?>" href="<?php echo $this->url(array(
                'module' => 'chat',
                'controller' => 'index',
                'action' => 'index',
                'subject' => $this->subjectName, 
                'subject_id' => $this->subjectId,
                'channel_id' => $channel->id
            ), null, true)?>"> <?php echo $channel->name?></a> (<?php echo count($channel->usersOnline);?>)<br/>
        <?php endforeach;?>
    </div>
    </div>
    <div id="channels-archive-container" <?php if(!count($this->archive)):?>style="display: none;"<?php endif;?>>
    <div class="hr"></div>
    <div class="channels-archive">
        <div class="label"><?php echo _('Архив каналов')?></div>
        <div class="spacer"></div>
            <div id="channels-archive">
        <?php foreach($this->archive as $channel):?>
        <span class="channel-date"><?php echo date('d.m.Y', strtotime($channel->start_date))?>
            <?php if($channel->start_time && $channel->end_time):?>
            <?php echo _('c') .' '. $channel->getStartTime() .' '. _('по') .' '. $channel->getEndTime()?>
            <?php endif;?>
            </span> <a class="channel" href="<?php echo $this->url(array(
                'module' => 'chat',
                'controller' => 'index',
                'action' => 'view',
                'subject' => $this->subjectName, 
                'subject_id' => $this->subjectId,
                    'channel_id' => $channel->id
                ), null, true)?>"><?php echo $channel->name?></a><br/>
        <?php endforeach;?>
    </div>
        </div>
    </div>
    <?php endif;?>
    <?php if(count($this->channel->usersOnline)):?>
    <div class="hr"></div>
    <div class="users-list">
        <div class="label"><?php echo $this->channel->name?></div>
        <div class="spacer"></div>
        <ul class="users">
        <?php foreach($this->channel->usersOnline as $user):?>
            <li id="us_<?php echo $user->MID?>" class="user">
                <a target="lightbox" class="lightbox card-link" href="<?php echo $this->url(array(
                    'module' => 'user',
                    'controller' => 'edit',
                    'action' => 'card',
                    'user_id' => $user->MID
                ), null, true)?>"><img align="left" src="/images/content-modules/grid/card.gif"/></a>
                <a class="login" href="javascript: void(null);"><?php echo $user->Login?></a><br/>
                <span class="name"><?php echo $user->getName()?></span>
            </li>
        <?php endforeach;?>
        </ul>
    </div>
    <?php endif;?>
</div>
<?php echo $this->chatSideBar('chatSideBar');?>
