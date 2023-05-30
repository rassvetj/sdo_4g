<?php if ($this->pageCount > 1): ?>
<!-- Previous page link -->
<?php if (isset($this->previous)): ?>
  <a href="<?php echo $this->url(array('page' => $this->previous, 'search_query' => $this->query)); ?>"><?php echo _('Предыдущая')?></a>&nbsp;
<?php endif; ?>

<!-- Numbered page links -->
<?php foreach ($this->pagesInRange as $page): ?>
<span class="page_links"><?php if ($page != $this->current): ?><a href="<?php echo $this->url(array('page' => $page, 'search_query' => $this->query)); ?>"><?php echo $page; ?></a><?php else: ?><?php echo $page; ?><?php endif; ?></span>
<?php endforeach; ?>

<!-- Next page link -->
<?php if (isset($this->next)): ?>
  &nbsp;<a href="<?php echo $this->url(array('page' => $this->next, 'search_query' => $this->query)); ?>"><?php echo _('Следующая')?></a>
<?php endif; ?>
<?php endif; ?>