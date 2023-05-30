{?strip?}
{?assign var='separator' value='<span class="separator">&#0155;</span>'?}
{?php?}
$th = $this->get_template_vars('this');
$group = $th->objects["menu"]->group_selected;
$elementsCount = count($group->elements);
if ($elementsCount) {
	reset($group->elements);
	$item = ($elementsCount > 1) ? $group->elements[key($group->elements)] : $group;
	$this->assign('href',  URL_ROOT."/".$item->url);
}
{?/php?}
{?if $this->objects.menu->objects.user->profile_current && $this->objects.menu->group_selected->element_selected->title ?}
<a href="{?$smarty.const.URL_ROOT?}/">{?$this->objects.menu->objects.user->profile_current->alias?}</a>
{?if $this->objects.menu->group_selected?}
{?if $this->objects.menu->group_selected->title?}
&#0032;{?$separator?}&nbsp;<a href="{?$href?}">{?$this->objects.menu->group_selected->title?}</a>
{?/if?}
{?if $this->objects.menu->group_selected->title != $this->objects.menu->group_selected->element_selected->title ?}
&#0032;{?$separator?}&nbsp;<a href="{?$smarty.const.URL_ROOT?}/{?$this->objects.menu->group_selected->element_selected->url?}" title="{?$this->objects.menu->group_selected->element_selected->alt?}">{?$this->objects.menu->group_selected->element_selected->title|truncate:50:"..."?}</a>
{?/if?}
{?else?}
&#0032;{?$separator?}&nbsp;<span>{?t?}Главная{?/t?}</span>
{?/if?}

{?/if?}
{?/strip?}