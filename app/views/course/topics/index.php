<? if (count($topics) > 0) : ?>
<table class="default withdetails">
    <colgroup>
        <col width="50%">
        <col>
        <col width="24px">
    </colgroup>
    <thead>
        <tr>
            <th><?= _("Thema") ?></th>
            <th><?= _("Termine") ?></th>
            <th>
                <abbr title="<?= _('Thema behandelt eine Hausarbeit oder ein Referat') ?>">
                    <?= Icon::create('glossary', Icon::ROLE_INFO) ?>
                </abbr>
            </th>
        </tr>
    </thead>
    <tbody>
    <? foreach ($topics as $key => $topic) : ?>
        <tr class="<?= Request::get("open") === $topic->getId() ? "open" : "" ?>">
            <td><a href="" name="<?=$topic->getId()?>" onClick="jQuery(this).closest('tr').toggleClass('open'); return false;"><?= htmlReady($topic['title']) ?></a></td>
            <td>
                <ul class="clean">
                    <? foreach ($topic->dates as $date) : ?>
                        <li>
                            <a href="<?= URLHelper::getLink("dispatch.php/course/dates/details/".$date->getId()) ?>" data-dialog="buttons=false">
                                <?= Icon::create('date', 'clickable')->asImg(['class' => "text-bottom"]) ?>
                                <?= htmlReady($date->getFullName()) ?>
                            </a>
                        </li>
                    <? endforeach ?>
                </ul>
            </td>
            <td>
            <? if ($topic->paper_related): ?>
                <?= Icon::create('checkbox-checked', Icon::ROLE_INFO) ?>
            <? else: ?>
                <?= Icon::create('checkbox-unchecked', Icon::ROLE_INFO) ?>
            <? endif; ?>
            </td>
        </tr>
        <tr class="details nohover">
            <td colspan="3">
                <div class="detailscontainer">
                    <table class="default nohover">
                        <tbody>
                        <tr>
                            <td><strong><?= _("Beschreibung") ?></strong></td>
                            <td><?= formatReady($topic['description']) ?></td>
                        </tr>
                        <tr>
                            <td><strong><?= _("Materialien") ?></strong></td>
                            <td>
                                <? $material = false ?>
                                <ul class="clean">
                                    <? $folder = $topic->folders->first() ?>
                                    <? if ($documents_activated && $folder) : ?>
                                        <li>
                                            <a href="<?= URLHelper::getLink(
                                                'dispatch.php/course/files/index/' . $folder->id
                                                ) ?>">
                                                <?= $folder->getTypedFolder()->getIcon('clickable')->asImg(['class' => "text-bottom"]) ?>
                                                <?= _("Dateiordner") ?>
                                            </a>
                                        </li>
                                        <? $material = true ?>
                                    <? endif ?>

                                    <? if ($forum_activated && ($link_to_thread = $topic->forum_thread_url)) : ?>
                                        <li>
                                            <a href="<?= URLHelper::getLink($link_to_thread) ?>">
                                                <?= Icon::create('forum', 'clickable')->asImg(['class' => "text-bottom"]) ?>
                                                <?= _("Thema im Forum") ?>
                                            </a>
                                        </li>
                                        <? $material = true ?>
                                    <? endif ?>
                                </ul>
                                <? if (!$material) : ?>
                                    <?= _("Keine Materialien zu dem Thema vorhanden") ?>
                                <? endif ?>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    <div style="text-align: center;">
                        <? if ($GLOBALS['perm']->have_studip_perm("tutor", Context::getId())) : ?>
                            <?= \Studip\LinkButton::createEdit(_('Bearbeiten'),
                                                               URLHelper::getURL("dispatch.php/course/topics/edit/".$topic->getId()),
                                                               array('data-dialog' => '')) ?>

                            <? if (!$cancelled_dates_locked && $topic->dates->count()) : ?>
                                <?= \Studip\LinkButton::create(_("Alle Termine ausfallen lassen"), URLHelper::getURL("dispatch.php/course/cancel_dates", array('issue_id' => $topic->getId())), array('data-dialog' => '')) ?>
                            <? endif ?>
                            <? if ($key > 0) : ?>
                                <form action="?" method="post" style="display: inline;">
                                    <input type="hidden" name="move_up" value="<?= $topic->getId() ?>">
                                    <input type="hidden" name="open" value="<?= $topic->getId() ?>">
                                    <?= \Studip\Button::createMoveUp(_("nach oben verschieben")) ?>
                                </form>
                            <? endif ?>
                            <? if ($key < count($topics) - 1) : ?>
                            <form action="?" method="post" style="display: inline;">
                                <input type="hidden" name="move_down" value="<?= $topic->getId() ?>">
                                <input type="hidden" name="open" value="<?= $topic->getId() ?>">
                                <?= \Studip\Button::createMoveDown(_("nach unten verschieben")) ?>
                            </form>
                            <? endif ?>
                        <? endif ?>
                    </div>
                </div>
            </td>
        </tr>
    <? endforeach ?>
    </tbody>
</table>
<? else : ?>
    <? PageLayout::postMessage(MessageBox::info(_("Keine Themen vorhanden."))) ?>
<? endif ?>

<?php
$sidebar = Sidebar::get();
$sidebar->setImage('sidebar/date-sidebar.png');

$actions = new ActionsWidget();
$actions->addLink(_("Alle Themen aufklappen"),
                  null, Icon::create('arr_1down', 'clickable'),
                  array('onClick' => "jQuery('table.withdetails > tbody > tr:not(.details):not(.open) > :first-child a').click(); return false;"));
if ($GLOBALS['perm']->have_studip_perm("tutor", Context::getId())) {
    $actions->addLink(
        _("Neues Thema erstellen"),
        URLHelper::getURL("dispatch.php/course/topics/edit"),
        Icon::create('add', 'clickable'),
        array('data-dialog' => "buttons")
    );
    $actions->addLink(
        _("Themen aus Veranstaltung kopieren"),
        URLHelper::getURL("dispatch.php/course/topics/copy"),
        Icon::create('topic+add', 'clickable'),
        array('data-dialog' => "buttons")
    );
}
$sidebar->addWidget($actions);

if ($GLOBALS['perm']->have_studip_perm('tutor', Context::getId())) {
    $options = new OptionsWidget();
    $options->addCheckbox(
        _('Themen öffentlich einsehbar'),
        Context::get()->public_topics,
        $controller->url_for('course/topics/allow_public')
    );
    $sidebar->addWidget($options);
}
