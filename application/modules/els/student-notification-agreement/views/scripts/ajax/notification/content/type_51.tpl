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
		С целью реализации программы социальной защиты студентов РГСУ, подлежащих призыву на военную службу, оказания им помощи и поддержки, 
		по инициативе Ректора Университета начата работа по согласованию вопроса целевого комплектования команд призывников для воинских частей, 
		расположенных в городе Москве и Московской области.
	</p>	
	<br />
	Цель проекта:	
	<ol>
		<li>Оказание помощи выпускникам ВУЗа, <b>снижение эмоционального и психологического напряжения</b>, обусловленного факторами призыва их на военную службу;</li>
		<li>Удерживание выпускников РГСУ в поле видимости университета, с предоставлением <b>приоритетного поступления в Университет</b> после окончания военной службы;</li>
		<li>Мониторинг состояния военнослужащего, путем взаимодействия с командованием воинских частей и <b>оказание необходимой помощи</b>, в том числе взаимодействие с родителями.</li>
	</ol>
	<br />
	Мы приглашаем:
	<ol>
		<li><span style="color:#1f7cbd; font-weight:bold;">Выпускников колледжа РГСУ</span> (Если призывник единожды воспользовался своим правом на отсрочку, он подлежит, даже если поступит в университет)</li>
		<li><span style="color:#1f7cbd; font-weight:bold;">Выпускников всех ступеней обучения</span>, решивших пройти военную службу после завершения образования;</li>
		<li><span style="color:#1f7cbd; font-weight:bold;">Студентов, обучающихся в настоящий момент</span>, желающих пройти срочную военную службу (Путем оформления академического отпуска).</li>
	</ol>
	<br />
	Требования к кандидату:	
	<ul>
		<li>Гражданство РФ;</li>
		<li>Категория здоровья «А» или «Б»;</li>
		<li>Отсутствие судимости;</li>
		<li>18+.</li>
	</ul>
	<br />
	<p>
		Наличие военного билета открывает огромные перспективы для трудоустройства! 
		Это возможность сделать карьеру в государственных структурах или ведомственных учреждениях. 
		Служба в Вооруженных Силах — зачастую обязательное условие приема на работу, поскольку многие предприятия и структуры вообще не берут в свой штат граждан, 
		не прошедших военную службу.
	</p>
	<p>
		<b>
			Для выпускников Колледжа, желающих продолжить обучение в РГСУ по программам бакалавриата или специалитета, 
			прошедших срочную военную службу, при наличии рекомендации командира воинской части предоставляется 
			<span style="color:#1f7cbd;">преимущественное право при поступлении на бюджетные места</span>.
		</b>
		А для выпускников высшего образования, желающих продолжить обучение в РГСУ, так же предоставляется преимущественное право при поступлении на любой уровень образования в дальнейшем.
	</p>
	<p>
		Военный комиссариат, к которому вы прикреплены – не имеет значения. 
		<span style="color:#1f7cbd;">
			Военная служба будет проходить <b>в Москве и Подмосковье</b> 
		</span>
		<b>(в/ч центрального подчинения и воздушно-космических войск)</b>.
		Призыв осуществляется с 1 апреля по 15 июля.  Весенняя зачетно-экзаменационная сессия 2021 и защита ВКР в случае необходимости будут осуществлены досрочно.
	</p>
	<p>
		По итогу формирования группы будет проведена встреча с представителем военной части для ознакомления.
	</p>
	<p>
		<span style="color:#1f7cbd;">
			Данное информационное сообщение является предварительным запросом.  
			При положительном ответе с вами свяжется представитель военно-учетного стола или заместитель декана факультета для уточнения деталей и ответа на вопросы.
		</span>
	</p>
	<p>
		<b>До 20 мая 2021г. просим дать <span style="color:#1f7cbd;">предварительный</span> ответ</b>.
	</p>
	<br>
	*Задать интересующие вас вопросы вы можете, нажав кнопку «Вопрос».
	<br/>
	**Нажимая «Согласен», вы подтверждаете готовность принять участие в программе. 
	
	<form   method="POST" 
			action="<?=$this->baseUrl($this->url(array('module' => 'student-notification-agreement', 'controller' => 'ajax', 'action' => 'save-agreement')));?>"
			class="form-sna-military"
	>
		<input type="hidden" name="type" value="<?=$this->notification_type?>">
		<input type="hidden" name="mode" value="">
		<textarea name="question" class="sna-military-question hidden" rows="3" placeholder="<?=_('Напишите вопрос')?>" class=""></textarea>
		<button   class="sna-btn-default"                           style="float: right;"               onClick="sendSnaForm($(this).data('mode'));   return false;" data-mode="Согласен"   ><?=_('Согласен')?></button>
		<button   class="sna-btn-default sna-btn-question"          style="float: right;"               onClick="changeSnaForm($(this).data('mode')); return false;" data-mode="Вопрос"     ><?=_('Вопрос')?></button>
		<button   class="sna-btn-default sna-btn-question-2 hidden" style="float: right; width: 150px;" onClick="sendSnaForm($(this).data('mode'));   return false;" data-mode="Вопрос"     ><?=_('Отправить вопрос')?></button>
		<button   class="sna-btn-default"                           style="float: right;"               onClick="sendSnaForm($(this).data('mode'));   return false;" data-mode="Ознакомлен" ><?=_('Ознакомлен')?></button>
	</form>
</div>
<script>
function changeSnaForm(mode)
{
	let form           = $('.form-sna-military');
	let el_mode        = form.find('[name="mode"]');
	let el_question    = form.find('[name="question"]');
	let btn_question   = form.find('.sna-btn-question');
	let btn_question_2 = form.find('.sna-btn-question-2');
	
	el_mode.val(mode);
	el_question.removeClass('hidden');
	btn_question.addClass('hidden');
	btn_question_2.removeClass('hidden');
	
	return false;
}

function sendSnaForm(mode)
{
	let form         = $('.form-sna-military');
	let el_mode      = form.find('[name="mode"]');
	let el_question  = form.find('[name="question"]');
	let btn_question = form.find('.sna-btn-question');
	let btns         = form.find('.sna-btn-default');
	
	var popup    = $('#sna_popup');
	var url      = form.attr('action');
	var level    = 'success';
	var $message = jQuery('');
	
	el_mode.val(mode);	
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




