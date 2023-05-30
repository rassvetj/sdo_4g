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
	.day-caption {
		font-size: 12px;
		font-weight: bold;
	}
	
	.mode_new {
		background-color: #ebf0f2;	
	}
	
	.modified sup {
		color: #d66161;
		font-size: 15px;
		font-weight: bold;
	}
	
	.isBeCaption, .formatAattendanceCaption, .markCaption, .day-caption {
		cursor: pointer;
	}
	
	.day-caption {
		padding-bottom: 5px;
		display: block;
	}
	
	.new-col {
		background-color: #0067a41f;
	}
</style>
<?php if(empty($this->users)): ?>
	<h3>Нет данных для отображения</h3>
	<?php return ;?>
<?php endif;?>

<?=$this->render('practice/partials/_description.tpl');?>
<?=$this->render('practice/partials/_navigation.tpl');?>
<?=$this->render('practice/partials/_main.tpl');?>
	

<div id="change-mark-dialog" title="Изменение оценки">
	<input type="hidden" name="ceilKey" value="">
	<p><span style="float: left; margin: 0 7px 20px 0;">Выберите новую оценку</span></p>
	<br />	
	<?=$this->form->getElement('ball');?>
</div>

<div id="change-day-dialog" title="Изменение заголовка">
	<input type="hidden" name="dayId" value="">
	<p><span style="float: left; margin: 0 7px 20px 0;">Выберите новую дату</span></p>
	<?=$this->form->getElement('day');?>
</div>

<div id="delete-day-dialog" title="Удаление дня">
	<p><span style="float: left; margin: 0 7px 20px 0;">Удалить все данные за выбранный день?</span></p>
</div>

<div id="save-journal-dialog" title="Сохранение журнала">
	<p><span style="float: left; margin: 0 7px 20px 0;">Сохранить внесенные изменения?</span></p>
</div>

<div class="hidden new-ceil-params"
	data-is_be_value               = "<?=HM_Lesson_Journal_Result_ResultModel::IS_BE_NO?>"
	data-is_be_caption             = "Не был"
	data-is_be_class               = "is-be-no"
	data-format_attendance_value   = "<?=HM_Lesson_Journal_Result_ResultModel::FORMAT_ATTENDANCE_ONLINE?>"
	data-format_attendance_caption = "<?=HM_Lesson_Journal_Result_ResultModel::getFormatAttendanceName(HM_Lesson_Journal_Result_ResultModel::FORMAT_ATTENDANCE_ONLINE)?>"
	data-format_attendance_class   = "format_attendance-base"
	data-ball_value                = "0"
	data-ball_caption              = "Нет"
	data-ball_class                = "score_gray"
></div>

<?php $this->inlineScript()->captureStart()?>
$( document ).ready(function() {
	$('#change-mark-dialog #ball').prop('disabled', false);
	
	$('.delete-day-btn').click(function(){
		$('#delete-day-dialog').attr('url', $(this).attr('href'));
		$('#delete-day-dialog').dialog('open');		
		return false;		
	});
		
	$('.new-day-btn').click(function(){
		addNewDay($(this));		
		return false;
	});
	
	$('.cancel-new-day-btn').click(function(){
		cancelNewday($(this));		
		return false;
	});
	
	$('.day-caption').click(function(){
		showDayPopup($(this));	
		return false;
	});
	
	$("#change-mark-dialog").dialog({
		resizable: false,
		autoOpen: false,
		height:180,
		modal: true,
		buttons:
		{
			<?=_('Принять')?>: function() {						
				$(this).dialog('close');
				applyMarkPopup();
				return false;
			},
			<?=_('Отменить')?>: function() {				
				$(this).dialog('close');
				resetMarkPopup();
				return false;
			}
		}
	});

	$("#change-day-dialog").dialog({
		resizable: false,
		autoOpen: false,
		height:180,
		modal: true,
		buttons:
		{
			<?=_('Принять')?>: function() {						
				$(this).dialog('close');
				applyDayPopup();
				return false;
			},
			<?=_('Отменить')?>: function() {				
				$(this).dialog('close');
				resetDayPopup();
				return false;
			}
		}
	});
	
	$("#delete-day-dialog").dialog({
		resizable: false,
		autoOpen: false,
		height:180,
		modal: true,
		buttons:
		{
			<?php echo _('Да')?>: function() {						
				$(this).dialog('close');				
				window.location.href = $(this).attr('url');
				return true;
			},
			<?php echo _('Нет')?>: function() {				
				$(this).dialog('close');
				return false;
			}
		}
	});
	
	$( "#save-journal-dialog" ).dialog({
		resizable: false,
		autoOpen: false,
		height:180,
		modal: true,
		buttons:
		{
			<?=_('Да')?>: function() {
				$('#<?=$this->form->getAttrib('id')?>').submit();				
				$(this).dialog('close');				
			},
			<?=_('Нет')?>: function() {
				$(this).dialog('close');				
			}
		}
	});
});


