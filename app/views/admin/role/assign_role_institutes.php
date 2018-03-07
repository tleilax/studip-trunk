<h4><?= sprintf(_('Einrichtungszuordnung für %s in der Rolle %s'), htmlReady($user->getFullname()), htmlready($role->getRoleName()))?></h4>
<form action="<?= $controller->link_for('/assign_role_institutes/' . $role->getRoleid() . '/' . $user->id) ?>" method="post" data-dialog="size=auto;reload-on-close">
    <?= $qsearch->render() ?>
    <?= Studip\Button::create(_('Einrichtung hinzufügen'), 'add_institute') ?>
</form>

<h4><?= _('Vorhandene Zuordnungen') ?></h4>
<ul>
<? foreach ($institutes as $institute): ?>
    <li>
        <?= htmlReady($institute->name) ?>
        <a href="<?= $controller->link_for("/assign_role_institutes/{$role->getRoleid()}/{$user->id}", ['remove_institute' => $institute->id]) ?>" data-dialog="size=auto;reload-on-close">
            <?= Icon::create('trash') ?>
        </a>
    </li>
<? endforeach ?>
</ul>

<?= Studip\LinkButton::createCancel(
    _('Schließen'),
    $controller->url_for("/assign_role/{$user->id}"),
    ['data-dialog-button' => '']
) ?>
