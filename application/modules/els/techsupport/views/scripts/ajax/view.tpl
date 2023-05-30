<div class="request-view" >
    <div>
        <label><?=_('Тема')?></label>
    </div>
    <div>
        <span><?=$this->request->theme?></span>
    </div>
    <div>
        <label><?=_('Описание проблемы')?></label>
    </div>
    <div>
        <span><?=$this->request->problem_description?></span>
    </div>
    <div>
        <label><?=_('Ожидаемый результат')?></label>
    </div>
    <div>
        <span><?=$this->request->wanted_result?></span>
    </div>
    <div>
        <a href="<?=$this->viewPageUrl?>">
            <?=_('Войти от имени пользователя и посмотреть страницу с ошибкой');?>
        </a>
    </div>
</div>