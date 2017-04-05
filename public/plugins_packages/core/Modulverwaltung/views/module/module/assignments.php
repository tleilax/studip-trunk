<section>
    <h3><?= _('Verwendet in Studieng�ngen') ?></h3>
    <? $trails = $modul->getTrails(array('Studiengang', 'StgteilVersion', 'StgteilAbschnitt')); ?>
    <? if (count($trails)) : ?>
    <ul>
        <? foreach ($modul->getPathes($trails, ' > ') as $i => $path) : ?>
        <li>
            <?= htmlReady($path) ?>
        </li>
        <? endforeach; ?>
    </ul>
    <? else : ?>
    <strong><?= _('Keine Zuordnungen vorhanden') ?></strong>
    <? endif; ?>
</section>