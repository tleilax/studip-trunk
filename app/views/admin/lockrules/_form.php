<form action="<?=$action?>" method="post">
<?=CSRFProtection::tokenTag();?>
<table class="default">
<tr class="<?= TextHelper::cycle('steel1', 'steelgraulight') ?>">
<td width="30%">
<?= _("Name")?>
</td>
<td>
<input type="text" style="width:90%" name="lockdata_name" value="<?=htmlReady($lock_rule['name'])?>">
</td>
</tr>
<tr class="<?= TextHelper::cycle('steel1', 'steelgraulight') ?>">
<td width="30%">
<?= _("Beschreibung")?>
<div style="font-size:80%"><?=_("(dieser Text wird auf allen Seiten mit gesperrtem Inhalt angezeigt)")?></div>
</td>
<td>
<textarea name="lockdata_description" rows="5" style="width:90%"><?=htmlReady($lock_rule["description"])?></textarea>
</tr>
<tr class="<?= TextHelper::cycle('steel1', 'steelgraulight') ?>">
<td width="30%">
<?= _("Nutzerstatus")?>
<div style="font-size:80%"><?=_("(die Einstellungen dieser Sperrebene gelten f�r Nutzer bis zu dieser Berechtigung)")?></div>
</td>
<td>
<select name="lockdata_permission">
<?foreach(($GLOBALS['perm']->have_perm('root') ? array('autor','tutor','dozent','admin','root') : array('autor','tutor','dozent')) as $p) :?>
	<option <?=($lock_rule['permission'] == $p ? 'selected' : '')?>><?=$p?></option>
<? endforeach;?>
</select>
</td>
</tr>
</table>
<h3><?=_("Attribute")?></h3>
<table class="default">
<?
foreach($lock_config['groups'] as $group => $group_title) {
    $attributes = array_filter(array_map(create_function('$a', 'return $a["group"]=="' . $group . '" ? $a["name"] : null;'), $lock_config['attributes']));
    if (count($attributes)) {
        ?>
        <tr class="<?= TextHelper::cycle('steel1', 'steelgraulight') ?>">
        <td width="70%">
        <span style="font-weight:bold"><?=htmlready($group_title)?></span>
        </td>
        <td width="15%" style="text-align:center">
        <span style="font-weight:bold"><?=_("gesperrt")?></span>
        </td>
        <td width="15%" style="text-align:center">
        <span style="font-weight:bold"><?=_("nicht gesperrt")?></span>
        </td>
        </tr>
        <?
        foreach ($attributes as $attr => $attr_name) {
             ?>
            <tr class="<?= TextHelper::cycle('steel1', 'steelgraulight') ?>">
            <td width="70%">
            <?=htmlready($attr_name)?>
            </td>
            <td width="15%" style="text-align:center">
            <input type="radio" name="lockdata_attributes[<?=$attr?>]" <?=($lock_rule['attributes'][$attr] ? 'checked' : '')?> value="1">
            </td>
            <td width="15%" style="text-align:center">
            <input type="radio" name="lockdata_attributes[<?=$attr?>]" <?=(!$lock_rule['attributes'][$attr] ? 'checked' : '')?> value="0">
            </td>
            </tr>
            <?
        }
        ?>
        <tr class="<?= TextHelper::cycle('steel1', 'steelgraulight') ?>">
        <td colspan="3" style="text-align:center">
        <?=makeButton('uebernehmen', 'input', _("Einstellungen �bernehmen"), 'ok')?>
        </td>
        </tr>
        <?

    }
}
?>
</table>
</form>