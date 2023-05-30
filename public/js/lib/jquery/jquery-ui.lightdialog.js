(function ($) {
    $.widget("ui.lightdialog", {
        options: {
            dialogClass: '',
            modal: true,
            width: 500,
            autoOpen: true,
            rel: null,
            contentMaxHeight: 600
        },
        _create: function () {
            var self = this;
            var options = {
                autoOpen: false,
                beforeClose: function(e){
                    $('.ui-lightdialog-active-target').removeClass('ui-lightdialog-active-target');
                    delete self.activeElement;
                    delete self.activeElementIdx;
                }
            };

            self.activeElement = this.element.eq(0);

            self.uiDialog = $('<div id="'+ _.uniqueId('ui-lightdialog-') +'"/>')
                .hide()
                .appendTo('body')
                .dialog(_.extend({}, self.options, options));
            self.uiDialog.after('<div class="ajax-spinner-local"/>')
                .dialog('widget').addClass("lightdialog ui-lightdialog");
            self.related = self.options.rel && self.options.rel.length ? $(self.options.rel) : null;
            if (self.related) {
                self.related = self.__filterRelated(self.related);
                self.activeElementIdx = self.__findActive(self.activeElement.get(0));
                this.activeElement = $(this.related[this.activeElementIdx]);
            }
        },
        _init: function () {
            if ( this.options.autoOpen ) {
                this.open();
            }
        },
        isOpen: function () {
            return this.uiDialog.dialog('isOpen');
        },
        next: function () {
            this.__step('next');
        },
        previous: function () {
            this.__step('prev');
        },
        __findActive: function (item) {
            var index = 0
              , found;
            if (this.related) {
                found = _.find(this.related, function (items, idx) {
                    index = idx;
                    return _.indexOf(items, item) != -1;
                });
                if (found == null) {
                    index = -1;
                }
            }
            return index;
        },
        __filterRelated: function (related) {
            var groupped = []
              , currentHref;

            related.each(function () {
                var href = this.href;
                if (href !== currentHref) {
                    groupped.push([]);
                    currentHref = href;
                }
                _.last(groupped).push(this);
            });
            return groupped;
        },
        __step: function (dir) {
            var idx;

            if (~_(['prev', 'next']).indexOf(dir)
                    && this.related
                    && !this.dataLoading
                    && !(dir == 'prev' && this.isFirst())
                    && !(dir == 'next' && this.isLast())) {
                idx = 1;
                this.activeElementIdx = this.activeElementIdx + (dir == 'prev' ? -1 : 1);
                this.activeElement = $(this.related[this.activeElementIdx]);
            }
            if (!dir || _.isNumber(idx)) {
                if (this.activeElement == null) {
                    this.activeElement = this.element.eq(0);
                    if (this.related) {
                        this.activeElementIdx = this.__findActive(this.activeElement.get(0));
                        this.activeElement = $(this.related[this.activeElementIdx]);
                    }
                }
                this._loadData(this.activeElement.eq(0).attr('href'));
                this.element.removeClass('ui-lightdialog-active-target');
                this.related
                    && $(_.flatten(this.related)).removeClass('ui-lightdialog-active-target');
                this.activeElement.addClass('ui-lightdialog-active-target');
                this._setupButtons();
                this._setupTitle();
            }
        },
        open: function () {
            var self = this;
            if (self.isOpen()) { return; }

            self.__step();
            this.uiDialog.dialog('open');
        },
        _loadData: function (url) {
            var self = this;
            if (!self.dataLoading) {
                self.dataLoading = true;
                self.uiDialog.dialog('widget').addClass('ui-lightdialog-loading');
                $.ajax(url, {
                    dataType: 'text',
                    global: false
                })
                .always(function () {
                    self.dataLoading = false;
                    self.uiDialog.dialog('widget').removeClass('ui-lightdialog-loading');
                    self._setupButtons();
                })
                .done(function (text) {
                    self.uiDialog.html(text);
                    self.uiDialog.css('overflow', 'visible');
                    if (self.options.contentMaxHeight) {
                        if (!$(document.documentElement).is('.ie6')) {
                            self.uiDialog.css({
                                overflow: 'auto',
                                maxHeight: self.options.contentMaxHeight
                            });
                        } else if (self.uiDialog.height() > self.options.contentMaxHeight) {
                            self.uiDialog.css({
                                overflow: 'auto',
                                height: self.options.contentMaxHeight
                            });
                        }
                    }
                    self.uiDialog
                        .dialog('option', 'position', self.uiDialog.dialog('option', 'position'));
                });
            }
        },
        __translate: function (key) {
            var defaults = {
                next: "Next",
                prev: "Previous"
            }
            return this.options.l10n && this.options.l10n[key]
                ? this.options.l10n[key]
                : defaults[key] || 'no translation';
        },
        _setupButtons: function () {
            var buttons = []
              , self = this;

            if (self.related) {
                buttons = [{
                    text: self.__translate('prev'),
                    click: function () { self.previous() },
                    disabled: self.isFirst() || self.dataLoading
                }, {
                    text: self.__translate('next'),
                    click: function () { self.next() },
                    disabled: self.isLast() || self.dataLoading
                }];
            }

            self.uiDialog.dialog('option', 'buttons', buttons);
        },
        _setupTitle: function () {
            var title = '';
            if (this.activeElement) {
                title = this.activeElement.last().attr('title') || '';
            }
            this.uiDialog.dialog('option', 'title', title);
        },
        isLast: function () {
            return !this.related || (this.related.length == (this.activeElementIdx + 1));
        },
        isFirst: function () {
            return !this.related || (this.activeElementIdx == 0);
        },
        close: function () {
            if (!this.isOpen()) { return; }

            this.uiDialog.dialog('close');
        },
        destroy: function () {
            if (this.uiDialog) {
                this.uiDialog.dialog('destroy');
            }

            this.element
                .unbind('.lightdialog');
            this.element.removeClass('ui-lightdialog-active-target');
            $.Widget.prototype.destroy.apply(this, arguments);
        },
        _setOptions: function (options) {
            this.uiDialog && this.uiDialog
                .dialog('option', options);
        },
        _setOption: function (key, value) {
            this.uiDialog && this.uiDialog
                .dialog('option', key, value);
            $.Widget.prototype._setOption.apply(this, arguments);
        }
    });

})(jQuery);