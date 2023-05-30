<div><?php echo _('Дата проведения:')?> <span style="align:right"><?php echo $this->date;?></span></div>
<div><?php echo _('Место проведения:')?> <span style="align:right"><?php echo $this->place?></span></div>
<!--div><?php echo _('Провайдер:')?> <span style="align:right"><?php echo $this->provider?></span></div-->
<div><?php echo _('Преподаватель:')?> <span style="align:right"><?php echo $this->teacher?></span></div>
<?php if($this->content != ''):?>
	<?php echo $this->content?>
<?php else:?>
    <?php echo _('Отсутствуют данные для отображения')?>
<?php endif;?>