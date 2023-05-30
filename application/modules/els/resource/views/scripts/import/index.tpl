<?php if ($this->form):?>
<?php echo $this->form?>
<?else:?>
<p><?php echo sprintf(_('Будут добавлены %d элемента(ов), обновлены %d элемента(ов)'), $this->importManager->getInsertsCount(), $this->importManager->getUpdatesCount())?></p>
<br/>
<?php echo $this->formButton('cancel', _('Отмена'), array('onClick' => 'window.location.href = "'.$this->serverUrl($this->url(array('module' => 'resource', 'controller' => 'list', 'action' => 'index'))).'"'))?>
<?php echo $this->formButton('process', _('Далее'), array('onClick' => 'window.location.href = "'.$this->serverUrl($this->url(array('module' => 'resource', 'controller' => 'import', 'action' => 'process'))).'"'))?>
<br/>
<?php if (count($this->importManager->getInserts())):?>
<?php $count = 1;?>
    <h3><?php echo _('Будут добавлены следующие элементы')?>:</h3>
    <br/>
    <table class="main" width="100%">
    <tr>
        <th><?php echo _('Код')?></th>
        <th><?php echo _('Название')?></th>
        <th><?php echo _('Описание')?></th>
    </tr>
    <?php foreach($this->importManager->getInserts() as $insert):?>
        <?php if ($count >= 1000) { echo "<tr><td colspan=\"2\">...</td></tr>"; break;}?>
        <tr><td><?php echo $insert->resource_id_external?></td><td><?php echo $insert->title?></td><td><?php echo $insert->description?></td></tr>
        <?php $count++;?>
    <?php endforeach;?>
    </table>
<?php endif;?>
<br/>
<?php if (count($this->importManager->getUpdates())):?>
<?php $count = 1;?>
    <h3><?php echo _('Будут обновлены следующие элементы')?>:</h3>
    <br/>
    <table class="main" width="100%">
    <tr>
        <th><?php echo _('Код элемента')?></th>
        <th><?php echo _('Название элемента')?></th>
        <th><?php echo _('Новое название элемента')?></th>
    </tr>
    <?php foreach($this->importManager->getUpdates() as $update): if($update['source'] == "" || $update['destination'] == ""){ continue;}?>
        <?php if ($count >= 1000) { echo "<tr><td colspan=\"2\">...</td></tr>"; break;}?>
        <tr>
            <td><?php echo $update['source']->resource_id_external?></td>
            <td><?php echo $update['source']->title?></td>
            <td><?php echo $update['destination']->title?></td>
        </tr>
        <?php $count++;?>
    <?php endforeach;?>
    </table>
<?php endif;?>
<br/>
<?php echo $this->formButton('cancel', _('Отмена'), array('onClick' => 'window.location.href = "'.$this->serverUrl($this->url(array('module' => 'resource', 'controller' => 'list', 'action' => 'index'))).'"'))?>
<?php echo $this->formButton('process', _('Далее'), array('onClick' => 'window.location.href = "'.$this->serverUrl($this->url(array('module' => 'resource', 'controller' => 'import', 'action' => 'process'))).'"'))?>
<?php endif; ?>