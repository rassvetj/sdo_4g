<style>
.input-disabled {
	pointer-events: none;
}
.area-form {
	position: unset;
	top: auto;
    right: auto;
	max-width: 500px;
	width: 100%;
	font-size: 12px;
}

#area-description {
	margin-right: 0px;
	max-width: 500px;
	width: 100%;
	margin-bottom: 15px;
}

.error-box {
	max-width: 500px;
	width: 100%;
}

.area-form input[type="text"] {
	width: 97%;
    padding: 2px 5px;
	height: 20px;
}

.area-form textarea {
	width: 98%!important;
    padding: 2px;
	float: none!important;
    text-align: justify;
}

.area-form dd{
	margin-bottom: 12px;
}

.area-form select {
	max-width: unset;
	width: 99.5%;
	padding: 2px;
	height: 26px;
}

.area-form fieldset dl {
	margin-right: 20px;
}

.area-form input[type="text"].hasDatepicker {
	width: 90%;
}

.area-form img.ui-datepicker-trigger{
	height: 26px;
	float: left;
}
</style>
<div style="    margin-top: 16px; height: 40px; float: left; padding: 5px; display: inline-block;  margin-bottom: -16px;">
		
	<div class="_grid_gridswitcher" data-userway-font-size="11">
		<a href="<?=$this->baseUrl($this->url(array('module' => 'student-certificate', 'controller' => 'list', 'action' => 'index')));?>">
			<div style="text-decoration: underline;" onclick="$('._grid_gridswitcher div').removeClass('_u_selected');$(this).addClass('_u_selected')" class="ending" data-userway-font-size="11">
				<?=_('Мои заявки')?>
			</div>
		</a>
		
		<a href="<?=$this->baseUrl($this->url(array('module' => 'student-certificate', 'controller' => 'certificate', 'action' => 'index')));?>">
			<div style="text-decoration: underline;" onclick="$('._grid_gridswitcher div').removeClass('_u_selected');$(this).addClass('_u_selected')" class="ending" data-userway-font-size="11">
				<?=_('Заказать справку/документ')?>
			</div>
		</a>
		
		<a href="<?=$this->baseUrl($this->url(array('module' => 'student-certificate', 'controller' => 'ask-question', 'action' => 'index')));?>">
			<div style="text-decoration: underline;" onclick="$('._grid_gridswitcher div').removeClass('_u_selected');$(this).addClass('_u_selected')" class="ending" data-userway-font-size="11">
				<?=_('Задать вопрос')?>
			</div>
		</a>
		
		<div class="ending _u_selected"><?=_('Отправить документ')?></div>
		
	</div>
</div>
<div style="clear:both;" ></div>
<div class="error-box"></div>	
<div class="area-form">	
	<?=$this->form?>
</div>
<div id="area-description" class="hidden"></div>
<?php $this->inlineScript()->captureStart()?>
document.addEventListener("DOMContentLoaded", () => {
    let form = document.querySelector('.area-form form');
	if(form){
		form.addEventListener('submit', sendForm);
	}
	
	let el_types = document.querySelector('.area-form form #type');
	el_types.addEventListener('change', changeForm);

	const e = new Event("change");
	el_types.dispatchEvent(e);
	
});

let sendForm = (event) => {
	event.preventDefault();
	
	let form              = event.target;
	let url               = form.action;
	let XHR               = new XMLHttpRequest();	
	let formData          = new FormData(form);
	let btn               = form.querySelector('[type="submit"]');
	let container         = document.querySelector('.area-form');
	let container_message = document.querySelector('.error-box');
	let level             = 'error'
	
	btn.disabled                = true;
	container_message.innerHTML = '';
	
	XHR.open('POST', url, true);
	XHR.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
	
    XHR.onload = function(data) {		
		if (XHR.status == 200) {
			
			$('.area-form').html(this.responseText);
			
			btn.disabled        = false;
			
			let form = document.querySelector('.area-form form');
			if(form){
				form.addEventListener('submit', sendForm);
				
				let el_types = document.querySelector('.area-form form #type');
				el_types.addEventListener('change', changeForm);

				const e = new Event("change");
				el_types.dispatchEvent(e);
			}
			
			
		} else {
			var $message = jQuery("<div><?= _('Произошла ошибка. Попробуйте ещё раз'); ?></div>");
			jQuery.ui.errorbox.clear($message);
			$message.errorbox({level: level});
		}
		
		let message    = container.querySelector('[id^="error-message-"]');
		if(message){		
			let message_id = message.id;		
			let n          = this.responseText.search(message_id+'...errorbox...level...success...');
		
			if(n != -1){
				level = 'success';			
			}
			jQuery.ui.errorbox.clear(jQuery("#" + message_id));
			jQuery("#" + message_id).errorbox({"level":level});
		}
	};
	XHR.send(formData);
	
	
	
};

let changeForm = (event) => {
	let el_types         = event.target;
	let form_description = JSON.parse('<?=$this->description?>');
	let form_fields      = JSON.parse('<?=$this->fields?>');
	let area_btn         = el_types.closest('form').querySelector('[type="submit"]').closest('dd');
	let area_info        = document.querySelector('#area-description');
	let fields_active    = form_fields[el_types.value];
	
	
	if (typeof form_description[el_types.value] !== 'undefined') { 
		area_info.innerHTML = form_description[el_types.value];	
		area_info.classList.remove('hidden');
	} else {
		area_info.innerHTML  = '';
		area_info.classList.add('hidden');
	}
	
	//area_btn.insertBefore(area_info, area_btn.childNodes[0]);
	
	let form_items = document.querySelectorAll('.area-form form dd, .area-form form dt, .area-form form fieldset');
	form_items.forEach(function(form_item) {
		form_item.classList.add('hidden');
	});
	
	$('.area-form form #type, .area-form form #submit').removeClass('hidden');	
	$('.area-form form dt#type-label, .area-form form dt#submit-label').removeClass('hidden');
	$('.area-form form #type, .area-form form #submit').closest('dd').removeClass('hidden');
	
	
	
	for (const [key, item_class] of Object.entries(fields_active)) {
		$('.area-form form .' + item_class).removeClass('hidden');
		$('.area-form form #' + item_class).removeClass('hidden');
		$('.area-form form dt#' + item_class + '-label').removeClass('hidden');
		$('.area-form form .' + item_class).closest('dd').removeClass('hidden');
		
		$('.area-form form #' + item_class).removeClass('hidden');
		$('.area-form form #' + item_class).closest('dd').removeClass('hidden');
	}
	
	
};



<?php $this->inlineScript()->captureEnd()?>