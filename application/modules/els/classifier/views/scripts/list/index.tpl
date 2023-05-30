
<?php if ($this->gridAjaxRequest): ?>
    <?php if ($this->gridmod):?>
        <?php echo $this->grid?>
    <?php else:?>
        <div id="classifier-list-grid">
            <?php echo $this->actions('classifier-list',
            array(
                array(
                    'title' => _('Создать рубрику'),
                    'url' => $this->url(array('module' => 'classifier', 'controller' => 'list', 'action' => 'new', 'parent' => $this->parent, 'type' => $this->type), null, true)
                ),
                array(
                    'title' => _('Импортировать рубрики из csv'),
                    'url' => $this->url(array('module' => 'classifier', 'controller' => 'import', 'action' => 'index', 'source' => 'csv', 'type' => $this->type), null, true)
                )
            ))?>
            <?php echo $this->grid?>
        </div>
    <?php endif;?>
<?else:?>
<?php $this->placeholder('columns')->captureStart('SET'); ?>
<div class="subject-catalog-list">
    <div id="classifier-list-grid">
        <?php echo $this->actions('classifier-list',
        array(
            array(
                'title' => _('Создать рубрику'),
                'url' => $this->url(array('module' => 'classifier', 'controller' => 'list', 'action' => 'new', 'parent' => $this->parent, 'type' => $this->type), null, true)
            ),
            array(
                'title' => _('Импортировать рубрики из csv'),
                'url' => $this->url(array('module' => 'classifier', 'controller' => 'import', 'action' => 'index', 'source' => 'csv', 'type' => $this->type), null, true)
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
                    'module' => 'classifier',
                    'controller' => 'ajax',
                    'action' => 'get-tree-branch'
                )),
                'title' => _('Классификаторы'),
                'onActivate' => 'function (dtnode) {
                    gridAjax("classifier-list-grid", "'.ltrim($this->url(array('module' => 'classifier', 'controller' => 'list', 'action' => 'index', 'type' => $this->type, 'parent' => ''), null, true), '/').'"+dtnode.data.key);
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