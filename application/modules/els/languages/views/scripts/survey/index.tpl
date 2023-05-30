<?php
$selected_items_ids = $this->selected_items_ids;
?>
<?php if($this->gridAjaxRequest): ?>
<?php else: ?>
	<style>
	
	.item-new {
		border: 2px solid #0067a4!important;
	}
	
	.languages-survey-form-area {
		width: 600px;
		float: left;
		margin-right: 5px;
	}
	
	.languages-survey-form-area select {
		width: 100%;		
	}
	
	.languages-survey-items-area {
		float: left;
		padding-bottom: 20px;
	}
	
	.languages-survey-items-area-selected {		
		padding-bottom: 20px;
		clear: both;
	}
	
	.languages-survey-items-area .item, .languages-survey-items-area-selected .item{
		margin-top: 15px;
		border: 1px solid #aaa;		
		padding: 5px;
		border-radius: 5px;
	    max-width: 800px;
		font-size: 13px;
	}
	
	.languages-survey-items-area .item .main-caption, .languages-survey-items-area-selected .item .main-caption{
		font-size: 15px;
		color: #0067a4;
		font-weight: bold;
	}
	
	.languages-survey-items-area .item .text, .languages-survey-items-area-selected .item .text {
		font-weight: bold;
		padding-right: 15px;
	}
	
	.languages-survey-items-area .item .caption, .languages-survey-items-area-selected .item .caption {
		color: #aaa;		
	}
	
	.languages-survey-items-area .item.is-free, .languages-survey-items-area-selected .item.is-free {
		//border-color: #ff9800;
	}
	
	.languages-survey-form-area dd{
		//padding-bottom: 10px;
		//padding-right: 30px;		
	}
	
	.languages-survey-form-area dd, .languages-survey-form-area dt {
		//float: left;		
	}
	
	.languages-survey-form-area dt {
		//padding-top: 6px;
		padding-right: 5px;		
		font-size: 12px;
	}
	
	.languages-survey-form-area fieldset {
		border: none;
		padding: 0px;
		float: left;
		width: 50%;		
	}
	
	.languages-survey-form-area fieldset dl {
		margin:0px;		
	}
	
	
	.languages-survey-selected-message {
		font-size: 14px;
		padding-top: 10px;
	}
	
	.languages-survey-info-text-base{
		//border: 1px solid #aaa!important;
		border: 1px solid #333333;
		border-radius: 5px;
		padding: 5px;
		font-size: 12px;
		margin-bottom: 5px;
		max-height: 200px;
	}
	
	.languages-survey-info-text-free{
		//border: 1px solid #ff9800!important;
		border: 1px solid #333333;
		border-radius: 5px;
		padding: 5px;
		font-size: 12px;
		margin-bottom: 5px;
		max-height: 200px;
	}
	
	.languages-survey-form-area .is-selected{
		border-color: #0067a4;		
	}
	
	.tooltip-show {
		display: block;
		z-index: 999999999;
		position: absolute!important;
		background-color: #b9e8fd;
		border-color: #333333;
		max-width: 600px;
	}
	
	.tooltip-listener-free:before, .tooltip-listener-free:after, .tooltip-listener-base:before, .tooltip-listener-base:after {
		content: "";
		position: absolute;
		border-left: 10px solid transparent;
		border-right: 10px solid transparent;
		top: 100%;
		left: 40px;
		margin-left: -10px;
	}

	.tooltip-listener-free:before, .tooltip-listener-base:before {
		border-top: 9px solid #333333;
		margin-top: 0px;
	}

	.tooltip-listener-free:after, .tooltip-listener-base:after{
		border-top: 10px solid #b9e8fd;
		margin-top: -2px;
		z-index: 1;
	}	
	
	.btn-tooltip{
		cursor: pointer;
		
	}
	</style>	
	
	<div class="languages-survey-message-container"></div>
	
	<div class="languages-survey-form-area">
		<br />
		<br />
		<?=$this->form?>
		
		<div class="languages-survey-selected-message  <?=$selected_items_ids ? '' : 'hidden'?>"><?=_('Вы выбрали курсы')?>:</div>
		<div class="languages-survey-items-area-selected">			
			<?php foreach($this->items as $i):?>
				<?php if(!in_array($i['languages_id'], $selected_items_ids)){ continue; } ?>
				<div class="item <?=empty($i['is_free']) ? '' : 'is-free'?>" >
					<span class="main-caption"><?=$i['discipline']?></span>				
					<br />
					<span class="caption">Семестр:</span> <span class="text"><?=$i['semester']?></span>
					<span class="caption">Шифр:</span> <span class="text"><?=$i['code']?></span>
					<span class="caption">Курс:</span> <span class="text"><?=$i['course']?></span>
					<span class="caption">Форма обучения:</span> <span class="text"><?=$i['study_form']?></span>
					<span class="caption">Рубежный контроль:</span> <span class="text"><?=$i['control']?></span>
					<br />
					<span class="caption">Направление подготовки / Специальность:</span> <span class="text"><?=$i['specialty']?></span>
					<br />
					<span class="caption">Направленность / Специализация:</span> <span class="text"><?=$i['specialization']?></span>
					<br />
					<span class="caption">ФИО ответственного преподавателя, ученая степень, должность:</span> <span class="text"><?=$i['teacher']?></span>
					<br />
					<?php if(empty($i['is_free'])):?>
						<span class="caption btn-tooltip" data-tooltip="listener-base" style="color:red;"><?=_('Дисциплина обязательна для изучения на иностранном зыке')?></span>
					<?php else: ?>
						<span class="caption btn-tooltip" data-tooltip="listener-free" style="color:red;"><?=_('Вольный слушатель')?></span>
					<?php endif;?>
					<br />
					<?php if( $this->selected_items[$i['languages_id']]['date_created_timestamp'] >= $this->timestamp_limit ):?>						
						<a class="btn-languages-delete" href="<?=$this->baseUrl($this->url(array('module' => 'languages', 'controller' => 'survey', 'action' => 'delete', 'item_id' => $i['languages_id'])));?>">Отменить</a>
					<?php endif; ?>
					
				</div>
			<?php endforeach;?>		
		</div>
		
	</div>	
	<div class="languages-survey-items-area hidden ">
		<div class="languages-survey-selected-message" ><?=_('Выберите курс')?>:</div>
		<?php foreach($this->items as $i):?>
			<div class="item <?=empty($i['is_free']) ? '' : 'is-free'?>" id="item_<?=$i['languages_id']?>" >				
				<span class="main-caption"><?=$i['discipline']?></span>				
				<br />
				<span class="caption">Семестр:</span> <span class="text"><?=$i['semester']?></span>
				<span class="caption">Шифр:</span> <span class="text"><?=$i['code']?></span>
				<span class="caption">Курс:</span> <span class="text"><?=$i['course']?></span>
				<span class="caption">Форма обучения:</span> <span class="text"><?=$i['study_form']?></span>
				<span class="caption">Рубежный контроль:</span> <span class="text"><?=$i['control']?></span>
				<br />
				<span class="caption">Направление подготовки / Специальность:</span> <span class="text"><?=$i['specialty']?></span>
				<br />
				<span class="caption">Направленность / Специализация:</span> <span class="text"><?=$i['specialization']?></span>
				<br />
				<span class="caption">ФИО ответственного преподавателя, ученая степень, должность:</span> <span class="text"><?=$i['teacher']?></span>
				<br />
				<?php if(!empty($selected_items_ids) && in_array($i['languages_id'], $selected_items_ids)):?>
					<span style="color:red"><?=_('Вы уже отправили заявку на этот курс')?></span>
				<?php else: ?>
					<a class="btn-languages-save" href="<?=$this->baseUrl($this->url(array('module' => 'languages', 'controller' => 'survey', 'action' => 'save', 'item_id' => $i['languages_id'])));?>">Выбрать <?=empty($i['is_free']) ? '' : 'как вольный слушатель'?></a>
				<?php endif; ?>
				
			</div>
		<?php endforeach;?>
	</div>
	<br />
	<br />
	<br />
	
	<div>
		<div class="languages-survey-info-text-base  tooltip-listener-base hidden " >
			Вы выбрали дисциплину на иностранном языке, данный предмет становится обязательным для изучения по программе предусмотренной данным курсом, задолженность по нему является академической задолженностью. 
			<br />
			Сдача рубежного контроля проходит на иностранном языке с регистрацией результата в электронной зачетной книжке (далее в документе об образовании). 
			<br />
			Время и место проведения занятий будут указаны в расписании учебных занятий (с пометкой «английский язык») после формирования учебных групп. 
			<br />
			Посещение курса на русском языке возможно только в статусе слушателя <b>без сдачи</b> рубежного контроля и <b>без регистрации</b> результата в электронной зачетной книжке (далее в документе об образовании).
		</div>
		<div class="languages-survey-info-text-free  tooltip-listener-free hidden">
			Вы выбрали дисциплину на иностранном языке, не предусмотренную учебным планом, для изучения как <b>слушатель</b> - посещение только аудиторных занятий, без сдачи рубежного контроля и <b>без регистрации</b> результата в электронной зачетной книжке (далее в документе об образовании). 
			<br />
			Время и место проведения занятий будут указаны в расписании учебных занятий (с пометкой «английский язык») после формирования учебных групп.
		</div>
	</div>
	
	
	
	<?php $this->inlineScript()->captureStart()?>
		
		$( document ).ready(function() {
			
			$('.languages-survey-form-area select').select2();
			
			
			$('body').on('mouseover', '.btn-tooltip', function() {
				var btn 	= $(this);				
				var tooltip = $(	'.tooltip-' + $(this).data('tooltip')	 );
				var left 	= btn.position().left;
				var top  	= btn.position().top - tooltip.height() - 45;
				
				if(tooltip.height() > 55){ top = top - 43; }
				
				tooltip.css({ top: top+'px', left: left+'px'  });
				tooltip.addClass('tooltip-show').removeClass('hidden');				
			});
			
			$('body').on('mouseleave', '.btn-tooltip', function() {
				var tooltip       = $(	'.tooltip-' + $(this).data('tooltip')	 );
				tooltip.removeClass('tooltip-show').addClass('hidden');
				tooltip.css({top: '0px', left: '0px'});
			});
			
			
			
			
			
			$('.languages-survey-form-area').on('change', 'select', function() {
				// берем все текущие значения и по ним находим данные. Потом эти данные расписхиваем по селектам
				var filtered_data 	= getFilteredData();
				filtered_data 		= groupByName(filtered_data);
					
				$.each( filtered_data, function( el_name, items) {				
					updateItems(el_name, items);					
				});
				
				filteredHtmlItems();
				updateSelectStyle();
			});
			
			$('.languages-survey-form-area select:first').trigger("change");
			
			
			$('body').on('click', '.btn-languages-delete', function(event) {				
				event.preventDefault();
				
				var message_container 	= $('.languages-survey-message-container');
				var btn 				= $(this);
				var url 				= btn.attr('href');
				var item_id				= btn.closest('.item').attr('id');
				
				message_container.html('');
				
				
				$.ajax(url, {
					type: 'POST',
						global: false,
						dataType:'json'
						
					}).done(function (res) {
						var $message = jQuery(res.data).appendTo(message_container);
						jQuery.ui.errorbox.clear($message);
						
						if(res.error == 1){
							$message.errorbox({level: 'error'});
						} else {
							$message.errorbox({level: 'success'});							
							btn.closest('.item').remove();
						}
						$("html, body").animate({ scrollTop: 0 }, "slow");
					}).fail(function () {				
						var $message = jQuery("<div><?= _('Произошла ошибка. Попробуйте ещё раз'); ?></div>").appendTo(message_container);
						jQuery.ui.errorbox.clear($message);
						$message.errorbox({level: 'error'});
						$("html, body").animate({ scrollTop: 0 }, "slow");
						
					}).always(function () {				
				});
				
				
				
				
				
			});
			
			
			
			$('.languages-survey-items-area').on('click', '.btn-languages-save', function(event) {
				event.preventDefault();
				
				
				
				
				var message_container 	= $('.languages-survey-message-container');
				var btn 				= $(this);
				var url 				= btn.attr('href');
				var item_id				= btn.closest('.item').attr('id');
				
				
				message_container.html('');
				
				$.ajax(url, {
					type: 'POST',
						global: false,
						dataType:'json'
						
					}).done(function (res) {
						//message_container.html(res);
						var $message = jQuery(res.data).appendTo(message_container);
						jQuery.ui.errorbox.clear($message);
						
						if(res.error == 1){
							$message.errorbox({level: 'error'});
						} else {
							$message.errorbox({level: 'success'});
							
							$('.languages-survey-form-area form').remove();
							$('.languages-survey-items-area .item:not([id='+item_id+'])').remove();
							
							var selected_el = $('.languages-survey-items-area .item');
							selected_el.addClass('item-new');
							$('.languages-survey-items-area-selected').prepend(selected_el);
							$('.languages-survey-items-area').remove();
							//$('.languages-survey-items-area').addClass('languages-survey-items-area-selected').removeClass('languages-survey-items-area'); 
							btn.remove();
							$('.languages-survey-selected-message').removeClass('hidden');
							
							
						}
						$("html, body").animate({ scrollTop: 0 }, "slow");
						
					}).fail(function () {				
						var $message = jQuery("<div><?= _('Произошла ошибка. Попробуйте ещё раз'); ?></div>").appendTo(message_container);
						jQuery.ui.errorbox.clear($message);
						$message.errorbox({level: 'error'});
						$("html, body").animate({ scrollTop: 0 }, "slow");
						
					}).always(function () {				
				});
		
			});
			
			
			
			
			
		});
		
		
		// фильтруем данные
		function getFilteredData()
		{
			var items 			= jQuery.parseJSON($('#linked').val());
			
			
			var faculty 		= $('#faculty').val();
			var chair 			= $('#chair').val();
			var semester 		= $('#semester').val();
			var discipline 		= $('#discipline').val();
			var code			= $('#code').val();
			var specialty 		= $('#specialty').val();
			var specialization 	= $('#specialization').val();
			var course 			= $('#course').val();
			var study_form		= $('#study_form').val();
			var control			= $('#control').val();
			var teacher			= $('#teacher').val();
			
			
			var items_filtered	= {};		
			$.each( items, function( key, el ) {
				if(typeof faculty 			!== "undefined" && faculty!=''			&& faculty 			!= el['faculty']		){ return true; }
				if(typeof chair 			!== "undefined" && chair!='' 			&& chair 			!= el['chair']			){ return true; }
				if(typeof semester 			!== "undefined" && semester!='' 		&& semester 		!= el['semester']		){ return true; }
				if(typeof discipline 		!== "undefined" && discipline!='' 		&& discipline 		!= el['discipline']		){ return true; }
				if(typeof code 				!== "undefined" && code!='' 			&& code 			!= el['code']			){ return true; }
				if(typeof specialty 		!== "undefined" && specialty!='' 		&& specialty 	  	!= el['specialty']		){ return true; }
				if(typeof specialization 	!== "undefined" && specialization!='' 	&& specialization 	!= el['specialization']	){ return true; }
				if(typeof course 			!== "undefined" && course!='' 			&& course 			!= el['course']			){ return true; }
				if(typeof study_form		!== "undefined" && study_form!='' 		&& study_form 		!= el['study_form']		){ return true; }
				if(typeof control 			!== "undefined" && control!='' 			&& control 			!= el['control']		){ return true; }
				if(typeof teacher 			!== "undefined" && teacher!='' 			&& teacher 			!= el['teacher']		){ return true; }				
				
				items_filtered[key] = el;				
			});		
			
			
			return items_filtered;
		}
		
		
		function groupByName(data)
		{
			var new_data = {};
			$.each( data, function( key, el ) {				
				$.each( el, function( field_name, val ) {
					if (typeof new_data[field_name] === "undefined") {
						new_data[field_name] = {};
					}					
					var obj = new_data[field_name];
					obj[val] = val;					
				});
			});
			return new_data;
		}
		
		
		function updateItems(el_name, items)
		{
			if($('#'+el_name).length < 1){ return false; }
			var item_selected = $('#'+el_name).val();
			
			$('#'+el_name+' option').remove();			
			var html_options = '<option value="" title="-- выберите --" label="-- выберите --">-- выберите --</option>';			
			$.each(items, function( el ){
				if(item_selected == el){
					html_options += '<option value="'+el+'" title="'+el+'" label="'+el+'" selected >'+el+'</option>';	
				} else {
					html_options += '<option value="'+el+'" title="'+el+'" label="'+el+'">'+el+'</option>';	
				}
			});
			$('#'+el_name).html(html_options);
			$('#'+el_name).select2();			
		}
		
		// показываем блоки доступных курсов
		function filteredHtmlItems()
		{
			var count_selected = 0;
			$('.languages-survey-form-area select').each(function() {
				if($(this).val() == ''){ return true; }
				count_selected++;
			});
			
			if(count_selected < 1)	{ $('.languages-survey-items-area').addClass('hidden'); 	} 
			else 					{ $('.languages-survey-items-area').removeClass('hidden'); 	}
			
			var filtered_data	= getFilteredData();
				filtered_data 	= groupByName(filtered_data);
				
			
				
				ids 			= filtered_data['languages_id'];
			if (typeof ids === "undefined") { 
				$('.languages-survey-items-area').addClass('hidden');
				return false; 
			}
			
			$('.languages-survey-items-area .item').addClass('hidden'); 
			$.each( ids, function( id ) {					
				$('#item_'+id).removeClass('hidden');
			});
		}
		
		//стилизуем выбранные списки
		function updateSelectStyle()
		{
			$('.languages-survey-form-area select').each(function( index ) {
				var el = $(this)
				if(el.val() != ''){
					el.closest('.element').find('.select2-selection').addClass('is-selected');
				} else {
					el.closest('.element').find('.select2-selection').removeClass('is-selected');
				}				
			});
		}
		
	<?php $this->inlineScript()->captureEnd()?>	
<?php endif; ?>

		
		