$('.journal-tbl').on('click', '.isBeCaption', function() {
	changeIsBe($(this));		
	return false;
});

$('.journal-tbl').on('click', '.formatAattendanceCaption', function() {
	changeFormatAattendance($(this));		
	return false;
});

$('.journal-tbl').on('click', '.markCaption', function() {
	showMarkPopup($(this));		
	return false;
});



function showDayPopup(el)
{
	let dayId      = el.data('day_id');
	let dayCaption = el.data('day_caption');	
	let popup      = $('#change-day-dialog');
	
	popup.find('[name="dayId"]').val(dayId);
	popup.find('[name="day"]').val(dayCaption);
	popup.dialog('open');
}

function resetDayPopup()
{
	let popup = $("#change-day-dialog");
	    popup.find('[name="day"]').val('');
	    popup.find('[name="dayId"]').val('');
}

function applyDayPopup()
{
	let popup      = $("#change-day-dialog");
	let dayCaption = popup.find('[name="day"]').val();
	let dayId      = popup.find('[name="dayId"]').val();
	
	$('[name="day[' + dayId + ']"]').prop('disabled', false).val(dayCaption);
	$('#day-caption-' + dayId).addClass('modified').html( dayCaption + '<sup>*</sup>');
	
	resetDayPopup();
}

function showMarkPopup(el)
{
	let container = el.closest('.item-container');
	let ceilKey   = container.data('ceil_key');
	let mark      = container.find('[name="mark[' + ceilKey +']"]').val();
	let popup     = $('#change-mark-dialog');
	
	popup.find('#ball').val(mark);	
	popup.find('[name="ceilKey"]').val(ceilKey);
	popup.dialog('open');
}

function changeFormatAattendance(el)
{
	let container            = el.closest('.item-container');
	let ceilKey              = container.data('ceil_key');
	let el_formatAattendance = container.find('[name="format_attendance[' + ceilKey + ']"]');
	
	if(el_formatAattendance.val() == '<?=HM_Lesson_Journal_Result_ResultModel::FORMAT_ATTENDANCE_FULL_TIME?>'){
		el_formatAattendance.prop('disabled', false).val('<?=HM_Lesson_Journal_Result_ResultModel::FORMAT_ATTENDANCE_ONLINE?>');
		el.addClass('modified').html('<?=HM_Lesson_Journal_Result_ResultModel::getFormatAttendanceName(HM_Lesson_Journal_Result_ResultModel::FORMAT_ATTENDANCE_ONLINE)?><sup>*</sup>');
	} else {
		el_formatAattendance.prop('disabled', false).val('<?=HM_Lesson_Journal_Result_ResultModel::FORMAT_ATTENDANCE_FULL_TIME?>');
		el.addClass('modified').html('<?=HM_Lesson_Journal_Result_ResultModel::getFormatAttendanceName(HM_Lesson_Journal_Result_ResultModel::FORMAT_ATTENDANCE_FULL_TIME)?><sup>*</sup>');
	}
}

