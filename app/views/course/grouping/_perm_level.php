<?php if (count($members) > 0) : ?>
    <form action="<?= $controller->url_for('course/grouping/action') ?>" method="post" data-dialog="size=auto">
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
                <?php if (count($members) > 1) : ?>
                    <col width="60">
                <?php endif ?>
                <col width="10">
                <col>
                <col width="20">
            </colgroup>
            <thead>
            <tr>
                <?php if (count($members) > 1) : ?>
                    <th>
                        <label>
                            <input type="checkbox" data-proxyfor=":checkbox.members-<?= $current->id ?>_<?= $level ?>">
                            <?= _('Alle') ?>
                        </label>
                    </th>
                <?php endif ?>
                <th></th>
                <th><?= _('Name') ?></th>
                <th><?= _('Aktionen') ?></th>
            </tr>
            </thead>
            <tbody>
            <?php $i = 0; foreach ($members as $m) : ?>
                <tr>
                    <?php if (count($members) > 1) : ?>
                        <td>
                            <input type="checkbox" name="members[]" value="<?= $m->user_id ?>" class="members-<?= $current->id ?>_<?= $level ?>">
                        </td>
                    <?php endif ?>
                    <td>
                        <?= sprintf('%02d', ++$i) ?>
                    </td>
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
                            <?php $actionMenu->addLink($controller->url_for('course/grouping/move_members_target',
                                $current->id, $m->user_id),
                                _('In andere Unterveranstaltung verschieben'),
                                Icon::create('arr_2right', 'clickable', ['title' => _('In andere Unterveranstaltung verschieben')]),
                                ['data-dialog' => 'size=auto']) ?>
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
            <?php if (count($members) > 1) : ?>
                <tfoot>
                    <tr>
                        <td colspan="2">
                            <label>
                                <input type="checkbox" data-proxyfor=":checkbox.members-<?= $current->id ?>_<?= $level ?>">
                                <?= _('Alle') ?>
                            </label>
                        </td>
                        <td colspan="2" class="actions">
                            <select name="action">
                                <option value="message">
                                    <?= _('Nachricht schicken') ?>
                                </option>
                                <option value="move">
                                    <?= _('In andere Unterveranstaltung verschieben') ?>
                                </option>
                                <option value="remove">
                                    <?= _('Austragen') ?>
                                </option>
                            </select>
                            <input type="hidden" name="course" value="<?= $current->id ?>">
                            <?= Studip\Button::createAccept(_('Ausführen'), 'do') ?>
                        </td>
                    </tr>
                </tfoot>
            <?php endif ?>
        </table>
    </form>
<? endif ?>
