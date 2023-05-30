<?php if ($this->gridAjaxRequest): ?>
    <div id="grid-ajax">
        <?php if($this->treeajax == 'true'): ?>
            <?php echo $this->Actions('orgstructure', array(), array('parent' => $this->orgId));?>
        <?php endif;?>
        <?php echo $this->grid?>
    </div>
<?php else: ?>
<?php $this->placeholder('columns')->captureStart('SET'); ?>
<div class="orgstructure-list">
    <div id="grid-ajax">
        <?php echo $this->Actions('orgstructure', array(), array('parent' => $this->orgId));?>
       <?php echo $this->grid?>
    </div>
</div>
<?php $this->placeholder('columns')->captureEnd(); ?>
<?php $this->placeholder('columns')->captureStart(); ?>
<div class="orgstructure-tree">
    <?php
        echo $this->uiDynaTree(
            'orgstructure-tree',
            $this->htmlTree($this->tree, 'htmlTree'),
            array(
                'remoteUrl' => $this->url(array(
                    'module' => 'orgstructure',
                    'controller' => 'list',
                    'action' => 'get-tree-branch'
                ), null, true),
                'title' => _('Оргструктура'),
                'onActivate' => 'function (dtnode) {
                    gridAjax("grid-ajax", "'.ltrim($this->url(array('module' => 'orgstructure', 'controller' => 'list', 'action' => 'index', 'gridmod' => 'ajax', 'treeajax' => 'true', 'key' => ''), null, true), '/').'"+dtnode.data.key);
                }',
                // block user interaction while loading child nodes
                'onClick' => 'function (dtnode, event) { if (dtnode.isLoading) { return false; } }',
                'onKeydown' => 'function (dtnode, event) { if (dtnode.isLoading && _.indexOf([37, 39, 187, 189], event.which) !== -1) { return false; } }'
            )
        );
    ?>
</div>

<?php $this->inlineScript()->captureStart(); ?>
jQuery(document).bind('dynatreecreate', function (event) {
    var $target = $(event.target)
        , dTree
        , active
        , current = <?php echo Zend_Json::encode($this->orgId); ?>;
    if ($target.is('#orgstructure-tree')) {
        dTree = $target.dynatree("getTree");
        active = dTree.getNodeByKey(current);
        if (!dTree.getActiveNode() && current && active) {
            active.activateSilently();
        }
    }
});
<?php $this->inlineScript()->captureEnd(); ?>

<?php $this->placeholder('columns')->captureEnd(); ?>
<?php echo $this->partial('_columns.tpl', array(
    'columns' => $this->placeholder('columns')->getArrayCopy(),
    'classes' => 'subject-catalog',
    'type' => 'px'
)); ?>
<?php endif; ?>
