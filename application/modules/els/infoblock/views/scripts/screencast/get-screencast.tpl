<?php if ($this->url): ?>
	<object codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,18,0" height="450" width="555">
		<embed autostart="false" src="<?php echo $this->url;?>" type="application/x-shockwave-flash" quality="high" movie="<?php echo $this->url;?>" allowfullscreen="true" height="450" width="555" allowFullScreen="true">
	</object>
<?php endif;?>
