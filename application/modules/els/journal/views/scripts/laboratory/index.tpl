<?php $this->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/content-modules/score.css'); ?>
<?php $this->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/content-modules/test.css'); ?>
<?php $this->headLink()->appendStylesheet($this->baseUrl('css/content-modules/marksheet.css')); ?>
<style>
	.main-grid thead th{
		text-align: center;
	}
	.is-be-yes {
		color: green;
	}
	.is-be-no {
		color: red;
	}
	.date-caption {
		font-size: 12px;
		font-weight: bold;
	}
	
	.mode_new {
		background-color: #ebf0f2;	
	}
</style>
		<table class="main-grid">
			<thead>
				<tr class="">					
					<?php foreach($this->dayList as $key => $day): ?>
						<th colspan="2" id="th_day_<?=$key?>" style="height: 45px;">
							<?=date('d.m.Y', strtotime($day));?>							
						</th>
					<?php endforeach; ?>										
					<th rowspan="2">Итог</th>			
				</tr>	
				<tr>					
					<?php foreach($this->dayList as $day): ?>
						<th>Явка</th>
						<th>Оценка</th>			
					<?php endforeach; ?>																
				</tr>				
			</thead>
			<tbody>		
				
					<tr class="odd fio-cell">
						<?php foreach($this->dayList as $key => $day): ?>
							<?php $userData = (isset($this->journalResultUser[$key])) ? ($this->journalResultUser[$key]) : (false); ?>
							<td class="is_be_row_<?=$key?>">							
								<?php									
									$isBe_user_value = (isset($userData['isBe']) && $userData['isBe'] == HM_Lesson_Journal_Result_ResultModel::IS_BE_YES) ? (HM_Lesson_Journal_Result_ResultModel::IS_BE_YES) : (HM_Lesson_Journal_Result_ResultModel::IS_BE_NO);																										
								?>
								<div class="mode_base">
									<?=($isBe_user_value == 1)?('<p class="is-be-yes">Был</p>'):('<p class="is-be-no">Не был</p>');?>
								</div>
								<div class="mode_edit hidden">
									<?=$isBe_user?>
								</div>								
							</td>
							<td class="ball_row_<?=$key?>">
								<?php 																	
									$ball_user_value 	= (isset($userData['mark'])) ? ($userData['mark']) : (0);
								?>
								<div class="mode_base">
									<div class="<?=($ball_user_value > 0) ? 'score_red' : 'score_gray'; ?> number_number">
										<span align="center"><?=($ball_user_value > 0) ? round($ball_user_value, 2) : 'Нет'; ?></span>
									</div>
								</div>															
							</td>							
						<?php endforeach; ?>										
						<td>
							<div class="<?=($this->userMark > 0) ? 'score_red' : 'score_gray'; ?> number_number">
								<span align="center"><?=($this->userMark > 0) ? round($this->userMark, 2) : 'Нет'; ?></span>
							</div>
						</td>
					</tr>						
			</tbody>
			<tfoot>
				<tr>
					<td colspan="100%">
						<table cellspacing="0">
							<tbody>								
								<tr class="last-row">									
									<td class="first-cell" style="text-align: right;">&nbsp;
									</td>
								</tr>
							</tbody>
						</table>
					</td>
				<tr>
			</tfoot>
		</table>