<?php
//var_dump($this->importManager->restoreFromCache());
?>

<?php if ($this->form):?>
<?php echo $this->form?>
<?else:?>
<p><?php echo sprintf(_('Будут добавлены %d элемента(ов), обновлены %d элемента(ов), удалены %d элемента(ов), обновлены %d штатных едениц'),
        $this->importManager->getInsertsCount(), $this->importManager->getUpdatesCount(), $this->importManager->getDeletesCount(), $this->importManager->getPositionsCount())?></p>
<br/>
<?php echo $this->formButton('cancel', _('Отмена'), array('onClick' => 'window.location.href = "'.$this->serverUrl($this->url(array('module' => 'orgstructure', 'controller' => 'list', 'action' => 'index'))).'"'))?>
<?php echo $this->formButton('process', _('Далее'), array('onClick' => 'window.location.href = "'.$this->serverUrl($this->url(array('module' => 'orgstructure', 'controller' => 'import', 'action' => 'process'))).'"'))?>
<br/>
<?php if (count($this->importManager->getInserts())):?>
<?php $count = 1;?>
    <h3><?php echo _('Будут добавлены следующие элементы')?>:</h3>
    <br/>
    <table class="main" width="100%">
    <tr><th><?php echo _('Название подразделения/должности')?></th><th><?php echo _('В должности')?></th></tr>
    <?php foreach($this->importManager->getInserts() as $insert):?>
        <?php if ($count >= 1000) { echo "<tr><td colspan=\"2\">...</td></tr>"; break;}?>
        <tr><td><?php echo $insert->name?></td><td><?php if ($insert->getUser()):?><?php echo $insert->getUser()->getName()?><?endif;?></td></tr>
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
        <th><?php echo _('Название исходного подразделения/должности')?></th><th><?php echo _('Назначен')?></th>
        <th><?php echo _('Название подразделения/должности')?></th><th><?php echo _('Назначен')?></th>
    </tr>
    <?php foreach($this->importManager->getUpdates() as $update): if($update['source'] == "" || $update['destination'] == ""){ continue;}?>
        <?php if ($count >= 1000) { echo "<tr><td colspan=\"2\">...</td></tr>"; break;}?>
        <tr>
            <td><?php echo $update['source']->name?></td><td><?php if ($update['source']->getUser()):?><?php echo $update['source']->getUser()->getName()?><?endif;?></td>
            <td><?php echo $update['destination']->name?></td><td><?php if ($update['destination']->getUser()):?><?php echo $update['destination']->getUser()->getName()?><?endif;?></td>
        </tr>
        <?php $count++;?>
    <?php endforeach;?>
    </table>
<?php endif;?>
<br/>
<?php if (count($this->importManager->getDeletes())):?>
<?php $count = 1;?>
    <h3><?php echo _('Будут удалены следующие элементы')?>:</h3>
    <br/>
    <table class="main" width="100%">
    <tr><th><?php echo _('Название подразделения/должности')?></th><th><?php echo _('В должности')?></th></tr>
    <?php foreach($this->importManager->getDeletes() as $delete):?>
        <?php if ($count >= 1000) { echo "<tr><td colspan=\"2\">...</td></tr>"; break;}?>
        <tr><td><?php echo $delete->name?></td><td><?php if ($delete->getUser()):?><?php echo $delete->getUser()->getName()?><?endif;?></td></tr>
        <?php $count++;?>
    <?php endforeach;?>
    </table>
<?php endif;?>
<br/>
<?php echo $this->formButton('cancel', _('Отмена'), array('onClick' => 'window.location.href = "'.$this->serverUrl($this->url(array('module' => 'orgstructure', 'controller' => 'list', 'action' => 'index'))).'"'))?>
<?php echo $this->formButton('process', _('Далее'), array('onClick' => 'window.location.href = "'.$this->serverUrl($this->url(array('module' => 'orgstructure', 'controller' => 'import', 'action' => 'process'))).'"'))?>
<?php endif; ?>