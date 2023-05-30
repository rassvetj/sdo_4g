<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:variable name="printForeighnStrings">no</xsl:variable>
	<xsl:variable name="printNotAuthors">yes</xsl:variable>
	<xsl:output encoding="UTF-8" indent="yes" method="html" omit-xml-declaration="yes"/>
	<xsl:template name="lomLangString">
		<xsl:param name="title" />
		<xsl:param name="nodesToPrint"/>
		<xsl:if test="$title">
			<h4><xsl:value-of select="$title" disable-output-escaping="no"/></h4>
		</xsl:if>
		<ul>
			<xsl:for-each select="$nodesToPrint">
				<li>
					<xsl:value-of select="."/>
				</li>
			</xsl:for-each>
		</ul>
	</xsl:template>
	<xsl:template name="lomContribute">
		<xsl:param name="title" />
		<xsl:param name="nodesToPrint"/>
		<h4><xsl:value-of select="$title" /></h4>
		<ul>
			<xsl:for-each select="$nodesToPrint">
				<xsl:for-each select="entity">
					<xsl:choose>
						<xsl:when test="$printNotAuthors = 'yes'">
							<li>
								<xsl:value-of select="."/>
								<xsl:if test="../role">
									<xsl:choose>
										<xsl:when test="../role = 'author'">
											<xsl:text> (Роль: автор)</xsl:text>
										</xsl:when>
										<xsl:when test="../role = 'unknown'">
											<xsl:text> (Роль: неизвестна)</xsl:text>
										</xsl:when>
										<xsl:when test="../role = 'script writer'">
											<xsl:text> (Роль: создатель скриптов)</xsl:text>
										</xsl:when>
										<xsl:when test="../role = 'graphical designer'">
											<xsl:text> (Роль: дизайнер)</xsl:text>
										</xsl:when>
										<xsl:when test="../role = 'publisher'">
											<xsl:text> (Роль: издатель)</xsl:text>
										</xsl:when>
										<xsl:otherwise>
											<xsl:text> (Роль: </xsl:text>
											<xsl:value-of select="../role"/>
											<xsl:text>)</xsl:text>
										</xsl:otherwise>
									</xsl:choose>
								</xsl:if>
							</li>
						</xsl:when>
						<xsl:otherwise>
							<xsl:choose>
								<xsl:when test="not(../role)">
									<li><xsl:value-of select="."/></li>
								</xsl:when>
								<xsl:when test="../role = 'author'">
									<li><xsl:value-of select="."/></li>
								</xsl:when>
							</xsl:choose>
						</xsl:otherwise>
					</xsl:choose>
				</xsl:for-each>
			</xsl:for-each>
		</ul>
	</xsl:template>
	<xsl:template name="lomTypicalLearningTime">
		<xsl:param name="title"/>
		<xsl:param name="nodesToPrint"/>
		<h4><xsl:value-of select="$title" />: 
			<span><xsl:value-of select="$nodesToPrint"/></span>
		</h4>
	</xsl:template>
	<xsl:template name="lomIntendedEndUserDescription">
		<xsl:param name="title"/>
		<xsl:param name="nodesToPrint"/>
		<xsl:param name="nodesToPrintInline"/>
		<h4><xsl:value-of select="$title" /></h4>
		<xsl:if test="$nodesToPrintInline">
			<div class="lomUserAge">
				<span><xsl:text>Возраст обучаемых: </xsl:text></span>
				<xsl:for-each select="$nodesToPrintInline">
					<xsl:value-of select="."/>
					<xsl:if test="position() != count($nodesToPrintInline)">
						<xsl:text>, </xsl:text>
					</xsl:if>
				</xsl:for-each>
			</div>
		</xsl:if>
		<ul>
			<xsl:for-each select="$nodesToPrint">
				<li>
					<xsl:choose>
						<xsl:when test=". = 'teacher'">
							<xsl:text>Учитель</xsl:text>
						</xsl:when>
						<xsl:when test=". = 'author'">
							<xsl:text>Автор курсов</xsl:text>
						</xsl:when>
						<xsl:when test=". = 'learner'">
							<xsl:text>Ученик</xsl:text>
						</xsl:when>
						<xsl:when test=". = 'manager'">
							<xsl:text>Руководитель</xsl:text>
						</xsl:when>
						<xsl:otherwise>
							<xsl:value-of select="."/>
						</xsl:otherwise>
					</xsl:choose>
				</li>
			</xsl:for-each>
		</ul>
	</xsl:template>
	<xsl:template name="lomGeneral">
		<h1><xsl:text disable-output-escaping="no">Общая информация</xsl:text></h1>
		<xsl:if test="general/descriptions">
			<div class="lomGeneralDescription">
				<xsl:call-template name="lomLangString">
					<xsl:with-param name="title">Описание курса</xsl:with-param>
					<xsl:with-param name="nodesToPrint" select="general/descriptions/description"/>
				</xsl:call-template>
			</div>
		</xsl:if>
		<xsl:if test="general/coverages">
			<div class="lomGeneralCoverage">
				<xsl:call-template name="lomLangString">
					<xsl:with-param name="title">Предметная область</xsl:with-param>
					<xsl:with-param name="nodesToPrint" select="general/coverages/coverage"/>
				</xsl:call-template>
			</div>
		</xsl:if>
		<xsl:if test="general/keywords">
			<div class="lomGeneralKeyword">
				<xsl:call-template name="lomLangString">
					<xsl:with-param name="title">Ключевые слова</xsl:with-param>
					<xsl:with-param name="nodesToPrint" select="general/keywords/keyword"/>
				</xsl:call-template>
			</div>
		</xsl:if>
	</xsl:template>
	<xsl:template name="lomContributors">
		<xsl:if test="contributors/contribute">
			<div class="lomContributorsContribute">
				<xsl:call-template name="lomContribute">
					<xsl:with-param name="title">Разработчики курса</xsl:with-param>
					<xsl:with-param name="nodesToPrint" select="contributors/contribute"/>
				</xsl:call-template>
			</div>
		</xsl:if>
	</xsl:template>
	<xsl:template name="lomEducational">
		<h1>Образовательные характеристики</h1>
		<xsl:if test="count(educationalContainer/educational) > 1">
			<xsl:text disable-output-escaping="yes"><![CDATA[<ol>]]></xsl:text>
		</xsl:if>
		<xsl:for-each select="educationalContainer/educational">
			<xsl:if test="count(../educational) > 1">
				<xsl:text disable-output-escaping="yes"><![CDATA[<li>]]></xsl:text>
			</xsl:if>
			<xsl:if test="intendedEndUserRoles">
				<div class="lomEducationalIntendedEndUserDescription">
					<xsl:call-template name="lomIntendedEndUserDescription">
						<xsl:with-param name="title">Категории обучаемых</xsl:with-param>
						<xsl:with-param name="nodesToPrint" select="intendedEndUserRoles/intendedEndUserRole"/>
						<xsl:with-param name="nodesToPrintInline" select="typicalAgeRanges/typicalAgeRange"/>
					</xsl:call-template>
				</div>
			</xsl:if>
			<xsl:if test="typicalLearningTime">
				<div class="lomEducationalTypicalLearningTime">
					<xsl:call-template name="lomTypicalLearningTime">
						<xsl:with-param name="title">Продолжительность обучения</xsl:with-param>
						<xsl:with-param name="nodesToPrint" select="typicalLearningTime"/>
					</xsl:call-template>
				</div>
			</xsl:if>
			<xsl:if test="difficulty">
				<div class="lomEducationalDifficulty">
					<h4>
						<xsl:text>Уровень сложности: </xsl:text>
						<xsl:choose>
							<xsl:when test="difficulty = 'very easy'">
								<xsl:text>очень легко</xsl:text>
							</xsl:when>
							<xsl:when test="difficulty = 'easy'">
								<xsl:text>легко</xsl:text>
							</xsl:when>
							<xsl:when test="difficulty = 'medium'">
								<xsl:text>средне</xsl:text>
							</xsl:when>
							<xsl:when test="difficulty = 'difficult'">
								<xsl:text>сложно</xsl:text>
							</xsl:when>
							<xsl:when test="difficulty = 'very difficult'">
								<xsl:text>очень сложно</xsl:text>
							</xsl:when>
							<xsl:otherwise>
								<xsl:value-of select="difficulty" />
							</xsl:otherwise>
						</xsl:choose>
					</h4>
				</div>
			</xsl:if>
			<xsl:if test="descriptions">
				<div class="lomEducationalDescription">
					<xsl:call-template name="lomLangString">
						<xsl:with-param name="title">Замечания к использованию курса</xsl:with-param>
						<xsl:with-param name="nodesToPrint" select="descriptions/description"/>
					</xsl:call-template>
				</div>
			</xsl:if>
			<xsl:if test="count(../educational) > 1">
				<xsl:text disable-output-escaping="yes"><![CDATA[</li>]]></xsl:text>
			</xsl:if>
		</xsl:for-each>
		<xsl:if test="count(educationalContainer/educational) > 1">
			<xsl:text disable-output-escaping="yes"><![CDATA[</ol>]]></xsl:text>
		</xsl:if>
	</xsl:template>
	<xsl:template name="lomRights">
		<h1>Права собственности</h1>
		<xsl:if test="rights/cost">
			<xsl:if test="rights/cost = 'yes'">
				<div class="lomRightsCost">
					<xsl:text>Данный курс является платным</xsl:text>
				</div>
			</xsl:if>
		</xsl:if>
		<xsl:if test="rights/copyrightAndOtherRestrictions">
			<xsl:if test="rights/copyrightAndOtherRestrictions = 'yes'">
				<div class="lomRightsCopyrightAndOtherRestrictions">
					<xsl:text>Содержание данного курса защищено авторскими правами</xsl:text>
				</div>
			</xsl:if>
		</xsl:if>
		<xsl:if test="rights/description">
			<div class="lomRightsDescription">
				<xsl:call-template name="lomLangString">
					<xsl:with-param name="title">Комментарии к соблюдению прав</xsl:with-param>
					<xsl:with-param name="nodesToPrint" select="rights/description"/>
				</xsl:call-template>
			</div>
		</xsl:if>
	</xsl:template>
	<xsl:template name="lomAnnotation">
		<h1>Комментарии и замечания</h1>
		<xsl:if test="count(annotations/annotation) > 1">
			<xsl:text disable-output-escaping="yes"><![CDATA[<ol>]]></xsl:text>
		</xsl:if>
		<xsl:for-each select="annotations/annotation">
			<xsl:if test="count(../annotation) > 1">
				<xsl:text disable-output-escaping="yes"><![CDATA[<li>]]></xsl:text>
			</xsl:if>
			<div><xsl:value-of select="entity"/></div>
			<div><xsl:value-of select="date"/></div>
			<div>
				<xsl:call-template name="lomLangString">
					<xsl:with-param name="nodesToPrint" select="description"/>
				</xsl:call-template>
			</div>
			<xsl:if test="count(../annotation) > 1">
				<xsl:text disable-output-escaping="yes"><![CDATA[</li>]]></xsl:text>
			</xsl:if>
		</xsl:for-each>
		<xsl:if test="count(annotations/annotation) > 1">
			<xsl:text disable-output-escaping="yes"><![CDATA[</ol>]]></xsl:text>
		</xsl:if>
	</xsl:template>
	<xsl:template name="lomListLanguages">
		<xsl:param name="nodesToPrint"/>
		<xsl:if test="$nodesToPrint">
			<xsl:for-each select="$nodesToPrint">
				<xsl:value-of select="."/>
				<xsl:if test="position() != count($nodesToPrint)">
					<xsl:text>, </xsl:text>
				</xsl:if>
			</xsl:for-each>
		</xsl:if>
	</xsl:template>
	<xsl:template name="lomTitle">
		<xsl:value-of select="lom/general/title"/>
		<xsl:if test="lom/general/languages">
			<div class="lomLanguage">
				<xsl:choose>
					<xsl:when test="count(lom/general/languages/language) > 1">
						<xsl:text> (Языки содержания курса: </xsl:text>
					</xsl:when>
					<xsl:otherwise>
						<xsl:text> (Язык содержания курса: </xsl:text>
					</xsl:otherwise>
				</xsl:choose>
				<xsl:call-template name="lomListLanguages">
					<xsl:with-param name="nodesToPrint" select="lom/general/languages/language"/>
				</xsl:call-template>
				<xsl:text>)</xsl:text>
			</div>
		</xsl:if>
	</xsl:template>
	<xsl:template name="lom">
		<xsl:if test="general|contributors">
			<div class="lomGeneral">
				<xsl:if test="general">
					<xsl:call-template name="lomGeneral"/>
				</xsl:if>
				<xsl:if test="contributors">
					<xsl:call-template name="lomContributors"/>
				</xsl:if>
			</div>
		</xsl:if>
		<!-- <xsl:if test="contributors">
			<div class="lomContributors">
				<xsl:call-template name="lomContributors"/>
			</div>
		</xsl:if> -->
		<xsl:if test="educationalContainer">
			<div class="lomEducational">
				<xsl:call-template name="lomEducational"/>
			</div>
		</xsl:if>
		<xsl:if test="rights">
			<div class="lomRights">
				<xsl:call-template name="lomRights"/>
			</div>
		</xsl:if>
		<xsl:if test="annotations">
			<div class="lomAnnotation">
				<xsl:call-template name="lomAnnotation"/>
			</div>
		</xsl:if>
	</xsl:template>
	<xsl:template match="lom">
		<xsl:call-template name="lom"/>
	</xsl:template>
	<xsl:template match="/organization" priority="10">
		<xsl:choose>
			<xsl:when test="not(lom/general/title)">
				<div class="header"><xsl:value-of select="@title" /></div>
			</xsl:when>
			<xsl:otherwise>
				<div class="lomTitle">
					<xsl:call-template name="lomTitle" />
				</div>
			</xsl:otherwise>
		</xsl:choose>
		<xsl:if test="lom">
			<div id="metadata">
				<div class="header">Метаданные</div>
				<div id="lom">
					<xsl:apply-templates select="lom" />
				</div>
			</div>
		</xsl:if>
		<div id="toc">
			<div class="header">Оглавление</div>
			<xsl:call-template name="generate_toc"/>
		</div>
		<div id="content">
			<xsl:call-template name="fill_content"/>
		</div>
	</xsl:template>
	<xsl:template name="generate_toc">
		<xsl:if test="count(item) > 0">
			<ol>
				<xsl:for-each select="item">
					<li>
						<xsl:if test="@visibility = 'hidden'">
							<xsl:attribute name="visibility">hidden</xsl:attribute>
							<xsl:attribute name="style">display: none;</xsl:attribute>
						</xsl:if>
						<xsl:choose>
							<xsl:when test="@type = 'lesson'">
								<a>
									<xsl:attribute name="href">#<xsl:value-of select="@DB_ID"/>
									</xsl:attribute><xsl:value-of select="@title" disable-output-escaping="yes"/>
								</a>
							</xsl:when>
							<xsl:otherwise>
								<xsl:value-of select="@title" disable-output-escaping="yes"/>
							</xsl:otherwise>
						</xsl:choose>
						<xsl:call-template name="generate_toc"/>
					</li>
				</xsl:for-each>
			</ol>
		</xsl:if>
	</xsl:template>
	<xsl:template name="list_subjects">
		<xsl:for-each select="subject">
			<div class="subject">
				<div class="header"><xsl:value-of select="@title" disable-output-escaping="yes"/></div>
				<div class="paragraph"><xsl:value-of select="." disable-output-escaping="yes"/></div>
			</div>
		</xsl:for-each>
	</xsl:template>
	<xsl:template name="list_targets">
		<xsl:for-each select="targets">
			<div class="targets">
				<div class="header"><xsl:value-of select="@title" disable-output-escaping="yes"/></div>
				<xsl:for-each select="target">
					<xsl:sort data-type="text" select="@type"/>
					<div class="target">
						<div class="header">
							<xsl:value-of select="position()"/>) <xsl:value-of select="@title" disable-output-escaping="yes"/>
							(Тип: <span class="type">
							<xsl:choose>
								<xsl:when test="@type='know'">знать</xsl:when>
								<xsl:when test="@type='can'">уметь</xsl:when>
								<xsl:otherwise>unknown</xsl:otherwise>
							</xsl:choose>
							</span>)
						</div>
						<div class="paragraph"><xsl:value-of select="." disable-output-escaping="yes"/></div>
					</div>
				</xsl:for-each>
			</div>
		</xsl:for-each>
	</xsl:template>
	<xsl:template name="list_tasks">
		<xsl:for-each select="task">
			<div class="task">
				<div class="header"><xsl:value-of select="@title" disable-output-escaping="yes"/></div>
				<div class="paragraph"><xsl:value-of select="." disable-output-escaping="yes"/></div>
			</div>
		</xsl:for-each>
	</xsl:template>
	<xsl:template name="list_metadata_common">
		<xsl:call-template name="list_subjects"/>
		<xsl:call-template name="list_tasks"/>
		<xsl:call-template name="list_targets"/>
	</xsl:template>
	<xsl:template name="fill_content">
		<xsl:for-each select="item">
			<xsl:choose>
				<xsl:when test="@type = 'unit'">
					<div class="unit">
						<xsl:if test="@visibility = 'hidden'">
							<xsl:attribute name="visibility">hidden</xsl:attribute>
							<xsl:attribute name="style">display: none;</xsl:attribute>
						</xsl:if>
						<div class="header"><xsl:value-of select="@title" disable-output-escaping="yes"/></div>
						<xsl:call-template name="list_metadata_common"/>
						<xsl:call-template name="fill_content"/>
					</div>
				</xsl:when>
				<xsl:when test="@type = 'topic'">
					<div class="topic">
						<xsl:if test="@visibility = 'hidden'">
							<xsl:attribute name="visibility">hidden</xsl:attribute>
							<xsl:attribute name="style">display: none;</xsl:attribute>
						</xsl:if>
						<div class="header"><xsl:value-of select="@title" disable-output-escaping="yes"/></div>
						<xsl:call-template name="list_metadata_common"/>
						<xsl:call-template name="fill_content"/>
					</div>
				</xsl:when>
				<xsl:when test="@type = 'theme'">
					<div class="theme">
						<xsl:if test="@visibility = 'hidden'">
							<xsl:attribute name="visibility">hidden</xsl:attribute>
							<xsl:attribute name="style">display: none;</xsl:attribute>
						</xsl:if>
						<div class="header"><xsl:value-of select="@title" disable-output-escaping="yes"/></div>
						<xsl:call-template name="list_metadata_common"/>
						<xsl:call-template name="fill_content"/>
					</div>
				</xsl:when>
				<xsl:when test="@type = 'lesson'">
					<div class="lesson">
						<xsl:if test="@visibility = 'hidden'">
							<xsl:attribute name="visibility">hidden</xsl:attribute>
							<xsl:attribute name="style">display: none;</xsl:attribute>
						</xsl:if>
						<a>
							<xsl:attribute name="name"><xsl:value-of select="@DB_ID"/></xsl:attribute>
							<div class="header"><xsl:value-of select="@title" disable-output-escaping="yes"/></div>
						</a>
						<xsl:call-template name="list_metadata_common"/>
						<xsl:call-template name="fill_content"/>
					</div>
				</xsl:when>
				<xsl:otherwise>
					<div class="missed">
						MISSED<br />
						"<xsl:copy-of select="."/>"
					</div>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:for-each>
		<xsl:for-each select="studiedproblems">
			<xsl:choose>
				<xsl:when test="studiedproblem">
					<div class="studiedproblems">
						<xsl:if test="@visibility = 'hidden'">
							<xsl:attribute name="visibility">hidden</xsl:attribute>
							<xsl:attribute name="style">display: none;</xsl:attribute>
						</xsl:if>
						<div class="header"><xsl:value-of select="@title" disable-output-escaping="yes"/></div>
						<xsl:for-each select="studiedproblem">
							<div class="studiedproblem">
								<xsl:if test="@visibility = 'hidden'">
									<xsl:attribute name="visibility">hidden</xsl:attribute>
									<xsl:attribute name="style">display: none;</xsl:attribute>
								</xsl:if>
								<a>
									<xsl:attribute name="name"><xsl:value-of select="@DB_ID"/></xsl:attribute>
								</a>
								<xsl:call-template name="transform_studiedproblem"/> 
							</div>
						</xsl:for-each>
					</div>
				</xsl:when>
				<xsl:otherwise>
					<div class="missed">
						MISSED<br />
						"<xsl:copy-of select="."/>"
					</div>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:for-each>
		<xsl:for-each select="test | references">
			<xsl:choose>
				<xsl:when test="question">
					<div class="test">
						<xsl:if test="@visibility = 'hidden'">
							<xsl:attribute name="visibility">hidden</xsl:attribute>
							<xsl:attribute name="style">display: none;</xsl:attribute>
						</xsl:if>
						<div class="header"><xsl:value-of select="@title" disable-output-escaping="yes"/></div>
						<xsl:call-template name="transform_test">
							<xsl:with-param name="position"><xsl:value-of select="position()"/></xsl:with-param>
						</xsl:call-template>
					</div>
				</xsl:when>
				<xsl:when test="reference">
					<div class="references">
						<div class="header"><xsl:value-of select="@title" disable-output-escaping="yes"/></div>
							<ol>
							<xsl:for-each select="reference">
								<li class="reference"><xsl:value-of select="." disable-output-escaping="yes"/></li>
							</xsl:for-each>
							</ol>
					</div>
				</xsl:when>
				<xsl:otherwise>
					<div class="missed">
						MISSED<br />
						"<xsl:copy-of select="."/>"
					</div>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:for-each>
	</xsl:template>
	<xsl:template name="transform_studiedproblem">
		<div class="header"><xsl:value-of select="@title" disable-output-escaping="yes"/></div>
		<xsl:for-each select="*">
			<xsl:choose>
				<xsl:when test="name(.) = 'text'">
					<div class="paragraph">
						<a>
							<xsl:attribute name="name"><xsl:value-of select="@DB_ID"/></xsl:attribute>
						</a>
						<xsl:value-of select="." disable-output-escaping="yes"/>
					</div>
				</xsl:when>
				<xsl:when test="name(.) = 'object'">
					<xsl:if test="@visible != 'false'">
						<div class="object">
							<a>
								<xsl:attribute name="name"><xsl:value-of select="@DB_ID"/></xsl:attribute>
							</a>
							<img>
								<xsl:attribute name="src">images/<xsl:value-of select="@type"/>.gif</xsl:attribute>
								<xsl:attribute name="title"><xsl:value-of select="@title"/></xsl:attribute>
							</img>
							<span class="object_title">
								<xsl:text disable-output-escaping="yes">&amp;nbsp;</xsl:text>
								<a>
									<xsl:attribute name="href">javascript:show_object('<xsl:value-of select="@DB_ID"/>')</xsl:attribute>
									<xsl:value-of select="@title"/> [<xsl:value-of select="translate(@type,'abcdefghijklmnopqrstuvwxyz','ABCDEFGHIJKLMNOPQRSTUVWXYZ')"/> file]
								</a>
							</span>
						</div>
					</xsl:if>
				</xsl:when>
			</xsl:choose>
		</xsl:for-each>
	</xsl:template>
	<xsl:template name="transform_test">
		<xsl:param name="position">0</xsl:param>
		<xsl:for-each select="question">
			<xsl:call-template name="transform_question">
				<xsl:with-param name="position"><xsl:value-of select="$position"/>-<xsl:value-of select="position()"/></xsl:with-param>
			</xsl:call-template>
		</xsl:for-each>
	</xsl:template>
	<xsl:template name="transform_question">
		<xsl:param name="position">0-0</xsl:param>
		<div class="question">
			<a>
				<xsl:attribute name="name"><xsl:value-of select="@DB_ID"/></xsl:attribute>
			</a>
			<div class="header"><xsl:value-of select="text" disable-output-escaping="yes"/></div>
			<xsl:choose>
				<xsl:when test="answers/@type = 'multiple'">
					<div class="answers_multiple">
						<xsl:for-each select="answers/answer">
							<div class="answer">
								<input type="checkbox"><xsl:attribute name="id">ID-<xsl:value-of select="$position"/>-<xsl:value-of select="position()"/></xsl:attribute></input>
								<label><xsl:attribute name="for">ID-<xsl:value-of select="$position"/>-<xsl:value-of select="position()"/></xsl:attribute><xsl:value-of select="." disable-output-escaping="yes"/></label>
							</div>
						</xsl:for-each>
					</div>
				</xsl:when>
				<xsl:when test="answers/@type = 'single'">
					<div class="answers_single">
						<xsl:for-each select="answers/answer">
							<div class="answer">
								<input type="radio" name="RADIO_BUTTON_INTERACTION"><xsl:attribute name="id">ID-<xsl:value-of select="$position"/>-<xsl:value-of select="position()"/></xsl:attribute></input>
								<label><xsl:attribute name="for">ID-<xsl:value-of select="$position"/>-<xsl:value-of select="position()"/></xsl:attribute><xsl:value-of select="." disable-output-escaping="yes"/></label>
							</div>
						</xsl:for-each>
					</div>
				</xsl:when>
				<xsl:when test="answers/@type = 'fill'">
					<div class="answers_fill">
						<table>
							<tbody>
								<xsl:for-each select="answers/answer">
									<tr>
										<td><xsl:value-of select="." disable-output-escaping="yes"/></td>
										<td><input type="text"/></td>
									</tr>
								</xsl:for-each>
							</tbody>
						</table>
					</div>
				</xsl:when>
				<xsl:when test="answers/@type = 'compare'">
					<div class="answers_compare">
						<table>
							<tbody>
								<xsl:for-each select="answers/answer">
									<tr>
										<td><xsl:value-of select="." disable-output-escaping="yes"/></td>
										<td>
											<select name="SELECT_INTERACTION">
												<xsl:attribute name="size"><xsl:value-of select="count(../answer)" /></xsl:attribute>
												<xsl:for-each select="../answer">
													<option><xsl:value-of select="@right" disable-output-escaping="yes"/></option>
												</xsl:for-each>
											</select>
										</td>
									</tr>
								</xsl:for-each>
							</tbody>
						</table>
					</div>
				</xsl:when>
			</xsl:choose>
		</div>
	</xsl:template>
</xsl:stylesheet>
