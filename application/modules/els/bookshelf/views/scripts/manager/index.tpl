<?php if($this->gridAjaxRequest):?>
	<?=$this->grid?>
<?php else: ?>
	<style>
.accordion-data ol{
    list-style: none;
	margin-left: 20px;
}
.accordion-data ol > li{
    padding-left: 20px;
    position: relative;
    margin: 10px;
}
.accordion-data ol > li:before{
    content: '✔';
    position: absolute; top: 0; left: 0;    
}

.accordion-container{
	font-size:17px;
	text-align: justify;
	border: 1px solid #fdfdfd;
    margin-bottom: 10px;	
	padding: 0px;
    padding-top: 10px;
}

.accordion-header a{
	margin: 0 !important;
    padding: 10px;
    font-size: 18px;
    background: #f9f9f9;
    display: block;
    padding-right: 30px;
    position: relative;	
	border-bottom: none;
    color: #3d3d3d;
	text-decoration: none;
}

.accordion-container.open .accordion-header a{
	background: #effaff;
	
}

.accordion-header a::after{
	content: '';
    position: absolute;
    right: 20px;
    top: 20px;
    border: 5px solid transparent;
    border-top: 5px solid #ccc;
}

.accordion-container.open .accordion-header a::after{
	content: '';
    position: absolute;
    right: 20px;
    top: 15px;
    border: 5px solid transparent;
    border-bottom: 5px solid #3467A0;
}

.accordion-container.open .accordion-data{
	display:block;
}

.accordion-container .accordion-data {
	display:none;
	font-size: 15px;
	padding: 10px;
}
</style>

	<div class="error-box"></div>

	<div style="padding-bottom: 10px; font-size: 15px;">
		<a href="\upload\files\manuals\bookshelf\Инструкция по работе в Виртуальной книжной полке для преподавателя.docx" target="_blank" >
			<?=_('Инструкция')?>
		</a>
	</div>	
	<div class="area-form">
		<?=$this->form?>
	</div>
	<div>
		<?=$this->grid?>
	</div>
	<div>
		<div style="float: left; padding-right: 10px; padding-top: 10px;">
			<a href="<?=$this->url(array('module' => 'library', 'controller' => 'biblioclub', 'action' => 'create-auth-link'), 'default', true);?>" target="_blank" >
				<img src="\images\logo\biblioklub.ru_220x215.png" alt="biblioklub.ru logo" height="80" >
			</a>
		</div>
		<div style="float: left; padding-right: 10px; padding-top: 10px;">
			<a href="<?=$this->url(array('module' => 'library', 'controller' => 'urait', 'action' => 'create-auth-link'), 'default', true);?>" target="_blank" >
				<img src="\images\logo\urait.ru_logo_206x56.svg" alt="urait.ru logo" height="80" >
			</a>
		</div>
	</div>
	<div style="clear:both;"></div>

	<?=$this->render('manager/partials/_description.tpl');?>
	<?=$this->render('manager/partials/_learning_subject.tpl');?>
	<?=$this->render('manager/partials/_published_to_subjects.tpl');?>

	<?php $this->inlineScript()->captureStart()?>
	document.addEventListener("DOMContentLoaded", () => {
		let form = document.querySelector('.area-form form');
		form.addEventListener('submit', sendForm);
	});

	let sendForm = (event) => {
		event.preventDefault();
		
		let form              = event.target;
		let url               = form.action;
		let XHR               = new XMLHttpRequest();	
		let formData          = new FormData(form);
		let btn               = form.querySelector('[type="submit"]');
		var container         = document.querySelector('.area-form');
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
					
				}
				
				
			} else {
				var $message = jQuery("<div><?= _('Произошла ошибка. Попробуйте ещё раз'); ?></div>");
				jQuery.ui.errorbox.clear($message);
				$message.errorbox({level: level});
			}
			
			var message    = document.querySelector('[id^="error-message-"]');
			
			
			if(message){		
				var message_id = message.id;		
				var n          = this.responseText.search(message_id+'...errorbox...level...success...');
			
				if(n != -1){
					level = 'success';
					
					setTimeout(function(){
					  location.reload();
					}, 1500);

					
				}
				jQuery.ui.errorbox.clear(jQuery("#" + message_id));
				jQuery("#" + message_id).errorbox({"level":level});
			}
		};
		XHR.send(formData);
		
		
		
	};
	<?php $this->inlineScript()->captureEnd()?>

	<script>
	$( document ).ready(function() {
		$('body').on('click', '.btn-accordion', function(event) {
			event.preventDefault();
			var container = $(this).closest('.accordion-container');
			if ( container.hasClass('open')){
				container.removeClass('open');
			} else {
				container.addClass('open');
			}
		});
	});
	</script>

<?php endif;?>