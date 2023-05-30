<?php if ($this->allow):?>
    <?php if ($this->news):?>
        <div id="last-news-block-accordion">
            <?php foreach($this->news as $news):?>
            <h3><a href="#"><table width="100%" border="0"><tr><td><?php echo strip_tags($news->announce)?></td><td nowrap valign="top" align="right"><?php echo date('d.m.Y H:i', strtotime($news->created))?></td></tr></table></a></h3>
            <div>
                <p>
                    <?php if (strlen($news->getFilteredMessage()) > 300):?>
                        <?php echo substr($news->getFilteredMessage(), 0, 300)?>... <a href="<?php echo $this->baseUrl($this->url(array('module' => 'news', 'controller' => 'view', 'action' => 'index', 'news_id' => $news->id)))?>"><?php echo _('Далее...')?></a>
                    <?php else:?>
                        <?php echo $news->getFilteredMessage()?>
                    <?php endif;?>
                </p>
            </div>
            <?php endforeach;?>
        </div>

        <?php $this->inlineScript()->captureStart()?>
        $(function () {
            $('#last-news-block-accordion').accordion({autoHeight: false, collapsible: true});
        });
        <?php $this->inlineScript()->captureEnd()?>
        <div class="bottom-links">
        	<hr />
			<a href="<?php echo $this->baseUrl($this->url(array('module' => 'news', 'controller' => 'index', 'action' => 'index')))?>?page_id=m00"><?php echo _('Все новости')?></a>
		</div>
    <?php else:?>
        <?php echo _('Отсутствуют данные для отображения')?>
    <?php endif;?>
<?php else:?>
    <?php echo sprintf(_('Сервис взаимодействия "%s" отключен на уровне портала.'), $this->serviceName)?>
<?php endif;?>