<script type="text/javascript" language="JavaScript">
// <!--
	{?$sajaxJavascript?}
	function _structureItemClick(id, checked) {
		x_structureItemClick(Number(id), Number(checked), null);
	}
	function structureItemClick(elm, id) {

	    jQuery(elm)
			.parents('.tree-view.org-structure')
			.find('a.current-item')
			.removeClass('current-item')
			.end()
			.end()
			.addClass('current-item');/*
		elm = jQuery('#checkbox'+id).get(0);
		elm.checked = false;
		if (elm && !elm.disabled) {
			elm.checked = true;
			if (_structureItemClick) { _structureItemClick(id,true); }
			if (parent.mainFrame.get_checked_items) {
				parent.mainFrame.getCheckedItems();
				return true;
			}
		}*/
	}
//-->
</script>
<div class="tree-view org-structure" url="{?$sitepath?}orgstructure_toc.php">
	{?$tree2?}
</div>