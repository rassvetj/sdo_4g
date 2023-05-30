<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<xsl:output method="html"/>
	
	<xsl:variable name="checkersEnabled" select="not(tree/@checkers) or tree/@checkers = 'true'" />
	
	<xsl:template match="/">
		<xsl:apply-templates/>
	</xsl:template>
	
	<xsl:template match="item">
		
		<xsl:variable name="noParent" select="name(..)='tree'"/>
		<xsl:variable name="hasChildren" select="item"/>
		<xsl:variable name="hasInnerData" select="@innerdata"/>
		<xsl:variable name="noPreceding" select="not(preceding-sibling::item)"/>
		<xsl:variable name="noSibling" select="not(following-sibling::item)"/>
		<xsl:variable name="hasChecker" select="not(@checker) or @checker = 'true'"/>
		<xsl:variable name="hasIcon" select="@icon"/>
				
		<div>
			
			<xsl:attribute name="class"><xsl:if test="not($noSibling)">line </xsl:if>item</xsl:attribute>
			
			<span>
				<xsl:attribute name="class">
					<xsl:choose>
						<xsl:when test="$noSibling">b</xsl:when>
						<xsl:otherwise>c</xsl:otherwise>
					</xsl:choose>
				</xsl:attribute>
				<span>
					<xsl:if test="$hasChildren">
						<xsl:attribute name="class">m</xsl:attribute>
					</xsl:if>
					<xsl:if test="$hasInnerData">
						<xsl:attribute name="class">p</xsl:attribute>
					</xsl:if>
					<span class="value">
						<xsl:choose>
							<xsl:when test="$checkersEnabled and $hasChecker">
								<input name="che[{@id}]" type="checkbox" />
							</xsl:when>
							<xsl:otherwise>
								<input type="checkbox" disabled="true"/>
							</xsl:otherwise>
						</xsl:choose>
						<xsl:if test="$hasIcon"><img src="{@icon}" style="padding:0;margin:0 5px 0 0;vertical-align:bottom; position: relative; top: -3px;" /></xsl:if>
						<strong>
							<xsl:choose>
								<xsl:when test="$hasIcon and not($checkersEnabled and $hasChecker)">
									<xsl:attribute name="class">hasIconOnly</xsl:attribute>
								</xsl:when>
								<xsl:when test="not($hasIcon) and ($checkersEnabled and $hasChecker)">
									<xsl:attribute name="class">hasCheckerOnly</xsl:attribute>
								</xsl:when>
								<xsl:when test="$hasIcon and ($checkersEnabled and $hasChecker)">
									<xsl:attribute name="class">hasIconAndChecker</xsl:attribute>
								</xsl:when>
								<xsl:otherwise>
									
								</xsl:otherwise>
							</xsl:choose>
							<a href="javascript:void(0);" onClick="wopen('soid_info.php?soid={@id}','soid_{@id}', '450', '350')">
							<xsl:choose>
								<xsl:when test="$hasInnerData">
									<b><xsl:value-of select="@value"/></b>
								</xsl:when>
								<xsl:otherwise>
									<xsl:value-of select="@value"/>
								</xsl:otherwise>
							</xsl:choose>							
							</a>
						</strong>
						<span class="a"><xsl:for-each select="@*[name() != 'innerdata' and name() != 'icon' and name() != 'value' and name() != 'bold' and name() != 'checker']">,'<xsl:value-of select="name(.)"/>':'<xsl:value-of select="."/>'</xsl:for-each></span>
					</span>
				</span>
			</span>
			
			<xsl:choose>
				<xsl:when test="$hasInnerData">
					<div class="item inner"><xsl:value-of select="@innerdata"/></div>
				</xsl:when>
				<xsl:otherwise>
					<xsl:apply-templates/>
				</xsl:otherwise>
			</xsl:choose>
			
		</div>
		
	</xsl:template>
	
</xsl:stylesheet>
