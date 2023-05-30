<?php
echo $this->form ?>
<?php
$this->inlineScript()->captureStart();?>
$(document).ready(function() {

	if($('select[name="event_id"]').val() == <?php echo HM_Event_EventModel::TYPE_POLL;?>){
		$('select[name="vedomost"]').attr('disabled', 'disabled');
	}
<?php if ( $this->subject->period == HM_Subject_SubjectModel::PERIOD_DATES ):?>
    var cDate = {
            beginDate: <?php echo strtotime($this->subject->begin);?>,
            endDate:   <?php echo strtotime($this->subject->end);?>,
            msg:   "<?php echo _('Сроки занятия выходят за рамки учебного курса');?>"
        },
        oldDate = {
            beginDate: $("#beginDate").val(),
            beginDate2: $("#beginDate2").val(),
            endDate: $("#endDate").val()
        }
    if($("#step1").length<1) return;
    $("#step1").delegate('#beginDate, #endDate, #beginDate2','change',function(e){
        var $target = $(e.currentTarget),
            act     = $target.attr('id'),
            cur = $('#'+act).datepicker('getDate').getTime()/1000,
            success = true;
        if(isNaN(cur)) return;
        if(act=='beginDate' || act=='beginDate2' || act=='endDate'){
            success = (cDate.beginDate<=cur) && (cDate.endDate>=cur);
        }
        /*if(act=='endDate'){
            success = cDate.endDate>cur;
        }*/
        if(!success){
            if(!confirm(cDate.msg)){
                $('#'+act).val(oldDate[act]);
            }
        }
    })
<?php endif; ?>
});
<?php $this->inlineScript()->captureEnd(); ?>

<?php if ($this->redirectUrl): ?>
<?php $this->inlineScript()->captureStart(); ?>
    jQuery(document).ready(function(){

        var buttons = {};
        buttons['<?php echo _('Да');?>'] = function () {
            $(this).dialog('close');
            document.location.href = '<?php echo $this->redirectUrl; ?>';
        };
        buttons['<?php echo _('Нет');?>'] = function () {
            $(this).dialog('close');
            document.location.href = '<?php echo $this->url(array('module' => 'lesson', 'controller' => 'list', 'action' => 'index', 'subject_id' => $this->subjectId)); ?>';
        };

        jQuery('#redirect-dialog').dialog({
            autoOpen: true,
            resizeable: false,
            width: 400,
            modal: true,
            title: '<?php echo _('Занятие успешно изменено'); ?>',
            buttons: buttons
        });
    });
<?php $this->inlineScript()->captureEnd(); ?>
<div id="redirect-dialog">
<p><?php echo _('В процессе был создан новый учебный материал. Перейти к его редактированию?'); ?></p>
</div>
<?php endif; ?>