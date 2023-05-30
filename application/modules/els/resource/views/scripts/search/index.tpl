<?php $this->headLink()->appendStylesheet($this->serverUrl('/css/content-modules/kbase.css')); ?>
<?php $this->headLink()->appendStylesheet($this->serverUrl('/css/content-modules/material-icons.css')); ?>
<div class="kbase_left">
<?php echo $this->partial('_search-form-simple.tpl', array('query' => $this->query));?>
</div>
<div class="clearfix"></div>
<?php if($this->error == false): ?>
<?php 
    $page = $this->paginator->getCurrentPageNumber()-1;
    $itemPerPage = $this->paginator->getDefaultItemCountPerPage();
    $i =0;
?>
<ol class="search-results" start="<?php echo $page * $itemPerPage + 1; // @todo: кажется оно depricated?>">
<?php    
    foreach($this->paginator as $key => $item){
        // здесь было много лишнего кода
        echo $this->searchItem($item['obj'], $page * $itemPerPage + (++$i), $this->words, array('search_query', 'page'));
               }
?>
</ol>
<?php echo $this->listMassActions(array(
    'pagination' => array($this->paginator, 'Sliding', 'search/controls.tpl', array('query' => $this->query)),
    'export' => array('formats' => array('excel'), 'params' => array('search_query' => $this->query)),
));?>
<?php else: ?>
<div><?php echo $this->error;?></div>
<?php endif;?>     