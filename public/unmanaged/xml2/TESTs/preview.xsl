<?xml version="1.0" encoding="windows-1251"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/TR/WD-xsl">
<!--xsl:stylesheet xmlns:xsl="http://www.w3.org/XML/1998/namespace"-->
	<xsl:template match="/">
		<HTML>
<head>
<title>Предварительный просмотр</title>
<link rel='stylesheet' type='text/css' href='styles/style.css'/>
</head>
<BODY>
	<TABLE width='100%' border='0' cellspacing='1' cellpadding='0' bgcolor='#C1C1C1'>
	<tr><td class='quest'>
		<xsl:value-of select="question/text"/>
	</td></tr>
	<xsl:apply-templates select="question/object"/>

	<xsl:apply-templates select="question/multiple_choice|question/match|question/filling_form|question/free"/>
	</TABLE>
</BODY>
</HTML>
</xsl:template>

	<xsl:template match="multiple_choice">
	<tr><td class='quest'>
		<xsl:for-each select="variant">
			<input type="checkbox"/>
			<xsl:value-of select="."/>
			<br/>
		</xsl:for-each>
	</td></tr>
	</xsl:template>

	<xsl:template match="filling_form">
	<tr><td class='quest'>	
		<xsl:apply-templates select="./answers|./text"/>
	</td></tr>
	</xsl:template>

	<xsl:template match="filling_form/answers">
		<input type="text" class='lineinput' value=''/>
	</xsl:template>

	<xsl:template match="free">
	<tr><td class='quest'>
		<textarea rows='9' cols='80'></textarea>
	</td></tr>
	</xsl:template>


	<xsl:template match="object[@type='flash']">
	<tr><td align='center'>
		<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=5,0,0,0" quality='high' width='100%'>
        	<param name='movie'>
		<xsl:attribute name='value'><xsl:value-of select="@src"/></xsl:attribute>
		</param>
		<!--embed quality='high' pluginspage="http://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash" type="application/x-shockwave-flash" width='200' height='200'><xsl:attribute name='src'><xsl:value-of select="@src"/></xsl:attribute></embed--> 
		</object>
	</td></tr>
	</xsl:template>
	<xsl:template match="object[@type='image']">
	<tr><td bgcolor='white' align='center'>
		<img>
		<xsl:attribute name='src'><xsl:value-of select="@src"/></xsl:attribute>
		</img>
	</td></tr>
	</xsl:template>
	<xsl:template match="object[not(@type)]|object[@type='application']">
	<tr><td bgcolor='white' align='center'>
		<a class='quest'>
			<xsl:attribute name='href'><xsl:value-of select="@src"/></xsl:attribute>
			Загрузить
		</a>
	</td></tr>
	</xsl:template>


	<xsl:template match="filling_form/text">
		<xsl:value-of select="."/>
	</xsl:template>

	<xsl:template match="match">
	<tr><td class='quest'>
		<xsl:for-each select="./cells/cell[@group='1']">
		<input type='text' class='lineinput' readonly='true' style='width: 240px;'>
			<xsl:attribute name='value'><xsl:value-of select="."/></xsl:attribute>
		</input>
<span class='cHilight'>
<b title='Соответствующее выражение' class='wing' style='font-size: 14px'>&#0224;</b>
</span>
			<select class='lineinput' style="width: 230px;border-style:0">
				<xsl:for-each select="../../cells/cell[@group='2']">
					<option><xsl:value-of select="."/></option>
				</xsl:for-each>
			</select><br/>
		</xsl:for-each>				
	 </td></tr>
	</xsl:template>
</xsl:stylesheet>


