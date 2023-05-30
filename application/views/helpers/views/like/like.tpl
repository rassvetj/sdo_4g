<span id="<?php echo $this->containerId ?>"></span>
<script>
    HM.create('hm.module.base.ui.like.Like', {
        renderTo:     '#<?php echo $this->containerId ?>',
        likeCount:    <?php echo $this->count_like ?>,
        dislikeCount: <?php echo $this->count_dislike ?>,
        itemType:     <?php echo $this->itemType ?>,
        itemId:       <?php echo $this->itemId ?>,
        vote:         <?php echo $this->vote ?>
    });
</script>