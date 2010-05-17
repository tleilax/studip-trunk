<form action="<?= $controller->url_for('plugin_admin/search') ?>" method="post" style="float: right;">
    <?= _('Suche nach Plugins:') ?>
    <input name="search" type="text" size="20" value="<?= htmlReady($search) ?>">
    <?= makeButton('suchen', 'input' , _('Suche starten')) ?>
    &nbsp;
    <a href="<?= $controller->url_for('plugin_admin/search') ?>">
        <?= makeButton('zuruecksetzen', 'img', _('Suche zur�cksetzen')) ?>
    </a>
</form>

<h3>
    <? if ($search === NULL): ?>
        <?= _('Empfohlene Plugins') ?>
    <? else: ?>
        <?= _('Suchergebnisse') ?>
    <? endif ?>
</h3>

<? if (empty($search_results)): ?>
    <?= MessageBox::info(_('Es wurden keine Plugins gefunden.')) ?>
<? else: ?>
    <table class="default">
        <tr>
            <th class="plugin_image"><?= _('Bild')?></th>
            <th><?= _('Name und Beschreibung')?></th>
            <th><?= _('Version') ?></th>
            <th><?= _('Bewertung') ?></th>
            <th class="plugin_install"><?= _('Installieren') ?></th>
        </tr>

        <? foreach ($search_results as $name => $plugin): ?>
            <tr class="<?= TextHelper::cycle('cycle_odd', 'cycle_even') ?>">
                <td class="plugin_image">
                    <? if ($plugin['image']): ?>
                        <? if ($plugin['plugin_url']): ?>
                            <a href="<?= htmlspecialchars($plugin['plugin_url']) ?>" target="_blank">
                                <img src="<?= htmlspecialchars($plugin['image']) ?>" class="plugin_preview">
                            </a>
                        <? else: ?>
                            <img src="<?= htmlspecialchars($plugin['image']) ?>" class="plugin_preview">
                        <? endif ?>
                    <? endif ?>
                </td>
                <td>
                    <? if ($plugin['plugin_url']): ?>
                        <a href="<?= htmlspecialchars($plugin['plugin_url']) ?>" target="_blank">
                            <b><?= htmlspecialchars($name) ?></b>
                        </a>
                    <? else: ?>
                        <b><?= htmlspecialchars($name) ?></b>
                    <? endif ?>
                    <p>
                        <?= htmlspecialchars($plugin['description']) ?>
                    </p>
                </td>
                <td>
                    <?= htmlspecialchars($plugin['version']) ?>
                </td>
                <td class="plugin_score">
                    <? for ($i = 0; $i < $plugin['score']; ++$i): ?>
                        <?= Assets::img('star.png') ?>
                    <? endfor ?>
                </td>
                <td class="plugin_install">
                    <form action="<?= $controller->url_for('plugin_admin/install', $name) ?>" method="post">
                        <input type="hidden" name="ticket" value="<?= get_ticket() ?>">
                        <input type="image" name="install" src="<?= Assets::image_path('install.png') ?>" title="<?= _('Plugin installieren') ?>">
                    </form>
                </td>
            </tr>
        <? endforeach ?>
    </table>
<? endif ?>

<? if (get_config('PLUGINS_UPLOAD_ENABLE')): ?>
    <h3>
        <?= _('Plugin als ZIP-Datei hochladen') ?>
    </h3>

    <form action="<?= $controller->url_for('plugin_admin/install') ?>" enctype="multipart/form-data" method="post">
        <?= _('Plugin-Datei:') ?>
        <input name="upload_file" type="file" size="40">
        <input type="hidden" name="ticket" value="<?= get_ticket() ?>">

        <?= makeButton('hinzufuegen', 'input' , _('neues Plugin installieren')) ?>
    </form>
<? endif ?>

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
        'kategorie' => _('Links:'),
        'eintrag'   => array(
            array(
                'icon' => 'link_extern.gif',
                'text' => '<a href="http://plugins.studip.de/" target="_blank">'._('Alle Plugins im Plugin-Marktplatz').'</a>'
            )
        )
    ), array(
        'kategorie' => _('Hinweise:'),
        'eintrag'   => array(
            array(
                'icon' => 'ausruf_small.gif',
                'text' => _('In der Liste "Empfohlene Plugins" finden Sie von anderen Betreibern empfohlene Plugins.')
            ), array(
                'icon' => 'ausruf_small.gif',
                'text' => _('Alternativ k�nnen Plugins und Plugin-Updates auch als ZIP-Datei hochgeladen werden.')
            )
        )
    )
);

$infobox = array('picture' => 'infoboxes/modules.jpg', 'content' => $infobox_content);
?>
