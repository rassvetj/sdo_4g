<?php $this->headScript()->appendFile($this->serverUrl('/webinar/swfobject.js'))?>
<?php $this->jQuery()->addOnLoad('swfobject.registerObject("webinar", "10", "'.$this->serverUrl("/webinar/expressInstall.swf").'");')?>

<object id="webinar" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="100%" height="100%">
    <param name="movie" value="<?=$this->serverUrl('/webinar/webinar.swf')?>" />
    <param name="flashvars" value="pointId=<?=$this->pointId?>&amp;media=<?=$this->media?>&amp;server=<?=$this->server?>&amp;userId=<?=$this->userId?>" />
    <!--[if !IE]>-->
    <object type="application/x-shockwave-flash" data="<?=$this->serverUrl('/webinar/webinar.swf')?>" width="100%" height="100%">
        <param name="flashvars" value="pointId=<?=$this->pointId?>&amp;media=<?=$this->media?>&amp;server=<?=$this->server?>&amp;userId=<?=$this->userId?>" />
    <!--<![endif]-->
        <p>Для просмотра необходимо установить Adobe Flash Player <a href="http://www.adobe.com/go/getflashplayer">версии 10 и выше</a>.</p>
    <!--[if !IE]>-->
    </object>
    <!--<![endif]-->
</object>
