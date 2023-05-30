<?php $idPrefix = $this->id('question'); ?>
<div id="<?= $idPrefix; ?>-message" class="error-box"></div>
<div id="<?= $idPrefix; ?>-form">
<?= $this->form_q?>
</div>
<div class="ajax-spinner-local"></div>

<?php $this->inlineScript()->captureStart()?>
(function () {
var formId = <?= Zend_Json::encode("{$idPrefix}-form"); ?>;
function sendOrder () {
    $('#' + formId).closest('.ui-portlet').addClass('ui-state-loading');

    var hwDetect = hm.core.ClassManager.require('hm.core.HardwareDetect').get();
	
    $.ajax(<?= Zend_Json::encode( $this->baseUrl($this->url(array('module' => 'student-certificate', 'controller' => 'index', 'action' => 'questionsend'))) ) ?>, {
        type: 'POST',
        global: false,
        data: {            
            fio: $('#'+ formId +' input[id="fio"]').val(),            
			
			email: $('#'+ formId +' input[id="email"]').val(),            
            
			question: $('#'+ formId +' textarea[id="question"]').val(),            
            
            systemInfo: hwDetect.getSystemInfo()
        }
    }).done(function (data) {		
        _.defer(function () {
            $('#' + formId).html(data);
        });
    }).fail(function () {
        var $message = jQuery("<div><?= _('Произошла ошибка. Попробуйте ещё раз'); ?></div>").appendTo('#' + formId);
        jQuery.ui.errorbox.clear($message);
        $message.errorbox({level: 'error'});
    }).always(function () {
        $('#' + formId).closest('.ui-portlet').removeClass('ui-state-loading');
        $('#' + formId)
            .prop('disabled', false)
            .find('input').prop('disabled', false);
    });
}

<? /*
$(document.body).delegate('#' + formId + ' *[id="refresh"]', 'click', function (event) {
    event.preventDefault();

    $('#' + formId).closest('.ui-portlet').addClass('ui-state-loading');
    var login = $('#' + formId + ' input[id="login"]').val();
    $.ajax(<?= Zend_Json::encode( $this->baseUrl($this->url(array('module' => 'student-certificate', 'controller' => 'index', 'action' => 'questionsend'))) ) ?>, {
        global: false
    }).done(function (data) {		
        $('#' + formId + ' input[id="password"]').val('');
        $('#' + formId).html(data);
        $('#' + formId + ' input[id="login"]').val(login);
    }).always(function () {
        $('#' + formId).closest('.ui-portlet').removeClass('ui-state-loading');
    });
});
*/ ?>

$(document.body).delegate('#' + formId + ' form', 'submit', _.debounce(function (event) {
    $('#' + formId)
        .prop('disabled', true)
        .find('input').prop('disabled', true);

    var $portletContent = $(this).closest('.ui-portlet-content');
    if ($portletContent.length) {
        $portletContent.find('.ajax-spinner-local').appendTo($portletContent.parent());
    }
	
    sendOrder();
}, 50));
$(document.body).delegate('#' + formId + ' form', 'submit', function(event) {
    event.preventDefault();
});

})();
<?php $this->inlineScript()->captureEnd()?>