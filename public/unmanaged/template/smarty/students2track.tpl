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

{?$sajax_javascript?}
//-->
</script>
<form method="POST" onSubmit="select_list_select_all('users'); select_list_select_all('all_users');">
<input type="hidden" name="action" value="assign">
<input type="hidden" name="trid" value="{?$trid?}">
<table width=100% border=0 cellpadding=2 cellspacing=1>
    <tr>
    <td width=50% valign=top>
    {?t?}{?php?}echo CObject::toUpperFirst("слушатели");{?/php?}{?/t?}:<br>
    <input type="button" value="{?t?}Все{?/t?}" style="width: 10%" onClick="if (elm = document.getElementById('search_people')) elm.value='*'; get_user_select('*');">
    <input type="text" name="search_people" id="search_people" value="{?$search?}" style="width: 88%" onKeyUp="if (typeof(filter_timeout)!='undefined') clearTimeout(filter_timeout); filter_timeout = setTimeout('get_user_select(\''+this.value+'\');',1000);">
    <br>
    <div id="people">
    <select size=10 id="all_users" name="del_users[]" multiple style="width:100%">
    {?$all_people?}
    </select>
    </div>
    </td>
    <td valign=middle align=middle>
        <input type="button" value=">>" onClick="select_list_move_extended('all_users','users','select_list_cmp_by_text',true);">
        <input type="button" value="<<" onClick="select_list_move_extended('users','all_users','select_list_cmp_by_text',false);">
    </td>
    <td width=50% valign=top>
    {?t?}{?php?}echo CObject::toUpperFirst("претенденты");{?/php?} на специальности{?/t?}{?*if $trid?} ({?$count_users?}/{?$count_pretendents?}){?/if*?}:
    <div id="people_used">
    <select size=10 id="users" name="need_users[]" multiple style="width: 100%">
    {?$users?}
    </select>
    </div>
    </td></tr>
</table>
{?$okbutton?}
</form>
