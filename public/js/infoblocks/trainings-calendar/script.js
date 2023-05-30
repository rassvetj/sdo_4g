(function ($) {
"use strict";

$.widget( "els.infoblockTrainingsCalendar", {
	options: {
	},

	_create: function () {
		var o = _.extend({}, this.options)
		  , _this = this
		  , currentDate
		  , fnCalendar
		  , datepickerOptions;

		this.element.find('.calendar-container').disableSelectionLight();

		datepickerOptions = {
			firstDay: 1,
			selectOtherMonths: false,
			showOtherMonths: false,
			showButtonPanel: true,
			onChangeMonthYear: function (year, month, inst) {
				_this._refreshMonthLabel(year, month);
				_.defer(function () {
					_this._showTrainingsOnCalendar(year, month);
				});
				_this._showTrainingsSummary(year, month);
			},
			onSelect: function () {
				var date;

				if (_this._calendar) {
					date = _this._calendar.datepicker('getDate');
					_this._refreshSelectedDate(date);
					_this._showTrainingsList(date);
					_.defer(function () {
						_this._showTrainingsOnCalendar(date.getFullYear(), date.getMonth() + 1);
					});
				}
			}
		};
		this._calendar = this.element.find('.calendar').datepicker(datepickerOptions);

		fnCalendar = this._calendar.datepicker;
		this._calendar.datepicker = function (action) {
			var ret = fnCalendar.apply(this, arguments)
			  , date;
			if (action === 'refresh') {
				date = _this._calendar.datepicker('getDate');
				_.defer(function () {
					_this._showTrainingsOnCalendar(date.getFullYear(), date.getMonth() + 1);
				});
			}

			return ret;
		};

		this._calendarHeader   = this.element.find('.calendar-header');
		this._realPrevButton   = this._calendar.find('.ui-datepicker-prev');
		this._realNextButton   = this._calendar.find('.ui-datepicker-next');
		this._realTodayButton  = this._calendar.find('.ui-datepicker-buttonpane > .ui-datepicker-current');
		this._trainingsList    = this.element.find('.trainings-list-list > .scroll-document');
		this._trainingsSummary = this.element.find('.calendar-container .month-summary');
		this.element.find('.trainings-list-list').css('height', this.element.find('.calendar-container').innerHeight() - this.element.find('.trainings-list > h2').outerHeight(true));

		this._today = this._calendar.datepicker('getDate');

		this._refreshMonthLabel(this._today.getFullYear(), this._today.getMonth() + 1);
		this._refreshSelectedDate(this._today);
		this._showTrainingsList(this._today);
		this._showTrainingsOnCalendar(this._today.getFullYear(), this._today.getMonth() + 1);
		this._showTrainingsSummary(this._today.getFullYear(), this._today.getMonth() + 1);

		this.element.delegate('.els-calendar-prev', 'click', function (event) {
			event.preventDefault();
			_this._realPrevButton.trigger('click');
		});
		this.element.delegate('.els-calendar-next', 'click', function (event) {
			event.preventDefault();
			_this._realNextButton.trigger('click');
		});
		this.element.delegate('.trainings-list > h2 > .today', 'click', function (event) {
			event.preventDefault();
			_this._calendar.datepicker('setDate', _this._today);
			datepickerOptions.onSelect();
		});

		this.element.delegate('.els-calendar-prev', 'mousedown', function (event) {
			_this._calendarHeader
				.removeClass('calendar-header-next')
				.addClass('calendar-header-prev');
		});
		this.element.delegate('.els-calendar-next', 'mousedown', function (event) {
			_this._calendarHeader
				.removeClass('calendar-header-prev')
				.addClass('calendar-header-next');
		});

		$(window).bind('datepicker-set-defaults', function () {
			var date = _this._calendar.datepicker('getDate');
			_this._refreshMonthLabel(date.getFullYear(), date.getMonth() + 1);
		});
		$(document).bind('mouseup', function (event) { if (/calendar-header-(prev|next)/.test(_this._calendarHeader.get(0).className)) {
			_this._calendarHeader
				.removeClass('calendar-header-prev calendar-header-next');
		} });
	},

	_refreshMonthLabel: function (year, month) {
		if (this._month_label == null) {
			this._month_label = this.element.find('.calendar-header > .els-calendar-month-label');
		}
		this._month_label.html(this._calendar.datepicker('option', 'monthNames')[month - 1] + ' ' + year);
	},

	_refreshSelectedDate: function (date) {
		if (this._current_date_label == null) {
			this._current_date_label = this.element.find('.els-calendar-date-selected');
		}
		this._current_date_label.html('' + date.getDate() + ' ' + this.options.months[date.getMonth() + 1] + ' ' + date.getFullYear());
	},

	_loadTrainingsList: function (year, month) {
		var deferred = $.Deferred()
		  , key = '' + year + '-' + month
		  , _this = this
		  , xhr;

		if (this._loaded_info_cache == null) {
			this._loaded_info_cache = {};
		}
		if (this._loaded_info_cache[key] == null || this._loaded_info_cache[key].data == null) {
			if (this._loaded_info_cache[key] == null) {
				this._loaded_info_cache[key] = {};
			}
			if (this._loaded_info_cache[key].xhr == null || this._loaded_info_cache[key].xhr.isRejected()) {
				this._loaded_info_cache[key].xhr = $.getJSON(this.options.url + key);
			}
			this._loaded_info_cache[key].xhr.done(function (data) {
				if (data && _.isArray(data)) {
					data = _.map(data, function (training) {
						var training = _.extend({
							date:        0,
							attendees:   0,
							url:         '',
							title:       '',
							description: ''
						}, training);
						training.date = new Date(parseInt(training.date, 10) * 1000);
						training.attendees = parseInt(training.attendees) || 0;
						return training;
					});
					_this._loaded_info_cache[key].data = data;
				} else {
					data = [];
				}
				deferred.resolveWith(_this, [ data ]);
			}).fail(function () {
				deferred.resolveWith(_this, [ [] ]);
			});
		} else {
			deferred.resolveWith(this, [ this._loaded_info_cache[key].data ]);
		}

		return deferred.promise();
	},

	_showTrainingsList: function (date) {
		var _this = this
		  , deferred = $.Deferred()
		  , todayTS = date.getTime()
		  , tomorrowTS = todayTS + 60 * 60 * 24 * 1000;

		if (this.__active_showTrainingsList != null) {
			this.__active_showTrainingsList.reject();
		}
		this.__active_showTrainingsList = deferred;

		this._setStateLoading();
		this._loadTrainingsList(date.getFullYear(), date.getMonth() + 1).always(function () {
			deferred.resolveWith(this, arguments);
		});
		deferred.done(function (trainings) {
			var today = _.filter(trainings, function (training) {
				var trainingTime = training.date.getTime();
				return todayTS <= trainingTime && trainingTime < tomorrowTS;
			});
			today = _.sortBy(today, function (training) {
				return training.date.getTime()
			});
			_this._trainingsList.html('<ul>' + ( _.reduce(today, function (memo, training) {
				return memo + '<li>'+ _this.options.templates.item(training) +'</li>';
			}, '') || '<li class="empty-set">' + _this.options.templates.empty() + '</li>') + '</ul>');
		}).always(function () {
			_this._unsetStateLoading();
		});
	},

	_showTrainingsOnCalendar: function (year, month) {
		var _this = this
		  , deferred = $.Deferred();

		if (this.__active_showTrainingsOnCalendar != null) {
			this.__active_showTrainingsOnCalendar.reject();
		}

		deferred.done(function (trainings) {
			var activeDates;
			activeDates = _.reduce(trainings, function (memo, training) {
				memo[training.date.getTime()] = training.date.getDate();
				return memo;
			}, {});
			activeDates = _.uniq(_.values(activeDates));
			_this._calendar.find('table td').each(function () {
				var $this = $(this)
				  , content = parseInt(jQuery.trim($this.text() || '-1'), 10)
				if (_.indexOf(activeDates, content) != -1) {
					$this.addClass('els-trainings-has-training');
				} else {
					$this.removeClass('els-trainings-has-training');
				}
			});
		});
		this._loadTrainingsList(year, month).always(function () {
			deferred.resolveWith(this, arguments);
		});
		this.__active_showTrainingsOnCalendar = deferred;
	},

	_showTrainingsSummary: function (year, month) {
		var _this = this
		  , deferred = $.Deferred();

		if (this.__active_showTrainingsSummary != null) {
			this.__active_showTrainingsSummary.reject();
		}
		this.__active_showTrainingsSummary = deferred;

		this._loadTrainingsList(year, month).always(function () {
			deferred.resolveWith(this, arguments);
		});

		deferred.done(function (trainings) {
			var attendeesTotal
			  , trainingsTotal;
			trainings = _.groupBy(trainings, function (training) { return training.url; });
			attendeesTotal = _.reduce(trainings, function (memo, trainingsGroup) {
				return memo + trainingsGroup[0].attendees;
			}, 0);
			trainingsTotal = _.filter(trainings, function (trainingsGroup) {
				return trainingsGroup[0].this_month_start == 'y';
			}).length;
			_this._trainingsSummary.html(_this.options.templates.summary({
				attendeesTotal: attendeesTotal,
				trainingsTotal: trainingsTotal
			}));
		});
	},

	_setStateLoading: function () {
	
	},
	_unsetStateLoading: function () {},

	destroy: function () {
		//this.element

		$.Widget.prototype.destroy.apply(this, arguments);
	},

	_setOption: function (key, value) {
		// TODO
		$.Widget.prototype._setOption.apply(this, arguments);
	}
});
$.extend($.els.infoblockTrainingsCalendar, {
	version: "1.0.0"
});

})(jQuery);