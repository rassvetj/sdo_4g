<div class="faq-post">

    <div class="faq-header">
        <div class="faq-controls">
            <?php if($this->isModerator):?>
            <a class="edit" href="<?php echo $this->url(array(
                'module' => 'faq',
                'controller' => 'list',
                'action' => 'edit',
                'faq_id' => $this->faq->faq_id
            ), null, true)?>"></a>
            <a class="delete" href="<?php echo $this->url(array(
                'module' => 'faq',
                'controller' => 'list',
                'action' => 'delete',
                'faq_id' => $this->faq->faq_id
            ), null, true)?>"></a>
            <?php endif;?>
        </div>
        <div class="faq-title"><?php echo $this->faq->question?></div>
    </div>
    
    <div class="spacer"></div>
    
    <div class="faq-body">
        <?php echo stripslashes($this->faq->answer);?>
    </div>

    <div class="spacer"></div>
</div>