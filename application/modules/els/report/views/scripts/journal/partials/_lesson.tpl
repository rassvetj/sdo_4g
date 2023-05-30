<?php
$isPracticeStyleLesson = in_array($this->lesson->typeID, array(HM_Event_EventModel::TYPE_JOURNAL_PRACTICE));
?>
<div class="accordion-container">
	<div class="accordion-header">
		<a href="#" class="btn-accordion" ><?=$this->lesson->title?>&nbsp;<span style="font-size: 15px; color: #40404099;">(<?=$this->lessonTypeList[$this->lesson->typeID]?>)</span></a>		
	</div>
	<div class="accordion-data">		
		<a target="_blank" href="<?=$this->url(array('module' => 'lesson', 'controller' => 'execute', 'action' => 'index', 'subject_id' => $this->subject->subid, 'lesson_id' => $this->lesson->SHEID));?>">
			Перейти в занятие
		</a>&nbsp;&nbsp;&nbsp;
		<a target="_blank" href="<?=$this->url(array('module' => 'report', 'controller' => 'journal', 'action' => 'export-lesson', 'subject_id' => $this->subject->subid, 'lesson_id' => $this->lesson->SHEID));?>">
			Выгрузить в файл
		</a>	
		<table class="report-journal-table_ main-grid">
			<thead>
				<tr>
					<th class="first-cell" >ФИО студента</th>
					<th class="first-cell" >Группа</th>
					<?php if($this->lesson->days && count($this->lesson->days)):?>
						<?php foreach($this->lesson->days as $day):?>
							<th class="first-cell_ date-caption"><?=(strtotime($day->date_lesson)>0 ? date('d.m.Y', strtotime($day->date_lesson)) : $day->date_lesson )?></th>
						<?php endforeach;?>
					<?php endif;?>
					
					<?php if($isPracticeStyleLesson):?>
						<th class="first-cell">АА</th>
						<th class="first-cell">ПР</th>
					<?php else: ?>
						<th class="first-cell">Итог</th>				
					<?php endif;?>
				</tr>				
			</thead>
			<tbody>
			<?php foreach($this->users as $user):?>
				<?php $lastExistsItem = false; ?>
				<tr class="odd fio-cell" >
					<td class="<?=$user->isGraduated ? 'isGraduated' : ''?>" style="color: #1171b4;" ><?=$user->getName()?></td>
					<td>
					<?php
						if($user->groupId){
							$group = $this->groups->exists('group_id', $user->groupId);
						}
						echo $group ? $group->name : 'нет';	
					?>
					</td>
					<?php if($this->lesson->days && count($this->lesson->days)):?>
						<?php foreach($this->lesson->days as $day):?>							
							<?php $journalResult = $day->journalResults ? $day->journalResults->exists('MID', $user->MID) : false; ?>
						<td>
							<?php if($journalResult):?>
								<?php $lastExistsItem = $journalResult;?>
								<?php if($journalResult->isBe == HM_Lesson_Journal_Result_ResultModel::IS_BE_YES): ?>
									<table style="width: 100%;">
										<tr>
											<td style="border: none;">
												<p class="is-be-yes">Был</p>
												<p><?=$journalResult->format_attendance?></p>
											</td>
											<?php if($isPracticeStyleLesson):?>
												<td style="border: none; text-align: center;">
													<div class="score_red number_number">
														<span align="center"><?=$journalResult->mark?></span>
													</div>
												</td>
											<?php endif;?>
										</tr>
									</table>
								<?php else: ?>
									<table style="width: 100%;">
										<tr>
											<td style="border: none;">
												<p class="is-be-no">Не был</p>
											</td>
											<?php if($isPracticeStyleLesson):?>
												<td style="border: none; text-align: center;">
													<div class="score_gray number_number">
														<span align="center">Нет</span>
													</div>
												</td>
											<?php endif;?>
										</tr>
									</table>
								<?php endif;?>
							<?php else:?>
								<table style="width: 100%;">
									<tr>
										<td style="border: none; text-align: center;">
											<p class="is-be-no">Не был</p>
										</td>
										<?php if($isPracticeStyleLesson):?>
											<td style="border: none;">
												<div class="score_gray number_number">
													<span align="center">Нет</span>
												</div>
											</td>
										<?php endif;?>
									</tr>
								</table>
							<?php endif;?>
						</td>
						<?php endforeach;?>
					<?php endif;?>
					
					<?php if($isPracticeStyleLesson):?>
						<td>
							<?php if($lastExistsItem):?>							
								<div class="<?=empty($lastExistsItem->ballAcademic) ? 'score_gray' : 'score_red'?> number_number">
									<span align="center">
										<?=empty($lastExistsItem->ballAcademic) ? 'Нет' : $lastExistsItem->ballAcademic?>
									</span>
								</div>
							<?php else:?>
								<div class="score_gray number_number">
									<span align="center">
										Нет
									</span>
								</div>
							<?php endif;?>
						</td>
						<td>
							<?php if($lastExistsItem):?>							
								<div class="<?=empty($lastExistsItem->ballPractic) ? 'score_gray' : 'score_red'?> number_number">
									<span align="center">
										<?=empty($lastExistsItem->ballPractic) ? 'Нет' : $lastExistsItem->ballPractic?>
									</span>
								</div>
							<?php else:?>
								<div class="score_gray number_number">
									<span align="center">
										Нет
									</span>
								</div>
							<?php endif;?>
						</td>
					<?php else:?>
						<td>
							<div class="<?=(empty($lastExistsItem->V_STATUS) || $lastExistsItem->V_STATUS==-1) ? 'score_gray' : 'score_red'?> number_number">
								<span align="center">
									<?=(empty($lastExistsItem->V_STATUS) || $lastExistsItem->V_STATUS==-1) ? 'Нет' : $lastExistsItem->V_STATUS?>
								</span>
							</div>
						</td>
					<?php endif;?>
				</tr>
			<?php endforeach;?>
			</tbody>
			<tfoot>
				<tr>
					<td colspan="100%">
						<table>
							<tbody>
								<tr class="last-row">
									<td>&nbsp;</td>
								</tr>
							</tbody>
						</table>
					</td>
				</tr>
			</tfoot>			
		</table>
	</div>
</div>