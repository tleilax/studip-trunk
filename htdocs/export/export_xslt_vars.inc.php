<?
$output_formats = array(
	"htm"		=>		"Hypertext (HTML)", 
	"rtf"			=>		"Rich Text Format (RTF)", 
	"txt"			=>		"Text (TXT)", 
	"fo"			=>		"Adobe Postscript (PDF)"
	"xml"		=>		"Extensible Markup Language (XML)", 
);


$xslt_files["txt-standard"]["name"] = "Standardmodul";
$xslt_files["txt-standard"]["desc"] = "Standardmodul zur Ausgabe von Personen- oder Veranstaltungsdaten im Textformat. 
	Die Daten werden nur mit Tabulatoren und Bindestrichen formatiert.
	Die Ausgabe-Datei kann in einer Textverarbeitung bearbeitet werden.";
$xslt_files["txt-standard"]["file"] = "txt-vp-1.xsl";
$xslt_files["txt-standard"]["txt"] = true;
$xslt_files["txt-standard"]["person"] = true;
$xslt_files["txt-standard"]["veranstaltung"] = true;

$xslt_files["txt-noformat"]["name"] = "Unformatierte Ausgabe";
$xslt_files["txt-noformat"]["desc"] = "Modul zur Ausgabe von Personen- oder Veranstaltungsdaten im Textformat. 
	Die Daten werden nicht formatiert.
	Die Ausgabe-Datei kann in einer Textverarbeitung bearbeitet werden.";
$xslt_files["txt-noformat"]["file"] = "txt-vp-2.xsl";
$xslt_files["txt-noformat"]["txt"] = true;
$xslt_files["txt-noformat"]["person"] = true;
$xslt_files["txt-noformat"]["veranstaltung"] = true;



$xslt_files["html-standard"]["name"] = "Standardmodul";
$xslt_files["html-standard"]["desc"] = "Standardmodul zur Ausgabe von Personen- oder Veranstaltungsdaten als HTML-Seite. 
	Personendaten werden als Tabelle angezeigt.
	Die Ausgabe-Datei kann in einem Web-Browser angezeigt werden.";
$xslt_files["html-standard"]["file"] = "html-vp-1.xsl";
$xslt_files["html-standard"]["htm"] = true;
$xslt_files["html-standard"]["html"] = true;
$xslt_files["html-standard"]["person"] = true;
$xslt_files["html-standard"]["veranstaltung"] = true;

$xslt_files["html-druck"]["name"] = "Druckmodul";
$xslt_files["html-druck"]["desc"] = "Modul zur Ausgabe von Personen- oder Veranstaltungsdaten als HTML-Seite. 
	Es wird eine druckbare HTML-Seite ohne Farben erzeugt.
	Die Ausgabe-Datei kann in einem Web-Browser angezeigt und ausgedruckt werden.";
$xslt_files["html-druck"]["file"] = "html-vp-2.xsl";
$xslt_files["html-druck"]["htm"] = true;
$xslt_files["html-druck"]["html"] = true;
$xslt_files["html-druck"]["person"] = true;
$xslt_files["html-druck"]["veranstaltung"] = true;

$xslt_files["html-liste"]["name"] = "&Uuml;bersicht";
$xslt_files["html-liste"]["desc"] = "Modul zur Ausgabe von Personen- oder Veranstaltungsdaten als HTML-Seite. 
	Es werden nur die Grunddaten der Veranstaltungen / Personen in eine Tabelle geschrieben. 
	Die Ausgabe-Datei kann in einem Web-Browser angezeigt werden.";
$xslt_files["html-liste"]["file"] = "html-vp-3.xsl";
$xslt_files["html-liste"]["htm"] = true;
$xslt_files["html-liste"]["html"] = true;
$xslt_files["html-liste"]["person"] = true;
$xslt_files["html-liste"]["veranstaltung"] = true;



$xslt_files["rtf-standard"]["name"] = "Standardmodul";
$xslt_files["rtf-standard"]["desc"] = "Standardmodul zur Ausgabe von Personen- oder Veranstaltungsdaten als RTF-Datei. 
	Die Ausgabe-Datei kann in einer Textverarbeitung bearbeitet werden.";
$xslt_files["rtf-standard"]["file"] = "rtf-vp-1.xsl";
$xslt_files["rtf-standard"]["rtf"] = true;
$xslt_files["rtf-standard"]["person"] = true;
$xslt_files["rtf-standard"]["veranstaltung"] = true;

$xslt_files["rtf-liste"]["name"] = "&Uuml;bersicht";
$xslt_files["rtf-liste"]["desc"] = "Modul zur Ausgabe von Personen- oder Veranstaltungsdaten als RTF-Datei. 
	Es werden nur die Grunddaten in eine Tabelle geschrieben 
	(DozentInnen, Titel, Status, Termin und Raum bzw.Name, Telefon, Sprechzeiten, Raum, E-Mail). 
	Ein Deckblatt wird automatisch erzeugt. 
	Die Ausgabe-Datei kann in einer Textverarbeitung bearbeitet werden.";
