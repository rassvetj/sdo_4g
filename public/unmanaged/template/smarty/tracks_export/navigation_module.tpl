{?if $data.content_type eq $smarty.const.CONTENT_COLLAPSED?}
{?/if?}

{?if $data.content_type eq $smarty.const.CONTENT_EXPANDED?}
<div id="toc" class="tree-view course-structure" url="">
{?$data.tree?}
</div>
{?/if?}