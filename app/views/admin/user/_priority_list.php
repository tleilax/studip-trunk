<article id="<?= 'priorities' ?>" class="<?= ContentBoxHelper::classes('priorities') ?>">
    <header>
        <h1>
            <a href="<?= ContentBoxHelper::href('priorities') ?>">
                <?= _('Übersicht Anmeldelisten von Veranstaltungen mit automatischer Platzvergabe') ?>
            </a>
        </h1>
    </header>
    <section>
        <table class="default">
            <colgroup>
                <col style="width: 200px">
                <col>
                <col style="width: 15%">
            </colgroup>
            <thead>
                <tr>
                    <th><?= _('Veranstaltungsnummer') ?></th>
                    <th><?= _('Veranstaltung') ?></th>
                    <th style="text-align: center"><?= _('Priorität') ?></th>
                </tr>
            </thead>
            <tbody>
                <? foreach ($priorities as $priority): ?>
                    <? $course = Course::find($priority['seminar_id']) ?>
                    <tr>
                        <td><?= htmlReady($course->veranstaltungsnummer) ?></td>
                        <td><?= htmlReady($course->getFullName('type-name')) ?></td>
                        <td style="text-align: center"><?= htmlReady($priority['priority']) ?></td>
                    </tr>
                <? endforeach; ?>
            </tbody>
        </table>
    </section>
</article>