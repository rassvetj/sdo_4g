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
<?php $this->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/content-modules/schedule_table.css'); ?>
<?php $this->headLink()->appendStylesheet($this->serverUrl('/css/content-modules/course-index.css')); ?>
<?php $this->headScript()->appendFile( $this->serverUrl('/js/lib/jquery/jquery.masonry.min.js') ); ?>
<?php if ($this->markDisplay):?>
	<?php if($this->isDOT) : ?>
		<div>
			<table class="progress_table" border="0" cellpadding="0" cellspacing="0">
			  <tr>
				<td height="20" width="470" align="left" valign="middle">
				<div class="progress_title"><br><?php echo _('График достижения итоговой оценки курса')?></div>
				</td>
				<td></td>
				<td></td>
				<td></td>
			  </tr>
			  <tr>
					  <td class="progress_td" height="48" width="470" align="center" style="vertical-align: bottom; padding-bottom: 7px;">
					<div id="cumulativeProgress"></div>
					<?php
					$HM = $this->HM();

					$HM->create('hm.core.ui.progressbar.cumulative.ProgressbarCumulative', array(
						'renderTo' => '#cumulativeProgress',

						'value' => $this->subjectProgressData['value'], //балл
						'bestValue' => $this->subjectProgressData['bestValue'], //лучший балл
						'maxValue' => 100, //максимальный балл

						'targetValue' => $this->subject->threshold, //проходной балл

						'altValue' => $this->subjectProgressData['altValue'], //альтернативный балл
						'bestAltValue' => $this->subjectProgressData['bestAltValue'], //лучший альтернативный балл
						'maxAltValue' => 40, //максимальный альтернативный балл
						'size' => 'xlarge'
					));
					?>
				</td>
				<!--
				<td width="1%" style="vertical-align: bottom;>
					<div style="float: left; text-align: center; padding: 0 10px;">            
				
					<?php
				
					$this->score(); //TODO: не знаю, зачем эта строка, но без неё не работает то, что дальше
					echo _('Сумма баллов');
					
					echo $this->score(array(
						'score' => $this->subjectProgressData['currentScore'], //$this->lesson->getStudentScore($this->currentUserId)
						'user_id' => $this->forStudent,
						//'lesson_id' => $this->subjectScoreData['bestValue'],
						'scale_id' => HM_Scale_ScaleModel::TYPE_CONTINUOUS,
						'mode' => HM_View_Helper_Score::MODE_DEFAULT,
					));
				
					?>
				
					</div>
				</td>
				-->
				
				<td width="1%">
					<div id="final_score" style="float: left; text-align: center; padding: 0 10px;">
					<?php
					echo _('Итоговая оценка');
					echo $this->score(array(
						'score' => $this->mark,
						'user_id' => $this->forStudent,
						'lesson_id' => 2,
						'scale_id' => HM_Scale_ScaleModel::TYPE_CONTINUOUS,
						'mode' => HM_View_Helper_Score::MODE_DEFAULT,
					));
					?>
					</div>
				</td>
				<td class="progress_legend_td" style="vertical-align: bottom; padding-bottom: 7px;">
					<div class="hm-progresstable-legend"><span style="background-color: #5e7aa8;"></span>&nbsp;<?=_('Ваш результат')?></div>
					<div class="hm-clear" style="height: 5px;"></div>
					<div class="hm-progresstable-legend"><span style="background-color: #51C54C;"></span>&nbsp;<?=_('Лучший результат')?></div>
				</td>
			  </tr>
			  <tr>
				  <td colspan="2">
					  <?php/*
					  <input id="set_mark" style="width: 100%;" type="button" value="Выставить оценку"
						<?php if(!$this->subjectProgressData['markAllowed']) echo 'disabled';?>
					  /> 
					  */?>
					  <script type="text/javascript">
						  $(function() {
							  $('#set_mark').on('click', function(){
								  $.get('<?php echo $this->url(array('module' => 'subject', 'controller' => 'ajax', 'action' => 'set-mark'))?>', {
									  score: <?php echo $this->subjectProgressData['currentScore'];?>,
									  subject_id: <?php echo $this->subject->subid; ?>
								  })
								  .done(function(data) {
									  var $final_score = $('#final_score');
										  $final_score.children()
											  .removeClass('score_gray')
											  .addClass('score_red')
											  .children().html(data);
								  });
							  });
						  });
					  </script>
				  </td>
			  </tr>
			</table>
		</div>
	<?php else : ?>
		<div style="max-width: 100%; padding-bottom: 10px">
			<table class="additional-tbl">
				<thead>
					<tr class="marksheet-labels">
						<?php if($this->isMainModule):?>
							<td ><span ><?=_('Итоговый текущий рейтинг по главному модулю')?></span></td>							
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
							<td ><span ><?=_('Итоговый текущий рейтинг')?></span></td>
							<td ><span ><?=_('Рубежный рейтинг')?> </span></td>
							<td ><span ><?=_('Оценка')?></span></td>
							<td ><span ><?=_('Итог')?></span></td>							
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

<?php if ($this->isEditSectionAllowed):?>
    <?php echo $this->Actions('lessons', array(
        array('title' => _('Создать раздел'), 'url' => $this->url(array('module' => 'subject', 'controller' => 'materials', 'action' => 'edit-section', 'return' => 'lesson')))
    ));?>
