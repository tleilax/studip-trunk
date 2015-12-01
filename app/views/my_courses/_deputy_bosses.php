<?
# Lifter010: TODO
?>
<? SkipLinks::addIndex(_("Personen, deren Standardvertretung ich bin"), 'my_deputy_bosses') ?>
<table class="default" id="my_deputy_bosses">
    <caption>
        <?= _("Personen, deren Standardvertretung ich bin") ?>
    </caption>
    <colgroup>
        <col width="30px">
        <col>
    </colgroup>
    <thead>
    <tr>
        <th></th>
        <th><?= _("Name") ?></th>
        <th><?= _('Aktion') ?></th>
    </tr>
    </thead>
    <tbody>
    <? foreach ($my_bosses as $boss) : ?>
        <tr>
            <td>
                <?= Avatar::getAvatar($boss['user_id'])->getImageTag(Avatar::SMALL, array('title' => htmlReady($boss['fullname']))) ?>
            </td>
            <td>
                <?= htmlReady($boss['fullname'])?>
            </td>
            <td>
                <? if ($boss['edit_about'] && $deputies_edit_about_enabled) : ?>
                    <a href="<?= URLHelper::getLink('dispatch.php/profile', array('username' => $boss['username'])) ?>">
                        <?= Icon::create('person', 'clickable', ['title' => _('Personenangaben bearbeiten')])->asImg(20) ?>
                    </a>
                <? endif ?>
                <a href="<?= URLHelper::getLink('dispatch.php/messages/write',
                    array('filter' => 'send_sms_to_all',
                          'rec_uname' => $boss['username']))?>">
                    <?= Icon::create('mail', 'clickable', ['title' => sprintf(_('Nachricht an %s senden'), htmlReady($boss['fullname']))])->asImg(20) ?>
                </a>
            </td>
        </tr>
    <? endforeach ?>
    </tbody>
</table>
<br/>