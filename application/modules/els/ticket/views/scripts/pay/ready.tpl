<style>
@media print {
	.header, footer, .back, .statusbar, .additional-links, .weekparity, .button, .hm-page-support, .breadcrumbs, #header, .tab-bar, #footer, .ajax-spinner-wrapper{
		display:none;
	}
}
</style>
<div class="calc-form">
    <p><span style="line-height: 1.6;">Уважаемый(ая) <?=$this->LastName;?> <?=$this->FirstName;?>!</span></p>
    <p>Это сообщение подтверждает произведенный Вами платеж по банковской карте.<br />
    <br />
    По вопросам, связанным с Вашим платежом, Вы можете обратиться в банк, выдавший Вашу карту.<br />
    <br />
    Информация о платеже:<br />
    <br />
    ID транзакции: <?=$this->orderNumber;?><br />
    Владелец карты: <?=$this->cardholderName;?><br />
    Номер банковской карты: <?=$this->pan;?><br />
    Дата оплаты: <?=(!empty($this->authDateTime))?(date("Y-m-d H:i:s", $this->authDateTime/1000)):('');?><br />
    Сумма: <?=$this->approvedAmount/100?> руб.</p>
    <p><em>Данный документ подтверждает проведение транзакции и не подтверждает факт получения денежных средств адресатом.</em></p>
	<a href="<?=$this->url(array('module' => 'ticket', 'controller' => 'index', 'action' => 'index'), 'default', true);?>" class="back">Назад</a>
</div>

<div class="row">
	<div class="large-2 columns end"><a class="button success expand" href="#" onclick="window.print();">Печать</a></div>
</div>
