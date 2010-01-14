<h3>
    <?= _('Plugin-Details') ?>
</h3>

<table>
    <tr>
        <td>Name:</td>
        <td><?= htmlspecialchars($plugin['name']) ?></td>
    </tr>
    <tr>
        <td>Klasse:</td>
        <td><?= $plugin['class'] ?></td>
    </tr>
    <tr>
        <td>Typ:</td>
        <td><?= join(', ', $plugin['type']) ?></td>
    </tr>
    <tr>
        <td>Origin:</td>
        <td><?= htmlspecialchars($plugininfos['origin']) ?></td>
    </tr>
    <tr>
        <td>Version:</td>
        <td><?= htmlspecialchars($plugininfos['version']) ?></td>
    </tr>
    <tr>
        <td>Beschreibung:</td>
        <td><?= htmlspecialchars($plugininfos['description']) ?></td>
    </tr>
</table>

<p>
    <a href="<?= $controller->url_for('plugin_admin') ?>">
        <?= makeButton('zurueck', 'img', _('zur�ck zur Plugin-Verwaltung')) ?>
    </a>
</p>

<?
$infobox_content = array(
    array(
        'kategorie' => _('Hinweise:'),
        'eintrag'   => array(
            array(
                'icon' => 'ausruf_small.gif',
                'text' => _('Hier finden Sie weitere Informationen zum ausgew�hlten Plugin.')
            )
        )
    )
);

$infobox = array('picture' => 'modules.jpg', 'content' => $infobox_content);
?>
