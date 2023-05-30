<?php echo $this->form?>
<?php $this->inlineScript()->captureStart();?>
$(document).ready(function(){
    $('#auto_mark').change(function(){
        if ($(this).attr('checked')) {
            val = ($('#scale_id').val() == <?php echo HM_Scale_ScaleModel::TYPE_CONTINUOUS?>)
            $('#formula_id').attr('disabled', !val);
            $('#threshold').attr('disabled', val);
        }
    });
    
    $('#scale_id').change(function(){
        $('#auto_mark').attr('checked', false);
        $('#formula_id').attr('disabled', true);
        $('#threshold').attr('disabled', true);
    });
});
<?php $this->inlineScript()->captureEnd(); ?>