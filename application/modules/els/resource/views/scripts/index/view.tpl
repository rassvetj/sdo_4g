<div class="formatted-text resource-type-<?php echo $this->type;?>">
<?php echo $this->content; ?>
</div>

<?php if($this->iframe_download):?>
<style type="text/css">

  .iframe_download {
    height: 100%;
    position: absolute;
    top: 0;
    left:-8px;
    width: 100%;
  }
  .iframe_download_bckg {
    background: none repeat scroll 0 0 #FFFFFF;
    height: 100%;
    opacity: 0.6;
    position: absolute;
    top: 0;
    width: 100%;
    filter: alpha(opacity=60);
  }

  .iframe_download_a {
    box-sizing: border-box;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    height: 100%;
    padding-top: 16em;
    vertical-align: middle;
  }

  .iframe_download_a a.button {
    background: url(/images/download<?php echo ($_COOKIE[HM_User_UserService::COOKIE_NAME_LANG] !== HM_User_UserService::DEFAULT_LANG) ? '_eng' : ''?>.gif) repeat scroll 0 0 transparent;
    display: block;
    height: 107px;
    margin: auto;
    opacity: 1;
    position: relative;
    vertical-align: middle;
    width: 107px;
  }

  .iframe_download_a a.text {
    display: block;
    min-height: 20px;
    line-height: 20px;
    opacity: 1;
    margin: 0 15px;
    opacity: 1;
    position: relative;
    vertical-align: middle;
    text-align: center;
    word-wrap: break-word;
  }

  .iframe_download_a a:link,
  .iframe_download_a a:active,
  .iframe_download_a a:visited,
  .iframe_download_a a:hover {
    color: #555;
  }

  .card_content {
    padding: 5px 15px;
  }
  .card_content, a {
    font-family: "PT Sans",verdana,sans-serif;
    font-size: 0.75em;
  }
  .card_content a {
    color: #455B81;
  }
  .card_content a:hover {
    color: #334460;
  }
  .card_content h6 {
    margin: 10px 0px 20px 0px;
    font-size: 2em;
  }
  .card_content hr {
    display: none;
  }
  .card_content strong {
    font-weight:bold;
  }
</style>
<div class='iframe_download'>
	<div class="iframe_download_bckg"></div>
	<div class="iframe_download_a">
		<a id="linkToFile" class="button" href='<?php echo $this->escape($this->url); ?>'></a>

        <?php if ($this->filelist):?>
        <?php echo $this->filelist;?>
        <?php endif;?>

	</div>
</div>
<script type="text/javascript">
$(function(){
	var href= $(".card tr").eq(0).children("td").children("a").attr("href");
	$('#linkToFile').attr("href",href);
})
</script>
<?php endif;?>