$( document ).ready(function() {
	let div = document.createElement('div');
	div.className = 'myAgreement';
	
	let shadow = document.createElement('div');
	shadow.id = 'myShadow';
	div.append(shadow);
	
	let agreement = document.createElement('div');
	agreement.id = 'Agreement';
	let mytext = document.createElement('p');
	mytext.id = 'myText';
	//mytext.innerText = 'В соответствии с п. 36 «Инструкции об организации работы по обеспечению функционирования системы воинского учета», утвержденной приказом Министра обороны Российской Федерации от 22 ноября 2021 г. № 700, в РГСУ проводится сверка сведений воинского учета. На основании распоряжения первого проректора РГСУ от 29 декабря 2022 г. № 90-р (https://rgsu.net/for-students/navigator/voenno-uchetnyy-stol/) всем студентам надлежит прибыть во Второй отдел РГСУ и сверить свои данные воинского учета, при этом обращаем внимание на перечень документов необходимый для сверки (указан в п. 6). По итогам проведения сверки на военнообязанного гражданина оформляется карточка ф.10 с проставлением его личной подписи, подтверждающей сведения, указанные в ней. В дальнейшем указанные сведения будут сверены с учетными данными военных комиссариатов. Указанное мероприятие также направлено на оказание обучающимся практической помощи в вопросах приведения их документов воинского учета в соответствие с действующим законодательством Российской Федерации, а также реализации их права на получение отсрочки от призыва на военную службу. Лица, которые по объективным причинам не имеют возможности прибыть на сверку, направляют письменное уведомление с указанием причины их не явки с приложением подтверждающих документов на адрес электронной почты: DupelevAA@rgsu.net. Контактный телефон: Начальник Второго отдела РГСУ – Дупелев Андрей Анатольевич, 8 (495) 255-67-67, доб. 3051.';
	mytext.innerText = 'В соответствии с п. 36 «Инструкции об организации работы по обеспечению функционирования системы воинского учета», утвержденной приказом Министра обороны Российской Федерации от 22 ноября 2021 г. № 700, в РГСУ проводится сверка сведений воинского учета. На основании распоряжения первого проректора РГСУ от 29 декабря 2022 г. № 90-р (https://rgsu.net/for-students/navigator/voenno-uchetnyy-stol/) всем студентам надлежит прибыть во Второй отдел РГСУ и сверить свои данные воинского учета, при этом обращаем внимание на перечень документов необходимый для сверки (указан в п. 6).';
	agreement.append(mytext);
	
	let mytext2 = document.createElement('p');
	mytext2.id = 'myText';
	mytext2.innerText = 'По итогам проведения сверки на военнообязанного гражданина оформляется карточка ф.10 с проставлением его личной подписи, подтверждающей сведения, указанные в ней.';
	agreement.append(mytext2);
	
	let mytext3 = document.createElement('p');
	mytext3.id = 'myText';
	mytext3.innerText = 'В дальнейшем указанные сведения будут сверены с учетными данными военных комиссариатов. Указанное мероприятие также направлено на оказание обучающимся практической помощи в вопросах приведения их документов воинского учета в соответствие с действующим законодательством Российской Федерации, а также реализации их права на получение отсрочки от призыва на военную службу.';
	agreement.append(mytext3);
	
	let mytext4 = document.createElement('p');
	mytext4.id = 'myText';
	mytext4.innerText = 'Лица, которые по объективным причинам не имеют возможности прибыть на сверку, направляют письменное уведомление с указанием причины их не явки с приложением подтверждающих документов на адрес электронной почты: DupelevAA@rgsu.net.';
	agreement.append(mytext4);
	
	let mytext5 = document.createElement('p');
	mytext5.id = 'myText';
	mytext5.innerText = 'Контактный телефон: Начальник Второго отдела РГСУ – Дупелев Андрей Анатольевич, 8 (495) 255-67-67, доб. 3051.';
	agreement.append(mytext5);
	
	let btn = document.createElement('button');
	btn.id = 'myBtn';
	btn.innerText = 'Ознакомлен';
	
	agreement.append(btn);
	
	div.append(agreement);
	
	$('body').prepend(div);
	
	$('#myBtn').click(function(){
		$('.myAgreement').css('display', 'none');
	});
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
