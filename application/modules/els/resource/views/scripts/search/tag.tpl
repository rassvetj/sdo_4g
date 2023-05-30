<?php $this->headLink()->appendStylesheet($this->serverUrl('/css/content-modules/kbase.css')); ?>
<?php $this->headLink()->appendStylesheet($this->serverUrl('/css/content-modules/material-icons.css')); ?>
<ol class="search-results">
<?php 
$count = 1;
foreach ( $this->items as $item ):
    echo $this->searchItem($item, $count, array(), array('tag', 'page'));
    $count ++;
endforeach;
?>
</ol>
<?php echo $this->listMassActions(array(
    'export' => array('formats' => array('excel'), 'params' => array('tag' => $this->tag)),
));?>
