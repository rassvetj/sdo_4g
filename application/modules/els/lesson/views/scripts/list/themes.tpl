<?php if ($this->themes):?>
    <?php foreach($this->themes as $theme => $count):?>
    <dt><?php echo $this->escape(trim($theme)?$theme:_('Без темы'))?>&nbsp;</dt><dd> <input type="text" value="<?php echo $count?>" name="questions_by_theme[<?php echo $this->escape($theme)?>]" /></dd>
    <?php endforeach;?>
<?php else:?>
<?php echo _('Темы отсутствуют')?>
<?php endif;?>