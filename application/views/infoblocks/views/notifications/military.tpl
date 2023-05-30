<style>
	#notification { display:none; }
</style>';
<div style="font-size: medium;">
</div>
<?php $this->inlineScript()->captureStart()?>
		$( document ).ready(function() {
			setTimeout(function(){
				$('.dialog-info-debt, .dialog-info-military').dialog('close');
				$('.dialog-info-military').dialog('open');				
			}, 1000);
			
			
			$('.dialog-info-military').dialog({
				resizable: false,
				autoOpen: false,
				width:"40%",
				height: 380,
				modal: true
			});

			$('.form-military-confirm input[name="debt-agree"]').change(function(){				
				if(	$(this).is(':checked')	)	{ $('.btn-debt-confirm').removeClass('hidden'); }
				else 							{ $('.btn-debt-confirm').addClass('hidden'); 	}
			});
			
		});
<?php $this->inlineScript()->captureEnd()?>
<div class="dialog-info-military"  title="Уведомление" style="height:290px;     text-indent: 15px;">
	Уважаемый <?=$this->fio?>!
	<p>Bнформируем, что Вам необходимо до 25.09.2019 г. предоставить оригиналы документов воинского учёта.</p>
	<p>Документами воинского учёта являются: удостоверение гражданина, подлежащего призыву на военную службу или военный билет (для отслуживших срочную службу, либо получивших билет  по состоянию здоровья).</p>
	<p>Оригиналы документов (для снятия копии) необходимо предоставить в подразделения отдела централизованного деканата на любой из площадок:</p>
		<ul style="list-style: none; margin-left:0px;">
			<li>- ул. Вильгельма Пика, д. 4, корпус 5 (строение 8), 3 этаж, зона коворкинга;</li>
			<li>- ул. Стромынка, д.18, каб. 318;</li>
			<li>- ул. Лосиноостровская, д.24, каб.218.</li>
		</ul>
		
		<p>После предоставления данных вы сможете через несколько дней получить на руки Справку (форма 2), которая является основанием для отсрочки от призыва на военную службу. Справка Вами лично предъявляется в военный комиссариат по месту воинского учёта (регистрации).</p>
		<p>В дальнейшем, с началом каждого учебного года, при переходе с курса на курс и до окончания обучения Справка будет отсылаться администрацией РГСУ в военные комиссариаты.</p>
		<p>В случае отсутствия документов воинского учёта,  потери, несоответствия записей, за помощью обращайтесь в Военно-учётный стол РГСУ: ул.Вильгельма Пика -д.4, корпус 2, этаж 3, помещение № 3 (+7-495-255-67-67, добавочные номера 16-87, 30-50, 30-51). </p>
		<br />
		С любовью и заботой, Ваш РГСУ.
		<br />
		<br />
	<form method="POST" class="form-military-confirm" name="form-debt-payment-confirm" action="<?=$this->url(array('module' => 'student-notification-agreement', 'controller' => 'index', 'action' => 'confirm'), 'default', true); ?>">
		<input type="hidden" name="type" value="<?=HM_StudentNotification_Agreement_AgreementModel::TYPE_MILITARY?>" >		
		<dd>
			<input type="checkbox" name="debt-agree" id="debt-agree-payment" value="1">
			<label style="user-select:none;" for="debt-agree-payment">Я ознакомлен</label>		
		</dd>
		<dd>
			<input type="submit" class="btn-debt-confirm hidden" style="color: #5ecff5;" value="Сохранить">		
		</dd>
	</form>
	
</div>