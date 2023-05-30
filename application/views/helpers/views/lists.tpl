<?php
$this->inlineScript()->captureStart();
?>
// todo: refactoring + добавить возможность поместить на одну страницу несколько элементов
var assigned = new Array(), dropped = new Array();

select_list_select_all = function(elm) {
    var cats = document.getElementById(elm);
    for(var j=0;j<cats.options.length;j++) {
        cats.options[j].selected = true;
    }
}

select_list_cmp_by_text = function(a,b) {
    if (a.text < b.text) return -1;
    if (a.text > b.text) return 1;
    return 0;
}

select_list_cmp_by_value = function(a,b) {
    if (a.value < b.value) return -1;
    if (a.value > b.value) return 1;
    return 0;
}

in_array = function(value, arr) {
    for(var i=0; i<arr.length;i++) {
        if (value == arr[i].value) return true;
    }
    return false;
}

drop_value = function(value, arr) {
    var ret = new Array();
    for(var i=0;i<arr.length;i++) {
        if (arr[i].value == value) continue;
        ret[ret.length] = arr[i];
    }
    return ret;
}

prepare_options = function(list, assign, drop, sort_func) {
    var elm, arr = new Array(), doSort = false, i;

    if (typeof(sort_func) == 'undefined') sort_func = 'select_list_cmp_by_text';

    if (elm = document.getElementById(list)) {

        for(i=0;i<elm.length;i++) {
            arr[i] = elm.options[i];
        }

        for(i=0;i<assign.length;i++) {
            if (!in_array(assign[i].value,arr)) {
                arr[arr.length] = assign[i];
            }
            doSort = true;
        }

        for(i=0;i<drop.length;i++) {
            arr = drop_value(drop[i].value, arr);
            doSort = true;
        }

        if (doSort) {
            eval("arr.sort( "+sort_func+");");
        }

        elm.length = 0;
        for(i=0;i<arr.length;i++) {
            elm.options[elm.length] = arr[i];
        }
    }

}

assign_option = function(option, assign) {
    if (assign) {
        if (!in_array(option.value, assigned)) {
            assigned[assigned.length] = option;
        }
        dropped = drop_value(option.value,dropped);
    } else {
        if (!in_array(option.value, dropped)) {
            dropped[dropped.length] = option;
        }
        assigned = drop_value(option.value,assigned);
    }
}

select_list_move_extended = function(elm1, elm2, sort_func, assign) {
    var list1 = document.getElementById(elm1);
    var list2 = document.getElementById(elm2);

    var arr1 = new Array(), arr2 = new Array();

    if (list1 && list2) {
        var obj, obj2, i;
        for(i=0; i<list1.length; ++i) {
            obj = list1.options[i];
            obj2 = new Option(obj.text, obj.value);
            obj2.parent = obj.parent;
            obj2.style.background = obj.style.background;
            obj2.dontmove = obj.dontmove;
            if(obj.selected && (obj.dontmove!='dontmove'))  {

                assign_option(obj2, assign);

                arr2[ arr2.length ] = obj2;
                if (obj.parent=='true') {
                    i++;
                    while(i<list1.length) {
                        obj = list1.options[i];
                        if (obj.parent=='false') {
                            obj2 = new Option(obj.text, obj.value);
                            obj2.parent = obj.parent;
                            obj2.style.background = obj.style.background;
                            obj2.dontmove = obj.dontmove;

                            assign_option(obj2, assign);

                            arr2[arr2.length] = obj2;
                        } else break;
                        i++;
                    }
                    i--;
                }
            }
            else
            arr1[ arr1.length ] = obj2;
        }

        for(i=0;i<list2.length;++i) {
            obj = list2.options[i];
            obj2 = new Option(obj.text, obj.value);
            obj2.parent = obj.parent;
            obj2.style.background = obj.style.background;
            obj2.dontmove = obj.dontmove;
            arr2[ arr2.length ] = obj2;
        }

        eval("arr2.sort( "+sort_func+");");

        list2.length = list1.length = 0;

        for(i=0; i<arr1.length; i++)
        list1.options[ list1.length ] = arr1[i];
        for(i=0; i<arr2.length; i++)
        list2.options[ list2.length ] = arr2[i];
    }
}

