<strong><?= _('Fachbereiche') ?></strong><br>
<form id="fachbereich_filter" action="<?= $action ?>" method="post">
    <select name="fachbereich_filter" size="1" style="width: 180px;" onChange="document.getElementById('fachbereich_filter').submit()">
        <option value=""><?= _('-- Fachbereich w�hlen --') ?></option>
        <? foreach ($fachbereiche as $fachbereich) : ?>
        <option value="<?= $fachbereich['fachbereich_id'] ?>"<?= ($fachbereich->getId() == $selected_fachbereich ? ' selected' : '') ?>><?= htmlReady($fachbereich->getDisplayName()) ?></option>
        <? endforeach; ?>
    </select>
</form>
