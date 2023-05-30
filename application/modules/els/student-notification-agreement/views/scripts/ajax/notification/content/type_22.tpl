<style>
	.fin-notice p {
		text-indent: 20px;
		padding-bottom: 0px;
    	margin-bottom: 0px;
    	margin-top: 0px;
    	padding-top: 0px;
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
	
	<p>
		Во исполнение требования Федерального закона "О воинской обязанности и военной службе" от 28.03.1998 N 53-ФЗ статьи 4 оповещаю Вас о вызове (повестке) 
		военного комиссариата <?=$this->commissariat_name?> на <b><?=$this->date_arrival?></b> в <b><?=$this->time_arrival?></b>, 
		по адресу: <b><?=$this->commissariat_address?></b>.
	</p>
	<p>
		Повестку военного комиссариата Вы можете получить в отделе мобилизационной подготовки и секретного делопроизводства 
		по адресу: г. Москва, ул. Вильгельма Пика, д. 4 , к. 1, кб. 417.
	</p>
	<a target="_blank" href="<?=$this->url(array('module' => 'military', 'controller' => 'file', 'action' => 'get', 'id' => $this->info_id), 'default', true);?>">
		Скачать файл
	</a>
	
	
	<form method="POST" action="<?=$this->baseUrl($this->url(array('module' => 'student-notification-agreement', 'controller' => 'ajax', 'action' => 'save-agreement')));?>" 
			  onSubmit="sendFormBaseSNA($(this)); return false;" 			  
		>
		<input type="hidden" name="type"        value="<?=$this->notification_type?>">
		<input type="hidden" name="external_id" value="<?=$this->external_id?>">
		<dd>
			<input type="submit" value="<?=_('Я ознакомлен')?>" class="sna-btn-default" style="float: right;">
		</dd>			
	</form>
	
</div>
<script>
function sendFormBaseSNA(form)
{
	var btn      = form.find('[type="submit"]');
	var popup    = $('#sna_popup');
	var url      = form.attr('action');
	var level    = 'success';
	var $message = jQuery('');
	
	btn.addClass('btn-in-progress');		
	jQuery.ui.errorbox.clear($message);
		
	$.ajax(url, {
		type  	 : 'POST',
		dataType : 'json',
		global	 : false,
		data  	 : form.serialize()
	}).done(function (data) {
			
		btn.removeClass('btn-in-progress');
			
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
			
		btn.removeClass('btn-in-progress');
						
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