function get_list1_options(str) {

    var elm = document.getElementById('<?php echo $this->list1['name']?>_container');
    if (elm) elm.innerHTML = '<select size=10 id="<?php echo $this->list1['name']?>" name="<?php echo $this->list1['name']?>[]" multiple style="width:100%"><option> <?php echo _('Загружаю данные...')?></option></select>';

    get_list2_options('');

    $.post('<?php echo (isset($this->list1['url']) ? $this->list1['url'] : $this->url(array('action' => 'list1-options')))?>', {searchString: str}, function(resp) {
        var elm = document.getElementById('<?php echo $this->list1['name']?>_container');
        if (elm) elm.innerHTML = '<select size=10 id="<?php echo $this->list1['name']?>" name="<?php echo $this->list1['name']?>[]" multiple style="width:100%">'+resp+'</select>';
        prepare_options('<?php echo $this->list1['name']?>', dropped, assigned);
        }, 'html'
  	);
}

function get_list2_options(str) {
    var elm = document.getElementById('<?php echo $this->list2['name']?>_container');
    if (elm) elm.innerHTML = '<select size=10 id="<?php echo $this->list2['name']?>" name="<?php echo $this->list2['name']?>[]" multiple style="width: 100%"><option> <?php echo _('Загружаю данные...')?></option></select>';

    $.post('<?php echo (isset($this->list2['url']) ? $this->list2['url'] : $this->url(array('action' => 'list2-options')))?>', {searchString: str}, function(resp) {
        var elm = document.getElementById('<?php echo $this->list2['name']?>_container');
        if (elm) elm.innerHTML = '<select size=10 id="<?php echo $this->list2['name']?>" name="<?php echo $this->list2['name']?>[]" multiple style="width:100%">'+resp+'</select>';
        prepare_options('<?php echo $this->list2['name']?>', assigned, dropped);
        }, 'html'
  	);
}

showHideString = function(obj, str) {
    str = str ? str : '<?php echo _('Введите часть имени или логина')?>';
    if (obj.value == str) {
        obj.value = '';
        obj.style.fontStyle = 'normal';
        obj.style.color = 'black';
    }else {
        if (!obj.value) {
            obj.style.fontStyle = 'italic';
            obj.style.color = 'grey';
            obj.value = str;
        }
    }
}

jQuery(function() {
    showHideString($('#<?php echo $this->list1['name'].'_search'?>').get(0))
});
<?php
$this->inlineScript()->captureEnd();
?>
<table width=100% border=0>
	<tr>
		<td width=50% valign=top>
			<?php echo $this->escape($this->list1['title'])?><br>
			<input type="button" value="<?php echo _('Все')?>" onClick="if (elm = document.getElementById('<?php echo $this->list1['name'].'_search'?>')) elm.value='*'; get_list1_options('*');"">
			<input type="text" name="<?php echo $this->list1['name']?>_search" id="<?php echo $this->list1['name']?>_search" value="" style="width: 80%" onFocus="showHideString(this);" onBlur="showHideString(this);" onKeyUp="if (typeof(filter_timeout)!='undefined') clearTimeout(filter_timeout); filter_timeout = setTimeout('get_list1_options(\''+this.value+'\');',1000);">
			<div id="<?php echo $this->list1['name']?>_container">
			<select size=10 id="<?php echo $this->list1['name']?>" name="<?php echo $this->list1['name']?>[]" multiple style="width:100%">
			<?php echo $this->list1['options']?>
			</select>
			</div>
		</td>
		<td valign=middle align=middle>
		    <input type="button" value=">>" onClick="select_list_move_extended('<?php echo $this->list1['name']?>','<?php echo $this->list2['name']?>','select_list_cmp_by_text',true); <?php echo $this->list1['click']?>">
		    <input type="button" value="<<" onClick="select_list_move_extended('<?php echo $this->list2['name']?>','<?php echo $this->list1['name']?>','select_list_cmp_by_text',false); <?php echo $this->list2['click']?>">
		</td>
		<td width=50% valign=top>
			<?php echo $this->escape($this->list2['title'])?><br>
			<div id="<?php echo $this->list2['name']?>_container">
			<select size=10 id="<?php echo $this->list2['name']?>" name="<?php echo $this->list2['name']?>[]" multiple style="width: 100%">
			<?php echo $this->list2['options']?>
			</select>
			</div>
		</td>
	</tr>
</table>