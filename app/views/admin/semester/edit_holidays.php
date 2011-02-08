<?
# Lifter010: TODO
?>
<form method="post" action="<?= $controller->url_for('admin/semester/edit_holidays') ?><?= ($holiday['holiday_id'])? '/'.$holiday['holiday_id'] : '' ?>">
<?= CSRFProtection::tokenTag() ?>
<table class="default">
    <tr>
    <? if(!$is_new) : ?>
         <th colspan="5"><?= _("Ferien bearbeiten") ?></th>
    <? else : ?>
         <th colspan="5"><?= _("Ferien neu anlegen") ?></th>
    <? endif ?>
    </tr>
    <tr class="<?= TextHelper::cycle('steel1', 'steelgraulight') ?>">
    <tr class="<?= TextHelper::cycle('steel1', 'steelgraulight') ?>">
        <td>
            <?= _("Name der Ferien:") ?>
        </td>
        <td colspan="4">
            <input type="text" size="60" value="<?= ($holiday['name']) ? $holiday['name'] : '' ?>" name="name" style="width: 350px;">
        </td>
    </tr>
    <tr class="<?= TextHelper::cycle('steel1', 'steelgraulight') ?>">
        <td>
            <?= _("Beschreibung:") ?>
        </td>
        <td colspan="4">
            <textarea name="description" rows="4" cols="50" style="width: 350px;"><?= ($holiday['description']) ? $holiday['description'] : '' ?></textarea>
        </td>
    </tr>
    <tr class="<?= TextHelper::cycle('steel1', 'steelgraulight') ?>">
        <td>
            <?= _("Ferienzeitraum:") ?>
        </td>
        <td>
            <?= _("Beginn:") ?>
        </td>
        <td>
            <input id="beginn" type="text" name="beginn" value="<?= ($holiday['beginn']) ? date('d.m.Y', $holiday['beginn']) : '' ?>">
        </td>
        <td>
            <?= _("Ende:") ?>
        </td>
        <td>
            <input id="ende" type="text" name="ende" value="<?= ($holiday['ende']) ? date('d.m.Y', $holiday['ende']) : '' ?>">
        </td>
    </tr>
    <tr>
        <td colspan="5" align="center">
        <? if (!$is_new) : ?>
            <?= makeButton("speichern", 'input', _('Die �nderungen speichern')) ?>
        <? else : ?>
            <?= makeButton("anlegen", 'input', _('Neue Ferien anlegen')) ?>
        <? endif ?>
            <a href="<?= $controller->url_for('admin/semester') ?>"><?= makeButton("abbrechen") ?></a>
        </td>
    </tr>
</table>
</form>

<script>
    jQuery('#beginn').datepicker();
    jQuery('#ende').datepicker();
</script>