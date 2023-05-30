<script type="text/javascript" language="JavaScript">
<!--
{?$sajaxJavascript?}

function _structureItemClick(id, checked) {
    x_structureItemClick(Number(id), Number(checked), null);
}

function structureItemClick(elm, id) {
    $('#toc').find('a').each(function(i) { 
        $(elm).removeClass('structureCurrentItem') 
    });
    $(elm).addClass('structureCurrentItem'); 
    $('#checkbox'+id).get(0).checked = true; 
    if (_structureItemClick) _structureItemClick(id,true); 
    if (parent.mainFrame.get_checked_items) parent.mainFrame.getCheckedItems(); return true;
}

//-->
</script>
<form action="" method="POST">
<ul id="toc" class=main>
{?$tree?}
</ul>
</form>