$xslt_files["rtf-liste"]["file"] = "rtf-vp-2.xsl";
$xslt_files["rtf-liste"]["rtf"] = true;
$xslt_files["rtf-liste"]["person"] = true;
$xslt_files["rtf-liste"]["veranstaltung"] = true;

$xslt_files["rtf-kommentar"]["name"] = "Vorlesungskommentar";
$xslt_files["rtf-kommentar"]["desc"] = "Modul zur Ausgabe von Veranstaltungsdaten als Vorlesungskommentar im Rich-Text-Format. 
	Der Kommentar enth&auml;lt die Veranstaltungs-Details-Daten.
	Es wird automatisch ein Deckblatt generiert. 
	Die Ausgabe-Datei kann in einer Textverarbeitung bearbeitet werden.";
$xslt_files["rtf-kommentar"]["file"] = "rtf-vp-3.xsl";
$xslt_files["rtf-kommentar"]["rtf"] = true;
$xslt_files["rtf-kommentar"]["veranstaltung"] = true;



$xslt_files["pdf-standard"]["name"] = "Standardmodul";
$xslt_files["pdf-standard"]["desc"] = "Standardmodul zur Ausgabe von Veranstaltungs- und Personendaten als Vorlesungskommentar 
	bzw. MitarbeiterInnenlisten mit Seitenzahlen im Adobe PDF-Format. 
	Die Datei kann mit dem Abrobat PDF-Reader gelesen werden.";
$xslt_files["pdf-standard"]["file"] = "pdf-vp-1.xsl";
$xslt_files["pdf-standard"]["fo"] = true;
$xslt_files["pdf-standard"]["person"] = true;
$xslt_files["pdf-standard"]["veranstaltung"] = true;

$xslt_files["pdf-kommentar"]["name"] = "Vorlesungskommentar";
$xslt_files["pdf-kommentar"]["desc"] = "Modul zur Ausgabe von Veranstaltungsdaten als Vorlesungskommentar im Adobe PDF-Format. 
	Die Seiten enthalten eine Kopfzeile und eine Fu&szlig;zeile mit der Seitenzahl. Deckblatt und Inhaltsverzeichnis werden automatisch generiert. 
	Die Datei kann mit dem Abrobat PDF-Reader gelesen werden.";
$xslt_files["pdf-kommentar"]["file"] = "pdf-vp-2.xsl";
$xslt_files["pdf-kommentar"]["fo"] = true;
$xslt_files["pdf-kommentar"]["veranstaltung"] = true;

$xslt_files["pdf-staff"]["name"] = "MitarbeiterInnenlisten";
$xslt_files["pdf-staff"]["desc"] = "Modul zur Ausgabe von Personendaten als MitarbeiterInnenlisten im Adobe PDF-Format. 
	Die Grunddaten der Personen (Name, Telefon, Sprechzeiten, Raum, E-Mail) werden in einer Tabelle angezeigt. 
	Die Seiten enthalten eine Kopfzeile und eine Fu&szlig;zeile mit der Seitenzahl. Es wird automatisch ein Deckblatt und ein Inhaltsverzeichnis generiert. 
	Die Datei kann mit dem PDF-Abrobat Reader gelesen werden.";
$xslt_files["pdf-staff"]["file"] = "pdf-vp-2.xsl";
$xslt_files["pdf-staff"]["fo"] = true;
$xslt_files["pdf-staff"]["person"] = true;

$xslt_files["pdf-liste"]["name"] = "Vorlesungsverzeichnis";
$xslt_files["pdf-liste"]["desc"] = "Modul zur Ausgabe von Veranstaltungsdaten als Vorlesungsverzeichnis im Adobe PDF-Format. 
	Die Grunddaten der Veranstaltungen (DozentInnen, Titel, Status, Termin und Raum) werden in einer Tabelle angezeigt. 
	Es wird automatisch ein Deckblatt und ein Inhaltsverzeichnis generiert. 
	Die Datei kann mit dem Abrobat Reader gelesen werden.";
$xslt_files["pdf-liste"]["file"] = "pdf-v-3.xsl";
$xslt_files["pdf-liste"]["fo"] = true;
$xslt_files["pdf-liste"]["veranstaltung"] = true;

$xslt_files["pdf-kommentar2"]["name"] = "Vorlesungskommentar, Layout 2";
$xslt_files["pdf-kommentar2"]["desc"] = "Modul zur Ausgabe von Veranstaltungsdaten als Vorlesungskommentar im Adobe PDF-Format. 
	Deckblatt und Inhaltsverzeichnis werden automatisch generiert. 
	Die Datei kann mit dem Abrobat Reader gelesen werden.";
$xslt_files["pdf-kommentar2"]["file"] = "pdf-v-4.xsl";
$xslt_files["pdf-kommentar2"]["fo"] = true;
$xslt_files["pdf-kommentar2"]["veranstaltung"] = true;


?>