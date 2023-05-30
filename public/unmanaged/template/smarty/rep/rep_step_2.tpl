{?php?}
echo show_tb();
{?/php?}

{?$header?}

{?php?}
$GLOBALS['controller']->captureFromOb(CONTENT);
$GLOBALS['controller']->setHeader(_("Отчет")." &laquo;" . $GLOBALS['reports'][$GLOBALS['s']['reports']['current']['name']]['title'] . "&raquo;");
if ($GLOBALS['controller']->enabled) {
    if (!$GLOBALS['commonDataFields'] && !$GLOBALS['reportInput']) refresh("{$GLOBALS['sitepath']}rep.php?type={$GLOBALS['s']['reports']['current']['type']}&step=3");
}

$this->_tpl_vars['nextStep'] = 3;
//Кастыль для перепрынивания ненужного для пользовательских отчётов третьего шага
if ($_SESSION['s']['reports']['current']['type'] == 6){
    $this->_tpl_vars['nextStep'] = 4;
}
{?/php?}

<script type="text/javascript" src="{?$sitepath?}js/datepicker.js"></script>
<script type="text/javascript">
<!--
    $.datePicker.setDateFormat('dmy','.');
    $.datePicker.setLanguageStrings(
        ['{?t?}воскресенье{?/t?}', '{?t?}понедельник{?/t?}', '{?t?}вторник{?/t?}', '{?t?}среда{?/t?}', '{?t?}четверг{?/t?}', '{?t?}пятница{?/t?}', '{?t?}суббота{?/t?}'],
        ['{?t?}Январь{?/t?}', '{?t?}Февраль{?/t?}', '{?t?}Март{?/t?}', '{?t?}Апрель{?/t?}', '{?t?}Май{?/t?}', '{?t?}Июнь{?/t?}', '{?t?}Июль{?/t?}', '{?t?}Август{?/t?}', '{?t?}Сентябрь{?/t?}', '{?t?}Октябрь{?/t?}', '{?t?}Ноябрь{?/t?}', '{?t?}Декабрь{?/t?}'],
        {p:'{?t?}Пред{?/t?}', n:'{?t?}След{?/t?}', c:'X', b:'{?t?}Выберите дату{?/t?}'}
    );
//-->
</script>
<style type="text/css">
    @import url("{?$sitepath?}js/datepicker.css");
</style>

<form action="{?$sitepath?}rep.php?type={?$s.reports.current.type?}&step={?$nextStep?}" {?if $nextStep==4?} target = "_blank" {?/if?} method="POST" {?if $inputFields?}onSubmit="{?foreach from=$inputFields item=field key=key?}{?if $field.presentation == 'multi_select'?}select_list_select_all('ms2_{?$key?}');{?/if?}{?/foreach?}return true;"{?/if?}>
<input type="hidden" name="laststep" value="2">

{?if $sajax_javascript?}
<script type="text/javascript">
<!--
{?$sajax_javascript?}
//-->
</script>
{?/if?}

