<div id="courseset_<?= $courseset->getId() ?>">
    <a href="#" onclick="return STUDIP.Admission.toggleDetails('courseset_arrow_<?= $courseset->getId() ?>', 'courseset_details_<?= $courseset->getId() ?>')">
        <?= Assets::img('icons/16/blue/arr_1right.png', 
            array('id' => 'courseset_arrow_'.$courseset->getId(), 
            'align' => 'top', 'rel' => Assets::image_path('icons/16/blue/arr_1down.png'))) ?>
        <?= $courseset->getName() ?>
    </a>
    <a href="<?= URLHelper::getURL('dispatch.php/admission/courseset/configure/'.$courseset->getId()); ?>">
        <?= Assets::img('icons/16/blue/edit.png', 
            array('alt' => _('Anmeldeset bearbeiten'), 
                  'title' => _('Anmeldeset bearbeiten'))); ?>
    </a>
    <a href="#">
        <?= Assets::img('icons/16/blue/trash.png', 
            array('alt' => _('Anmeldeset löschen'), 
                  'title' => _('Anmeldeset löschen'))); ?>
    </a>
</div>
<div id="courseset_details_<?= $courseset->getId() ?>" style="display: none; margin-left: 20px;">
    <i><?= _("Einrichtungszuordnung:") ?></i>
    <ul>
    <?php foreach ($institutes as $institute) { ?>
        <li><?= htmlReady($institute) ?></li>
    <?php } ?>
    </ul>
    <i><?= _("Anmelderegeln:") ?></i>
    <ul>
    <?php foreach ($courseset->getAdmissionRules() as $rule) { ?>
        <li><?= $rule->toString() ?></li>
    <?php } ?>
    </ul>
    <i><?= _("Veranstaltungszuordnung:") ?></i>
    <ul>
    <?php foreach ($courses as $id => $course) { ?>
        <li><?= htmlReady($course) ?></li>
    <?php } ?>
    </ul>
</div>