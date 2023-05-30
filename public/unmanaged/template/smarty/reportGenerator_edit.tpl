<script type="text/javascript">
    $(function(){
       $("#dataSource").bind("change", function(event) {
        //if (event.target && $(event.target).is("option")) {            
            $("#action").val("refresh");
            $("#form").submit();
        //}        
        }); 
    });
</script>
<form action="" method="POST" id='form'>
<input type="hidden" id="action" name="action" value="update" />
{?if $id?}
    <input type="hidden" name="data[id]" value="{?$id?}" />
{?/if?}
<table width=100% class=main cellspacing=0>
<tr>
    <th colspan='2'>    
        {?if $id?}
            {?t?}Редактирование шаблона отчёта{?/t?}
        {?else?}
            {?t?}Добавление шаблона отчёта{?/t?}
        {?/if?}
    </th>
</tr>
<tr>
    <td>{?t?}Название шаблона{?/t?}:</td>
    <td>
        <input type="text" id = 'template_name' name="data[template_name]" size="47" value="{?$data.template_name?}"/>        
    </td>
</tr>
<tr>
    <td>{?t?}Источник данных{?/t?}:</td>
    <td>
        {?if $reports?}
            <select name='data[report_name]' id='dataSource'>
            {?foreach from=$reports key=name item=title?}
                <option value="{?$name?}" 
		          {?if $name==$data.report_name?}
		              selected = 'selected'
		          {?/if?}
		        >
                    {?$title?}
                </option>
            {?/foreach?}
            </select>
        {?/if?}            
    </td>
</tr>
<tr>
    <td>
        {?t?}Шаблон{?/t?}:
    </td>
    <td>
        {?$data.template?}
    </td>
</tr>
<tr>
    <td colspan="99" align="right">        
        <table border="0">
            <tr>                
                <td>{?$okbutton?}</td>
                <td>{?$cancelbutton?}</td>
            </tr>
        </table>
    </td>
</tr>
</table>
</form>