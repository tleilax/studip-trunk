<tr>
    <td>
        <a href="<?= $controller->url_for('profile?username=' . $user['username']) ?>">
            <?= Avatar::getAvatar($user['user_id'], $user['username'])->getImageTag(Avatar::SMALL, array('title' => htmlReady($user['name']))) ?>
        </a>
    </td>
    <td>
        <a href="<?= $controller->url_for('profile?username=' . $user['username']) ?>">
            <?= htmlReady($user['name']) ?>
        </a>
    <? foreach (StudipKing::is_king($user['user_id'], true) as $text) : ?>
        <?= Icon::create('crown', 'sort', ['title' => $text])->asImg(16) ?>
    <? endforeach ?>
    </td>
    <td style="white-space: nowrap;">
        <?= ucfirst(reltime(time() - $user['last_action'])) ?>
    </td>
    <td class="actions" nowrap="nowrap">
    <? if (class_exists("Blubber")) : ?>
        <a href="<?= URLHelper::getLink('plugins.php/blubber/streams/global', array('mention' => $user['username'])) ?>">
            <?= Icon::create('blubber', 'clickable', ['title' => _('Blubber diesen Nutzer an')])->asImg(16) ?>
        </a>
    <? endif ?>

        <a href="<?= URLHelper::getLink('dispatch.php/messages/write', array('rec_uname' => $user['username'])) ?>" data-dialog="button">
            <?= Icon::create('mail', 'clickable', ['title' => _('Nachricht an Benutzer verschicken')])->asImg(16) ?>
        </a>
    <? if ($user['is_buddy']): ?>
        <a href="<?= $controller->url_for('online/buddy/remove?username=' . $user['username']) ?>">
            <?= Assets::img('icons/16/blue/remove/person.png', tooltip2(_('Aus den Kontakten entfernen'))) ?>
        </a>
    <? else: ?>
        <a href="<?= $controller->url_for('online/buddy/add?username=' . $user['username']) ?>">
            <?= Assets::img('icons/16/blue/add/person.png', tooltip2(_('Zu den Kontakten hinzufügen'))) ?>
        </a>
    <? endif; ?>
    </td>
</tr>
