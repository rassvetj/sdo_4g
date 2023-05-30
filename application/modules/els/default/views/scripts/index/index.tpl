<?php Zend_Registry::get('serviceContainer')->getService('Unmanaged')->getController()->setView('Document', 'document-widgets');?>

<?php echo $this->partial('_portlets.tpl', array(
    'roles'      => $this->roles,
    'role'       => $this->role,
    'blocks'     => $this->blocks,
    'isAdmin'    => $this->isAdmin,
    'isEditMode' => $this->isEditMode,
    'user'       => $this->user
)); ?>

<?php if ($this->isEditMode || $this->user): ?>
<?php $this->inlineScript()->captureStart(); ?>
yepnope({
    load: ['/js/lib/jquery/jquery-ui.portlets.js', '/css/application/interface/edit.css', '/js/application/interface/edit.js'],
    complete: function () { $(function () {
        $('<div class="interface-editor-enabler container-ear">')
            .css('top', $('#main > .tab-bar').outerHeight())
            .click(function () {
                createColumnsEditor(settings);
                $(this).detach();
            })
            .appendTo('#main');
    }); }
});
<?php $this->inlineScript()->captureEnd(); ?>
<?php endif; ?>

<?php if($this->user): ?>
	<script src="/themes/rgsu/js/common.js" charset="UTF-8"></script>
<?php endif;?>
