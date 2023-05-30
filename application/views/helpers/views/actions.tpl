<div class="dropdown-actions <?php if ($this->name): ?>dropdown-actions-<?php echo _($this->name);?><?php endif; ?> <?php if(empty($this->options)): ?>dropdown-actions-empty<?php endif; ?>">
    <span class="clicker"<?php if(!empty($this->options)): ?> title="<?php echo _("Открыть полный список действий"); ?>"<?php endif; ?>></span>
    <a <?php if ($this->main['class']): ?>class="<?php echo $this->main['class']; ?>"<?php endif; ?> <?php if ($this->main['target']): ?>target="<?php echo $this->main['target']; ?>"<?php endif; ?> href="<?php echo $this->baseUrl($this->main['url']);?>"><?php echo _($this->main['title']);?></a>
    <?php if ($this->options != null || !empty($this->options)): ?>
    <ul class="dropdown-actions-menu">
        <?php foreach($this->options as $option):?>
        <li><a href="<?php echo $this->baseUrl($option['url']); ?>" <?php if ($option['target']): ?>target="<?php echo $option['target']; ?>"<?php endif; ?> <?php if ($option['class']): ?>class="<?php echo $option['class']; ?>"<?php endif; ?>><?php echo _($option['title']); ?></a></li>
        <?php endforeach; ?>
    </ul>
    <?php endif; ?>
</div>
<?php
$this->inlineScript()->captureStart();
?>
$(document).undelegate('#container .dropdown-actions .clicker', 'click.dropdown-actions');
$(document).delegate('#container .dropdown-actions .clicker', 'click.dropdown-actions', function (event) {

    var $cTarget = $(event.currentTarget)
      , $menu
      , id = $cTarget.data('ddid');

    event.preventDefault();

    if ($cTarget.closest('.dropdown-actions').is('.dropdown-actions-empty')) {
        return;
    }

    $menu = $cTarget.closest('.dropdown-actions').find('ul.dropdown-actions-menu');

    if ($menu.length) {
        id = _.uniqueId('dropdown-actions-menu-');
        $cTarget.data('ddid', id);
        $menu.attr('id', id).hide();
    }

    $menu = $('#' + id);
    $menu.appendTo('body').toggle();

    if ($menu.is(':visible')) {
        $menu.position({
            my: 'left top',
            at: 'left bottom',
            of: $cTarget,
            offset: '0px 2px'
        });
    }

    $cTarget.closest('.dropdown-actions').toggleClass('dropdown-actions-active');
});
$(document).click(function (event) {
    var $target = $(event.target);
    if (!$target.closest('.dropdown-actions-menu, .dropdown-actions, .breadcrumbs').length) {
        $('body > .dropdown-actions-menu').hide();
        //$(".breadcrumbs .dropdown-actions-menu").hide();
        $('#container .dropdown-actions').removeClass('dropdown-actions-active');
        //$(".breadcrumbs .separator").removeClass("active_separate");
    }
});
<?php
$this->inlineScript()->captureEnd();
?>