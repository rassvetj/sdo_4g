<?php if ($this->fact->text):?>
<div>
<?php echo $this->fact->text;?>
</div>
<? else: ?>
<div><p><? echo _('Отсутствуют данные для отображения'); ?></p></div>
<? endif; ?>
<?php if($this->isModerator):?>
<div class="bottom-links">
<hr/>
<a href="<?php echo $this->url(array('module' => 'infoblock', 'action' => 'index', 'controller' => 'interesting-fact'));?>"><?php echo _('Редактировать'); ?></a>
</div>    
<?php endif;?>