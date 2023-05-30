<?php if ($this->form):?>
<?php echo $this->form?>
<?php else:?>
<p><?php echo sprintf(_('Были добавлена %d классификаций'), count($this->processed))?></p>
<br/>
<?php echo $this->formButton('next', _('Далее'), array('onClick' => 'window.location.href = "'.$this->serverUrl($this->url(array('module' => 'subject', 'controller' => 'list', 'action' => 'index'))).'"'))?>
<br/>
<?php if (count($this->processed)):?>
    <table class="main" width="100%">
        <tr><th><?php echo _('Учебный курс')?></th><th><?php echo _('Рубрика')?></th></tr>
        <?php foreach($this->processed as $subject => $class):?>
        <tr><td><?php echo $subject?></td><td><?php echo $class?></td></tr>
        <?php endforeach;?>
    </table>
<?php else:?>
    <?php echo _('Нет данных для отображения')?>
<?php endif;?>
<?php endif;?>