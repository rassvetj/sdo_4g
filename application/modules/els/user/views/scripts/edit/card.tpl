<div class="ui-dialog pcard pcard_inline">
	<div class="ui-dialog-content-wrapper">
		<div class="lightdialog ui-dialog-content ui-widget-content" id="ui-lightdialog-2">

<?php echo $this->action('view', 'list', 'user', array('user_id' => $this->user->MID))?>

		</div>
	</div>
</div>
<?php if (strlen(strip_tags(trim($this->user->additionalData)))) :?>
<br>
<br>
<h2><?php echo _('Дополнительная информация о пользователе');?></h2>
<hr>
<div class="text-content">
<?php echo $this->user->additionalData?>
</div>
<?php endif; ?>
