"use strict";
// ЮТФ-8
(function () {
// Генерация уникального GUID 
function S4 () {
	return (((1+Math.random())*0x10000)|0).toString(16).substring(1);
}
window.guid = function () {
	return (S4()+S4()+"-"+S4()+"-"+S4()+"-"+S4()+"-"+S4()+S4()+S4());
}
})();

// Модель столбца отчётной таблицы
// т.к. строки должны буть упорядочены
// у них есть атрибуты isFirst, isLast и order
var ReportField = Backbone.Model.extend({
	// значения полей модели по умолчнию
	defaults: {
		isFirst:     false, // Является ли столбец первым в коллекции
		isLast:      false, // Является ли столбец крайним в коллекции
		isHidden:    false, // Является ли столбец скрытым
		isInput:     false, // Просить ли на первом шаге генерации отчёта предварительного ввода значаения этого поля
		value:       '',    // идентификатор поля из списка доступных полей, который будет показываться в данном столбце отчёта
		displayName: ''     // название столбца, которое будет показываться в шапке таблицы
	},
	initialize: function () {
		// При создании модели инициализируем коллекцию моделей значения
		// здесь будут хранится модели, описывающие атрибуты конкретного поля
		this.values = new ReportFieldValues;
		// при смене поля проверяем есть-ли в коллекции модель для этого поля
		this.bind('change:value', _.bind(function () {
			var value  = this.get('value');
			if (value && !this.values.get(value)) {
				this.values.add({ id: value });
			}
		}, this));
		// Модель сама умеет добавляться в коллекцию в указанном месте
		this._appendToCollection();
		// Присваиваем модели уникальный идентификатор
		this.set({ id: window.guid() });
	},
	// Получаем индекс (порядок) модели в коллекции
	// для получения индекса нельзя просто прочитать поле order, т.к.
	// набор значений order не непрерывен!!!
	index: function () {
		if (!this.collection) return -1;
		return this.collection.indexOf(this);
	},
	// Находим соседнюю подель в коллекции справа
	next: function () {
		if (!this.collection) return null;
		return this.collection.at(this.index() + 1);
	},
	// Находим соседнюю подель в коллекции слева
	prev: function () {
		if (!this.collection) return null;
		return this.collection.at(this.index() - 1);
	},
	// Внутренний метод вставки модели field в коллекцию
	// в заданное положение до или после this
	// Также, если field - это обыкновенный Object,
	// то создаёт на его основе инстанс модели ReportField
	_insert: function (field, position) {
		if (!this.collection)                 return false;
		if (_.isEqual(this, field))           return;
		if ( !field || !(field instanceof Backbone.Model) )
			field = new this.collection.model(field || {});
		if ( !(field instanceof ReportField)) return false;
		
		var fieldOrder = this.get('order') + {before: 0, after: 1}[position]
		  , order = fieldOrder;

		// update order of fields
		this.collection.each(function (f, idx, all) {
			var attrs = {};
			(f.get('order') == order) && !_.isEqual(field, f)
				&& (attrs.order = ++order);
			f.set(attrs, { silent: true });
		}, this);
		field.set({ order: fieldOrder }, { silent: true });
		
		if (!this.collection.getByCid(field)) {
			// remove from previous collection
			field.remove();
			this.collection.add(field);
		}
		
		// После обновления атрибутов order пересортируем коллекцию
		this.collection.sort({ silent: true });
		
		this.collection.each(function (f, idx, all) {
			f.set({
				isFirst: idx == 0,
				isLast:  idx == (all.length - 1)
			}, { silent: true });
		});
		this.collection.each(function (f) { f.changedAttributes() && f.change(); });
		this.collection.trigger('reorder');
	},
	_appendToCollection: function () {
		if (!this.collection) return;
		if (this.collection.length) {
			this.collection.last().after(this);
		} else {
			this.set({ order: 1, isLast: true, isFirst: true });
		}
	},
	// Вставка элемента после текущего
	after:  function (field) {
		return this._insert(field, 'after');
	},
	// Вставка элемента до текущего
	before: function (field) {
		return this._insert(field, 'before');
	},
	// Является ли текущая модель последней в коллекции
	isLast: function () {
		return !this.collection || _.isEqual(this, this.collection.last());
	},
	// Является ли текущая модель первой в коллекции
	isFirst: function () {
		return !this.collection || _.isEqual(this, this.collection.first());
	},
	// Удаляем модель из коллекции
	remove: function () {
		var collection = this.collection;
		if (!collection) return false;
		if (collection)
			collection.remove(this);
		if (collection.length == 1) {
			collection.first().set({ isFirst: true, isLast: true });
		} else if (collection.length) {
			collection.first().set({ isFirst: true });
			collection.last().set({ isLast: true });
		}
		collection.trigger('reorder');
	},
	// Валидация модели
	validate: function (attrs) {
		if (attrs.value && _.isUndefined(this.possibleValues[attrs.value])) {
			return 'invalid field value';
		}
		var hasInvalidBoolean = _.any(attrs, function (value, key) {
			return /^is[A-Z]/.test(key) && !_.isBoolean(value);
		});
		if (hasInvalidBoolean)
			return 'invalid field boolean attribute';
	},
	// Сопоставляет id поля (value) его человеческому написанию
	humanValue: function () {
		return this.get('value')
			? this.possibleValues[this.get('value')]
			: '';
	}
});
// Коллекция моделей ReportField
var ReportFields = Backbone.Collection.extend({
	model: ReportField,
	// Список моделей у которых задан value
	completed: function () {
		return this.filter(function (field) { return field.get('value'); });
	},
	// Все-ли элементы коллекции имеют value
	isComplete: function () {
		return this.completed().length == this.length;
	},
	// Сериализация модели для отправки данных на сервер
	serialize: function () {
		return _(this.completed()).map(function (item) {
			var options = {}
			  , valueModel = item.values.get(item.get('value'))
			  , filter
			  , pad
			  , date;
			pad = function (n, h) {
				h || (h = 2);
				return (n / Math.pow(10, h)).toFixed(h).substr(2);
			};
			date = function (phpTs) {
				var dt = new Date(phpTs * 1000);
				return [ pad(dt.getDate()), pad(dt.getMonth()+1), pad(dt.getFullYear(), 4) ].join('.');
			}
			if (valueModel) {
				_.extend(options, {
					'hiden': Number(!!item.get('isHidden')),
					'input': Number(!!item.get('isInput'))
				});
				if ( valueModel.get('isFilterEnabled') ) {
					filter = valueModel.get('filter');
					// change format of date filter
					if (filter && (!_.isUndefined(filter.from) || !_.isUndefined(filter.to))) {
						filter = _.extend({}, filter);
						if (filter.from != null) {
							filter.from = date(filter.from);
						}
						if (filter.to != null) {
							filter.to = date(filter.to);
						}
					}
					_.extend(options, { 'filter': filter });
				}
				if ( valueModel.get('isAggregatorEnabled') )
					_.extend(options, { 'aggregation': valueModel.get('aggregator') });
				if ( valueModel.get('isFunctionEnabled') )
					_.extend(options, { 'function': valueModel.get('function') });
			}
            _.extend(options, {'title' : item.get('displayName')});
			return {
				field: item.get('value'),
				title: item.get('displayName'),
				options: options
			}
		});
	},
	// Функция которая используется при сортировке для получения значения
	// по которому производится сортировка
	comparator: function (field) {
		return field.get('order');
	}
});
// Модель, описывающая операции, фильтры и функции которые будут применяться
// к полю (столбцу)
var ReportFieldValue = Backbone.Model.extend({
	defaults: {
		'function':            window.undefined, // Функция преобразования поля
		'aggregator':          window.undefined, // Аггрегирующая функция поля
		'isAggregatorEnabled': false,
		'isFunctionEnabled':   false,
		'type':                window.undefined, // Тип данных поля (string, date, datetime, double, integer...)
		'isFilterEnabled':     false
	},
	// Валидация данных
	validate: function (attrs) {
		if (attrs['function'] && !this.get('functions')) {
			return "Can't change function value while functions list is not set";
		}
		if (attrs.aggregator && !this.get('aggregators')) {
			return "Can't change aggregator value while aggregators list is not set";
		}
		if (attrs['function'] && !_.any(this.get('functions'), function (i) { return attrs['function'] == i.name })) {
			return "No such function '"+ attrs['function'] +"'";
		}
		if (attrs.aggregator && !_.any(this.get('aggregators'), function (i) { return attrs.aggregator == i.name })) {
			return "No such aggregator '"+ attrs.aggregator +"'";
		}
		// Валидация значений фильтров
		if (!_.isUndefined(attrs.filter) && !_.isUndefined(this.get('type'))) {
			switch (this.get('type')) {
			case 'date':
            case 'datetime':
			case 'datetimestamp':
				// TODO делать проверку введённых дат
				break;
			case 'string':
				if (!_.isString(attrs.filter))
					return "Invalid filter format for string";
				break;
			case 'integer':
			case 'double':
				if (!_.isNumber(attrs.filter) || _.isNaN(attrs.filter))
					return "Invalid filter format for number";
				break;
			}
		}
	}
});
// Коллекция моделей ReportFieldValue
var ReportFieldValues = Backbone.Collection.extend({
	model: ReportFieldValue
});
// TODO disable unset of fields
// Модель отчёта. Содержит название отчёта
// и после первого сохранения отчёта хранит
// серверный идентификатор отчёта
var Report = Backbone.Model.extend({
	defaults: {
		name: ''
	},
	initialize: function () {
		var reportFields = new ReportFields;
		this.set({
			fields: reportFields,
			id: window.guid()
		});
	}
});
// View модели ReportFieldValue
// Отображает выпадающие списки c функциями преобразования данных
// и аггрегирующими функциями
var ReportFieldValueView = Backbone.View.extend({
	tagName: "span",
	events: {
		"change select.functions":   "updateOnFunctionChange",
		"change select.aggregators": "updateOnAggregatorChange"
	},
	initialize: function () {
		_.bindAll(this, "render", "renderOnError");
		this.template = _.template($('#field-row-functions-template').html());
		this.model.bind('change', this.render);
		this.model.bind('error', this.renderOnError);
		this.model.view = this;
	},
	remove: function () {
		Backbone.View.prototype.remove.apply(this, arguments);
	},
	_setSelectValue: function ($select, value) {
		if ($select.is(':ui-selectmenu')) {
			$select.selectmenu('value', value);
		} else {
			$select.val(value);
		}
	},
	renderOnError: function (model, error) {
		this._setSelectValue(this.$('select.aggregators'), model.get('aggregator'));
		this._setSelectValue(this.$('select.functions'), model.get('function'));
	},
	render: function () {
	   var $el = $(this.el)
		  , changedAttributes
		  , me = this;
		if (this.model.get('aggregators')) {
			if ($el.is(':empty')) {
				$el.html( this.template( this.model.toJSON() ) )
					.find('select').hide();
				_.defer(function () {
					$el.find('select').each(function () {
						var width = $(this).outerWidth()
						  , diff = parseInt($(this).css('margin-left'), 10);
						$(this).selectmenu({
							width: width,
							menuWidth: width - 7,
							positionOptions: { offset: '0 1px' }
						});
					});
					me.updateOnFunctionChange();
					me.updateOnAggregatorChange();
				});
			}
			if (this.model.hasChanged('aggregator')) {
				this._setSelectValue(this.$('select.aggregators'), this.model.get('aggregator'));
			} else if (this.model.hasChanged('function')) {
				this._setSelectValue(this.$('select.functions'), this.model.get('function'));
			}
		}
		return this;
	},
	updateOnFunctionChange: function () {
		this.model.set({
			'function': this.$('select.functions').val()
		});
	},
	updateOnAggregatorChange: function () {
		this.model.set({
			'aggregator': this.$('select.aggregators').val()
		});
	}
});
// View для показа контролов фильтров.
// т.к. в зависимости от типа данных поля контролы могут быть разными
// их отображение вынесено в отдельный view
var ReportFieldValueFilterView = Backbone.View.extend({
	tagName: "span",
	className: "field-filters-value",
	events: {
		"click":                                         "toggleCalendars",
		"change .field-filters-value-container > input": "setInputValue"
	},
	initialize: function () {
		_.bindAll(this, "render", "renderOnError", "_format_date_and_update");
		this.model.bind('change', this.render);
		this.model.bind('error', this.renderOnError);
		this.model.filterView = this;
		
		this.filterTemplates = {
			'date':          _.template($('#filter-type-date').html()    || ''),
			'datetime':      _.template($('#filter-type-date').html()    || ''),
            'datetimestamp': _.template($('#filter-type-date').html()    || ''),
			'string':        _.template($('#filter-type-string').html()  || ''),
			'integer':       _.template($('#filter-type-integer').html() || ''),
			'double':        _.template($('#filter-type-double').html()  || ''),
			'default':       _.template($('#filter-type-default').html() || '')
		};

		$(window).bind('datepicker-set-defaults', this._format_date_and_update);

		this.tplData = {
			rowId:   this.options.rowId,
			valueId: this.options.valueId.replace(/\./g, '-')
		};
	},
	_format_date_and_update: function () {
		var datepickers = {
			from: this._getCalendars().children('div.calendar-from'),
			to:   this._getCalendars().children('div.calendar-to')
		};
		if (datepickers.from.length) {
			this.$('em.date-from').html(this._formate_date(this.model.get('filter') || {}, 'from', datepickers));
			this.$('em.date-to').html(this._formate_date(this.model.get('filter') || {}, 'to', datepickers));
		}
	},
	remove: function () {
		this._getCalendars().remove();
		$(window).unbind('datepicker-set-defaults', this._format_date_and_update);
		Backbone.View.prototype.remove.apply(this, arguments);
	},
	_getCalendars: function () {
		return $('#date-'+ this.tplData.rowId + '-' + this.tplData.valueId);
	},
	toggleCalendars: function () {
		var $calendars = this._getCalendars();
		$calendars.toggle();
		if ($calendars.is(':visible')) {
			$calendars.position({
				my: 'center top',
				at: 'center bottom',
				of: $(this.el).closest('.field-filters')
			}).find('> div').datepicker('refresh');
		}
	},
	_setDateFilterToModel: function (filter) {
		if (!_.isEqual(this.model.get('filter'), filter)) {
			this.model.set({ filter: filter });
		}
	},
	_create_datepickers: function (datepickers) {
		var options
		  , model = this.model
		  , filter = _({}).extend(model.get('filter') || {});
		options = {
			inline:          true,
			changeMonth:     true,
			changeYear:      true,
			showOtherMonths: true,
			onSelect: function (dt, inst) {
				var $this = $(this)
				  , filter = _({}).extend(model.get('filter') || {})
				  , cur = filter[$this.data('rangepos')]
				  , sel = $this.datepicker('getDate');

				if (!_.isNumber(cur) || _.isNaN(cur) || cur != parseInt(sel.getTime() / 1000)) {
					filter[$this.data('rangepos')] = parseInt(sel.getTime() / 1000);
				} else {
					filter[$this.data('rangepos')] = null;
				}
				model.set({ filter: filter });
			}
		};
		
		datepickers.from.datepicker( _.extend(options, { defaultDate: filter.from ? new Date(filter.from * 1000) : null }) );
		datepickers.to.datepicker  ( _.extend(options, { defaultDate: filter.to   ? new Date(filter.to   * 1000) : null }) );
		filter.from && (filter.from = parseInt(datepickers.from.datepicker('getDate').getTime() / 1000));
		filter.to   && (filter.to   = parseInt(datepickers.to.datepicker('getDate').getTime()   / 1000));
		
		model.set({ filter: filter }, { silent: true });
	},
	_update_datepickers: function (datepickers) {
		var filter = this.model.get('filter') || {}
		  , upd = [];
		filter.to   || upd.push(datepickers.to.get(0));
		filter.from || upd.push(datepickers.from.get(0));
		$(upd)
			.find('td.ui-datepicker-current-day')
			.removeClass('ui-datepicker-days-cell-over ui-datepicker-current-day')
			.find('a')
			.removeClass('ui-state-active');
	},
	_formate_date: function (filter, key, datepickers) {
		var app = {from: '-&#8734', to: '+&#8734'}[key || 'from']
		  , inst = datepickers[key];
		if (!_.isNumber(filter[key]) || _.isNaN(filter[key])) { return app; }
		return $.datepicker.formatDate(
			  inst.datepicker('option', 'dateFormat') || $.datepicker._defaults.dateFormat
			, new Date(filter[key] * 1000)
		);
	},
	_render_date: function (abinitio) {
		var filter
		  , datepickers;
		if (abinitio) {
			this.$('.calendars').hide()
				.css('position', 'absolute')
				.appendTo('body');
		}
		datepickers = {
			from: this._getCalendars().children('div.calendar-from'),
			to:   this._getCalendars().children('div.calendar-to')
		};
		if (abinitio) {
			this._create_datepickers(datepickers);
			$(document).click( _(function (event) {
				var $calendars = this._getCalendars();
				if (!$calendars.is(':visible')) return;
				if ( !$(event.target).closest('body').length
						|| jQuery.contains(this.el, event.target)
						|| $(event.target).closest('#' + this._getCalendars().attr('id')).length) {
					return;
				}
				this.toggleCalendars();
			}).bind(this) );
		} else {
			filter = this.model.get('filter') || {};
			if (this.model.hasChanged('isFilterEnabled') && this.model.get('isFilterEnabled')) {
				if (!filter.to && !filter.from) {
					_.defer(_(function () { this.toggleCalendars(); }).bind(this));
				}
			}
			if (this.model.hasChanged('filter')) {
				this._format_date_and_update();

				this._render_date_update_datepicker_restrictions(datepickers, filter);
			}
		}
		this._update_datepickers(datepickers);
		_.defer( _(function () { this._update_datepickers(datepickers); }).bind(this) );
	},
	_render_date_update_datepicker_restrictions: function (datepickers, filter) {
		var ff = _({}).extend(filter || {});

		datepickers.from.datepicker('option', 'maxDate', filter.to ? new Date(filter.to * 1000) : null);
		datepickers.to.datepicker('option', 'minDate', filter.from ? new Date(filter.from * 1000) : null);
		
		datepickers.from.datepicker('setDate', filter.from ? new Date(filter.from * 1000) : null);
		datepickers.to.datepicker('setDate', filter.to ? new Date(filter.to * 1000) : null);
		
		filter.from && (ff.from = parseInt(datepickers.from.datepicker('getDate').getTime() / 1000));
		filter.to   && (ff.to   = parseInt(datepickers.to.datepicker('getDate').getTime()   / 1000));
		
		this.model.set({ filter: ff }, { silent: true });
	},
	setInputValue: function (event) {
		var val = $(event.target).val()
		  , type = this.model.get('type');
		this.$('.field-filters-value-container')
			.attr('title', '')
			.children('input')
			.removeClass('invalid');
		if (type == 'integer' || type == 'double') {
			val = jQuery.trim(val);
			if (val) {
				type == 'integer' && /^[+\-]?[0-9]+$/.test(val) && (val = parseInt(val, 10));
				if (type == 'double' && /^[+\-]?[0-9]*([.,][0-9]+)?$/.test(val))
					val = parseFloat(val.replace(/,/, '.'));
			} else {
				val = null;
			}
		}
		if (val == null) {
			this.model.unset('filter');
		} else {
			this.model.set({ filter: val });
		}
	},
	_render_datetime: function (abinitio) {
		this._render_date.apply(this, arguments);
	},
    _render_datetimestamp: function (abinitio) {
        this._render_date.apply(this, arguments);
    },
	_render_string: function (abinitio, error) {
		this._render_integer.apply(this, arguments);
	},
	_render_integer: function (abinitio, error) {
		var $container = this.$('.field-filters-value-container');
		if (!_.isUndefined(error)) {
			$container
				.attr('title', $container.data('validationerror') || '')
				.children('input')
				.addClass('invalid');
		} else {
			if (this.model.hasChanged('isFilterEnabled') && this.model.get('isFilterEnabled')) {
				$container.children('input:first').val(this.model.get('filter') || '').focus();
			}
		}
	},
	_render_double: function (abinitio, error) {
		this._render_integer.apply(this, arguments);
	},
	render: function () {
		var type = this.model.get('type')
		  , filter = this.model.get('filter')
		  , tpl = this.filterTemplates[type] || this.filterTemplates['default']
		  , tplData = _.extend({}, this.tplData, { filter: filter || '' });

		if ($(this.el).is(':empty')) {
			$(this.el).html(tpl(tplData));
			_.isFunction(this['_render_' + type]) && this['_render_' + type]('abinitio');
		} else {
			_.isFunction(this['_render_' + type]) && this['_render_' + type]();
		}
		
		return this;
	},
	renderOnError: function (model, error) {
		var type = this.model.get('type');
		_.isFunction(this['_render_' + type]) && this['_render_' + type](false, error);
		return this;
	}
});
// View столбца отчёта
var ReportFieldView = Backbone.View.extend({
	tagName: "li",
	className: "report-row",
	events: {
		"change .select-field > select":           "updateOnFieldChange",
		"click  .move-row-up":                     "moveRowUp",
		"click  .move-row-down":                   "moveRowDown",
		"click  .add-row":                         "addRow",
		"click  .remove-row":                      "removeRow",
		"click  .field-add-btt":                   "addRow",
		"click  .field-remove-btt":                "removeRow",
		"click  .field-hide .field-btt":           "updateHiddenState",
		"click  .field-use-as-source .field-btt":  "updateInputState",
		"blur   input.field-alt-name-value":       "updateDisplayName",
		"change input.field-alt-name-value":       "updateDisplayName",
		"keyup  input.field-alt-name-value":       "updateDisplayName",
		"click  button":                           "preventManualFormSubmission",
		"click  .function-field":                  "showFunctionsSelect",
		"click  .aggregate-field":                 "showAggregatorsSelect",
		"click  .field-filters":                   "enableFilters"
	},
	initialize: function () {
		_.bindAll(this, "render", "renderOnError", "generateDisplayName", "updateFunctionsView", "updateFiltersView", "createValueModel");
		this.template = _.template($("#field-row-template").html());
		this.model.bind('change', this.render);
		this.model.bind('error',  this.renderOnError);
		this.model.bind('change:value', this.createValueModel);
		this.model.bind('change:value', this.generateDisplayName);
		this.model.values.bind('change:function', this.generateDisplayName);
		this.model.values.bind('change:aggregator', this.generateDisplayName);
		this.model.values.bind('change:isFunctionEnabled', this.generateDisplayName);
		this.model.values.bind('change:isAggregatorEnabled', this.generateDisplayName);
		this.model.bind('change:value', this.updateFunctionsView);
		this.model.values.bind('change:function', this.updateFunctionsView);
		this.model.values.bind('change:aggregator', this.updateFunctionsView);
		this.model.values.bind('change:isFunctionEnabled', this.updateFunctionsView);
		this.model.values.bind('change:isAggregatorEnabled', this.updateFunctionsView);
		this.model.bind('change:value', this.updateFiltersView);
		this.model.values.bind('change:isFilterEnabled', this.updateFiltersView);
		
		this.model.view = this;
	},
	addRow: function () {
		this.model.after({});
	},
	removeRow: function () {
		if (this.model.get('isFirst') && this.model.get('isLast')) return;
		this.remove();
	},
	moveRowUp: function () {
		if (!this.model.isFirst())
			this.model.prev().before(this.model);
	},
	moveRowDown: function () {
		if (!this.model.isLast())
			this.model.next().after(this.model);
	},
	remove: function () {
		if (!this.model.collection) { return; }
		this.model.values.each(function (model) {
			model.view       && model.view.remove();
			model.filterView && model.filterView.remove();
		});
		$(this.el).fadeOut('slow', _.bind(function () {
			this.model.remove();
			Backbone.View.prototype.remove.apply(this, arguments);
		}, this));
	},
	_collectDataFromOption: function ($option) {
		var aggregators = []
		  , functions   = [];
		if ($.trim($option.data('aggregation') || '')) {
			aggregators = _.select($option.data('aggregation') || [], function (item) {
				return item.name && item.title;
			});
		}
		if ($.trim($option.data('functions') || '')) {
			functions = _.select($option.data('functions') || [], function (item) {
				return item.name && item.title;
			});
		}
		return {
			aggregators: aggregators,
			functions:   functions,
			type:        $.trim($option.data('type') || '')
		};
	},
	_getValueModel: function () {
		var value = this.model.get('value');
		return value ? this.model.values.get(value) : null;
	},
	createValueModel: function () {
		var valueModel = this._getValueModel();
		if (valueModel && !valueModel.view) {
			new ReportFieldValueView({model: valueModel});
			new ReportFieldValueFilterView({
				model: valueModel,
				rowId: this.model.get('id'),
				valueId: this.model.get('value')
			});
			
			// set will trigger render!
			valueModel.set(this._collectDataFromOption(
				this.$('.select-field > select').find('option[value="'+ this.model.get('value') +'"]')
			));
		}
	},
	changeValue: function () {
		var valueModel = this._getValueModel();
		
		this.$('.function-fields').children().detach();
		this._setSelectValue(this.$('.select-field > select'), this.model.get('value'));
		this.$('div.field-row-wrapper')
			.removeClass('has-aggregators has-functions');
		if (valueModel) {
			this.$('.function-fields')
				.append(valueModel.view.el);
			if (valueModel.get('aggregators').length) {
				this.$('div.field-row-wrapper')
					.addClass('has-aggregators');
			}
			if (valueModel.get('functions').length) {
				this.$('div.field-row-wrapper')
					.addClass('has-functions');
			}
		}
	},
	updateFunctionsView: function () {
		var valueModel = this._getValueModel()
		  , $row = this.$('div.field-row-wrapper');
		$row.removeClass('has-aggregator has-function function-enabled aggregator-enabled');
		if (valueModel) {
			if (valueModel.get('isAggregatorEnabled')) {
				$row.addClass('aggregator-enabled');
			}
			if (valueModel.get('isFunctionEnabled')) {
				$row.addClass('function-enabled');
			}
			if (valueModel.get('aggregator')) {
				$row.addClass('has-aggregator');
			}
			if (valueModel.get('function')) {
				$row.addClass('has-function');
			}
		}
	},
	showAggregatorsSelect: function (event) {
		var $target = $(event.currentTarget)
		  , valueModel = this._getValueModel()
		  , curValue;
		if (valueModel && valueModel.get('aggregators').length) {
			curValue = valueModel.get('isAggregatorEnabled');
			if ($(event.target).is('.field-icon')) {
				valueModel.set({ isAggregatorEnabled: !valueModel.get('isAggregatorEnabled') });
			} else {
				valueModel.set({ isAggregatorEnabled: true });
			}
			if (valueModel.get('isAggregatorEnabled') && curValue !== valueModel.get('isAggregatorEnabled')) {
				$target.find('select').selectmenu('open');
			}
		}
	},
	showFunctionsSelect: function (event) {
		var $target = $(event.currentTarget)
		  , valueModel = this._getValueModel()
		  , curValue;
		if (valueModel && valueModel.get('functions').length) {
			curValue = valueModel.get('isFunctionEnabled');
			if ($(event.target).is('.field-icon')) {
				valueModel.set({ isFunctionEnabled: !valueModel.get('isFunctionEnabled') });
			} else {
				valueModel.set({ isFunctionEnabled: true });
			}
			if (valueModel.get('isFunctionEnabled') && curValue !== valueModel.get('isFunctionEnabled')) {
				$target.find('select').selectmenu('open');
			}
		}
	},
	_setSelectValue: function () {
		ReportFieldValueView.prototype._setSelectValue.apply(this, arguments);
	},
	renderOnError: function (model, error) {
		this._setSelectValue(this.$('.select-field > select'), model.get('value'));
		this.$('.field-hide input').attr('checked', m.get('isHidden'));
		this.$('.field-use-as-source input').attr('checked', m.get('isInput'));
		// TODO isFirst and isLast
	},
	render: function () {
		var $el = $(this.el)
		  , changedAttributes;
		if ($el.is(':empty')) {
			$el.html(this.template({
				m:   this.model.toJSON(),
				idx: this.model.index() + 1
			}))
			.find('.select-field')
			.append(this.options.sourceOfValues)
			.find('select')
			.selectmenu({width: 172});
			this.changeValue();
			$el.find('button').each(function () {
				$(this).is(':ui-button') || $(this).button($(this).data('ui-options') || {});
			});
			$el.hide().fadeIn('slow');
		} else {
			var m = this.model;
			if (m.hasChanged('isLast')) {
				this.$('.move-row-down').attr('disabled', m.get('isLast'));
				if (m.get('isLast'))
					this.$('.move-row-down').button('disable');
				else
					this.$('.move-row-down').button('enable');
			}
			if (m.hasChanged('isFirst')) {
				this.$('.move-row-up').attr('disabled', m.get('isFirst'));
				if (m.get('isFirst'))
					this.$('.move-row-up').button('disable');
				else
					this.$('.move-row-up').button('enable');
			}
			if (m.hasChanged('isFirst') || m.hasChanged('isLast')) {
				if (m.get('isFirst') && m.get('isLast'))
					this.$('div.field-row-wrapper').addClass('is-single');
				else
					this.$('div.field-row-wrapper').removeClass('is-single');
			}
			if (m.hasChanged('value')) {
				this.$('.value-container').html(m.escape('value') + " &mdash; " + this.$('.select-field > select option:selected').text());
				this.changeValue();
				if (m.get('value')) {
					this.$('div.field-row-wrapper').addClass('has-value');
				} else {
					this.$('div.field-row-wrapper').removeClass('has-value');
				}
			}
			if (m.hasChanged('isHidden')) {
				this.$('.field-hide input').attr('checked', m.get('isHidden'));
				if (m.get('isHidden')) {
					this.$('div.field-row-wrapper').addClass('is-hidden')
						.find('.field-hide > span')
						.attr('title', this.$('div.field-row-wrapper .field-hide > span').data('title').hidden);
				} else {
					this.$('div.field-row-wrapper').removeClass('is-hidden')
						.find('.field-hide > span')
						.attr('title', this.$('div.field-row-wrapper .field-hide > span').data('title').visible);
				}
			}
			if (m.hasChanged('isInput')) {
				this.$('.field-use-as-source input').attr('checked', m.get('isInput'));
				if (m.get('isInput')) {
					this.$('div.field-row-wrapper').addClass('is-input')
						.find('.field-use-as-source > span')
						.attr('title', this.$('div.field-row-wrapper .field-use-as-source > span').data('title').input);
				} else {
					this.$('div.field-row-wrapper').removeClass('is-input')
						.find('.field-use-as-source > span')
						.attr('title', this.$('div.field-row-wrapper .field-use-as-source > span').data('title').notinput);
				}
			}
			if (m.hasChanged('displayName') && this.$('input.field-alt-name-value').val() != m.get('displayName')) {
				this.$('input.field-alt-name-value').val(m.get('displayName'));
			}
		}
		return this;
	},
	updateOnFieldChange: function (event) {
		this.model.set({
			value: this.$('.select-field > select').val()
		});
	},
	generateDisplayName: function () {
		var valueModel = this._getValueModel()
		  , humanValue = this.model.humanValue()
		  , aggregator
		  , humanAggregator
		  , funct
		  , humanFunct
		  , $input;

		if (valueModel) {
			valueModel.get('isAggregatorEnabled') && (aggregator = valueModel.get('aggregator'));
			valueModel.get('isFunctionEnabled') && (funct = valueModel.get('function'));
			$input = this.$('input.field-alt-name-value');
			
			if (!$input.data('userChangedValue') || !$input.val()) {
				$input.data('userChangedValue', false);
				aggregator && (humanAggregator = _.detect(valueModel.get('aggregators'), function (a) { return a.name == aggregator; }).title);
				funct && (humanFunct = _.detect(valueModel.get('functions'), function (a) { return a.name == funct; }).title)
				$input
					.val(_.compact([humanValue, humanFunct, humanAggregator]).join('; '))
					.trigger('change', [true]);
			}
		}
	},
	setFilters: function (event) {
		var valueModel = this._getValueModel()
		  , tplData;
		if (!valueModel) { return; }
		
		this.$('.field-filters > .field-filters-value').detach();
		this.$('.field-filters').append(valueModel.filterView.el);
	},
	enableFilters: function (event) {
		var valueModel = this._getValueModel();
		if (valueModel) {
			if ($(event.target).is('.field-icon')) {
				valueModel.set({ isFilterEnabled: !valueModel.get('isFilterEnabled') })
			} else {
				valueModel.set({ isFilterEnabled: true })
			}
		}
	},
	updateFiltersView: function () {
		var valueModel = this._getValueModel();
		this.$('.field-row-wrapper').removeClass('filter-enabled');
		if (valueModel && valueModel.get('isFilterEnabled')) {
			this.$('.field-row-wrapper').addClass('filter-enabled');
		}
		this.setFilters();
	},
	updateInputState: function (event) {
		this.model.set({
			isInput: !this.model.get('isInput')
		});
	},
	updateHiddenState: function (event) {
		this.model.set({
			isHidden: !this.model.get('isHidden')
		});
	},
	updateDisplayName: function (event, isGenerated) {
		if (event.type == 'change' && !isGenerated)
			this.$('input.field-alt-name-value').data('userChangedValue', true);
		this.model.set({
			displayName: this.$('input.field-alt-name-value').val()
		});
	},
	preventManualFormSubmission: function (event) {
		event.preventDefault();
	}
});
// View конструктора отчётов
var ReportView = Backbone.View.extend({
	events: {
		"click .new":            "createNew",
		"blur .report-title":    "updateReportName",
		"change .report-title":  "updateReportName",
		"change .publish input": "updateStatus",
		"keyup .report-title":   "updateReportName",
		"click .preview":        "previewResults",
		"click .save":           "saveReport",
		"click .exit":           "exitFromEditor",
		"submit form":           "preventManualFormSubmission",
		"click button":          "preventManualFormSubmission"
	},
	initialize: function () {
		var date;
		_.bindAll(this, 'addOne', 'reorder', 'render', 'removeOne');
		
		this.collection = this.model.get('fields');
		
		this.collection.bind('add',     this.addOne);
		this.collection.bind('remove',  this.removeOne);
		this.collection.bind('reorder', this.reorder);
		this.collection.bind('all',     this.render);
		this.model.bind('change',       this.render);
		this.isPostingData = false;
		
		$(this.el).disableSelectionLight();
		
		if (this.options.reportFields.length) {
			_(this.options.reportFields).each(_(function (field, index, all) {
				var valueModelAttributes = {};
				this.collection.add(new this.collection.model({
					order: index + 1
				}));
				this.collection.last().set({
					value:    field.field,
					isHidden: !!parseInt(field.options.hiden || '0', 10),
					isInput:  !!parseInt(field.options.input  || '0', 10),
					displayName: field.title || ''
				});
				if (field.options['function']) {
					_(valueModelAttributes).extend({
						isFunctionEnabled: true,
						'function': field.options['function']
					});
				}
				if (field.options.aggregation) {
					_(valueModelAttributes).extend({
						isAggregatorEnabled: true,
						aggregator: field.options.aggregation
					});
				}
				if (!_.isUndefined(field.options.filter)) {
					if (field.options.filter.from && field.options.filter.to) {
						date = function (ts) {
							var dt = new Date(0);
							ts = _(ts.split('.')).map(function (n) { return parseInt(n, 10) || 0; });
							dt.setFullYear(ts[2]);
							dt.setMonth(ts[1] - 1);
							dt.setDate(ts[0]);
							dt.setHours(0);
							dt.setMinutes(0);
							return Math.floor(dt.getTime() / 1000);
						}
						field.options.filter = {
							from: date(field.options.filter.from),
							to:   date(field.options.filter.to)
						};
					}
					_(valueModelAttributes).extend({
						isFilterEnabled: true,
						filter: field.options.filter
					});
				}
				this.collection.last().values.first().set(valueModelAttributes);
			}).bind(this));
		} else {
			this.createNew();
		}
		
		this.render();
	},
	updateButtons: function () {
		this.$('.preview').attr('disabled', !this.collection.isComplete() || !this.collection.length || !!this.options.xhr);
		this.$('.save').attr('disabled', !this.collection.isComplete() || !this.collection.length || !this.model.get('name') || !!this.options.xhr);
		this.$('.delete').attr('disabled', _.isUndefined(this.model.get('report_id')) || !!this.options.xhr);
		this.$('.exit').attr('disabled', !!this.options.xhr);
	},
	render: function () {
		var collection = this.collection;
		this.$('span.total').text(this.collection.length);
		
		this.$('ol').sortable({
			axis: 'y',
			tolerance: 'pointer',
			handle: 'span.drag-handler',
			revert: true,
			update: function (event, ui) {
				var prev
				  , next
				  , myModel = collection.get(ui.item.data('id'));
				if ((prev = ui.item.prev('li.report-row')).length)
					collection.get(prev.data('id')).after(myModel);
				else if ((next = ui.item.next('li.report-row')).length)
					collection.get(next.data('id')).before(myModel);
			}
		});
		
		this.updateButtons();
		
		if (this.model.hasChanged('name'))
			this.$('.report-head h2').html(this.model.escape('name'));
		
		if (!this.options.previewUrl) this.$('.preview').hide();
		if (!this.options.saveUrl) this.$('.save').hide();
		return this;
	},
	_collectPossibleValues: function ($select) {
		return this._possibleValues || ( this._possibleValues = _.reduce($select.find('option').get(), function (memo, option) {
			if (option.value)
				memo[option.value] = $(option).text();
			return memo;
		}, {}) );
	},
	addOne: function (field) {
		var view;
		field.possibleValues = this._collectPossibleValues(this.options.sourceOfValues);
		view = new ReportFieldView({
			model: field,
			sourceOfValues: this.options.sourceOfValues.clone(true, true)
		});
		this.$('ol:first').append($(view.render().el).data('id', field.get('id')));
	},
	removeOne: function (field) {
		field.view.remove();
	},
	exitFromEditor: function (event) {
		window.location.href = this.options.exitUrl;
	},
	previewResults: function (event) {
		var data
		  , grid
		  , reportPreview;
		if (!this.collection.isComplete() || !this.options.previewUrl)
			return;
		
		data = {
			fields: this.collection.serialize(),
			domain: this.options.domain
		};
		
		reportPreview = this.$('.report-preview');
		grid = this.$('.report-preview .els-grid').get(0);
		grid && window.gridShowOverlay && gridShowOverlay(grid);
		
		this._post(this.options.previewUrl, data)
			.then(function () { grid && window.gridHideOverlay  && gridHideOverlay(grid); }
			    , function () { grid && window.gridHideOverlay  && gridHideOverlay(grid); })
			.done(function (data) { reportPreview.html('' + data); })
			.fail(function () {
				jQuery.ui.errorbox.clear();
				$('<div>' + HM._('Произошла ошибка при сохранении шаблона') + '</div>').errorbox({ level: 'notice' });
			});
	},
	saveReport: function (event) {
		var fields
		  , data;
		if (!this.collection.isComplete() || !this.options.saveUrl || !this.model.get('name'))
			return;

		data = {
			fields:    this.collection.serialize(),
			name:      this.model.get('name'),
			domain:    this.options.domain,
			report_id: this.model.get('report_id'),
			status:    Number(!!this.model.get('status'))
		};
		this._post(this.options.saveUrl, data)
			.then(
				_.bind(function (data) {
					jQuery.ui.errorbox.clear();
					$('<div>' + HM._('Шаблон отчетной формы успешно сохранен') + '</div>').errorbox();
					this.model.set({report_id: data.report_id});
				}, this),
				function () {
					jQuery.ui.errorbox.clear();
					$('<div>' + HM._('Произошла ошибка при сохранении шаблона') + '</div>').errorbox({ level: 'notice' });
				}
			);
	},
	_post: function (url, data) {
		var options = this.options, that = this;
		options.xhr && options.xhr.abort();
		
		return (options.xhr = $.post(url, data)
			.then(function () { delete options.xhr; that.updateButtons(); }
			    , function () { delete options.xhr; that.updateButtons(); }), this.updateButtons(), options.xhr);
	},
	updateReportName: function (event) {
		this.model.set({
			name: this.$('input.report-title').val()
		});
	},
	preventManualFormSubmission: function (event) {
		event.preventDefault();
	},
	reorder: function () {
		this.collection.each(function (field, idx) {
			$(field.view.el).appendTo(this.$('ol:first'))
				.find('.idx-value').text(idx + 1);
		}, this);
	},
	createNew: function (event) {
		event && event.preventDefault();
		this.collection.add({});
	},
	updateStatus: function (event) {
		this.model.set({
			status: $(event.currentTarget).is(':checked')
		});
	}
});
