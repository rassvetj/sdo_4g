$( document ).ready(function() {
	
	// аккордеон
	$('body').on('click', '.btn-accordion', function(event) {
		event.preventDefault();
		var container = $(this).closest('.accordion-container');
		if ( container.hasClass('open')){
			container.removeClass('open');
		} else {
			container.addClass('open');
		}
	});
	
	
	$('body').on('submit', '.form-area-default form', function(e) {
		e.preventDefault();
		
		var form = $(this);
		if(form.attr('id') == 'confirmingstudent'){
			sendConfirmingStudentQuestion(form);
		}
	});
	

	$('body').on('click', '.btn-ask-question', function(event) {
		event.preventDefault();
		var el = $(this);
		var destination_id	= el.data('destination_id');
		var popup_id		= 'popup-'+destination_id;
		var form 			= $('#'+destination_id);
		
		var contract_number = el.data('contract_number');
		var contract_date 	= el.data('contract_date');
		var total_debt 		= el.data('total_debt');
		var update_date 	= el.data('update_date');
		
		form.find('#contract_number').val(contract_number);
		form.find('#contract_date').val(contract_date);
		form.find('#total_debt').val(total_debt);
		form.find('#update_date').val(update_date);
		
		openPopup(popup_id);
	});
	
	function openPopup(popup_id){
		$('#'+popup_id).dialog( "open" );		
	}
	
	
	$('body').on('submit', '.form-area-mypaymentsquestion form', function(event) {
		event.preventDefault();
		var form 			= $(this);
		var message 		= '';
		var message_type 	= 'success';
		var popup			= $('#popup-mypaymentsquestion');
		var btns			= popup.closest('.ui-dialog').find('.ui-button');
		var files_items		= $('.file-upload-list li');
		
		
		
		jQuery.ui.errorbox.clear('');
		//console.log(btns);
		btns.prop('disabled', true);
		
		
		$.ajax({
			type	: 'POST',
			url		: form.attr('action'),
			data	: form.serialize(),
			dataType: 'json',
			success	: function(data){
				
				if (typeof data === "undefined" || jQuery.type( data ) === "null") {
					message 		= 'Произошла ошибка. Попробуйте ещё раз';
					message_type	= 'error';
					var $message 	= jQuery('<div>'+message+'</div>');
					jQuery.ui.errorbox.clear($message);
					$message.errorbox({level: message_type});
					$message.closest('.error-box').prependTo('#mypaymentsquestion');
					btns.prop('disabled', false);
					return false;
				}
				
				btns.prop('disabled', false);
				
				if (typeof data.message !== "undefined") {		
					message 		= data.message;
				} else {
					message 		= 'Произошла ошибка. Попробуйте ещё раз';
					message_type	= 'error';
				}
               
				if (typeof data.error !== "undefined") {
					message_type = 'error';				   
				} 
				var $message = jQuery('<div>'+message+'</div>');
				jQuery.ui.errorbox.clear($message);
				$message.errorbox({level: message_type});
				
				if(message_type == 'success'){					
					form.find('.cancel-upload a').click();
					files_items.addClass('hidden');
					
					form.find('#contract_number, #contract_date, #total_debt, #update_date').val('');
					form.find('textarea').val('');
					form.trigger('reset');					
					popup.dialog('close');					
					$message.closest('.error-box').prependTo('.my-payments-area');
					
				} else {					
					$message.closest('.error-box').prependTo('#mypaymentsquestion');
				}
			   
			},
			error: function (xhr, ajaxOptions, thrownError) {
				btns.prop('disabled', false);
				message 		= 'Произошла ошибка. Попробуйте ещё раз';
				message_type	= 'error';
				var $message = jQuery('<div>'+message+'</div>');
				jQuery.ui.errorbox.clear($message);
				$message.errorbox({level: message_type});
				$message.closest('.error-box').prependTo('#mypaymentsquestion');
			}
		});
		
	});
	
	
});

// обработка формы отправки ошибки в деканат по "Справка, подтверждающая статус студента"
function sendConfirmingStudentQuestion(form)
{
	var url 	 	= form.attr('action');
	var text 		= form.find('textarea').val();
	var text_limit 	= 3;
	
	var btn = form.find('input[type="submit"]');
	
	if(text.length < text_limit){
		var $message = jQuery('<div>Введите текст длиннее '+text_limit+' символов</div>');
		jQuery.ui.errorbox.clear($message);
		$message.errorbox({level: 'error'});
		return false;
	}
	
	text = $.trim(text);	
	if(text.length < text_limit){
		var $message = jQuery('<div>Введите текст длиннее '+text_limit+' символов</div>');
		jQuery.ui.errorbox.clear($message);
		$message.errorbox({level: 'error'});
		return false;
	}
	
	btn.prop('disabled', true);
	
	var request = $.ajax({		
		url  	 : form.attr('action'),
		type 	 : form.attr('method'),
		data 	 : form.serialize(),       
		dataType : 'json',
	});	  
	
	request.done(function (data) {			
		btn.prop('disabled', false);
		var message 	    = '';
		var message_type = 'success';
		
		if (typeof data.message !== "undefined") {		
			message 		= data.message;
		} else {
			message 		= 'Произошла ошибка. Попробуйте ещё раз';
			message_type = 'error';
		}
		
		if (typeof data.error !== "undefined") {
			message_type = 'error';
		} 
		
		if(message_type != 'error'){ form.trigger('reset'); }
		
		var $message = jQuery('<div>'+message+'</div>');
		jQuery.ui.errorbox.clear($message);
		$message.errorbox({level: message_type});

		$([document.documentElement, document.body]).animate({ scrollTop: $(".breadcrumbs").offset().top }, 1000);
		
		
	});
	
	request.fail(function (jqXHR, textStatus) {
		btn.prop('disabled', false);
		var $message = jQuery('<div>Произошла ошибка. Попробуйте ещё раз</div>');
		jQuery.ui.errorbox.clear($message);
		$message.errorbox({level: 'error'});
		
		$([document.documentElement, document.body]).animate({ scrollTop: $(".breadcrumbs").offset().top }, 1000);
	});
	
}



// обработка формы отправки ошибки в деканат по "Справка, подтверждающая статус студента"
function checkNews(item)
{
	var url       = item.data('action');
	var container = $(item.data('container'));
	item.prop('disabled', true);
	container.html('Загрузка...');
	
	var request = $.ajax({		
		url  	 : url,
		type 	 : 'POST',		
		dataType : 'html',
	});	  
	
	request.done(function (data) {			
		item.prop('disabled', false);
		container.html(data);
	});
	
	request.fail(function (jqXHR, textStatus) {
		btn.prop('disabled', false);
		container.html('<div>Произошла ошибка. Попробуйте ещё раз</div>');		
	});
	
}