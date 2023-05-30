
<?php if(!$this->success) {
    echo $this->form;
}
else { ?>
 <script type="text/javascript">
    window.top.$('#callback-form').dialog("close");
 </script>
<?php
}
?>
