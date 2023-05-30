<?php if ($this->gridAjaxRequest):?>	
	<?=$this->grid?>	
<?php else : ?>	
	<style>
		.dormitory-refund-form-area{
			max-width: 400px;
		}
		.dormitory-refund-form-area input[type=text]{
			width: 93%;
		}
		.dormitory-refund-form-area select {
			width: 100%;
			max-width: 95%;
		}
		.dormitory-refund-file-area {
			font-size: 13px;
			padding-bottom: 20px;
		}
		.disable {			
			opacity: 0.5;
			pointer-events: none;
		}
	</style>
	<div class="dormitory-refund-form-area form-area-default">
		<?=$this->form?>
	</div>
	<div class="dormitory-refund-file-area">
		<a href="/upload/files/docs/dormitory/refund/ВОЗВРАТ ДС.docx" target="_blank" class="dr-doc-return">Заявление «Возврат денежных средств»</a>
		<br />
		<a href="/upload/files/docs/dormitory/refund/Зачесть в счет оплаты.docx" target="_blank" class="dr-doc-in-payment" >Заявление «Зачесть в счет оплаты»</a>
	</div>
	<div>
		<?=$this->grid?>
	</div>
<?php endif;?>

<?php $this->inlineScript()->captureStart()?>
	
	$( document ).ready(function() {
		updateForm($('.dormitory-refund-form-area form'));
	});
	
	$('body').on('change', '.dormitory-refund-form-area form', function(event) {
		updateForm($(this));
	});


	$('body').on('submit', '.dormitory-refund-form-area form', function(event) {
		var form 	 = $(this);		
		var btn  	 = form.find('[type="submit"]');
		var url 	 = form.attr('action');
		var level    = 'success';
		var $message = jQuery('');
		
		btn.addClass('disable');
		btn.addClass('btn-in-progress');
		
		jQuery.ui.errorbox.clear($message);
		
		$.ajax(url, {
			type  	 : 'POST',
			dataType : 'json',
			global	 : false,
			data  	 : form.serialize()
		}).done(function (data) {
			
			if (typeof data.form !== "undefined"){
				$('.dormitory-refund-form-area').html(data.form);
			}
			
			btn.removeClass('disable');
			btn.removeClass('btn-in-progress');
			
			var $message = jQuery('<div class="dormitory_refund_error_box">Произошла ошибка. Попробуйте ещё раз</div>');
			
			if (typeof data.code !== "undefined" && data.code == '<?=HM_Notification_NotificationModel::TYPE_ERROR?>'){
				var $message = jQuery('<div class="dormitory_refund_error_box">'+data.error+'</div>');
				level 	 = 'error';
			} else {
				form[0].reset();
			}
			
			if (typeof data.message !== "undefined"){
				var $message = jQuery('<div class="dormitory_refund_error_box">'+data.message+'</div>');				
			}
			
			jQuery.ui.errorbox.clear($message);
			$message.errorbox({level: level});			
			var error_box = $('#error\-box');
			
			updateForm(form);
			
			
		}).fail(function () {
			
			btn.removeClass('disable');
			btn.removeClass('btn-in-progress');
						
			var $message = jQuery('<div class="dormitory_refund_error_box">Произошла ошибка. Попробуйте ещё раз</div>');
			jQuery.ui.errorbox.clear($message);
			$message.errorbox({level: 'error'});
			var error_box = $('#error\-box');
			
		}).always(function () {			
		});
		
		return false;
		
		event.preventDefault();
	});
	
	
	function updateForm(form)
	{
		var type_id = form.find('[name="type"]').val();
		if(type_id == '<?=HM_Dormitory_Refund_RefundModel::TUPE_IN_PAYMENT?>'){
			$('#fieldset-bank').addClass('hidden');
			$('.dr-doc-return').addClass('hidden');
			$('.dr-doc-in-payment').removeClass('hidden');
		} else {
			$('#fieldset-bank').removeClass('hidden');
			$('.dr-doc-return').removeClass('hidden');
			$('.dr-doc-in-payment').addClass('hidden');
		}
	}
<?php $this->inlineScript()->captureEnd()?>