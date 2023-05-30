<style>
	.tbl-result th, .tbl-result td {
		border: 1px solid #B2C0C9;
		vertical-align: middle;
		padding: 3px;
		text-align: center;
	}
	.tbl-result {
		border-collapse: collapse;
	}
	.tbl-result td {
		padding: 3px;
	}
</style>
<? if(empty($this->data)) : ?>
	Нет данных
<? else : ?>
	<p>Тьютор, выделенный красным, недоступен для данного студента</p>
	<br />
	<table class="tbl-result">
		<tr>
			<th><?=_('Студент')?></th>
			<th><?=_('Код 1С')?></th>
			<th><?=_('Группа')?></th>
			<th><?=_('Дата назначения курса (не позднее)')?></th>
			<th><?=_('Студент: назначен')?></th>
			<th><?=_('Студент: завершил')?></th>
			<th><?=_('Студент: продление 1')?></th>
			<th><?=_('Студент: продление 2')?></th>
			<th><?=_('Сессия')?></th>
			<th><?=_('Cессия: код')?></th>
			<th><?=_('Cессия: семестр')?></th>
			<th><?=_('Cессия: начало')?></th>
			<th><?=_('Cессия: окончание')?></th>
			<th><?=_('Cессия: продление 1')?></th>
			<th><?=_('Cессия: продление 2')?></th>
			<th><?=_('Cессия для студента')?></th>
			<th><?=_('Тьюторы')?></th>
			<th><?=_('Оценка')?></th>			
			<th><?=_('Решение на проверку')?></th>
		</tr>
		<?php $count = 0; ?>
		<? foreach($this->data as $i) : ?>
			<?php 
				$count++;
				if($count > 1000){
					echo '<tr><td colspan="100%" style="font-weight: bold; color: red; font-size: 14px; padding: 10px;">
								Показано 1000 из '.count($this->data).' записей. Для полного просмотра выгрузите данные в файл
						  </td></tr>';
					break;
				}
				
				$lastMessage = _('Нет');
				if($i['hasSolutionToCheck']){
					$lastMessage = '<span style="color:red;">' . _('Да') . '</span>';
				} elseif($i['hasTeacherAnswer']){
					$lastMessage = '<span style="color:red;">' . _('Ответ преподавателя') . '</span>';
				}
				
			?>
			<tr >
				<td><?=$i['student_fio'];?></td>
				<td><?=$i['student_mid_external'];?></td>
				<td><?=$i['student_group_name'];?></td>
				<td><?=$i['lesson']['min_date_assign']?></td>
				<td><?=$i['student_time_registered'];?></td>
				<td><?=$i['student_time_graduated'];?></td>
				<td><?=$i['student_time_ended_debtor'];?></td>
				<td><?=$i['student_time_ended_debtor_2'];?></td>
				<td style="text-align: left;">
					<a target="_blank" href="<?=$this->url(array('module' => 'subject', 'controller' => 'index', 'action' => 'card', 'subject_id' => $i['subject_id']))?>">
						<?=$i['subject_name'];?>
					</a>
				</td>
				<td><?=$i['subject_external_id'];?></td>
				<td><?=$i['subject_semester'];?></td>
				<td><?=$i['subject_begin'];?></td>
				<td><?=$i['subject_end'];?></td>
				<td><?=$i['subject_time_ended_debt'];?></td>
				<td><?=$i['subject_time_ended_debt_2'];?></td>
				<td style="background-color:<?=$i['color']?>" ><?=$i['subject_status_for_student'];?></td>
				<td><?php
					if(!empty($i['tutors'])){
						$tutors_str = '';
						foreach($i['tutors'] as $tutor){
							$color = $tutor['available'] ? '' : 'red';
							$tutors_str .= ', <span style="color:'.$color.'">'.$tutor['fio'].'</span>';
						}
						echo trim($tutors_str, ',');
					}
				?></td>
				<td><?php
					if(!empty($i['mark'])){						
						echo $i['mark']['mark'].'<br />('.$i['mark']['mark_current'].'/'.$i['mark']['mark_landmark'].')';
					}
				?></td>
				<td>
					<?=$lastMessage?>
				</td>
			</tr>
		<? endforeach; ?>
	</table>
	<a href="<?=$this->baseUrl($this->url(array('module' => 'report', 'controller' => 'student-subjects', 'action' => 'get-csv')));?>" target="_blank"><button>Выгрузить в csv</button></a>
<? endif; ?>