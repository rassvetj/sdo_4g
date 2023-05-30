<style>
.input-disabled {
	pointer-events: none;
}

.area-form {
	font-size: 12px;
}

.error-box {
	max-width: 500px;
	width: 100%;
}

.area-form {
	position: unset;
	top: auto;
    right: auto;
	max-width: 500px;
	width: 100%;	
}

.area-form input[type="text"] {
	width: 98%;
    padding: 2px;
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

.area-form fieldset dl {
	margin-right: 20px;
}
</style>
<div style="margin-top: 16px; height: 40px; float: left; padding: 5px; display: inline-block;  margin-bottom: -16px;">
		
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
		
		<div class="ending _u_selected"><?=_('Задать вопрос')?></div>
		
		<a href="<?=$this->baseUrl($this->url(array('module' => 'student-certificate', 'controller' => 'send-document', 'action' => 'index')));?>">
			<div style="text-decoration: underline;" onclick="$('._grid_gridswitcher div').removeClass('_u_selected');$(this).addClass('_u_selected')" class="ending" data-userway-font-size="11">
				<?=_('Отправить документ')?>
			</div>
		</a>
		
	</div>
</div>
<div style="clear:both;" ></div>
<div class="error-box"></div>	
<div class="area-form">	
	<?=$this->form?>
</div>
<?php $this->inlineScript()->captureStart()?>
document.addEventListener("DOMContentLoaded", () => {
    let form = document.querySelector('.area-form form');
	form.addEventListener('submit', sendForm);
});

let sendForm = (event) => {
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
			
			container.innerHTML = this.responseText;
			btn.disabled        = false;
			
			let form = document.querySelector('.area-form form');
			form.addEventListener('submit', sendForm);
			
		} else {
			var $message = jQuery("<div><?= _('Произошла ошибка. Попробуйте ещё раз'); ?></div>");
			jQuery.ui.errorbox.clear($message);
			$message.errorbox({level: level});
		}
		
		let message_id = container.querySelector('[id^="error-message-"]').id;		
		let n          = this.responseText.search(message_id+'...errorbox...level...success...');
		
		if(n != -1){
			level = 'success';			
		}
		jQuery.ui.errorbox.clear(jQuery("#" + message_id));
		jQuery("#" + message_id).errorbox({"level":level});
	};
	XHR.send(formData);
	
	console.log(event.target);
	event.preventDefault();
};
<?php $this->inlineScript()->captureEnd()?>