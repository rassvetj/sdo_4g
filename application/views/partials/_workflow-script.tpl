<?php $this->headLink()->appendStylesheet($this->serverUrl('/css/content-modules/workflow.css')); ?>
<?php $this->inlineScript()->captureStart(); ?>
$(document).undelegate('.workflow-view');

$(document).on('click.workflow-view', function (event) {
    var $target = $(event.target);

    if ($target.closest('.ui-dialog, .ui-datepicker, .ui-widget-overlay').length) {
        return;
    }

    if ($target.closest('.workflow').length == 0 || $target.closest('.close').length != 0) {
        if (event._justAddedClass == null) {
            $('.grid-workflow-active').removeClass('grid-workflow-active');
        }
        $('.workflow').closest('.els-content').remove();
    }
});
$(document).delegate('.els-grid .grid-workflow', 'click.workflow-view', function (event) {
    var xhr = $(document).data('workflowXhr')
      , oldId = $(document).data('workflowUrl')
      , $target = $(this)
      , Id = $target.data('workflow_id');

    if (xhr != null && oldId != Id) {
        xhr.abort();
    }
    if (xhr == null || xhr.isResolved() || xhr.isRejected()) {
        $('.grid-workflow-active').removeClass('grid-workflow-active');
        $target.addClass('grid-workflow-active');
        event._justAddedClass = 'yes';
        xhr = $.ajax(<?= Zend_Json::encode($this->get_url) ?>, {
            type: 'GET',
            data: { index: Id }
        }).always(function () {
            $('.grid-workflow-active').removeClass('grid-workflow-active');
        }).done(function (msg) {
            var $msg;

            $msg = $('<div class="els-content">').appendTo('body').append(msg);
            $target.addClass('grid-workflow-active');
            $msg.find('.workflow')
                .find('.workflow_list').accordion({
                    header: '> .workflow_item > .workflow_item_head',
                    active: $msg.find('.workflow .workflow_list > .workflow_item.complete > .workflow_item_head')
                }).end()
                .find('select').each(function () {
                    $(this).selectmenu({
                        width: $(this).width(),
                        menuWidth: $(this).width() - 2
                    })
                }).end()
                .position({
                    my: 'left',
                    at: 'right',
                    of: $target,
                    collision: 'fit'
                });
            }).fail(function () {
            });

        $(document).data('workflowId', Id);
        $(document).data('workflowXhr', xhr);
    }
});

$(document).delegate(".workflow .workflow_list select, .workflow .workflow_list input, .workflow .workflow_list textarea", "change", function (event) {
    var currentTarget = $(this);
    <?php if (isset($this->confirm)): ?>
    elsHelpers.confirm(<?= Zend_Json::encode($this->confirm) ?>, <?= Zend_Json::encode($this->confirm_title) ?>).done(function () {
    <?php endif; ?>
    $.post(<?= Zend_Json::encode($this->submit_url) ?>, currentTarget.closest("form").serializeArray(), function () {
        $(document).trigger('click.workflow-view');
        window.location.href = window.location.href;
    });
    <?php if (isset($this->confirm)): ?>
    });
    <?php endif; ?>
});
<?php $this->inlineScript()->captureEnd(); ?>