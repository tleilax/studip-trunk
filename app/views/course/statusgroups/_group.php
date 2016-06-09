<article class="<?= ContentBoxHelper::classes($group->id) ?>" id="<?= $group->id ?>">
    <header>
        <h1>
            <?php if ($group->id != 'nogroup' && $is_tutor && !$is_locked) : ?>
                <input aria-label="<?= _('Gruppe auswählen') ?>"
                       type="checkbox" name="groups[]"
                       class="groupselector" value="<?= $group->id ?>"
                       id="<?= $group->id ?>" style="float:left"/>
            <?php endif ?>
            <a href="<?= ContentBoxHelper::href($group->id, array('contentbox_type' => 'news')) ?>">
                <?= htmlReady($group->name) ?> (<?= count($members) .
                ($group->size ? '/' . $group->size : '') ?>)
            </a>
            <?php if ($group->id != 'nogroup') : ?>
                <a class="no-contentbox-link"
                        href="<?= $controller->url_for('course/statusgroups/groupinfo', $group->id) ?>"
                        data-dialog="size=auto">
                    <?= Icon::create('info-circle', 'clickable',
                        array('title' => sprintf(_('Informationen zu %s'), htmlReady($group->name)))) ?></a>
            <?php endif ?>
        </h1>
        <nav>
            <?php if ($is_tutor) : ?>
                <?php if ($group->id != 'nogroup') : ?>
                    <ul class="actionmenu">
                        <li>
                            <div class="action-title">
                                <?= _('Aktionen') ?>
                            </div>
                            <?= Icon::create('action', 'clickable', array('title' => _('Aktionen'), 'class' => 'action-icon')) ?>
                            <ul>
                                <li>
                                    <a href="<?= $controller->url_for('messages/write', array(
                                        'group_id' => $group->id,
                                        'default_subject' => htmlReady($course_title).' ('.htmlReady($group->name).')'
                                    )) ?>" data-dialog="size=auto;">
                                        <?= Icon::create('mail', 'clickable',
                                            array('title' => sprintf(_('Nachricht an alle Mitglieder der Gruppe %s schicken'),
                                                htmlReady($group->name)))) ?>
                                        <?= _('Nachricht schicken') ?>
                                    </a>
                                </li>
                                <?php if (!$is_locked) : ?>
                                    <li>
                                        <?= MultiPersonSearch::get('add_statusgroup_member' . $group->id)
                                            ->setTitle(_('Teilnehmende der Veranstaltung hinzufügen'))
                                            ->setLinkText(_('Personen hinzufügen'))
                                            ->setSearchObject($memberSearch)
                                            ->setDefaultSelectedUser($group->members->pluck('user_id'))
                                            ->setDataDialogStatus(Request::isXhr())
                                            ->setJSFunctionOnSubmit(Request::isXhr() ?
                                                'jQuery(this).closest(".ui-dialog-content").dialog("close");' : false)
                                            ->setExecuteURL($controller->url_for('course/statusgroups/add_member/' .
                                                $group->id))
                                            ->addQuickfilter(_('keiner Gruppe zugeordnete Personen'),
                                                $no_group ? $no_group['members']->pluck('user_id') : array())
                                            ->render() ?>
                                    </li>
                                    <li>
                                        <a href="<?= $controller->url_for('course/statusgroups/edit', $group->id) ?>" data-dialog>
                                            <?= Icon::create('edit', 'clickable',
                                                array('title' => sprintf(_('Gruppe %s bearbeiten'),
                                                    htmlReady($group->name)))) ?>
                                            <?= _('Bearbeiten') ?>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="<?= $controller->url_for('course/statusgroups/delete', $group->id) ?>"
                                           data-confirm="<?= _('Soll die Gruppe wirklich gelöscht werden?') ?>">
                                            <?= Icon::create('trash', 'clickable',
                                                array('title' => sprintf(_('Gruppe %s löschen'),
                                                    htmlReady($group->name)))) ?>
                                            <?= _('Löschen') ?>
                                        </a>
                                    </li>
                                <?php endif ?>
                            </ul>
                        </li>
                    </ul>
                <?php else : ?>
                    <a href="<?= $controller->url_for('messages/write', array(
                        'rec_uname' => $members->pluck('username'),
                        'default_subject' => htmlReady($course_title).' ('.htmlReady($group->name).')'
                    )) ?>" data-dialog="size=auto;">
                        <?= Icon::create('mail', 'clickable',
                            array('title' => _('Nachricht an alle nicht zugeordneten Personen schicken'))) ?></a>
                <?php endif ?>
            <?php else : ?>
                <?php if ($group->id != 'nogroup' && $group->userMayJoin($GLOBALS['user']->id)) : ?>
                    <a href="<?= $controller->url_for('course/statusgroups/join', $group->id) ?>">
                        <?= Icon::create('door-enter', 'clickable',
                            array('title' => sprintf(_('Mitglied von Gruppe %s werden'),
                                htmlReady($group->name)))) ?></a>
                <?php elseif ($group->id != 'nogroup' && $group->selfassign &&
                    $group->selfassign_start > mktime()) : ?>
                        <?= Icon::create('door-enter', 'inactive',
                            array('title' => sprintf(_('Der Eintrag in diese Gruppe ist möglich ab %s.'),
                                date('d.m.Y H:i', $group->selfassign_start)))) ?>
                <?php elseif ($group->id != 'nogroup' && $group->selfassign &&
                    $group->selfassign_end && $group->selfassign_end < mktime()) : ?>
                        <?= Icon::create('door-enter', 'inactive',
                            array('title' => sprintf(_('Der Eintrag in diese Gruppe war möglich bis %s.'),
                                date('d.m.Y H:i', $group->selfassign_end)))) ?>
                <?php elseif ($group->id != 'nogroup' && $group->isMember($GLOBALS['user']->id)) : ?>
                    <a href="<?= $controller->url_for('course/statusgroups/leave', $group->id) ?>">
                        <?= Icon::create('door-leave', 'clickable',
                            array('title' => sprintf(_('Aus Gruppe %s austragen'),
                                htmlReady($group->name)))) ?></a>
                <?php endif ?>
            <?php endif ?>
        </nav>
    </header>
    <section>
        <?php if (count($members)) : ?>
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
                                   data-proxyfor=":checkbox.groupmembers-<?= $group->id ?>">
                        </th>
                    <?php endif ?>
                    <th></th>
                    <th <?= ($sort_by == 'nachname' && $sort_group == $group->id) ?
                        sprintf('class="sort%s"', $order) : '' ?>>
                        <a href="<?= URLHelper::getLink('#' . $group->id,
                            array(
                                'sortby' => 'nachname',
                                'sort_group' => $group->id,
                                'order' => $group->id && $sort_by == 'nachname' ?
                                    ($order == 'desc' ? 'asc' : 'desc') : 'desc',
                                'contentbox_open' => $group->id
                            )) ?>">
                            <?=_('Nachname, Vorname')?>
                        </a>
                    </th>
                    <?php if ($is_tutor) :?>
                        <th <?= ($sort_by == 'mkdate' && $sort_group == $group->id) ? sprintf('class="sort%s"', $order) : '' ?>>
                            <a href="<?= URLHelper::getLink('#' . $group->id,
                                array(
                                    'sortby' => 'mkdate',
                                    'sort_group' => $group->id,
                                    'order' => $group->id && $sort_by == 'mkdate' ?
                                        ($order == 'desc' ? 'asc' : 'desc') : 'desc',
                                    'contentbox_open' => $group->id
                                )) ?>">
                                <?= _('Anmeldedatum') ?>
                            </a>
                        </th>
                        <th>
                            <?= _('Studiengang') ?>
                        </th>
                    <?php endif ?>
                    <th><?= _('Aktion') ?></th>
                </tr>
                </thead>
                <tbody>
                <?php $i = 1; $invisible = 0; foreach ($members as $m) : ?>
                    <?php if ($is_tutor || $m->user_id == $GLOBALS['user']->id || $m->visible != 'no') : ?>
                        <?= $this->render_partial('course/statusgroups/_member',
                            array('m' => $m, 'i' => $i, 'is_tutor' => $is_tutor, 'is_locked' => $is_locked)); $i++ ?>
                    <?php else : $invisible++; endif ?>
                <?php endforeach ?>
                </tbody>
                <tfoot>
                    <tr>
                        <?php if ($is_tutor) : ?>
                            <td colspan="6">
                                <?php if (!$is_locked) : ?>
                                    <div class="memberselect">
                                        <label>
                                            <input aria-label="<?= sprintf(_('Alle Mitglieder dieser Gruppe auswählen')) ?>"
                                                   type="checkbox" name="all" value="1"
                                                   data-proxyfor=":checkbox.groupmembers-<?= $group->id ?>"
                                                   data-activates="select#members-action-<?= $group->id ?>">
                                            <?= _('Alle Mitglieder dieser Gruppe auswählen') ?>
                                        </label>
                                    </div>
                                    <div class="memberactions">
                                        <label>
                                            <select name="members_action[<?= $group->id ?>]"
                                                    id="members-action-<?= $group->id ?>" disabled>
                                                <option value="move"><?= _('In andere Gruppe verschieben') ?></option>
                                                <?php if ($group->id != 'nogroup') : ?>
                                                    <option value="delete"><?= _('Aus dieser Gruppe entfernen') ?></option>
                                                <?php endif ?>
                                            </select>
                                        </label>
                                        <?= Studip\Button::create(_('Ausführen'), 'batch_members['.$group->id.']',
                                            array('data-dialog' => 'size=auto')) ?>
                                    </div>
                                <?php endif ?>
                            </td>
                        <?php elseif (!$is_tutor) : ?>
                            <td colspan="3">
                                <?= sprintf(_('+ %u unsichtbare Personen'), $invisible) ?>
                            </td>
                        <?php endif ?>
                    </tr>
                </tfoot>
            </table>
        <?php else : ?>
            <div class="statusgroup-no-members">
                <?= _('Diese Gruppe hat keine Mitglieder.') ?>
            </div>
        <?php endif ?>
    </section>
</article>
