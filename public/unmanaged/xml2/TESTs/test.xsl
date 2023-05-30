<?xml version="1.0" encoding="Windows-1251"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/TR/WD-xsl">
<!--xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
  <xsl:output method="html" indent='yes' encoding="Windows-1251"/-->
<xsl:template match="/">

<HTML>
<head>
<title>Задание</title>
<link rel='stylesheet' type='text/css' href='http://hyper.hypermethod.com/test/istudium/styles/style.css'/>
</head>
<BODY>
	<form action='\servlet\Tests' method='POST'>
	<TABLE border='0' cellspacing='1' cellpadding='0' bgcolor='#C1C1C1'>
		<xsl:apply-templates select="test/question"/>
<tr><td align='center'>
	<input type='hidden' name='make' value='answerTest'/>
	<input type='submit' value='OK'/>
	<input type='hidden' name='TID'>
		<xsl:attribute name='value'><xsl:value-of select="test/@TID"/></xsl:attribute>
	</input>
</td></tr>
	</TABLE>
	</form>
</BODY>
</HTML>
</xsl:template>
	<xsl:template match="question">
	<tr><td class='quest'>
		Вопрос: <xsl:value-of select="./text"/>
	</td></tr>
	<xsl:apply-templates select="./object"/>

	<xsl:apply-templates select="./multiple_choice|./match|./filling_form|./free"/>
<tr><td height='4'></td></tr>
	</xsl:template>

	<xsl:template match="multiple_choice">
	<tr><td class='quest'>	
		<xsl:for-each select="variant">
			<input type="checkbox">
			<xsl:attribute name='name'><xsl:value-of select='/test/@TID'/>_<xsl:value-of select='../../@qid'/>_<xsl:value-of select='./@num'/></xsl:attribute>
			</input>
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
		<input type="text" class='lineinput' value=''>
		<xsl:attribute name='name'><xsl:value-of select='/test/@TID'/>_<xsl:value-of select='../../@qid'/>_<xsl:value-of select='@num'/></xsl:attribute>
		</input>
	</xsl:template>

	<xsl:template match="free">
	<tr><td class='quest'>
		<textarea rows='9' cols='80'><xsl:attribute name='name'><xsl:value-of select='/test/@TID'/>_<xsl:value-of select='../@qid'/>_1</xsl:attribute>...</textarea>
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
		<a class='quest' target="_blank">
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
			<xsl:attribute name='name'><xsl:value-of select='/test/@TID'/>_<xsl:value-of select='../../../@qid'/>_<xsl:value-of select='@num'/></xsl:attribute>
				<xsl:for-each select="../../cells/cell[@group='2']">
					<option>
					<xsl:attribute name='value'><xsl:value-of select='@num'/></xsl:attribute>
					<xsl:value-of select="."/>					
					</option>
 
				</xsl:for-each>
			</select><br/>
		</xsl:for-each>				
	 </td></tr>
	</xsl:template>
</xsl:stylesheet>


