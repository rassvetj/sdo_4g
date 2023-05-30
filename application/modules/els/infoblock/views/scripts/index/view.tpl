<?php if ($this->name): ?>
<?php
    try {
        if (isset($this->content)) {
            echo $this->{$this->name}($this->title, $this->content, $this->attribs);
        } else {
            echo $this->{$this->name}($this->title, $this->attribs);
        }
    } catch (Exception $e) {
        Zend_Registry::get('log_system')->debug($e->getMessage().'\n'.$e->getTraceAsString());
        echo $this->ScreenForm($this->title, "<img src=\"".$this->serverUrl('/images/errors/500.png')."\"/>", array());
    }
?>
<?php else: ?>
<?php echo $this->ScreenForm(_("Виджет не найден"), _("Виджет не найден"), array()); ?>
<?php endif; ?>