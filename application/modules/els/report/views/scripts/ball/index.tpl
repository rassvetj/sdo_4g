<div class="report">
	<p>Доп. условия формирования отчета</p>
	<ol>
		<li>Только сессии (без уч. курсов)</li>				
		<li>Только занятия, в названии которых есть: "Академическая активность", "Задание к разделу", "Рубежный контроль", "Итоговый контроль" или тип "журнал"</li>
		<li>Сессии без продления (первого и второго)</li>
		<li>Если ячейка %% пуста, значит нет занятия этого типа, недоступно тьютору или в сессии не заданы часы</li>
		<li>Если у тьютора не задана роль в сессии, % выполнения будет расчитываться по всем типам занятий соотв. колонки</li>
		<li>Если поле "Дата начала сессии больше или равна, чем" не заполнено, то испльзуется дата 01.09.2016</li>
		<li>Данные студентов, прошедших обучение - не учитываются</li>
	</ol>
	<dt>
		2 - <div class="tooltip-description" style="display: none;">			
			&lt;b&gt;л&lt;/b&gt; &ndash; лектор
			&lt;br&gt;
			&lt;b&gt;лаб&lt;/b&gt; &ndash; лаборант
			&lt;br&gt;
			&lt;b&gt;пр&lt;/b&gt; &ndash; семинарист
			&lt;br&gt;
			&lt;b&gt;отсутствие метки&lt;/b&gt; &ndash; доступны все занятия (роли нет)
			&lt;br&gt;
			Данные появляются при импорте тьюторов из csv файла (роль назначения на сессию)
		</div>
		<span class="tooltip"></span>		
	</dt>
	
	<dt>
		2.2 - <div class="tooltip-description" style="display: none;">			
				Данные появляются при назначении тьютора на занятие (роль назначения на занятие), ручное или автоматическое.
				&lt;br&gt;
				Может отличатся от п.2, т.к. роль определяется по принадлежности занятия.
		</div>
		<span class="tooltip"></span>		
	</dt>
	
	<dt>
		12, 13, 14, 15, 17 - <div class="tooltip-description" style="display: none;">			
			Общее количество необходимых колонок берется из сессии из соотв. поля. Если там указано кол-во менее 1, тогда данные берутся из занятия. Значение в занятии настраивается тьютором в любое время.
			&lt;br&gt;			
			Если в сессии несколько журналов одного типа или ИПЗ, тогда % выводится через разделитель /
		</div>
		<span class="tooltip"></span>		
	</dt>
	
	<dt>
		18 - <div class="tooltip-description" style="display: none;">			
			Если в 12, 13, 14, 15 или 17 более одного значения, то вычисляется средее в колонке. Ex: в 12 значение 100% / 50%. В 18 будет использовано значение (100 * 50) / 2 = 75%
			&lt;br&gt;
			Формула расчета = (12 + 13 + 14 + 15 + 17)/5
			&lt;br&gt;
			Колонки без значений не учитываются в формуле.
		</div>
		<span class="tooltip"></span>		
	</dt>
	
	
	
	
	<br>	
	<?=$this->form;?>
	<div class="report-content-area">
	</div>
</div>

<?php $this->inlineScript()->captureStart()?>
	$('.report select').select2();
	
	$(document.body).delegate('.report form', 'submit', function(event) {
		event.preventDefault();
	});	
	
	var selector_form     = '.report form';
	var selector_content  = '.report-content-area';
	
	$(document.body).delegate(selector_form, 'submit', _.debounce(function (event) {
		var form_id 	= $(this).attr('id');
		var form_action = $(this).attr('action');
		var form_data 	= $(this).serialize();	
		$(selector_form +' :submit').prop('disabled', true);			
		$(selector_content).addClass('ajax-spinner-local spinner-area-tutor');
		
		$.ajax(form_action, {
				type: 	'POST',
				global: false,
				data: 	form_data,				
			}).done(function (data) {						
				_.defer(function () {


				
					$(selector_content).removeClass('ajax-spinner-local');
					$(selector_content).removeClass('spinner-area-tutor');				
					
					$(selector_content).html('');
					$(selector_content).append(data);									
					$(selector_form +' :submit').prop('disabled', false);					
				});
			}).fail(function () {				
				var $message = jQuery("<div><?= _('Произошла ошибка. Попробуйте ещё раз'); ?></div>").appendTo('.report form');
				jQuery.ui.errorbox.clear($message);
				$message.errorbox({level: 'error'});
				$('#'+form_id+' #submit').prop('disabled', false);
			}).always(function () {				
		});
	}, 50));
<?php $this->inlineScript()->captureEnd()?>