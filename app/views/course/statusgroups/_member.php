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
        <ul class="actionmenu">
            <li>
                <div class="action-title">
                    <?= _('Aktionen') ?>
                </div>
                <?= Icon::create('action', 'clickable', array('title' => _('Aktionen'))) ?>
                <ul>
                    <?php if ($is_tutor || $m->user_id != $GLOBALS['user']->id) : ?>
                        <li>
                            <a href="<?= $controller->url_for('messages/write', array(
                                'rec_uname' => $m->user->username,
                                'default_subject' => $course_title
                            )) ?>" data-dialog="size=auto;">
                                <?= Icon::create('mail', 'clickable',
                                    array('title' => sprintf(_('Nachricht an %s schicken'),
                                        $m->user->getFullname()))) ?>
                                <?= _('Nachricht schicken') ?>
                            </a>
                        </li>
                    <?php endif ?>
                    <?php if ($is_tutor) : ?>
                        <li>
                            <a href="<?= $controller->url_for('course/statusgroups/move_member',
                                $m->user_id, $group->id) ?>" data-dialog="size=auto;">
                                <?= Icon::create('person+move_right', 'clickable',
                                    array('title' => sprintf(_('%s in eine andere Gruppe verschieben'),
                                        $m->user->getFullname()))) ?>
                                <?= _('In eine andere Gruppe verschieben') ?>
                            </a>
                        </li>
                    <?php endif ?>
                    <?php if ($group->id != 'nogroup' &&
                        ($is_tutor || $m->user_id == $GLOBALS['user']->id)) : ?>
                        <li>
                            <a href="<?= $controller->url_for('course/statusgroups/delete_member',
                                $m->user_id, $group->id) ?>"
                               data-confirm="<?= sprintf(
                                   _('Soll %s wirklich aus der Gruppe %s entfernt werden?'),
                                   htmlReady($m->getUserFullname()), htmlReady($group->name)) ?>">
                                <?= Icon::create('trash', 'clickable',
                                    array('title' => sprintf(_('%s aus Gruppe %s entfernen'),
                                        htmlReady($m->getUserFullname()),
                                        htmlReady($group->name)))) ?>
                                <?= _('Aus der Gruppe entfernen') ?>
                            </a>
                        </li>
                    <?php endif ?>
                </ul>
            </li>
        </ul>
    </td>
</tr>
