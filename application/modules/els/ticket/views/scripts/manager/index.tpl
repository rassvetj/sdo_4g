<style>
	.tree-button-area.pending {
		opacity: 0.4;
		pointer-events: none;    
		user-select: none;
		-moz-user-select: none;
		-khtml-user-select: none;
	}
	
	.tree-button-area a{
		font-size: 14px;
		display: inline-block;
		padding-right: 20px;		
	}
	
	.tree-button-area .btn-reset{
		font-size: 12px;
	}
</style>
<div class="tree-button-area">	
	<a class="btn-default" href="<?=$this->baseUrl($this->url(array('module' => 'ticket', 'controller' => 'manager', 'action' => 'get-tree')));?>">Вывести список оплат</a>
	
	<a class="btn-reset" href="<?=$this->baseUrl($this->url(array('module' => 'ticket', 'controller' => 'manager', 'action' => 'refresh-tree')));?>">Обновить список оплат</a>
</div>
<div class="tree-content">
</div>
<div>
	<br />
	<hr />
	<p>В этом разделе выводятся все оплаты студентов, которые они прикрепили.</p>
	<p><b>Вывести список оплат</b>&nbsp;&nbsp;&nbsp;- формирует список и кэширует его. Повторное нажатие выведет ранее сформированный список.</p>
	<p><b>Обновить список оплат</b>&nbsp;- формирует список заново.</p>
	<br />
</div>


<?php $this->inlineScript()->captureStart()?>
	$('.tree-button-area').on('click', '.tree-button-area a', function() {
		
		$('.tree-button-area').addClass('pending');
		
		var url = $(this).attr('href');
		
		$.ajax(url, {
			type: 'POST',
		
		}).done(function (data) {						
			$('.tree-content').html(data);
			$('.tree-button-area').removeClass('pending');
		
		}).fail(function () {
			var $message = jQuery("<div><?= _('Произошла ошибка. Попробуйте ещё раз'); ?></div>").appendTo('.tree-content');
			jQuery.ui.errorbox.clear($message);
			$message.errorbox({level: 'error'});		
			
			$('.tree-button-area').removeClass('pending');
		
		}).always(function () {
			$('.tree-button-area').removeClass('pending');
		});	
		return false;		
	});
	
	
	$('.tree-content').on('click', '.tree-ticket-item', function() {
			var el = $(this).closest('li').find('.tree-ticket-sub-items').first();
			if(el.hasClass('hidden')){ 
				el.removeClass('hidden'); 
				$(this).addClass('opened');
			} else {
				el.addClass('hidden'); 
				$(this).removeClass('opened');
			}
		
	});
	
	
<?php $this->inlineScript()->captureEnd()?>