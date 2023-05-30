<style>
	.fin-notice p {
		text-indent: 20px;
		padding-bottom: 0px;
    	margin-bottom: 0px;
    	margin-top: 0px;
    	padding-top: 0px;
	}
	
	.hidden{
		display:none!important;
	}
	
	.sna-btn-confirm{
		display:none!important;
	}
	
	.btn-in-progress{
		opacity: 0.5;
		pointer-events: none;
	}
	
	#sna_popup .sna-btn-default{
			color: #1171b4;
			cursor: pointer;
			font-size:15px;
			font-weight: bold;
			
			display: block;
			border: 2px solid #3192be;
			padding: 3px;
			border-radius: 3px;
			width: 120px;
			
			background-image: none;
			background-color: white;
			text-align: center;
	}
		
	#sna_popup .sna-btn-default:hover{
		background-color: #3192be;
		color:white;
	}
</style>
<div class="fin-notice" style="3width: 900px;">
	<p style="text-align: center;" >
		Уважаемый студент!
	</p>
	<p>
		Укажите Ваше отношение к службе в армии?
	</p>
	<br />
	<form   method="POST" 
			action="<?=$this->baseUrl($this->url(array('module' => 'student-notification-agreement', 'controller' => 'ajax', 'action' => 'save-agreement')));?>"
			class="form-sna-military"
	>
		<input type="hidden" name="type" value="<?=$this->notification_type?>">
		
		<dd class="element">
			<label for="military_attitude_1" style="padding-bottom: 10px; display: block;" >
				<input id="military_attitude_1" type="radio" name="military_attitude" value="По окончанию обучения вернусь домой (регистрация по месту жительства), где планирую решать вопрос самостоятельно" >
				По окончанию обучения вернусь домой (регистрация по месту жительства), где планирую решать вопрос самостоятельно
			</label>
			
			<label for="military_attitude_2"  style="padding-bottom: 10px; display: block;" >
				<input id="military_attitude_2" type="radio" name="military_attitude" value="Рассчитываю на углубленное медицинское освидетельствование с целью освобождения от призыва по состоянию здоровья" >
				Рассчитываю на углубленное медицинское освидетельствование с целью освобождения от призыва по состоянию здоровья
			</label>
			
			<label for="military_attitude_3"  style="padding-bottom: 10px; display: block;" >
				<input id="military_attitude_3" type="radio" name="military_attitude" value="Планирую связать свою судьбу с силовыми структурами (служба по контракту)" >
				Планирую связать свою судьбу с силовыми структурами (служба по контракту)
			</label>
			
			<label for="military_attitude_4"  style="padding-bottom: 10px; display: block;" >
				<input id="military_attitude_4" type="radio" name="military_attitude" value="Планирую службу в армии после окончания обучения (срочная служба)" >
				Планирую службу в армии после окончания обучения (срочная служба)
			</label>
			
			<label for="military_attitude_5"  style="padding-bottom: 10px; display: block;" >
				<input id="military_attitude_5" type="radio" name="military_attitude" value="Не скрываю негативного отношения к службе в армии в целом" >
				Не скрываю негативного отношения к службе в армии в целом
			</label>
			
			<label for="military_attitude_6" style="padding-bottom: 10px; display: block;"  >
				<input id="military_attitude_6" type="radio" name="military_attitude" value="Не готов принять решения без согласования с родителями (иными родственниками)" >
				Не готов принять решения без согласования с родителями (иными родственниками)
			</label>
			
			<label for="military_attitude_7"  style="padding-bottom: 10px; display: block;" >
				<input id="military_attitude_7" type="radio" name="military_attitude" value="Затрудняюсь ответить" >
				Затрудняюсь ответить
			</label>
		</dd>
		
		
		<button class="sna-btn-default" style="float: right;" onClick="sendSnaForm(); return false;"><?=_('Отправить')?></button>
	</form>
</div>
<script>

function sendSnaForm(mode)
{
	let form         = $('.form-sna-military');
	let btn_question = form.find('.sna-btn-question');
	let btns         = form.find('.sna-btn-default');
	
	var popup    = $('#sna_popup');
	var url      = form.attr('action');
	var level    = 'success';
	var $message = jQuery('');
	
	btns.addClass('btn-in-progress');		
	jQuery.ui.errorbox.clear($message);
		
	$.ajax(url, {
		type  	 : 'POST',
		dataType : 'json',
		global	 : false,
		data  	 : form.serialize()
	}).done(function (data) {
			
		btns.removeClass('btn-in-progress');
			
		var $message = jQuery('<div class="sna_error_box">Произошла ошибка. Попробуйте ещё раз</div>');
			
		if (typeof data.error !== "undefined"){
			var $message = jQuery('<div class="sna_error_box">'+data.error+'</div>');
			level 	 = 'error';
		}
			
		if (typeof data.message !== "undefined"){
			var $message = jQuery('<div class="sna_error_box">'+data.message+'</div>');
			setTimeout(function(){ $('#sna_popup').dialog('close');	}, 2000);
		}
			
		jQuery.ui.errorbox.clear($message);
		$message.errorbox({level: level});			
		var error_box = $('#error\-box');
		popup.before(error_box);
			
	}).fail(function () {
			
		btns.removeClass('btn-in-progress');
						
		var $message = jQuery('<div class="sna_error_box">Произошла ошибка. Попробуйте ещё раз</div>');
		jQuery.ui.errorbox.clear($message);
		$message.errorbox({level: 'error'});
		var error_box = $('#error\-box');
		popup.before(error_box);
			
			
	}).always(function () {			
	});
		
	return false;
}
</script>




