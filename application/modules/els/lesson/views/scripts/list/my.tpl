<style>
	.additional-tbl{
		border-collapse: collapse;
	}
	
	.additional-tbl td{
		text-align: center;
		padding: 1px;
	}
	
	.additional-tbl tbody td{
		width: 6.5em;
		padding: 1px;
		border-width: 1px;
		border-color: rgb(197, 208, 215);
		border-style: solid;
		vertical-align: middle;
	}
	
	.additional-tbl .total-ball{	    
		display: inline-block;
		width: 94%;
		color: #b55b5b;
		text-align: center;
		font-size: 1.636em;
	}
	
	.additional-tbl .marksheet-labels span{
		color: white;
		font-weight: bold;
		line-height: 1em;    
		padding: 1em;
		display: inline-block;
		-moz-border-radius: 1em;
		border-radius: 1em;
		background-color: #222853;
	}
</style>
<?php
require_once APPLICATION_PATH .  '/views/helpers/Score.php';
$this->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/content-modules/schedule_table.css');
?>
<form id="marksheetteacher">
<?php echo $this->headSwitcher(array('module' => 'lesson', 'controller' => 'list', 'action' => 'index', 'switcher' => 'my'));
?>

<?php if ($this->markDisplay):?>
	<?php if($this->isDOT) : ?>
		<div style="max-width: 560px; float: left;">
			<table class="progress_table" border="0" cellpadding="0" cellspacing="0">
			  <tr>
				<td height="20" width="470" align="left" valign="middle">
				<div class="progress_title"><br><?php echo _('Прогресс прохождения плана')?></div>
				</td>
				<td height="20" valign="middle">
				<div class="progress_title"><?php echo sprintf(_('Итоговая%sоценка'), '<br>')?></div>
				</td>
			  </tr>
			  <tr>
				<td class="progress_td" height="48" width="470" align="center" valign="middle">
					<?php echo $this->progress($this->percent, 'xlarge')?>
				</td>
				<td>
				<?php echo $this->score(array(
					'score' => $this->mark,
					'user_id' => $this->forStudent,
					'lesson_id' => 'total',
					'scale_id' => $this->subject->getScale(),
					'mode' => HM_View_Helper_Score::MODE_DEFAULT,
				));?>
			  </td>
			  </tr>
			</table>
		</div>
	<?php else : ?>
		<div style="max-width: 100%; float: left; padding-bottom: 10px">
			<table class="additional-tbl">
				<thead>
					<tr class="marksheet-labels">
						<?php if($this->isMainModule):?>
							<td ><span ><?=_("Итоговый текущий рейтинг по главному модулю") ?></span></td>							
							<?php if(!empty($this->additional['moduleData']['subjects'])):  ?>
								<?php foreach($this->additional['moduleData']['subjects'] as $subject_id => $subject_name): ?>
									<?php if($subject_id == $this->subject->subid) { continue; } ?>
										<td ><span >
											<?php echo _("Итоговый текущий рейтинг по доп. модулю:") ?>
											<a target="_blank" title="<?php echo _('Модульная дисциплина')?>" href="<?=$this->url(array('module' => 'subject', 'controller' => 'index', 'action' => 'card', 'subject_id' => $subject_id), 'default', true);?>">
											<?=$this->escape($subject_name)?>
										</span></td>
								<?php endforeach; ?>
							<?php endif; ?>
							<td ><span ><?=_("Интегральный текущий рейтинг") ?></span></td>
							<td ><span ><?=_("Рубежный рейтинг") ?></span></td>
							<td ><span ><?=_("Оценка") ?></span></td>
							<td ><span ><?=_("Итог") ?></span></td>
						<?php else:?>
							<td ><span ><?=_("Итоговый текущий рейтинг") ?></span></td>
							<td ><span ><?=_("Рубежный рейтинг") ?></span></td>
							<td ><span ><?=_("Оценка") ?></span></td>
							<td ><span ><?=_("Итог") ?></span></td>							
						<?php endif;?>
						<td >&nbsp;</td>
					</tr> 
				</thead>
				<tbody>
					<tr >
						<?php if($this->isMainModule):?>
							<td >
								<div class="total-ball"><?=$this->ratingMmedium;?></div>
							</td>
							<?php if(!empty($this->additional['moduleData']['subjects'])):  ?>
								<?php foreach($this->additional['moduleData']['subjects'] as $subject_id => $subject_name): ?>
									<?php if($subject_id == $this->subject->subid) { continue; } ?>							
									<td >
										<div class="total-ball"><?=$this->additional['moduleData']['rating'][$subject_id]['medium'][$this->user_id];?></div>
									</td>
								<?php endforeach; ?>
							<?php endif; ?>
							
							<td >
								<div class="total-ball total-ball-text"><?=round($this->additional['moduleData']['integrate'][$this->user_id]['medium']);?></div>
							</td>
							<td >
								<div class="total-ball"><?=empty($this->ratingTotal) ? '' : $this->ratingTotal;?></div>
							</td>
							<td >
								<div class="total-ball total-ball-text"><?=$this->fiveBallText;?></div>
							</td>
							<td >
								<div class="total-ball"><?=empty($this->ratingTotal) ? '' : $this->ballTotal;?></div>								
							</td>
						<?php else:?>
							<td >
								<div class="total-ball"><?=$this->ratingMmedium;?></div>
							</td>
							<td >
								<div class="total-ball"><?=$this->ratingTotal;?></div>
							</td>
							<td >
								<div class="total-ball total-ball-text"><?=$this->fiveBallText;?></div>
							</td>
							<td >
								<div class="total-ball"><?=$this->ballTotal;?></div>
							</td>
						<?php endif;?>
						<td style="border: none;width: 100%;text-align: left;padding-left: 10px;">
								<?php $reason_fail = !empty($this->reasonFail) ? current($this->reasonFail) : ''; ?>
								<div><?=( !empty($this->reasonFail) || !empty($this->reasonFailModule) ) ? ('<b>'._("Вы не допущены к зачету/экзамену").':</b><br>'.$reason_fail['message'] ) : (_("Вы допущены к зачету/экзамену"));?></div>
								<?php 
									if(!empty($this->reasonFailModule)){
										foreach($this->reasonFailModule as $subject_id => $i){
											$cur_reason = current($i['reasons']);
											echo $cur_reason['message'].' '._("в сессии").' <b>'.$i['name'].'</b>'.'<br />';										
										}
									}
								?>							
						</td>												
					</tr>            
				</tbody>		
			</table>
		</div>
	<?php endif;?>    
