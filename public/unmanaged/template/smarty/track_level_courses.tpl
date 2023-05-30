<script type="text/javascript" language="JavaScript" src="{?$sitepath?}js/roles.js"></script>
<script type="text/javascript">{?$sajax_javascript?}</script>
<script type="text/javascript">
<!--
function show_course_select(html) {
    var elm = document.getElementById('courses_div');
    if (elm) elm.innerHTML = '<select size=10 id=\"all_courses\" name=\"del_courses[]\" multiple style=\"width:100%\">'+html+'</select>';
}

function get_course_select(str) {
    var current = 0;

    var select = document.getElementById('search_courses_div');
    if (select) current = select.value;

    var elm = document.getElementById('courses_div');
    if (elm) elm.innerHTML = '<select size=10 id=\"all_courses\" name=\"del_courses[]\" multiple style=\"width:100%\"><option>{?t?}Загружаю данные{?/t?}</option></select>';

    //get_course_select_used('');
    x_search_courses_unused(str, Number('{?$trid?}'), Number('{?$level?}'), show_course_select);
}

function show_course_select_used(html) {
    var elm = document.getElementById('courses_div_used');
    if (elm) elm.innerHTML = '<select size=10 id=\"courses\" name=\"need_courses[]\" multiple style=\"width: 100%\">'+html+'</select>';
}

function get_course_select_used(str) {
    var elm = document.getElementById('courses_div_used');
    if (elm) elm.ennerHTML = '<select size=10 id=\"courses\" name=\"need_courses[]\" multiple style=\"width: 100%\"><option>{?t?}Загружаю данные{?/t?}</option></select>';
    x_search_courses_used(Number('{?$trid?}'), Number('{?$level?}'), show_course_select_used);
}

//-->
</script>
<form method="POST" action="tracks.php" onSubmit="select_list_select_all('courses'); select_list_select_all('all_courses');">
<input type="hidden" name="c" value="edit_courses_level_assign">
<input type="hidden" name="trid" value="{?$trid?}">
<input type="hidden" name="level" value="{?$level?}">
<table width=100% class=main cellspacing=0>
    <tr>
    <th>{?t?}Все курсы{?/t?}</th>
    <th></th>
    <th>{?t?}{?t?}Курсы семестра{?/t?}{?/t?}</th>
    </tr>
    <tr>
    <td width=50% valign=top>
    <input type="button" value="{?t?}Все{?/t?}" style="width: 10%" onClick="if (elm = document.getElementById('search_courses')) elm.value='*'; get_course_select('*');">
    <input type="text" name="search_courses" id="search_people" value="{?$search?}" style="width: 88%" onKeyUp="if (typeof(filter_timeout)!='undefined') clearTimeout(filter_timeout); filter_timeout = setTimeout('get_course_select(\''+this.value+'\');',1000);">
    <br>
    <div id="courses_div">
    <select size=10 id="all_courses" name="del_courses[]" multiple style="width:100%">
    {?$all_courses?}
    </select>
    </div>
    </td>
    <td valign=middle align=middle>
        <input type="button" value=">>" onClick="select_list_move('all_courses','courses','select_list_cmp_by_text');">
        <input type="button" value="<<" onClick="select_list_move('courses','all_courses','select_list_cmp_by_text');">
    </td>
    <td width=50% valign=top>
    <div id="courses_used_div">
    <select size=10 id="courses" name="need_courses[]" multiple style="width: 100%">
    {?$courses?}
    </select>
    </div>
    </td></tr>
</table><br>
{?$okbutton?}
</form>
