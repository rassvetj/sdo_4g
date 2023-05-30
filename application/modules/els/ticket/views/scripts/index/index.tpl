<?php $form_area_id = 'ticket_form_area'; ?>
<style>
	#filial_id, #filial_id-label {
		margin-left: 5px;
	}
	.ticket-form-area select  {
		height: 28px;
		padding: 0px;
	}
	.ticket-form-area .separator{
		display: none;
		cursor: auto;
	}
	
	.ticket-form-area dd input[type="text"] {
		height: 26px;
		padding: 0px;
	}
	.ticket-form-area {
		width:845px;
		float: left;
	}
	.ticket_groupBase {
		width: 269px;
		float: left;
		padding: 5px;
		border: none;		
	}
	.ticket_groupBase select, .ticket_groupBase dd input[type="text"] {
		width: 268px;		
	}
	.ticket_groupBase dl{
		margin: 0;
	}
	.ticket_groupPayer {		
		width: 65%;		
		padding-right: 0px;
		margin-left: 5px;
	}
	.ticket_groupForWhomPay {
		width: 65%;	
		padding-right: 0px;
		margin-left: 5px
	}
	.ticket_groupPayer dd input[type="text"], .ticket_groupForWhomPay dd input[type="text"], .ticket_groupPayer dd textarea, .ticket_groupForWhomPay dd textarea {
		width: 92%
	}
	.ticket_groupBase_short {
		width: 129.5px;
	}
	
	.ticket_groupBase_middle {
		width: 135px;
	}
	
	.ticket_groupBase_short select, .ticket_groupBase_short dd input[type="text"] {
		width: 128px;
	}
	#main .ticket_groupBase_short dd .hasDatepicker {
		width: 110px;
	}	
	
	.hideElement { display: none!important; }
	.showElement { display: block; }
	
	.ticket-contracts-area {
		max-width:500px;
		float: left;
	}
	.contract_item {
		cursor: pointer;
	}
	.contract_item:hover {
		background-color:#e7f4f8;
	}
	
	.ticket-order-area {
		padding-left: 5px;
		font-size: 12px;
		padding-bottom: 15px;
		max-width:500px;
		float: left;
	}
	
	.ticket-order-area table{
		border-collapse: collapse;		
	}
	
	.ticket-order-area .order-cost td, .ticket-order-area .order-cost th{
		text-align: center;
		border: 1px solid black;
		padding: 3px;
	}
	
	.block-payments{
		display: none;
	}
	
	.block-payments th, .block-payments td {
		border-bottom:0px!important;
	}
	
	.block-payments th:first-child, .block-payments td:first-child {
		border-left:0px!important;		
	}
	
	.block-payments th:last-child, .block-payments td:last-child {		
		border-right:0px!important;		
	}	
	
	.ticket-order-area input{
		width: 75px;
	}
	
	.ticket-order-area .els-inputfile-infoblock{
		display: none;
	}
	
	.hasError {
		color: red;
	}
	
	.hasSuccess {
		color: green;
	}
</style>

