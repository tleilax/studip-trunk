<? if (isset($flash['message'])): ?>
    <?= MessageBox::success($flash['message']) ?>
<? endif ?>

<h3>
    <?= _('Default-Aktivierung') ?>: <?= htmlspecialchars($plugin_name) ?>
</h3>

<p>
    <?= _('W�hlen Sie die Einrichtungen, in deren Veranstaltungen das Plugin automatisch aktiviert sein soll:') ?>
</p>

<form action="<?= $controller->url_for('plugin_admin/save_default_activation', $plugin_id) ?>" method="post">
    <input type="hidden" name="ticket" value="<?= get_ticket() ?>">
    <select name="selected_inst[]" multiple size="20">
        <? foreach ($institutes as $id => $institute): ?>
            <option style="font-weight: bold;" value="<?= $id ?>" <?= in_array($id, $selected_inst) ? 'selected' : '' ?>>
                <?= htmlReady($institute['name']) ?>
            </option>

            <? if (isset($institute['children'])): ?>
                <? foreach ($institute['children'] as $id => $child): ?>
                    <option style="padding-left: 1em;" value="<?= $id ?>" <?= in_array($id, $selected_inst) ? 'selected' : '' ?>>
                        <?= htmlReady($child['name']) ?>
                    </option>
                <? endforeach ?>
            <? endif ?>
        <? endforeach ?>
    </select>
    <p>
        <?= makeButton('uebernehmen', 'input', _('Einstellungen speichern'), 'save') ?>
        &nbsp;
        <a href="<?= $controller->url_for('plugin_admin') ?>">
            <?= makeButton('zurueck', 'img',  _('Zur�ck zur Plugin-Verwaltung')) ?>
        </a>
    </p>
</form>

<?
$infobox_content = array(
    array(
        'kategorie' => _('Aktionen:'),
        'eintrag'   => array(
            array(
                'icon' => 'link_intern.gif',
                'text' => '<a href="'.$controller->url_for('plugin_admin').'">'._('Verwaltung von Plugins').'</a>'
            )
        )
    ), array(
        'kategorie' => _('Hinweise:'),
        'eintrag'   => array(
            array(
                "icon" => "icons/16/black/info.png",
                'text' => _('Eine Mehrfachauswahl in der Liste der Einrichtungen ist durch Dr�cken der Strg-Taste m�glich.')
            )
        )
    )
);

$infobox = array('picture' => 'infobox/modules.jpg', 'content' => $infobox_content);
?>
