<?php
    $this->headLink()->appendStylesheet($this->baseUrl('css/content-modules/marksheet.css'));
    $this->headScript()->appendFile($this->baseUrl('js/application/marksheet/index/index/script.js'));
    $this->headScript()->appendFile($this->baseUrl('/js/lib/jquery/datefilter.js'));
?>
<style>
	.in-progress {
		opacity: 0.5;
		pointer-events: none;
	}
</style>
<table id="marksheet" class="main-grid" cellspacing="0" data-schedules="4" data-persons="4">
    <colgroup><col><col><col span="4"></colgroup>
    <thead>
        <tr class="marksheet-labels">
            <td class="first-cell cell125" colspan="1"></td>
            <td class="score-cell total-cell last-cell"><span class="total-score-label"><?php echo _("Итоговый текущий рейтинг") ?></span></td>
			<td class="score-cell total-cell last-cell"><span class="total-score-label"><?php echo _("Рубежный рейтинг") ?></span></td>
			<td class="score-cell total-cell last-cell"><span class="total-score-label"><?php echo _("Итог") ?></span></td>
			<td class="score-cell total-cell last-cell"><span class="total-score-label"><?php echo _("Оценка") ?></span></td>
        </tr>
        <tr class="marksheet-head">
            <th class="fio-cell" rowspan="2" style="border-left-width: 1px;"><?php echo _('ФИО');?></th>
            <td class="total-cell last-cell score-cell" >&nbsp;</td>
            <td class="total-cell last-cell score-cell" >&nbsp;</td>
            <td class="total-cell last-cell score-cell" >&nbsp;</td>
            <td class="total-cell last-cell score-cell" >&nbsp;</td>
        </tr>		
    </thead>
    <tbody>
		<?php if(count($this->students) > 5): ?>
			<tr class="last-row ui-helper-hidden">
				<td class="first-cell" colspan="2"></td>
				<td class="slider-cell 12" colspan="12"><div id="marksheet-slider-top"></div></td>
				<td class="last-cell" colspan="100%"></td>
			</tr>
		<?php endif; ?>
        <?php $temp1 = 1; ?>
		<?php foreach($this->students as $student):?>		
			<tr class="student-item <?php echo ($temp1 % 2 == 0) ? "even" : "odd"; if ($temp1 == 1) { echo " first-row"; } else if ($temp1 == $totalPersons) { echo " last-row"; } ?>" >
					<td class="fio-cell cell125" style="border-left-width: 1px;">
						<a href="<?=$this->url(array('module' => 'lesson', 'controller' => 'list','action' => 'my', 'user_id' => $student->MID));?>">
							<?=$this->escape($student->getName());?>
						</a>
					</td>
					<td class="score-cell total-cell last-cell">
						<div class="mark-current-container">
							<?php if($this->is_chairman && empty($this->marksheet->files) ):?>
								<form  method="POST" class="mark-form" action="<?=$this->baseUrl($this->url(array(	'module'		=> 'marksheet', 
																										'controller'	=> 'external', 
																										'action' 		=> 'set-mark'),'default', true));?>">
									<input type="hidden" 	name="student_id" 		value="<?=$student->MID?>">
									<input type="hidden" 	name="marksheet_id" 	value="<?=$this->marksheet->marksheet_id?>">
									<input type="text"	name="mark_current" value="<?=$student->marks->mark_current?>">
								</form>
							<?php else:?>
								<?=$student->marks->mark_current?>
							<?php endif;?>
						</div>
					</td>
					<td class="score-cell total-cell last-cell">
						<div class="mark-landmark-container">
							<?php if($this->is_chairman && empty($this->marksheet->files) ):?>
								<form  method="POST" class="mark-form" action="<?=$this->baseUrl($this->url(array(	'module'		=> 'marksheet', 
																										'controller'	=> 'external', 
																										'action' 		=> 'set-mark'),'default', true));?>">
									<input type="hidden" 	name="student_id" 		value="<?=$student->MID?>">
									<input type="hidden" 	name="marksheet_id" 	value="<?=$this->marksheet->marksheet_id?>">
									<input type="text" 		name="mark_landmark" 	value="<?=$student->marks->mark_landmark?>">
								</form>
							<?php else:?>
								<?=$student->marks->mark_landmark?>
							<?php endif;?>
						</div>
					</td>
					<td class="score-cell total-cell last-cell">
						<div class="total-ball mark-container">			
							<?=$student->marks->mark?>
						</div>
					</td>
					<td class="score-cell total-cell last-cell">
						<div class="total-ball ball-container">
							<?=$student->marks->ball?>
						</div>
					</td>
				<?php $temp++;?>
			</tr>
        <?php $temp1++; ?>
        <?php endforeach;?>
        <tr class="last-row ui-helper-hidden">
            <td class="first-cell" colspan="2"></td>
            <td class="slider-cell 12" colspan="12"><div id="marksheet-slider"></div></td>
            <td class="last-cell" colspan="100%" style="border-top-width: 1px;"></td>
        </tr>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="100%">
                <table cellspacing="0">
                    <colgroup><col width="1"><col width="1"><col width="1"><col width="*"></colgroup>
                    <tr class="first-row">
                        <td class="first-cell hide-column">
                        </td>
                        <td>
						</td>
                        <td class="button-cell">
                        </td>
                        <td class="last-cell" rowspan="2">
							<?/*
							<?php $url_draft = $this->serverUrl($this->url(array('module' => 'marksheet', 'controller' => 'external', 'action' => 'draft', 'marksheet_id' => $this->marksheet->marksheet_id))) ?>
                            <?=$this->formButton('wordButton', _('Зач./Экз.ведомость = ЧЕРНОВИК'), array('onClick' => 'window.open("'.$url_draft.'")'));?>
							*/?>
							
							<?php if(!$this->is_chairman):?>
								<p>Пересчитать балл может только председатель комиссии</p>
							<?php endif;?>
							
							<?php if($this->is_chairman && empty($this->marksheet->files)):?>															
								<a href="<?=$this->baseUrl($this->url(array('module'       => 'marksheet', 
																			'controller'   => 'external', 
																			'action'       => 'recalculate-mark',
																			'marksheet_id' => $this->marksheet->marksheet_id,
								), 'default', true));?>"><?=_('Пересчитать балл')?></a>
							<?php endif;?>
							
							
							
							<?php if($this->is_chairman && $this->all_confirmed):?>
								<?php if(!empty($this->marksheet->files)):?>
									<p><?=_('Ведомость уже сформирована')?></p>
								<?php else:?>
									<?php $url_graduate = $this->serverUrl($this->url(array('module' => 'marksheet', 'controller' => 'external', 'action' => 'graduate', 'marksheet_id' => $this->marksheet->marksheet_id)))?>
									<?=$this->formButton('wordButton', _('Завершить курс'), array('onClick' => 'window.open("'.$url_graduate.'")'));?>
								<?php endif;?>
							<?php endif;?>
							<?php if(!$this->is_chairman):?>
								<p>Формировать ведомость может только председатель комиссии</p>
							<?php endif;?>
                        </td>
                    </tr>
                    <tr class="last-row">
                        <td class="first-cell hide-column">
                        </td>
                        <td>
                        </td>
                        <td class="button-cell">
                        </td>
					</tr>
                </table>			
            </td>
        </tr>
    </tfoot>
