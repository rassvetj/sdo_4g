<?php
    if (!$this->isAjaxRequest):
?>
<h2 style="margin-bottom: 20px;"><?php echo $this->title ?></h2>
<?php
    endif;
?>
<?php
    echo $this->grid;
?>