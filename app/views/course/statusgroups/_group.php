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
        </h1>
        <nav>
            <?php
                if ($group->id != 'nogroup') {
                    $info .= '<p>'.($group->size > 0 ?
                        sprintf(_('Diese Gruppe ist auf %u Mitglieder beschränkt.'), $group->size) :
                        sprintf(_('Die Größe dieser Gruppe ist nicht beschränkt.'))).'</p>';
                    if ($group->selfassign) {
                        if ($group->selfassign == 1) {
                            $info .= '<p>'._('Die Teilnehmenden dieser Veranstaltung können sich ' .
                                'selbst in beliebig viele der Gruppen einteilen, bei denen ' .
                                'kein Exklusiveintrag aktiviert ist.').'</p>';
                        } else if ($group->selfassign == 2) {
                            $info .= '<p>'._('Die Teilnehmenden dieser Veranstaltung können sich ' .
                                'in genau einer der Gruppen einteilen, bei denen der ' .
                                'Exklusiveintrag aktiviert ist.').'</p>';
                        }
                        if ($group->selfassign_start && $group->selfassign_end) {
                            $info .= '<p>'.sprintf(_('Der Eintrag ist möglich von %s bis %s.'),
                                date('d.m.Y H:i', $group->selfassign_start),
                                date('d.m.Y H:i', $group->selfassign_end)).'</p>';
                        } else if ($group->selfassign_start && !$group->selfassign_end) {
                            $info .= '<p>'.sprintf(_('Der Eintrag ist möglich ab %s.'),
                                date('d.m.Y H:i', $group->selfassign_start)).'</p>';
                        } else if (!$group->selfassign_start && $group->selfassign_end) {
                            $info .= '<p>'.sprintf(_('Der Eintrag ist möglich bis %s.'),
                                date('d.m.Y H:i', $group->selfassign_end)).'</p>';
                        }
                    }
                    echo tooltipicon($info);
                }
            ?>
            <?php if ($is_tutor) : ?>
                <?php if ($group->id != 'nogroup') : ?>
                    <?= tooltipicon($info) ?>
                    <a href="<?= $controller->url_for('messages/write', array(
                        'group_id' => $group->id,
                        'default_subject' => $course_title
                    )) ?>" data-dialog="size=auto;">
                        <?= Icon::create('mail', 'clickable',
                            array('title' => sprintf(_('Nachricht an alle Mitglieder der Gruppe %s schicken'),
                                htmlReady($group->name)))) ?></a>
                    <a href="<?= $controller->url_for('course/statusgroups/edit', $group->id) ?>" data-dialog>
                        <?= Icon::create('edit', 'clickable',
                            array('title' => sprintf(_('Gruppe %s bearbeiten'),
                                htmlReady($group->name)))) ?></a>
                    <a href="<?= $controller->url_for('course/statusgroups/delete', $group->id) ?>"
                       data-confirm="<?= sprintf(_('Soll die Gruppe %s wirklich gelöscht werden?'),
                           htmlReady($group->name)) ?>">
                        <?= Icon::create('trash', 'clickable',
                            array('title' => sprintf(_('Gruppe %s löschen'),
                                htmlReady($group->name)))) ?></a>
                <?php else : ?>
                    <a href="<?= $controller->url_for('messages/write', array(
                        'rec_uname' => $members->pluck('username'),
                        'default_subject' => $course_title
                    )) ?>" data-dialog="size=auto;">
                        <?= Icon::create('mail', 'clickable',
                            array('title' => _('Nachricht an alle nicht zugeordneten Personen schicken'))) ?></a>
                <?php endif ?>
            <?php else : ?>
                <?php if ($group->id != 'nogroup' && $group->userMayJoin($GLOBALS['user']->id)) : ?>
                    <a href="<?= $controller->url_for('course/statusgroups/join', $group->id) ?>">
                        <?= Icon::create('arr_2right', 'clickable',
                            array('title' => sprintf(_('Mitglied von Gruppe %s werden'),
                                htmlReady($group->name)))) ?></a>
                <?php elseif ($group->id != 'nogroup' && $group->selfassign &&
                    $group->selfassign_start > mktime()) : ?>
                        <?= Icon::create('arr_2right', 'inactive',
                            array('title' => sprintf(_('Der Eintrag in diese Gruppe ist möglich ab %s.'),
                                date('d.m.Y H:i', $group->selfassign_start)))) ?>
                <?php elseif ($group->id != 'nogroup' && $group->selfassign &&
                    $group->selfassign_end && $group->selfassign_end < mktime()) : ?>
                        <?= Icon::create('arr_2right', 'inactive',
                            array('title' => sprintf(_('Der Eintrag in diese Gruppe war möglich bis %s.'),
                                date('d.m.Y H:i', $group->selfassign_end)))) ?>
                <?php elseif ($group->id != 'nogroup' && $group->isMember($GLOBALS['user']->id)) : ?>
                    <a href="<?= $controller->url_for('course/statusgroups/leave', $group->id) ?>">
                        <?= Icon::create('trash', 'clickable',
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
                        <tr>
                            <?php if ($is_tutor && !$is_locked) : ?>
                                <td>
                                    <input aria-label="<?= _('Mitglieder auswählen') ?>"
                                           type="checkbox" name="group[<?= $group->id ?>][<?= $m->user_id ?>]"
                                           class="groupmembers-<?= $group->id ?>" value="1" />
                                </td>
                            <?php endif ?>
                            <td>
                                <?= str_pad($i, 2, '0', STR_PAD_LEFT) ?>
                            </td>
                            <td>
                                <a href="<?= $controller->url_for(sprintf('profile?username=%s',$m->username)) ?>">
                                    <?= Avatar::getAvatar($m->user_id, $m->username)->getImageTag(Avatar::SMALL,
                                        array('style' => 'margin-right: 5px',
                                            'title' => htmlReady($m->getUserFullname('full_rev')))); ?>
                                    <?= htmlReady($m->getUserFullname('full_rev')) ?>
                                    <?php if ($user_id == $m->user_id && $m->visible == 'no') : ?>
                                        (<?= _('unsichtbar') ?>)
                                    <?php endif ?>
                                </a>
                            </td>
                            <?php if($is_tutor) : ?>
                                <td>
                                    <?= strftime('%x %X', $m->mkdate) ?>
                                </td>
                                <td>
                                    <?= $this->render_partial('course/members/_studycourse.php',
                                        array('study_courses' => UserModel::getUserStudycourse($m->user_id))) ?>
                                </td>
                            <?php endif ?>
                            <td class="memberactions">
                                <?php if ($is_tutor || $m->user_id != $GLOBALS['user']->id) : ?>
                                    <a href="<?= $controller->url_for('messages/write', array(
                                        'rec_uname' => $m->user->username,
                                        'default_subject' => $course_title
                                    )) ?>" data-dialog="size=auto;">
                                        <?= Icon::create('mail', 'clickable',
                                            array('title' => sprintf(_('Nachricht an %s schicken'),
                                                $m->user->getFullname()))) ?></a>
                                <?php endif ?>
                                <?php if ($is_tutor) : ?>
                                    <a href="<?= $controller->url_for('course/statusgroups/move_member',
                                        $m->user_id, $group->id) ?>" data-dialog="size=auto;">
                                        <?= Icon::create('person+move_right', 'clickable',
                                            array('title' => sprintf(_('%s in eine andere Gruppe verschieben'),
                                                $m->user->getFullname()))) ?></a>
                                <?php endif ?>
                                <?php if ($group->id != 'nogroup' &&
                                    ($is_tutor || $m->user_id == $GLOBALS['user']->id)) : ?>
                                    <a href="<?= $controller->url_for('course/statusgroups/delete_member',
                                        $m->user_id, $group->id) ?>"
                                       data-confirm="<?= sprintf(
                                           _('Soll %s wirklich aus der Gruppe %s entfernt werden?'),
                                           htmlReady($m->getUserFullname()), htmlReady($group->name)) ?>">
                                        <?= Icon::create('trash', 'clickable',
                                            array('title' => sprintf(_('%s aus Gruppe %s entfernen'),
                                                htmlReady($m->getUserFullname()),
                                                htmlReady($group->name)))) ?></a>
                                <?php endif ?>
                            </td>
                        </tr>
                        <?php $i++; else : $invisible++; endif ?>
                <?php endforeach ?>
                </tbody>
                <tfoot>
                <tr>
                    <?php if (count($members) > 1 && $is_tutor && !$is_locked) : ?>
                        <td colspan="4" class="memberselect">
                            <label>
                                <input aria-label="<?= sprintf(_('Alle Mitglieder dieser Gruppe auswählen')) ?>"
                                       type="checkbox" name="all" value="1"
                                       data-proxyfor=":checkbox.groupmembers-<?= $group->id ?>">
                                <?= _('Alle Mitglieder dieser Gruppe auswählen') ?>
                            </label>
                        </td>
                        <td colspan="2" class="memberactions">
                            <label>
                                <select name="members_action[<?= $group->id ?>]">
                                    <option value="move"><?= _('In andere Gruppe verschieben') ?></option>
                                    <?php if ($group->id != 'nogroup') : ?>
                                        <option value="delete"><?= _('Aus dieser Gruppe entfernen') ?></option>
                                    <?php endif ?>
                                </select>
                            </label>
                            <?= Studip\Button::create(_('Ausführen'), 'batch_members['.$group->id.']',
                                array('data-dialog' => 'size=auto')) ?>
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
            <?= MessageBox::info(_('Diese Gruppe hat keine Mitglieder.')) ?>
        <?php endif ?>
    </section>
</article>