{?if $inputFields?}
<table width=100% class=main cellspacing=0>
    {?if $commonDataFields?}
    <tr><th colspan=2 class="intermediate">{?t?}Входные данные для отчета{?/t?}</th></tr>
    {?/if?}
{?foreach from=$inputFields item=field key=key?}
<tr>
    <td>
    {?$field.name?}
    </td>
    <td>
    {?if $field.presentation == 'structure_select'?}
        {?assign var="key_parent" value=$key|cat:'_parent'?}
        {?include file="control_treeselect.tpl" list_name="`$key`" list_extra='style="width: 300px;"' list_default_value="`$inputData.$key_parent`" list_selected="'`$inputData.$key`'" container_name="container_`$key`" url="`$sitepath`structure.php"?}
    {?/if?}
    {?if $field.presentation == 'string'?}
        <input type="text" name="{?$key?}" value="{?if $inputData.$key?}{?$inputData.$key?}{?/if?}">
    {?/if?}
    {?if $field.presentation == 'filtered_select'?}
        <script type="text/javascript">
        <!--
	    function show_select_{?$key?}(html) {
	        var elm = document.getElementById('div_{?$key?}');
	        if (elm) elm.innerHTML = "<select style=\"width: 300px;\" id=\"select_{?$key?}\" name=\"{?$key?}\" {?if $field.dependent?}onChange=\"document.location.href='{?$sitepath?}rep.php?type={?$s.reports.current.type?}&step=2{?foreach from=$inputFields item=f key=k?}{?if $k != $key && $inputData.$k?}&{?$k?}={?$inputData.$k?}{?/if?}{?/foreach?}&{?$key?}='+this.value+'&filtered_select='+document.getElementById('select_filter_{?$key?}').value\"{?/if?}>"+html+"</select>";
	        if (global_refresh && (elm = document.getElementById('select_{?$key?}'))) {
	        	if (elm.value!=last_value_{?$key?}) {
	        	    elm.onchange();
	        	}
	        }
	    }

	    function get_select_{?$key?}(str, refresh) {
	    	global_refresh = refresh;
	    	if (typeof(global_refresh)=='undefined') global_refresh = false;

            var elm;
	    	if (elm = document.getElementById('select_{?$key?}')) {
            	last_value_{?$key?} = elm.value;
            }

	        elm = document.getElementById('div_{?$key?}');
	        if (elm) elm.innerHTML = '<select style=\"width: 300px;\"><option>{?t?}Загружаю данные...{?/t?}</option></select>';

	        x_process_filter_{?$key?}(str, {?if $field.dependent?}'{?$inputData.$key?}'{?else?}last_value_{?$key?}{?/if?},show_select_{?$key?});

	    }
        //-->
        </script>
	    <input type="button" style="width: 45px;" value="{?t?}Все{?/t?}" onClick="if (elm = document.getElementById('select_filter_{?$key?}')) elm.value='*'; get_select_{?$key?}('*',true);">
	    <input style="width: 250px;" type="text" id="select_filter_{?$key?}" value="{?php?}echo $_GET['filtered_select'];{?/php?}" onKeyUp="if (typeof(filter_timeout)!='undefined') clearTimeout(filter_timeout); filter_timeout = setTimeout('get_select_{?$key?}(\''+this.value+'\',true);',1000);">
	    <div id="div_{?$key?}">
        <select style="width: 300px;" id="select_{?$key?}" name="{?$key?}" {?if $field.dependent?}onChange="document.location.href='{?$sitepath?}rep.php?type={?$s.reports.current.type?}&step=2{?foreach from=$inputFields item=f key=k?}{?if $k != $key && $inputData.$k?}&{?$k?}={?$inputData.$k?}{?/if?}{?/foreach?}&{?$key?}='+this.value+'&filtered_select='+document.getElementById('select_filter_{?$key?}').value"{?/if?}>
        </select>
        </div>
        <script type="text/javascript">
        <!--
        $(function() {get_select_{?$key?}(document.getElementById('select_filter_{?$key?}').value)});
        //-->
        </script>
    <br>
    {?/if?}
    {?if $field.presentation == 'select'?}
        <select name="{?$key?}" {?if $field.dependent?}onChange="document.location.href='{?$sitepath?}rep.php?type={?$s.reports.current.type?}&step=2{?foreach from=$inputFields item=f key=k?}{?if $k != $key && $inputData.$k?}&{?$k?}={?$inputData.$k?}{?/if?}{?/foreach?}&{?$key?}='+this.value"{?/if?}>
            {?foreach from=$inputDataForm.$key item=name key=v?}
            <option value="{?$v?}" {?if $inputData.$key == $v?}selected{?/if?}> {?$name?}</option>
            {?/foreach?}
        </select>
    {?/if?}
    {?if $field.presentation == 'date'?}
        <input type="text" id="report_{?$key?}" name="{?$key?}" value="{?if $inputData.$key?}{?$inputData.$key?}{?/if?}"> {?$field.format?}
        <script type="text/javascript">
        <!--
        $('#report_{?$key?}').datePicker({startDate:'01/01/2000', firstDayOfWeek:1});
        //-->
        </script>
    {?/if?}
    
    {?if $field.presentation == 'multi_select'?}
        <script type="text/javascript">
            <!--
        	function select_list_select_all(elm) {
                var cats = document.getElementById(elm);
                for(var j=0;j<cats.options.length;j++) {
                    cats.options[j].selected = true;
                }
            }
            
            function select_list_cmp_by_value(a,b) {
                if (a.text < b.text) return -1;                
                if (a.text > b.text) return 1;                
                return 0;
            }
        
            function select_list_move(elm1,elm2,sort_func) {
        
                var list1 = document.getElementById(elm1);
                var list2 = document.getElementById(elm2);
                
                var arr1 = new Array(), arr2 = new Array();
        
                var obj, obj2, i;
                for(i=0; i<list1.length; ++i) {
                    obj = list1.options[i];
                    obj2 = new Option(obj.text, obj.value);
                    obj2.parent = obj.parent;
                    obj2.style.background = obj.style.background;
                    obj2.label = obj.label;
                    if(obj.selected && (obj.label!='dontmove'))  {
                        arr2[ arr2.length ] = obj2;
                        if (obj.parent=='true') {
                            i++;
                            while(i<list1.length) {
                                obj = list1.options[i];
                                if (obj.parent=='false') {
                                    obj2 = new Option(obj.text, obj.value);
                                    obj2.parent = obj.parent;
                                    obj2.style.background = obj.style.background;
                                    obj2.label = obj.label;
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
                    obj2.label = obj.label;
                    arr2[ arr2.length ] = obj2;
                }
        
                eval("arr2.sort( "+sort_func+");");
        //        arr2.sort( select_list_cmp_by_text );
        
                list2.length = list1.length = 0;
        
                for(i=0; i<arr1.length; i++)
                list1.options[ list1.length ] = arr1[i];
                for(i=0; i<arr2.length; i++)
                list2.options[ list2.length ] = arr2[i];    
                select_list_select_all(elm1);
                select_list_select_all(elm2);
            }
            //-->
        </script>
	    <table width=100% border=0 cellpadding=0 cellspacing=0>
            <tr>
                <td width=50%>
                    {?t?}Все{?/t?}
                    <select size=10 id="ms1_{?$key?}" name="ms1_{?$key?}" multiple style="width:100%">
                    {?if $inputDataForm.$key?}
                    {?foreach from=$inputDataForm.$key key=k item=v?}
                        <option value="{?$k?}"> {?$v?}</options>
                    {?/foreach?}
                    {?/if?}
                    </select>
                    </td>
                    <td valign=middle align=middle>
                        <input type="button" value=">>" onClick="select_list_move('ms1_{?$key?}','ms2_{?$key?}','select_list_cmp_by_value');">
                        <input type="button" value="<<" onClick="select_list_move('ms2_{?$key?}', 'ms1_{?$key?}','select_list_cmp_by_value');">
                    </td>
                    <td width=50%>
                    {?t?}Необходимые{?/t?}
                    <select size=10 id="ms2_{?$key?}" name="{?$key?}[]" multiple style="width: 100%" {?if $field.dependent?}onChange="document.location.href='{?$sitepath?}rep.php?type={?$s.reports.current.type?}&step=2{?foreach from=$inputFields item=f key=k?}{?if $k != $key && $inputData.$k?}&{?$k?}={?$inputData.$k?}{?/if?}{?/foreach?}&{?$key?}='+this.value"{?/if?}>
                    {?if $inputData.$key?}
                    {?foreach from=$inputData.$key key=k item=v?}
                        <option value="{?$k?}"> {?$v?}</options>
                    {?/foreach?}
                    {?/if?}
                    </select>
                </td>
            </tr>
            </table>
    {?/if?}
    
    </td>
</tr>
{?/foreach?}
</table>
{?/if?}

{?if $html?}
<table width=100% class=main cellspacing=0>
<tr><th colspan=2>{?t?}Введите данные для отчета{?/t?}</th></tr>
{?$html?}
</table>
{?/if?}

{?if $commonDataFields?}
<table width=100% class=main cellspacing=0>
<tr><th colspan="2" class="intermediate">{?t?}Необходимые вычисления{?/t?}</th></tr>
{?foreach from=$commonDataFields item=i key=k?}
<tr>
    <td>{?$i?}</td>
    <td>
    <input type="checkbox" {?if $commonCalcFields && $commonCalcFields.max.$k?}checked{?/if?} name="commonCalc[max][{?$k?}]" value="do"> {?t?}максимум{?/t?}
    <input type="checkbox" {?if $commonCalcFields && $commonCalcFields.min.$k?}checked{?/if?} name="commonCalc[min][{?$k?}]" value="do"> {?t?}минимум{?/t?}
    <input type="checkbox" {?if $commonCalcFields && $commonCalcFields.avg.$k?}checked{?/if?} name="commonCalc[avg][{?$k?}]" value="do"> {?t?}среднее{?/t?}
    <input type="checkbox" {?if $commonCalcFields && $commonCalcFields.sum.$k?}checked{?/if?} name="commonCalc[sum][{?$k?}]" value="do"> {?t?}сумма{?/t?}
    </td>
</tr>
{?/foreach?}
</table>
{?/if?}
<table align='right'>
    <tr>
        <td>{?$cancel?}</td>
        <td>{?$main?}</td>
        <td>{?$submit?}</td>
    </tr>
</table>

<!--div align=right>
<input type="button" name="cancel" onClick="document.location.href='{?$sitepath?}rep.php?type={?$s.reports.current.type?}&step=1'" value="<< {?t?}Назад{?/t?}">
<input type="button" name="cancel" onClick="document.location.href='{?$sitepath?}rep.php?type={?$s.reports.current.type?}&step=1'" value="{?t?}Главная{?/t?}">
<input type="submit" name="submit" value="{?t?}Далее{?/t?} >>">
</div-->

</form>

{?php?}
$GLOBALS['controller']->captureStop(CONTENT);
echo show_tb();
{?/php?}
