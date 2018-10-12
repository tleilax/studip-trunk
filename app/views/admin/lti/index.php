<?
$widget = new ActionsWidget();
$widget->addLink(_('Neues LTI-Tool registrieren'), $controller->url_for('admin/lti/edit/0'), Icon::create('add'), ['data-dialog' => '']);
Sidebar::get()->addWidget($widget);

Helpbar::get()->addPlainText('', _('Hier können Sie Verknüpfungen mit externen Tools konfigurieren, sofern diese den LTI-Standard (Version 1.x) unterstützen.'));
?>

<form action="" method="POST">
    <?= CSRFProtection::tokenTag() ?>
    <table class="default">
        <caption>
            <?= _('Aktuell konfigurierte LTI-Tools') ?>
        </caption>

        <thead>
            <th style="width: 30%;">
                <?= _('Name der Anwendung') ?>
            </th>
            <th style="width: 40%;">
                <?= _('URL der Anwendung') ?>
            </th>
            <th style="width: 20%;">
                <?= _('Consumer-Key') ?>
            </th>
            <th style="width: 5%;">
                <?= _('Links') ?>
            </th>
            <th class="actions">
                <?= _('Aktionen') ?>
            </th>
        </thead>

        <tbody>
            <? foreach ($tools as $tool): ?>
                <tr>
                    <td>
                        <a href="<?= $controller->url_for('admin/lti/edit/' . $tool->id) ?>" title="<?= _('LTI-Tool konfigurieren') ?>" data-dialog>
                            <?= Icon::create('edit') ?>
                            <?= htmlReady($tool->name) ?>
                        </a>
                    </td>
                    <td>
                        <?= htmlReady($tool->launch_url) ?>
                    </td>
                    <td>
                        <?= htmlReady($tool->consumer_key) ?>
                    </td>
                    <td>
                        <?= count($tool->links) ?>
                    </td>
                    <td class="actions">
                        <?= Icon::create('trash')->asInput([
                            'formaction' => $controller->url_for('admin/lti/delete/' . $tool->id),
                            'title' => _('LTI-Tool löschen'),
                            'data-confirm' => sprintf(_('Wollen Sie wirklich das LTI-Tool "%s" löschen?'), $tool->name)
                        ]) ?>
                    </td>
                </tr>
            <? endforeach ?>
        </tbody>
    </table>
</form>
