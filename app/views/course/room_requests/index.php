<?php
echo $flash['message'];
?>
<h3>
    <?=_("Vorhandene Raumanfragen")?>
</h3>
<? if (count($room_requests)) : ?>
    <table class="default">
        <tr>
            <th width="50%"><?= _('Art der Anfrage') ?></th>
            <th width="15%"><?= _('Anfragender') ?></th>
            <th width="25%"><?= _('Bearbeitungsstatus')?></th>
            <th style="text-align:center"><?= _('Aktionen') ?></th>
        </tr>
    <? foreach ($room_requests as $rr): ?>
        <tr class="<?= TextHelper::cycle('hover_odd', 'hover_even') ?>">
        <td>
        <?=htmlReady($rr->getTypeExplained(),1,1)?>
        </td>
        <td>
        <?=htmlReady($rr['user_id'] ? get_fullname($rr['user_id']) : '')?>
        </td>
        <td>
        <?=htmlReady($rr->getStatusExplained())?>
        </td>
        <td>
        <div style="width:100px;text-align:right;white-space: nowrap">
            <a class="load-in-new-row" href="<?= $controller->link_for('index/'.$course_id, array('request_id' => $rr->getId())) ?>">
                <?= Icon::create('info', 'clickable', ['title' => _('Weitere Informationen einblenden')])->asImg() ?>
            </a>
            <a href="<?= $controller->link_for('edit/'.$course_id, array('request_id' => $rr->getId())) ?>">
                <?= Icon::create('edit', 'clickable', ['title' => _('Diese Anfrage bearbeiten')])->asImg() ?>
            </a>
            <? if (getGlobalPerms($GLOBALS['user']->id) == 'admin' || ($GLOBALS['perm']->have_perm('admin') && count(getMyRoomRequests(null, null, true, $rr->getId())))) : ?>
                <a href="<?= URLHelper::getLink('resources.php', array('view' => 'edit_request', 'single_request' => $rr->getId())) ?>">
                    <?= Icon::create('admin', 'clickable', ['title' => _('Diese Anfrage selbst auflösen')])->asImg() ?>
                </a>
            <? endif ?>
            <a href="<?= $controller->link_for('delete/'.$course_id, array('request_id' => $rr->getId())) ?>">
                <?= Icon::create('trash', 'clickable', ['title' => _('Diese Anfrage zurückziehen')])->asImg() ?>
            </a>
        </div>
        </td>
    </tr>
    <? endforeach ?>
    <? if ($request_id == $rr->getId()) : ?>
        <tr>
            <td colspan="4">
                <?= $this->render_partial('course/room_requests/_request.php', array('request' => $rr));?>
            </td>
        </tr>
    <? endif ?>
    </table>
<? else : ?>
    <?= MessageBox::info(_("Zu dieser Veranstaltung sind noch keine Raumanfragen vorhanden.")) ?>
<? endif ?>
<?
$actions = new ActionsWidget();
$actions->addLink(_('Neue Raumanfrage erstellen'), $controller->url_for('new/'.$course_id), Assets::image_path("icons/16/black/add"));
Sidebar::get()->addWidget($actions);

if ($GLOBALS['perm']->have_perm("admin")) {
    $list = new SelectorWidget();
    $list->setUrl("?#admin_top_links");
    $list->setSelectParameterName("cid");
    foreach (AdminCourseFilter::get()->getCoursesForAdminWidget() as $seminar) {
        $list->addElement(new SelectElement($seminar['Seminar_id'], $seminar['Name']), 'select-' . $seminar['Seminar_id']);
    }
    $list->setSelection($course_id);
    Sidebar::get()->addWidget($list);
}