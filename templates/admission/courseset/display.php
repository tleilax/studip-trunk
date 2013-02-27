<i><?= _("Einrichtungszuordnung:") ?></i>
<ul>
<?php foreach ($institutes as $institute) { ?>
    <li><?= htmlReady($institute) ?></li>
<?php } ?>
</ul>
<i><?= _("Anmelderegeln:") ?></i>
<ul>
<?php foreach ($courseset->getAdmissionRules() as $rule) { ?>
    <li>
        <b><?= $rule->getName() ?></b>
        <br/>
        <?= $rule->toString() ?>
    </li>
<?php } ?>
</ul>
<i><?= _("Veranstaltungszuordnung:") ?></i>
<ul>
<?php foreach ($courses as $id => $course) { ?>
    <li><?= htmlReady($course) ?></li>
<?php } ?>
</ul>