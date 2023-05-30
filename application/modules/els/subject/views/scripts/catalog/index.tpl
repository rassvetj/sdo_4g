<?php if ($this->gridAjaxRequest): ?>
<?php echo $this->grid?>
<?php else: ?>
<?php $this->placeholder('columns')->captureStart('SET'); ?>
<div class="subject-catalog-list">
    <?php echo $this->grid?>
    <?php if (!$this->gridAjaxRequest):?>
        <?php echo $this->footnote();?>
    <?php endif;?>
</div>
<?php $this->placeholder('columns')->captureEnd(); ?>
<?php $this->placeholder('columns')->captureStart(); ?>
<fieldset class="subject-catalog-tree">
<legend><?php echo _('Классификатор учебных курсов');?></legend>
<br>
<?php echo $this->formSelect('type', $this->type, array('onChange' => 'window.location.href="'.$this->baseUrl($this->url(array('module' => 'subject', 'controller' => 'catalog', 'action' => 'index', 'item' => null, 'type' => ''))).'"+this.value'), $this->types)?>
<div class="subject-catalog-categories">
    <?php
        echo $this->uiDynaTree(
            'categories',
            $this->htmlTree($this->tree, 'htmlTree'),
            array(
                'remoteUrl' => $this->url(array(
                    'module' => 'subject',
                    'controller' => 'catalog',
                    'action' => 'get-tree-branch'
                )),
                'title' => _('Классификаторы'),
                'onActivate' => 'function (dtnode) {
                    gridAjax("grid", "'.ltrim($this->url(array('module' => 'subject', 'controller' => 'catalog', 'action' => 'index', 'gridmod' => 'ajax', 'classifier_id' => '')), '/').'"+dtnode.data.key);
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
<?php
if ($this->confirmID):
    $this->inlineScript()->captureStart();
?>
    $(document).ready(function(){
        if (confirm('<?php echo _('Начать прохождение курса?');?>')) {
            window.location ='<?php echo $this->url(array('module' => 'subject', 'controller' => 'index', 'action' => 'card', 'subject_id' => $this->confirmID),null,true);?>?page_id=m0602';
        }
    });
<?php
    $this->inlineScript()->captureEnd();
endif;
?>