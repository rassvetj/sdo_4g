$(function(){
	$.fn.dfilter = function (options) {
		var dfilter = this,
			idCounter= 0,
			$calendar,
			onSelectId=0,
			defaults = { 
				clear: null,
				cdateTo: 'cdateTo',
				cdateFrom: 'cdateFrom',
				fillFrom: null,
				fillTo: null,
				from: $(dfilter[0]),
				to: $(dfilter[1]),
				handler: ".field-filters-dbl",
				optionsDatepicker: {
					inline:           true,
					changeMonth:      true,
					changeYear:       true,
					showOtherMonths:  true,
					hideIfNoPrevNext: true,
					onSelect: function (dt, inst) {
						if(onSelectId==0){
							onSelectId=1;
							if(inst.input.is(".cdateTo")){
								var typeDate	= 'maxDate',
									cDate		= inst.input.datepicker( "getDate" ),
									dTarget		= $calendar.from;
							}else if(inst.input.is(".cdateFrom")){
								var typeDate	= 'minDate',
									cDate		= inst.input.datepicker( "getDate" ),
									dTarget		= $calendar.to;
							}
							$(dTarget).datepicker('option', typeDate, cDate);
							onSelectId=0;
						}
						var formatDate;
						if(options.fillFrom!=null&&typeof $calendar.from!='undefined'){
							formatDate = $.datepicker.formatDate('dd.mm.yy', $calendar.from.datepicker( "getDate" ));
							options.from.val(formatDate);
							$(options.fillFrom).text(formatDate);
						}
						if(options.fillTo!=null&&typeof $calendar.to!='undefined'){
							formatDate = $.datepicker.formatDate('dd.mm.yy', $calendar.to.datepicker( "getDate" ));
							options.to.val(formatDate);
							$(options.fillTo).text($.datepicker.formatDate('dd.mm.yy', $calendar.to.datepicker( "getDate" )));
						}
					}
				}
			},
		calendar = null,
		options = $.extend(defaults, options),
		optionsDatepicker = $.extend(defaults.optionsDatepicker, options.optionsDatepicker, { dateFormat: 'dd.mm.yy' }),
		_toggleCalendar = function(){
			if($calendar.is(":visible")){
				
			}else{
				if($(options.handler).is(".disabled-field-filters")){
					_toggleFormatDate();
				}
			}
			$calendar.toggle();	
			if ($calendar.is(':visible')) {
				$calendar.position({
					my: 'left top',
					at: 'left bottom',
					of: options.handler,
					offset: '0 2px'
				});
			}
		},
		_uniqueId = function(prefix) {
			var id = idCounter++;
			return prefix ? prefix + id : id;
		},
		id = _uniqueId('calendar_'),
		_create =  function(){
			$calendar = $('<div class="calendar"><div class="cdateFrom"></div><div class="cdateTo"></div></div>');
			$('body')
				.append($calendar.data("id",id));
			var $pos = $(options.handler).offset();
			/*$calendar.css({
				top: $pos.top+30,
				left: $pos.left
			})*/
			var from = options.from.val();
			var to = options.to.val();
			$calendar.from= $( ".cdateFrom" ).datepicker(optionsDatepicker);
			$calendar.to = $( ".cdateTo" ).datepicker(optionsDatepicker);			
			if(options.startDate){
				$calendar.from.datepicker('option','minDate',options.startDate);
			}
			if(options.endDate){
				$calendar.to.datepicker('option','maxDate',options.endDate);
			}
			// если задана дата начала, устанавливаем её 
			if (from) { 
				$calendar.from.datepicker('setDate', from); 
			}
			// задана дата конца, устанавливаем
			if (to) { 
				$calendar.to.datepicker('setDate', to); 
			}
			
			$calendar.hide(); 
			_toggleCalendar();
		},
		_isCreated = function(){
			var arCalendar = $(".calendar");
			for(i=0;i<arCalendar.length;i++){
				if($(arCalendar[i]).data("id")==id) {
					return true
				}
			}		
		},
		_getCalendars = function () {
			return $('#date-'+ this.tplData.rowId + '-' + this.tplData.valueId);
		},
		_toggleFormatDate = function(){
			$("#from,#to").attr("disabled",$("#from").attr('disabled') == 'disabled' ? false : true);
			$(options.handler).toggleClass("disabled-field-filters");
			$(options.handler).find(".field-filters-value-date").toggle();
		};
		// создаём шаблон контрола
		fcontrol = ['<div class="report-constructor" id="report-app">',
				'<span class="field-cell field-filters '+options.handler.substr(1)+' disabled-field-filters">',
					'<span class="field-icon"></span>',
					'<span class="field-filters-value">',
						'<div class="field-filters-value-date field-filters-value-container" style="display: none;">',
							'<div>',
								'<strong>'+options.descFrom+'</strong> ',
								'<em class="date-from">-&#8734;</em>',
							'</div>',
							'<div>',
								'<strong>'+options.descTo+'</strong> ',
								'<em class="date-to">+&#8734;</em>',
							'</div>',
						'</div>',
					'</span>',
				'</span>',
				'</div>'].join("");
		// вставляем его в DOM
		$(fcontrol).insertAfter($(dfilter[1]));
		
		// устанавливаем текущее значение фильтра "от", если оно установлено
		if (options.to.val()) {
			$(options.fillTo).text(options.to.val());
		}
		// устанавливаем текущее значение фильтра "до", если оно установлено
		if (options.from.val()) {
			$(options.fillFrom).text(options.from.val());
		}
		
		$(options.handler).bind("click",function(e){
			if(!_isCreated()){
				_create();
			}else{
				_toggleCalendar();
			}
			e.stopPropagation();
		});
		
		$(document).delegate("*","click",function(e){
			var $target = $(e.currentTarget);
			if(
				$target.closest("table").is(".ui-datepicker-calendar")||
				$target.closest(".calendar").length||
				$target.closest(".ui-datepicker-header").length||
				$target.closest(options.handler).length
			){
				e.stopPropagation();
				return false;
			}
			if(typeof $calendar != 'undefined'){
				if($calendar.is(":visible")&&typeof $calendar!='undefined'){
					$calendar.toggle();
				}
			}
		});
		$($(options.handler).find(".field-icon")).bind("click",function(e){
			if($calendar.is(":visible")&&typeof $calendar!='undefined'){
				$calendar.toggle();
			}
			_toggleFormatDate();
			e.preventDefault();
			e.stopPropagation();
		})
		if (options.from.val() || options.to.val()) {
			setTimeout(function () {
				$(options.handler).trigger('click');
				_toggleCalendar();
			}, 0)
		}
	}
})

$(function(){
	var $wrap = $('.field-filters-group'),
		$icon = $wrap.find(".field-icon"),
		$group = $wrap.find("select");
	
	var val = $group.val();
	if (val == 0) {
		$group.attr("disabled",true).hide();
	} else {
		$wrap.toggleClass("disabled-field-filters");
	}
	$wrap.bind("click",function(e){
		if(e.target===$group[0]||$(e.target).closest("select")[0]===$group[0]){
			e.stopPropagation();
			return false;
		}
		$wrap.toggleClass("disabled-field-filters")
		$group.attr("disabled",$group.attr('disabled') == 'disabled' ? false : true).toggle();
		//$group.find("option").eq(0).attr("selected",true);
	})
})