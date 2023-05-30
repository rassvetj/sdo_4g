$( document ).ready(function() {
    
	// уведомление студенту о ВКР
	/*
	$.ajax('/qualification-work/index/render-notification/', {
		type  : 'POST',
		global: false,
		data  : ''
	
	}).done(function (data) {		
		
		$('#qw_area').remove();
		$('body').after(data);
		
		$('#qw_popup').dialog({
			resizable: false,
			autoOpen : false,
			height   : 400,
			width    : 600,
			modal    : true,
			buttons  :
			{
				'Закрыть': function() {
					$( this ).dialog( "close" );
				}
			}
		});
		$('#qw_popup').dialog( "open" );		
	}).fail(function () {		
	}).always(function () {		
	});
	*/
	
	
	// Скрипты для уведомление студенту о ВКР
	$('body').on('click', '.qw-btn-show-form', function() {
		var area_form = $('.qw-form-area');
		var area_text = $('.qw-text-area');
			
		if(area_form.is(':visible')) { 
			area_form.hide(); 
			area_text.show(); 
		} else {
			area_form.show();
			area_text.hide(); 
		}	
	});

	
	$('body').on('submit', '#qualification_work_agreement, #qualification_work_agreement_confirm', function() {
		var form 	= $(this);
		var popup 	= $('#qw_popup');
		var btn 	= form.find('#submit');
		
		btn.prop('disabled', true);
		
		var level    = 'success';
		var $message = jQuery('');
		jQuery.ui.errorbox.clear($message);
		
		$.ajax(form.attr('action'), {
			type  	 : 'POST',
			dataType : 'json',
			global	 : false,
			data  	 : form.serialize()
		}).done(function (data) {
			
			var $message = jQuery('<div class="qw_error_box">Произошла ошибка. Попробуйте ещё раз</div>');
			
			if (typeof data.error !== "undefined"){
				var $message = jQuery('<div class="qw_error_box">'+data.error+'</div>');
				level 	 = 'error';
			}
			
			if (typeof data.message !== "undefined"){
				var $message = jQuery('<div class="qw_error_box">'+data.message+'</div>');
				setTimeout(function(){ $('#qw_popup').dialog('close');	}, 2000);
			}
			
			jQuery.ui.errorbox.clear($message);
			$message.errorbox({level: level});			
			var error_box = $('#error\-box');
			popup.before(error_box);
			
			btn.prop('disabled', false);
			
		}).fail(function () {
			
			var $message = jQuery('<div class="qw_error_box">Произошла ошибка. Попробуйте ещё раз</div>');
			jQuery.ui.errorbox.clear($message);
			$message.errorbox({level: 'error'});
			var error_box = $('#error\-box');
			popup.before(error_box);
			btn.prop('disabled', false);
			
		}).always(function () {			
		});
		return false;		
	});
	
	
	// общие уведомления	
	$.ajax('/student-notification-agreement/ajax/render/', {
		type  : 'POST',
		global: false,
		data  : ''	
	}).done(function (data) {		
		
		$('#sna_area').remove();
		$('body').after(data);
		
		$('#sna_popup').dialog({
			closeOnEscape: false,
			resizable: false,
			autoOpen : false,
			//height   : 400,
			width    : 700,
			modal    : true,
			open: function(event, ui) {
				$(".ui-dialog-titlebar-close").hide();				
			},
			buttons  :
			{
				//'Закрыть': function() {
					//$( this ).dialog( "close" );
				//}
			}
		});
		$('#sna_popup').dialog( "open" );		
	}).fail(function () {		
	}).always(function () {		
	});	
	
	$('body').on('click', '.sna-btn-confirm', function() {
		
		var btn = $(this);
		btn.addClass('disable');
		
		
		var notification_type = $(this).data('type');
		var url = '/student-notification-agreement/ajax/save-agreement/';
		
		var level    = 'success';
		var $message = jQuery('');
		jQuery.ui.errorbox.clear($message);
		
		$.ajax(url, {
			type  	 : 'POST',
			dataType : 'json',
			global	 : false,
			data  	 : {type: notification_type}
		}).done(function (data) {
			
			btn.removeClass('disable');
			
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
			
			btn.removeClass('disable');
						
			var $message = jQuery('<div class="sna_error_box">Произошла ошибка. Попробуйте ещё раз</div>');
			jQuery.ui.errorbox.clear($message);
			$message.errorbox({level: 'error'});
			var error_box = $('#error\-box');
			popup.before(error_box);
			
			
		}).always(function () {			
		});
		
		return false;
		
	});
	
	
	// подтверждение личной информации
	$('body').on('submit', '.form-confirm-user-info', function(evant) {
		var form = $(this);
		
		var btn = form.find('[type="submit"]');
		btn.addClass('disable');
		btn.addClass('btn-in-progress');
		
		var row = form.closest('.item-confirmed');
		
		var popup 	= $('#sna_popup');
		var url = form.attr('action');
		
		var level    = 'success';
		var $message = jQuery('');
		jQuery.ui.errorbox.clear($message);
		
		$.ajax(url, {
			type  	 : 'POST',
			dataType : 'json',
			global	 : false,
			data  	 : form.serialize()
		}).done(function (data) {
			
			btn.removeClass('disable');
			btn.removeClass('btn-in-progress');
			
			var $message = jQuery('<div class="sna_error_box">Произошла ошибка. Попробуйте ещё раз</div>');
			
			if (typeof data.error !== "undefined"){
				var $message = jQuery('<div class="sna_error_box">'+data.error+'</div>');
				level 	 = 'error';
			} else {
				row.remove();
			}
			
			if (typeof data.message !== "undefined"){
				var $message = jQuery('<div class="sna_error_box">'+data.message+'</div>');				
			}
			
			jQuery.ui.errorbox.clear($message);
			$message.errorbox({level: level});			
			var error_box = $('#error\-box');
			popup.before(error_box);
			
			
			
			if (typeof data.hide_popup !== "undefined"){
				if(data.hide_popup == 1){
					setTimeout(function(){ $('#sna_popup').dialog('close');	}, 2000);
				}
			}
			
		}).fail(function () {
			
			btn.removeClass('disable');
			btn.removeClass('btn-in-progress');
						
			var $message = jQuery('<div class="sna_error_box">Произошла ошибка. Попробуйте ещё раз</div>');
			jQuery.ui.errorbox.clear($message);
			$message.errorbox({level: 'error'});
			var error_box = $('#error\-box');
			popup.before(error_box);
			
			
		}).always(function () {			
		});
		
		return false;
		
		
		evant.preventDefault();
	});
	
	
	// подтверждение личной информации
	$('body').on('submit', '.form-snils-inn', function(evant) {
		var form = $(this);
		
		var btn = form.find('[type="submit"]');
		btn.addClass('disable');
		btn.addClass('btn-in-progress');
		
		var row      = form.closest('.item-confirmed');
		
		var popup    = $('#sna_popup');
		var url      = form.attr('action');
		
		var level    = 'success';
		var $message = jQuery('');
		jQuery.ui.errorbox.clear($message);
		
		$.ajax(url, {
			type  	    : 'POST',
			dataType    : 'json',
			global	    : false,
			data        : new FormData(this),
			processData : false,
			contentType : false,
		}).done(function (data) {
			
			btn.removeClass('disable');
			btn.removeClass('btn-in-progress');
			
			var $message = jQuery('<div class="sna_error_box">Произошла ошибка. Попробуйте ещё раз</div>');
			
			if (typeof data.error !== "undefined"){
				var $message = jQuery('<div class="sna_error_box">'+data.error+'</div>');
				level 	 = 'error';
			} else {
				row.remove();
			}
			
			if (typeof data.message !== "undefined"){
				var $message = jQuery('<div class="sna_error_box">'+data.message+'</div>');				
			}
			
			jQuery.ui.errorbox.clear($message);
			$message.errorbox({level: level});			
			var error_box = $('#error\-box');
			popup.before(error_box);
			
			
			
			if (typeof data.hide_popup !== "undefined"){
				if(data.hide_popup == 1){
					setTimeout(function(){ $('#sna_popup').dialog('close');	}, 2000);
				}
			}
			
		}).fail(function () {
			
			btn.removeClass('disable');
			btn.removeClass('btn-in-progress');
						
			var $message = jQuery('<div class="sna_error_box">Произошла ошибка. Попробуйте ещё раз</div>');
			jQuery.ui.errorbox.clear($message);
			$message.errorbox({level: 'error'});
			var error_box = $('#error\-box');
			popup.before(error_box);
			
			
		}).always(function () {			
		});
		
		return false;
		
		
		evant.preventDefault();
	});
	
});


