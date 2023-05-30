<?php $cardId = $this->id('card-inline'); ?>
<div class="pcard pcard_inline pcard_noimage" id="<?php echo _($this->escape($cardId)) ?>">
    <?php echo $this->card(
    $this->item,
    $this->item->getCardFields(),
    array(
    'title' => _('Карточка элемента оргструктуры')
    ));
    ?>
</div>
<?php if (strlen(strip_tags(trim($this->item->info)))) :?>
<br>
<br>
<h2><?php echo _('Описание');?></h2>
<hr>
<div class="text-content">
    <?php echo _($this->item->info)?>
</div>
<?php endif; ?>

