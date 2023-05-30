<script type="text/javascript" language="JavaScript">
<!--
    function roomClick(elm) {
        jQuery(elm)
            .parents('.tree-view.org-structure')
            .find('a.current-item')
            .removeClass('current-item')
            .end()
            .end()
            .addClass('current-item');
    }
//-->
</script>

<div class="tree-view org-structure" url="{?$sitepath?}">
<ul>
    <li class="branch-expanded"> {?t?}Каналы{?/t?}
        <ul><li> <a class="current-item" onClick="roomClick(this)" href="{?$sitepath?}ajaxchat_chat.php?rid=0" target="mainFrame">{?t?}Свободный{?/t?}</a></li>
        {?if $rooms?}
            {?foreach from=$rooms key=cid item=room?}
                <li> {?$room.name?}
                    {?if $room.children?}
                        <ul>
                        {?foreach from=$room.children item=subroom?}
                            <li> {?if $subroom.room?}<a onClick="roomClick(this)" href="{?$sitepath?}ajaxchat_chat.php?rid={?$subroom.id?}&cid={?$cid?}" target="mainFrame">{?/if?}{?$subroom.name?}{?if $subroom.room?}</a>{?/if?}</li>
                        {?/foreach?}
                        </ul>
                    {?/if?}                    
                </li>
            {?/foreach?}
        {?/if?}
        </ul>
    </li>
</ul>
</div>