function changeIsBe(el)
{
	let container = el.closest('.item-container');
	let ceilKey   = container.data('ceil_key');
	let el_isBe   = container.find('[name="isBe[' + ceilKey + ']"]');
	
	console.log(ceilKey);
	console.log(el_isBe);
	
	if(el_isBe.val() == '<?=HM_Lesson_Journal_Result_ResultModel::IS_BE_YES?>'){
		el_isBe.prop('disabled', false).val('<?=HM_Lesson_Journal_Result_ResultModel::IS_BE_NO?>');
		el.addClass('is-be-no modified').removeClass('is-be-yes').html('Не был<sup>*</sup>');
	} else {
		el_isBe.prop('disabled', false).val('<?=HM_Lesson_Journal_Result_ResultModel::IS_BE_YES?>');
		el.addClass('is-be-yes modified').removeClass('is-be-no').html('Был<sup>*</sup>');
	}	
}

function cancelNewday(el)
{
	el.addClass('hidden');
	$('.new-day-btn').removeClass('hidden');
	$('.new-col').addClass('hidden');
	
	$('.new-col').find('input').prop('disabled', true);
}

function addNewDay(el)
{
	el.addClass('hidden');
	$('.cancel-new-day-btn').removeClass('hidden');
	$('.new-col').removeClass('hidden');
	$('.new-col').find('input').prop('disabled', false);
	
	$('.isBe-new-ceil').each(function(){
		renderIsBeCeil($(this));
	});
	
	$('.ball-new-ceil').each(function(){
		renderBallCeil($(this));
	});
}

function renderIsBeCeil(destination)
{
	let userId  = destination.data('user_id');
	let params  = $('.new-ceil-params');
	let ceilKey = userId + '_new';
	let html    = '<input type="hidden" name="isBe['              + ceilKey + ']" value="' + params.data('is_be_value')             + '">';
		html   += '<input type="hidden" name="format_attendance[' + ceilKey + ']" value="' + params.data('format_attendance_value') + '">';
		html   += '<p class="isBeCaption '              + params.data('is_be_class') + '">'             + params.data('is_be_caption')             + '</p>';
		html   += '<p class="formatAattendanceCaption ' + params.data('format_attendance_class') + '">' + params.data('format_attendance_caption') + '</p>';	
	destination.html(html);
}

function renderBallCeil(destination)
{
	let userId  = destination.data('user_id');
	let params  = $('.new-ceil-params');
	let ceilKey = userId + '_new';
	let html    = '<input type="hidden" name="mark[' + ceilKey + ']" value="' + params.data('ball_value') + '">';
		html   += '<div class="markCaption">';
			html   += '<div class="' + params.data('ball_class') + ' number_number">';
				html   += '<span align="center" id="caption_mark_' + ceilKey + '">' + params.data('ball_caption') + '</span>';
			html   += '</div>';
		html   += '</div>';	
	destination.html(html);
}

function resetMarkPopup()
{
	let popup = $("#change-mark-dialog");
	    popup.find('#ball').val('');
	    popup.find('[name="ceilKey"]').val('');	
}

function applyMarkPopup()
{
	let popup       = $("#change-mark-dialog");
	let ball        = popup.find('#ball').val();
	let ceilKey     = popup.find('[name="ceilKey"]').val();
	let weightedBal = getWeightedBal(ball);
	let ballClass   = 'score_gray';
	if(weightedBal > 0){
		ballClass = 'score_red';
	}
	
	$('[name="mark[' + ceilKey + ']"]').prop('disabled', false).val(ball);
	$('#caption_mark_' + ceilKey).addClass('modified ' + ballClass).html( weightedBal + '<sup>*</sup>');
	
	resetMarkPopup();
}

function getWeightedBal(ball)
{
	let ballWeightPractic = $('.journal-tbl').data('ball_weight_practic');
	return (parseInt(ball) * parseFloat(ballWeightPractic)).toPrecision(2);
}
<?php $this->inlineScript()->captureEnd()?>