<?php if ($this->form):?>
<?php echo $this->form?>
<?else:?>
    <p><?php echo sprintf(_('Будут добавлены %d записи(ей), обновлены %d записи(ей), %d конфликта(ов) программы обучения'), $this->importManager->getInsertsCount(), $this->importManager->getUpdatesCount(), $this->importManager->getConflictedProgrammsStudyGroups())?></p>
    <br/>
    <?php echo $this->formButton('cancel', _('Отмена'), array('onClick' => 'window.location.href = "'.$this->serverUrl($this->url(array('module' => 'study-groups', 'controller' => 'list', 'action' => 'index'))).'"'))?>
    <?php echo $this->formButton('process', _('Далее'), array('onClick' => 'window.location.href = "'.$this->serverUrl($this->url(array('module' => 'study-groups', 'controller' => 'import', 'action' => 'process', 'source' => $this->source))).'"'))?>
    <br/>
	
	
	<?php if ($this->importManager->getCountConflictedProgrammsStudyGroups()):?>
        <?php $count = 1;?>
        <h3><?php echo _('Группы, назначенные на другие программы обучения')?>:</h3>
        <h3><?php echo _('Программа обучения этих групп не изменится')?>.</h3>
        <br/>
        <table class="main" width="100%">
        <tr>
			<th><?php echo _('ID группы')?></th>
			<th><?php echo _('Группа')?></th>
			<th><?php echo _('Текущйи ID программы')?></th>
			<th><?php echo _('Текущяя программа')?></th>
			<th><?php echo _('Новый ID программы')?></th>
			<th><?php echo _('Новая программа')?></th>
		</tr>
        <?php foreach($this->importManager->getConflictedProgrammsStudyGroups() as $conflict):?>            
            <tr>				
				<td><?php echo $conflict['source']->id_external?></td>
				<td><?php echo $conflict['source']->name?></td>
				<td><?=implode(', ', $conflict['additional']['current_programm_ids']);?></td>
				<td><?=implode(', ', $conflict['additional']['current_programm_names']);?></td>
				<td><?php echo $conflict['additional']['new_programm_id'];?></td>
				<td><?php echo $conflict['additional']['new_programm_name'];?></td>
			</tr>            
        <?php endforeach;?>
        </table>
    <?php endif;?>
    <br/>
	
	
	<?php if ($this->importManager->getCountLinkedProgrammsStudyGroups()):?>
        <?php $count = 1;?>
        <h3><?php echo _('Существующие группы, которые будут назначены на программу обучения')?>:</h3>        
        <br/>
        <table class="main" width="100%">
        <tr>
			<th><?php echo _('ID группы')?></th>
			<th><?php echo _('Группа')?></th>
			<th><?php echo _('ID программы')?></th>
			<th><?php echo _('Программа')?></th>
		</tr>
        <?php foreach($this->importManager->getLinkedProgrammsStudyGroups() as $link):?>            
            <tr>				
				<td><?php echo $link['destination']->id_external?></td>
				<td><?php echo $link['destination']->name?></td>				
				<td><?php echo $link['destination']->programm_id_external?></td>				
				<td><?php echo $link['destination']->programm_id_name?></td>				
			</tr>            
        <?php endforeach;?>
        </table>
    <?php endif;?>
    <br/>
	
	
	
    <?php if ($this->importManager->getInsertsCount()):?>
        <?php $count = 1;?>
        <h3><?php echo _('Будут добавлены следующие записи')?>:</h3>
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
    <?php if ($this->importManager->getUpdatesCount()):?>
        <?php $count = 1;?>
        <h3><?php echo _('Будут обновлены следующие записи')?>:</h3>
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
    <?php if ($this->importManager->getCount()): ?>
        <?php echo $this->formButton('cancel', _('Отмена'), array('onClick' => 'window.location.href = "'.$this->serverUrl($this->url(array('module' => 'study-groups', 'controller' => 'list', 'action' => 'index'))).'"'))?>
        <?php echo $this->formButton('process', _('Далее'), array('onClick' => 'window.location.href = "'.$this->serverUrl($this->url(array('module' => 'study-groups', 'controller' => 'import', 'action' => 'process', 'source' => $this->source))).'"'))?>
    <?php endif;?>
<?php endif;?>