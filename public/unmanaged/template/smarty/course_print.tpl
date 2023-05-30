<html>
 <head>
  <link rel="stylesheet" href="{?$sitepath?}styles/report.css" type="text/css">
  <style>
  .section {
    font-weight: 700;
  }
  .topic {
    font-style: italic;
    font-weight: 700;
  }
  .task {
  }
 </style>
 </head>
 <body>
   <table width=100% border="0" cellpadding="4" cellspacing="0" class="print_report_table">
    <tr>
     <th rowspan="2">{?t?}Наименование раздела, темы{?/t?}</th><th rowspan="2">{?t?}Время{?/t?}</th><th rowspan="2">№ {?t?}элемента задачи{?/t?}</th><th colspan="2">{?t?}Цели обучения{?/t?}</th>
    </tr>
    <tr>
     <th>{?t?}Знать{?/t?}</th><th>{?t?}Уметь{?/t?}</th>
    </tr>
    {?foreach from=$structure key=section_number item=section?}
    <tr>
     <td class="section">{?$section.title?}</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
    </tr>
    {?foreach from=$section.sub key=topic_number item=topic?}
    <tr>
     <td class="topic">{?$topic.title?}</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
    </tr>
    {?foreach from=$topic.sub key=task_number item=task?}
    <tr>
     <td class="task">{?$task.title?}</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;{?$task.targets.know?}</td><td>&nbsp;{?$task.targets.can?}</td>
    </tr>
    {?/foreach?}
    {?/foreach?}
    {?/foreach?}
   </table>
 </body>
</html>