<script type="text/javascript" language="JavaScript" src="{?$sitepath?}js/roles.js"></script>
<form enctype="multipart/form-data" method="POST" onSubmit="select_list_select_all('addons');">
<input type="hidden" name="action" value="save">
<input type="hidden" name="tab" value="{?$tab?}">
<table width=100% class=main cellspacing=0>
    <tr>
        <th colspan=3>{?t?}Технические настройки{?/t?}</th>
    </tr>
    <tr>
        <td width=30% valign=top>{?t?}Название окна браузера:{?/t?} </td>
        <td valign=top><input type="text" name="options[windowTitle][text]" value="{?if $options.windowTitle?}{?$options.windowTitle|escape?}{?/if?}" style="width: 100%;"></td>
        <td width=30%>&nbsp;</td>
    </tr>
    <tr>
        <td width=30% valign=top>{?t?}Текст приветствия:{?/t?} </td>
        <td valign=top><input type="text" name="options[welcomeText][text]" value="{?if $options.welcomeText?}{?$options.welcomeText|escape?}{?/if?}" style="width: 100%;"></td>
        <td width=30%>&nbsp;</td>
    </tr>
</table>
<br><table width=100% class=main cellspacing=0>
    <tr>
        <th colspan=3>{?t?}Дизайн портала{?/t?}</th>
    </tr>
    <tr>
        <td width=30% valign=top>{?t?}Цвета:{?/t?} </td>
        <td valign=top>
        <div class="preview-color-1" style="float: right; width: 45%; border: 1px solid white;">&nbsp;</div>
        <input type="text" name="options[color1][text]" id="color1_{?$tab_number?}" style="width: 50%;" value="{?if $options.color1?}{?$options.color1|escape?}{?/if?}" readonly="readonly" />
        <div class="clear-both"></div>
        <div style="margin-top: 0.5em;">
					<div class="preview-color-2" style="float: right; width: 45%; border: 1px solid white;">&nbsp;</div>
					<input type="text" name="options[color2][text]" id="color2_{?$tab_number?}" style="width: 50%;" value="{?if $options.color2?}{?$options.color2|escape?}{?/if?}" readonly="readonly" />
        </div>
		<div>
		<table border=0 cellpadding=0 cellspacing=0>
			<tr>
				<td>
			        <div class="button" style="margin-top: 0.5em;"><a onclick="eLS.utils.ColorPicker.show(); return false;" href="javascript:void(0);">{?t?}Выбрать{?/t?}</a></div>
					</div>
				</td>
				<td>
					<div class="button" style="margin-top: 0.5em;"><a onclick="javascript:eLS.utils.ColorPicker.apply(['#3490c5', '#f78f15']); return false;" href="javascript:void(0);">{?t?}Очистить{?/t?}</a></div>
				</td>
			</tr>
		</table>
		</div>
        </td>
        <td width=30%>&nbsp;</td>
    </tr>
    <tr>
        <td>{?t?}Логотип{?/t?} (.gif): </td>
        <td>
            <input type="file" name="options[logo][file]" style="width: 100%;"><br>
            {?if $options.logo?}<a href="{?$sitepath?}options/{?$options.logo?}" target=_blank>{?$options.logo|escape?}</a>{?/if?}
        </td>
        <td></td>
    </tr>
</table><br>
<p>
{?$okbutton?}
</form>
