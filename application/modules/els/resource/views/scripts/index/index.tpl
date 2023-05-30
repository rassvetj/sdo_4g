<div class="course-iframe-box">
    <iframe src="<?php echo $this->url(array(
					'module' => 'resource',
					'controller' => 'index',
					'action' => 'view',
					'resource_id' => $this->resourceId,
					'revision_id' => $this->revisionId
              )); ?>" name="item" id="course-iframe" frameborder="0" allowfullscreen>
        <?php echo _("Отсутствует поддержка iframe!"); ?>
    </iframe>
    <div class="course-navigation">
        <div class="gradient-me"></div>
        <div class="gradient-me-again"></div>
        <div class="gradient-hr"></div>
        <div class="wrapper"></div>
    </div>
</div>
<?php if ($this->resourceId): ?>
<script type="text/javascript">
var resource_id = <?php echo $this->resourceId;?>;
</script>
<?php endif;?>