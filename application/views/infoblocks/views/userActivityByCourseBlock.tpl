<?php $this->headLink()->appendStylesheet( $this->serverUrl('/css/infoblocks/schedule-accordion/schedule.css') ); ?>
<div class="schedule-accordion" style="text-align:center">
<?php if ($this->sec[$this->subject->subid]!=0): ?>
<?php echo _('Продолжительность обучения, часов') . ': ' . round($this->sec[$this->subject->subid]/3600, 1);?><hr>
<?php endif; ?>
<?php if ($this->logs[$this->subject->subid] != 0): ?>
<?php echo _('Количество попыток прохождения тестов') . ': ' . $this->logs[$this->subject->subid];?><hr>
<?php endif; ?>
<?php if ($this->my_mess[$this->subject->subid] != 0): ?>
<?php echo _('Количество сообщений в форуме') . ': ' . $this->my_mess[$this->subject->subid];?><br />
<?php endif; ?>
<?php if ($this->my_mess[$this->subject->subid] == 0 && $this->sec[$this->subject->subid]==0 && $this->logs[$this->subject->subid]==0): ?>
<?php echo _('По данному курсу нет активности')?>
<?php endif; ?>  
</div>

