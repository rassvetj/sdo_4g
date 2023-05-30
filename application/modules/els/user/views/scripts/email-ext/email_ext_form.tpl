<?php $idPrefix = $this->id('user_email_ext'); ?>
<div class="form-area-def">
	<div id="<?= $idPrefix; ?>-message" class="error-box"></div>
	<div  id="<?= $idPrefix; ?>-form">
		<?= $this->form?>
	</div>
	<div class="ajax-spinner-local"></div>
</div>

<?php $this->inlineScript()->captureStart()?>
(function () {
var formId = <?= Zend_Json::encode("{$idPrefix}-form"); ?>;
function saveEmail () {
    $('#' + formId).closest('.ui-portlet').addClass('ui-state-loading');

    var hwDetect = hm.core.ClassManager.require('hm.core.HardwareDetect').get();
	
    $.ajax(<?= Zend_Json::encode( $this->baseUrl($this->url(array('module' => 'user', 'controller' => 'email-ext', 'action' => 'save'))) ) ?>, {
        type: 'POST',
        global: false,
        data: {            
            email: $('#'+ formId +' input[id="email"]').val(),            
			
            systemInfo: hwDetect.getSystemInfo()
        }
    }).done(function (data) {		
        _.defer(function () {
            $('#' + formId).html(data);				
        });
    }).fail(function () {
        var $message = jQuery("<div><?= _('��������� ������. ���������� ��� ���'); ?></div>").appendTo('#' + formId);
        jQuery.ui.errorbox.clear($message);
        $message.errorbox({level: 'error'});
    }).always(function () {
        $('#' + formId).closest('.ui-portlet').removeClass('ui-state-loading');
        $('#' + formId)
            .prop('disabled', false)
            .find('input').prop('disabled', false);
    });
}

$(document.body).delegate('#' + formId + ' form', 'submit', _.debounce(function (event) {
    $('#' + formId)
        .prop('disabled', true)
        .find('input').prop('disabled', true);

    var $portletContent = $(this).closest('.ui-portlet-content');
    if ($portletContent.length) {
        $portletContent.find('.ajax-spinner-local').appendTo($portletContent.parent());
    }
	
    saveEmail();
}, 50));
$(document.body).delegate('#' + formId + ' form', 'submit', function(event) {
    event.preventDefault();
});

})();
<?php $this->inlineScript()->captureEnd()?>