<?xml version='1.0' encoding='ISO-8859-1'?>
<xsl:stylesheet xmlns:xsl='http://www.w3.org/1999/XSL/Transform' version='1.0'>
<!--xsl:stylesheet xmlns:xsl="http://www.w3.org/TR/WD-xsl"-->
  <xsl:output method="html" indent='no'/>
        <xsl:template match="/">
                <!--form action='[URLServlets]SheduleAdmin' method='POST'-->
                        <table>
                        <xsl:apply-templates select="eventType/*"/>
                        </table>
                        <!--input type='submit' value='Save'/>
                </form-->
        </xsl:template>
<xsl:template match="redirectURL">
        <tr><th>Redirect URL</th><td>
        <input size='50' type='text' name='redirectURL_url' value='[redirectURL_url]'/>
        </td>
        </tr>
</xsl:template>
<xsl:template match="chatApplet">
        <!--tr><th>
        ChatApplet<br/>Parameters:</th><td class='skip'-->
        <xsl:for-each select='./attribute'>
                <!--xsl:value-of select="./@name"/>:-->
                <input type='hidden'>
                        <xsl:attribute name='name'>chatApplet_<xsl:value-of select="./@name"/></xsl:attribute>
                        <xsl:attribute name='value'>[chatApplet_<xsl:value-of select="./@name"/>]</xsl:attribute>
                </input>
                <!--br/-->
        </xsl:for-each>
        <!--/td>        </tr-->
</xsl:template>
<xsl:template match="liveCamPro">        <!--tr><th>
        LiveCamPro<br/>Parameters:       </th><td class='skip'-->
        <xsl:for-each select='./attribute'>
                <!--xsl:value-of select="./@name"/:-->
                <input type='hidden'>
                        <xsl:attribute name='name'>liveCamPro_<xsl:value-of select="./@name"/></xsl:attribute>
                        <xsl:attribute name='value'>[liveCamPro_<xsl:value-of select="./@name"/>]</xsl:attribute>
                </input><!--br/-->
        </xsl:for-each>
        <!--/td></tr-->
</xsl:template>
<xsl:template match="liveCam1">
        <!--tr><th>LiveCam1<br/>Parameters:       </th> <td!-->
        <xsl:for-each select='./attribute'>
                <!--br/><xsl:value-of select="./@name"/>:-->
                <input type='hidden'>
                        <xsl:attribute name='name'>liveCam1_<xsl:value-of select="./@name"/></xsl:attribute>
                        <xsl:attribute name='value'>[liveCam1_<xsl:value-of select="./@name"/>]</xsl:attribute>
                </input><!--br/-->
        </xsl:for-each><!--/td></tr-->
</xsl:template>
<xsl:template match="externalURLs">
        <tr><th>
        URLs<br/>(ðàçäåëèòåëü , èëè ;):</th><td>
        <textarea name='externalURLs_urls' cols='50' rows='5'>[externalURLs_urls]</textarea>
        </td></tr>
</xsl:template>
<xsl:template match="tests">
        <tr><th>Âûáåðèòå çàäàíèå</th>
        <td class=''>   <xsl:for-each select='./attribute'>             
                <select>
                        <xsl:attribute name='name'>tests_<xsl:value-of select="./@name"/></xsl:attribute>
                        [SHE_TESTS_LIST]
                </select>
        </xsl:for-each>
        </td></tr>
</xsl:template>
<xsl:template match="module">
        <tr><th>Âûáåðèòå ó÷åáíûé ìîäóëü</th>
        <td class=''>   <xsl:for-each select='./attribute'>             
                <select>
                        <xsl:attribute name='name'>module_<xsl:value-of select="./@name"/></xsl:attribute>
                        [SHE_MODULE_LIST]
                </select>
        </xsl:for-each>
        </td></tr>
</xsl:template>
</xsl:stylesheet>