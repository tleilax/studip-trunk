<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">
	<xsl:output method="text" encoding="iso-8859-1"/>
	<xsl:template match="/">
	<xsl:for-each select="studip/institut">
Institut: <xsl:value-of select="name"/>
Fakult�t: <xsl:value-of select="fakultaet"/>
Homepage: <xsl:value-of select="homepage"/>
Strasse: <xsl:value-of select="strasse"/>
Postleitzahl: <xsl:value-of select="plz"/>
Telefon: <xsl:value-of select="telefon"/>
Fax: <xsl:value-of select="fax"/>
E-mail: <xsl:value-of select="email"/>
<xsl:text>
</xsl:text>				
<xsl:if test="seminare">
Seminare
			<xsl:choose>
				<xsl:when test="seminare/gruppe">
				<xsl:for-each select="seminare/gruppe">
Gruppe: <xsl:value-of select="@key"/><xsl:text>
</xsl:text>
					<xsl:choose>
					<xsl:when test="untergruppe">
						<xsl:for-each select="untergruppe">
Untergruppe:<xsl:value-of select="@key"/><xsl:text>
</xsl:text>
							<xsl:call-template name="showseminar"/>
						</xsl:for-each>
					</xsl:when>
					<xsl:otherwise>
						<xsl:call-template name="showseminar"/>
					</xsl:otherwise>
					</xsl:choose>
				</xsl:for-each>
				</xsl:when>
				<xsl:otherwise>
					<xsl:for-each select="seminare">
						<xsl:call-template name="showseminar"/>
					</xsl:for-each>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:if>
		<xsl:if test="personen">
MitarbeiterInnen
						<xsl:choose>
							<xsl:when test="personen/gruppe">
								<xsl:for-each select="personen/gruppe">
<xsl:text>
</xsl:text><xsl:value-of select="@key"/><xsl:text>
</xsl:text>
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
				</xsl:for-each>
	</xsl:template>

<xsl:template name="showperson">
	<xsl:for-each select="person">
<xsl:if test="titel">
				<xsl:value-of select="titel"/><xsl:text> </xsl:text> 
				</xsl:if>
				<xsl:value-of select="vorname"/>
				<xsl:text> </xsl:text>
				<xsl:value-of select="nachname"/>
				<xsl:if test="titel2">
					<xsl:text>, </xsl:text><xsl:value-of select="titel2"/> 
				</xsl:if>
				<xsl:if test="telefon">
Telefon: <xsl:value-of select="telefon"/>
				</xsl:if>
				<xsl:if test="raum">
Raum: <xsl:value-of select="raum"/>
				</xsl:if>
				<xsl:if test="sprechzeiten">
Sprechzeit: <xsl:value-of select="sprechzeiten"/>
				</xsl:if>
				<xsl:if test="email">
E-Mail: <xsl:value-of select="email"/>
				</xsl:if>
<xsl:text>
</xsl:text>
	</xsl:for-each>
</xsl:template>

<xsl:template name="showseminar">
	<xsl:for-each select="seminar">
Seminar - <xsl:for-each select="dozenten/dozent">
			<xsl:if test="position() &gt; 1">
				<xsl:text>, </xsl:text>
			</xsl:if>
			<xsl:value-of select="."/>
		</xsl:for-each> - <xsl:value-of select="titel"/> -
		<xsl:if test="untertitel">
Untertitel: <xsl:value-of select="untertitel"/>
		</xsl:if>
DozentIn: <xsl:for-each select="dozenten/dozent">
					<xsl:if test="position() &gt; 1">
						<xsl:text>, </xsl:text>
					</xsl:if>
					<xsl:value-of select="."/>
				</xsl:for-each>
Termin: <xsl:value-of select="termine/termin"/>
Erster Termin: <xsl:value-of select="termine/erstertermin"/>
		<xsl:if test="termine/vorbesprechung">
Vorbesprechung: <xsl:value-of select="termine/vorbesprechung"/>
		</xsl:if>
		<xsl:if test="status">
Status: <xsl:value-of select="status"/>
		</xsl:if>
		<xsl:if test="beschreibung">
Beschreibung: <xsl:value-of select="beschreibung"/>
		</xsl:if>
		<xsl:if test="raum">
Raum: <xsl:value-of select="raum"/>
		</xsl:if>
		<xsl:if test="sonstiges">
Sonstiges: <xsl:value-of select="sonstiges"/>
		</xsl:if>
		<xsl:if test="art">
Art der Veranstaltung: <xsl:value-of select="art"/>
		</xsl:if>
		<xsl:if test="teilnehmer">
Teilnehmer: <xsl:value-of select="teilnehmer"/>
		</xsl:if>
		<xsl:if test="voraussetzung">
Voraussetzungen: <xsl:value-of select="voraussetzung"/>
		</xsl:if>
		<xsl:if test="lernorga">
Lernorganisation: <xsl:value-of select="lernorga"/>
		</xsl:if>
		<xsl:if test="schein">
Leistungsnachweis: <xsl:value-of select="schein"/>
		</xsl:if>
		<xsl:if test="ects">
ECTS: <xsl:value-of select="ects"/>
		</xsl:if>
		<xsl:if test="bereich">
Bereich: <xsl:value-of select="bereich"/>
		</xsl:if>
<xsl:text>
</xsl:text>
	</xsl:for-each>
</xsl:template>		
</xsl:stylesheet>