</table>

<?php if($this->is_chairman):?>
<script>
	$( document ).ready(function() {
		$('.student-item .mark-form').change(function(){
			updateMark($(this));
			return false;
		});
		$('.student-item .mark-form').submit(function(event){
			event.preventDefault();
		});
	});

	function updateMark(form){
		var url = form.attr('action');
		var row = form.closest('.student-item');
		
		row.addClass('in-progress');
		
		$.ajax(url, {
				type: 'POST',
				global: false,
				dataType: 'json',
				data: form.serialize()
			}).done(function (data) {	
				if (typeof data.error !== "undefined") {
					
				} else {
					if (typeof data.mark_current 	!== "undefined") { row.find('.mark-current-container [name="mark_current"]').val(data.mark_current); }
					if (typeof data.mark_landmark 	!== "undefined") { row.find('.mark-landmark-container [name="mark_landmark"]').val(data.mark_landmark); }
					if (typeof data.mark 			!== "undefined") { row.find('.mark-container').html(data.mark); }
					if (typeof data.ball 			!== "undefined") { row.find('.ball-container').html(data.ball); }
				}			
				row.removeClass('in-progress');
				
			}).fail(function () {
				row.removeClass('in-progress');
			}).always(function () {
							
			});
	}
</script>
<?php endif;?>