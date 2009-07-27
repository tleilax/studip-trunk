<?
$infobox=array();
$infobox['picture']='infoboxbild_studygroup.jpg';
$infobox['content']=array(
        array(
        'kategorie'=>_("Information"),
        'eintrag'=>array(
            array("text"=>"Studiengruppen sind eine einfache M�glichkeit, mit Kommilitonen, Kollegen und anderen zusammenzuarbeiten. Jeder kann Studiengruppen gr�nden.","icon"=>"ausruf_small2.gif"))),
        array(
        'kategorie'=>_("Aktionen"),
        'eintrag'=>array(
            array("text"=>'<a href="'.$controller->url_for('course/studygroup/new').'">'._('Neue Arbeitsgruppe anlegen').'</a>', 
                  "icon"=>"icon-cont.gif"),
	        array("text"=>'<a href="'.$controller->url_for('course/studygroup/delete/'.$sem_id).'">'._('Diese Arbeitsgruppe l�schen').'</a>',
	              "icon"=>"trash.gif"))),
     );

?>

<?= $this->render_partial("course/studygroup/_feedback") ?>
<h1><?= _("Arbeitsgruppe bearbeiten") ?></h1>

<form action="<?= $controller->url_for('course/studygroup/update/'.$sem_id) ?>" method=post>


<table class="blank" width="75%" cellspacing="5" cellpadding="0" border="0" style="margin-left:75px; margin-right:300px;">

<tr>
  <td style='text-align:right; font-size:150%;'>Name:</td>
  <td style='font-size:150%;'><input type='text' name='groupname' size='25' value='<?=$sem->getName()?>' style='font-size:100%'></td>
</tr>

<tr>
  <td style='text-align:right; vertical-align:top;'>Beschreibung:</td>
  <td><textarea name='groupdescription' rows=5 cols=50><?=$sem->description?></textarea></td>
</tr>
<tr>
  <td style='text-align:right; vertical-align:top;'>Module:</td>
  <td>
  	<? foreach($available_modules as $key => $name) : ?>
	<label>
	    <input name="groupmodule[<?= $key ?>]" type="checkbox" <?= ($modules->getStatus($key, $sem_id, 'sem')) ? 'checked="checked"' : '' ?>> <?= $name ?>
	</label><br>
	<? endforeach; ?>

	<? if ($GLOBALS['PLUGINS_ENABLE']) : ?>
	  	<? foreach($available_plugins as $key => $name) : ?>
		<label>
		    <input name="groupplugin[<?= $key ?>]" type="checkbox" <?= ($enabled_plugins[$key]) ? 'checked="checked"' : '' ?>> <?= $name ?>
		</label><br>
		<? endforeach; ?>
	<? endif; ?>
  </td>
</tr>

<tr>
  <td style='text-align:right;'></td>
</tr>

<tr>
  <td style='text-align:right;'>Zugang:</td>
  <td>
      <select size=0 name="groupaccess">
          <option <?= ($sem->admission_prelim == 0) ? 'selected="selected"':'' ?> value="all">Offen f�r alle
         <option <?= ($sem->admission_prelim == 1) ? 'selected="selected"':'' ?> value="invite">Auf Anfrage
      </select>
  </td>
</tr>

<tr>
  <td style='text-align:right;'></td>
  <td>&nbsp;</td>
</tr>

<tr>
  <td></td>
  <td><input type='submit' value="�nderungen �bernehmen"></td>
</tr>

</table>
</form>
