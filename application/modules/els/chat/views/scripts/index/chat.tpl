<ul id="chat-list">
    <?php $even = 1;?>
    <?php foreach($this->messages as $message):?>
    <li id="msg_<?php echo $message->id?>" class="message <?php if($even == 1) { echo 'even'; } else { echo 'odd'; }?>">
        <span class="date"><?php echo date('d.m.Y, H:i', strtotime($message->created))?></span><br>
        <?php if($message->users):?>
        <a href="javascript: void(null);" rel="<?php echo $message->users->current()->MID?>" class="<?php if($message->users->current()->MID == $this->curUserId):?>current <?php endif;?>login">
            <?php echo $message->users->current()->Login?>
        </a>
        <?php else:
           echo _('Пользователь удален');
        endif;?><span class="pointer">></span><?php echo $message->message?>
    </li>
    <?php $even *= -1;?>
    <?php endforeach;?>
</ul>