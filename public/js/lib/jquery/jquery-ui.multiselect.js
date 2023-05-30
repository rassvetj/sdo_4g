/*
 * jQuery UI Multiselect
 *
 * Authors:
 *  Michael Aufreiter (quasipartikel.at)
 *  Yanick Rochon (yanick.rochon[at]gmail[dot]com)
 * 
 * Dual licensed under the MIT (MIT-LICENSE.txt)
 * and GPL (GPL-LICENSE.txt) licenses.
 * 
 * http://www.quasipartikel.at/multiselect/
 *
 * 
 * Depends:
 *  ui.core.js
 *
 * Optional:
 * localization (http://plugins.jquery.com/project/localisation)
 * scrollTo (http://plugins.jquery.com/project/ScrollTo)
 * 
 * Todo:
 *  Make batch actions faster
 *  Implement dynamic insertion through remote calls
 */


(function($) {

var limit = function(func, wait, debounce) {
    var timeout;
    return function() {
        var context = this, args = arguments;
        var throttler = function() {
            timeout = null;
            func.apply(context, args);
        };
        if (debounce) clearTimeout(timeout);
        if (debounce || !timeout) timeout = setTimeout(throttler, wait);
    };
};
var dataParser = function (input) {
    var data = []
      , selected
      , line
      , lines 
      , pattern = /^(\s\n\r\t)*\+?$/
      , i;
    if ( typeof input == 'string' ) {
        lines = input.split(/\n/);
        for (i = 0; i < lines.length; i++) {
            line = lines[i].split("=");
            // make sure the key is not empty
            if (!pattern.test(line[0])) {
                selected = (line[0].lastIndexOf('+') == line[0].length - 1);
                if (selected) line[0] = line[0].substr(0,line[0].length-1);
                // if no value is specified, default to the key value
                data.push({
                    selected: selected,
                    value: line[1] || line[0],
                    key: line[0]
                });
            }
        }
    }
    return data;
};
var outers = function (c, horizontal, margins) {
    var props = horizontal ? ['left', 'right'] : ['top', 'bottom']
      , val = 0;
    
    val += parseFloat(c.css('padding-' + props[0]));
    val += parseFloat(c.css('padding-' + props[1]));
    val += parseFloat(c.css('border-' + props[0] + '-width'));
    val += parseFloat(c.css('border-' + props[1] + '-width'));
    if (margins) {
        val += parseFloat(c.css('margin-' + props[0]));
        val += parseFloat(c.css('margin-' + props[1]));
    }
    return val;
}

$.widget("ui.multiselect", {
    options: {
        searchable: true,
        doubleClickable: true,
        animated: 'fast',
        show: 'slideDown',
        hide: 'slideUp',
        dividerLocation: 0.5,
        disabled: false,
        onLoad: ''
    },
    _setOption: function(key, value) {
        var method = 'set' + key.charAt(0).toUpperCase() + key.substr(1);
        if (typeof this[method] === 'function') {
            this[method](value);
        }
        $.Widget.prototype._setOption.apply(this, arguments);
    },
    setDisabled: function(value) {
        if (!value) {
            this.$_selectedActionsAgain.show();
            this.$_availableActionsAgain.show();
            this.container.find('.connected-list .ui-element a.action').css({
                visibility: 'visible'
            });
        } else {
            this.$_selectedActionsAgain.hide();
            this.$_availableActionsAgain.hide();
            this.container.find('.connected-list .ui-element a.action').css({
                visibility: 'hidden'
            });
            this.container.find('.connected-list .ui-element').removeClass('ui-state-selected');
        }
    },
    _create: function() {
        var elementWidth = this.element.outerWidth()
          , elementHeight = this.element.outerHeight();
        
        this.treeStack = {};
        this.tree = [];
        this.treeIndex = {};
        this.selectedItems = {};
        
        this.element
            .addClass('ui-helper-hidden-accessible')
            .css({
                visibility: 'hidden',
                width: 1,
                height: 1
            });
        this.options.disabled = !!this.element.attr('disabled');
        this.id = this.element.attr("id");
        this.sOptions = this.element.find('option');
        this.count = 0; // number of currently selected options
        
        this.container = $('<div class="ui-multiselect ui-helper-clearfix ui-widget"></div>');
        this.availableContainer = $('<div class="available container"></div>').appendTo(this.container);
        this.selectedContainer = $('<div class="selected container"></div>').appendTo(this.container);
        var selectedActions = $('<div class="labels ui-widget-header ui-helper-clearfix"><span class="items-count-label">'+$.ui.multiselect.locale.itemsCount+'</span> <span class="items-count-value">(<span class="count">0</span>)</span></div>').appendTo(this.selectedContainer);
        this.$_selectedActions = selectedActions;
        var availableActions = $('<div class="labels ui-widget-header ui-helper-clearfix"><span class="all-label">'+$.ui.multiselect.locale.itemsAll+'</span> <input class="search empty ui-widget-content ui-corner-all"/></div>').appendTo(this.availableContainer);
        this.$_availableActions = availableActions;
        this.selectedList = $('<ul class="selected connected-list"></ul>').bind('selectstart', function(){return false;}).appendTo(this.selectedContainer);
        this.availableList = $('<ul class="available connected-list"></ul>').bind('selectstart', function(){return false;}).appendTo(this.availableContainer);
        
        var selectedActionsAgain = $('<div class="actions ui-widget-header ui-helper-clearfix"><a href="#" class="remove-all">'+$.ui.multiselect.locale.removeAll+'</a></div>').appendTo(this.selectedContainer);
        var availableActionsAgain = $('<div class="actions ui-widget-header ui-helper-clearfix"><a href="#" class="add-all">'+$.ui.multiselect.locale.addAll+'</a></div>').appendTo(this.availableContainer);
        this.$_selectedActionsAgain = selectedActionsAgain;
        this.$_availableActionsAgain = availableActionsAgain;
        
        this.container.insertAfter(this.element);

        var that = this;

        // set dimensions
        var containerHOuters = outers(this.container, true)
          , containerVOuters = outers(this.container, false)
          , selectedContainerHOuters = outers(this.selectedContainer, true, true)
          , availableContainerHOuters = outers(this.availableContainer, true, true)
          , selectedListHOuters = outers(this.selectedList, true, true)
          , availableListHOuters = outers(this.availableList, true, true)
          , selectedActionsHeight
          , availableActionsHeight
          , availableContainerWidth = Math.floor( (elementWidth - containerHOuters) * this.options.dividerLocation );
        
        this.container.width(elementWidth - containerHOuters);
        
        this.availableContainer.width(availableContainerWidth - availableContainerHOuters);
        this.selectedContainer.width(elementWidth - containerHOuters - availableContainerWidth - selectedContainerHOuters);

        selectedActionsHeight = selectedActions.outerHeight();
        availableActionsHeight = availableActions.outerHeight();

        this.selectedList.height(Math.max(elementHeight-containerVOuters-selectedActionsHeight,1));
        this.availableList.height(Math.max(elementHeight-containerVOuters-availableActionsHeight,1));
        
        this.availableList.width(availableContainerWidth - availableContainerHOuters - selectedListHOuters);
        this.selectedList.width(elementWidth - containerHOuters - availableContainerWidth - selectedContainerHOuters - selectedListHOuters);
        
        if ( !this.options.animated ) {
            this.options.show = 'show';
            this.options.hide = 'hide';
            this.options.animated = 0;
        }
        
        // init lists
        this._populateLists(this.sOptions);
        
        // set up event bindings
        // set up livesearch
        this.container
            .delegate('input.search', 'focus blur', function (event) {
                if (event.type == 'focus') {
                    $(event.currentTarget).addClass('ui-state-active');
                } else {
                    $(event.currentTarget).removeClass('ui-state-active');
                }
            })
            .delegate('input.search', 'keypress', function (event) {
                if (event.keyCode == 13) event.preventDefault();
            })
            .delegate('input.search', 'keyup change', limit(function (event) {
                if (!that.options.searchable) return;
                that._filter($(event.currentTarget).val());
            }, 1000, true));
        if (!this.options.searchable) {
            this.availableContainer.find('input.search').hide();
        }
        
        // batch actions
        this.container.delegate(".remove-all", "click", function (event) {
            that.unSelectAll();
            event.preventDefault();
        });
        this.container.delegate(".add-all", "click", function (event) {
            that.selectAll();
            event.preventDefault();
        });
        // dblclick
        this.container.delegate(".connected-list .ui-element", "dblclick", function (event) {

            event.preventDefault();
            
            that.onClick_item(event);
            
            if (!that.options.doubleClickable) {
                return;
            }
            $(event.currentTarget).find("a.action").click();
        });
        this.container.delegate(".connected-list .ui-element", "click", function (event) {
            that.onClick_item(event);
        });
        this.container.delegate(".connected-list .ui-element a.action", "click", function(event) {
            that.onClick_itemsAdd(event);
        });
        
        var options = this.options;
        
        if (this.options.remoteUrl) {
            $.get(this.options.remoteUrl, function (data) {
                var items = dataParser(data)
                  , html = [];
                for (var i = 0; i < items.length; ++i) {
                    html.push('<option '+ (items[i].selected ? 'selected' : '') +' value="'+ items[i].key +'">'+ items[i].value +'</option>');
                }
                that.element.html(html.join(''));
                that.sOptions = that.element.find('option');
                that._populateLists(that.sOptions);
                eval(options.onLoad);
            });
        }
        
        this.setDisabled(this.options.disabled);
    },
    onClick_item: function(event) {
        if (this.options.disabled || event.which != 1) {
            return;
        }
        if ($(event.target).hasClass('ui-element')) {
            $(event.currentTarget).toggleClass('ui-state-selected');
        }
    },
    onClick_itemsAdd: function (event) {

        event.preventDefault();
        
        if (this.options.disabled) {
            return;
        }
        
        var $target = $(event.currentTarget);

        $target.closest('.ui-element').addClass('ui-state-selected');
        
        var $list = $target.closest('.connected-list'),
            $items = $list.find('.ui-state-selected').parent(),
            willBeSelected = $list.is('.available'),
            me = this;
        
        $items.each(function () {
            me._setSelected($(this).data('idx'), willBeSelected);
        });
        
        willBeSelected
            ? (this.count += $items.length)
            : (this.count -= $items.length);
        
        this._updateCount();
    },
    destroy: function() {
        this.element.show();
        this.container.remove();

        $.Widget.prototype.destroy.apply(this, arguments);
    },
    selectAll: function() {
        for (var i = 0; i < this.tree.length; i++) {
            this._setSelected(this.tree[i].id, true);
        }
	this.count = this.tree.length;	//[che 20.05.2014 #16837]
	this._updateCount(); 		//[che 20.05.2014 #16837]
    },
    unSelectAll: function() {
        for (var i = 0; i < this.tree.length; i++) {
            this._setSelected(this.tree[i].id, false);
        }
	this.count = 0;  	//[che 20.05.2014 #16837]
	this._updateCount();  	//[che 20.05.2014 #16837]
    },
    _populateLists: function(options) {
        var item
          , option
          , $option
          , selectedParent = this.selectedList.parent()
          , selectedNext = this.selectedList.next()
          , availableParent = this.availableList.parent()
          , availableNext = this.availableList.next()
          , rawSelectedList = document.createDocumentFragment()
          , rawAvailableList = document.createDocumentFragment();

        this.count = 0;
        this.previousFilter = '';

        this.selectedList.detach();
        this.availableList.detach();
        
        this.selectedList.children('.ui-element').remove();
        this.availableList.children('.ui-element').remove();

        var that = this,
            treeItem,
            selected = [];
        
        for (var i = 0, length = options.length; i < length; ++i) {
            $option = options.eq(i);
            option = options.get(i);
            treeItem = this._getOptionNode($option, i);
            if (option.selected) {
                selected.push(i);
            }
            if (option.selected) {
                this.count += 1;
            }
        }
        
        for (var i = 0; i < this.tree.length; i++) {
            rawAvailableList.appendChild(this.tree[i].el);
        }
        
        for (var i = 0; i < selected.length; i++) {
            this._setSelected(selected[i], true);
        }
        
        this.selectedList.append(rawSelectedList);
        this.availableList.append(rawAvailableList);

        this._filter(this.availableContainer.find('input.search').val());
        
        if (selectedNext.length) {
            this.selectedList.insertBefore(selectedNext);
        } else {
            this.selectedList.appendTo(selectedParent);
        }
        if (availableNext.length) {
            this.availableList.insertBefore(availableNext);
        } else {
            this.availableList.appendTo(availableParent);
        }
        // update count
        this._updateCount();
    },
    _updateCount: function() {

	//[che 20.05.2014 #16837] или как вариант решения => this.selectedContainer.find('span.count').text(this.selectedList.find('li').length);
        this.selectedContainer.find('span.count').text(this.count);
    },
    _getOptionNode: function(option, idx) {
        var optionText = option.text()
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/'/g, "&#39;");

        var match = optionText.match(/^(\-*)\s*(.*)$/m),
            level = match[1].length,
            title = match[2];

        var li = document.createElement('li'),
            container = document.createElement('div'),
            icon = document.createElement('span'),
            caption = document.createTextNode(title),
            a = document.createElement('a'),
            aCorners = document.createElement('span'),
            treeItem;
                
        if (level) {
            
            var parent = this.treeStack[level - 1],
                parentUl = parent.el.lastChild;

            if (parentUl.nodeName !== 'UL') {
                
                parentUl = document.createElement('UL');
                parent.el.className += ' hm-selectmenu-folder';
                
                var folderIcon = document.createElement('span');
                folderIcon.className = 'hm-selectmenu-folder-icon';

                var me = this;
                
                $(folderIcon).on('click', function() {
                    $(parent.el).toggleClass('hm-selectmenu-folder-opened');
                });
                
                parent.el.appendChild(folderIcon);
                parent.el.appendChild(parentUl);
            }

            parentUl.appendChild(li);
            
            treeItem = {
                id: idx,
                el: li,
                $el: $(li),
                title: optionText,
                children: [],
                parent: parent,
                selected: false
            };

            this.treeStack[level] = treeItem;
            this.treeIndex[idx] = treeItem;
            parent.children.push(treeItem);
            
        } else {

            treeItem = {
                id: idx,
                el: li,
                $el: $(li),
                title: optionText,
                children: [],
                parent: null,
                selected: false
            };
            
            this.treeStack = {
                0: treeItem
            };
            this.tree.push(treeItem);
            this.treeIndex[idx] = treeItem;
        }

        container.className = 'ui-state-default ui-element';
        container.setAttribute('title', title);
        container.setAttribute('data-idx', idx);
        li.setAttribute('data-idx', idx);

        icon.className = 'ui-icon';
        
        a.href = '#';
        a.className = 'action';
        if (this.options.disabled) {
            a.style.visibility = 'hidden';
        }

        aCorners.className = 'ui-corner-all ui-icon';
        a.appendChild(aCorners);

        container.appendChild(icon);
        container.appendChild(caption);
        container.appendChild(a);
        li.appendChild(container);
        
        return treeItem;
    },
    _setSelected: function(idx, selected) {

        selected = !!selected;

        var treeItem = this.treeIndex[idx];
        
        var $option = $(this.sOptions[idx]),
            $list = selected ? this.selectedList : this.availableList;
        
        if (selected) {
            this.selectedItems[idx] = treeItem;
            $(treeItem.el).show();
        } else {
            if (treeItem.parent && treeItem.parent.selected) {
                alert('Нельзя снимать выделение с элемента, родитель которого остается выделенным')
                return;
            }
            delete this.selectedItems[idx];
        }

        $option[0].selected = selected;

        treeItem.selected = !!selected;
        
        var $el = $(treeItem.el);
        
        if (treeItem.parent && treeItem.selected == treeItem.parent.selected) {
            treeItem.parent.el.lastChild.appendChild(treeItem.el);
            $el.parents('.hm-selectmenu-folder').addClass('hm-selectmenu-folder-opened');
            
        } else {
            $list.append(treeItem.el);
        }

        for (var i = 0; i < treeItem.children.length; i++) {
            this._setSelected(treeItem.children[i].id, selected);
        }
        
        if (treeItem.children.length) {
            $el.addClass('hm-selectmenu-folder-opened');
        }
        
        $el.find('.ui-element').removeClass('ui-state-selected');
    },
    _filter: function(term) {
        for (var i = 0; i < this.tree.length; i++) {
            this.filterItem(this.tree[i], term);
        } 
    },
    /**
     * Вернет true, если элемент или его детеныш подошёл к фильтру
     * @param treeItem
     * @param term
     */
    filterItem: function(treeItem, term) {
        var result = false;
        
        if (treeItem.selected || treeItem.title.indexOf(term) !== -1) {
            result = true;
        }
        
        for (var i = 0; i < treeItem.children.length; i++) {
            if (this.filterItem(treeItem.children[i], term)) {
                result = true;
            }
        }
        
        if (result) {
            $(treeItem.el).show();
        } else {
            $(treeItem.el).hide();
        }
        
        return result;
    }
});
        
$.extend($.ui.multiselect, {
    locale: {
        addAll:'Add all',
        removeAll:'Remove all',
        itemsCount:'Selected',
        itemsAll: 'All'
    }
});


})(jQuery);