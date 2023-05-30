<?php if (1 || !$this->gridAjaxRequest): // здесь засада; если вызывают из tree - надо показывать actions; а если фильтруют таблицу - не надо; как быть..? не знаю.. ?>
<?php 
if($this->field == 'role') {
	$action = _('Создать группу страниц');
	$url = $this->url(array('module' => 'htmlpage', 'controller' => 'group', 'action' => 'new', 'role' => $this->key), null, true);
}
else {
	$action = _('Создать страницу');	
	$url = $this->url(array('module' => 'htmlpage', 'controller' => 'list', 'action' => 'new', 'group_id' => $this->key), null, true);
}
?>
<?php endif;?>
<?php if ($this->gridAjaxRequest || $this->field == 'page_id'): ?>
    <div id="htmlpage-list-grid">
        <?php if($this->addAction):?>
            <?php echo $this->actions('htmlpage-list',
            array(
                array(
                    'title' => $action,
                    'url' => $url
                )
            ))?>
        <?php endif;?>
        <?php echo $this->grid?>
    </div>
<?else:?>
<?php $this->placeholder('columns')->captureStart('SET'); ?>
<div class="subject-catalog-list">
    <div id="htmlpage-list-grid">
        <?php echo $this->actions('htmlpage-list',
        array(
            array(
                'title' => $action,
                'url' => $this->url(array('module' => 'htmlpage', 'controller' => 'list', 'action' => 'new', 'parent' => $this->parent, 'type' => $this->type), null, true)
            )
        ))?>
        <?php echo $this->grid?>
    </div>
</div>
<?php $this->placeholder('columns')->captureEnd(); ?>

<?php $this->placeholder('columns')->captureStart(); ?>
<div class="subject-catalog-categories">
    <?php
        echo $this->uiDynaTree(
            'categories',
            $this->htmlTree($this->tree, 'htmlTree'),
            array(
                'remoteUrl' => $this->url(array(
                    'module' => 'htmlpage',
                    'controller' => 'ajax',
                    'action' => 'get-tree-branch'
                )),
                'title' => _('Информационные страницы'),
                'onActivate' => 'function (dtnode) {
                    gridAjax("htmlpage-list-grid", "'.ltrim($this->url(array(
                    														'module' => 'htmlpage', 
                    														'controller' => 'list', 
                    														'action' => 'index', 
                    														'gridmod' => 'ajax', 
                    														'key' => ''), null, true), '/').'"+dtnode.data.key + "/type/" + dtnode.data.isFolder);
                }',
                // block user interaction while loading child nodes
                'onClick' => 'function (dtnode, event) { if (dtnode.isLoading) { return false; } }',
                'onKeydown' => 'function (dtnode, event) { if (dtnode.isLoading && _.indexOf([37, 39, 187, 189], event.which) !== -1) { return false; } }'
            )
        );
    ?>
</div>
<?php $this->placeholder('columns')->captureEnd(); ?>
<?php echo $this->partial('_columns.tpl', array(
    'columns' => $this->placeholder('columns')->getArrayCopy(),
    'classes' => 'subject-catalog',
    'type' => 'px'
)); ?>
<?php endif;?>