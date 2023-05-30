<!DOCTYPE html>
<html>
	<head>
		<title></title>
		<meta charset="UTF-8">
		<?php echo $this->headLink(); ?>
		<?php echo $this->headStyle()?>
		<script src="<?php echo $this->escape($this->serverUrl('/js/lib/json2.min.js')) ?>"></script>
		<script src="<?php echo $this->escape($this->serverUrl('/js/lib/jquery/jquery-1.7.2.min.js')) ?>"></script>
		<script src="<?php echo $this->escape($this->serverUrl('/js/lib/underscore-1.3.3.min.js')) ?>"></script>
		<?php echo $this->headScript(); ?>
	</head>
	<body style="margin: 0;">
		<?php echo $this->layout()->content ?>
		<?php echo $this->inlineScript() ?>
		<?php echo $this->jQuery() ?>
	</body>
</html>
