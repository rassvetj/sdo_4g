<?=$this->msg;?>
<br>
<br>
<?php if($this->subjects) : ?> 
	<?php foreach($this->subjects as $s) : ?>
		<?php		
			$tutors = explode(',', $s['tutors']);
			$last_tutor_id = $s['last_tutor_id'];//--на него будет формироваться отчет окончательный.
			$begin = strtotime($s['begin']) ? date('d.m.Y', strtotime($s['begin'])) : false;
			$end = strtotime($s['end']) ? date('d.m.Y', strtotime($s['end'])) : false;			
			$debtEnd = strtotime($s['time_ended_debt']) ? date('d.m.Y', strtotime($s['time_ended_debt'])) : false;
		?>
		<div class="lesson">
			<div class="lesson_wrapper_1">
				<div class="lesson_wrapper_2">
					<div class="lesson_block">
						<div class="lesson_table">
							<table border="0" cellpadding="0" cellspacing="0">
								<tbody>
									<tr>
										<td width="109" align="center" valign="top" class="lesson_bg">
											<div id="lesson_bg_img">
												<a href="/subject/index/card/subject_id/<?=$s['subid'];?>">
													<img class="subject-icon" src="/images/subject-icons/distance.png" alt="<?=$s['name'];?>" title="<?=$s['name'];?>">
												</a>
											</div>											
										</td>
										<td width="450" class="lesson_options">
											<div id="lesson_title">
												<a href="/subject/index/card/subject_id/<?=$s['subid'];?>"><?=$s['name'];?></a>
											</div>
											<div id="lesson_go">											
												<div id="lesson_begin" class="">
												<?php if($begin && $end) :?>
													<?php echo '<p>'._('Дата начала обучения:').' '.$begin.'</p>';?>
													<?php echo '<p>'._('Дата окончания обучения, не позднее:').' '.$end.'</p>';?>
													
													<?php if($debtEnd) : ?>													
														<?php echo '<p>'._('Дата продления до:').' '.$debtEnd.'</p>';?>
													<?php endif; ?>		
												<?php else :?>
													<?php echo '<p>'._('Время обучения не ограничено').'</p>';?>
												<?php endif; ?>	
												</div>
											</div>
											<div class="lesson_teacher">
												<?php echo '<p>'._('Тьюторы:').'</p>';?>
												<?php if(empty($tutors)) : ?>
													<div><?php echo '<p>'._('нет.').'</p>';?></div>
												<?php else :?>
													<?php foreach($tutors as $t) : ?>
														<div>															
															<img src="/images/content-modules/grid/card.gif" title="<?=_('Карточка')?>" class="ui-els-icon ">															
															<?=$t;?>
														</div>	
													<?php endforeach; ?>												
												<?php endif; ?>																								
											</div>							
										</td>
										<td width="100" align="center" valign="top" class="showscore"></td>
										<td width="150" valign="top" class="lesson_descript_td" id="hm-subject-list-item-description-container-1">
											<div class="hm-subject-list-item-description ui-tabs ui-widget ui-widget-content ui-corner-all">
												<?php if($s['isSheetPassed']) : ?>
													<a target="_blank" href="<?=$this->url(array('controller' => 'report','action' => 'get-workload-report', 'subject_id'=> $s['subid'], 'user_id' => $last_tutor_id, 'isEnd' => '1'));?>"><?=_('Сформировать отчет')?></a>												
												<?php else :?>
													<a class="close-subject-btn" href="<?=$this->url(array('controller' => 'sheet','action' => 'close', 'subject_id'=> $s['subid']));?>"><?=_('Закрыть сессию')?></a>
												<?php endif; ?>								
											</div>
										</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
	<?php endforeach; ?>
<?php else : ?>
	<p><?=_('Нет сессий для отображения.')?></p>
<?php endif;?>
<script>
 function closeSubject(item) {	 
	 $( "#dialog-confirm" ).dialog({
		resizable: false,
		autoOpen: false,
		height:180,
		modal: true,
		buttons:
		{
			<?php echo _('Да')?>: function() {
				$( this ).dialog( "close" );												
				location.href = $(item).attr('href');				
			},
			<?php echo _('Нет')?>: function() {
				$( this ).dialog( "close" );
            }
		}
	});
	$( "#dialog-confirm" ).dialog( "open" );
 }
 $(function() { 
	$( ".close-subject-btn" ).click(function() {				
		closeSubject(this);		
		return false;		
	});
});
</script>
<div id="dialog-confirm" title="<?=_('Подтверждение действия')?>" style="display:none;">
    <p><span style="float: left; margin: 0 7px 20px 0;"><?=_('Вы действительно желаете закрыть сессию? Произойдет отправка мотивированного сообщения студентам.')?></span></p>
</div>
