<div class='notify-switcher' data-evType='<?php echo $this->eventTypeId; ?>' data-notifyType='<?php echo $this->notifyTypeId; ?>'>
    <input class='radio' type='radio' name='radio<?php echo $this->eventTypeId.$this->notifyTypeId; ?>' id='radioOff<?php echo $this->eventTypeId.$this->notifyTypeId; ?>' value='0' <?php if (!$this->isActive) { echo 'checked="checked"'; } ?> />
	<label for="radioOff<?php echo $this->eventTypeId.$this->notifyTypeId; ?>">
		<?= _('Выкл') ?>
	</label>
    <input class='radio' type='radio' name='radio<?php echo $this->eventTypeId.$this->notifyTypeId; ?>' id='radioOn<?php echo $this->eventTypeId.$this->notifyTypeId; ?>' value='1' <?php if ($this->isActive) { echo 'checked="checked"'; } ?> />
	<label for="radioOn<?php echo $this->eventTypeId.$this->notifyTypeId; ?>">
		<?= _('Вкл') ?>
	</label>
</div>