<div class="card_content">

<?php if ($this->item):?>

<?php if(method_exists($this->item, 'getName') && !method_exists($this->item, 'getNameCyr') && !method_exists($this->item, 'getNameLat')):?>
	<div>
	<h6><?php echo $this->item->getName();?></h6>
	<hr/>
	</div>
<?php endif;?>

<?php if(method_exists($this->item, 'getNameCyr') && method_exists($this->item, 'getNameLat')):?>
    <div>
    <h6><?php echo $this->item->getNameCyr();?></h6>
    <?php if (strlen($this->item->getNameLat())):?>
        <h6><?php echo $this->item->getNameLat();?></h6>
    <?php endif;?>
    <hr/>
    </div>
<?php endif;?>

<table cellpadding="0" cellspacing="0" class="card" border="0">
<colgroup width="50%"></colgroup>
<colgroup width="50%"></colgroup>
<!--tr><th colspan="2"><?php echo $this->escape($this->attribs['title'])?></th></tr--->
<?php foreach($this->fields as $key => $title):?>
<?php if (is_array($title)) {$tooltip = $title['tooltip']; $title = $title['title']; } else $tooltip = false;?>
    <?php $data = ( !is_array($this->item->$key) )? $this->item->$key: $this->item->$key; ?>
    <?php if ($data == '-' || empty($data)) continue; ?>
    <?php if (strtolower($key) == 'description'): // @todo: придумать более правильный критерий?>
    <tr>
        <td colspan="2">		
		<div class="card_description">
			<strong><?php echo $this->escape($title)?>:</strong>
			<div><?php echo nl2br($data);?></div>
		</div>
	</td>
    </tr>
    <?php else : ?>
    <tr>
        <td>
            <?php if ($tooltip):?>
                <?php echo $this->tooltip($tooltip); ?>
            <?php endif;?>
            <strong><?php echo $this->escape($title)?>:</strong>
        </td>
        <td>
      		<div class="card-info-content">
            <?php if(is_array($this->item->$key)):?>
                <?php $urlParams = $this->item->$key; unset($urlParams['name']);?>
                <a href="<?php echo $this->baseUrl($this->url($urlParams, null, true))?>" target="_blank"><?php echo $data['name']; ?></a>
            <?php elseif (strpos($data, 'http://') === 0):?>
                <a href="<?php echo $data; ?>" target="_blank"><?php echo $data; ?></a>
            <?php else:?>
                <?php echo $data; ?>
            <?php endif;?>
            </div>
        </td>
    </tr>
    <?php endif;?>
<?php endforeach;?>
</table>
<br>

<?php if ($this->info):?>
<?php foreach($this->info as $key => $data):?>
    <table cellpadding="0" cellspacing="0" class="card" border="0">

        <?php if ($data['department']):?>
        <tr>
            <td><strong><?php echo _('Подразделение')?>:</strong></td>
            <td><?php echo $data['department']->name?></td>
        </tr>
        <?php endif;?>

        <tr>
        <td><strong><?php echo _('Должность')?>:</strong></td>
            <td><?php echo $data['post']->name?></td>
        </tr>

        <?php if (count($data['classifiers'])):?>
        <tr>
            <td><strong><?php echo _('Классификация'); // ВНИМАНИЕ!!! При мерже с билайном оставить там слово 'Функция'?>:</strong></td>
            <td>
            <?php foreach($data['classifiers'] as $key => $classifiers):?>
                <?php echo $classifiers[0]->name?>
            <?php endforeach;?>
            </td>
        </tr>
        <?php endif;?>

    </table>
    <br>

<?php endforeach;?>
<?php endif;?>

<?php endif;?>
</div>