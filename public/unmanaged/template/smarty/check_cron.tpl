<script>
{? $Sajax ?}

var current_task_id = "";

function run_cron(task_id) {
    var elm = document.getElementById(task_id);
    if (elm) {
        elm.innerHTML = "{?t?}Выполняется...{?/t?}";
    }
    current_task_id = task_id;
    x_run_cron(task_id, run_cron_done);
}

function run_cron_done(res) {
    var elm = document.getElementById(current_task_id);
    if (elm) elm.innerHTML = "{?t?}Выполнено{?/t?}";
}
</script>

<table width=100% class=main cellspacing=0>
{? if count($Tasks) ?}
<tr>
    <th>ID</th>
    <th>{?t?}Название{?/t?}</th>
    <th>{?t?}Вкл.{?/t?}</th>
    <th>{?t?}Период выполнения{?/t?}</th>
    <th>{?t?}Последний автоматический запуск{?/t?}</th>
    <th width="100"></th>
</tr>
{? foreach from=$Tasks item=Task ?}
<tr>
    <td>{? $Task.id ?}</td>
    <td><b>{? $Task.name ?}</b></td>
    <td align="center"><img src="{?$sitepath?}../images/icons/{? if $Task.launch == "true" ?}ok.gif{?else?}cancel.gif{?/if?}" border="0"/></td>
    <td>{? if $Task.runperiod==0 ?}{?t?}Всегда{?/t?}{?else?}{?t?}Каждые{?/t?} {?$Task.runperiod?} {?t?}секунд{?/t?}{?/if?}</td>
    <td>{? if $Task.runtime==0 ?}{?t?}Нет{?/t?}{?else?}{?$Task.runtime|date_format:"%d-%m-%Y %H:%M:%S"?}{?/if?}</td>
    <td id="{? $Task.id ?}"><input type="button" onclick="run_cron('{? $Task.id ?}');" value="Выполнить"/></td>
</tr>
{? /foreach?}
{? else ?}
<tr>
    <td>{?t?}Нет ни одного назначенного задания{?/t?}</td>
</tr>
{? /if ?}
</table>