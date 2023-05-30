(function($) {
    $.widget("ui.treeselect", {
        options: {
            size: 5,
            remoteUrl: null,
            selected: 0,
            itemId: null
        },
        _selectItem: function (value) {
            this.ul.children("li").removeClass("li_select");
            if (value == null || value == 0) {
                this.options.selected = null;
                this.element.html('');
            } else {
                this.options.selected = value;
				    this.ul.children("li#" + this._id + value).addClass("li_select");
				    this.element.html('<option value="'+ value +'" selected>'+ value +'</option>');
            }
        },
        _create: function () {
            var that = this;
            this._id = _.uniqueId('treeselect');
            this.element.wrap('<div class="ui-treeselect ui-widget"></div>');
            this.element.parent().prepend("<div class=\"tree-select-title\"><span>"+ this.element.closest("dd").prev("dt").html() +"</span></div><div class=\"tree-select-nselect\"><ul class=\"tree-select-ul\"></ul></div>");
            this.element.closest("dd").prev("dt").remove();
            this.ul = this.element.parent().find(".tree-select-ul");
            this.ul.disableSelection();
            this.element.parent().prepend('<div class="tree-select-control"><a class="tree-select-control-home"></a><a class="tree-select-control-up"></a></div>');
            this.control = this.element.parent().find(".tree-select-control-up");

            this._loadData(this.options.itemId);

            this.ul.delegate('li', 'dblclick', function (event) {
                event.preventDefault();

                that._selectItem($(this).data('value'));
                if ($(this).children('a').length) {
                    that._loadData($(this).data('value'));
                }
            });

				this.ul.delegate('li', 'click', function (event) {
				    event.preventDefault();

				    if (that.options.selected == $(this).data('value')) {
				        that._selectItem(null);
				    } else {
				        that._selectItem($(this).data('value'));
				    }
				});

            this.element.parent().delegate('.tree-select-control a', 'click', function (event) {
                event.preventDefault();

                that._loadData($(this).data('value'));
            });
        },
        destroy: function () {
            this.container.remove();
            this.ul.enableSelection();
            $.Widget.prototype.destroy.apply(this, arguments);
        },
        _loadData: function (itemId) {
            var that = this
              , value = itemId != null ? itemId : 0;

            this.ul.html('<li> ' + $.ui.treeselect.locale.loading + '</li>');
            $.ajax(that.options.remoteUrl + "/item_id/" + value, {
                dataType: 'xml',
                global: false
            }).done(function (xml) {
                var htmlLi = [];

                if (xml && xml.documentElement) {
                    htmlLi = _.map(_.toArray(xml.documentElement.childNodes), function (n) {
                        var id = n.getAttribute('id')
                          , value = n.getAttribute('value');

                        if (n.getAttribute('leaf') == 'yes') {
                            return '<li id="'+ that._id + id +'" data-value="'+ id +'"><span>'+  value +'</span></li>';
                        } else {
                            return '<li id="'+ that._id + id +'" data-value="'+ id +'"><a href="'+ that.options.remoteUrl + "/item_id/" + id +'">'+ value +'</a></li>';
                        }
                    });
                    that.control
                        .attr('href', that.options.remoteUrl + "/item_id/" + xml.documentElement.getAttribute('owner'))
                        .data('value', xml.documentElement.getAttribute('owner'));
                }
                that.ul.html(htmlLi.join(''));
                that._selectItem(that.options.selected);
            });
        }
    }); 

    $.extend($.ui.treeselect, {
        locale: {
            loading: "Загрузка..."
        }
    });

})(jQuery);