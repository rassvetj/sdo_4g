{?php?}
	try {	
		$isFilial = ! Zend_Registry::get('serviceContainer')->getService('User')->isMainOrganization();
	} catch (Exception $e) {
		$isFilial = false;
	} 	
{?/php?}
	
{?foreach from=$this->objects.menu->groups item=group name=mainmenu?}
{?	if !$group->hide?}
{?		assign var='title' value='&nbsp;'?}
{?		assign var='href' value=''?}
{?		php?}
		$th = $this->get_template_vars('this');
		$group = $this->get_template_vars('group');
		$this->assign('is_selected', $th->objects["menu"]->group_selected->id == $group->id);
		$elementsCount = count($group->elements);
		if ($elementsCount) {
			reset($group->elements);
			$item = $group->elements[key($group->elements)];
			$subElements = $item->getChildren();
			if ($subElements && count($subElements)) {
				reset($subElements);
			}
			$item = $subElements
				? $subElements[key($subElements)]
				: $item;
			$this->assign('href',  URL_ROOT."/".$item->url);
			$this->assign('title', $elementsCount == 1 ? $item->title : $group->title);
			if ($elementsCount == 1) {
				$this->assign('data_id', "");
			} else {
				$this->assign('data_id', "menu-".$group->id);
			}
		}
{?		/php?}<li class="{?if $is_selected?}ui-tabs-selected ui-state-active{?/if?} {?if $group->id == "m99"?}collaboration{?/if?} {?if $group->id == "m01"?}home{?/if?}" {?if $data_id?}data-submenu-id="{?$data_id?}"{?/if?}><a href="{?$href|escape?}"><span>{?$title?} {? if (count($group->elements) > 1)?}<em class="arrow">&#9660;</em>{?/if?}</span></a>{?if (count($group->elements) > 1)?}<div class="submenu ui-helper-hidden {?if $is_selected?}submenu-selected{?/if?}" id="menu-{?$group->id?}">
	<div class="submenu-wrapper"><div class="submenu-gradient"></div><div class="submenu-wrapper">
		<ul>
			{?foreach from=$group->elements item=element name="menu"?}
			{?if $element->getChildren()?}
				<li class="item-with-submenu{?if $smarty.foreach.menu.first?} item-with-submenu-first{?/if?}"><ul>
					{?foreach from=$element->getChildren() item=subElement name="submenu"?}
					
					{?php?} if($isFilial) : {?/php?}					
						{?if $subElement->alt != 'hideFilial'?}					
							<li{?if $subElement->selected?} class="menu-current"{?/if?} {?if $subElement->id?}id="{?$subElement->id?}"{?/if?}>{?$subElement->alt?}<a href="{?$smarty.const.URL_ROOT|escape?}/{?$subElement->url|escape?}" {?if $subElement->target?}target="{?$subElement->target|escape?}"{?/if?}>{?if $subElement->title?}{?$subElement->title|escape?}{?else?}<del>{?t?}я здесь лишний{?/t?}</del>{?/if?}</a></li>					
						{?/if?}						
					{?php?} else : {?/php?}
						<li{?if $subElement->selected?} class="menu-current"{?/if?} {?if $subElement->id?}id="{?$subElement->id?}"{?/if?}><a href="{?$smarty.const.URL_ROOT|escape?}/{?$subElement->url|escape?}" {?if $subElement->target?}target="{?$subElement->target|escape?}"{?/if?}>{?if $subElement->title?}{?$subElement->title|escape?}{?else?}<del>{?t?}я здесь лишний{?/t?}</del>{?/if?}</a></li>					
					{?php?} endif; {?/php?}
						
					
					
					{?/foreach?}
				</ul></li>
			{?else?}
				<li{?if $element->selected?} class="menu-current"{?/if?}><a href="{?$smarty.const.URL_ROOT|escape?}/{?$element->url|escape?}">{?if $element->title?}{?$element->title|escape?}{?else?}<del>{?t?}я здесь лишний{?/t?}</del>{?/if?}</a></li>
			{?/if?}
			{?/foreach?}
		</ul>
	</div></div>
</div>{?/if?}</li>{?	/if?}{?* !$group->hide *?}{?/foreach?}