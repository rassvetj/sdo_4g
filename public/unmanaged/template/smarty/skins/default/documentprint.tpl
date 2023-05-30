<!DOCTYPE html>
<html>
	<head>
		<meta charset="{?$this->encoding?}">
		<title>{?$smarty.const.APPLICATION_TITLE?} :: {?t?}Страница для печати{?/t?}</title>
	</head>
	<body>
		{?php?}
		echo getField('OPTIONS', 'value', 'name', 'template_report_header');
		{?/php?}
		<br />
		{?$this->content?}
		<br />
		{?php?}
		echo getField('OPTIONS', 'value', 'name', 'template_report_footer');
		{?/php?}
	</body>
</html>
