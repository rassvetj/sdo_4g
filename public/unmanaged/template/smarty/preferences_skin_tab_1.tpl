<script type="text/javascript" language="JavaScript" src="{?$sitepath?}js/roles.js"></script>
<form enctype="multipart/form-data" method="POST" onsubmit="javascript: return confirm('{?t?}Вы уверены, что хотите изменить настройки отображения для всех страниц системы?{?/t?}')">
<input type="hidden" name="action" value="save">
<input type="hidden" name="skin" value="1">
<table width=100% class=main cellspacing=0>
    <tr>
        <td>{?t?}Шаблон{?/t?} </td>
        <td>
            <div style="float:left; width: 200px; padding: 10px; text-align: center;"><img src="{?$sitepath?}template/smarty/skins/redmond/images/layout/thumb.gif" style="border: 1px solid #888; cursor: pointer;" onclick="javascript:wopen('{?$sitepath?}template/smarty/skins/redmond/images/layout/preview.gif', 'redmond', 692, 476)"><input type="radio" name="options[skin][text]" value="redmond" {?if $current_skin == 'default'?}onclick="document.getElementById('prefs_default_skin').style.display = this.checked ? 'none' : 'block'"{?/if?} {?if $current_skin == 'redmond'?}checked{?/if?}>&nbsp;Redmond</input></div>
            <div style="float:left; width: 200px; padding: 10px; text-align: center;"><img src="{?$sitepath?}template/smarty/skins/default/images/layout/thumb.gif" style="border: 1px solid #888; cursor: pointer;" onclick="javascript:wopen('{?$sitepath?}template/smarty/skins/default/images/layout/preview.gif', 'default', 688, 459)"><input type="radio" name="options[skin][text]" value="default" {?if $current_skin == 'default'?}onclick="document.getElementById('prefs_default_skin').style.display = this.checked ? 'block' : 'none'"{?/if?} {?if $current_skin == 'default'?}checked{?/if?}>&nbsp;Classic</input>&nbsp;&nbsp;{?$tooltip->display('layout_classic')?}</div>
        </td>
    </tr>
</table>
<div id="prefs_default_skin" {?if $current_skin != 'default'?}style="display: none;"{?/if?}>
<table width=100% class=main cellspacing=0>    <tr>
        <td valign=top>{?t?}Цвета{?/t?} </td>
        <td valign=top>
		<table border=0 cellpadding=0 cellspacing=0>
			<tr>
				<td>
					<input type="text" name="options[color1][text]" id="color1_01" style="width: 70px;" value="{?if $options.color1?}{?$options.color1|escape?}{?/if?}"maxlength=7" />
				</td>
				<td>
					<div class="preview-color-1" style="float: right; width: 100px; border: 1px solid white;">&nbsp;</div>
				</td>
				<td rowspan=2>
					{?$tooltip->display('design_colours')?}
				</td>
			</tr>
			<tr>
				<td>
					<input type="text" name="options[color2][text]" id="color2_01" style="width: 70px;" value="{?if $options.color2?}{?$options.color2|escape?}{?/if?}"maxlength=7" />
				</td>
				<td>
					<div class="preview-color-2" style="float: right; width: 100px; border: 1px solid white;">&nbsp;</div>
				</td>
			</tr>
		</table>
		<table border=0 cellpadding=0 cellspacing=0>
			<tr>
				<td>
					<div class="button" style="margin-top: 0.5em;"><a onclick="eLS.utils.ColorPicker.show(); return false;" href="javascript:void(0);">{?t?}Выбрать{?/t?}</a></div>
			     </td>
			     <td>
					<div class="button" style="margin-top: 0.5em;"><a onclick="javascript:eLS.utils.ColorPicker.apply(['#3490c5', '#f78f15']); return false;" href="javascript:void(0);">{?t?}Очистить{?/t?}</a></div>
					<!-- <input type="button" value="{?t?}Выбрать{?/t?}" onClick="eLS.utils.ColorPicker.show('color1', 'color2')"> -->
				</td>
			</tr>
		</table>
		</td>
    </tr>
    <tr>
        <td>{?t?}Логотип{?/t?} </td>
        <td>
            <input type="file" name="options[logo][file]" style="width: 90.7%;">
			{?$tooltip->display('design_logo')?}
			<br>
            {?if $options.logo?}<a href="{?$sitepath?}options/{?$options.logo?}" target=_blank>{?$options.logo|escape?}</a>{?/if?}

        </td>
    </tr>
</table>
</div>
<p>
{?$okbutton?}
</form>
