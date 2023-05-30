<!-- table cellspacing="0" cellpadding=4 border=0 width=100%>
<tr>
    <td colspan=2 valign=top>
    "УТВЕРЖДАЮ"<br>Председатель Ученого Совета<br>Красноярского госуниверситета
    <br><br>
    </td>    
    <td valign=top>Министерство образования РФ</td>
    <td valign=top>ФГОУ ВПО СФУ</td>
</tr>
<tr>
    <td>Ректор</td>
    <td>Ваганов Е.А.</td>
    <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong>Учебный план</strong><br><br></td>
    <td>
    Форма обучения:<br>
    Срок обучения:<br>
    Квалификация:<br>
    </td>
</tr>
</table-->
<br><br>
<center><strong>{?t?}Специальность{?/t?} "{?$track.2?}" - {?$track.1?}</strong></center>
<br><br>
<table cellspacing="0" cellpadding=4 class="print_report_table">
    <tr>
        <td rowspan="4">{?t?}№ п/п{?/t?}</td>
        <td rowspan="4">{?t?}Наименование дисциплин{?/t?}</td>
        <td colspan="{?math equation="3 + 12" x=$examTypesCount?}" align=center>{?t?}Распределение по курсам и семестрам{?/t?}</td>
        <td colspan="{?$examTypesCount?}">&nbsp;</td>
    </tr>
    <tr>
        <td rowspan="3">{?t?}Всего часов{?/t?}</td>
        <td colspan="2" align=center>{?t?}из них{?/t?}</td>
        <td colspan="2">1&nbsp;курс</td>
        <td colspan="2">2&nbsp;курс</td>
        <td colspan="2">3&nbsp;курс</td>
        <td colspan="2">4&nbsp;курс</td>
        <td colspan="2">5&nbsp;курс</td>
        <td colspan="2">6&nbsp;курс</td>
        {?foreach from=$examTypes item=item?}
            <td rowspan=3>{?$item?}</td>
        {?/foreach?}
    </tr>
    <tr>
        <td rowspan="2" colspan="1">{?t?}Аудиторные{?/t?}</td>
        <td rowspan="2">{?t?}Самостоятельная работа{?/t?}</td>
        {?section name=col loop=13 start=1?}
            <td rowspan=2 valign=top>{?$smarty.section.col.index?}{?t?}с{?/t?}</td>
        {?/section?}        
    </tr>
    <tr>            
                
    </tr>
    <tr>
    {?section name=col loop=$countCols start=1?}
        <td>{?$smarty.section.col.index?}</td>
    {?/section?}
    </tr>
    <!--tr>
        {?section name=col loop=$countCols start=1?}
            <td>
            {?if $track[$smarty.section.col.index]?}
                {?$track[$smarty.section.col.index]?}
            {?else?}
                &nbsp;
            {?/if?}
            </td>
        {?/section?}
    </tr-->
    {?foreach from = $courses item = info key = name?}
        <tr>
            <td>{?t?}ГСЭ.Ф.{?/t?}{?counter?}</td>
            <td>{?$name?}</td>
            <td>{?$info.hours?}</td>
            <td>{?$info.hours1?}</td>
            <td>{?$info.hours2?}</td>
            <!-- >td>
                {?if $info.eventsum?}
                    {?$info.eventsum?}
                {?else?}
                    &nbsp;
                {?/if?}
            </td>                
            <td>
                {?if $info.hours_events?}
                    {?$info.hours_events?}
                {?else?}
                    &nbsp;
                {?/if?}
            </td-->            

            {?section name=col loop=13 start=1?}
            <td>
            {?if $info.level == $smarty.section.col.index?}
                {?$info.hours?}
            {?else?}
                &nbsp;            
            {?/if?}
            </td>
            {?/section?}
            
            {?foreach from = $examTypes key = key item = item?}
                <td>
                    {?if $info.examtype.$key?}
                        {?$info.level?}
                    {?else?}
                        &nbsp;
                    {?/if?}
                </td>                
            {?/foreach?}
    </tr>
    {?/foreach?}
    
    <tr>
        <td colspan='2' align='right'>{?t?}Итого за цикл{?/t?}:</td>
        <td>{?$totalHours.courses?}</td>
        <td>{?$hours1Total?}</td>                
        <td>
            {?$totalHours.courses-$hours1Total?}
        </td>
        {?section name=col loop=13 start=1?}            
            {?if $totalHours.levels[$smarty.section.col.index]?}
                <td>{?$totalHours.levels[$smarty.section.col.index]?}</td>
            {?else?}
                <td>&nbsp;</td>
            {?/if?}
        {?/section?}
        <td colspan='{?$examTypesCount?}'>&nbsp;</td>
    </tr>
    
</table>        
        
        