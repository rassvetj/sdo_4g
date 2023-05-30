<form method="POST">
<input type="hidden" name="group_id" value="<?php echo $this->group->getValue('group_id')?>"/>
<table class="main" cellpadding="0" cellspacing="0" border="0" width="100%">
<tr>
    <th colspan="2"><?php if ($this->action == 'new'):?><?php echo _('Добавить группу полей')?><?else:?><?php echo _('Редактировать группу полей')?><?php endif;?></th>
</tr>
<tr>
    <td><?php echo _('Название')?></td>
    <td><?php echo $this->formText('name', $this->group->getValue('name'))?></td>
</tr>
<tr>
    <td><?php echo _('Роли')?></td>
    <td>
        <input <?php if (!is_array($this->group->getValue('roles')) || !count($this->group->getValue('roles'))):?>checked<?php endif;?> onClick="if (this.checked) jQuery('.role').each(function(n, item) {item.checked = true; item.disabled = true}); else jQuery('.role').each(function(n, item) {item.checked = false; item.disabled = ''})" type="checkbox" value="0"/> <?php echo $this->escape(_('Все'))?>
        <?php foreach ($this->roles as $roleId => $roleName):?>
        <input class="role" <?php if (is_array($this->group->getValue('roles')) && in_array($roleId, $this->group->getValue('roles'))):?>checked<?php endif;?> type="checkbox" name="roles[]" value="<?php echo $roleId?>"/> <?php echo $this->escape($roleName)?>
        <?php endforeach;?>
    </td>
</tr>
</table>
<br />
<table class="main" cellpadding="0" cellspacing="0" border="0" width="100%">
<tr id="metadataItemsBegin">
    <th nowrap="nowrap">&nbsp;</th>
    <th><?php echo _('Название поля')?></th>
    <th><?php echo _('Тип')?></th>
    <th><?php echo _('Значения')?></th>
    <th><?php echo _('Публичное')?></th>
    <th><?php echo _('Обязательное')?></th>
    <th><?php echo _('Защищённое')?></th>
</tr>
<?php if ($this->items && count($this->items)):?>
    <?php $count = 1;?>
    <?php foreach($this->items as $item):?>
<tr>
    <td nowrap="nowrap">
        <a href="javascript:void(0)" onClick="jQuery(this).parent().parent().remove()"><img src="/images/icons/cancel.gif" title="<?php echo _('Удалить поле')?>"></a>
        <a href="javascript:void(0)" onClick="upItem(this)"><img src="/images/icons/up.gif" title="<?php echo _('Поднять')?>"></a>
        <a href="javascript:void(0)" onClick="downItem(this)"><img src="/images/icons/down.gif" title="<?php echo _('Опустить')?>"></a>
    </td>
    <td>
        <?php echo $this->formHidden("values[$count][item_id]", $item->item_id)?>
        <?php echo $this->formText("values[$count][name]", $item->name)?>
    </td>
    <td><?php echo $this->formSelect("values[$count][type]", $item->type, null, HM_Metadata_Item_ItemModel::getTypes())?></td>
    <td><?php echo $this->formText("values[$count][value]", $item->value)?></td>
    <td><?php echo $this->formCheckbox("values[$count][public]", 1, array('checked' => $item->public))?></td>
    <td><?php echo $this->formCheckbox("values[$count][required]", 1, array('checked' => $item->required))?></td>
    <td><?php echo $this->formCheckbox("values[$count][editable]", 1, array('checked' => $item->editable))?></td>
    <?php $count++;?>
</tr>
    <?php endforeach;?>
<?php endif;?>
<tr style="display: none;" id="blankItem">
    <td nowrap="nowrap">
        <a href="javascript:void(0)" onClick="jQuery(this).parent().parent().remove()"><img src="/images/icons/cancel.gif" title="<?php echo _('Удалить поле')?>"></a>
        <a href="javascript:void(0)" onClick="upItem(this)"><img src="/images/icons/up.gif" title="<?php echo _('Поднять')?>"></a>
        <a href="javascript:void(0)" onClick="downItem(this)"><img src="/images/icons/down.gif" title="<?php echo _('Опустить')?>"></a>
    </td>
    <td><?php echo $this->formText("values[{count}][name]")?></td>
    <td><?php echo $this->formSelect("values[{count}][type]", null, null, HM_Metadata_Item_ItemModel::getTypes())?></td>
    <td><?php echo $this->formText("values[{count}][value]")?></td>
    <td><?php echo $this->formCheckbox("values[{count}][public]", null, array('checked' => true))?></td>
    <td><?php echo $this->formCheckbox("values[{count}][required]", null, array('checked' => true))?></td>
    <td><?php echo $this->formCheckbox("values[{count}][editable]", null, array('checked' => false))?></td>
</tr>
<tr>
    <td colspan="7"><a href="javascript:void(0)" onClick="addItem()"><img src="/images/icons/add_shedule.gif" title="<?php echo _('Добавить поле')?>"></a></td>
</tr>
<tr>
    <td colspan="7" align="right"><?php echo $this->submitButton(_('Сохранить'))?></td>
</tr>
</table>
</form>
<?php
$this->inlineScript()->captureStart();
?>

function addItem(initial) {
	var count = (typeof initial == 'undefined')
		? (arguments.callee.count || 1)
		: initial;

	var html=jQuery('#blankItem').html();
	jQuery('#blankItem').before('<tr>'+String(html).replace(/{count}/g, count)+'</tr>');
	count++;

	arguments.callee.count = count;
}
function upItem(item) {
    var tr = jQuery(item).parent().parent();
    if (tr.prev().attr('id') != 'metadataItemsBegin') {
        tr.prev().before(tr.clone());
        tr.remove();
    }
}
function downItem(item) {
    var tr = jQuery(item).parent().parent();
    if (tr.next().attr('id') != 'blankItem') {
        var html = tr.html();
        tr.next().after(tr.clone());
        tr.remove();
    }
}

jQuery(function ($) {
	addItem(new Number('<?php echo $count?>'));
<?php if (!is_array($this->group->getValue('roles')) || !count($this->group->getValue('roles'))):?>
	jQuery('.role').each(function () {
		this.checked = true;
		this.disabled = true;
	});
<?php endif;?>
});
<?php
$this->inlineScript()->captureEnd();
?>