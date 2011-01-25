<?= sprintf(ngettext('Es ist ein Update f�r ein Plugin verf�gbar', 'Es sind Updates f�r %d Plugins verf�gbar', $num_updates), $num_updates) ?>

<form action="<?= $controller->url_for('admin/plugin/install_updates') ?>" method="post">
    <?= CSRFProtection::tokenTag() ?>
    <input type="hidden" name="ticket" value="<?= get_ticket() ?>">
    <div style="margin: 1ex;">
        <? foreach ($plugins as $plugin): ?>
            <? $pluginid = $plugin['id'] ?>
            <? if (isset($update_info[$pluginid]['update']) && !$plugin['depends']): ?>
                <div>
                    <label>
                        <input type="checkbox" name="update[]" value="<?= $pluginid ?>" checked>
                        <?= htmlspecialchars(sprintf(_('%s: Version %s installieren'), $plugin['name'], $update_info[$pluginid]['update']['version'])) ?>
                    </label>
                </div>
            <? endif ?>
        <? endforeach ?>
    </div>

    <?= makeButton('starten', 'input', _('Updates installieren'), 'update') ?>
</form>
