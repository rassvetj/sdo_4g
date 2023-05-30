<?php 
	$_model				= HM_MyPayments_MyPaymentsModel;	
?>
<style>
	#popup-mypaymentsquestion dd.element  {
		padding-bottom: 5px;
	}
</style>
<div class="my-payments-area">

<div style="" >
	<div id="error-box" class="error-box">
		<div class="ui-widget ui-els-flash-message">
			<div class="ui-state-error ui-corner-all">
				<div class="ui-message-here">
					В связи с проведением технических работ информация на странице может быть не актуальной.
					В ближайшее время данные будут актуализированы.
					<br />
					Присвоим извинения за предоставленные неудобства.
				</div>
			</div>
		</div>
	</div>	
</div>
<br />

<?php if(!empty($this->details)):?>
	<?=$this->render('index/partials/_details.tpl');?>
<?php else:?>

<?php if(empty($this->payments_plan)):?>
	<p><?=_('Нет данных')?></p>
	<br />	
<?php else:?>
	<?php foreach($this->payments_plan as $contract_number => $payments):?>		
		<br />
		<br />
		<table>
			<tr>
				<td colspan="4" style="border: 1px solid white;" >
					<?=_('Сведения о взаиморасчетах по договору')?> 
					№<span style="font-weight:bold;"><?=$contract_number?></span> 
					<?=_('от')?> <span style="font-weight:bold;"><?=$this->info[$contract_number]['contract_date']?></span>
				</td>
			</tr>
			<tr>
				<td colspan="4" style="border: 1px solid white;" >
					<?=_('Ваш УИК')?>: <span style="font-weight:bold;"><?=$this->info[$contract_number]['uik']?></span>
				</td>
			</tr>
			<tr>
				<td colspan="4" style="font-size: 11px; text-align: left; border-top-color: white; border-left-color: white; border-right-color: white;" >
					<?=_('Данные обновлены')?> <?=$this->info[$contract_number]['date_created']?>
				</td>
			</tr>
		
			<tr>		
				
				<td class="caption" style="width:145px;" colspan="2" ><?=_('Ожидаемая дата по графику платежей(до)')?></td>
				<td class="caption" style="width:145px;"><?=_('Ожидаемая сумма по графику платежей')?></td>
				<td class="caption" style="width:145px;"><?=_('Фактически оплачено')?></td>
				<td class="caption" style="width:145px;"><?=_('Долг')?></td>				
			</tr>
			<?php
				$plan_total_sum 	 = 0;
				$plan_total_sum_fact = 0;
				$plan_total_sum_debt = 0;
				$row_num = 0;
			?>
			<?php foreach($payments as $i):?>				
				<?php
					$plan_total_sum 	 += $i['sum'];
					$plan_total_sum_fact += $i['sum_fact'];
					$plan_total_sum_debt += $i['sum_debt'];
					$row_num++;
				?>			
				<tr>
					<td><?=$row_num?></td>
					<td><?=$i['date']?></td>
					<td class="number" ><?=$_model::priceFormat($i['sum'])?></td>
					<td class="number" ><?=$_model::priceFormat($i['sum_fact'])?></td>
					<td class="number" ><?=$_model::priceFormat($i['sum_debt'])?></td>
				</tr>		
			<?php endforeach;?>					
			<tr style="font-weight:bold;">						
				<td colspan="2"><?=_('Итого')?></td>
				<td class="number" ><?=$_model::priceFormat($plan_total_sum)?></td>
				<td class="number" ><?=$_model::priceFormat($plan_total_sum_fact)?></td>
				<td class="number" ><?=$_model::priceFormat($plan_total_sum_debt)?></td>
			</tr>			
		</table>
		<br />
		<br />
		<table>
			<tr>
				<td class="caption" style="width:145px;" colspan="2" ><?=_('Дата поступления')?></td>
				<td class="caption" style="width:145px;"><?=_('Поступившие оплаты')?></td>
				<td class="info-area" style="width:300px; border: 1px solid white;" rowspan="<?=count($this->payments_fact[$contract_number])+2?>" >
					<?=$this->text_info[$contract_number]?>
				</td>
			</tr>
			<?php $row_num = 0; ?>
			<?php if(!empty($this->payments_fact[$contract_number])):?>
				<?php foreach($this->payments_fact[$contract_number] as $i):?>
					<?php $row_num++; ?>
					<tr>
						<td><?=$row_num?></td>
						<td><?=$i['date']?></td>
						<td class="number" ><?=$_model::priceFormat($i['sum'])?></td>
					</tr>
				<?php endforeach;?>
			<?php endif;?>
			<tr style="font-weight:bold;">
				<td colspan="2"  ><?=_('Итого')?></td>
				<td class="number" ><?=$_model::priceFormat(	$this->additional['payments_fact_sum_total'][$contract_number]	)?></td>
			</tr>
		</table>		
	<?php endforeach;?>
<?php endif;?>

<?php endif;?>

<p style="font-size: 11px; color: #9fa8af; line-height: 20px;">
	Сведения о поступлении платежей обновляются в течение 10 дней с момента поступления средств на счет университета
</p>
</div>

<?php if(!empty($this->payments_plan) || !empty($this->details)):?>
	<div class="popup-default" id="popup-mypaymentsquestion" >
		<div class="form-area-default form-area-full-width form-area-mypaymentsquestion" >
			<?=$this->form?>
		</div>
	</div>
	<script>
		$( document ).ready(function() {		
			$('#popup-mypaymentsquestion').dialog({
				resizable: false,
				autoOpen: false,			
				width:440,
				modal: true,
				buttons:
				{
					<?=_('Отправить')?>: function() {
						//$( this ).dialog( "close" );
						$('.form-area-mypaymentsquestion form').submit();
					},
					<?=_('Закрыть')?>: function() {
						$( this ).dialog( "close" );
					}
				}
			});
		});
	</script>
<?php endif;?>