<?php endif;?>    
    
<?php if (count($this->lessons)):?>
    <?php foreach($this->lessons as $lesson):?>
        <?php if ($lesson instanceof HM_Lesson_LessonModel):?>
        <?php
			
            if($this->subject->mark_type == HM_Mark_StrategyFactory::MARK_BRS){
                $previewType = 'lesson-preview-markBrs';                            
            } else {
				
                $previewType = (Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_TEACHER))? 'lesson-preview-teacher' : 'lesson-preview';
                                        
            }    
        ?>			
		<?php $lesson->isNewActionInLesson = (isset($this->newActionInLessons[$lesson->SHEID]) && count($this->newActionInLessons[$lesson->SHEID])) ? (true) : (false); ?>
		<?php
			if(isset($reason_fail['lessons'][$lesson->SHEID])){
				$lesson->is_fail = true;	
			}					
		?>		
        <?php echo $this->lessonPreview($lesson,
                                        $this->titles,
                                        $previewType,
                                        $this->forStudent)?>
        <?php endif;?>
    <?php endforeach;?>
<?php else:?>
    <?php echo _('Отсутствуют данные для отображения')?>
<?php endif;?>
<?php if(!$this->forStudent && Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_TEACHER)):?>
<?php $this->inlineScript()->captureStart(); ?>
    $(function(){
    	$(".lesson_bg_img").prepend('<span class="field-cell drag-handler"></span>')
    	$('#marksheetteacher').sortable({
    		tolerance: 'pointer',
    		appendTo: 'body',
    		handle: 'span.drag-handler',
    		helper: 'clone',
    		revert: true,
    		update: function (event, ui) {
    			var cItemSort = $.map($('#marksheetteacher').sortable("toArray"),function(item){
    				if(item.length>0) return item
    			})
    			$.getJSON('<?php echo $this->url(array('module' => 'lesson', 'controller' => 'list', 'action' => 'save-order'));?>', {
    				posById: cItemSort
    			});
    		}
    	});
    })

<?php $this->inlineScript()->captureEnd(); ?>
<?php endif;?>

<?php if($this->isTutorOnCourse || Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_TEACHER)):?>
<?php $this->inlineScript()->captureStart(); ?>
if(typeof initMarksheet=="function"){
    initMarksheet({
        url: {
            comments: "<?php echo $this->url(array('module' =>'marksheet', 'controller' => 'index', 'action' => 'set-comment'));?>",
            score: "<?php echo $this->url(array('module' =>'marksheet', 'controller' => 'index', 'action' => 'set-score'));?>"
        },
        l10n: {
            save: "<?php echo _("Сохранить"); ?>",
            noStudentActionSelected: "<?php echo _("Не выбрано ни одного действия со слушателем"); ?>",
            noStudentSelected: "<?php echo _("Не выбрано ни одного слушателя"); ?>",
            noLessonActionSelected: "<?php echo _("Не выбрано ни одного действия с занятием"); ?>",
            noLessonSelected: "<?php echo _("Не выбрано ни одного занятия"); ?>",
            formError: "<?php echo _("Ошибка формы") ?>",
            ok: "<?php echo _("Хорошо"); ?>",
            confirm: "<?php echo _("Подтверждение"); ?>",
            areUShure: "<?php echo _("Вы уверены?"); ?>",
            yes: "<?php echo _("Да"); ?>",
            no: "<?php echo _("Нет"); ?>"
        }
    });
}
<?php $this->inlineScript()->captureEnd(); ?>

</form>
<?php endif;?>

<?php $this->inlineScript()->captureStart(); ?>

jQuery(document).ready(function(){
    $(".lesson-callback").on('click','a',function(e){
    e.preventDefault();
    $('#callback-form > iframe').attr('src','/message/ajax/lesson-callback/lesson_id/'+$(e.target).data('testid'));
    $('#callback-form').dialog({width:600,
                                height:443,
                                modal:true,
                                closeOnEscape:true,
                                resizable:false
        });
    });
});
<?php $this->inlineScript()->captureEnd(); ?>

<div id="callback-form" style="display:none;">
    <iframe style="height: 100%;width:100%;" src="/message/ajax/lesson-callback/lesson_id/">
    </iframe>
</div>

