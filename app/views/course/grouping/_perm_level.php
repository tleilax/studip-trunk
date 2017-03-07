<table class="default">
    <caption>
        <?= htmlReady($level == 'deputy' ? _('Vertretung') : get_title_for_status($level, count($members), $course->status)) ?>
    </caption>
    <colgroup>
        <col width="10">
        <col>
    </colgroup>
    <thead>
    <tr>
        <th></th>
        <th><?= _('Name') ?></th>
    </tr>
    </thead>
    <tbody>
    <?php $i = 0; foreach ($members as $m) : ?>
        <tr>
            <td><?= sprintf('%02d', ++$i) ?></td>
            <td><?= htmlReady($m->getUserFullname('full_rev')) ?></td>
        </tr>
    <?php endforeach ?>
    </tbody>
</table>
