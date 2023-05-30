<table width=100% class=main cellspacing=0>
    <tr>
        <th>{?t?}Курс{?/t?}</th>
        <th>{?t?}Доступ{?/t?}</th>
        <th>{?t?}Преподователи{?/t?}</th>
        <th>{?t?}Категории{?/t?}</th>
        <th>{?t?}Действие{?/t?}</th>
    </tr>
    {?assign var='url' value=''?}
    {?if $perm==1?}
        {?assign var='url' value='&Action=change&redirect=1&mytypereg=new_student'?}
    {?/if?}
    {?if $perm==2?}
        {?assign var='url' value='&Action=change&redirect=1&mytypereg=new_teacher'?}
    {?/if?}
    
    {?if $courses?}
        {?foreach from=$courses item=course?}
            <tr>
                <td>
                    <a href='#' onClick="javascript: wopen('courseinfo.php?cid={?$course.CID?}','desc_{?$course.CID?}', '800', '600')" title='{?t?}описание курса{?/t?}'>
                        {?$course.Title?}
                    </a>
                </td>
                <td>{?$course.TypeDes?}</td>
                <td>{?$course.teachers?}</td>
                <td>
                    {?if $course.dids?}
                        {?foreach from=$course.dids item=did?}
                            <a href="{?$sitepath?}order.php?cat={?$did.did?}">{?$did.name?}</a><br />
                        {?/foreach?}
                    {?else?}
                        {?t?}Курс вне категорий{?/t?}
                    {?/if?}
                </td>
                <td>
                    {?if $regFree?}                                       
                        <div class='button'>
                            <a href="javascript:void(0);" onclick="document.location.href = 'reg.php4?Course={?$course.CID?}{?$url?}'; return false;">
                                {?if !$course.free?}
                                    {?t?}Подать заявку{?/t?}
                                {?else?}
                                    {?t?}Зарегистрироваться{?/t?}
                                {?/if?}
                            </a>
                        </div>
                        <input type='button' value='{$title}' style='display: none;'/>
                        {?if !$course.free?} 
                            &nbsp;&nbsp;{?$course.tooltip?}                    
                        {?/if?}
                        <div class='clear-both'></div>
                    {?else?}
                        <br />
                    {?/if?}
                </td>
            </tr>
        {?/foreach?}
    {?else?}
        <tr>
            <td colspan='99' align="center">{?t?}Ничего не найдено{?/t?}</td>
        </tr>
    {?/if?}
</table>

<a href='{?$sitepath?}order.php'>&larr;&nbsp;назад к категориям</a>