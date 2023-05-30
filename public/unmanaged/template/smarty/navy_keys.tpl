<a title="{?$titles.change?}" onclick="window.location.href = '{?$sitepath?}teachers/edit_navigation.php?CID={?$cid?}&make=edit_item&item_id={?$oid?}&new_id=o'+parent.leftFrame.organizationItemId; return false;" href="javascript:void(0);">
<img hspace="3" border="0" src="{?$sitepath?}images/struct/change.gif" align="absmiddle"/>
</a>
<a title="{?$titles.add?}" href="{?$sitepath?}teachers/edit_navigation.php?CID={?$cid?}&make=additem&after={?$oid?}">
<img hspace="3" border="0" src="{?$sitepath?}images/struct/add.gif" align="absmiddle"/>
</a>
<a title="{?$titles.move_up?}" href="{?$sitepath?}teachers/edit_navigation.php?CID={?$cid?}&itemID={?$oid?}&make=up_item">
<img hspace="3" border="0" src="{?$sitepath?}images/struct/up.gif" align="absmiddle" />
</a>
<a title="{?$titles.move_right?}" href="{?$sitepath?}teachers/edit_navigation.php?CID={?$cid?}&itemID={?$oid?}&make=next_level">
<img hspace="3" border="0" src="{?$sitepath?}images/struct/right.gif" align="absmiddle" />
</a>
<a title="{?$titles.move_down?}" href="{?$sitepath?}teachers/edit_navigation.php?CID={?$cid?}&itemID={?$oid?}&make=down_item">
<img hspace="3" border="0" src="{?$sitepath?}images/struct/down.gif" align="absmiddle" />
</a>
<a title="{?$titles.move_left?}" href="{?$sitepath?}teachers/edit_navigation.php?CID={?$cid?}&itemID={?$oid?}&make=prev_level">
<img hspace="3" border="0" src="{?$sitepath?}images/struct/left.gif" align="absmiddle" />
</a>
&nbsp;&nbsp;
<a onclick="putElem('edit_{?$oid?}');" title="{?$titles.edit?}" href="javascript:void(0);">
<img hspace="3" border="0" src="{?$sitepath?}images/icons/edit.gif" align="absmiddle"/>
</a>
<a title="{?$titles.delete?}" href="{?$sitepath?}teachers/edit_navigation.php?CID={?$cid?}&itemID={?$oid?}&make=deleteItem" onclick="javascript: return confirm('{?t?}Вы действительно желаете удалить этот и все вложенные элементы курса?{?/t?}')">
<img hspace="3" border="0" src="{?$sitepath?}images/icons/delete.gif" align="absmiddle"/>
</a>