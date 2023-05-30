$.widget( "ui.portlets", {
	options: {
		// new option
		columns: '.column',
		disabled: false,
		appendTo: 'parent',
		axis: false,
		cancel: ':input,button',
		// connectWith - unused!
		containment: false,
		cursor: 'auto',
		cursorAt: false,
		delay: 0,
		distance: 1,
		dropOnEmpty: true,
		forceHelperSize: false,
		forcePlaceholderSize: false,
		grid: false,
		handle: 'clone',
		helper: 'original',
		items: '> *',
		opacity: false,
		placeholder: false,
		revert: false,
		scroll: true,
		scrollSensitivity: 20,
		scrollSpeed: 20,
		tolerance: 'intersect',
		zIndex: 1000
	},
	
	__sortable: null,

	_create: function () {
		var o = _.extend({}, this.options)
		    , self = this
		    , handlers = {};

		this.element
			.addClass("ui-portlets-edit-mode")
			.find(o.columns)
			.addClass("ui-portlets-sortable");

		_.each('start sort change beforeStop stop update receive remove over out activate deactivate'.split(/\s+/), function (item) {
			handlers[item] = function () {
				var args = _.toArray(arguments);
				args.unshift(item);
				self._trigger.apply(self, args);
			};
		});

		o.connectWith = o.columns;

		this.__sortable = this.element.find(o.columns)
			.sortable(_.extend(o, handlers));

		this.element
			.disableSelection();
	},

	destroy: function () {
		this.element
			.enableSelection();
		this.__sortable.sortable('destroy');
		this.element
			.removeClass("ui-portlets-edit-mode")
			.find(this.options.columns)
			.removeClass("ui-portlets-sortable");

		$.Widget.prototype.destroy.apply(this, arguments);
	},

	serialize: function () {
		return JSON.stringify(this.toArray());
	},

	toArray: function () {
		var columns = [];
		$(this.__sortable).each(function () {
			columns.push($(this).sortable('toArray'));
		});
		return columns;
	},

	_setOption: function (key, value) {
		// TODO: disabled
		$.Widget.prototype._setOption.apply(this, arguments);
		switch (key) {
		case "disabled":
			if (value) {
			} else {
			}
			break;
		}
	}
});

$.extend($.ui.portlets, {
	version: "0.0.1"
});