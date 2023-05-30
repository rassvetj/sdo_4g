<?php
    $this->headLink()->appendStylesheet($this->baseUrl('css/content-modules/marksheet.css'));
    $this->headScript()->appendFile($this->baseUrl('js/lib/jquery/jquery.datasheet.js'));
?>
<form id="<?php echo $this->sheet->getId()?>-form" method="POST" action="<?php echo $this->sheet->getSaveUrl()?>">
<table id="<?php echo $this->sheet->getId()?>" cellspacing="0" class="main-grid" data-schedules="<?php echo $this->sheet->getHorizontalHeader()->getFieldsCount();?>" data-persons="<?php echo $this->sheet->getVerticalHeader()->getFieldsCount();?>">
    <colgroup>
        <?php if ($this->sheet->getVerticalHeader()->isCheckBoxEnabled()):?><col><?php endif;?>
        <col>
        <col span="<?php echo $this->sheet->getHorizontalHeader()->getFieldsCount() + 1?>">
    </colgroup>
    <thead>
        <tr class="marksheet-labels">
        <?php if (null !== $this->sheet->getVerticalHeader()):?>
            <?if ($this->sheet->getVerticalHeader()->isCheckboxEnabled()):?>
                <td class="first-cell" colspan="2">&nbsp;</td>
            <?php else:?>
                <td class="first-cell">&nbsp;</td>
            <?php endif;?>
        <?php endif;?>
        <?php foreach($this->sheet->getHorizontalHeader()->getFields() as $field):?>
            <td class="lesson-cell score-cell"><?php echo $field->getTitle()?></td>
        <?php endforeach;?>
        </tr>

        <?php if ($this->sheet->getHorizontalHeader()->isCheckboxEnabled()):?>
            <tr class="marksheet-head">
                <?if ($this->sheet->getVerticalHeader()->isCheckboxEnabled()):?>
                    <th class="marksheet-rowcheckbox first-cell"><?php echo $this->formCheckbox($this->sheet->getVerticalHeader()->getName().'_all')?></th>
                    <th><?php echo $this->sheet->getVerticalHeader()->getTitle()?></th>
                <?php else:?>
                    <th class="first-cell"><?php echo $this->sheet->getVerticalHeader()->getTitle()?></th>
                <?php endif;?>
                <?php foreach($this->sheet->getHorizontalHeader()->getFields() as $id => $field):?>
                    <td class="lesson-cell score-cell marksheet-colcheckbox"><?php echo $this->formCheckbox($this->sheet->getHorizontalHeader()->getName().'['.$id.']')?></td>
                <?php endforeach;?>
            </tr>
        <?php endif;?>
    </thead>
    <tbody>
        <?php $count = 1?>
        <?php foreach($this->sheet->getVerticalHeader()->getFields() as $verticalId => $verticalField):?>
            <tr class="<?php echo ($count %2 == 0) ? 'even' : 'odd'; if ($count == 1) echo ' first-row'; if ($count++ == $this->sheet->getVerticalHeader()->getFieldsCount()) echo ' last-row';?>">
                <td class="first-cell marksheet-rowcheckbox"><?php echo $this->formCheckbox($this->sheet->getVerticalHeader()->getName().'['.$verticalId.']')?></td>
                <td class="fio-cell"><?php echo $verticalField->getTitle()?></td>
                <?php foreach($this->sheet->getHorizontalHeader()->getFields() as $id => $field):?>
                      <td class="score-cell lesson-cell <?php if (null === $this->sheet->getValue($id, $verticalId)):?>no-score<?php endif;?>">
                          <?php if (null !== $this->sheet->getValue($id, $verticalId)):?>
                              <?php echo $this->sheet->getValue($id, $verticalId)->render()?>
                          <?php endif;?>
                      </td>
                <?php endforeach;?>
            </tr>
        <?php endforeach;?>
        <tr class="last-row ui-helper-hidden">
            <td class="first-cell" colspan="<?php if ($this->sheet->getVerticalHeader()->isCheckBoxEnabled()) echo 2; else echo 1;?>"></td>
            <td class="slider-cell" colspan="<?php echo $this->sheet->getHorizontalHeader()->getFieldsCount() ?>"><div id="marksheet-slider"></div></td>
        </tr>
    </tbody>
    <?php if ((null !== $this->sheet->getVerticalActions()) || (null !== $this->sheet->getHorizontalActions())):?>
    <tfoot>
        <tr>
            <td colspan="<?php $count = 3; if (!$this->sheet->getVerticalHeader()->isCheckboxEnabled()) $count = 2; echo $this->sheet->getHorizontalHeader()->getFieldsCount() + $count?>">
                <table cellspacing="0">
                    <col width="1"><col width="1"><col width="1"><col width="*"></colgroup>
                    <?php if (null !== $this->sheet->getVerticalActions()):?>
                    <tr class="<?php if (null !== $this->sheet->getHorizontalActions()):?>first-row<?php else:?>last-row<?php endif;?>">
                        <td class="first-cell"><?php echo $this->sheet->getVerticalActions()->getLabel()?>: </td>
                        <td>
                            <?php echo $this->formSelect('verticalMassAction', 'none', null, $this->sheet->getVerticalActions()->getActions());?>
                        </td>
                        <td class="button-cell">
                            <?php echo $this->formButton('verticalSubmitButton', _('Выполнить'), '');?>
                        </td>
                        <td class="last-cell" rowspan="2">
                            &nbsp;
                        </td>
                    </tr>
                    <?php endif;?>

                    <?php if (null !== $this->sheet->getHorizontalActions()):?>
                    <tr class="last-row">
                        <td class="first-cell"><?php echo $this->sheet->getHorizontalActions()->getLabel()?></td>
                        <td>
                            <?php echo $this->formSelect('horizontalMassAction', 'none', null, $this->sheet->getHorizontalActions()->getActions());?>
                        </td>
                        <td class="button-cell">
                            <?php echo $this->formButton('horizontalSubmitButton', _('Выполнить'), '');?>
                        </td>
                        <?php if (null === $this->sheet->getVerticalActions()):?>
                        <td>&nbsp;</td>
                        <?php endif;?>
                    </tr>
                    <?php endif;?>
                </table>
            </td>
        </tr>
    </tfoot>
    <?php endif;?>
</table>
</form>
<?php
$this->inlineScript()->captureStart();
?>
$('#<?php echo $this->sheet->getId()?>').datasheet({
    id: '<?php echo $this->sheet->getId()?>',
    url: {save: '<?php echo $this->sheet->getSaveUrl()?>'},
    l10n: {
        noVerticalActionSelected: "<?php echo $this->sheet->getMessage('noVerticalActionSelected')?>",
        noVerticalSelected: "<?php echo $this->sheet->getMessage('noVerticalSelected')?>",
        noHorizontalActionSelected: "<?php echo $this->sheet->getMessage('noHorizontalActionSelected')?>",
        noHorizontalSelected: "<?php echo $this->sheet->getMessage('noHorizontalSelected')?>",
        formError: "<?php echo $this->sheet->getMessage('formError')?>",
        ok: "<?php echo $this->sheet->getMessage('ok')?>",
        configm: "<?php echo $this->sheet->getMessage('configm')?>",
        areUshure: "<?php echo $this->sheet->getMessage('areUshure')?>",
        yes: "<?php echo $this->sheet->getMessage('yes')?>",
        no: "<?php echo $this->sheet->getMessage('no')?>"
    }
});
<?php
$this->inlineScript()->captureEnd();
?>