<?php $this->headLink()->appendStylesheet( $this->serverUrl('/css/infoblocks/materials-recent/materials-recent.css') )
                       ->appendStylesheet( $this->serverUrl('/css/content-modules/material-icons.css') ); ?>
<div class="materials-recent revisions">
    <?php if(count($this->revisions) > 0): ?>
    <ul>
        <?php foreach($this->revisions as $revision):?>
        <li class="material revision">
            <?php $num = count($this->revisions) - $i++;?>
            <a href="<?= $this->url(array('action' => 'index', 'num' => $num, 'resource_id' => $this->resource->resource_id, 'revision_id' => $revision->revision_id));?>" class="material-icon-small <?= $this->resource->getIconClass();?>"></a>
            <p class="number"><a href="<?= $this->url(array('action' => 'index', 'num' => $num, 'resource_id' => $this->resource->resource_id, 'revision_id' => $revision->revision_id));?>"><?=sprintf(_('Версия #%s'), $num);?></a></p>
        </li>
        <p class="created_by"><?php echo $this->users[$revision->created_by]?></p>
        <p class="date"><?php echo $revision->dateTime($revision->updated)?></p>
        <?php endforeach;?>
    </ul>
    <?php else:?>
    <p><?= _('Это первая версия ресурса, история изменений пуста.'); ?></p>
    <?php endif;?>
</div>
<?php if ($this->restoreable):?>
<?php $this->inlineScript()->captureStart();?>
$(function(){
   $('.number').hover(
        function () {
            var urlRestore = $(this).find("a:first").attr('href').replace('index/index', 'index/restore');
            var urlDelete = $(this).find("a:first").attr('href').replace('index/index', 'index/delete-revision');
            $(this).append($('<a href="' + urlRestore + '" class="restore" title="<?php echo _('Восстановить');?>"></a>'));
            $(this).append($('<a href="' + urlDelete + '" class="delete" title="<?php echo _('Удалить');?>"></a>'));
        },
        function () {
            $(this).find("a:last").remove();
            $(this).find("a:last").remove();
        }   
   ); 
   $(document).on('click', '.restore', function(){
       return confirm('<?php echo _('Вы действительно желаете восстановить данную версию, сделать её актуальной версией ресурса?');?>');
   });   
});
<?php $this->inlineScript()->captureEnd();?>
<?php endif;?>