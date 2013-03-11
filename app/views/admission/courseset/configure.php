<?php
use Studip\Button, Studip\LinkButton;

//Infobox:
$info = array();
$info[] = array(
              "icon" => "icons/16/black/info.png",
              "text" => "Hier k�nnen Sie die Regeln, Eigenschaften und ".
                        "Zuordnungen des Anmeldesets bearbeiten.");
$info[] = array(
              "icon" => "icons/16/black/info.png",
              "text" => "Sie k�nnen das Anmeldeset allen Einrichtungen zuordnen, ".
                        "an denen Sie mindestens Dozentenrechte haben.");

$info[] = array(
              "icon" => "icons/16/black/info.png",
              "text" => "Alle Veranstaltungen der Einrichtungen, an denen Sie ".
                        "mindestens Dozentenrechte haben, k�nnen zum ".
                        "Anmeldeset hinzugef�gt werden.");

$infobox = array(
    array("kategorie" => _('Informationen:'),
          "eintrag" => $info
    )
);
$infobox = array('content' => $infobox,
                 'picture' => 'infobox/administration.png'
);

?>
<?= $this->render_partial('dialog/confirm_dialog') ?>
<h2><?= $courseset ? _('Anmeldeset bearbeiten') : _('Anmeldeset anlegen') ?></h2>
<form action="<?= $controller->url_for('admission/courseset/save', ($courseset ? $courseset->getId() : '')) ?>" method="post">
    <div style="width: 80%; padding: 10px;" class="table_row_<?= TextHelper::cycle('even', 'odd'); ?>">
        <div align="right" style="display: inline-block; vertical-align: top; width: 20%; font-weight: bold;"><?= _('Name des Anmeldesets:') ?></div>
        <div style="display: inline-block; vertical-align: top;">
            <input type="text" size="60" maxlength="255" name="name" value="<?= $courseset ? $courseset->getName() : '' ?>" required/>
        </div>
    </div>
    <div style="width: 80%; padding: 10px;" class="table_row_<?= TextHelper::cycle('even', 'odd'); ?>">
        <div align="right" style="display: inline-block; vertical-align: top; width: 20%; font-weight: bold;"><?= _('Einrichtungszuordnung:') ?></div>
        <div style="display: inline-block; vertical-align: top;">
        <?php foreach ($myInstitutes as $institute) { ?>
        <input type="checkbox" name="institutes[]" value="<?= $institute['Institut_id'] ?>"
            <?= $selectedInstitutes[$institute['Institut_id']] ? 'checked="checked"' : '' ?>
            class="institute" onclick="STUDIP.Admission.getCourses('institute', 'instcourses', 
            '<?= $controller->url_for('admission/courseset/instcourses') ?>')"/>
            <?= $institute['Name'] ?>
        <br/>
        <?php } ?>
        </div>
    </div>
    <div style="width: 80%; padding: 10px;" class="table_row_<?= TextHelper::cycle('even', 'odd'); ?>">
        <div align="right" style="display: inline-block; vertical-align: top; width: 20%; font-weight: bold;"><?= _('Anmelderegeln:') ?></div>
        <div style="display: inline-block; vertical-align: top;" id="rules">
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
                <?= Assets::img('icons/16/blue/plus.png', array(
                    'alt' => _('Anmelderegel hinzuf�gen'),
                    'title' => _('Anmelderegel hinzuf�gen'))) ?><?= _('Anmelderegel hinzuf�gen') ?></a>
        </div>
    </div>
    <div style="width: 80%; padding: 10px;" class="table_row_<?= TextHelper::cycle('even', 'odd'); ?>">
        <div align="right" style="display: inline-block; vertical-align: top; width: 20%; font-weight: bold;"></div>
        <div style="display: inline-block; vertical-align: top;">
            <?php
                // Set checkbox accordingly to courseset status or to unchecked if new courseset.
                $checked = $courseset ? ($courseset->getInvalidateRules() ? ' checked="checked"' : '') : '';
            ?>
            <input type="checkbox" name="invalidate"<?= $checked ?>/>
            <?= _('Anmeldebedingungen werden nach erfolgter Platzverteilung aufgehoben') ?>
        </div>
    </div>
    <div style="width: 80%; padding: 10px;" class="table_row_<?= TextHelper::cycle('even', 'odd'); ?>">
        <div align="right" style="display: inline-block; vertical-align: top; width: 20%; font-weight: bold;"><?= _('Veranstaltungszuordnung:') ?></div>
        <div style="display: inline-block; vertical-align: top;" id="instcourses">
            <?php
            $courseIds = $courseset ? $courseset->getCourses() : array();
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
    <div align="center" style="width: 80%; padding: 10px;" class="table_row_<?= TextHelper::cycle('even', 'odd'); ?>">
        <?= Button::createAccept(_('Speichern'), 'submit') ?>
        <?= LinkButton::createCancel(_('Abbrechen'), $controller->url_for('admission/courseset')) ?>
    </div>
</form>