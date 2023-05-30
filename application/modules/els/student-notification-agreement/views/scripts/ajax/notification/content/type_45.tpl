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
			width: 106px;
			
			background-image: none;
			background-color: white;
			text-align: center;
	}
		
	#sna_popup .sna-btn-default:hover{
		background-color: #3192be;
		color:white;
	}
	
	#period-label{
		float: left;
		padding: 7px 10px 0px 5px;
	}
	
	#sna_popup .element{
		padding: 5px;
	}
</style>
<div class="fin-notice" >
	<p>
		Уважаемый студент!
	</p>
	<p>
		Вы обучаетесь за счет средств образовательного кредитования с государственной поддержкой.
	</p>
	<p>
		Для исключения фактов взимания комиссии за перечисление средств за обучение, 
		просим сообщить о взимании банком комиссии в момент перечисления средств за обучение.
	</p>
	<p>
		<form method="POST" action="<?=$this->baseUrl($this->url(array('module' => 'student-notification-agreement', 'controller' => 'ajax', 'action' => 'save-agreement')));?>" 
			  onSubmit="sendFormBaseSNA($(this)); return false;" 
			  onChange="changeFormBaseSNA($(this));"
		>
			<input type="hidden" name="type" value="<?=$this->notification_type?>">
			<dd class="element">
				<label for="levy-1">
					<input type="radio" name="levy" id="levy-1" value="1"  >
					<?=_('Взимают')?>
				</label>
				<label for="levy-2">
					<input type="radio" name="levy" id="levy-2" value="0" checked >
					<?=_('Не взимают')?>
				</label>
			</dd>
			<div class="area-element-period hidden">
				<dt id="period-label">
					<label for="period" class="optional">
						<?=_('Период')?>
					</label>
				</dt>
				
				<dd class="element">
					<input type="text" name="period" id="period" value="" >
				</dd>
			</div>			
			<dd>
				<input type="submit" value="<?=_('Сохранить')?>" class="sna-btn-default" style="float: right;">
			</dd>			
		</form>
	</p>	
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

function changeFormBaseSNA(form)
{
	var levy_value          = form.find('[name="levy"]:checked').val();
	var area_element_period = form.find('.area-element-period');
	if(levy_value == '1'){
		area_element_period.removeClass('hidden');
	} else {
		area_element_period.addClass('hidden');
	}
}
</script>






