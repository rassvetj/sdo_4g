
<script type="text/javascript" language="JavaScript" src="{?$sitepath?}js/roles.js"></script>

<script type="text/javascript">
<!--

var assigned = new Array(), dropped = new Array();

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

    function showHideString(obj, str) {
        str = str ? str : '{?t?}введите часть имени или логина{?/t?}';
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
    }/*
    $(function() {
        showHideString($('#{?$editbox_search_name?}').get(0))
    });*/

{?$javascript?}
//-->
</script>

<table width=100% border=0>
	<tr>
		<td width=50% valign=top>
			{?$list1_title?}<br>
			<input type="button" value="{?t?}Все{?/t?}" onClick="{?$button_all_click?}">
			<input type="text" name="{?$editbox_search_name?}" id="{?$editbox_search_name?}" value="{?$editbox_search_text?}" style="width: 80%" onFocus="showHideString(this);" onBlur="showHideString(this);" onKeyUp="{?$editbox_search_keyup?}">
			<div id="{?$list1_container_id?}">
			<select size=10 id="{?$list1_name?}" name="{?$list1_name?}[]" multiple style="width:100%">
			{?$list1_options?}
			</select>
			</div>
            {?if $list3_options?}
                <br>
                <select id="{?$list3_name?}" name="{?$list3_name?}" style="width:100%" onChange="{?$list3_change?}">
                {?$list3_options?}
                </select>
            {?/if?}
		</td>
		<td valign=middle align=middle>
		    <input type="button" value=">>" onClick="select_list_move_extended('{?$list1_name?}','{?$list2_name?}','select_list_cmp_by_text',true); {?$list1_list2_click?}">
		    <input type="button" value="<<" onClick="select_list_move_extended('{?$list2_name?}','{?$list1_name?}','select_list_cmp_by_text',false); {?$list2_list1_click?}">
		</td>
		<td width=50% valign=top>
			{?$list2_title?}<br>
			<div id="{?$list2_container_id?}">
			<select size=10 id="{?$list2_name?}" name="{?$list2_name?}[]" multiple style="width: 100%">
			{?$list2_options?}
			</select>
			</div>
		</td>
	</tr>
</table>
