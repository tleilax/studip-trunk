<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">
	<xsl:output method="html" encoding="iso-8859-1"/>
	<xsl:template match="/">
	<html>
		<body>
			<xsl:for-each select="studip/institut">
				<h1>Institut: <xsl:value-of select="name"/>
				</h1>
				<b>Fakult�t: </b>
				<xsl:value-of select="fakultaet"/>
				<br/>
				<b>Homepage: </b>
				<xsl:value-of select="homepage"/>
				<br/>
				<b>Strasse: </b>
				<xsl:value-of select="strasse"/>
				<br/>
				<b>Postleitzahl: </b>
				<xsl:value-of select="plz"/>
				<br/>
				<b>Telefon: </b>
				<xsl:value-of select="telefon"/>
				<br/>
				<b>Fax: </b>
				<xsl:value-of select="fax"/>
				<br/>
				<b>E-mail: </b>
				<xsl:value-of select="email"/>
				<br/>
				<br/>
				<xsl:if test="seminare">
					<table width="100%" cellpadding="5" cellspacing="0" border="1">
						<tr>
							<td colspan="2">
								<h2>Seminare</h2>
							</td>
						</tr>
						<xsl:choose>
							<xsl:when test="seminare/gruppe">
								<xsl:for-each select="seminare/gruppe">
									<tr bgcolor="#FFFFFF">
										<td colspan="2">
											<h2>
												<font color="#000000">
													<b>
													<xsl:value-of select="@key"/>
													</b>
												</font>
											</h2>
										</td>
									</tr>
									<xsl:choose>
										<xsl:when test="untergruppe">
											<xsl:for-each select="untergruppe">
												<tr bgcolor="#FFFFFF">
													<td colspan="2">
														<h3>
															<font color="#000000">
																<b>
																<xsl:value-of select="@key"/>
																</b>
															</font>
														</h3>
													</td>
												</tr>
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
					</table>
				</xsl:if>
				<xsl:if test="personen">
					<table width="100%" cellpadding="5" cellspacing="0" border="1">
						<tr>
							<td colspan="5">
								<h2>MitarbeiterInnen</h2>
							</td>
						</tr>
						<tr>
							<td colspan="5">
								<br/>
							</td>
						</tr>
						<tr bgcolor="#FFFFFF">
							<td>
								<b>
									<font color="#000000">Name</font>
									</b>
								</td>
								<td>
									<b>
										<font color="#000000">Telefon</font>
									</b>
								</td>
								<td>
									<b>
										<font color="#000000">Raum</font>
									</b>
								</td>
								<td>
									<b>
										<font color="#000000">Sprechzeiten</font>
									</b>
								</td>
								<td>
									<b>
										<font color="#000000">E-Mail</font>
									</b>
								</td>
							</tr>
						<xsl:choose>
							<xsl:when test="personen/gruppe">
								<xsl:for-each select="personen/gruppe">
									<tr bgcolor="#FFFFFF">
										<td colspan="5">
											<font color="#000000">
												<h2>
													<xsl:value-of select="@key"/>
												</h2>
											</font>
										</td>
									</tr>
								<xsl:call-template name="showperson"/>
								</xsl:for-each>
							</xsl:when>
							<xsl:otherwise>
								<xsl:for-each select="personen">
									<xsl:call-template name="showperson"/>
								</xsl:for-each>
							</xsl:otherwise>
						</xsl:choose>
						</table>
					</xsl:if>
					<br/>
					<br/>
				</xsl:for-each>
			</body>
		</html>
	</xsl:template>

<xsl:template name="showperson">
	<xsl:for-each select="person">
		<tr>
			<td>
				<xsl:if test="titel">
					<xsl:value-of select="titel"/>
					<xsl:text> </xsl:text>
				</xsl:if>
				<xsl:value-of select="vorname"/>
				<xsl:text> </xsl:text>
				<xsl:value-of select="nachname"/>
				<xsl:if test="titel2">
					<xsl:text>, </xsl:text>
					<xsl:value-of select="titel2"/>
				</xsl:if>
				<br/>
			</td>
			<td>
				<xsl:if test="telefon">
					<xsl:value-of select="telefon"/>
				</xsl:if>
				<br/>
			</td>
			<td>
				<xsl:if test="raum">
					<xsl:value-of select="raum"/>
				</xsl:if>
				<br/>
			</td>
			<td>
				<xsl:if test="sprechzeiten">
					<xsl:value-of select="sprechzeiten"/>
				</xsl:if>
				<br/>
			</td>
			<td>
				<xsl:if test="email">
					<xsl:value-of select="email"/>
				</xsl:if>
				<br/>
			</td>
		</tr>
	</xsl:for-each>
