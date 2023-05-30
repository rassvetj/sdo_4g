<div class="portlets-editor hgll-columns columns"><div class="hgll-colmask hgll-colwrap-outer"><div class="hgll-colwrap-inner">
    <div class="hgll-col1wrap"><div class="hgll-col1 column">

    <?php $blocks = (!$this->columns || empty($this->columns)) ? array() : $this->columns; ?>
    <?php if (count($blocks) === 1) array_push($blocks, array()); ?>
    <?php $this->placeholder('columns')->exchangeArray(array()); ?>
    <?php foreach($blocks as $value):?>
        <?php $this->placeholder('columns')->captureStart(); ?>
        <?php
            foreach($value as $val){
                $val['attribs']['data-description'] = $val['content'];
                $val['attribs']['data-title'] = $val['title'];
                echo $this->{$val['block']}($val['title'], $val['content'], $val['attribs']);
            }
        ?>
        <?php $this->placeholder('columns')->captureEnd(); ?>
    <?php endforeach;?>
    <?php echo $this->partial('_columns.tpl', array(
        'columns' => $this->placeholder('columns')->getArrayCopy(),
        'classes' => 'draggable-area-2columns'
    )); ?>

    </div></div>
    <div class="hgll-col2 column" id="infoblocks-list">
    <fieldset id="fieldset-infoblocks-list">
    <legend><?php echo _("Все блоки"); ?></legend>
        <?php echo $this->htmlTree($this->infoblocks, 'infoblockEdit'); ?>
    </fieldset>
    <div class="fieldset-infoblocks-buttons">
    <input type="submit" name="button" id="portlets-ready" onclick="window.location.href='/';" value="Готово" class="ui-button ui-widget ui-state-default ui-corner-all" role="button" aria-disabled="false">
    <input type="submit" name="button" id="portlets-delete-all" value="Очистить всё" class="ui-button ui-widget ui-state-default ui-corner-all" role="button" aria-disabled="false">
    </div>
    </div>
</div></div></div>
<?php $this->inlineScript()->captureStart(); ?>
yepnope({
    test: $.ui.portlets && window.initPortletsEditor,
    nope: ['/js/lib/jquery/jquery-ui.portlets.js', '/js/application/interface/edit/portlets.js'],
    complete: function () { $(function () {
        _.defer(function () {
        initPortletsEditor({
                draggableHtmlStub: <?php echo Zend_Json::encode( $this->screenForm("title", "content", array('id' => '')) ); ?>,
                uploadUrl: "<? echo  str_replace( "\"", "\\\"", $this->url(array('action'=>$this->updateAction)) ); ?>",
            l10n: {
                del: "<?php echo _("Удалить"); ?>"
            }
        });
        });
    }); }
});
<?php $this->inlineScript()->captureEnd('initportletseditor'); ?>