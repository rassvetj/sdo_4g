<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
	<title>iWebinar</title>
	<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
	<script type="text/javascript" src="<?=$this->serverUrl('/webinar/swfobject.js')?>"></script>
	<script type="text/javascript">
		swfobject.registerObject("webinar", "10", "<?=$this->serverUrl('/webinar/expressInstall.swf')?>");
	</script>
	
	<style type="text/css">
		body, html {
			padding: 0;
			margin: 0;
			width: 100%;
			height: 100%;
			overflow: hidden;
		}
		p {
			padding: 10px;
		}
	</style>
</head>
<body>
	
	<object id="webinar" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="100%" height="100%">
		<param name="movie" value="<?=$this->serverUrl('/webinar/fileViewer.swf')?>" />
		<param name="flashvars" value="pointId=<?=$this->pointId?>&amp;media=<?=$this->media?>&amp;server=<?=$this->server?>&amp;userId=<?=$this->userId?>" />
		<!--[if !IE]>-->
		<object type="application/x-shockwave-flash" data="<?=$this->serverUrl('/webinar/fileViewer.swf')?>" width="100%" height="100%">
			<param name="flashvars" value="pointId=<?=$this->pointId?>&amp;media=<?=$this->media?>&amp;server=<?=$this->server?>&amp;userId=<?=$this->userId?>" />
		<!--<![endif]-->
			<p><?php echo _('Для просмотра необходимо установить Adobe Flash Player <a href="http://www.adobe.com/go/getflashplayer">версии 10 и выше</a>.');?></p>
		<!--[if !IE]>-->
		</object>
		<!--<![endif]-->
	</object>
	
</body>
</html>