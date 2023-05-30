<html>
	<head>
		<title>{?$smarty.const.APPLICATION_TITLE|escape?}</title>
	</head>
	<frameset cols="400,*" frameborder="no" border="0" framespacing="0" name="superMainFrameset" id="superMainFrameset" noresize>
		<frame src="{?$sitepath?}orgstructure_toc.php?page_id={?$page_id?}" id="leftFrame" name="leftFrame" scrolling="auto">
		<frame name="mainFrame" id="mainFrame" scrolling="yes" src="{?$sitepath?}orgstructure_info.php?page_id={?$page_id?}">
	</frameset>
	<noframes><body>
	</body></noframes>
</html>
