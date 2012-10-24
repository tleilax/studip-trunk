<?
# Lifter010: TODO

$translations = array(
    "suchen" => _("suchen")
  , "Sonntag" => _("Sonntag")
  , "Montag" => _("Montag")
  , "Dienstag" => _("Dienstag")
  , "Mittwoch" => _("Mittwoch")
  , "Donnerstag" => _("Donnerstag")
  , "Freitag" => _("Freitag")
  , "Samstag" => _("Samstag")
  , "Bitte �ndern Sie ihre Eingabe" => _("Bitte �ndern Sie ihre Eingabe")
  , "Bitte w�hlen Sie einen Wert aus" => _("Bitte w�hlen Sie einen Wert aus")
  , "Bitte geben Sie eine g�ltige E-Mail-Adresse ein" => _("Bitte geben Sie eine g�ltige E-Mail-Adresse ein")
  , "Bitte geben Sie eine Zahl ein" => _("Bitte geben Sie eine Zahl ein")
  , "Bitte geben Sie eine g�ltige Web-Adresse ein" => _("Bitte geben Sie eine g�ltige Web-Adresse ein")
  , "Der eingegebene Wert darf nicht gr��er als $1 sein" => _("Der eingegebene Wert darf nicht gr��er als $1 sein")
  , "Der eingegebene Wert darf nicht kleiner als $1 sein" => _("Der eingegebene Wert darf nicht kleiner als $1 sein")
  , "Dies ist ein erforderliches Feld" => _("Dies ist ein erforderliches Feld")
  , "Nicht buchbare R�ume:" => _("Nicht buchbare R�ume:")
  // add your translations here
);

// translations have to be UTF8 for #json_encode
$translations = $plugin->utf8EncodeArray($translations);

?>
String.toLocaleString({
  "<?= strtr($language, "_", "-") ?>": <?= json_encode($translations) ?>

});
