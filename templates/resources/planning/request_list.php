<? if ($license_to_kill) : ?>
    <form name="list_requests_form" method="post" action="<?= URLHelper::getLink() ?>">
    <?= CSRFProtection::tokenTag() ?>
<? endif ?>
<? $i = 0; ?>
    <table class="default">
        <caption>
            <?= _('Anfrageliste') ?>
        </caption>
        <thead>
            <tr>
                <? if ($license_to_kill) : ?>
                    <th>
                        <input type="checkbox"
                               data-proxyfor="[name^=requests_marked_to_kill]:checkbox"
                               title="<?= _('Alle ausw�hlen') ?>">
                    </th>
                <? endif ?>
                <th><?= _('Z�hler') ?></th>
                <th><?= _('V.-Nummer') ?></th>
                <th><?= _('Titel') ?></th>
                <th><?= _('Dozenten') ?></th>
                <th><?= _('Anfrager') ?></th>
                <th><?= _('Start-Semester') ?></th>
                <th class="actions"><?= _('Aktionen') ?></th>
            </tr>
        </thead>
        <? foreach ($_SESSION['resources_data']['requests_working_on'] as $key => $val) : ?>
            <? $i++; ?>
            <? if ($_SESSION['resources_data']['requests_open'][$val['request_id']] || !$_SESSION['resources_data']['skip_closed_requests']) : ?>
                <?
                $reqObj = new RoomRequest($val['request_id']);
                $semObj = Seminar::GetInstance($reqObj->course->id)
                ?>
                <? if ($semObj->getName() != "")  : ?>
                    <tr>

                        <? if ($license_to_kill) : ?>
                            <td>
                                <input type="checkbox" name="requests_marked_to_kill[]"
                                       value="<?= $val['request_id'] ?>">
                            </td>
                        <? endif ?>
                        <td>
                            <?= $i ?>
                        </td>
                        <td>
                            <?= htmlReady($semObj->seminar_number) ?>
                        </td>
                        <td>
                            <a href="<?= URLHelper::getLink('dispatch.php/course/details/', ['sem_id'                => $semObj->id,
                                                                                             'send_from_search'      => true,
                                                                                             'send_from_search_page' => URLHelper::getLink('resources.php', ['view' => 'list_requests'])]) ?>">
                        <?= my_substr(htmlReady($semObj->getFullName()), 0, 50) ?></a>
                        </td>
                        <td>
                            <?
                            $k = false;
                            foreach ($semObj->getMembers('dozent') as $doz) {
                                if ($k) echo ", ";
                                echo "<a href=\"" . URLHelper::getLink('dispatch.php/profile', ['username' => $doz['username']]) . "\">" . HtmlReady($doz['fullname']) . "</a>";
                                $k = true;
                            }
                            ?>
                        </td>
                        <td>
                            <a href="<?= URLHelper::getLink('dispatch.php/profile', ['username' => $reqObj->user->username]) ?>
                            "> <?= htmlReady($reqObj->user->getFullName()) ?></a>
                        </td>
                        <td>
                            <?= htmlReady($semObj->start_semester->name) ?>
                        </td>
                        <td class="actions">
                            <a href="<?= URLHelper::getLink('resources.php', ['view' => 'edit_request',
                                                                              'edit' => $val['request_id']]) ?>">
                                <?= Icon::create('edit', 'clickable', ['title' => _("Anfrage bearbeiten")])->asImg() ?>
                            </a>
                            <?= (($_SESSION['resources_data']['requests_open'][$val['request_id']]) ? '' : Icon::create('accept', 'accept')->asImg()) ?>
                        </td>
                    </tr>
                <? endif ?>
            <? endif ?>
        <? endforeach; ?>
        <? if ($license_to_kill) : ?>
            <tfoot>
                <tr>
                    <td colspan="8">
                        <?= Button::create(_('Ausgew�hlte l�schen'), 'do_delete_requests', ['title' => _('Ausgew�hlte Anfragen l�schen')]) ?>
                    </td>
                </tr>
            </tfoot>
        <? endif ?>
    </table>
<? if ($license_to_kill) : ?>
    </form>
<? endif ?>