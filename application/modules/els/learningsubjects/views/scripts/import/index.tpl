<?php if ($this->form):?>
<?php echo $this->form?>
<?else:?>
    <p><?php echo sprintf(_('Будут добавлены %d записи(ей) и обновлены %d записи(ей)'), $this->importManager->getInsertsCount(), $this->importManager->getUpdatesCount())?></p>
    <br/>
    <?php echo $this->formButton('cancel', _('Отмена'), array('onClick' => 'window.location.href = "'.$this->serverUrl($this->url(array('module' => 'learningsubjects', 'controller' => 'list', 'action' => 'index'))).'"'))?>
    <?php echo $this->formButton('process', _('Далее'), array('onClick' => 'window.location.href = "'.$this->serverUrl($this->url(array('module' => 'learningsubjects', 'controller' => 'import', 'action' => 'process', 'source' => $this->source))).'"'))?>
    <br/>
    <?php if ($this->importManager->getInsertsCount()):?>
        <?php $count = 1;?>
        <h3><?php echo _('Будут добавлены следующие записи')?>:</h3>
        <br/>
        <table class="main" width="100%">
        <tr>
			<th><?php echo _('ID предмета из 1С')?></th>
			<th><?php echo _('Название')?></th>
		</tr>
        <?php foreach($this->importManager->getInserts() as $insert):?>			
            <?php if ($count >= 1000) { echo "<tr><td>...</td></tr>"; break;}?>
            <tr>
				<td><?=$insert->id_external?></td>
				<td><?=$insert->name?></td>
			</tr>
            <?php $count++;?>
        <?php endforeach;?>
        </table>
    <?php endif;?>
    <br/>
    <?php if ($this->importManager->getUpdatesCount()):?>
        <?php $count = 1;?>
        <h3><?php echo _('Будут обновлены следующие записи')?>:</h3>
        <br/>
        <table class="main" width="100%">
        <tr>
            <th><?=_('ID предмета из 1С')?></th>
            <th><?=_('Было')?></th>
            <th><?=_('Стало')?></th>
        </tr>
        <?php foreach($this->importManager->getUpdates() as $update):?>		
            <?php if ($count >= 1000) { echo "<tr><td colspan=\"2\">...</td></tr>"; break;}?>
            <tr>
                <td><?=$update['source']->id_external?></td>
                <td><?=$update['source']->name?></td>
                <td><?=$update['destination']->name?></td>
            </tr>
            <?php $count++;?>
        <?php endforeach;?>
        </table>
    <?php endif;?>
    <?php if ($this->importManager->getCount()): ?>
        <?php echo $this->formButton('cancel', _('Отмена'), array('onClick' => 'window.location.href = "'.$this->serverUrl($this->url(array('module' => 'learningsubjects', 'controller' => 'list', 'action' => 'index'))).'"'))?>
        <?php echo $this->formButton('process', _('Далее'), array('onClick' => 'window.location.href = "'.$this->serverUrl($this->url(array('module' => 'learningsubjects', 'controller' => 'import', 'action' => 'process', 'source' => $this->source))).'"'))?>
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