<?php if (!$this->gridAjaxRequest):?>	

	<div style="margin-top: 16px; height: 40px; float: left; padding: 5px; display: inline-block; width: 100%;">			
		<div class="_grid_gridswitcher">			
			<?/*
			<a href="<?=$this->baseUrl($this->url(array('module' => 'payment', 'controller' => 'index', 'action' => 'index')));?>">
				<div style="text-decoration: underline;" onclick="$('._grid_gridswitcher div').removeClass('_u_selected');$(this).addClass('_u_selected')" class="ending">
					<?=_('Мои оплаты')?>
				</div>
			</a>	
			*/?>
			<div class="ending _u_selected"><?=_('Сформировать квитанцию')?></div>
		</div>
	</div>	
	<!--
	<div class="ticket-contracts-area" style="padding-left: 5px; font-size: 12px; padding-bottom: 15px;">
		<b>Договоры</b>						
		<br>
		<?php if(!empty($this->contractEducation) || !empty($this->contractLiving)) : ?>
			<span style="color:red; font-size: 10px;"><?=_('выберите договор')?></span>
		<?php else : ?>
			<br>
		<?php endif; ?>
		<hr>
		<?php if(empty($this->contractEducation) && empty($this->contractLiving)) : ?>
			Нет данных			
		<?php else :?>
			<?php if(!empty($this->contractEducation)) : ?>
				<table class="description contract_item" onClick = "
					$('#contract_number').val('<?=$this->contractEducation['contract_number'];?><?=' ('.mb_strtolower($this->contractEducation['type_contract']).')';?>');
					$('#contract_date').val('<?=date('d.m.Y', strtotime($this->contractEducation['date']));?>');
				">	
					<tr><td><?=_('Тип договора:')?></td><td><span class="contract-value"><?=$this->contractEducation['type_contract'];?></span></td></tr>
					<tr><td><?=_('Регистрационный номер договора:')?></td><td><span class="contract-value"><?=$this->contractEducation['contract_number'];?></span></td></tr>
					<tr><td><?=_('Дата заключения:')?> </td><td><span class="contract-value"><?=date('d.m.Y', strtotime($this->contractEducation['date']));?></span></td></tr>
					<tr><td><?=_('Общая сумма по договору:')?> </td><td><span class="contract-value"><?=$this->contractEducation['sum_contract'];?> р.</span></td><tr>
					<tr><td><?=_('Стоимость за год:')?> </td><td><span class="contract-value"><?=$this->contractEducation['sum_payment'];?> р.</span></td><tr>				
				</table>
				<?php if(count($this->contractEducation['additional'])) : ?>
					<a href="" onClick="showAdditional($('#area_education_additional'), $(this)); return false;"><?=_('Подробнее')?></a>
					<div style="display:none; background-color: rgba(191, 217, 230, 0.22); margin-left: 5px;" id="area_education_additional">					
					<?php foreach($this->contractEducation['additional'] as $ad) : ?>						
						<table>
							<tr><td><?=_('Тип договора:')?></td><td><span class="contract-value"><?=$ad['type_contract'];?></span></td></tr>
							<tr><td><?=_('Регистрационный номер договора:')?></td><td><span class="contract-value"><?=$ad['contract_number'];?></span></td></tr>
							<tr><td><?=_('Дата заключения:')?> </td><td><span class="contract-value"><?=date('d.m.Y', strtotime($ad['date']));?></span></td></tr>
							<tr><td><?=_('Общая сумма по договору:')?> </td><td><span class="contract-value"><?=$ad['sum_contract'];?> р.</span></td><tr>
							<tr><td><?=_('Стоимость за год:')?> </td><td><span class="contract-value"><?=$ad['sum_payment'];?> р.</span></td><tr>					
						</table>
						<hr>
					<?php endforeach; ?>
					</div>
				<?php endif; ?>			
				<hr>				
			<?php endif; ?>			
			<?php if(!empty($this->contractLiving)) : ?>
				<table class="description contract_item" onClick = "
					$('#contract_number').val('<?=$this->contractLiving['contract_number'];?><?=' ('.mb_strtolower($this->contractLiving['type_contract']).')';?>')
					$('#contract_date').val('<?=date('d.m.Y', strtotime($this->contractLiving['date']));?>');
				">					
					<tr><td><?=_('Тип договора:')?></td><td><span class="contract-value"><?=$this->contractLiving['type_contract'];?></span></td></tr>
					<tr><td><?=_('Регистрационный номер договора:')?></td><td><span class="contract-value"><?=$this->contractLiving['contract_number'];?></span></td></tr>
					<tr><td><?=_('Дата заключения:')?> </td><td><span class="contract-value"><?=date('d.m.Y', strtotime($this->contractLiving['date']));?></span></td></tr>
					<tr><td><?=_('Общая сумма по договору:')?> </td><td><span class="contract-value"><?=$this->contractLiving['sum_contract'];?> р.</span></td><tr>
					<tr><td><?=_('Стоимость за год:')?> </td><td><span class="contract-value"><?=$this->contractLiving['sum_payment'];?> р.</span></td><tr>				
				</table>
				<?php if(count($this->contractLiving['additional'])) : ?>
					<a href="" onClick="showAdditional($('#area_living_additional'), $(this)); return false;">Подробнее</a>
					<div style="display:none; background-color: rgba(191, 217, 230, 0.22); margin-left: 5px;" id="area_living_additional">					
					<?php foreach($this->contractLiving['additional'] as $ad) : ?>						
						<table>
							<tr><td><?=_('Тип договора:')?></td><td><span class="contract-value"><?=$ad['type_contract'];?></span></td></tr>
							<tr><td><?=_('Регистрационный номер договора:')?></td><td><span class="contract-value"><?=$ad['contract_number'];?></span></td></tr>
							<tr><td><?=_('Дата заключения:')?> </td><td><span class="contract-value"><?=date('d.m.Y', strtotime($ad['date']));?></span></td></tr>
							<tr><td><?=_('Общая сумма по договору:')?> </td><td><span class="contract-value"><?=$ad['sum_contract'];?> р.</span></td><tr>
							<tr><td><?=_('Стоимость за год:')?> </td><td><span class="contract-value"><?=$ad['sum_payment'];?> р.</span></td><tr>					
						</table>
						<hr>
					<?php endforeach; ?>
					</div>
				<?php endif; ?>	
			<?php endif; ?>
		<?php endif; ?>		
	</div>	
	<div class="ticket-contracts-area" style="padding-left: 5px; font-size: 12px; padding-bottom: 15px;">		
		<b><?=_('График платежей')?></b>
		<br>		
		<br>		
		<hr>
		<?php if($this->schedulePayments) : ?>
			<table style="border-collapse: collapse;">
				<tr>
					<th style="text-align: center; border: 1px solid black; padding: 3px;"><?=_('Номер договора')?></th>
					<th style="text-align: center; border: 1px solid black; padding: 3px;"><?=_('Дата оплаты')?></th>
					<th style="text-align: center; border: 1px solid black; padding: 3px;"><?=_('Сумма')?></th>
					<th style="text-align: center; border: 1px solid black; padding: 3px;"><?=_('Семестр')?></th>
				</tr>
			<?php $totalSum = 0; ?>	
			<?php foreach($this->schedulePayments as $sp) : ?>
				<?php
					$timestamp = strtotime($sp['date_payment']);
					if($timestamp <= time()){
						$totalSum = $totalSum + $sp['sum'];
					}					
				?>
				<tr>
					<td style="text-align: center; border: 1px solid black; padding: 3px;"><?=$sp['contract_number'];?></td>
					<td style="text-align: center; border: 1px solid black; padding: 3px;"><?=date('d.m.Y', $timestamp);?></td>
					<td style="text-align: center; border: 1px solid black; padding: 3px;"><?=$sp['sum'];?> р.</td>
					<td style="text-align: center; border: 1px solid black; padding: 3px;"><?=$sp['period'];?></td>
				</tr>
			<?php endforeach; ?>			
			</table>			
		<?php endif; ?>		
	</div>
	-->
	<? /*
	<div class="ticket-contracts-area" style="padding-left: 5px; font-size: 12px; padding-bottom: 15px;">	
		<b><?=_('ВАШ УИК для оплаты')?></b>: 
		<?php foreach($this->orderCost as $period => $cost): ?>
			<?=$cost[uik];?>
			<?php break; ?>
		<?php endforeach; ?>
		<br><br>	
		<b><?=_('Ваши оплаты')?></b>
		<br>		
		<br>		
		<hr>
		<?php if($this->contractEducation['contract_number'] && count($this->historyPayed)) : ?>
			<?php $sumPayed = 0; ?>
			<table style="border-collapse: collapse; width: 100%;">
				<tr>
					<th style="text-align: center; border: 1px solid black; padding: 3px;"><?=_('Номер договора')?></th>
					<th style="text-align: center; border: 1px solid black; padding: 3px;"><?=_('Сумма')?></th>
					<th style="text-align: center; border: 1px solid black; padding: 3px;"><?=_('Дата оплаты')?></th>
				</tr>
				<?php if(!empty($this->historyPayed)):?>
				<?php foreach($this->historyPayed as $h): ?>
					<?php $sumPayed = $sumPayed + $h['sum_payment']; ?>
					<tr>
						<td style="text-align: center; border: 1px solid black; padding: 3px;"><?=$h['contract_number'];?></td>					
						<td style="text-align: center; border: 1px solid black; padding: 3px;"><?=$h['sum_payment'];?> р.</td>					
						<td style="text-align: center; border: 1px solid black; padding: 3px;"><?=($h['date_payment'])?(date('d.m.Y', strtotime($h['date_payment']))):_('нет');?></td>
					</tr>		
				<?php endforeach; ?>			
				<?php endif;?>
			</table>
			<hr>			
			<?=_('Всего оплачено по договору')?> №<?=$this->contractEducation['contract_number'];?>  на <?=date('d.m.Y');?>: <b><?=round($sumPayed, 2);?> р.</b><br>
			<?php
				if($sumPayed < $totalSum){
					#echo '<span style="color:red;">Необходимо оплатить еще <b>'.($totalSum - $sumPayed).'</b> р.</span>';
					echo '&nbsp;<button onClick="fillForm(\''.ceil($totalSum - $sumPayed).'\', \''.$this->contractEducation['contract_number'].' ('.mb_strtolower($this->contractEducation['type_contract']).')'.'\', \''.date('d.m.Y', strtotime($this->contractEducation['date'])).'\');">'._('Заполнить квитанцию').'</button>';
				} else {
					#echo 'Переплата: <b>'.round(($sumPayed - $totalSum), 2).'</b> р.';
				}
			?>
		<?php endif; ?>
	</div>
	*/?>
	<div class="ticket-form-area" id="<?=$form_area_id;?>" style="padding-left: 5px; font-size: 12px; padding-bottom: 15px;">
		<?=$this->form;?>
		
		<?php #if($_GET['dev'] == 1):?>
			<div style="margin-left: -10px; padding-top: 5px;">
				<?php if(!empty($this->link_fast_pay_education)):?>
					<a href="<?=$this->link_fast_pay_education?>" target="_blank" class="hidden link_fast_pay link_fast_pay_education" ><img src="/images/paysber.svg"></a>
				<?php endif;?>
				<?php if(!empty($this->link_fast_pay_hostel)):?>
					<a href="<?=$this->link_fast_pay_hostel?>" target="_blank" class="hidden link_fast_pay link_fast_pay_hostel" ><img src="/images/paysber.svg"></a>
				<?php endif;?>
			</div>
			<script>
				$( document ).ready(function() {
					updateLinkFastPay($('.ticket-form-area form'));
				});
				
				$('body').on('change', '.ticket-form-area form', function() {
					updateLinkFastPay($(this));
				});
				
				function updateLinkFastPay(form)
				{
					var service_type_id = form.find('[name="service_type_id"]').val();
					
					$('.link_fast_pay').addClass('hidden');
					
					if(service_type_id == '<?=HM_Ticket_TicketModel::SERVICE_TYPE_EDUCATION?>'){
						$('.link_fast_pay_education').removeClass('hidden');
					}
					
					if(service_type_id == '<?=HM_Ticket_TicketModel::SERVICE_TYPE_HOSTEL?>'){
						$('.link_fast_pay_hostel').removeClass('hidden');
					}
					
				}
			</script>
		<?php #endif;?>
	</div>
	
	<div class="ticket-order-area" id="ticket-order-area">
		<b><?=_('Прикрепить сведения об оплате')?></b>
		<br>
		<br>
		<hr>
		<?php if(empty($this->orderCost)): ?>
			<p><?=_('Нет данных')?></p>
		<?php else: ?>
			<p><?=_('Для загрузки использовать файлы форматов: jpg, jpeg, png, pdf. Максимальный размер файла &ndash; 2 Mb')?></p>
			<br>
			<p id="area-message"></p>
			<table class="order-cost">
				<tr>
					<th><?=_('Год обучения')?></th>
					<?/*<th><?=_('Стоимость года по приказу')?></th>*/?>
					<th><?=_('Оплаты')?></th>
				</tr>				
				<?php
				$form = $this->formOrder;
				foreach ($form->getElements() as $element) {
					$lbl = $element->getDecorator('label');
					if($lbl instanceof HM_Form_Decorator_Label ){																								
						$lbl->setOption('tag', null);
					}
				}
				$count = 0;
				?>	
				<?php foreach($this->orderCost as $period => $cost): ?>
					<?php
					$count++;
					$form->period->setValue($period);					
					$form->postfix->setValue('__'.$count);										
					?>
					<tr>
						<td><?=$period;?></td>
						<?/*<td><?=$cost[cost];?></td>*/?>
						<td style="text-align: left; padding:0px">
							<a href="#" onClick="showAdditional($(this).next('.block-payments'), $(this)); return false;"><?=_('Загрузить')?></a>
							<div class="block-payments">
								<form method="<?=$form->getMethod();?>" action="<?=$form->getAction();?>" name="<?=$form->getName();?>__<?=$count;?>" id="<?=$form->getName();?>__<?=$count;?>">
									<?=$form->period->renderViewHelper();?>
									<?=$form->postfix->renderViewHelper();?>									
									<table>
										<tr>
											<th><?=_('Сумма')?></th>
											<th><?=_('Дата')?></th>
											<th><?=_('Квитанция')?></th>
											<th>&nbsp;</th>
										</tr>
										<?php if(!empty($this->orderPayments[$period])): ?>											
											<?php foreach($this->orderPayments[$period] as $values): ?>
												<tr>
													<td><?=$values['sum']?></td>	
													<td><?=$values['date_payment']?></td>	
													<td>
													<?php if(!empty($values['file_id'])): ?>
														<a href="<?=$this->baseUrl($this->url(array('module' => 'ticket', 'controller' => 'order', 'action' => 'get-file', 'id'=> $values['file_id'])));?>" target="_blank"><?=_('Скачать')?></a></td>
													<?php else: ?>
														<p>Нет</p>
													<?php endif; ?>
													<td>&nbsp;</td>													
												</tr>
											<?php endforeach; ?>
										<?php endif; ?>																				
										<tr>
											<td><?=$form->sum->renderViewHelper();?></td>											
											<td><?=$this->datePicker('date_payment__'.$count, '', array());?></td>
											<td><?=$form->file->renderViewHelper();?></td>
											<td><?=$this->formSubmit('submit', 'Сохранить', array('onClick' => 'sentForm($(this).closest(form));')) ?></td>
										</tr>											
									</table>
								</form>								
							</div>						
						</td>						
					</tr>					
				<?php endforeach; ?>
			</table>		
		<?php endif; ?>
	</div>
	<br>
	<div><?=$this->grid;?></div>	
