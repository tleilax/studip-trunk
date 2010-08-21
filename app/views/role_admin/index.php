<?= $this->render_partial('role_admin/status_message') ?>

<? if ($delete_role): ?>
    <?= $GLOBALS['template_factory']->render('shared/question',
        array('question' => sprintf(_('Wollen Sie wirklich die Rolle "%s" l�schen?'), $roles[$delete_role]->getRolename()),
              'approvalLink' => $controller->url_for('role_admin/remove_role', $delete_role).'?ticket='.get_ticket(),
              'disapprovalLink' => $controller->url_for('role_admin'))) ?>
<? endif ?>

<h3>
    <?= _('Vorhandene Rollen') ?>
</h3>

<table class="default">
    <tr>
        <th><?= _('Name') ?></th>
        <th style="text-align: right;"><?= _('Benutzer') ?></th>
        <th style="text-align: right;"><?= _('Plugins') ?></th>
        <th></th>
    </tr>
    <? foreach ($roles as $role): ?>
        <? $role_id = $role->getRoleid() ?>
        <tr class="<?= TextHelper::cycle('steel1', 'steelgraulight') ?>">
            <td>
                <a href="<?= $controller->url_for('role_admin/show_role', $role_id) ?>">
                    <?= htmlReady($role->getRolename()) ?>
                    <? if ($role->getSystemtype()): ?>[<?= _('Systemrolle') ?>]<? endif ?>
                </a>
            </td>
            <td style="text-align: right;">
                <?= $stats[$role_id]['users'] ?>
            </td>
            <td style="text-align: right;">
                <?= $stats[$role_id]['plugins'] ?>
            </td>
            <td style="text-align: right;">
                <? if (!$role->getSystemtype()): ?>
                    <a href="<?= $controller->url_for('role_admin/ask_remove_role', $role_id) ?>">
                        <?= Assets::img('icons/16/blue/trash.png', array('title' => _('Rolle l�schen'))) ?>
                    </a>
                <? endif ?>
            </td>
        </tr>
    <? endforeach ?>
</table>

<h3>
    <?= _('Neue Rolle anlegen') ?>
</h3>

<form action="<?= $controller->url_for('role_admin/create_role') ?>" method="POST">
    <input type="hidden" name="ticket" value="<?= get_ticket() ?>">
    Name: <input type="text" name="name" size="25" value="">
    <?= makeButton('anlegen', 'input', _('Rolle anlegen'), 'createrolebtn') ?>
</form>

<?
$infobox_content = array(
    array(
        'kategorie' => _('Aktionen:'),
        'eintrag'   => array(
            array(
                'icon' => 'icons/16/black/admin.png',
                'text' => '<a href="'.$controller->url_for('role_admin').'">'._('Rollen verwalten').'</a>'
            ), array(
                'icon' => 'icons/16/black/person.png',
                'text' => '<a href="'.$controller->url_for('role_admin/assign_role').'">'._('Benutzerzuweisungen bearbeiten').'</a>'
            ), array(
                'icon' => 'icons/16/black/plugin.png',
                'text' => '<a href="'.$controller->url_for('role_admin/assign_plugin_role').'">'._('Pluginzuweisungen bearbeiten').'</a>'
            ), array(
                'icon' => 'icons/16/black/log.png',
                'text' => '<a href="'.$controller->url_for('role_admin/show_role').'">'._('Rollenzuweisungen anzeigen').'</a>'
            )
        )
    ), array(
        'kategorie' => _('Hinweise:'),
        'eintrag'   => array(
            array(
                "icon" => "icons/16/black/info.png",
                'text' => _('Zum Erstellen neuer Rollen geben Sie den Namen ein und klicken Sie auf "anlegen".')
            ), array(
                "icon" => "icons/16/black/info.png",
                'text' => _('Zum L�schen von Rollen klicken Sie auf das M�lleimersymbol. Systemrollen k�nnen jedoch nicht gel�scht werden.')
            )
        )
    )
);

$infobox = array('picture' => 'infobox/modules.jpg', 'content' => $infobox_content);
?>
