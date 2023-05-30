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
		createColumnsEditor(settings);
	}); }
});
<?php $this->inlineScript()->captureEnd(); ?>
<?php endif; ?>
