<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"  xmlns:php="http://php.net/xsl" version="1.0">
	<xsl:template match="actions">
		<config>
	        <nav>
	            <xsl:apply-templates/>
	        </nav>
        </config>
    </xsl:template>
    
    <xsl:template match="page/link">
    	<xsl:variable name="temp" select="php:function('randInt')"></xsl:variable>
        <xsl:element name="{concat('page_', $temp)}">
        	<label><xsl:value-of select="@name"/></label>
        	<resource><xsl:value-of select="php:function('getResource',string(@url))"/></resource>
        	<allow><xsl:value-of select="php:function('setAllow',string(@profiles))"/></allow>
        	<deny><xsl:value-of select="php:function('setDeny',string(@profiles))"/></deny>
        	<module><xsl:value-of select="php:function('getModule', string(@url))"/></module>
        	<controller><xsl:value-of select="php:function('getController', string(@url))"/></controller>
        	<action><xsl:value-of select="php:function('getAction', string(@url))"/></action>
        	<xsl:apply-templates/>
        </xsl:element>
    </xsl:template>
    
    <xsl:template match="subgroup">
		<xsl:apply-templates/>
    </xsl:template>
    
    <xsl:template match="custom">
		<xsl:apply-templates/>
    </xsl:template>
    
    <xsl:template match="subgroup/page">
    	<xsl:variable name="temp" select="php:function('randInt')"></xsl:variable>
		<xsl:element name="{concat('page_', $temp)}">
        	<label><xsl:value-of select="@name"/></label>
        	<id><xsl:value-of select="@id"/></id>
        	<resource><xsl:value-of select="php:function('getResource',string(@url))"/></resource>
        	<allow><xsl:value-of select="php:function('setAllow',string(@profiles))"/></allow>
        	<deny><xsl:value-of select="php:function('setDeny',string(@profiles))"/></deny>
        	<module><xsl:value-of select="php:function('getModule', string(@url))"/></module>
        	<controller><xsl:value-of select="php:function('getController', string(@url))"/></controller>
        	<action><xsl:value-of select="php:function('getAction', string(@url))"/></action>
        	<pages>
        		<xsl:apply-templates/>
        	</pages>
        </xsl:element>
    </xsl:template>
    
    <xsl:template match="group">
    	<xsl:variable name="temp" select="php:function('randInt')"></xsl:variable>
    	<xsl:element name="{concat('page_', $temp)}">
	    	<label><xsl:value-of select="@name"/></label>
	    	<resource><xsl:value-of select="php:function('getResource',string(@url))"/></resource>
	        <allow><xsl:value-of select="php:function('setAllow',string(@profiles))"/></allow>
	        <deny><xsl:value-of select="php:function('setDeny',string(@profiles))"/></deny>
	        <module><xsl:value-of select="php:function('getModule', string(@url))"/></module>
        	<controller><xsl:value-of select="php:function('getController', string(@url))"/></controller>
        	<action><xsl:value-of select="php:function('getAction', string(@url))"/></action>
			<pages>
	        	<xsl:apply-templates/>
	        </pages>
	        
	    </xsl:element>
    </xsl:template>
</xsl:stylesheet>