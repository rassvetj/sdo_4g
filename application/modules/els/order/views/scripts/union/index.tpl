<script type="text/javascript" src="http://my.ru/js/lib/jquery/jquery-ui.lightdialog.js" charset="UTF-8"></script>
<script>
    $(function() {
        $( "#dialog-confirm" ).dialog({
            resizable: false,
			autoOpen: false,
            height:160,
            modal: true,
            buttons: {
                "да": function() {
                    $( this ).dialog( "close" );
					$('#target').submit(); 
					//window.location.href = "http://my.ru/order/union/index/gridmod//MID/79/dublicate/79";
                },
                "нет": function() {
                    $( this ).dialog( "close" );
                }
            }
        });
		$( "#unionDublicate" ).click(function() {
            $( "#dialog-confirm" ).dialog( "open" );
            return false;
        });
    });
    </script>
<div id="dialog-confirm" title="Подтверждение действия">
    <p><span style="float: left; margin: 0 7px 20px 0;"></span>Вы действительно желаете сопоставить данную заявку с уже имеющейся учетной записью пользователя? При этом новая учетная запись будет удалена</p>
</div>	
<?php /*44-outsource.hypermethod.com*/ echo "<br><br><div style='float:left; '><p style='font-size:14px; font-weight:bold;'>Старая учетная запись</p><br>".$this->formUnical."</div>"; 
echo "<div style='margin-left:480px;'><p style='font-size:14px; font-weight:bold;'>Новая учетная запись</p><br>".$this->formDublicate."</div>";?>
<br><br>
<!--<a href="http://my.ru/union/union/index/dublicate/<?=$this->midDublUser?>/unical/<?=$this->mibUnicUser?>" style='background:url(/images/save.png) no-repeat; 
padding-left: 120px; padding-top: 28px;'
    data-confirm="{&quot;ok&quot;:&quot;\u0414\u0430&quot;,&quot;cancel&quot;:&quot;\u041d\u0435\u0442&quot;,&quot;text&quot;:&quot;\u0412\u044b \u0434\u0435\u0439\u0441\u0442\u0432\u0438\u0442\u0435\u043b\u044c\u043d\u043e \u0436\u0435\u043b\u0430\u0435\u0442\u0435 \u0441\u043e\u043f\u043e\u0441\u0442\u0430\u0432\u0438\u0442\u044c \u0434\u0430\u043d\u043d\u0443\u044e \u0437\u0430\u044f\u0432\u043a\u0443 \u0441 \u0443\u0436\u0435 \u0438\u043c\u0435\u044e\u0449\u0435\u0439\u0441\u044f \u0443\u0447\u0435\u0442\u043d\u043e\u0439 \u0437\u0430\u043f\u0438\u0441\u044c\u044e \u043f\u043e\u043b\u044c\u0437\u043e\u0432\u0430\u0442\u0435\u043b\u044f? \u041f\u0440\u0438 \u044d\u0442\u043e\u043c \u043d\u043e\u0432\u0430\u044f \u0443\u0447\u0435\u0442\u043d\u0430\u044f \u0437\u0430\u043f\u0438\u0441\u044c \u0431\u0443\u0434\u0435\u0442 \u0443\u0434\u0430\u043b\u0435\u043d\u0430&quot;,&quot;title&quot;:&quot;\u041f\u043e\u0434\u0442\u0432\u0435\u0440\u0436\u0434\u0435\u043d\u0438\u0435 \u0434\u0435\u0439\u0441\u0442\u0432\u0438\u044f&quot;}">
<br></a>-->

   
   
	