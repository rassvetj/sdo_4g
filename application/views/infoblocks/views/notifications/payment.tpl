<div style="font-size: medium;">		
	У Вас имеется <a href="/payment/">задолженность по оплате</a>.	
</div>
<?php $this->inlineScript()->captureStart()?>
		$( document ).ready(function() {
			setTimeout(function(){
				$('.dialog-info-debt, .dialog-info-debt-payment').dialog('close');
				$('.dialog-info-debt-payment').dialog('open');				
			}, 1000);
			
			
			$('.dialog-info-debt-payment').dialog({
				resizable: false,
				autoOpen: false,
				width:"40%",
				height:265,				
				modal: true
			});

			$('.form-debt-payment-confirm input[name="debt-agree"]').change(function(){				
				if(	$(this).is(':checked')	)	{ $('.btn-debt-confirm').removeClass('hidden'); }
				else 							{ $('.btn-debt-confirm').addClass('hidden'); 	}
			});
			
		});
<?php $this->inlineScript()->captureEnd()?>
<div class="dialog-info-debt-payment"  title="Уведомление">
	<p>Уважаемый студент!</p>
	<br />
	<p>Обращаем внимание, что у Вас имеется задолженность по оплате.</p>	
	<p>Студенты, не ликвидировавшие задолженности в установленные сроки, будут отчислены из Университета!</p>	
	<p>Ваш РГСУ.</p>
	<p style="text-align: center;"><a href="<?=$this->url(array('module' => 'payment', 'controller' => 'index', 'action' => 'index'), 'default', true); ?>">Посмотреть долг</a></p>
	<br />	
	<form method="POST" class="form-debt-payment-confirm" name="form-debt-payment-confirm" action="<?=$this->url(array('module' => 'student-notification-agreement', 'controller' => 'index', 'action' => 'confirm'), 'default', true); ?>">
		<input type="hidden" name="type" value="<?=HM_StudentNotification_Agreement_AgreementModel::TYPE_PAYMENT?>" >		
		<dd>
			<input type="checkbox" name="debt-agree" id="debt-agree-payment" value="1">
			<label style="user-select:none;" for="debt-agree-payment">Я ознакомлен со своими долгами</label>		
		</dd>
		<dd>
			<input type="submit" class="btn-debt-confirm hidden" style="color: #5ecff5;" value="Сохранить">		
		</dd>
	</form>
	
</div>