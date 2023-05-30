			<? foreach($this->content as $i) : ?>
				<tr>
					<td><?=$i['fio'];?></td>
					<td><?=$i['departments'];?></td>
					<td><?=$i['positions'];?></td>
					<td><?=$i['group_id_external'];?></td>
					<td><?=$i['email'];?></td>
					<td><?=$i['courses'];?></td>
					<td><?=$i['time_assign'];?></td>
					<td><?=$i['time_begin'];?></td>
					<td><?=$i['time_ended'];?></td>
					<td><?=$i['dete_debtor'];?></td>
					<td><?=$i['status'];?></td>
					<td><?=$i['mark'];?></td>
					<td><?=$i['ball_date'];?></td>
					<td><?=$i['type_last_message'];?></td>
					<td><?=$i['last_message_id'];?></td>
					<td><?=$i['is_new'];?></td>
					<td><?=$i['i_file'];?></td>
					<td><?=$i['i_lesson_name'];?></td>
					<td><?=$i['tutors'];?></td>
					<td><?=$i['tutor_emails'];?></td>
					<td><?=$i['teacher'];?></td>
					<td><?=$i['t_chair'];?></td>
					<td><?=$i['t_faculty'];?></td>
				</tr>
			<? endforeach; ?>		