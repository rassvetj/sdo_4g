<script type="text/javascript" src="{?$sitepath?}js/chains.js"></script>
{?if $smarty.const.USE_AT_INTEGRATION?}
<script type="text/javascript">{?$js?}</script>
{?/if?}

<form action="" method="POST" onSubmit="javascript:if (document.getElementById('chain').options.length<=0) {alert('{?t?}Невозможно создать пустую цепочку!{?/t?}'); return false;} chain_select_all('chain');">

{?if $action=='add'?}
    <input type="hidden" name="post" value="add">
{?else?}
    <input type="hidden" name="post" value="edit">
{?/if?}
<input type="hidden" name="id" value="{?if $chain?}{?$chain.id?}{?else?}0{?/if?}">
<table width=100% cellspacing=0 class=main>
<tr>
    <td>{?t?}Название{?/t?} </td>
    <td><input type="text" name="name" value="{?$chain.name?}" style="width: 300px;"></td>
</tr>
<tr>
    <td colspan=2>
    <table cellpaddin=0 cellspacing=0 border=0>
{?if $smarty.const.USE_AT_INTEGRATION?}
        <tr>
            <td><div align="right">{?t?}Должность в структуре организации:{?/t?}&nbsp;</div></td>
            <td>
                <select name="positions" style="width: 200px;" onclick="var This = this; setTimeout(function() {getPositions(This);}, 0);" id="positions" multiple size='8'>
                    {?if $positions?}
                        {?foreach from=$positions item=p?}
                            <option value="{?$p.soid?}"> {?$p.name?}</option>
                        {?/foreach?}
                    {?else?}
                    <option value="0"> {?t?}нет{?/t?}</option>
                    {?/if?}
                </select>
                {?$tooltip->display('chain_struct')?}
            </td>
            <td>&nbsp;<div class="button"><a href="javascript:void(0);" title="{?t?}добавить{?/t?}" onClick="chain_add_item('positions','chain')">&#8594;</a></div>&nbsp;</td>
            <td rowspan=3>
                &nbsp;<div class="button"><a href="javascript:void(0);" title="{?t?}удалить{?/t?}" onClick="chain_select_clear_item('chain')">&#8592;</a>&nbsp;</div>
            </td>
            <td rowspan=3>
                <select name="chain[]" id="chain" style="width: 200px;" size=7 multiple>
                {?if $chain.items?}
                    {?foreach from=$chain.items key=k item=i?}
                        <option value="{?$k?}"> {?$i?}</option>
                    {?/foreach?}
                {?/if?}
                </select>
            </td>
            <td rowspan='3'>
                {?$tooltip->display('chain')?}
            </td>
        </tr>
        <tr>
        <td>
            <div align="right">{?t?}Должность в учебной структуре:{?/t?}&nbsp;</div></td>
            <td>
                <select name="departments" style="width: 200px;" id="departments">
                    {?if $departments?}
                        {?foreach from=$departments item=d?}
                            <option value="d:{?$d.did?}"> {?$d.name?}</option>
                        {?/foreach?}
                    {?else?}
                    <option value="0"> {?t?}нет{?/t?}</option>
                    {?/if?}
                </select>
                {?$tooltip->display('chain_struct')?}
            </td>
            <td>&nbsp;<div class="button"><a href="javascript:void(0);" title="{?t?}добавить{?/t?}" onClick="chain_add_item('departments','chain')">&#8594;</a></div>&nbsp;</td>
        </tr>
{?else?}
        <tr>
          <td><div align="right">{?t?}Должность в учебной структуре:{?/t?}&nbsp;</div></td>
            <td>
                <select name="departments" style="width: 200px;" id="departments">
                    {?if $departments?}
                        {?foreach from=$departments item=d?}
                            <option value="d:{?$d.did?}"> {?$d.name?}</option>
                        {?/foreach?}
                    {?else?}
                    <option value="0"> {?t?}нет{?/t?}</option>
                    {?/if?}
                </select>
                {?$tooltip->display('chain_struct')?}&nbsp;
            </td>
            <td>&nbsp;<div class="button"><a href="javascript:void(0);" title="{?t?}добавить{?/t?}" onClick="chain_add_item('departments','chain')">&#8594;</a></div>&nbsp;</td>
            <td rowspan=2>
                &nbsp;<div class="button"><a href="javascript:void(0);" title="{?t?}добавить{?/t?}" onClick="chain_select_clear_item('chain')">&#8592;</a>&nbsp;</div>
            </td>
            <td rowspan=2>
                <select name="chain[]" id="chain" style="width: 200px;" size=7 multiple>
                {?if $chain.items?}
                    {?foreach from=$chain.items key=k item=i?}
                        <option value="{?$k?}"> {?$i?}</option>
                    {?/foreach?}
                {?/if?}
                </select>
            </td>
            <td rowspan=2>
                {?$tooltip->display('chain')?}
            </td>
        </tr>
{?/if?}
        <tr>
          <td><div align="right">{?t?}Относительная должность:{?/t?}&nbsp;</div></td>
            <td>
                <select name="others" style="width: 200px;" id="others">
                    {?if $others?}
                        {?foreach from=$others item=o?}
                            <option value="{?$o.value?}"> {?$o.name?}</option>
                        {?/foreach?}
                    {?else?}
                    <option value="0"> {?t?}нет{?/t?}</option>
                    {?/if?}
                </select>
                {?$tooltip->display('chain_relative')?}&nbsp;
            </td>
            <td>&nbsp;<div class="button"><a href="javascript:void(0);" title="{?t?}добавить{?/t?}" onClick="chain_add_item('others','chain')">&#8594;</a></div>&nbsp;</td>
            <td></td>
        </tr>
    </table>
    </td>
</tr>
<tr>
    <td colspan=2><input type="checkbox" name="order" value="1" {?if $chain.order?}checked{?/if?}>
        {?t?}Важна последовательность{?/t?}
        {?$tooltip->display('important_order')?}
    </td>
</tr>
</table>
<table border="0" cellspacing="5" cellpadding="0" width="100%">
      <tr>
        <td align="right" width="99%">
        {?$okbutton?}
        </td>
        <td align="right" width="1%">
        <div style='float: right;' class='button'><a href='javascript:history.back();'>{?t?}Отмена{?/t?}</a></div><input type='button' value='{?t?}отмена{?/t?}' style='display: none;'/><div class='clear-both'></div>
        </td>
      </tr>
</table>        
</form>