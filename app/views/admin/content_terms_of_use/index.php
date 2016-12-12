<h1><?= _('Inhalts-Nutzungsbedingungen') ?></h1>
<? if($content_terms_of_use_entries): ?>
<table class="default">
    <thead>
        <tr>
            <th><?= _('ID') ?></th>
            <th><?= _('Name') ?></th>
            <th><?= _('Download-Bedingung') ?></th>
            <th><?= _('Symbol') ?></th>
            <th><?= _('Position') ?></th>
            <th><?= _('Beschreibung') ?></th>
            <th><?= _('Aktionen') ?></th>
        </tr>
    </thead>
    <tbody>
        <? foreach ($content_terms_of_use_entries as $entry): ?>
        <tr>
            <td><?= htmlReady($entry->id) ?></td>
            <td><?= htmlReady($entry->name) ?></td>
            <td><?= (($entry->download_condition == '1')
                ? _('Nur für geschlossene Gruppen')
                : (($entry->download_condition == '2')
                    ? _('Nur für Eingentümer')
                    : _('Ohne Bedingung')
                    )
                ) ?></td>
            <td><?= Icon::create($entry->icon)->asImg('20px') ?></td>
            <td><?= htmlReady($entry->position) ?></td>
            <td><?= htmlReady($entry->description) ?></td>
            <td>
                <a href="<?= 
                    URLHelper::getLink(
                        'dispatch.php/admin/content_terms_of_use/edit',
                        [
                            'entry_id' => $entry->id
                        ]
                    ) ?>" data-dialog="1">
                    <?= Icon::create('edit', 'clickable')->asImg('20px') ?>
                </a>
                <a href="<?= 
                    URLHelper::getLink(
                        'dispatch.php/admin/content_terms_of_use/delete',
                        [
                            'entry_id' => $entry->id
                        ]
                    ) ?>" data-dialog="1">
                    <?= Icon::create('trash', 'clickable')->asImg('20px') ?>
                </a>
            </td>
        </tr>
        <? endforeach ?>
    </tbody>
</table>
<? else: ?>
<?= MessageBox::info(_('Es sind keine Nutzungsbedingungen für Inhalte definiert!')) ?>
<?= \Studip\LinkButton::create(
    _('Neue Nutzungsbedingungen definieren'),
    URLHelper::getUrl('dispatch.php/admin/content_terms_of_use/add'),
    [
        'data-dialog' => 'reload-on-close'
    ]
) ?>

<? endif ?>
