<form action="<?= $controller->url_for('admin/plugin') ?>" method="post">
    <?= CSRFProtection::insertToken() ?>
    <select name="plugin_filter" onchange="this.form.submit();">
        <option value="">
            <?= _('alle anzeigen') ?>
        </option>
        <? foreach ($plugin_types as $type): ?>
            <option value="<?= $type ?>" <?= $type == $plugin_filter ? 'selected' : '' ?>>
                <?= strlen($type) > 20 ? substr($type, 0, 17) . '...' : $type ?>
            </option>
        <? endforeach ?>
    </select>

    <noscript>
        <input type="image" class="middle" name="show" src="<?= Assets::image_path('icons/16/blue/accept.png') ?>">
    </noscript>
</form>
