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
  , "Bitte geben Sie g�ltige E-Mail-Adresse ein" => _("Bitte geben Sie g�ltige E-Mail-Adresse ein")
  , "Bitte geben Sie eine Zahl ein" => _("Bitte geben Sie eine Zahl ein")
  , "Bitte geben Sie eine g�ltige Web-Adresse ein" => _("Bitte geben Sie eine g�ltige Web-Adresse ein")
  , "Bitte geben Sie maximal $1 Zeichen ein" => _("Bitte geben Sie maximal $1 Zeichen ein")
  , "Bitte geben Sie mindestens $1 Zeichen ein" => _("Bitte geben Sie mindestens $1 Zeichen ein")
  , "Dies ist ein erforderliches Feld" => _("Dies ist ein erforderliches Feld")
  // add your translations here
);

// translations have to be UTF8 for #json_encode
$translations = $plugin->utf8EncodeArray($translations);

?>
String.toLocaleString({
  "<?= strtr($language, "_", "-") ?>": <?= json_encode($translations) ?>

});
