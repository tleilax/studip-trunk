<article id="<?= $class ?>" class="<?= ContentBoxHelper::classes($class) ?>">
    <header>
        <h1>
            <a href="<?= ContentBoxHelper::href($class) ?>">
                <?= $headline ?>
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
                    <th><?= _('Status') ?></th>
                </tr>
            </thead>
            <tbody>
                <? foreach ($memberships as $membership): ?>
                    <tr>
                        <td><?= htmlReady($membership->course->veranstaltungsnummer) ?></td>
                        <td>
                            <?= htmlReady($membership->course->getFullName('type-name')) ?>
                        </td>
                        <td>
                            <?= htmlReady($membership->status) ?>
                        </td>
                    </tr>
                <? endforeach; ?>
            </tbody>
        </table>
    </section>
</article>