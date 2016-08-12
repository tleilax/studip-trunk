<form name="links_admin_search" action="<?= URLHelper::getLink(Request::path()) ?>" method="post">
    <?= CSRFProtection::tokenTag() ?>

    <legend>
        <?= _('Bitte w�hlen Sie die Einrichtung aus, die Sie bearbeiten wollen:') ?>
    </legend>
    <select name="cid" required class="nested-select">
        <option value="" class="is-placeholder">
            <?= _('-- Bitte Einrichtung ausw�hlen --') ?>
        </option>
    <? foreach ($institutes as $institute): ?>
        <option value="<?= htmlReady($institute['Institut_id']) ?>"
                class="<?= $institute['is_fak'] ? 'nested-item-header' : 'nested-item' ?>">
            <?= htmlReady(my_substr($institute['Name'],0,80)) ?>
        </option>
    <? endforeach; ?>
    </select>
    
    <?= Studip\Button::create(_('Einrichtung ausw�hlen')) ?>
</form>
