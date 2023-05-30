<?php if(count($this->content)) : ?>
<table class = "workload">
	<tr>
		<th>ФИО преподавателя</th>
		<th>Кафедра</th>
		<th>Факультет</th>
		<th>Дисциплина</th>
		<th>Номер группы</th>
		<th>Количество студентов</th>				
		<th>Выполнено педагогической нагрузки, <?=($this->report_type_id == HM_Workload_WorkloadModel::REPORT_TYPE_ALL) ? ('проверенных работ') : ('часов');?></th>
	</tr>		
	
	<?php foreach($this->content as $i): ?>
	<tr>					
		<td><?=$i['fio'];?></td>														
		<td><?=$i['department'];?></td>														
		<td><?=$i['faculty'];?></td>																
		<td><a target="_blank" href="<?=$this->url(array('module' => 'subject', 'controller' => 'index', 'action' => 'card', 'list-switcher' => 'current', 'subject_id' => $i['subject_id']), 'default', true);?>"><?=$i['name'];?></a></td>		
		<td><?=$i['groups'];?></td>														
		<td><?=$i['students'];?></td>																
		<td><?=$i['workloadTotal'];?></td>			
	</tr>
	<?php endforeach; ?>
</table>
<?php 
$params = array();
if(count($this->urlParams)){
	$params = $this->urlParams;
}
$params['controller'] = 'workload';
$params['action'] = 'get-workload-report';
$params['export'] = 'excel';

?>
<a href="<?=$this->baseUrl($this->url($params));?>"><button>Выгрузить в Excel</button></a>
<?php else : ?>
	нет данных
<?php endif; ?>