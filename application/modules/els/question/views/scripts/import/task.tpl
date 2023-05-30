<?php if ($this->form):?>
    <?php echo $this->form?>
<?else:?>
<p><?php echo sprintf(_('Будут добавлены %d вопроса(ов)'), $this->importManager->getInsertsCount())?></p>
<br/>
<?php echo $this->formButton('cancel', _('Отмена'), array('onClick' => 'window.location.href = "'.$this->returnUrl.'"'))?>
<?php echo $this->formButton('process', _('Далее'), array('onClick' => 'window.location.href = "'.$this->serverUrl($this->url(array('module' => 'question', 'controller' => 'import', 'action' => 'taskprocess', 'source' => $this->source))).'"'))?>
<br/>
<?php if (count($this->importManager->getInserts())):?>
<?php $count = 1;?>
    <h3><?php echo _('Будут добавлены следующие вопросы')?>:</h3>
    <br/>
    <table class="main" width="100%">
    <tr><th><?php echo _('Вопрос')?></th>
    <?php foreach($this->importManager->getInserts() as $insert):?>
        <?php if ($count >= 1000) { echo "<tr><td colspan=1>...</td></tr>"; break;}?>
        <tr>
            <td><?php echo $insert->title?></td>            
        </tr>
        <?php $count++;?>
    <?php endforeach;?>
    </table>
<?php endif;?>
<br/>
<?php echo $this->formButton('cancel', _('Отмена'), array('onClick' => 'window.location.href = "'.$this->returnUrl.'"'))?>
<?php echo $this->formButton('process', _('Далее'), array('onClick' => 'window.location.href = "'.$this->serverUrl($this->url(array('module' => 'question', 'controller' => 'import', 'action' => 'taskprocess', 'source' => $this->source))).'"'))?>

<?endif;?>