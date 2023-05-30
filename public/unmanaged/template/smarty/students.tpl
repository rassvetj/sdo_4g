<form method="POST">
<input type="hidden" name="post_action" value="assign">
<table border=0 cellpadding=4 cellspacing=0 width=100%>
<tr>
    <td width=50% valign=top>
    <table width=100% class=main cellspacing=0>
        <tr>
            <th><input type="checkbox" id="select_all" onClick="select_all_items('person',this.checked);"></th><th>{?t?}ФИО{?/t?}</th><th>{?t?}Логин{?/t?}</th>
        </tr>
        {?if $this->model->students->list?}
            {?foreach from=$this->model->students->list item=s?}
        <tr>
            <td><input type="checkbox" id="person_{?$s.n?}" name="students[]" value="{?$s.MID?}"></td>
            <td>{?$s.LastName?} {?$s.FirstName?} {?$s.Patronymic?}</td>
            <td>{?$s.Login?}</td>
        </tr>
            {?/foreach?}
        {?/if?}
    </table>
    </td>
    <td valign=top>
    <table width=100% class=main cellspacing=0>
        <tr>
            <th><input type="checkbox" id="select_all" onClick="select_all_items('course',this.checked);"></th><th>{?t?}Курс{?/t?}</th>
        </tr>
        {?if $this->model->courses->list?}
            {?foreach from=$this->model->courses->list item=c?}
        <tr>
            <td><input type="checkbox" id="course_{?$c.n?}" name="courses[]" value="{?$c.CID?}"></td>
            <td>{?$c.Title?}</td>
        </tr>
            {?/foreach?}
        {?/if?}
    </table>
    </td>
</tr>
<tr>
    <td colspan=2>
    <table width=100% class=main cellspacing=0>
        <tr>
            <td>{?t?}Выполнить{?/t?}
                <select name="action">
                    <option value="assign"> {?t?}назначить на курсы{?/t?}</option>
                    <option value="delete"> {?t?}удалить с курсов{?/t?}</option>
                </select>
            </td>
            <td>{?$okbutton?}</td>
        </tr>
    </table>
    </td>
</tr>
</table>
</form>

{?literal?}
<script language="javascript" type="text/javascript">
<!--
    function select_all_items(elm_prefix,checked) {
        var i=1;
        elm = document.getElementById(elm_prefix+'_'+(i++));
        while (elm) {
            elm.checked = checked;
            elm = document.getElementById(elm_prefix+'_'+(i++));
        }
    }
//-->
</script>
{?/literal?}
