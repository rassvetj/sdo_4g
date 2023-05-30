<?php if ($this->pageCount > 1): ?>
<div class="news-pagination">
    <div class="container left">
    <!-- Previous page link -->
    <?php if (isset($this->previous)): ?>
      <a class="arrows" href="<?php echo $this->url(array('page' => $this->previous)); ?>">&lt; </a>
      <a href="<?php echo $this->url(array('page' => $this->previous)); ?>"><?php echo _('Предыдущие') ?></a>
    <?php else: ?>
      <span class="arrows">&lt; </span><span class="disabled"><?php echo _('Предыдущие') ?></span>
    <?php endif; ?>
    </div>
 
     <div class="container center">
    <!-- Numbered page links -->
    <?php foreach ($this->pagesInRange as $page): ?>
      <?php if ($page != $this->current): ?>
        <a href="<?php echo $this->url(array('page' => $page)); ?>"><?php echo $page; ?></a>&nbsp;
      <?php else: ?>
        <?php echo $page; ?>&nbsp;
      <?php endif; ?>
    <?php endforeach; ?>
    </div>
 
     <div class="container right">
    <!-- Next page link -->
    <?php if (isset($this->next)): ?>
      <a href="<?php echo $this->url(array('page' => $this->next)); ?>"><?php echo _('Следующие') ?></a>
      <a class="arrows" href="<?php echo $this->url(array('page' => $this->next)); ?>"> &gt;</a>
    <?php else: ?>
      <span class="disabled"><?php echo _('Следующие') ?></span><span class="arrows"> &gt;</span>
    <?php endif; ?>
    </div>

</div>
<?php endif; ?>