</xsl:template>


<xsl:template name="showseminar">
	<xsl:for-each select="seminar">
		<tr bgcolor="#FFFFFF" border="0" align="left">
			<td>
				<font color="#000000">
					<b>
						<xsl:for-each select="dozenten/dozent">
							<xsl:if test="position() &gt; 1">
								<xsl:text>, </xsl:text>
							</xsl:if>
							<xsl:value-of select="."/>
						</xsl:for-each>
					</b>
				</font>
			</td>
			<td>
				<font color="#000000">
					<b>
						<xsl:value-of select="titel"/>
					</b>
				</font>
			</td>
		</tr>
		<xsl:if test="untertitel">
		<tr>
			<td>
				<b>Untertitel: </b>
			</td>
			<td>
				<xsl:value-of select="untertitel"/>
			</td>
		</tr>
		</xsl:if>
		<tr>
			<td>
				<b>DozentIn: </b>
			</td>
			<td>
				<xsl:for-each select="dozenten/dozent">
					<xsl:if test="position() &gt; 1">
						<xsl:text>, </xsl:text>
					</xsl:if>
					<xsl:value-of select="."/>
				</xsl:for-each>
			</td>
		</tr>
		<tr>
			<td>
				<b>Termin: </b>
			</td>
			<td>
				<xsl:value-of select="termine/termin"/>
			</td>
		</tr>
		<tr>
			<td>
				<b>Erster Termin: </b>
			</td>
			<td>
				<xsl:value-of select="termine/erstertermin"/>
			</td>
		</tr>
		<xsl:if test="termine/vorbesprechung">
			<tr>
				<td>
					<b>Vorbesprechung: </b>
				</td>
				<td>
					<xsl:value-of select="termine/vorbesprechung"/>
				</td>
			</tr>
		</xsl:if>
		<xsl:if test="status">
			<tr>
				<td>
					<b>Status: </b>
				</td>
				<td>
					<xsl:value-of select="status"/>
				</td>
			</tr>
		</xsl:if>
		<xsl:if test="beschreibung">
			<tr>
				<td>
					<b>Beschreibung: </b>
				</td>
				<td>
					<xsl:value-of select="beschreibung"/>
				</td>
			</tr>
		</xsl:if>
		<xsl:if test="raum">
			<tr>
				<td>
					<b>Raum: </b>
				</td>
				<td>
					<xsl:value-of select="raum"/>
				</td>
			</tr>
		</xsl:if>
		<xsl:if test="sonstiges">
			<tr>
				<td>
					<b>Sonstiges: </b>
				</td>
				<td>
					<xsl:value-of select="sonstiges"/>
				</td>
			</tr>
		</xsl:if>
		<xsl:if test="art">
			<tr>
				<td>
					<b>Art der Veranstaltung: </b>
				</td>
				<td>
					<xsl:value-of select="art"/>
				</td>
			</tr>
		</xsl:if>
		<xsl:if test="teilnehmer">
			<tr>
				<td>
					<b>Teilnehmer: </b>
				</td>
				<td>
					<xsl:value-of select="teilnehmer"/>
				</td>
			</tr>
		</xsl:if>
		<xsl:if test="voraussetzung">
			<tr>
				<td>
					<b>Voraussetzungen: </b>
				</td>
				<td>
					<xsl:value-of select="voraussetzung"/>
				</td>
			</tr>
		</xsl:if>
		<xsl:if test="lernorga">
			<tr>
				<td>
					<b>Lernorganisation: </b>
				</td>
				<td>
					<xsl:value-of select="lernorga"/>
				</td>
			</tr>
		</xsl:if>
		<xsl:if test="schein">
			<tr>
				<td>
					<b>Leistungsnachweis: </b>
				</td>
				<td>
					<xsl:value-of select="schein"/>
				</td>
			</tr>
		</xsl:if>
		<xsl:if test="ects">
			<tr>
				<td>
					<b>ECTS: </b>
				</td>
				<td>
					<xsl:value-of select="ects"/>
				</td>
			</tr>
		</xsl:if>
		<xsl:if test="bereich">
			<tr>
				<td>
					<b>Bereich: </b>
				</td>
				<td>
					<xsl:value-of select="bereich"/>
				</td>
			</tr>
		</xsl:if>
		<tr>
			<td colspan="2">
				<br/>
			</td>
		</tr>
	</xsl:for-each>
</xsl:template>		
</xsl:stylesheet>