<?xml version="1.0" encoding="windows-1251"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:template match="/">
        <html>
                <head>
                        <link rel="stylesheet" href="style.css" type="text/css"/>
                        <title>Результаты тестирования</title>
                </head>
                <body>  <h3 align="center">Результаты тестирования</h3><BR/>
                <xsl:apply-templates select="./*"/>
                </body>
        </html>
</xsl:template>
<xsl:template match="control">

        <xsl:variable name="count_sch" select="count(./group/student)"/>
        <xsl:choose>
                <xsl:when test="$count_sch>0">

                        <table width='100%' cellspacing='0' cellpadding='5' class="tab">
                        <tr>
                        <th>Login</th>
                        <th width="100%">ФИО</th>
                        <th>Дата сдачи</th>
                        <th nowrap="">Процент выполнения</th>
                        <th>Набранный балл</th>
                        <th>Оценка</th>
                        </tr>
                        <xsl:apply-templates select="./group"/>
                        <tr>
                         <td colspan="6"><img src="void.gif" width="1" height="1"/>
                          Средний процент выполнения: <xsl:value-of disable-output-escaping='yes' select='group/@aver_percent'/><br />
                          <img src="void.gif" width="1" height="1"/>
                          Средняя оценка: <xsl:value-of disable-output-escaping='yes' select='group/@aver_bal'/><br />
                         </td>
                        </tr>
                       </table>
                </xsl:when>
                <xsl:otherwise>
                        <h3 align='center'>В выбранный период для указанной группы занятий не было</h3>
                </xsl:otherwise>
        </xsl:choose>
</xsl:template>



<xsl:template match="student">
<tr>
        <td><img src="void.gif" width="1" height="1"/>
        <xsl:value-of  disable-output-escaping='yes'  select='./@stud_login'/>
        </td>
        <td><img src="void.gif" width="1" height="1"/>
        <xsl:value-of  disable-output-escaping='yes'  select='./@stud_name'/>
        </td>
        <td align="center"><img src="void.gif" width="1" height="1"/>
        <xsl:value-of  disable-output-escaping='yes'  select='./mark/@date'/>
        </td>
        <td align="center"><img src="void.gif" width="1" height="1"/>
        <xsl:value-of  disable-output-escaping='yes'  select='./mark/@percent'/>
        </td>
        <td align="center" nowrap=""><img src="void.gif" width="1" height="1"/>
        <xsl:value-of  disable-output-escaping='yes'  select='./mark/@count_ball'/>
        </td>
        <td align="center" nowrap=""><img src="void.gif" width="1" height="1"/>
        <xsl:value-of  disable-output-escaping='yes'  select='./mark/@ball'/>
        </td>
</tr>
</xsl:template>
</xsl:stylesheet>