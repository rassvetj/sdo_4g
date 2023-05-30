<div style="font-size: medium;">		
	У Вас имеются академические <a href="/student-debt/">задолженности</a>.	
</div>
<?php $this->inlineScript()->captureStart()?>
		$( document ).ready(function() {
			setTimeout(function(){
				$('.dialog-info-debt, .dialog-info-debt-payment').dialog('close');
				$('.dialog-info-debt').dialog('open');				
			}, 1000);
			
			
			$('.dialog-info-debt').dialog({
				resizable: false,
				autoOpen: false,
				width:"50%",
				height:350,				
				modal: true
			});

			$('.form-debt-confirm input[name="debt-agree"]').change(function(){				
				if(	$(this).is(':checked')	)	{ $('.btn-debt-confirm').removeClass('hidden'); }
				else							{ $('.btn-debt-confirm').addClass('hidden');	}
			});
			
		});
<?php $this->inlineScript()->captureEnd()?>
<div class="dialog-info-debt"  title="Уведомление">
	<p>Уважаемый студент!</p>
	<br />
	<p>Обращаем внимание, что у Вас имеются академические задолженности.</p>
	<p>Вам необходимо ликвидировать указанные задолженности в пределах года с момента их образования в соответствии с утвержденным 
	<a href="http://rgsu.net/for-students/timetable/file/#tab5" target="_blank">графиком ликвидации академических задолженностей</a>.</p>
	<p>Задолженности по итогам зимней (летней) сессии прошлого учебного года необходимо ликвидировать в срок не позднее 2-х недель до начала зимней (летней) сессии текущего учебного года, 
	для студентов выпускного курса – не позднее 2-х недель до начала периода дипломирования.</p>
	<p>Студенты, не ликвидировавшие академические задолженности в установленные сроки, будут отчислены из Университета!</p>
	<p>Ваш РГСУ.</p>
	<br />	
	<form method="POST" class="form-debt-confirm" name="form-debt-confirm" action="<?=$this->url(array('module' => 'student-notification-agreement', 'controller' => 'index', 'action' => 'confirm'), 'default', true); ?>">
		<input type="hidden" name="type" value="<?=HM_StudentNotification_Agreement_AgreementModel::TYPE_DEBT?>" >		
		<dd>
			<input type="checkbox" name="debt-agree" id="debt-agree" value="1">
			<label style="user-select:none;" for="debt-agree">Я ознакомлен со своими долгами</label>		
		</dd>
		<dd>
			<input type="submit" class="btn-debt-confirm hidden" value="Сохранить">		
		</dd>
	</form>
	
</div>