<?php endif;?>

<?php $containerIds = array();?>
<?php if(count($this->sections)):?>
    <div id="<?= $subjectPageId = $this->id('sp') ?>" class="subject-page <?php if ($this->isEditSectionAllowed): ?>edit-mode<?php endif; ?>">
        <?php
        foreach ($this->sections as $section):
        if( ($this->isStudentRole && count($section->lessons)) || !$this->isStudentRole):
        ?>
        <form action="<?= $this->url(array('module' => 'lesson', 'controller' => 'list', 'action' => 'order-section', 'section_id' => $section->section_id)); ?>" method="POST">
        <div class="container-wrapper"><div class="container" title="<?php echo $section->description;?>">
            <h3 class="<?php if (!strlen($section->name)): ?>no-title<?php endif; ?>">
                <span><?= (strlen($section->name) ? $section->name : _("Нет названия") )?></span>
                <?php if ($this->isEditSectionAllowed):?>
                <a href="<?= $this->url(array('module' => 'subject', 'controller' => 'materials', 'action' => 'edit-section', 'section_id' => $section->section_id, 'return' => 'lesson'));?>"><img src="<?= $this->serverUrl('/images/blog/controls-edit.png'); ?>"></a>
                <?php endif; ?>
                <?php if ((!count($section->lessons)) && (count($this->sections) > 1) && $this->isEditSectionAllowed): ?>
                <a href="<?= $this->url(array('module' => 'subject', 'controller' => 'materials', 'action' => 'delete-section', 'section_id' => $section->section_id, 'return' => 'lesson'));?>"><img src="<?= $this->serverUrl('/images/blog/controls-delete.png'); ?>"></a>
                <?php endif; ?>
            </h3>
            <div class="items" id="<?= $containerIds[] = $this->id('c'); ?>">
                <?php foreach ($section->lessons as $lesson): ?>
                <div class="material-preview" style="width: 95%">
                    <?php if(!$this->isStudentRole):?><div class="grip"><div class="handle"></div></div><?php endif;?>
                    <div class="content">
					<?php
						if(isset($reason_fail['lessons'][$lesson->SHEID])){
							$lesson->is_fail = true;	
						}					
					?>
                    <?php echo $this->lessonPreview($lesson,
                                                    $this->subjectProgressData['titles'],
                                                    $this->subjectProgressData['lesson-preview'],
                                        $this->forStudent, $this->eventCollection)?>
                    </div>
                    <input type="hidden" name="material[]" value="<?= $lesson->SHEID ?>">
                </div>
                <?php endforeach; ?>
            </div>
        </div></div>
        </form>
        <?php
         endif;
        endforeach;
        ?>
    </div>
<?php else:?>
    <?php echo _('Отсутствуют данные для отображения')?>
<?php endif;?>

<?php $this->inlineScript()->captureStart(); ?>



(function () {

var selector = <?= Zend_Json::encode('#'.implode(', #', $containerIds)) ?>;
var subjPage = <?= Zend_Json::encode('#'.$subjectPageId) ?>

function enableMassonry () {
    $(selector).masonry({
        itemSelector : '.material-preview',
        columnWidth : function( containerWidth ) {
            return containerWidth / 1;
        },
        isFitWidth: true,
        isAnimated: true,
        animationOptions: {
            duration: 400
    		}
    	});
}
function disableMassonry () {
    $(selector).masonry('destroy');
}
function saveOrder ($form) {
    var data = $form.serializeArray()
      , action = $form.attr('action')
      , method = $form.attr('method');

    method = /^(GET|PUT|POST|DELETE|HEAD|OPTIONS)$/i.test(method || '') ? method.toUpperCase() : 'GET';

    $.ajax(action || '', {
        type: method,
        data: data
    });
}
function enableEditMode () {
    $(subjPage).disableSelectionLight().addClass('edit-mode');
    $(selector).sortable({
        connectWith: selector,
        containment: '#main',
        cursor: 'move',
        forceHelperSize: true,
        forcePlaceholderSize: true,
        placeholder: 'ui-state-highlight placeholder',
        //tolerance: 'pointer',
        handle: '> .grip',
        revert: 300,
        update: function () {
            saveOrder($(this).closest('form'));
        }
    });
}
function disableEditMode () {
    $(subjPage).enableSelection().removeClass('edit-mode');
    $(selector).sortable('destroy');
}

$(function () {
    if ($(subjPage).hasClass('edit-mode')) {
        enableEditMode();
    } else {
        enableMassonry();
    }
});
$(document).delegate('#edit-mode-enable', 'click', function (event) {
    event.preventDefault();
    disableMassonry();
    enableEditMode();
});
$(document).delegate('#edit-mode-disable', 'click', function (event) {
    event.preventDefault();
    disableEditMode();
    enableMassonry();
});

})();

//скрипт, который отображает форму для редактирования иконки занятия
jQuery(document).ready(function(){
    var dialogContainer = $('<div id="ico-dialog"></div>');
    $('.els-iconEdit').on('click', function(e){
        //console.log('dial_'+index);
        e.preventDefault();
        dialogContainer.html('Загрузка...');
        dialogContainer.dialog({width: 470});
        dialogContainer.load($(this).attr('href'));
    });
});
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