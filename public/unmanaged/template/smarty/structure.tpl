<style type="text/css">
    @import url('{?$sitepath?}js/treeview.css');
</style>

<script type="text/javascript" src="{?$sitepath?}js/treeview.js.php"></script>


<div id="container"></div>

<script type="text/javascript">
    t = new treeview('{?$sitepath?}structure.php?soid=0')
//    t.addField('Номер',50,'{department}')
    t.addField('{?t?}Описание{?/t?}',200,'{info}')
    t.addField('{?t?}В должности{?/t?}',200,"<a href=\"javascript: void(0);\" onClick=\"wopen('userinfo.php?mid={mid}','user_520', '400', '300');\">{position}</a>")
    t.addField('',40, "<a style='visibility:{assignVisibility,hidden}' title='{?t?}Назначить{?/t?}' href='positions.php?c=assignement&soid={id}'><img src='{?$sitepath?}images/icons/people.gif' border=0 hspace='3'></a>")
    t.addField('',40,"<a style='visibility:{editVisibility,visible}' title='{?t?}Редактировать{?/t?}' href='positions.php?c=edit&soid={id}' target='_blank'><img src='{?$sitepath?}images/icons/edit.gif' border=0 hspace='3'></a>")
    t.addField('',40,"<a style='visibility:{deleteVisibility,visible}' title='{?t?}Удалить{?/t?}' onClick=\"if (confirm('{?t?}Вы действительно желаете удалить?{?/t?}?')) return true; else return false;\" href='positions.php?c=delete&soid={id}'><img src='{?$sitepath?}images/icons/delete.gif' border=0 hspace='3'></a>")
    t.showIn('container')
</script>
