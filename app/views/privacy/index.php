<? if(empty($plugins) || empty(reset($plugins))): ?>
<?= Messagebox::info(_('In dieser Kategorie sind keine Daten vorhanden.')); ?>
<? endif; ?>

<? foreach ($plugins as $plugin_id => $plugin_data) : ?>
    <? foreach ($plugin_data as $label => $tabledata) : ?>
        <? if ($tabledata['table_content']) : ?>
            <h2 onclick="jQuery(this).next('table').toggle();" style="cursor: pointer;">
                <?= htmlReady($label) ?>, <?= sprintf(_('%u Einträge'), count($tabledata['table_content'])) ?>
                <? if (Request::isDialog()) : ?>
                (<a href="<?= $controller->url_for("privacy/export2csv/{$plugin_id}/{$tabledata['table_name']}/{$user_id}") ?>">
                    <?= htmlReady($label) ?> CSV
                </a>)
                <? endif; ?>
            </h2>
            <table class="default" <?= (Request::isDialog() || $section == null)?'style="display: none;"':'' ?>>
                <thead>
                    <tr>
                    <? foreach (array_keys($tabledata['table_content'][0]) as $caption) : ?>
                        <th><?= htmlReady($caption) ?></th>
                    <? endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                <? foreach ($tabledata['table_content'] as $row) : ?>
                    <tr>
                    <? foreach ($row as $key => $value): ?>
                        <td>
                        <? if ($tabledata['table_name'] === 'log_events' && $key === "readable_entry"): ?>
                            <?= $value ?>
                        <? else: ?>
                            <?= htmlReady($value) ?>
                        <? endif; ?>
                        </td>
                    <? endforeach; ?>
                    </tr>
                <? endforeach; ?>
                </tbody>
            </table>
        <? endif; ?>
    <? endforeach; ?>
<? endforeach; ?>

<div data-dialog-button>
<? if (Request::isDialog()): ?>
    <?= Studip\LinkButton::create(_('Zurück'), $controller->url_for("privacy/landing/{$user_id}"), ['data-dialog' => 'size=medium']); ?>
<? else: ?>
    <?= Studip\LinkButton::create(_('Zurück'), $controller->url_for("privacy/landing/{$user_id}")); ?>
<? endif; ?>
</div>
