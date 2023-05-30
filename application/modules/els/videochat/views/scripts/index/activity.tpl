	<script type="text/javascript" src="<?=$this->baseUrl('webinar/swfobject.js')?>"></script>
	<script type="text/javascript">
		swfobject.registerObject("webinar", "10", "<?=$this->baseUrl('webinar/expressInstall.swf')?>");
	</script>
	<object id="webinar" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="100%" height="800">
		<param name="movie" value="<?=$this->baseUrl('videochat/videochat.swf')?>" />
		<param name="flashvars" value="pointId=<?=$this->pointId?>&amp;media=<?=$this->media?>&amp;server=<?=$this->server?>&amp;userId=<?=$this->userId?>" />
		<!--[if !IE]>-->
		<object type="application/x-shockwave-flash" data="<?=$this->baseUrl('videochat/videochat.swf')?>" width="100%" height="800">
			<param name="flashvars" value="pointId=<?=$this->pointId?>&amp;media=<?=$this->media?>&amp;server=<?=$this->server?>&amp;userId=<?=$this->userId?>" />
		<!--<![endif]-->
			<p>Для просмотра необходимо установить Adobe Flash Player <a href="http://www.adobe.com/go/getflashplayer">версии 10 и выше</a>.</p>
		<!--[if !IE]>-->
		</object>
		<!--<![endif]-->
	</object>
