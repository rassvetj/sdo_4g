<li class="material">
    <div class="title">
        <?php if (1): // проверка для ресурсов у которых опубликована только карточка?>
            <a href="<?= $this->url($this->item->getViewUrl());?>"><?= $this->item->getName();?></a>
        <?php else: ?>
            <p><?php echo $this->item->getName();?></p>
        <?php endif; ?>
        <?php if (count($this->item->tag)):?>
        <span class="keywords">
            <?php foreach ($this->item->tag as $tag):?>
                <?php if (is_a($this->item, 'HM_Resource_ResourceModel') && ($tag->item_type != HM_Tag_Ref_RefModel::TYPE_RESOURCE)) continue;?>
                <?php if (is_a($this->item, 'HM_Course_CourseModel') && ($tag->item_type != HM_Tag_Ref_RefModel::TYPE_COURSE)) continue;?>
                <span class="keyword"><?php echo $tag->body;?></span>
            <?php endforeach;?>
        </span>
        <?php endif;?>
    </div>
    <div class="clearfix"></div>
    <div class="icon-wrapper"><?php 
        echo $this->cardLink(
                $this->url($this->item->getCardUrl()),
                _('Карточка'),
                'icon-custom',
                'pcard',
                'pcard',
                'material-icon ' . $this->item->getIconClass()
            );    
    ?>
    </div>
    <div class="data-wrapper">
        <p class="url"><a href="<?= $this->url(array_merge($this->unsetParams, $this->item->getViewUrl()));?>"><?php echo $this->serverUrl() . $this->url(array_merge($this->unsetParams, $this->item->getViewUrl())); // @todo: здесь надо unset'ить вообще все параметры расширенной формы?></a></p>
        <p class="date"><?php echo $this->item->getCreateUpdateDate();?></p>
    </div>
    <div class="clearfix"></div>
	<div class="description"><?php echo nl2br($this->item->description);?></div>
</li>