<?php echo $this->form?>
<?php //@todo: create custom form element ?>
<?php $this->inlineScript()->captureStart(); ?>
    $(document).ready(function(){
        $('#weight-slider').bind('slidecreate', function(){showSliderValue()});
        $('#weight-slider').bind('slidestop', function(){showSliderValue()});
    });
    function showSliderValue() {
        $('.ui-slider-handle').html($('#weight-slider').slider('values', 0));
    }
<?php $this->inlineScript()->captureEnd(); ?>
