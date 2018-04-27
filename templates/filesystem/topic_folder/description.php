<div>
    <?= _('Dieser Ordner ist ein themenbezogener Dateiordner.')?>
    <?$dates = isset($topic) ? $topic->dates->getFullname() : [];?>
    <? if (count($dates)) :?>
    <?=_('Folgende Termine sind diesem Thema zugeordnet:') ?>
        <div>
            <strong>
                <?=join('; ', $dates)?>
            </strong>
        </div>
    <? endif ?>
</div>
<? if ($folderdata['description']) : ?>
    <div>
        <?= formatReady($folderdata['description']) ?>
    </div>
<? endif ?>
