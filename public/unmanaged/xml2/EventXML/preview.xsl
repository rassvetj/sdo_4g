<?xml version='1.0' encoding='UTF-8'?>
<xsl:stylesheet xmlns:xsl='http://www.w3.org/1999/XSL/Transform' version='1.0'>
<!--xsl:stylesheet xmlns:xsl="http://www.w3.org/TR/WD-xsl"-->
  <xsl:output method="html" indent='no'/>
	<xsl:template match="/">
		<form action='[URLServlets]ControlEventTypes' method='POST'>
			<input type='hidden' name='make' value='preview'/>
			<input type='hidden' name='id'>
				<xsl:attribute name='value'>
				<xsl:value-of select="eventType/@ID"/>
				</xsl:attribute>
			</input>
			<xsl:apply-templates select="eventType/*"/>			
			<input type='submit' value='Preview'/>
		</form>
	</xsl:template>
<xsl:template match="redirectURL">Redirect URL:<br/>
		<input type='hidden' name='redirectURL' value='1'/>
		<input type='text' size='50' name='redirectURL_url' value='http://www.istudium.spb.ru'/>
		<!--input><xsl:attribute name='type'>text</xsl:attribute>
		<xsl:attribute name='name'>redirectURL_url</xsl:attribute>
                <xsl:attribute name='value'>[redirectURL_url]</xsl:attribute>
	</input--><hr/>
</xsl:template>
<xsl:template match="chatApplet">
	<input type='hidden' name='chatApplet' value='1'/>
	ChatApplet Parameters:<br/>
	Width<input type='text' name='chatApplet_width' value='100'/><br/>
	Height<input type='text' name='chatApplet_height' value='100'/><br/>
	login<input type='text' name='chatApplet_in_login' value=''/><br/>
	Password<input type='password' name='chatApplet_in_pass' value=''/><br/>
	Port<input type='text' name='chatApplet_in_port' value=''/><br/>
        Room<input type='text' name='chatApplet_in_room' value=''/><br/>
	<!--xsl:for-each select='./attribute'>
		<xsl:value-of select="./@name"/>:
		<input><xsl:attribute name='type'>text</xsl:attribute>
			<xsl:attribute name='name'>chatApplet_<xsl:value-of select="./@name"/></xsl:attribute>
                        <xsl:attribute name='value'>[chatApplet_<xsl:value-of select="./@name"/>]</xsl:attribute>
		</input>
		<br/>
	</xsl:for-each-->
		<hr/>
</xsl:template>
<xsl:template match="liveCamPro">
	<input type='hidden' name='liveCamPro' value='1'/>
	LiveCamPro Parameters:<br/>
	Width<input type='text' name='liveCamPro_width' value='100'/><br/>
	Height<input type='text' name='liveCamPro_height' value='100'/><br/>
	Delay<input type='text' name='liveCamPro_sleep' value='10'/><br/>
	File<input type='text' name='liveCamPro_file' value='web.jpg'/><br/>
	<!--xsl:for-each select='./attribute'>
		<xsl:value-of select="./@name"/>:
		<input><xsl:attribute name='type'>text</xsl:attribute>
			<xsl:attribute name='name'>liveCamPro_<xsl:value-of select="./@name"/></xsl:attribute>
                        <xsl:attribute name='value'>[liveCamPro_<xsl:value-of select="./@name"/>]</xsl:attribute>
		</input><br/>
	</xsl:for-each-->
	<hr/>
</xsl:template>
<xsl:template match="liveCam1">
	<input type='hidden' name='liveCam1' value='1'/>
	LiveCam1:<br/>
	Width:<input type='text' name='liveCam1_width' value='100'/><br/>
	Height:<input type='text' name='liveCam1_height' value='100'/><br/>
	Delay:<input type='text' name='liveCam1_sleep' value='10'/><br/>
	File<input type='text' name='liveCam1_file' value='web.jpg'/>
	<!--xsl:for-each select='./attribute'>
		<br/><xsl:value-of select="./@name"/>:
		<input><xsl:attribute name='type'>text</xsl:attribute>
			<xsl:attribute name='name'>liveCam1_<xsl:value-of select="./@name"/></xsl:attribute>
                        <xsl:attribute name='value'>[liveCam1_<xsl:value-of select="./@name"/>]</xsl:attribute>
		</input>
	</xsl:for-each--><hr/>
</xsl:template>
<xsl:template match="externalURLs">
	<input type='hidden' name='externalURLs' value='1'/>
	External URL:<br/>
	<textarea name='externalURLs_urls' cols='50' rows='5'>www.istudium.spb.ru/labs/laba1.exe,
www.istudium.spb.ru/labs/laba2.exe</textarea>
	<hr/>
</xsl:template>
<xsl:template match="books">
	<input type='hidden' name='books' value='1'/>
	Books:<br/>
	<hr/>
</xsl:template>
<xsl:template match="tests">
	<input type='hidden' name='tests' value='1'/>
	Tests:<br/>
	<hr/>
</xsl:template>
</xsl:stylesheet>