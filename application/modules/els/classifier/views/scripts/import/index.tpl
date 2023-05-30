<?php if ($this->form):?>
    <?php echo $this->form?>
<?else:?>
<p><?php echo sprintf(_('Будут добавлены %d рубрик(а) и удалены %d рубрик(а)'), $this->importManager->getInsertsCount(), $this->importManager->getDeletesCount())?></p>
<br/>
<?php echo $this->formButton('cancel', _('Отмена'), array('onClick' => 'window.location.href = "'.$this->serverUrl($this->url(array('module' => 'classifier', 'controller' => 'list', 'action' => 'index', 'type' => $this->type))).'"'))?>
<?php echo $this->formButton('process', _('Далее'), array('onClick' => 'window.location.href = "'.$this->serverUrl($this->url(array('module' => 'classifier', 'controller' => 'import', 'action' => 'process', 'source' => $this->source, 'type' => $this->type))).'"'))?>
<br/>
<?php if (count($this->importManager->getInserts())):?>
<?php $count = 1;?>
    <h3><?php echo _('Будут добавлены следующие рубрики')?>:</h3>
    <br/>
    <table class="main" width="100%">
    <tr><th><?php echo _('Название')?></th></tr>
    <?php foreach($this->importManager->getInserts() as $insert):?>
        <?php if ($count >= 1000) { echo "<tr><td>...</td></tr>"; break;}?>
        <tr><td><?php echo $insert->name?></td></tr>
        <?php $count++;?>
    <?php endforeach;?>
    </table>
<?php endif;?>
<br/>
<?php if (count($this->importManager->getUpdates())):?>
<?php $count = 1;?>
    <h3><?php echo _('Будут обновлены следующие рубрики')?>:</h3>
    <br/>
    <table class="main" width="100%">
    <tr>
        <th><?php echo _('Было')?></th>
        <th><?php echo _('Стало')?></th>
    </tr>
    <?php foreach($this->importManager->getUpdates() as $update):?>
        <?php if ($count >= 1000) { echo "<tr><td colspan=\"2\">...</td></tr>"; break;}?>
        <tr>
            <td><?php echo $update['source']->name?></td>
            <td><?php echo $update['destination']->name?></td>
        </tr>
        <?php $count++;?>
    <?php endforeach;?>
    </table>
<?php endif;?>
<?php if (count($this->importManager->getDeletes())):?>
<?php $count = 1;?>
    <h3><?php echo _('Будут удалены следующие рубрики')?>:</h3>
    <br/>
    <table class="main" width="100%">
    <tr>
        <th><?php echo _('Название')?></th>
    </tr>
    <?php foreach($this->importManager->getDeletes() as $delete):?>
        <?php if ($count >= 1000) { echo "<tr><td>...</td></tr>"; break;}?>
        <tr>
            <td><?php echo $delete->name?></td>
        </tr>
        <?php $count++;?>
    <?php endforeach;?>
    </table>
<?php endif;?>
<?php echo $this->formButton('cancel', _('Отмена'), array('onClick' => 'window.location.href = "'.$this->serverUrl($this->url(array('module' => 'classifier', 'controller' => 'list', 'action' => 'index', 'type' => $this->type))).'"'))?>
<?php echo $this->formButton('process', _('Далее'), array('onClick' => 'window.location.href = "'.$this->serverUrl($this->url(array('module' => 'classifier', 'controller' => 'import', 'action' => 'process', 'source' => $this->source, 'type' => $this->type))).'"'))?>
<?php endif;?>