<?php echo $this->form?>
<?php echo $this->validateFormScript($this->url(array('action' => 'validate-form')), $this->form->getName())?>