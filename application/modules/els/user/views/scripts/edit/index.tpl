<?php if ($this->user && ($this->user->Registered == 1)):?>
<?php $this->notifications(array(array('message' => _('Вы зашли в систему первый раз. Пожалуйста измените свой пароль и отредактируйте персональную информацию.'), 'type' => HM_Notification_NotificationModel::TYPE_NOTICE)))?>
<?php endif;?>
<?php echo $this->form?>

