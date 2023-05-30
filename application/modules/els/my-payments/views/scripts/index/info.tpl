<?php 
$total_debt			= $this->additional['total_debt'][$this->current_contract_number];

$_model				= HM_MyPayments_MyPaymentsModel;
$total_debt_format	= $_model::priceFormat($total_debt);


if(empty($total_debt)){
	$sum = $this->additional['next_sum'][$this->current_contract_number];	
} else {
	$sum = $total_debt;
}

?>
<div>
	<span style="font-size: 16px; font-weight: bold;"><?=_('Задолженность по основному договору на')?> <?=$this->info[$this->current_contract_number]['date_created']?></span>
</div>
<span style="font-weight: bold; font-size: 17px;">
<?php if(empty($total_debt)):?>
	<span style="color:green;"><?=_('отсутствует')?></span>
<?php else: ?>
	<span style="color:red;"><?=$total_debt_format?></span>
<?php endif;?>
</span>
<br />
<?php /*
<a	target="_blank" 
	class="ui-button btn-my-payments-pay-online"
	href="<?=$this->url(array(	'module' 		=> 'my-payments', 	
								'controller' 	=> 'pay', 	
								'action' 		=> 'card', 
								'sum' 			=> str_replace('.', ',', $sum), 
								'contract' 		=> str_replace('.', ',', $this->current_contract_number),
								'date' 			=> strtotime($this->info[$this->current_contract_number]['contract_date']),
						), 'default', true);?>">
	Оплатить online
</a>
*/?>

<a  target="_blank"
	class="ui-button btn-my-payments-get-ticket"	
	href="<?=$this->url(array(	'module' 		=> 'ticket', 	
								'controller' 	=> 'index', 	
								'action' 		=> 'index', 
								'sum' 			=> str_replace('.', ',', $sum), 
								'contract' 		=> str_replace('.', ',', $this->current_contract_number),
								'date' 			=> strtotime($this->info[$this->current_contract_number]['contract_date']),
						), 'default', true);?>" >
	Получить квитанцию
</a>
<br />
<a 	target="_blank" 
	href="#" 
	class="btn-ask-question" 
	data-contract_number="<?=$this->current_contract_number?>" 
	data-contract_date="<?=strtotime($this->info[$this->current_contract_number]['contract_date'])?>" 
	data-total_debt="<?=$total_debt?>" 
	data-update_date="<?=strtotime($this->info[$this->current_contract_number]['date_created'])?>"
	data-destination_id="mypaymentsquestion"
 >Задать вопрос</a>
<br />
<a target="_blank" href="https://www.sberbank.ru/ru/person/credits/money/credit_na_obrazovanie" >Кредит с государственной поддержкой</a>
