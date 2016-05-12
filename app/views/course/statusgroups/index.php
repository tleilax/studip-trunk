<form action="<?= $controller->url_for('course/statusgroups/batch_action') ?>" method="post">
<section class="contentbox statusgroups">
    <header>
        <h1>
            <?= _('Teilnehmende nach Gruppen') ?>
        </h1>
    </header>
    <?php foreach ($groups as $group) : ?>
        <article class="<?= ContentBoxHelper::classes($group['id']) ?>" id="<?= $group['id'] ?>">
            <header>
                <h1>
                    <?php if ($group['id'] != 'nogroup') : ?>
                    <input aria-label="<?= _('Gruppe auswählen') ?>"
                           type="checkbox" name="groups[]"
                           class="groups" value="<?= $group['id'] ?>"
                           id="<?= $group['id'] ?>" style="float:left"/>
                    <?php endif ?>
                    <a href="<?= ContentBoxHelper::href($group['id'], array('contentbox_type' => 'news')) ?>">
                        <?= htmlReady($group['name']) ?> (<?= count($group['members']) ?>)
                    </a>
                </h1>
                <nav>
                    <?php if ($is_tutor) : ?>
                        <?php if ($group['id'] != 'nogroup') : ?>
                            <a href="<?= $controller->url_for('messages/write', array(
                                    'group_id' => $group['id'],
                                    'default_subject' => $course_title
                                )) ?>" data-dialog="size:auto;">
                                <?= Icon::create('mail', 'clickable',
                                    array('title' => sprintf(_('Nachricht an alle Mitglieder der Gruppe %s schicken'),
                                        htmlReady($group['name'])))) ?></a>
                            <a href="<?= $controller->url_for('course/statusgroups/edit', $group['id']) ?>" data-dialog>
                                <?= Icon::create('edit', 'clickable',
                                    array('title' => sprintf(_('Gruppe %s bearbeiten'),
                                        htmlReady($group['name'])))) ?></a>
                            <a href="<?= $controller->url_for('course/statusgroups/delete', $group['id']) ?>"
                               data-confirm="<?= sprintf(_('Soll die Gruppe %s wirklich gelöscht werden?'),
                                   htmlReady($group['name'])) ?>">
                                <?= Icon::create('trash', 'clickable',
                                    array('title' => sprintf(_('Gruppe %s löschen'),
                                        htmlReady($group['name'])))) ?></a>
                        <?php else : ?>
                            <a href="<?= $controller->url_for('messages/write', array(
                                'rec_uname' => $group['members']->pluck('username'),
                                'default_subject' => $course_title
                            )) ?>" data-dialog="size:auto;">
                                <?= Icon::create('mail', 'clickable',
                                    array('title' => _('Nachricht an alle nicht zugeordneten Personen schicken'))) ?></a>
                        <?php endif ?>
                    <?php endif ?>
                </nav>
            </header>
            <section>
                <?php if (count($group['members'])) : ?>
                <table class="default">
                    <colgroup>
                        <col width="20">
                        <?php if($is_tutor) : ?>
                            <?php if (!$is_locked) : ?>
                                <col width="20">
                            <?php endif ?>
                            <col>
                            <col width="15%">
                            <?php $cols = 6 ?>
                            <col width="35%">
                            <?php $cols_foot = 6 ?>
                        <?php else : ?>
                            <col>
                            <?php $cols = 3 ?>
                        <?php endif ?>

                        <col width="80">
                    </colgroup>
                    <thead>
                        <tr class="sortable">
                            <?php if ($is_tutor && !$is_locked) : ?>
                                <th>
                                    <input aria-label="<?= sprintf(_('Alle Mitglieder dieser Gruppe auswählen')) ?>"
                                           type="checkbox" name="all" value="1"
                                           data-proxyfor=":checkbox.groupmembers-<?= $group['id'] ?>">
                                </th>
                            <?php endif ?>
                            <th></th>
                            <th <?= ($sort_by == 'nachname' && $sort_group == $group['id']) ?
                                sprintf('class="sort%s"', $order) : '' ?>>
                                <a href="<?= URLHelper::getLink('#' . $group['id'],
                                        array(
                                            'sortby' => 'nachname',
                                            'sort_group' => $group['id'],
                                            'order' => $group['id'] && $sort_by == 'nachname' ?
                                                ($order == 'desc' ? 'asc' : 'desc') : 'desc',
                                            'contentbox_open' => $group['id']
                                        )) ?>">
                                    <?=_('Nachname, Vorname')?>
                                </a>
                            </th>
                            <?php if ($is_tutor) :?>
                                <th <?= ($sort_by == 'mkdate' && $sort_group == $group['id']) ? sprintf('class="sort%s"', $order) : '' ?>>
                                    <a href="<?= URLHelper::getLink('#' . $group['id'],
                                        array(
                                            'sortby' => 'mkdate',
                                            'sort_group' => $group['id'],
                                            'order' => $group['id'] && $sort_by == 'mkdate' ?
                                                ($order == 'desc' ? 'asc' : 'desc') : 'desc',
                                            'contentbox_open' => $group['id']
                                        )) ?>">
                                        <?= _('Anmeldedatum') ?>
                                    </a>
                                </th>
                                <th>
                                    <?= _('Studiengang') ?>
                                </th>
                            <?php endif ?>
                            <th style="text-align: right"><?= _('Aktion') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1; foreach ($group['members'] as $m) : ?>
                        <tr>
                            <?php if ($is_tutor && !$is_locked) : ?>
                                <td>
                                    <input aria-label="<?= _('Mitglieder auswählen') ?>"
                                           type="checkbox" name="group[<?= $group['id'] ?>][<?= $m->user_id ?>]"
                                           class="groupmembers-<?= $group['id'] ?>" value="1" />
                                </td>
                            <?php endif ?>
                            <td>
                                <?= str_pad($i, 2, '0', STR_PAD_LEFT) ?>
                            </td>
                            <td>
                                <a href="<?= $controller->url_for(sprintf('profile?username=%s',$m->username)) ?>">
                                    <?= Avatar::getAvatar($m->user_id, $m->username)->getImageTag(Avatar::SMALL,
                                        array('style' => 'margin-right: 5px', 'title' => htmlReady($m->getUserFullname('full_rev')))); ?>
                                    <?= htmlReady($m->getUserFullname('full_rev')) ?>
                                    <?php if ($user_id == $m->id && $m->visible == 'no') : ?>
                                        (<?= _('unsichtbar') ?>)
                                    <?php endif ?>
                                </a>
                            </td>
                            <td>
                                <?= strftime('%x %X', $m->mkdate) ?>
                            </td>
                            <td>
                                <?= $this->render_partial('course/members/_studycourse.php',
                                    array('study_courses' => UserModel::getUserStudycourse($m->user_id))) ?>
                            </td>
                            <td></td>
                        </tr>
                        <?php $i++; endforeach ?>
                    </tbody>
                </table>
                <?php else : ?>
                    <?= MessageBox::info(_('Diese Gruppe hat keine Mitglieder.')) ?>
                <?php endif ?>
            </section>
        </article>
    <?php endforeach ?>
    <?php if (count($groups) > 1) : ?>
        <footer style="text-align: left">
            <label>
                <input aria-label="<?= sprintf(_('Alle Gruppen auswählen')) ?>"
                       type="checkbox" name="allgroups" value="1"
                       data-proxyfor=":checkbox.groups">
                <?= _('alle an/abwählen') ?>
            </label>
            <br/>
            <label>
                <?= _('Ausgewählte Gruppen') ?>:
                <select name="groups_action">
                    <option value="">-- <?= _('bitte auswählen') ?> --</option>
                    <option value="edit"><?= _('Bearbeiten') ?></option>
                    <option value="delete"><?= _('Löschen') ?></option>
                </select>
            </label>
            <?= Studip\Button::create(_('Ausführen'), 'batch_groups', array('data-dialog' => 'size=auto')) ?>
        </footer>
    <?php endif ?>
</section>
