<?php if ($this->gridAjaxRequest): ?>
    <div id="grid-ajax">

    </div>
<?php else: ?>
<?php // echo $this->headScript(); ?>
<?php $this->headStyle()->captureStart(); ?>
.orgstructure-list {
    max-width: 100%;
    overflow: auto;
}
<?php $this->headStyle()->captureEnd(); ?>
<?php $this->placeholder('columns')->captureStart('SET'); ?>
<div class="orgstructure-list">
    <div id="grid-ajax">

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
                )),
                'title' => _('Отчеты'),
                'onActivate' => 'function (dtnode) {
                    $("#grid-ajax").load("'.$this->serverUrl($this->url(array('module' => 'report', 'controller' => 'index', 'action' => 'index', 'report_id' => ''))).'"+dtnode.data.key);
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
<?php endif; ?>