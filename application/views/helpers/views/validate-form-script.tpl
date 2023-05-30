<?php
$this->inlineScript()->captureStart();
?>
jQuery(function ($) {
    $("#<?php echo $this->name?> .form input").blur(function () {
        if (($(this).attr('type') != 'submit') && ($(this).attr('type') != 'button')) {
            var formElementId = $(this).parent().prev().find('label').attr('for');

            doValidation(formElementId);
        }
    });
});

function doValidation(id)
{
    var url = '<?php echo $this->action?>'
    var data = {};
    $("#<?php echo $this->name?> .form input").each(function()
    {
        if (($(this).attr('name') != 'captcha[input]') &&
           ($(this).attr('name') != 'captcha[id]')) {
            data[$(this).attr('name')] = $(this).val();
        }
    });
    $("#<?php echo $this->name?> #"+id).removeClass('invalid');
    $.post(url, data, function(resp) {
            $("#<?php echo $this->name?> #"+id).parent().find('.errors').remove();
            $("#<?php echo $this->name?> #"+id).parent().append(getErrorHtml(resp[id], id));
        }, 'json'
  	);
  	    	   
}

function getErrorHtml(formErrors , id)
{
    var o = '<ul id="errors-'+id+'" class="errors">';
    for(errorKey in formErrors)
    {
        $("#<?php echo $this->name?> #"+id).addClass('invalid');        
        o += '<li>' + formErrors[errorKey] + '</li>';
    }
    o += '</ul>';
    return o;
}
<?php
$this->inlineScript()->captureEnd();
?>