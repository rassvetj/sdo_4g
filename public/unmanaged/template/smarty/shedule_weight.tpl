<table width=100% class=main cellspacing=0>
    <tr>
        <th>{?t?}Тип занятия{?/t?}</th>
        <!--th>{?t?}Вес типа занятия{?/t?}</th-->
        <th>{?t?}Вес типа занятия на курсе{?/t?}</th>
        <th>{?t?}Действия{?/t?}</th>
    </tr>
{?if $weights?}
    {?foreach from=$weights item=w?}
    <tr>
        <td><img alt="{?$w.name?}" border=0 src="images/events/{?$w.icon?}" align="absmiddle" hspace="5">{?$w.name?}</td>
        <!--td>{?$w.weight_base?}</td-->
        <td>
        {?if $w.weight==-1?}
            {?t?}не используется{?/t?}
        {?else?}
            {?$w.weight?}
        {?/if?}
        </td>
        <td align=center>
        <a href="{?$sitepath?}shedule_weight.php?action=edit&id={?$w.id?}&cid={?$course.id?}">
        <img title="{?t?}Редактировать свойства типа{?/t?}" border=0 src="images/icons/edit.gif"></a>
        <!--a href="{?$sitepath?}shedule_weight.php?action=delete&id={?$w.id?}&cid={?$course.id?}" onClick="if (confirm('{?t?}Вы действительно желаете удалить вес по курсу\{?/t?}n({?t?}будет использовано базовое значение){?/t?}?')) return true; else return false;">
        <img title="{?t?}Удалить вес по курсу{?/t?}" border=0 src="images/icons/delete.gif"></a-->
        </td>
    </tr>
    {?/foreach?}
{?/if?}
</table>

    <br>

    <script type="text/javascript" src="{?$sitepath?}js/pie.js.php"></script>
    <style type="text/css">
          @import "{?$sitepath?}/styles/pie.css";
    </style>

    <div id="pie_container">
    </div>

    <script type="text/javascript">
        self.p = new pie()

{?if $weights?}
    {?foreach from=$weights item=w?}
        {?if ($w.weight>=0)?}
        p.addPiece('{?$w.name?}','{?$w.id?}','{?$w.color?}','{?$w.weight?}')
        {?/if?}
    {?/foreach?}
{?/if?}

//        p.addPiece('Piece 1','id1','red',1)
//        p.addPiece('Piece 2','id2','green',29)
//        p.addPiece('Piece 3','id3','blue',30)
//        p.addPiece('Piece 4','id4','salmon',38)
//        p.addPiece('Piece 5','id5','#AABBCC',1)
//        p.addPiece('Piece 6','id6','#BBCCAA',1)

        p.sendTo = '{?$sitepath?}shedule_weight.php?cid={?$cid?}'
        p.gotoAfter = '{?$sitepath?}shedule_weight.php?cid={?$cid?}'
				
        $(window).ready(function(){
        self.p.drawIn('pie_container')})
				
    </script>
