<?php if ($this->news):?>
    <h3><?php echo $this->news->announce?>, <?php echo date('d.m.Y H:i', strtotime($this->news->created))?></h3>
    <br/>
    <p><?php echo $this->news->message?></p>
<?php else:?>
    <?php echo _('Новость не найдена')?>
<?php endif;?>