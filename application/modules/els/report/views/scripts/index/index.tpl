<?php echo $this->form?>
<?php $this->headScript()->appendFile($this->serverUrl('/js/lib/jquery/jquery.form.js'))?>

<?php $this->headScript()->captureStart() ?>
$('#grid-ajax form').ajaxForm(
    {target: '#grid-ajax'}
);
<?php $this->headScript()->captureEnd() ?>