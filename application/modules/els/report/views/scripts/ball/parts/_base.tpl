<a href="<?=$this->baseUrl($this->url(array('module' => 'report', 'controller' => 'ball', 'action' => 'csv')));?>" target="_blank">
	<button value="Скачать" class="ui-button ui-widget ui-state-default ui-corner-all ui-state-hover"><?=_('Скачать')?></button>
</a>
<table>
	<thead>
		<tr>
			<?php foreach($this->fields as $key => $f):?>
				<?php if($key == 22){  continue; } # ссылка на сессию доступна только для выгрузки. Тут она в имени сессии ?>
				<th><?=$key?></th>
			<?php endforeach;?>
		</tr>	
		<tr>			
			<?php foreach($this->fields as $key => $f):?>
				<?php if($key == 22){  continue; } # ссылка на сессию доступна только для выгрузки. Тут она в имени сессии ?>
				<th><?=$f?></th>			
			<?php endforeach;?>
		</tr>	
	</thead>
	<tbody>
		<?php if(empty($this->data)): ?>
			<tr><td colspan="100%"><b><?=_('Нет данных')?></b></td></tr>
		<?php else: ?>
			<?php foreach($this->data as $i): ?>
			<tr>				
				<!--1 --><td><?=$i['tutor_name']?></td>
				<!--2 --><td><?=$i['roles']?></td>
				<!--2.2--><td><?=$i['roles_lessons']?></td>
				<!--3 --><td><?=$i['subject_faculty']?></td>
				<!--4 --><td><?=$i['subject_chair']?></td>
				<!--5 --><td><a target="_blank" href="<?=$this->baseUrl($this->url(array('module' => 'subject', 'controller' => 'index', 'action' => 'card', 'subject_id' => $i['subject_id'])));?>"><?=$i['subject_name'];?></a></td>
				<!--6 --><td><?=$i['subject_external_id']?></td>				
				<!--7 --><td><?=date('d.m.Y', strtotime($i['subject_begin']))?></td>
				<!--8 --><td><?=date('d.m.Y', strtotime($i['subject_end']))?></td>							
				<!--9 --><td><?=$i['zet']?></td>
				<!--10--><td><?=$i['semester']?></td>
				<!--11--><td><?=$i['countStudents']?></td>
				<!--12--><td><?=empty($i['groups']) ? '' : implode(',<br>', $i['groups'])?></td>
				<!--13--><td><?=empty($i['programms']) ? '' : implode(',<br>', $i['programms'])?></td>
				<!--14--><td><?=$i['percent_lecture']?></td>
				<!--15--><td><?=$i['percent_practice']?></td>
				<!--15--><td><?=$i['percent_lab']?></td>
				<!--16--><td><?=$i['percent_boundary_control']?></td>
				<!--17--><td><?=$i['boundary_control_detail']?></td>
				<!--19--><td><?=$i['percent_ipz']?></td>
				<!--20--><td><?=$i['percent_plan_ready']?></td>				
				<!--21--><td><?=$i['subject_isDO']?></td>				
				<!--23--><td><?=$i['subject_lection']?></td> 
				<!--24--><td><?=$i['subject_practice']?></td>
				<!--25--><td><?=$i['subject_lab']?></td>
			</tr>
			<?php endforeach; ?>
		<?php endif; ?>
	</tbody>
</table>
<a href="<?=$this->baseUrl($this->url(array('module' => 'report', 'controller' => 'ball', 'action' => 'csv')));?>" target="_blank">
	<button value="Скачать" class="ui-button ui-widget ui-state-default ui-corner-all ui-state-hover"><?=_('Скачать')?></button>
</a>