<?php if ($this->gridAjaxRequest): ?>
<?php echo _($this->grid)?>
<?php else: ?>
<?php $this->placeholder('columns')->captureStart('SET'); ?>
<div class="subject-catalog-list">
    <?php echo _($this->grid)?>
</div>
<?php $this->placeholder('columns')->captureEnd(); ?>
<?php $this->placeholder('columns')->captureStart(); ?>
<fieldset class="subject-catalog-tree">
<legend><?php echo _('Классификатор ресурсов');?></legend>
<br>
<?php echo $this->formSelect('type', $this->type, array('onChange' => 'window.location.href=("'.$this->baseUrl($this->url(array('module' => 'resource', 'controller' => 'catalog', 'action' => 'index','type' => 'typemark'))).'").replace("typemark",this.value)'), $this->types)?>
<div class="subject-catalog-categories">
    <?php
        echo $this->uiDynaTree(
            _('categories'),
            $this->htmlTree($this->tree, 'htmlTree'),
            array(
                'remoteUrl' => $this->url(array(
                    'module' => 'subject',
                    'controller' => 'catalog',
                    'action' => 'get-tree-branch'
                )),
                'title' => _('Классификаторы'),
                'onActivate' => 'function (dtnode) {
                    gridAjax("grid", ("'.ltrim($this->url(array('module' => 'resource', 'controller' => 'catalog', 'action' => 'index','gridmod' => 'ajax','type'=>$this->type,'classifier_id' =>'classifiermark')), '/').'").replace("classifiermark",dtnode.data.key));
                }',
                // block user interaction while loading child nodes
                'onClick' => 'function (dtnode, event) { if (dtnode.isLoading) { return false; } }',
                'onKeydown' => 'function (dtnode, event) { if (dtnode.isLoading && _.indexOf([37, 39, 187, 189], event.which) !== -1) { return false; } }'
            )
        );
    ?>
</div>
</fieldset>
<?php $this->placeholder('columns')->captureEnd(); ?>
<?php echo $this->partial('_columns.tpl', array(
    'columns' => $this->placeholder('columns')->getArrayCopy(),
    'classes' => 'subject-catalog',
    'type' => 'px'
)); ?>
<?php endif; ?>