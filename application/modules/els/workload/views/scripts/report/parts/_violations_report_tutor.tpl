<?php if(count($this->content)) : ?>
<table class = "workload">
	<tr>
		<th rowspan="2">Дисциплина</th>					
		<th rowspan="2">Номер группы</th>					
		<th rowspan="2">Количество студентов</th>							
		<th rowspan="2">Заданий без оценки</th>
		<th colspan="2">Направление приветственного письма</th>
		<th colspan="2">Проверка практических заданий</th>
		<th colspan="2">Консультирование на предметном форуме</th>
		<th colspan="2">Направление мотивированного заключения</th>
		<th rowspan="2">Всего нарушений</th>				
		<th rowspan="2">Ссылки на нарушения</th>	
		<th rowspan="2">Кол-во непроверенных  работ студентов</th>				
	</tr>
		<th>всего нарушений</th>
		<th>средняя продолжительность нарушения сроков, дней</th>
		
		<th>всего нарушений</th>
		<th>средняя продолжительность нарушения сроков, дней</th>
		
		<th>всего нарушений</th>
		<th>средняя продолжительность нарушения сроков, дней</th>
		
		<th>всего нарушений</th>
		<th>средняя продолжительность нарушения сроков, дней</th>
	<tr>
	</tr>
	<?php foreach($this->content as $i): ?>
	<tr>		
		<td><a target="_blank" href="<?=$this->url(array('module' => 'subject', 'controller' => 'index', 'action' => 'card', 'list-switcher' => 'current', 'subject_id' => $i['subject_id']), 'default', true);?>"><?=$i['name'];?></a></td>		
		<td><?=$i['groups'];?></td>															
		<td><?=$i['students'];?></td>												
		<td><?=$i['notBall'];?></td>		
		<td><?=$i['vi_MessageCount'];?></td>
		<td><?=$i['T_message'];?></td>
		<td><?=$i['vi_SubjectCount'];?></td>
		<td><?=$i['T_subject_avg'];?></td>
		<td><?=$i['vi_ForumCount'];?></td>
		<td><?=$i['T_forum_avg'];?></td>
		<td><?=$i['vi_VedomostCount'];?></td>
		<td><?=$i['T_vedomost'];?></td>
		<td><?=$i['count_violations'];?></td>		
		<td><?php 
			if(count($i['urls'])){
				foreach($i['urls'] as $l){
					echo '<a href="'.$l['url'].'" target="_blank">'.$l['name'].'</a>&nbsp;';
				}
			}
		 ?></td>
		<td><?=$i['newWorks'];?></td>		 
	</tr>
	<?php endforeach; ?>
</table>
<?php 
$params = array();
if(count($this->urlParams)){
	$params = $this->urlParams;
}
$params['controller'] = 'violations';
$params['action'] = 'get-violations-report';
$params['export'] = 'excel';

?>
<a href="<?=$this->baseUrl($this->url($params));?>"><button>Выгрузить в Excel</button></a>
<?php else : ?>
	нет данных
<?php endif; ?>