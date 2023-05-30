<?php if ($this->gridAjaxRequest): ?>
    <div id="grid-ajax">
        <?php if($this->treeajax == 'true'): ?>
            <?php echo $this->headActions?>
        <?php endif;?>
        <?php echo $this->grid?>
    </div>
<?php else: ?>
<?php echo $this->headSwitcher(array(
                                       'module' => 'course',
                                       'controller' => 'structure',
                                       'action' => 'index',
                                       'switcher' => 'edit',
                                       'subject_id' => (int) $this->subjectId,
                                       'course_id' => (int) $this->courseId,
                                       'owner' => $this->course->chain
    ), 'course');
?>
<?php $this->placeholder('columns')->captureStart('SET'); ?>
<div class="course-content-list">
    <div id="grid-ajax">
        <?php echo $this->headActions?>
        <?php echo $this->grid?>
    </div>
</div>
<?php $this->placeholder('columns')->captureEnd(); ?>
<?php $this->placeholder('columns')->captureStart(); ?>
<div class="course-content-tree">
    <?php
    
        $queryExpand = "function (flag, dtnode) {
            if (!flag) {
                $.post('".$this->url(array('module' => 'course', 'controller' => 'structure', 'action' => 'delete-tree-branch'), null, true )."', { key: dtnode.data.key});
            }
        }";
		$ltrimUrl = ltrim($this->url(array('module' => 'course', 'controller' => 'structure', 'action' => 'index', 'gridmod' => 'ajax', 'course_id' => $this->courseId, 'subject_id' => $this->subjectId, 'key' => ''), null, true), '/');
        echo $this->uiDynaTree(
            'course-content-tree',
            $this->htmlTree($this->tree, 'htmlTree'),
            array(
                'remoteUrl' => $this->url(array(
                    'module' => 'course',
                    'controller' => 'structure',
                    'action' => 'get-tree-branch'
                ), null, true),
                'title' => _('Структура'),
                'onActivate' => 'function (dtnode) {gridAjax("grid-ajax", "'.ltrim($this->url(array('module' => 'course', 'controller' => 'structure', 'action' => 'index', 'gridmod' => 'ajax', 'treeajax' => 'true', 'course_id' => $this->courseId, 'subject_id' => $this->subjectId, 'key' => ''), null, true), '/').'"+dtnode.data.key);dragdrop()}',
                'onPostInit'=>'function (dtnode) {dragdrop()}',
                'onExpand'=>'function (dtnode) {dragdrop()}',
          		'onPostInit'=>'function (dtnode) {dragdrop()}',
		        'onExpand'=>'function (dtnode) {dragdrop()}',
                'onQueryExpand' => $queryExpand,
                // block user interaction while loading child nodes
                'onClick' => 'function (dtnode, event) {if (dtnode.isLoading) { return false; }}',
                'onKeydown' => 'function (dtnode, event) { 
                	if (dtnode.isLoading && _.indexOf([37, 39, 187, 189], event.which) !== -1) { return false; } 
				}'
            )
        );
    ?>
</div>
<?php $this->placeholder('columns')->captureEnd(); ?>

<?php $this->inlineScript()->captureStart(); ?>

jQuery(document).bind('dynatreecreate', function (event) {	
    var $target = $(event.target)
        , dTree
        , active
        , current = <?php echo Zend_Json::encode($this->key); ?>;
    if ($target.is('#course-content-tree')) {
        dTree = $target.dynatree("getTree");
        active = dTree.getNodeByKey(current);
        if (!dTree.getActiveNode() && current && active) {
            active.activateSilently();
        }
    }
});
function dragdrop(){
	$(".dynatree-node").droppable({
		activeClass: "active",
		hoverClass: "hover",
		accept: ".draggable-item",
		drop: function(event,ui){
			var reg=/\d+/g
			var dropItemId = reg.exec(ui.draggable.children("a").attr("id"))
			var tParentNode = $.ui.dynatree.getNode(this);
			var activeNode = $("#course-content-tree").dynatree("getActiveNode");
			if(activeNode==null) activeNode = tParentNode.parent
			if(activeNode.data.key===tParentNode.data.key) {
				return false
			}else{		
				$.ajax({
					type: "POST",
					url: "<?php echo $this->serverUrl($this->url(array('module' => 'course', 'controller' => 'structure', 'action' => 'move')))?>",
					data: "parent="+tParentNode.data.key+"&child="+dropItemId,
					success: function(msg){
						if(msg.result===true){
							if(ui.draggable.children("span").is(".icon-folder")){
								window.location.reload();
							}else						
								gridAjax("grid-ajax", "<?php echo $ltrimUrl?>"+activeNode.data.key);							
						}else {							
							$(msg.message).errorbox({ level: 'error' });
						}
					}
				})							
			}		
		}			
	});	
}
$(function(){
   var dragOpts = {
		addClasses: false,
		appendTo: 'body',
		connectToDynatree: true,
		cursorAt: {left: 20, top: 10},
		helper: 'clone',
		zIndex: 2000,
		scroll: false
	}
	$(document).on("hover", ".draggable-item", function() {
		if (!$(this).data("init")) {
			$(this).data("init", true).draggable(dragOpts);
		}
	}); 		
})
<?php $this->inlineScript()->captureEnd(); ?>

<?php echo $this->partial('_columns.tpl', array(
    'columns' => $this->placeholder('columns')->getArrayCopy(),
    'classes' => 'subject-catalog',
    'type' => 'px'
)); ?>
<?php endif; ?>
