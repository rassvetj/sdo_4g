<div class="congratulations">
    <div class="congr_title"><?php echo _('Автоматическое завершение курса')?></div>
    <div class="congr_img">
        <?php echo $this->confirmationImage($this->scale, $this->value);?>
    </div>
    <div class="congr_desc">
        <p><?php echo _('Уважаемый слушатель! Вы выполнили весь план занятий и Вам автоматически выставлена итоговая оценка за курс. Вы можете продолжать пользоваться материалами курса вплоть до окончания его срока действия (если применимо).');?></p>
    </div>
    <div class="congr_button">
        <button class="congr_sub" onClick="window.location.href = '<?php echo $this->redirectUrl?>';"><?php echo _('Продолжить');?></button>
    </div>
</div>