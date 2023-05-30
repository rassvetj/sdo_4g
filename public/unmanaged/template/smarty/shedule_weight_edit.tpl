<form action="" method="POST">

{?if $action=='add'?}
    <input type="hidden" name="post" value="add">
{?else?}
    <input type="hidden" name="post" value="edit">
{?/if?}
<input type="hidden" name="id" value="{?$weight.id?}">
<input type="hidden" name="cid" value="{?$course.id?}">
<table width=100% class=main cellspacing=0>
<!--tr>
    <th colspan=2>
{?if $action=='add'?}{?t?}Создание{?/t?}{?else?}{?t?}Редактирование{?/t?}{?/if?}
    </th>
</tr-->
<tr>
    <td>{?t?}Тип занятия:{?/t?} </td>
    <td>{?$weight.name?}</td>
</tr>
<tr>
    <td>{?t?}Курс:{?/t?} </td>
    <td>{?$course.title?}</td>
</tr>
<tr>
    <td colspan="2"><input type="checkbox" name="ch_disabled" value="1" {?if $weight.disabled?}checked{?/if?}> {?t?}тип занятия не используется на данном курсе{?/t?}</td>
</tr>
<!--tr>
    <td>{?t?}Вес типа занятия на курсе:{?/t?} </td>
    <td><input type="text" name="weight" id="txt_weight" value="{?$weight.weight?}" style="width:30px;" {?if $weight.disabled?}disabled{?/if?}></td>
</tr-->
</table>
<table border="0" align="right">
    <tr>        
        <td>{?$cancelbutton?}</td>
        <td>{?$okbutton?}</td>
    </tr>
</table>
</form>