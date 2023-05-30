<?php if ($this->form):?>
<?php echo $this->form?>
<?else:?>  
	<p><?php echo sprintf(_('Будет создано %d записей. Все старые записи будут удалены.'), $this->importManager->getInsertCount())?></p>
    <br/>
    <?php echo $this->formButton('cancel', _('Отмена'), array('onClick' => 'window.location.href = "'.$this->serverUrl($this->url(array('module' => 'kbase', 'controller' => 'source', 'action' => 'import', 'source' => $this->source))).'"'))?>
    <?php echo $this->formButton('process', _('Далее'), array('onClick' => 'window.location.href = "'.$this->serverUrl($this->url(array('module' => 'kbase', 'controller' => 'source', 'action' => 'process', 'source' => $this->source))).'"'))?>
    <?php if ($this->importManager->getInsertCount()):?> 		
        <h3><?php echo _('Новые записи')?>:</h3>
        <br/>
        <table class="main" width="100%">
        <tr>
            <th><?=_('Направление подготовки')?></th>            
            <th><?=_('Шифр')?></th>            
            <th><?=_('Год начала обучения')?></th>            
            <th><?=_('Наименование дисциплины')?></th>            
            <th><?=_('Перечень электронных изданий указанных в рабочей программе дисциплины')?></th>            
            <th><?=_('Ссылки на электронные издания указанные в рабочей программе дисциплины')?></th>            
            <th><?=_('Перечень электронных образовательных ресурсов указанных в рабочей программе дисциплины')?></th>            
            <th><?=_('Ссылки на электронные образовательные ресурсы указанные в рабочей программе дисциплины	')?></th>   
        </tr>
        <?php foreach($this->importManager->getInsert() as $i):?>			                        
			<tr>
                <td><?=$i->direction; ?></td>				
                <td><?=$i->code; ?></td>				
                <td><?=$i->years; ?></td>				
                <td><?=$i->discipline; ?></td>				
                <td><?=$i->e_publishing; ?></td>				
                <td><?=$i->e_publishing_url; ?></td>				
                <td><?=$i->e_educational; ?></td>				
                <td><?=$i->e_educational_url; ?></td>				
            </tr>            
        <?php endforeach;?>
        </table>
	<?php endif; ?>
    <br/>
<?php endif;?>