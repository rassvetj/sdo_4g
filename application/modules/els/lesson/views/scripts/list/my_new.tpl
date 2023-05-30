<?php $this->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/content-modules/schedule_table.css'); ?>
<?php $this->headLink()->appendStylesheet($this->serverUrl('/css/content-modules/course-index.css')); ?>
<?php $this->headScript()->appendFile( $this->serverUrl('/js/lib/jquery/jquery.masonry.min.js') ); ?>

<?php echo $this->headSwitcher(array('module' => 'lesson', 'controller' => 'list', 'action' => 'index', 'switcher' => 'my'));?>

<?php if ($this->markDisplay):?>
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
	    <td class="progress_td" height="48" width="470" align="center" valign="middle" rowspan="2">
            <div id="cumulativeProgress"></div>
            <?php
            $HM = $this->HM();

            $HM->create('hm.core.ui.progressbar.cumulative.ProgressbarCumulative', array(
                'renderTo' => '#cumulativeProgress',

                'value' => 20, //балл
                'bestValue' => 40, //лучший балл
                'maxValue' => 100, //максимальный балл

                'targetValue' => 70, //проходной балл

                'altValue' => 10, //альтернативный балл
                'bestAltValue' => 16, //лучший альтернативный балл
                'maxAltValue' => 40, //максимальный альтернативный балл
                'size' => 'xlarge'
            ));
            ?>
		</td>
	    <td width="1%">
            <div style="float: left; text-align: center; padding: 0 10px;">
            <?php
            $this->score(); //TODO: не знаю, зачем эта строка, но без неё не работает то, что дальше
            echo _('Сумма баллов');
            echo $this->score(array(
                'score' => 22, //$this->lesson->getStudentScore($this->currentUserId)
                'user_id' => 1,
                'lesson_id' => 2,
                'scale_id' => HM_Scale_ScaleModel::TYPE_CONTINUOUS,
                'mode' => HM_View_Helper_Score::MODE_DEFAULT,
            ));
            ?>
            </div>
        </td>
        <td width="1%">
            <div style="float: left; text-align: center; padding: 0 10px;">
            <?php
            echo _('Итоговая оценка');
            echo $this->score(array(
                'score' => -1,
                'user_id' => 1,
                'lesson_id' => 2,
                'scale_id' => HM_Scale_ScaleModel::TYPE_CONTINUOUS,
                'mode' => HM_View_Helper_Score::MODE_DEFAULT,
            ));
            ?>
            </div>
	    </td>
        <td rowspan="2" class="progress_legend_td">
            <div class="hm-progresstable-legend"><span style="background-color: #5e7aa8;"></span>&nbsp;Ваш результат</div>
            <div class="hm-clear" style="height: 5px;"></div>
            <div class="hm-progresstable-legend"><span style="background-color: #51C54C;"></span>&nbsp;Лучший результат</div>
        </td>
	  </tr>
      <tr>
          <td colspan="2"><input style="width: 100%;" type="button" value="Выставить оценку"/></td>
      </tr>
	</table>
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
                    <?php echo $this->lessonPreview($lesson,
                                                    $this->titles,
                                        (Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_TEACHER))? 'lesson-preview-teacher' : 'lesson-preview',
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