<?php else : ?>
	<?=$this->grid;?>
<?php endif;?>
<?php $this->inlineScript()->captureStart()?>
	$(document.body).delegate('.block-payments form', 'submit', function(event) {
		event.preventDefault();		
	});
	
	function sentForm(form){
		
		var areaMsg = $('#area-message');
		areaMsg.html('');
		var form_id 	= form.attr('id');		
		var form_action = form.attr('action');
		var form_data 	= form.serialize();	
		form.find("#submit").prop('disabled', true);
		$.ajax(form_action, {
			type: 'POST',
			global: false,
			data: form_data,
			dataType: 'json'				
		}).done(function (data) {
			if (typeof data.message !== "undefined"){ areaMsg.html(data.message); }
			
			if (typeof data.error !== "undefined"){
				if(data.error == 1){
					areaMsg.removeClass('hasSuccess').addClass('hasError');
				} else {
					if (typeof data.sum !== "undefined" && typeof data.date_payment !== "undefined" && typeof data.file_id !== "undefined") {
						html = '<tr><td>'+data.sum+'</td><td>'+data.date_payment+'</td><td><a href="<?=$this->baseUrl($this->url(array('module' => 'ticket', 'controller' => 'order', 'action' => 'get-file')));?>/id/'+data.file_id+'" target="_blank"><?=_('Скачать')?></a></td><td></td></tr>';
						form.find('tr').eq(-2).after(html);
						areaMsg.removeClass('hasError').addClass('hasSuccess');
						form.trigger('reset');
						form.find('.cancel-upload a').click();						
					}							
				}				
			}
			form.after(data);									
			form.find("#submit").prop('disabled', false);																			
		}).fail(function () {				
			var $message = jQuery("<div><?= _('Произошла ошибка. Попробуйте ещё раз'); ?></div>").appendTo('#' + form_id);
			jQuery.ui.errorbox.clear($message);
			$message.errorbox({level: 'error'});
			form.find("#submit").prop('disabled', false);
		}).always(function () {				
		});	
		
	}




	function fillForm(money, contract_number, contract_date){
		$('#sum1').val(money);
		$('#service_type_id').val('EDUCATION').trigger( "change" );
		$('#service_education_id').val(1).trigger( "change" );
		$('#contract_number').val(contract_number);
		$('#contract_date').val(contract_date);
		$('#period_id').val(1);
		
	}

	function showAdditional(area, lnk){
		if(area.is(':visible')){
			area.hide();
			lnk.text('Загрузить');
		} else {
			area.show();
			lnk.text('Скрыть');
		}
	}
	
	function changeTicketForm(type_id){
		if(type_id == '<?=HM_Ticket_TicketModel::SERVICE_TYPE_LAUNDRY;?>'){
			$('#sum1').val(100).prop('readonly', true);
			$('#sum2').val(0).prop('readonly', true);
			$('#submit').hide();
		} else {
			$('#sum1').prop('readonly', false);
			$('#sum2').prop('readonly', false);
			$('#submit').show();
		}
		
		var dataShow = JSON.parse('<?=Zend_Json::encode(HM_Ticket_TicketModel::getFormShowElements()); ?>');
		if (typeof dataShow[type_id] != "undefined"){	
			if(dataShow[type_id].length > 0){								
				$('#formticket .ticket_groupBase .ticket_element_container').removeClass('showElement').addClass('hideElement');							
				$.each(dataShow[type_id], function( index, element_id ) {				  
					$('#formticket .ticket_groupBase #'+element_id).closest(".ticket_element_container").removeClass('hideElement').addClass('showElement');					
				});			
			}
		}
		
		var dataHideBlock = JSON.parse('<?=Zend_Json::encode(HM_Ticket_TicketModel::getFormHidedBlocks()); ?>');
		if (typeof dataHideBlock[type_id] != "undefined"){	
			if(dataHideBlock[type_id].length > 0){				
				$('#formticket fieldset').removeClass('hideElement').addClass('showElement');			
				$.each(dataHideBlock[type_id], function( index, block_id ) {				  				
					$('#formticket #fieldset-'+block_id).removeClass('showElement').addClass('hideElement');					
				});			
			}
		}
	}
	
	
	function setActiveService(filial_name, isReset){
		if(isReset === true){
			$('#service_type_id').val('<?=HM_Ticket_TicketModel::SERVICE_TYPE_EDUCATION;?>');
		}
		changeTicketForm('<?=HM_Ticket_TicketModel::SERVICE_TYPE_EDUCATION;?>');
		
		var main_list = ['<?=mb_strtolower(HM_Ticket_Requisite_RequisiteModel::MAIN_ORGANIZATION);?>', '<?=mb_strtolower(HM_Ticket_Requisite_RequisiteModel::MAIN_REQUISITE_NAME);?>'];
		if($.inArray(filial_name.toLowerCase(), main_list) === -1){ <?/*Филиал выбран*/?>
			var emabledFilialTicketServices = JSON.parse('<?=Zend_Json::encode(HM_Ticket_TicketModel::enabledFilialTicketServices()); ?>');
			if (typeof emabledFilialTicketServices != "undefined"){				
				$('#service_type_id option').each(function( index ) {										
					if($.inArray($( this ).attr('value'), emabledFilialTicketServices) === -1){
						$( this ).prop( "disabled", true );			  	
					}
				});
			}
		} else {
			$('#service_type_id option').prop( "disabled", false );
		}		
	}
	
	function changeHotelServiceList(hotel_id){		
		$('#service_hotel_id').val('');
		if(hotel_id == '<?=HM_Ticket_TicketModel::HOTEL_CHAIKOVSKY;?>'){			
			$('#service_hotel_id option[value="<?=HM_Ticket_TicketModel::HOTEL_SERVICE_MEDICAL;?>"]').prop( "disabled", false );
		} else {			
			$('#service_hotel_id option[value="<?=HM_Ticket_TicketModel::HOTEL_SERVICE_MEDICAL;?>"]').prop( "disabled", true );
		}
	}
	
	function updatePaymentButton(filial_id){ <?/* доступность кнопки оплаты по карте */?>
		var activeList = JSON.parse('<?=Zend_Json::encode($this->cardPaymentOrganizations);?>');		
		if($.inArray(parseInt(filial_id), activeList) === -1){			
			$('#btn_pay_card').prop( "disabled", true );	
		} else {			
			$('#btn_pay_card').prop( "disabled", false );	
		}
	}
	
	changeTicketForm('<?=HM_Ticket_TicketModel::SERVICE_TYPE_EDUCATION;?>'); <? /** по умолчанию скрываем лишние элементы**/ ?>
	changeHotelServiceList('');	
	setActiveService($('#filial_id option:selected').text());
