{?if $BLANK?}
<input name="view" type="hidden" value="blank">
{?/if?}
<table width=100% class="forum_main" cellspacing=0>
    {?if !$category?}
    <tr>
        <th>
            {?if $answer?}{?t?}Ответить на сообщение{?/t?}{?else?}{?t?}Ответить на сообщение{?/t?}{?/if?}
        </th>
    </tr>
    {?/if?}
    {?if !$answer && $ICONS?}
    <tr>
        <td>
            <p class="lf_msg">{?t?}Иконка{?/t?}</p>
            {?foreach name="icons" from=$ICONS key=key item=icon?}
            <input
                type="radio"
                name="data[icon][int]"
                value="{?$key?}"
                {?if $thread_info.icon eq $key?}checked{?/if?}
                {?if $smarty.foreach.icons.first and not $thread_info.icon?}checked{?/if?}>
            <img src="{?$sitepath?}{?$icon?}">
            {?/foreach?}
        </td>
    </tr>
    {?/if?}
    {?if !$answer?}
    <tr>
        <td>
            <p class="lf_msg">{?t?}Тема{?/t?}</p>
            <textarea name="data[name][string]" rows=1 cols=80>{?$thread_info.theme|escape?}</textarea>
        </td>
    </tr>
    {?/if?}
    <tr>
        <td>
            <p class="lf_msg">{?t?}Сообщение{?/t?}</p>
            {?if $smarty.const.ENABLE_FORUM_RICHTEXT && $fckeditor?}
                {?if $answer?}
                    {?$fckeditor_answer?}
                {?else?}
                    {?$fckeditor?}
                {?/if?}
            {?else?}
                {?if $answer?}
                <textarea name="data[message_answer][string]" rows=5 cols=80>{?$thread_info.message|escape?}</textarea>
                {?else?}
                <textarea name="data[message][string]" rows=5 cols=80>{?$thread_info.message|escape?}</textarea>
                {?/if?}
            {?/if?}
        </td>
    </tr>
    {?* remove false if u want to enable block *?}
    {?if $category && false?}
    <tr>
        <td>
            <p class="lf_msg">{?t?}Видимость{?/t?}</p>
            <select name="data[type][int]" onChange="
                    $(this).parent().find('select[id]').hide();
                    {?if $VISIBILITY_DEPENDENCES?}
                    {?foreach from=$VISIBILITY_DEPENDENCES key=key item=item?}
                    {?if (is_array($item) && count($item))?}
                    {?foreach from=$item item=i?}
                        if (this.value=={?$key?}) document.getElementById('{?$i?}').style.display = 'inline';
                    {?/foreach?}
                    {?/if?}
                    {?/foreach?}
                    {?/if?}">
                {?if $VISIBILITY_TYPES?}
                {?foreach from=$VISIBILITY_TYPES key=key item=type?}
                <option value="{?$key?}"> {?$type?}</option>
                {?/foreach?}
                {?/if?}
            </select>
            <select id="courses" name="data[courses][int]" onChange="" style="display: none;">
                {?if $courses?}
                {?foreach from=$courses key=key item=course?}
                <option {?if $thread_info.icon and $thread_info.icon == $key?}checked{?/if?} value="{?$key?}">{?$course?}</option>
                {?/foreach?}
                {?/if?}
            </select>
            <select id="departments" name="data[departments][int]" onChange="" style="display: none;">
                {?if $departments?}
                {?foreach from=$departments key=key item=department?}
                <option value="{?$key?}">{?$department?}</option>
                {?/foreach?}
                {?/if?}
            </select>
            <select id="teachers" name="data[teachers][int]" onChange="" style="display: none;">
                {?if $teachers?}
                {?foreach from=$teachers key=key item=teacher?}
                <option value="{?$key?}">{?$teacher?}</option>
                {?/foreach?}
                {?/if?}
            </select>
            {?$tooltip->display($toolTip)?}
        </td>
    </tr>
    <tr>
        <td><input name="data[sendmail][int]" {?if $thread_info.sendmail?}checked{?/if?} type="checkbox" value="1">&nbsp;{?t?}высылать сообщения по email{?/t?}</td>
    </tr>
    {?/if?}
    <tr>    
        <td class="buttons-panel">
            {?$OKBUTTON?}
        </td>
    </tr>
</table>