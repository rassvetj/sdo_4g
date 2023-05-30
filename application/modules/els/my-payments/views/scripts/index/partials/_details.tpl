<?php 
	$_model = HM_MyPayments_MyPaymentsModel;
?>
<?php if(!empty($this->details)):?>
	<?php foreach($this->details as $contract_number => $items):?>
	<?php 
		$rowNum      = 0;
		$firstItem   = reset($items);
		$lastItem    = end($items);
		$urlSum      = str_replace('.', ',', $firstItem['summ_plan']);
		$urlContract = str_replace('.', ',', $contract_number);
		$urldate     = strtotime($firstItem['date_contract']);
		$hasDebt     = $lastItem['balance'] > 0 ? true : false;

		if($hasDebt){
			$urlSum  = str_replace('.', ',', $lastItem['balance']);
			$urldate = strtotime($lastItem['date_contract']);
		}

		$payOnlineUrl = $this->url(array('module'=>'my-payments','controller'=>'pay',  'action'=>'card', 'sum'=>$urlSum,'contract'=>$urlContract,'date'=>$urldate), 'default', true);
		$payTicketUrl = $this->url(array('module'=>'ticket',     'controller'=>'index','action'=>'index','sum'=>$urlSum,'contract'=>$urlContract,'date'=>$urldate), 'default', true);
	
	?>
	<style>
	.my-payments-area .last-row, .my-payments-area .last-ceil{
		border-bottom: 1px solid white;
		border-right: 1px solid white;
		border-left: 1px solid white;
	}
	</style>
	<table>
		<tr>
			<td colspan="4" style="border: 1px solid white;" >
				<?=_('Сведения о взаиморасчетах по договору')?> №<span style="font-weight:bold;"><?=$contract_number?></span> 
				<?=_('от')?> <span style="font-weight:bold;"><?=$firstItem['date_contract']?></span>
			</td>
		</tr>
		<?php if(!empty($firstItem['uik'])):?>
		<tr>
			<td colspan="4" style="border: 1px solid white;" >
				<?=_('Ваш УИК')?>: <span style="font-weight:bold;"><?=$firstItem['uik']?></span>
			</td>
		</tr>
		<?php endif;?>
		<tr>
			<td colspan="4" style="font-size: 11px; text-align: left; border-top-color: white; border-left-color: white; border-right-color: white;" >
				<?=_('Данные обновлены')?> <?=$firstItem['date_created']?>
			</td>
		</tr>
		<tr>
			<td class="caption" style="width:145px;" colspan="2"><?=_('Дата операции')?></td>
			<td class="caption" style="width:145px;"><?=_('Номер ДоговораУОП / Университет')?></td>
			<td class="caption" style="width:145px;"><?=_('Плановая сумма')?></td>
			<td class="caption" style="width:145px;"><?=_('Оплачено')?></td>				
			<td class="caption" style="width:145px;"><?=_('Сальдо')?></td>				
			<td class="caption" style="width:145px;"><?=_('Дней просрочки')?></td>				
			<?/*<td class="caption" style="width:145px;"><?=_('Пени')?></td>*/?>
			<?/*<td class="caption" style="width:145px;"><?=_('Общая задолженность')?></td>*/?>			
		</tr>
		<?php foreach($items as $item):?>
			<?php $rowNum++?>
			<tr>
				<td><?=$rowNum?></td>
				<td><?=$item['date_operation']?></td>
				<td><?=$item['contract_number']?></td>
				<td><?=$_model::priceFormat($item['summ_plan'])?></td>
				<td><?=$_model::priceFormat($item['summ_fact'])?></td>
				<td><?=$_model::priceFormat($item['balance'])?></td>
				<td><?=$item['delay_days']?></td>
				<?/*<td><?=$_model::priceFormat($item['fine'])?></td>*/?>
				<?/*<td><?=$_model::priceFormat($item['total_debt'])?></td>*/?>
			</tr>		
		<?php endforeach;?>
		<tr class="last-row">
			<td colspan="100%" class="last-ceil">
				<div>
					<span style="font-size: 16px; font-weight: bold;"><?=_('Задолженность по договору на')?> <?=$lastItem['date_created']?></span>
				</div>
				<span style="font-weight: bold; font-size: 17px;">
				<?php if($hasDebt):?>
					<span style="color:red;"><?=$_model::priceFormat($lastItem['balance'])?></span>
				<?php else: ?>
					<span style="color:green;"><?=_('отсутствует')?></span>
				<?php endif;?>
				</span>
				<br />				
				<?php /*
				<a target="_blank" class="ui-button btn-my-payments-pay-online" href="<?=$payOnlineUrl?>">Оплатить online</a>
				*/?>
				<a target="_blank" class="ui-button btn-my-payments-get-ticket" href="<?=$payTicketUrl?>">Получить квитанцию</a>
				<br />
				<a  target="_blank" 
					href="#" 
					class="btn-ask-question" 
					data-contract_number="<?=$contract_number?>" 
					data-contract_date  ="<?=strtotime($lastItem['date_contract'])?>" 
					data-total_debt     ="<?=$lastItem['balance']?>" 
					data-update_date    ="<?=strtotime($lastItem['date_created'])?>"
					data-destination_id ="mypaymentsquestion"
 				>Задать вопрос</a>
				<br />
				<a target="_blank" href="https://www.sberbank.ru/ru/person/credits/money/credit_na_obrazovanie" >Кредит с государственной поддержкой</a>
			</td>
		</tr>
	</table>
	<br />
	<br />
	<?php endforeach;?>
<?php endif;?>