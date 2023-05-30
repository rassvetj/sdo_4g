<?php $idPrefix = $this->id('reg'); ?>
<?php echo sprintf(_('%s - поля, обязательные для заполнения'), '<span style="color: red">*</span>')?>
<br>
<br>
<div id="<?= $idPrefix; ?>-form">
<?php echo $this->form?>
</div>
<?php $this->inlineScript()->captureStart()?>
(function () {
    var formId = <?= Zend_Json::encode("{$idPrefix}-form"); ?>;
    $(document.body).delegate('#' + formId + ' *[id="refresh"]', 'click', function (event) {
            event.preventDefault();
            // @todo: refresh    
        });
    });
})();
<?php $this->inlineScript()->captureEnd()?>

