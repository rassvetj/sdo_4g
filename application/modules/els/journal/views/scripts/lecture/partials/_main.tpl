<form method="<?=$this->form->getAttrib('method')?>" action="<?=$this->form->getAttrib('action')?>", id="<?=$this->form->getAttrib('id')?>" enctype="<?=$this->form->getAttrib('enctype')?>" >
	<table class="main-grid journal-tbl" data-ball_weight_practic="<?=$this->ballWeightPractic?>" >
		<thead>
			<tr>
				<th class="first-cell" rowspan="2">ФИО студента</th>
				<?php foreach($this->dayList as $dayId => $dayCaption): ?>
					<input type="hidden" name="day[<?=$dayId?>]" value="<?=date('d.m.Y', strtotime($dayCaption))?>" disabled="disabled" >
					<th id="col_day_<?=$dayId?>" style="height: 45px;">
						<div>
							<p class="day-caption" id="day-caption-<?=$dayId?>" data-day_id="<?=$dayId?>" data-day_caption="<?=date('d.m.Y', strtotime($dayCaption))?>" ><?=date('d.m.Y', strtotime($dayCaption))?></p>
							<p>
								<a  href="<?=$this->url(array('module' => 'journal', 'controller' => 'storage', 'action' => 'delete-day', 'day_id' => $dayId, 'referer_redirect' => 1), null, false);?>" 
									class="delete-day-btn"
									id="day_<?=$dayId?>"
								>Удалить</a>
							</p>
						</div>
					</th>
				<?php endforeach; ?>
				<th class="new-col hidden">
					<input type="hidden" name="day[new]" value="<?=date('d.m.Y')?>" disabled="disabled" >
					<p class="day-caption" id="day-caption-new" data-day_id="new" data-day_caption="<?=date('d.m.Y')?>" ><?=date('d.m.Y')?></p>					
				</th>
				<th>Итог</th>
			</tr>							
		</thead>
		<tbody>
		<?php foreach($this->users as $user) : ?>
			<tr class="odd fio-cell">
				<td class="fio-cell">
					<?=$user['card']; ?>
					<b style="color: #1171b4;"><?=$user['fio'];?></b>
					<p>Учебные группы: <?=(!empty($user['groups_name'])) ? (implode(', ',$user['groups_name'])) : (_('Нет')); ?></p>
				</td>
				<?php foreach($this->dayList as $dayId => $dayCaption): ?>
					<?php 
						$ceilKey = $user['MID'] . '_' . $dayId;
						
						$userData = isset($this->journalResult[$user['MID']][$dayId]) ? $this->journalResult[$user['MID']][$dayId] : false;
						$isBeValue = isset($userData['isBe']) && $userData['isBe']==HM_Lesson_Journal_Result_ResultModel::IS_BE_YES
							? HM_Lesson_Journal_Result_ResultModel::IS_BE_YES
							: HM_Lesson_Journal_Result_ResultModel::IS_BE_NO;
						
						$formatAttendanceValue = isset($userData['format_attendance']) && $userData['format_attendance']==HM_Lesson_Journal_Result_ResultModel::FORMAT_ATTENDANCE_FULL_TIME
							? HM_Lesson_Journal_Result_ResultModel::FORMAT_ATTENDANCE_FULL_TIME
							: HM_Lesson_Journal_Result_ResultModel::FORMAT_ATTENDANCE_ONLINE;
					?>
					<td class="item-container" data-ceil_key="<?=$ceilKey?>">
						<input type="hidden" name="isBe[<?=$ceilKey?>]"              value="<?=$isBeValue?>"             disabled="disabled" >
						<input type="hidden" name="format_attendance[<?=$ceilKey?>]" value="<?=$formatAttendanceValue?>" disabled="disabled" >
						
						<p class="isBeCaption <?=$isBeValue==HM_Lesson_Journal_Result_ResultModel::IS_BE_YES ? 'is-be-yes' : 'is-be-no'?>">
							<?=$isBeValue==HM_Lesson_Journal_Result_ResultModel::IS_BE_YES ? 'Был' : 'Не был'?>
						</p>						
						<p class="formatAattendanceCaption <?=$formatAttendanceValue==HM_Lesson_Journal_Result_ResultModel::FORMAT_ATTENDANCE_FULL_TIME ? 'format_attendance-base' : 'format_attendance-online'?>">
							<?=HM_Lesson_Journal_Result_ResultModel::getFormatAttendanceName($formatAttendanceValue)?>
						</p>
						
					</td>					
				<?php endforeach;?>
				<td class="item-container new-col isBe-new-ceil hidden" data-user_id="<?=$user['MID']?>" data-ceil_key="<?=$user['MID']?>_new" >&nbsp;</td>
				
				<td>
					<div class="<?=($user['mark'] > 0) ? 'score_red' : 'score_gray'; ?> number_number">
						<span align="center"><?=($user['mark'] > 0) ? round($user['mark'], 2) : 'Нет'; ?></span>
					</div>
				</td>
			</tr>
		<?php endforeach;?>
		</tbody>
		<tfoot>
			<tr>
				<td colspan="100%">
					<table cellspacing="0">
						<tbody>								
							<tr class="last-row">									
								<td class="first-cell" style="text-align: right;">
									<a 
										target="_blank" 
										href="<?=$this->url(array('module' => 'report', 'controller' => 'journal', 'action' => 'export-lesson', 'subject_id' => $this->subject_id, 'lesson_id' => $this->lesson_id));?>">
										Выгрузить в файл
									</a>&nbsp;&nbsp;
										
									<a href="#" onClick="return false;" class="new-day-btn">Создать занятие</a>
									<a href="#" onClick="return false;" class="cancel-new-day-btn hidden">Отмена</a>
									&nbsp;
									<input type="submit" value="Сохранить" onClick="$('#save-journal-dialog').dialog('open'); return false;">
								</td>
							</tr>
						</tbody>
					</table>
				</td>
			<tr>
		</tfoot>
	</table>
</form>