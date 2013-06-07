<?php
use Studip\Button, Studip\LinkButton;

//Infobox:
$info = array();
$info[] = array(
              "icon" => "icons/16/black/info.png",
              "text" => "Hier können Sie die Regeln, Eigenschaften und ".
                        "Zuordnungen des Anmeldesets bearbeiten.");
$info[] = array(
              "icon" => "icons/16/black/info.png",
              "text" => "Sie können das Anmeldeset allen Einrichtungen zuordnen, ".
                        "an denen Sie mindestens Dozentenrechte haben.");

$info[] = array(
              "icon" => "icons/16/black/info.png",
              "text" => "Alle Veranstaltungen der Einrichtungen, an denen Sie ".
                        "mindestens Dozentenrechte haben, können zum ".
                        "Anmeldeset hinzugefügt werden.");

$infobox = array(
    array("kategorie" => _('Informationen:'),
          "eintrag" => $info
    )
);
$infobox = array('content' => $infobox,
                 'picture' => 'infobox/administration.png'
);

// Load assigned course IDs.
$courseIds = $courseset ? $courseset->getCourses() : array();
// Load assigned user list IDs.
$userlistIds = $courseset ? $courseset->getUserlists() : array();
?>
<?= $this->render_partial('dialog/confirm_dialog') ?>
<h2><?= $courseset ? _('Anmeldeset bearbeiten') : _('Anmeldeset anlegen') ?></h2>
<form action="<?= $controller->url_for('admission/courseset/save', ($courseset ? $courseset->getId() : '')) ?>" method="post">
    <div class="table_row_<?= TextHelper::cycle('even', 'odd'); ?> admission_data">
        <div class="admission_label"><?= _('Name des Anmeldesets:') ?></div>
        <div class="admission_value">
            <input type="text" size="60" maxlength="255" name="name" value="<?= $courseset ? htmlReady($courseset->getName()) : '' ?>" required/>
        </div>
    </div>
    <div class="table_row_<?= TextHelper::cycle('even', 'odd'); ?> admission_data">
        <div class="admission_label"><?= _('Einrichtungszuordnung:') ?></div>
        <div class="admission_value">
            <?php if ($myInstitutes) { ?>
                <?php foreach ($myInstitutes as $institute) { ?>
                <input type="checkbox" name="institutes[]" value="<?= $institute['Institut_id'] ?>"
                    <?= $selectedInstitutes[$institute['Institut_id']] ? 'checked="checked"' : '' ?>
                    class="institute" onclick="STUDIP.Admission.getCourses('institute', 'instcourses', 
                    '<?= $controller->url_for('admission/courseset/instcourses', $courseset ? $courseset->getId() : '') ?>')"/>
                    <?= $institute['Name'] ?>
                <br/>
                <?php } ?>
            <?php } else { ?>
                <i><?=  _('Sie sind keiner Einrichtung zugeordnet.') ?></i>
            <?php } ?>
        </div>
    </div>
    <div class="table_row_<?= TextHelper::cycle('even', 'odd'); ?> admission_data">
        <div class="admission_label"><?= _('Anmelderegeln:') ?></div>
        <div class="admission_value" id="rules">
            <?php if ($courseset) { ?>
            <div id="rulelist">
                <?php foreach ($courseset->getAdmissionRules() as $rule) { ?>
                    <?= $this->render_partial('admission/rule/save', array('rule' => $rule)) ?>
                <?php } ?>
            </div>
            <?php } else { ?>
                <span id="norules">
                    <i><?= _('Sie haben noch keine Anmelderegeln festgelegt.') ?></i>
                </span>
                <br/>
            <?php } ?>
            <br/>
            <a href="<?= $controller->url_for('admission/rule/configure') ?>" onclick="return STUDIP.Admission.configureRule(null, '<?= $controller->url_for('admission/rule/configure') ?>');">
                <?= Assets::img('icons/16/blue/add.png', array(
                    'alt' => _('Anmelderegel hinzufügen'),
                    'title' => _('Anmelderegel hinzufügen'))) ?><?= _('Anmelderegel hinzufügen') ?></a>
        </div>
    </div>
    <div class="table_row_<?= TextHelper::cycle('even', 'odd'); ?> admission_data">
        <div class="admission_label"></div>
        <div class="admission_value">
            <?php
                // Set checkbox accordingly to courseset status or to unchecked if new courseset.
                $checked = $courseset ? ($courseset->getInvalidateRules() ? ' checked="checked"' : '') : '';
            ?>
            <input type="checkbox" name="invalidate"<?= $checked ?>/>
            <?= _('Anmeldebedingungen werden nach erfolgter Platzverteilung aufgehoben') ?>
        </div>
    </div>
    <div class="table_row_<?= TextHelper::cycle('even', 'odd'); ?> admission_data">
        <div class="admission_label"><?= _('Veranstaltungszuordnung:') ?></div>
        <div class="admission_value" id="instcourses">
            <?php
            foreach ($courses as $course) {
                $title = $course['Name'];
                if ($course['VeranstaltungsNummer']) {
                    $title = $course['VeranstaltungsNummer'].' | '.$title;
                }
                $checked = '';
                if (in_array($course['seminar_id'], $courseIds)) {
                    $checked = ' checked="checked"';
                }
            ?>
            <input type="checkbox" name="courses[]" value="<?= $course['seminar_id'] ?>"<?= $checked ?>/> <?= $title ?><br/>
            <?php } ?>
        </div>
    </div>
    <div class="table_row_<?= TextHelper::cycle('even', 'odd'); ?> admission_data">
        <div class="admission_label"><?= _('Nutzerlisten zuordnen:') ?></div>
        <div class="admission_value">
            <?php if ($myUserlists) { ?>
                <?php
                foreach ($myUserlists as $list) {
                    $checked = '';
                    if (in_array($list->getId(), $userlistIds)) {
                        $checked = ' checked="checked"';
                    }
                ?>
                <input type="checkbox" name="userlists[]" value="<?= $list->getId() ?>"<?= $checked ?>/> <?= $list->getName() ?><br/>
                <?php } ?>
            <?php } else { ?>
                <i><?=  _('Sie haben noch keine Nutzerlisten angelegt.') ?></i>
            <?php
            }
            // Keep lists that were assigned by other users.
            foreach ($userlistIds as $list) {
                if (!in_array($list, array_keys($myUserlists))) {
            ?>
            <input type="hidden" name="userlists[]" value="<?= $list ?>"/>
            <?php
                }
            }
            ?>
        </div>
    </div>
    <div class="table_row_<?= TextHelper::cycle('even', 'odd'); ?> admission_data">
        <div class="admission_label"><?= _('weitere Hinweise:') ?></div>
        <div class="admission_value">
            <textarea cols="60" rows="3" name="infotext"><?= $courseset ? htmlReady($courseset->getInfoText()) : '' ?></textarea>
        </div>
    </div>
    <div class="table_row_<?= TextHelper::cycle('even', 'odd'); ?> admission_buttons">
        <?= Button::createAccept(_('Speichern'), 'submit') ?>
        <?= LinkButton::createCancel(_('Abbrechen'), $controller->url_for('admission/courseset')) ?>
    </div>
</form>