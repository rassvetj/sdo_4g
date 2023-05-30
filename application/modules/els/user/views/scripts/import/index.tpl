<?php if ($this->form):?>
<?php echo $this->form?>
<?else:?>
    <p><?php echo sprintf(_('Будут добавлены %d пользователя(ей) и обновлены %d пользователя(ей)'), $this->importManager->getInsertsCount(), $this->importManager->getUpdatesCount())?></p>
    <br/>
    <?php echo $this->formButton('cancel', _('Отмена'), array('onClick' => 'window.location.href = "'.$this->serverUrl($this->url(array('module' => 'user', 'controller' => 'list', 'action' => 'index'))).'"'))?>
    <?php echo $this->formButton('process', _('Далее'), array('onClick' => 'window.location.href = "'.$this->serverUrl($this->url(array('module' => 'user', 'controller' => 'import', 'action' => 'process', 'source' => $this->source))).'"'))?>
    <br/>
    <?php if ($this->importManager->getInsertsCount()):?>
        <?php $count = 1;?>
        <h3><?php echo _('Будут добавлены следующие пользователи')?>:</h3>
        <br/>
        <table class="main" width="100%">
        <tr><th><?php echo _('ФИО')?></th></tr>
        <?php foreach($this->importManager->getInserts() as $insert):?>
            <?php if ($count >= 1000) { echo "<tr><td>...</td></tr>"; break;}?>
            <tr><td><?php echo $insert->getName()?></td></tr>
            <?php $count++;?>
        <?php endforeach;?>
        </table>
    <?php endif;?>
    <br/>
    <?php if ($this->importManager->getUpdatesCount()):?>
        <?php $count = 1;?>
        <h3><?php echo _('Будут обновлены следующие пользователи')?>:</h3>
        <br/>
        <table class="main" width="100%">
        <tr>
            <th><?php echo _('Было')?></th>
            <th><?php echo _('Стало')?></th>
        </tr>
        <?php foreach($this->importManager->getUpdates() as $update):?>
            <?php if ($count >= 1000) { echo "<tr><td colspan=\"2\">...</td></tr>"; break;}?>
            <tr>
                <td><?php echo $update['source']->getName()?></td>
                <td><?php echo $update['destination']->getName()?></td>
            </tr>
            <?php $count++;?>
        <?php endforeach;?>
        </table>
    <?php endif;?>
	
	<?php if ($this->importManager->getDoubleRowsCount()):?>
        <?php $count = 1;?>
        <h3><?php echo _('Задвоение по коду студента')?>:</h3>
        <br/>
        <table class="main" width="100%">
        <tr>
            <th><?php echo _('Код студента')?></th>
            <th><?php echo _('ФИО')?></th>
        </tr>
        <?php foreach($this->importManager->getDoubleRows() as $d):?>
            <?php if ($count >= 1000) { echo "<tr><td colspan=\"2\">...</td></tr>"; break;}?>            
			<tr>
                <td><?=$d->mid_external;?></td>
                <td><?=$d->LastName.' '.$d->FirstName.' '.$d->Patronymic;?></td>
            </tr>
            <?php $count++;?>
        <?php endforeach;?>
        </table>
    <?php endif;?>
	
    <?php if ($this->importManager->getCount()): ?>
        <?php echo $this->formButton('cancel', _('Отмена'), array('onClick' => 'window.location.href = "'.$this->serverUrl($this->url(array('module' => 'user', 'controller' => 'list', 'action' => 'index'))).'"'))?>
        <?php echo $this->formButton('process', _('Далее'), array('onClick' => 'window.location.href = "'.$this->serverUrl($this->url(array('module' => 'user', 'controller' => 'import', 'action' => 'process', 'source' => $this->source))).'"'))?>
    <?php endif;?>
<?php endif;?>
<?php $this->inlineScript()->captureStart()?>
	$( document ).ready(function() {
		var buttons = $('button[name="process"]');
		
		$(buttons).click(function(){
			$(buttons).prop("disabled", true);
		});
	});
<?php $this->inlineScript()->captureEnd()?>