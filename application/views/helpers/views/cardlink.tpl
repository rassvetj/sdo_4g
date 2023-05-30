<?php
$cls = $this->cls[0];
if ($cls) {
    $this->cls[0] = $cls."-link";
}
$attribs = $this->htmlAttribsPrepare(
    array( 'class' => $this->cls ),
    array( 'class' => array('lightbox') )
);
?><a href="<?php echo $this->url?>"
    class="<?php echo $this->escape(implode(' ', $attribs['class'])) ?>"
    target="lightbox"
    rel="<?php echo $this->escape($this->rel) ?>"
    <?php if ($this->type == 'icon'): ?>title="<?php echo $this->escape($this->title); ?>"<?php endif; ?>><?php
    if ($this->type == 'icon') {
        echo $this->icon('card');
    } else if ($this->type == 'icon-custom') {
        echo $this->icon($this->iconType, $this->title, '', '', '', 'span');
    } else if ($this->type == 'html') {
        echo $this->title;
    } else if ($this->type == 'icon-and-text') {
        echo $this->icon('card').' <span>'.$this->escape($this->title).'</span>';
    } else if ($this->type == 'icon-and-html') {
        echo $this->icon('card').' <span>'.$this->title.'</span>';
    } else {
        echo '<span>'.$this->escape($this->title).'</span>';
    }
?></a><?php
$this->inlineScript()->captureStart() ?>
$(document)
    .undelegate('a.lightbox', 'mousedown.<?php echo $cls; ?>')
    .delegate('a.lightbox', 'mousedown.<?php echo $cls; ?>', function (event) {
        var currel = $(event.currentTarget).attr('rel')
          , $lb = $(event.currentTarget).closest('.lightbox');

        if (event.button == 2 || event.which == 2) { return; }
        event.preventDefault();
        $(event.currentTarget).lightdialog({
            title: $lb.attr('title') || $lb.text(),
            dialogClass: 'hm-dialog-prev-next ' + <?php echo Zend_Json::encode($cls); ?>,
            rel: "a.lightbox[rel='"+currel+"']",
            l10n: {
                prev: <?php echo Zend_Json::encode(_("Назад")) ?>,
                next: <?php echo Zend_Json::encode(_("Вперёд")) ?>
            }
        }).lightdialog("open");
    });
<?php $this->inlineScript()->captureEnd('cardlink'.($cls))?>