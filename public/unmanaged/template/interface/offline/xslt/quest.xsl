<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="xml" encoding="UTF-8" />

	<xsl:template match="question">
		<div class="eAu-question-container">
			<h1 class="eAu"><xsl:value-of select="../@title"/></h1>
			<div id="eAu-test-statistics" class="eAu-test-statistics">
				<table cellpadding="0" cellspacing="0" style="border-collapse: collapse;" border="0" width="100%">
					<tr><td>
						<div id="eAu-test-questions-statistics"></div>
						<div id="eAu-test-time" class="eAu-test-time"></div>
					</td>
					<td align="right">
						<div id="eAu-test-break">
							<a href="test:break" onclick="break_test(); return false;">прервать тестирование</a>
							<img hspace="5" tooltip-url="tooltips/quit_test.tpl" class="tooltip-link" src="../common/template/images/tooltip/tooltip.gif" />
						</div>
						<div style="display: none;" id="eAu-test-end">
							<a href="test:pre-term-ending" onclick="end_test(); return false;" id="eAu-test-end">досрочно завершить тестирование</a>
							<img hspace="5" tooltip-url="tooltips/pre-term_ending_stud.tpl" class="tooltip-link" src="../common/template/images/tooltip/tooltip.gif" />
						</div>
					</td></tr>
				</table>
			</div>
			<h2 class="eAu"><xsl:value-of select="@title"/></h2>
			<div id="eAu-question-statistics" class="eAu-test-statistics">
				<xsl:if test="@group and normalize-space(@group) != ''">
					<div class="eAu-question-theme">тема вопроса <![CDATA[&ndash;]]> <span><xsl:value-of select="normalize-space(@group)" /></span></div>
				</xsl:if>
				<div id="eAu-question-time" class="eAu-quesion-time"></div>
			</div>
			<div class="eAu-question-text"><xsl:value-of select="text"/></div>
			<div class="eAu-question-answers eAu-question-answers-interactive" id="eAu-question-answers">
				<xsl:apply-templates select="answers"/>
			</div>
		</div>
	</xsl:template>

	<xsl:template match="answers">
		<form id="eAu-question-form" name="eAu-question-form" onsubmit="return false;">
			<xsl:if test="@type = 'table'">
				<xsl:attribute name="class">els-content</xsl:attribute>
			</xsl:if>
			<xsl:if test="@type = 'single' or @type = 'multiple' or @type='fill'">
				<span style="display: none;" id="eAu-enter-triggers-submit">enter-triggers-submit</span>
			</xsl:if>
			<table cellpadding="0" cellspacing="0" style="border-collapse: collapse; border-color: #737373;">
				<xsl:choose>
					<xsl:when test="@type = 'free'">
						<xsl:attribute name="width">100%</xsl:attribute>
						<tr><td>
							<div class="eAu-input-textarea">
								<textarea id="EU-{../@DB_ID}-TextArea" style="height: 200px;"></textarea>
							</div>
						</td></tr>
					</xsl:when>
					<xsl:when test="@type = 'table'">
						<xsl:attribute name="class">eAu-question-table main</xsl:attribute>
						<tr>
							<th><![CDATA[&nbsp;]]></th>
							<xsl:for-each select="answer">
								<xsl:if test="normalize-space(.) != ''">
									<th><xsl:value-of select="normalize-space(.)"/></th>
								</xsl:if>
							</xsl:for-each>
						</tr>
						<xsl:apply-templates select="answer[@right and @right != '']" />
						<tr>
							<th class="eAu-question-table-qtext">Комментарии</th>
							<td style="zoom: 1;">
								<xsl:attribute name="colspan">
									<xsl:value-of select="count(answer[normalize-space(.) != ''])"/>
								</xsl:attribute>
								<div class="eAu-input-textarea">
									<textarea id="EU-{../@DB_ID}-TextArea" style="height: 70px;"></textarea>
								</div>
							</td>
						</tr>
					</xsl:when>
					<xsl:otherwise>
						<xsl:apply-templates select="answer"/>
					</xsl:otherwise>
				</xsl:choose>
			</table>
		</form>
	</xsl:template>

	<xsl:template match="answers[@type='single']/answer">
		<xsl:variable name="DB_ID"><xsl:value-of select="../../@DB_ID"/></xsl:variable>
		<tr>
			<td><input type="radio" name="EU-{$DB_ID}" id="EU-{$DB_ID}-{position()}" value="{position()}" class="eAu-input-radio" /></td>
			<td class="eAu-answer-variant">
				<xsl:attribute name="onclick">
					var node = document.getElementById('EU-<xsl:value-of select="$DB_ID"/>-<xsl:value-of select="position()"/>');
					if (!node.disabled) { node.checked = true; }
				</xsl:attribute>
				<div><xsl:value-of select="."/></div>
			</td>
			<td class="eAu-answer-indicator"></td>
		</tr>
	</xsl:template>

	<xsl:template match="answers[@type='multiple']/answer">
		<xsl:variable name="DB_ID"><xsl:value-of select="../../@DB_ID"/></xsl:variable>
		<tr>
			<td><input type="checkbox" name="EU-{$DB_ID}-{@DB_ID}" id="EU-{$DB_ID}-{@DB_ID}-1" value="1" class="eAu-input-checkbox"/></td>
			<td class="eAu-answer-variant">
				<xsl:attribute name="onclick">
					var node = document.getElementById('EU-<xsl:value-of select="$DB_ID"/>-<xsl:value-of select="@DB_ID"/>-1');
					if (!node.disabled) { node.checked = node.checked ? false : true; }
				</xsl:attribute>
				<div><xsl:value-of select="."/></div>
			</td>
			<td class="eAu-answer-indicator"></td>
		</tr>
	</xsl:template>

	<xsl:template match="answers[@type='fill']/answer">
		<xsl:variable name="DB_ID"><xsl:value-of select="@DB_ID"/></xsl:variable>
		<tr>
			<td class="eAu-answer-variant"><div><xsl:value-of select="."/></div></td>
			<td><span class="eAu-arrow">→</span></td>
			<td><input type="text" class="eAu-input-text" width="40" id="EU-{$DB_ID}" name="EU-{$DB_ID}" /></td>
			<td class="eAu-answer-indicator"></td>
			<td id="EU-RA-{$DB_ID}"></td>
		</tr>
	</xsl:template>

	<xsl:template match="answers[@type='compare']/answer">
		<xsl:variable name="DB_ID"><xsl:value-of select="@DB_ID"/></xsl:variable>
		<tr>
			<td class="eAu-answer-variant"><div><xsl:value-of select="."/></div></td>
			<td><span class="eAu-arrow">→</span></td>
			<td>
				<select id="EU-{$DB_ID}" name="EU-{$DB_ID}">
					<xsl:for-each select="../answer">
						<xsl:sort select="@right"/>
						<option val="{@right}" value="EU-VAL-{@DB_ID}"><xsl:value-of select="@right"/></option>
					</xsl:for-each>
				</select>
			</td>
			<td class="eAu-answer-indicator"></td>
			<td id="EU-RA-{$DB_ID}"></td>
		</tr>
	</xsl:template>	
	
	<xsl:template match="answers[@type='table']/answer[@right and @right != '']">
		<xsl:variable name="DB_ID"><xsl:value-of select="@DB_ID"/></xsl:variable>
		<xsl:variable name="pos"><xsl:value-of select="position()"/></xsl:variable>
		<tr>
			<th class="eAu-question-table-qtext"><xsl:value-of select="@right"/></th>
			<xsl:for-each select="../answer[normalize-space(.) != '']">
				<td>
					<input type="radio" class="eAu-input-radio" id="EU-{$DB_ID}-{@DB_ID}" name="EU-{$DB_ID}" value="{@DB_ID}" question_n="{$pos}" answer_n="{position()}">
						<xsl:if test="@weight">
							<xsl:attribute name="weight"><xsl:value-of select="@weight" /></xsl:attribute>
						</xsl:if>
						<xsl:if test="position() = 1">
							<xsl:attribute name="checked">checked</xsl:attribute>
						</xsl:if>
					</input>
				</td>
			</xsl:for-each>
		</tr>
	</xsl:template>
</xsl:stylesheet>
