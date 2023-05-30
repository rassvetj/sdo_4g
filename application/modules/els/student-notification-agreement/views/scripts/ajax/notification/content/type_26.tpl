<style>
	.fin-notice p {
		text-indent: 20px;
		padding-bottom: 0px;
    	margin-bottom: 0px;
    	margin-top: 0px;
    	padding-top: 0px;
	}
	
	.sna-btn-confirm{
		display: none!important;		
	}
	
	.vacc-item .vacc-lbl,  .vacc-item .vacc-el{
		width: 45%;
		float: left;
	}
	
	.vacc-item {
		padding-top: 15px;
		padding-bottom: 10px;
	}
	
	.vacc-item-file .vacc-lbl, .vacc-item-file .vacc-el {
		width: 98%;
		float: none;
	}
	.vacc-item-file .vacc-el .ui-button {
		background: none;
		float: left;
		width: 96%;
		text-align: left;
	}
	.vacc-item-file .vacc-el .ui-button>span {
		display: none!important;
	}
	
	.vacc-item-file .els-inputfile-infoblock span{
		float: left;
		display: block;
		width: 98%;
	}
	
	.vacc-item-file .els-inputfile-infoblock {
		padding-left: 10px;
		width: 100%;
		display: block;
	}
	
	.vacc-area .ui-button {
		color: #5ecff5!important;
	}
	
	.disable {
		pointer-events: none;
		opacity: 0.5;
	}
	
	.vacc-additional-item .vacc-el{
		padding-bottom: 1px;
		width: 60%;
	}
	
	.vacc-additional-item .vacc-lbl{
		width: 30%;		
	}
	
	.vacc-additional-item .vacc-el input, .vacc-el input {
		width: 100%;
	}
	
	#passport_series, #passport_number, #birth_date, #vaccination_date, #vaccine_series, #policy_number{
		text-align: center;
	}
	
</style>
<div class="fin-notice" style="3width: 900px;">	
	<p>
		Уважаемые студенты!
	</p>
	<p>		
		В связи с проведением сезонной вакцинации в Российской Федерации Администрация РГСУ рекомендует Вам бесплатно провести вакцинацию против гриппа 
		в кабинете вакцинации 1 этаж поликлиники ГБУЗ «ГКБ им. Братьев Бахрушиных ДЗМ» по адресу г. Москва, улица Стромынка, д.7, к.2. (часы работы в будние дни 9.00-16.00), 
		в лечебных учреждениях по месту прикрепления на медицинское обслуживание или в мобильных пунктах вакцинации у станций метро и в МФЦ. 
	</p>
	<div class="vacc-area">
		<div class="vacc-message-area"></div>
		<form method="<?=$this->form->getMethod() ?>" action="<?=$this->form->getAction()?>" class="vacc-form" onSubmit="sendVaccinationForm($(this)); return false;" >
			<?=$this->form->type->renderViewHelper()?>
			
			<div class="vacc-item">
				<div><?=$this->form->status->renderViewHelper()?></div>
				<br />
			</div>	
			
			<div class="vacc-item vacc-item-<?=HM_Survey_SurveyModel::STATUS_VACCINATION_1?>  hidden">
				<div class="vacc-lbl" ><?=$this->form->policy_number->renderLabel()?></div>
				<div class="vacc-el"  ><?=$this->form->policy_number->renderViewHelper()?></div>				
			</div>
			
			<div class="vacc-item vacc-item-<?=HM_Survey_SurveyModel::STATUS_VACCINATION_1?> hidden">
				<div class="vacc-el"  style="width: 1%; clear: both; min-width: 10px;"><?=$this->form->vaccination_confirm->renderViewHelper()?></div>
				<div class="vacc-lbl" style="width: 98%; padding-top: 3px;" ><?=$this->form->vaccination_confirm->renderLabel()?></div>				
			</div>			
			<br />
			<div class="vacc-item vacc-item-<?=HM_Survey_SurveyModel::STATUS_VACCINATION_1?> vacc-item-<?=HM_Survey_SurveyModel::STATUS_VACCINATION_4?> hidden">
				<?=$this->form->getElement('save_button')?>
			</div>
		</form>
	</div>
	<div style="clear:both;"></div>
	<br />
	<p>
		С уважением, Ваш РГСУ.
	</p>	
</div>