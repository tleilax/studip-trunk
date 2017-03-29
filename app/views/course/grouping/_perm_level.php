<?php if (count($members) > 0) : ?>
    <table class="default">
        <caption>
            <?= htmlReady($level == 'deputy' ? _('Vertretung') : get_title_for_status($level, count($members), $current->status)) ?>
            <span class="actions">
                <?php $actionMenu = ActionMenu::get() ?>
                <?php $actionMenu->addLink(URLHelper::getLink('dispatch.php/messages/write',
                    ['rec_uname' => $members->pluck('username'),
                        'default_subject' => '[' . $current->getFullname() . ']']),
                    _('Nachricht schicken'),
                    Icon::create('mail', 'clickable', ['title' => _('Nachricht schicken')]),
                    ['data-dialog' => 'size=auto']) ?>
                <?= $actionMenu->render() ?>
            </span>
        </caption>
        <colgroup>
            <col width="10">
            <col>
            <col width="20">
        </colgroup>
        <thead>
        <tr>
            <th></th>
            <th><?= _('Name') ?></th>
            <th><?= _('Aktionen') ?></th>
        </tr>
        </thead>
        <tbody>
        <?php $i = 0; foreach ($members as $m) : ?>
            <tr>
                <td><?= sprintf('%02d', ++$i) ?></td>
                <td><?= htmlReady($level == 'deputy' ?
                        $m->getDeputyFullname('full_rev') :
                        $m->getUserFullname('full_rev')) ?></td>
                <td class="actions">
                    <?php $actionMenu = ActionMenu::get() ?>
                    <?php $actionMenu->addLink(URLHelper::getLink('dispatch.php/messages/write',
                        ['rec_uname' => $m->user_id,
                            'default_subject' => '[' . $current->getFullname() . ']']),
                        _('Nachricht schicken'),
                        Icon::create('mail', 'clickable', ['title' => _('Nachricht schicken')]),
                        ['data-dialog' => 'size=auto']) ?>
                    <?php if ($level != 'dozent' || count($members) > 1) : ?>
                        <?php $actionMenu->addLink(
                            URLHelper::getLink('dispatch.php/course/members/cancel_subscription/' . $m->user_id),
                            _('Aus Veranstaltung austragen'),
                            Icon::create('door-leave', 'clickable', ['title' => sprintf(_('%s austragen'),
                                $level == 'deputy' ?
                                    $m->getDeputyFullname('full_rev') :
                                    $m->getUserFullname('full_rev'))])) ?>
                    <?php endif ?>
                    <?= $actionMenu->render() ?>
                </td>
            </tr>
        <?php endforeach ?>
        </tbody>
    </table>
<? endif ?>
