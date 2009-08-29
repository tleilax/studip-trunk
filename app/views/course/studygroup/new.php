<?php
$infobox = array();
$infobox['picture'] = 'infoboxbild_studygroup.jpg';
$infobox['content'] = array(
    array(
        'kategorie'=>_("Information"),
        'eintrag'=>array(
            array("text"=>_("Studentische Arbeitsgruppen sind eine einfache M�glichkeit, mit KommilitonInnen, KollegInnen und anderen zusammenzuarbeiten. JedeR kann Studiengruppen gr�nden."),"icon"=>"ausruf_small2.gif"),
            array("text"=>_("W�hlen Sie 'Offen f�r alle', wenn beliebige Nutzer der Gruppe ohne Nachfrage beitreten k�nnen sollen. 'Auf Anfrage' erfordert Ihr Eingreifen: Sie m�ssen jede einzelne Aufnahmeanfrage annehmen oder ablehnen."),"icon"=>"ausruf_small2.gif"),
            array("text"=>_("Alle Einstellungen k�nnen auch sp�ter noch unter dem Reiter 'Admin' ge�ndert werden."),"icon"=>"ausruf_small2.gif")
            )    
    )
);

?>

<?= $this->render_partial("course/studygroup/_feedback") ?>
<h1><?= _("Studentische Arbeitsgruppe anlegen") ?></h1>

<form action="<?= $controller->url_for('course/studygroup/create') ?>" method=post>

<table class="blank" width="75%" cellspacing="5" cellpadding="0" border="0" style="margin-left:75px; margin-right:300px;">
<tr>
  <td style='text-align:right; font-size:150%;'><?= _("Name:") ?></td>
  <td style='font-size:150%;'><input type='text' name='groupname' size='25' value="<?= htmlReady($this->flash['request']['groupname'])?>" style='font-size:100%'></td>
</tr>

<tr>
  <td style='text-align:right; vertical-align:top;'><?= _("Beschreibung:") ?></td>
  <td><textarea name='groupdescription' rows=5 cols=50><?= ($this->flash['request']['groupdescription'] ? htmlReady($this->flash['request']['groupdescription']) : _("Hier aussagekr�ftige Beschreibung eingeben.")) ?></textarea></td>
</tr>

<tr>
  <td style='text-align:right; vertical-align:top;'><?= _("Module:") ?></td>
  <td>
    <? foreach($available_modules as $key => $name) : ?>
    <label>
        <input name="groupmodule[<?= $key ?>]" type="checkbox" <?= ($this->flash['request']['groupmodule'][$key]) ? 'checked="checked"' : '' ?>> <?= $name ?>
    </label><br>
    <? endforeach; ?>

    <? if ($GLOBALS['PLUGINS_ENABLE']) : ?>
        <? foreach($available_plugins as $key => $name) : ?>
        <label>
            <input name="groupplugin[<?= $key ?>]" type="checkbox" <?= ($this->flash['request']['groupplugin'][$key]) ? 'checked="checked"' : '' ?>> <?= $name ?>
        </label><br>
        <? endforeach; ?>
    <? endif; ?>
  </td>
</tr>

<tr>
  <td style='text-align:right;'></td>
</tr>

<tr>
  <td style='text-align:right;'><?= _("Zugang:") ?></td>
  <td>
      <select size=0 name="groupaccess">
         <option value="all"><?= _("Offen f�r alle") ?></option>
         <option value="invite"><?= _("Auf Anfrage") ?></option>
      </select>
  </td>
</tr>

<tr>
  <td style='text-align:right;'></td>
  <td>&nbsp;</td>
</tr>

<tr>
  <td style='text-align:right; vertical-align:top;'><p><?= _("Nutzungsbedingungen:") ?></p></td>
  <td>
      <p>
        <em><?= formatReady( $terms ) ?></em>
    </p>
    <p>
        <label>
            <input type=checkbox name="grouptermsofuse_ok"> <?= _("Einverstanden") ?>
        </label>
    </p>
  </td>
</tr>


<tr>
  <td></td>
  <td><?= makebutton("speichern","input") ?></td>
</tr>

</table>
</form>



