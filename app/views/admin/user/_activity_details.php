<?
$sections = [
        'seminars'   => [
                'title' => _('Dateiübersicht Veranstaltungen'),
                'link'  => 'seminar_main.php?redirect_to=folder.php&cmd=all',
        ],
        'institutes' => [
                'title' => _('Dateiübersicht Einrichtungen'),
                'link'  => 'dispatch.php/institute/overview?redirect_to=folder.php&cmd=all',
        ],
];
?>

<? if (count($courses)) : ?>
    <section class="contentbox">
        <header>
            <h1>
                <?= _('Dateiübersicht') ?>
            </h1>
        </header>
        <article id="<?= 'courses' ?>" class="<?= ContentBoxHelper::classes('courses') ?>">
            <header>
                <h1>
                    <a href="<?= ContentBoxHelper::href('courses') ?>">
                        <?= _('Dateiübersicht für Veranstaltungen') ?>
                    </a>
                </h1>
            </header>
            <section>
                <table class="default">
                    <colgroup>
                        <col style="width: 200px">
                        <col>
                        <col style="width: 120px">
                        <col style="width: 20px">
                    </colgroup>
                    <thead>
                        <tr>
                            <th><?= _('Veranstaltungsnummer') ?></th>
                            <th><?= _('Veranstaltung') ?></th>
                            <th><?= _('Anzahl') ?></th>
                            <th class="actions"><?= _('Aktionen') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <? foreach ($courses as $data): ?>
                            <tr>
                                <td><?= htmlReady($data['course']->veranstaltungsnummer) ?></td>
                                <td>
                                    <?= htmlReady($data['course']->getFullName('type-name')) ?>
                                </td>
                                <td>
                                    <?= sprintf('%u %s', count($data['files']), _('Dokumente')) ?>
                                </td>
                                <td class="actions">
                                    <?
                                    $actionMenu = ActionMenu::get();
                                    $actionMenu->addLink($controller->url_for('admin/user/list_files/' . $user['user_id'] . '/' . $data['course']->id),
                                            _('Dateien auflisten'),
                                            Icon::create('folder-full', 'clickable'),
                                            ['data-dialog' => 'size=50%']);
                                    $actionMenu->addLink($controller->url_for('admin/user/download/' . $data['course']->id),
                                            _('Dateien als ZIP herunterladen'),
                                            Icon::create('download', 'clickable'));

                                    ?>

                                    <?= $actionMenu->render() ?>
                                </td>
                            </tr>
                        <? endforeach; ?>
                    </tbody>
                </table>
            </section>
        </article>
        <? if ($institutes) : ?>
            <article id="<?= 'institutes' ?>" class="<?= ContentBoxHelper::classes('institutes') ?>">
                <header>
                    <h1>
                        <a href="<?= ContentBoxHelper::href('institutes') ?>">
                            <?= _('Dateiübersicht für Einrichtungen') ?>
                        </a>
                    </h1>
                </header>

                <section>
                    <table class="default">
                        <colgroup>
                            <col>
                            <col style="width: 120px">
                            <col style="width: 20px">
                        </colgroup>
                        <thead>
                            <tr>
                                <th><?= _('Dateiname') ?></th>
                                <th><?= _('Anzahl') ?></th>
                                <th class="actions"><?= _('Aktionen') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <? foreach ($institutes as $data): ?>
                                <tr>
                                    <td>
                                        <?= htmlReady($data['institute']->name) ?>
                                    </td>
                                    <td>
                                        <?= sprintf('%u %s', count($data['files']), _('Dokumente')) ?>
                                    </td>
                                    <td class="actions">
                                        <?
                                        $actionMenu = ActionMenu::get();
                                        $actionMenu->addLink($controller->url_for('admin/user/list_files/' . $user['user_id'] . '/' . $data['institute']->id),
                                                _('Dateien auflisten'),
                                                Icon::create('folder-full', 'clickable'),
                                                ['data-dialog' => 'size=50%']);
                                        $actionMenu->addLink($controller->url_for('admin/user/download/' . $data['institute']->id),
                                                _('Dateien als ZIP herunterladen'),
                                                Icon::create('download', 'clickable'));

                                        ?>

                                        <?= $actionMenu->render() ?>
                                    </td>
                                </tr>
                            <? endforeach; ?>
                        </tbody>
                    </table>
                </section>
            </article>

        <? endif ?>
    </section>
<? endif ?>

    


