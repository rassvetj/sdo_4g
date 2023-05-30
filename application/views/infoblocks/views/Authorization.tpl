<?php $idPrefix = $this->id('auth'); ?>
<div>
<p class="disclaimer"><?= _('Для доступа в закрытый раздел системы необходимо ввести логин и пароль.')?></p>
<div id="<?= $idPrefix; ?>-message" class="error-box"></div>
<div id="<?= $idPrefix; ?>-form">
    <?= $this->form?>
</div>
<hr>
<div class="footer">
<?php if (Zend_Registry::get('serviceContainer')->getService('Option')->getOption('regDeny') !== '1'): ?>
<a href="<?= $this->baseUrl('user/reg/self')?>"><?= _('Зарегистрироваться')?></a>
<?php endif;?>
	<!--<a href="<?= $this->baseUrl('remember')?>"><?= _('Восстановить пароль')?></a>-->
</div>
</div>
<div class="ajax-spinner-local"></div>

<?php $this->inlineScript()->captureStart()?>
(function () {
var formId = <?= Zend_Json::encode("{$idPrefix}-form"); ?>;
function authorizeMe () {
    $('#' + formId).closest('.ui-portlet').addClass('ui-state-loading');

    var hwDetect = hm.core.ClassManager.require('hm.core.HardwareDetect').get();

    $.ajax(<?= Zend_Json::encode( $this->baseUrl($this->url(array('module' => 'default', 'controller' => 'index', 'action' => 'authorization'))) ) ?>, {
        type: 'POST',
        global: false,
        data: {
            start_login: 1,
            captcha: {
                id: $('#' + formId + ' input[id="captcha-id"]').val(),
                input: $('#'+ formId +' input[id="captcha-input"]').val()
            },
            login: $('#'+ formId +' input[id="login"]').val(),
            password: $('#'+ formId +' input[id="password"]').val(),
            remember: Number($('#'+ formId +' input[id="remember"]').prop('checked')),
            systemInfo: hwDetect.getSystemInfo()
        }
    }).done(function (data) {
        $('#password').val('');
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

$(document.body).delegate('#' + formId + ' *[id="refresh"]', 'click', function (event) {
    event.preventDefault();

    $('#' + formId).closest('.ui-portlet').addClass('ui-state-loading');
    var login = $('#' + formId + ' input[id="login"]').val();
    $.ajax(<?= Zend_Json::encode( $this->baseUrl($this->url(array('module' => 'default', 'controller' => 'index', 'action' => 'authorization'))) ) ?>, {
        global: false
    }).done(function (data) {
        $('#' + formId + ' input[id="password"]').val('');
        $('#' + formId).html(data);
        $('#' + formId + ' input[id="login"]').val(login);
    }).always(function () {
        $('#' + formId).closest('.ui-portlet').removeClass('ui-state-loading');
    });
});
$(document.body).delegate('#' + formId + ' form', 'submit', _.debounce(function (event) {
    $('#' + formId)
        .prop('disabled', true)
        .find('input').prop('disabled', true);

    var $portletContent = $(this).closest('.ui-portlet-content');
    if ($portletContent.length) {
        $portletContent.find('.ajax-spinner-local').appendTo($portletContent.parent());
    }

    authorizeMe();
}, 50));
$(document.body).delegate('#' + formId + ' form', 'submit', function(event) {
    event.preventDefault();
});

})();
<?php $this->inlineScript()->captureEnd()?>