function updateVaccinationForm(el)
{	
	var id = el.val();
	$('.vacc-item-1, .vacc-item-2, .vacc-item-3, .vacc-item-4').addClass('hidden');
	
	$('.vacc-item-' + id).removeClass('hidden');
}

function sendVaccinationForm(form)
{
	//var file_data = form.find('[type="file"]').prop('files')[0];
    var form_data = new FormData(form[0]);
	var level     = 'success';
	var $message  = jQuery('');
	var btn 	  = form.find('[type="submit"]');
	var popup 	  = $('#sna_popup');
	
	jQuery.ui.errorbox.clear($message);
	btn.addClass('disable');
	
	$.ajax({
        url		    : form.attr('action'),
        dataType    : 'json',
        cache       : false,
        contentType : false,
        processData : false,
        data        : form_data,                         
        type        : 'POST',
        success: function(data){
			
			btn.removeClass('disable');
            
			$message = jQuery('<div class="sna_error_box">Произошла ошибка. Попробуйте ещё раз</div>');
			
			if (typeof data.error !== "undefined"){
				var $message = jQuery('<div class="sna_error_box">'+data.error+'</div>');
				level 	 = 'error';
			} else {	
				setTimeout(function(){ popup.dialog('close');	}, 2000);			
			}
			
			if (typeof data.message !== "undefined"){
				var $message = jQuery('<div class="sna_error_box">'+data.message+'</div>');				
			}
			
			jQuery.ui.errorbox.clear($message);
			$message.errorbox({level: level});			
			var error_box = $('#error\-box');
			form.before(error_box);			
        },
		error: function(){
						
			$message = jQuery('<div class="sna_error_box">Произошла ошибка. Попробуйте ещё раз</div>');
			jQuery.ui.errorbox.clear($message);
			$message.errorbox({level: 'error'});
			var error_box = $('#error\-box');
			form.before(error_box);
			btn.removeClass('disable');
		}
     });
	
	
}
