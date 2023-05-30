<h3><?=$this->caption;?></h3>
Период: <?=$this->period;?>
<?php if(count($this->content)) : ?>
<table style="margin: 0 auto; border-collapse: collapse; text-align: center;">
	<tr>
		<th rowspan="2" style="border: 1px solid black; font-weight: bold; vertical-align: middle;">ФИО преподавателя</th>					
		<th rowspan="2" style="border: 1px solid black; font-weight: bold; vertical-align: middle;">Кафедра</th>					
		<th rowspan="2" style="border: 1px solid black; font-weight: bold; vertical-align: middle;">Факультет</th>					
		<th colspan="2" style="border: 1px solid black; font-weight: bold; vertical-align: middle;">Нарушения сроков реагирования</th>	
		<th rowspan="2" style="border: 1px solid black; font-weight: bold; vertical-align: middle;">Заданий без оценки</th>		
		<th rowspan="2" style="border: 1px solid black; font-weight: bold; vertical-align: middle;">Кол-во непроверенных  работ студентов</th>		
	</tr>
	<tr>
		<th style="border: 1px solid black; font-weight: bold; vertical-align: middle;">всего</th>					
		<th style="border: 1px solid black; font-weight: bold; vertical-align: middle;">средняя продолжительность нарушения сроков, дней</th>
	</tr>
	<tr></tr>
	<?php foreach($this->content as $i): ?>
	<tr>		
		<td style="border: 1px solid black; padding: 3px;"><?=$i['fio'];?></td>																													
		<td style="border: 1px solid black; padding: 3px;"><?=$i['department'];?></td>																													
		<td style="border: 1px solid black; padding: 3px;"><?=$i['faculty'];?></td>																													
		<td style="border: 1px solid black; padding: 3px;"><?=$i['count_violations'];?></td>																													
		<td style="border: 1px solid black; padding: 3px;"><?=$i['avg'];?></td>
		<td style="border: 1px solid black; padding: 3px;"><?=$i['notBall'];?></td>
		<td style="border: 1px solid black; padding: 3px;"><?=$i['newWorks'];?></td>
	</tr>
	<?php endforeach; ?>
</table>
<?php else : ?>
	нет данных
<?php endif; ?>