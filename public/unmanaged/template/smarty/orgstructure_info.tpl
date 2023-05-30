<script type="text/javascript" language="JavaScript">
<!--
    {?$sajaxJavaScript?}
    function show_checked_items(html) {
        var elm = document.getElementById('checkedItems');
        if (elm) elm.innerHTML = html;
    }
        
    function get_checked_items() {   
        x_getCheckedItems(show_checked_items);
    }
            
    function uncheck_items() {   
        x_unCheckItems();
    }
    
    function getCheckedItems() {
        setTimeout('get_checked_items()', 1000);
    }
//-->
</script>
{?if $id?}
    <table border=0 cellpadding=0 cellspacing=0 class="card-person-trans">
        <tr>
            <td valign=top>
            {?$itemCard?}
            </td>
            <td valign=top style="padding: 10px; padding-top: 0px;">
            <p><a title="{?t?}редактировать{?/t?}" href="{?$sitepath?}orgstructure_main.php?id={?$id?}"><img border=0 src="{?$sitepath?}images/icons/edit.gif"></a><br>
            <p><a title="{?t?}удалить{?/t?}" href="{?$sitepath?}orgstructure_main.php?id={?$id?}&action=delete" onClick="if (confirm('{?t?}Удалить элемент структуры?{?/t?}')) return true; return false;"><img border=0 src="{?$sitepath?}images/icons/delete.gif"></a><br>
            {?if $type == 2?}
            <p><a title="{?t?}добавить подэлемент{?/t?}" href="{?$sitepath?}orgstructure_main.php?id={?$id?}&type=add"><img border=0 src="{?$skin_url?}/images/add_structure_item.gif"></a>
            {?/if?}            
            </td>
        </tr>
    </table>
{?/if?} 

<br>
<div id="checkedItems">
    {?$checked_items?}
</div>