<? use \Studip\Button; ?>
<br />
<a name="awaiting"></a>
<form action="<?= $controller->url_for('course/members/edit_awaiting/') ?>" method="post" data-dialog="size=50%">
    <?= CSRFProtection::tokenTag() ?>
    <table class="default collapsable ">
        <caption>
            <?= $waitingTitle ?>
            <span class="actions">
                    <a href="<?= URLHelper::getLink('dispatch.php/messages/write',
                            ['filter'               => $waiting_type,
                             'emailrequest'         => 1,
                             'course_id'            => $course_id,
                             'default_subject'      => $subject])?>" data-dialog>
                        <?= Icon::create('inbox', 'clickable', ['title' =>  _('Nachricht mit Mailweiterleitung an alle Wartenden versenden')])->asImg()?>
                    </a>
            </span>
        </caption>
        <colgroup>
            <col width="20">
            <col width="20">
            <col>
            <col width="15%">
            <col width="35%">
            <col width="80">
        </colgroup>
        <thead>
            <tr class="sortable">
                <? if (!$is_locked) : ?>
                <th>
                    <input aria-label="<?= _('NutzerInnen ausw�hlen') ?>"
                            type="checkbox" name="all" value="1" data-proxyfor=":checkbox[name^=awaiting]" />
                </th>
                <? endif ?>
                <th></th>
                <th <?= ($sort_by == 'nachname' && $sort_status == $waiting_type) ?
                    sprintf('class="sort%s"', $order) : '' ?>>
                    <a href="<?= URLHelper::getLink(sprintf("?sortby=nachname&sort_status=$waiting_type&order=%s&toggle=%s",
                            $order, ($sort_by == 'nachname'))) ?>#awaiting">
                        <?=_('Nachname, Vorname')?>
                    </a>
                </th>
                <th style="text-align: center" <?= ($sort_by == 'position' && $sort_status == $waiting_type) ?
                    sprintf('class="sort%s"', $order) : '' ?>>
                    <? ($sort_status != $waiting_type) ? $order = 'desc' : $order = $order ?>
                    <a href="<?= URLHelper::getLink(sprintf('?sortby=position&sort_status='.$waiting_type.'&order=%s&toggle=%s',
                            $order, ($sort_by == 'position'))) ?>#awaiting">
                        <?= $waiting_type === 'awaiting' ? _('Position') : _('Priorit�t') ?>
                    </a>
                </th>
                <th>
                    <?= _('Studiengang')  ?>
                </th>
                <th class="actions"><?= _('Aktion') ?></th>
            </tr>
        </thead>
        <tbody>
        <? $nr = 0 ?>
        <? foreach($awaiting as $waiting) : ?>
        <? $fullname = $waiting['fullname'] ;?>
            <tr>
                <td>
                <? if (!$is_locked) : ?>
                    <input aria-label="<?= _('Alle NutzerInnen ausw�hlen') ?>" type="checkbox"
                            name="awaiting[<?= $waiting['user_id'] ?>]" value="1" />
                <? endif ?>
                </td>
                <td style="text-align: right"><?= (++$nr < 10) ? sprintf('%02d', $nr) : $nr ?></td>
                <td>
                    <a href="<?= $controller->url_for(sprintf('profile?username=%s',$waiting['username'])) ?>" <? if ($waiting['mkdate'] >= $last_visitdate) echo 'class="new-member"'; ?>>
                        <?= Avatar::getAvatar($waiting['user_id'], $waiting['username'])->getImageTag(Avatar::SMALL,
                                ['style' => 'margin-right: 5px',
                                 'title' => htmlReady($fullname)]); ?>
                        <?= htmlReady($fullname) ?>
                    </a>
                </td>
                <td style="text-align: center">
                    <?= $waiting['position'] ?>
                </td>
                <td>
                    <?= $this->render_partial('course/members/_studycourse.php',['studycourses' => new SimpleCollection(UserStudyCourse::findByUser_id($autor['user_id']))]) ?>
                </td>
                <td class="actions">
                    <? $actionMenu = ActionMenu::get() ?>
                    <? if($user_id != $waiting['user_id']) : ?>
                        <? $actionMenu->addLink(URLHelper::getLink('dispatch.php/messages/write',
                                ['filter'           => 'send_sms_to_all',
                                 'emailrequest'    => 1,
                                 'rec_uname'       => $waiting['username'],
                                 'default_subject' => $subject]),
                                _('Nachricht mit Mailweiterleitung senden'),
                                Icon::create('mail', 'clickable', ['title' => sprintf('Nachricht mit Weiterleitung an %s senden', $fullname)]),
                                ['data-dialog' => '1']) ?>
                    <? endif?>
                    <? if (!$is_locked) : ?>
                        <? $actionMenu->addLink($controller->url_for('course/members/cancel_subscription/singleuser/' . $waiting_type . '/' . $waiting['user_id']),
                                _('Aus Veranstaltung austragen'),
                                Icon::create('door-leave', 'clickable', ['title' => sprintf(_('%s austragen'),htmlReady($fullname))])) ?>
                    <? endif ?>
                    <?= $actionMenu->render() ?>
                </td>
            </tr>
        <? endforeach ?>
        </tbody>
        <? if (!$is_locked) : ?>
        <tfoot>
            <tr>
                <td colspan="6">
                    <select name="action_awaiting" id="action_awaiting" aria-label="<?= _('Aktion ausf�hren') ?>">
                        <option value="">- <?= _('Aktion w�hlen') ?></option>
                        <option value="upgrade_autor"><?= sprintf(_('Zu %s hochstufen'),
                                htmlReady($status_groups['autor'])) ?></option>
                        <option value="upgrade_user"><?= sprintf(_('Zu %s hochstufen'),
                                htmlReady($status_groups['user'])) ?></option>
                        <option value="remove"><?= _('Austragen') ?></option>
                        <option value="message"><?=_('Nachricht senden')?></option>
    <!--                    <option value="copy_to_sem"><?= _('In Seminar verschieben/kopieren') ?></option>-->
                    </select>
                    <input type="hidden" value="<?=$waiting_type?>" name="waiting_type"/>
                    <?= Button::create(_('Ausf�hren'), 'submit_awaiting') ?>
                </td>
            </tr>
        </tfoot>
        <? endif ?>
    </table>
</form>