<?php $this->inlineScript()->captureEnd()?>

<?/* отправка формы*/?>
<?php $this->inlineScript()->captureStart()?>
$(document.body).delegate('#<?=$form_area_id;?> form', 'submit', _.debounce(function (event) {
		
		var form_id 	= $(this).attr('id');
		
		//if($('#'+form_id+' #submit').is(":hidden")){ return false; }
		
		var form_action = $(this).attr('action');
		var form_data 	= $(this).serialize();	
		$('#'+form_id+' #submit').prop('disabled', true);	
		$('#'+form_id+' #btn_pay_card').prop('disabled', true);

		
		//console.log(form.find("#submit").is(":hidden"));
		//return false;
				
		
		$.ajax(form_action, {
				type: 'POST',
				global: false,
				data: form_data,				
			}).done(function (data) {		
				_.defer(function () {					
					$('#<?=$form_area_id;?>').html('');
					$('#<?=$form_area_id;?>').append(data);									
					$('#'+form_id+' #submit').prop('disabled', false);
					$('#'+form_id+' #btn_pay_card').prop('disabled', false);					
					setActiveService($('#'+form_id+' #filial_id option:selected').text());
					changeTicketForm($('#'+form_id+' #service_type_id').val()); <? /** по умолчанию скрываем лишние элементы**/ ?>					
					changeHotelServiceList($('#'+form_id+' #hotel_id').val());
					
				});
			}).fail(function () {				
				var $message = jQuery("<div><?= _('Произошла ошибка. Попробуйте ещё раз'); ?></div>").appendTo('#' + form_id);
				jQuery.ui.errorbox.clear($message);
				$message.errorbox({level: 'error'});
				$('#'+form_id+' #submit').prop('disabled', false);
			}).always(function () {				
		});	
		
	}, 50));

	$(document.body).delegate('#<?=$form_area_id;?> form', 'submit', function(event) {
		event.preventDefault();
	});
	
<?php $this->inlineScript()->captureEnd()?>

