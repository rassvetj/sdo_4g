<div class="_grid_gridswitcher">		
	<div  class="ending _u_selected"><?=_('Стандартное отображение')?></div>
	<a href="<?=$this->url(array('module' => 'default', 'controller' => 'list', 'action' => 'current'));?>" ><div style="text-decoration: underline;" onclick="$('._grid_gridswitcher div').removeClass('_u_selected');$(this).addClass('_u_selected')" class="ending"><?=_('Все уведомления')?></div></a>
	<a href="<?=$this->url(array('module' => 'default', 'controller' => 'list', 'action' => 'deleted'));?>" ><div style="text-decoration: underline;" onclick="$('._grid_gridswitcher div').removeClass('_u_selected');$(this).addClass('_u_selected')" class="ending"><?=_('Удаленные уведомления')?></div></a>
</div>
<br />
<br />
<div id="hm-es-event-list-container">

</div>
<script>
    $(function() {
        HM.create('hm.module.es.ui.event.panel.EventListPanel', {
            renderTo: '#hm-es-event-list-container'
        });
    });
</script>