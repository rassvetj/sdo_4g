<?php $content_area_id 	= 'content-area'; ?>
<?php $form_area_id 	= 'form-area'; ?>
<?php if ($this->gridAjaxRequest):?>
	<?=$this->grid?>	
<?php else : ?>
	<style>
		.no_students {
			color: pink;
			font-weight: bold;
		}
		.btn-rules{
			cursor: pointer;
			font-weight:bold;			
		}
		
		.content-rules{
			padding-bottom: 15px;
		}
	</style>
	<?php if(!$this->isTutor):?>
	<div style="font-size: 13px;">
		<div class="item-rules" >
			<span  class="btn-rules">Условия формирования</span>
			<ol class="hidden content-rules">
				<li>&laquo;<b>Все</b>&raquo; &mdash; без отбора по данному параметру</li>
				<li>&laquo;<b>-без кафедры-</b>&raquo; &mdash; отбор по сессиям, у которых нет кафедры. Все сессии с кафедрой будут исключены</li>			
				<li>&laquo;<b>Тип сессии: очная форма</b>&raquo; &mdash; в базовом курсе в названии есть фраза "очка"</li>
				<li>&laquo;<b>Тип сессии: практика</b>&raquo; &mdash; в базовом курсе в названии есть фраза "! Практика"</li>			
				<li>&laquo;<b>Новости (да/нет)</b>&raquo; &mdash; есть верно оформленная новость</li>
				<li>&laquo;<b>Количество новостей (число)</b>&raquo; &mdash; кол-во верно оформленных новостей</li>
				<li><span style="background-color:pink;">&nbsp;&nbsp;&nbsp;&nbsp;</span> &mdash; сессии, в которых нет назначенных студентов</li>
				<li>Сессии с учебным планом, начинающимся на &laquo;<b>ДО</b>&raquo; или &laquo;<b>АН_</b>&raquo; не учитываются</li>
				<li>Заблокированные тьюторы не учитываются</li>
			</ol>
		</div>
		<div class="item-rules" >
			<span class="btn-rules">Правила оформления новостей</span>
			<ol class="hidden content-rules">
				<li>Для сессий, в которых учебный курс НЕ содержит &laquo;<b>очка</b>&raquo; и  &laquo;<b>классическая заочка</b>&raquo;: 1 ЗЕТ = 1 любая новость. Ссылка не важна</li>			
				<li>Обязательное наличие <b>внутренней ссылки</b> на sdo.rgsu.net. Без нее вся новость игнорируется. Кроме &laquo;<b>очка</b>&raquo; и  &laquo;<b>классическая заочка</b>&raquo;</li>
				<li>Для сессий, в которых учебный курс содержит &laquo;<b>! ГИА (2018-2019) ОЧКА универс. Курс</b>&raquo; или &laquo;<b>! Практика</b>&raquo;: 1 ЗЕТ = 1 внутренняя ссылка</li>
				<li>Для сессий, в которых учебный курс содержит &laquo;<b>Классическая заочка</b>&raquo;: 1 ЗЕТ = Рубежный контроль N</li>
				<li>Для всех остальных сессий: 1 ЗЕТ = (Рубежный контроль N + Практическое задание N)</li>
				<li>, где N - это номер раздела/модуля</li>
				<li>Два одинаковых номера N считаются за 1</li>
				<li>Поиск фраз осуществляется в аннонсе и теле новости</li>
				<li>
					<b>Рубежный контроль</b> определяется по фразам:
					<ul style="list-style: none;">
						<li>Рубежный контроль N</li>
						<li>Рубежный контроль к разделу N</li>
					</ul>
				</li>
				<li>
					<b>Практическое задание</b> определяется по фразам:
					<ul style="list-style: none;">
						<li>Практическое задание N</li>
						<li>Практическое задание к разделу N</li>
						<li>Практические задания N</li>
						<li>Практические задания к разделу N</li>
						<li>Задание к разделу N</li>
						<li>Задания к разделу N</li>					
					</ul>
				</li>
			</ol>
		</div>
 
			
			<?/*
			<li><?=_('"Кол-во новостей с ссылкой" - кол-во новостей, в названии которых есть вхождение "раздел", а в тексте указаны ссылки на sdo.rgsu.net')?></li>
			<li><?=_('"Номер разделов в аннонсах" - все числа, указанные в названии новостей, которые попали под условие "Кол-во новостей с ссылкой"')?></li>
			<li><?=_('"Курс" - определяется по году, указанному в названии группы. 1 - 2016, 2 - 2015, 3 - 2014, 4 - 2013, 5 - 2012')?></li>
			<li><?=_('Если поле "Дата начала сессии" заполнено неверно или пустое, принимается дата 29.08.2016')?></li>
			<li><?=_('Только тьюторы-лекторы')?></li>
			*/?>
	</div>
	<?php endif;?>
	<br />
	<div class="default-form-area form-report-conditions"  id="<?=$form_area_id;?>">
		<?=$this->form;?>
	</div>	
	<br>
	<br>
	<div id='content-area'>		
		<?=$this->grid;?>			
	</div>
	<?php $this->inlineScript()->captureStart()?>
		
		$('.btn-rules').click(function(){			
			var content = $(this).closest('.item-rules').find('.content-rules');
			if(content.hasClass('hidden')){
				content.removeClass('hidden');
			} else {
				content.addClass('hidden');				
			}
			return false;
		});
	
		$('#tutor_id').select2();				
		$('#faculty_name').select2();				
		$('#chair_name').select2();	
	
		$(document.body).delegate('#<?=$form_area_id;?> form', 'submit', _.debounce(function (event) {
			var form_id 	= $(this).attr('id');
			var form_action = $(this).attr('action');
			var form_data 	= $(this).serialize();	
			$('#'+form_id+' #submit').prop('disabled', true);	
			
			$.ajax(form_action, {
					type: 'POST',
					global: false,
					data: form_data,				
				}).done(function (data) {		
					_.defer(function () {					
						$('#<?=$content_area_id;?>').html('');
						$('#<?=$content_area_id;?>').append(data);									
						$('#'+form_id+' #submit').prop('disabled', false);											
					});
				}).fail(function () {				
					var $message = jQuery("<div><?= _('Произошла ошибка. Попробуйте ещё раз'); ?></div>").appendTo('#' + form_id);
					jQuery.ui.errorbox.clear($message);
					$message.errorbox({level: 'error'});
					$('#'+form_id+' #submit').prop('disabled', false);
				}).always(function () {				
			});
			
		}, 50));

		$(document.body).delegate('#<?=$form_area_id;?> form', 'submit', function(event) {
			event.preventDefault();
		});	
	<?php $this->inlineScript()->captureEnd()?>
<?php endif;?>