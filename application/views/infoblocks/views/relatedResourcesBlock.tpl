<?php $this->headLink()->appendStylesheet( $this->serverUrl('/css/infoblocks/materials-recent/materials-recent.css') )
                       ->appendStylesheet( $this->serverUrl('/css/content-modules/material-icons.css') ); ?>
<div class="materials-recent">
    <?php if(count($this->relatedResources) > 0): ?>
    <ul>
        <?php foreach($this->relatedResources as $resource):?>
        <li class="material <?php if ($resource->status != HM_Resource_ResourceModel::STATUS_PUBLISHED):?>unpublished<?php endif;?>"><a href="<?= $this->url(array('action' => 'index', 'resource_id' => $resource->resource_id));?>" class="material-icon-small <?= $resource->getIconClass();?>"></a><a href="<?= $this->url(array('module' => 'resource', 'controller' => 'index', 'action' => 'index', 'resource_id' => $resource->resource_id));?>"><?= $resource->title;?></a></li>
        <?php endforeach;?>
    </ul>
    <?php else:?>
    <p><?= _('Данный ресурс не связан с другими ресурсами Базы знаний.'); ?></p>
    <?php endif;?>
</div>
<?php if ($this->editable): ?>
<ul>
	<li>
        <ul>
        	<li>
        	    <a href="<?php echo $this->url(array('module' => 'resource', 'controller' => 'related', 'action' => 'assign',  'resource_id' => $this->resource->resource_id));?>"><?php echo _('Настроить связанные ресурсы');?></a>
            </li>
        </ul>
    </li>
</ul>
<?php endif;?>
