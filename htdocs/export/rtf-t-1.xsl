<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">
	<xsl:output method="html" encoding="iso-8859-1"/>
	<xsl:template match="/">
<xsl:text>{\rtf1\ansi\ansicpg1252\deff0\deflang1031{\fonttbl{\f0\fnil\fcharset0 Times New Roman;}}
\viewkind4\uc1\pard</xsl:text>

		<xsl:for-each select="studip">
				<xsl:text>\par\fs36 Veranstaltung: </xsl:text><xsl:value-of select="@range"/>
			<xsl:for-each select="institut"><xsl:text>
				\par</xsl:text>
				<xsl:if test="personen">
			<xsl:text>
\par\fs28 TeilnehmerInnenliste
\par\trowd \trgaph70\trleft-70\trbrdrt\brdrs\brdrw10 \trbrdrl\brdrs\brdrw10 \trbrdrb\brdrs\brdrw10 
\trbrdrr\brdrs\brdrw10 \trbrdrh\brdrs\brdrw10 \trbrdrv\brdrs\brdrw10 \clvertalt\clbrdrt\brdrs\brdrw10 \clbrdrl\brdrs\brdrw10 \clbrdrb\brdrs\brdrw10 \clbrdrr\brdrs\brdrw10 \cltxlrtb \cellx1839\clvertalt\clbrdrt\brdrs\brdrw10 \clbrdrl\brdrs\brdrw10 \clbrdrb
\brdrs\brdrw10 \clbrdrr\brdrs\brdrw10 \cltxlrtb \cellx3748\clvertalt\clbrdrt\brdrs\brdrw10 \clbrdrl\brdrs\brdrw10 \clbrdrb\brdrs\brdrw10 \clbrdrr\brdrs\brdrw10 \cltxlrtb \cellx5657\clvertalt\clbrdrt\brdrs\brdrw10 \clbrdrl\brdrs\brdrw10 \clbrdrb
\brdrs\brdrw10 \clbrdrr\brdrs\brdrw10 \cltxlrtb \cellx7566\clvertalt\clbrdrt\brdrs\brdrw10 \clbrdrl\brdrs\brdrw10 \clbrdrb\brdrs\brdrw10 \clbrdrr\brdrs\brdrw10 \cltxlrtb \cellx9475\pard\plain \nowidctlpar\intbl\adjustright \lang1031\cgrid 
{\fs24\b Name\b0\cell\b Telefon\b0\cell\b Adresse\b0\cell\b E-Mail\b0\cell\b Kontingent\b0\cell }\pard \nowidctlpar\widctlpar\intbl\adjustright {\row }\pard</xsl:text>

						<xsl:choose>
							<xsl:when test="personen/gruppe">
								<xsl:for-each select="personen/gruppe">
			<xsl:text>
\brdrt\brdrs\brdrw10\brsp20 \brdrl\brdrs\brdrw10\brsp80 \brdrb
\brdrs\brdrw10\brsp20 \brdrr\brdrs\brdrw10\brsp80 \adjustright \fs28\lang1031\cgrid { </xsl:text>
									<xsl:value-of select="@key"/>
			<xsl:text>\par }\pard</xsl:text>
								<xsl:call-template name="showperson"/>
								</xsl:for-each>
							</xsl:when>
							<xsl:otherwise>
								<xsl:for-each select="personen">
									<xsl:call-template name="showperson"/>
								</xsl:for-each>
							</xsl:otherwise>
						</xsl:choose>
					</xsl:if>
<xsl:text>
\page</xsl:text>
				</xsl:for-each>
\par\qr\fs16 Generiert von Stud.IP Version <xsl:value-of select="@version"/>
			</xsl:for-each>
		<xsl:text> }</xsl:text>
	</xsl:template>

<xsl:template name="showperson">
	<xsl:for-each select="person">
			<xsl:text>
\trowd \trgaph70\trleft-70\trbrdrt\brdrs\brdrw10 \trbrdrl\brdrs\brdrw10 \trbrdrb\brdrs\brdrw10 
\trbrdrr\brdrs\brdrw10 \trbrdrh\brdrs\brdrw10 \trbrdrv\brdrs\brdrw10 \clvertalt\clbrdrt\brdrs\brdrw10 \clbrdrl\brdrs\brdrw10 \clbrdrb\brdrs\brdrw10 \clbrdrr\brdrs\brdrw10 \cltxlrtb \cellx1839\clvertalt\clbrdrt\brdrs\brdrw10 \clbrdrl\brdrs\brdrw10 \clbrdrb
\brdrs\brdrw10 \clbrdrr\brdrs\brdrw10 \cltxlrtb \cellx3748\clvertalt\clbrdrt\brdrs\brdrw10 \clbrdrl\brdrs\brdrw10 \clbrdrb\brdrs\brdrw10 \clbrdrr\brdrs\brdrw10 \cltxlrtb \cellx5657\clvertalt\clbrdrt\brdrs\brdrw10 \clbrdrl\brdrs\brdrw10 \clbrdrb
\brdrs\brdrw10 \clbrdrr\brdrs\brdrw10 \cltxlrtb \cellx7566\clvertalt\clbrdrt\brdrs\brdrw10 \clbrdrl\brdrs\brdrw10 \clbrdrb\brdrs\brdrw10 \clbrdrr\brdrs\brdrw10 \cltxlrtb \cellx9475\pard\plain \nowidctlpar\intbl\adjustright \lang1031\cgrid 
{\fs24 </xsl:text>
				<xsl:if test="titel">
					<xsl:value-of select="titel"/>
					<xsl:text> </xsl:text>
				</xsl:if>
				<xsl:value-of select="vorname"/>
				<xsl:text> </xsl:text>
				<xsl:value-of select="nachname"/>
				<xsl:if test="titel2">
					<xsl:text> </xsl:text>
					<xsl:value-of select="titel2"/>
				</xsl:if>
<xsl:text>\cell </xsl:text>
				<xsl:if test="privadr">
					<xsl:value-of select="privadr"/>
				</xsl:if>
<xsl:text>\cell </xsl:text>
				<xsl:if test="privatnr">
					<xsl:value-of select="privatnr"/>
				</xsl:if>
<xsl:text>\cell </xsl:text>
				<xsl:if test="email">
					<xsl:value-of select="email"/>
				</xsl:if>
<xsl:text>\cell </xsl:text>
				<xsl:if test="kontingent">
					<xsl:value-of select="kontingent"/>
				</xsl:if>
<xsl:text>\cell }\pard \nowidctlpar\widctlpar\intbl\adjustright {\row }\pard</xsl:text>
	</xsl:for-each>
</xsl:template>
</xsl:stylesheet>