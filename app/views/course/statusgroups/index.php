<form action="<?= $controller->url_for('course/statusgroups/batch_action') ?>" method="post">
<section class="contentbox course-statusgroups">
    <?php if ($no_group) : ?>
        <?= $this->render_partial('course/statusgroups/_group',
            array('group' => $no_group['group'], 'members' => $no_group['members'])) ?>
    <?php endif ?>
    <?php foreach ($groups as $group) : ?>
        <?= $this->render_partial('course/statusgroups/_group',
            array('group' => $group['group'], 'members' => $group['members'])) ?>
    <?php endforeach ?>
    <?php if (count($groups) > 1 && $is_tutor && !$is_locked) : ?>
        <footer>
            <div class="groupselection">
                <label>
                    <input aria-label="<?= sprintf(_('Alle Gruppen ausw�hlen')) ?>"
                           type="checkbox" name="allgroups" value="1"
                           data-proxyfor=":checkbox.groupselector">
                    <?= _('Alle Gruppen ausw�hlen') ?>
                </label>
            </div>
            <div class="groupactions">
                <label>
                    <select name="groups_action">
                        <option value="edit"><?= _('Bearbeiten') ?></option>
                        <option value="delete"><?= _('L�schen') ?></option>
                    </select>
                </label>
                <?= Studip\Button::create(_('Ausf�hren'), 'batch_groups', array('data-dialog' => 'size=auto')) ?>
            </div>
        </footer>
    <?php endif ?>
</section>
