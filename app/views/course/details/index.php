<style>
    /* This should be done by an own class (maybe not table? maybe dd?) */
    #tablefix {
        padding: 0;
    }
    #tablefix > header {
        margin: 0px;
    }
    #tablefix table {
        margin-bottom: 0;
        border-bottom: 0;
    }
    #tablefix table tbody tr:last-child td {
        border-bottom: 0;
    }
</style>
    <article class="studip" id="tablefix">
        <header>
            <h1><?= _('Allgemeine Informationen') ?></h1>
        </header>
        <table class="default">
            <colgroup>
                <col width="40%">
            </colgroup>
            <tbody>
            <? if ($course->veranstaltungsnummer) : ?>
                <tr>
                    <td><strong><?= _('Untertitel') ?></strong></td>
                    <td><?= htmlReady($course->untertitel) ?></td>
                </tr>
            <? endif ?>            
            <? if ($course->veranstaltungsnummer) : ?>
                <tr>
                    <td><strong><?= _('Veranstaltungsnummer') ?></strong></td>
                    <td><?= htmlReady($course->veranstaltungsnummer) ?></td>
                </tr>
            <? endif ?>
            <? if ($course->start_semester): ?>
                <tr>
                    <td>
                        <strong><?= _('Semester') ?></strong>
                    </td>
                    <td>
                        <?= htmlReady($course->getFullname('sem-duration-name')) ?>
                    </td>
                </tr>
            <? endif ?>
                <tr>
                    <td>
                        <strong><?= _('Aktuelle Anzahl der Teilnehmenden') ?></strong>
                    </td>
                    <td><?= $course->getNumParticipants() ?></td>
                </tr>
            <? if ($course->admission_turnout) : ?>
                <tr>
                    <td>
                        <strong>
                        <? if ($sem->isAdmissionEnabled()) : ?>
                            <?= _('maximale Teilnehmeranzahl') ?>
                        <? else : ?>
                            <?= _('erwartete Teilnehmeranzahl') ?>
                        <? endif ?>
                        </strong>
                    </td>
                    <td><?= htmlReady($course->admission_turnout) ?></td>
                </tr>
            <? endif ?>
            <? if ($sem->isAdmissionEnabled() && $course->getNumWaiting()) : ?>
                <tr>
                    <td>
                        <strong><?= _('Wartelisteneintr�ge') ?></strong>
                    </td>
                    <td><?= $course->getNumWaiting() ?></td>
                </tr>
            <? endif ?>
                <tr>
                    <td><strong><?= _("Heimat-Einrichtung") ?></strong></td>
                    <td>
                        <a href="<?= URLHelper::getScriptLink("dispatch.php/institute/overview", array('auswahl' => $course->institut_id)) ?>">
                            <?= htmlReady($course->home_institut->name) ?>
                        </a>
                    </td>
                </tr>
            <? if ($course->institutes->count() > 1): ?>
                <tr>
                    <td>
                        <strong><?= _('beteiligte Einrichtungen') ?></strong>
                    </td>
                    <td>
                        <?= join(', ', $course->institutes
                                        ->findBy('institut_id', $course->institut_id, '<>')
                                        ->orderBy('name')
                                        ->map(function($i) {
                                            return sprintf('<a href="%s">%s</a>', URLHelper::getScriptLink("dispatch.php/institute/overview", array('auswahl' => $i->id))
                                                                                , htmlReady($i->name));
                                })
                        ) ?>
                    </td>
                </tr>
            <? endif ?>
            <tr>
                <td><strong><?= _("Veranstaltungstyp") ?></strong></td>
                <td>
                    <?= sprintf(_("%s in der Kategorie %s"), $course->getSemType()->offsetGet('name'), $course->getSemClass()->offsetGet('name')) ?>
                </td>
            </tr>

            <? if ($prelim_discussion) : ?>
                <tr>
                    <td><strong><?= _('Vorbesprechung') ?></strong></td>
                    <td><?= $prelim_discussion ?></td>
                </tr>
            <? endif ?>
            <? $next_date = $sem->getNextDate() ?>
            <? if ($next_date) : ?>
                <tr>
                    <td><strong><?= _('N�chster Termin') ?></strong></td>
                    <td><?= $next_date ?></td>
                </tr>
            <? else : ?>
                <? $firstTerm = $sem->getFirstDate() ?>
                <? if ($firstTerm) : ?>
                    <tr>
                        <td><strong><?= _('Erster Termin') ?></strong></td>
                        <td><?= $firstTerm ?></td>
                    </tr>
                <? endif ?>
            <? endif ?>
            <? if ($course->art) : ?>
                <tr>
                    <td><strong><?= _("Art/Form") ?></strong></td>
                    <td><?= htmlReady($course->art) ?></td>
                </tr>
            <? endif ?>
            <? if ($course->teilnehmer != "") : ?>
                <tr>
                    <td><strong><?= _("Teilnehmende") ?></strong></td>
                    <td>
                        <?= htmlReady($course->teilnehmer, true, true) ?>
                    </td>
                </tr>
            <? endif ?>
            <? if ($course->vorrausetzungen != "") : ?>
                <tr>
                    <td><strong><?= _("Voraussetzungen") ?></strong></td>
                    <td>
                        <?= htmlReady($course->vorrausetzungen, true, true) ?>
                    </td>
                </tr>
            <? endif ?>
            <? if ($course->lernorga != "") : ?>
                <tr>
                    <td><strong><?= _("Lernorganisation") ?></strong></td>
                    <td>
                        <?= htmlReady($course->lernorga, true, true) ?>
                    </td>
                </tr>
            <? endif ?>
            <? if ($course->leistungsnachweis != "") : ?>
                <tr>
                    <td><strong><?= _("Leistungsnachweis") ?></strong></td>
                    <td>
                        <?= htmlReady($course->leistungsnachweis, true, true) ?>
                    </td>
                </tr>
            <? endif ?>
            <? foreach ($course->datafields->getTypedDatafield() as $entry) : ?>
                <? if ($entry->isVisible() && $entry->getValue()) : ?>
                    <tr>
                        <td><strong><?= htmlReady($entry->getName()) ?></strong></td>
                        <td>
                            <?= $entry->getDisplayValue() ?>
                        </td>
                    </tr>
                <? endif ?>
            <? endforeach ?>
            <? if ($course->sonstiges != "") : ?>
                <tr>
                    <td><strong><?= _("Sonstiges") ?></strong></td>
                    <td>
                        <?= formatLinks($course->sonstiges) ?>
                    </td>
                </tr>
            <? endif ?>
            <? if ($course->ects) : ?>
                <tr>
                    <td><strong><?= _("ECTS-Punkte") ?></strong></td>
                    <td>
                        <?= htmlReady($course->ects, true, true) ?>
                    </td>
                </tr>
            <? endif ?>
            </tbody>
        </table>
    </article>

    <? $lecturers = $course->getMembersWithStatus('dozent'); ?>
    <? $count_lecturers = count($lecturers); ?>
    <? if ($count_lecturers) : ?>
        <article class="studip">
            <header>
                <h1><?= get_title_for_status('dozent', $count_lecturers, $course->status) ?></h1>
            </header>
            <section>
                <ul class="list-csv">
                <? foreach ($lecturers as $lecturer) : ?>
                    <li>
                        <a href="<?= URLHelper::getScriptLink('dispatch.php/profile', array('username' => $lecturer['username'])) ?>">
                            <?= htmlReady($lecturer->getUserFullname() . ($lecturer->label ? " (" . $lecturer->label . ")" : "")) ?>
                        </a>
                    </li>
                <? endforeach ?>
                </ul>
             </section>
        </article>
    <? endif ?>

    <? $tutors = $course->getMembersWithStatus('tutor'); ?>
    <? $count_tutors = count($tutors); ?>
    <? if ($count_tutors) : ?>
        <article class="studip">
            <header>
                <h1><?= get_title_for_status('tutor', $count_tutors, $course->status) ?></h1>
            </header>
            <section>
                <ul class="list-csv">
                <? foreach ($tutors as $tutor) : ?>
                    <li>
                        <a href="<?= URLHelper::getScriptLink('dispatch.php/profile', array('username' => $tutor['username'])) ?>">
                            <?= htmlReady($tutor->getUserFullname() . ($tutor->label ? " (" . $tutor->label . ")" : "")) ?>
                        </a>
                    </li>
                <? endforeach ?>
                </ul>
             </section>
        </article>
    <? endif ?>

    <article class="studip">
        <header>
            <h1><?= _('Zeiten') ?></h1>
        </header>
        <section>
            <?= $sem->getDatesHTML() ?>
        </section>
    </article>

    <? if ($course['public_topics'] && count($course->topics)) : ?>
        <article class="studip">
            <header>
                <h1><?= _("Themen") ?></h1>
            </header>
            <section>
                <? foreach ($course->topics as $key => $topic) {
                    if ($key > 0) {
                        echo ", ";
                    }
                    echo " ".htmlReady($topic['title']);
                } ?>
            </section>
        </article>
    <? endif ?>

    <article class="studip">
        <header>
            <h1><?= _('Veranstaltungsort') ?></h1>
        </header>
        <section>
            <?= $sem->getDatesTemplate('dates/seminar_html_location', array('ort' => $course->ort)) ?>
        </section>
    </article>
    <? if ($this->studymodules) : ?>

        <article class="studip">
            <header>
                <h1><?= _('Studienmodule') ?></h1>
            </header>
            <section>
                <ul class="list-unstyled">
                    <? foreach ($this->studymodules as $module) : ?>
                        <li>
                            <a class="module-info" href="<?= URLHelper::getLink($module['nav']->getUrl())?>">
                                <?= htmlReady($module['title']) ?>
                                <? if ($module['nav']->getImage()) : ?>
                                    <?= $module['nav']->getImage()->asImg($module['nav']->getLinkAttributes()) ?>
                                <? endif ?>
                                <span><?= htmlReady($module['nav']->getTitle())?></span>
                            </a>
                        </li>
                    <? endforeach ?>
                </ul>
            </section>
        </article>
    <? endif ?>

    <? if ($studyAreaTree) : ?>
        <article class="studip">
            <section>
                <ul class="collapsable css-tree">
                    <?= $this->render_partial('study_area/tree.php', array('node' => $studyAreaTree, 'open' => true, 'dont_open' => Config::get()->COURSE_SEM_TREE_CLOSED_LEVELS)) ?>
                </ul>
            </section>
        </article>
    <? endif ?>

    <? if ($study_areas && count($study_areas) > 0) : ?>
        <article class="studip">
            <section>
                <ul class="list-unstyled">
                    <? foreach ($study_areas as $area) : ?>
                        <li>
                            <a href="<?=URLHelper::getScriptLink('show_bereich.php?level=sbb&id=' . $area->id)?>">
                                <?= htmlReady($area->getPath(' > ')) ?>
                            </a>
                        </li>
                    <? endforeach ?>
                </ul>
            </section>
        </article>
    <? endif ?>

    <?
    // Ausgabe der Modulzuordnung MVV
    if ($mvv_pathes) : ?>
        <section class="contentbox">
            <header>
                <h1><?= _('Modulzuordnungen') ?></h1>
            </header>
            <section>
                <ul class="list-unstyled">
                    <? foreach ($mvv_pathes as $mvv_path) : ?>
                    <li>
                        <a data-dialog href="<?= URLHelper::getScriptLink('plugins.php/mvvplugin/search/module/overview/' . reset(array_keys($mvv_path)) . '/', array('sem_select' => $mvv_end_semester_id)) ?>">
                            <?= htmlReady(implode(' > ', reset(array_values($mvv_path)))) ?>
                        </a>
                    </li>
                    <? endforeach; ?>
                </ul>
            </section>
        </section>
    <? endif; ?>

    <? if ($course->beschreibung) : ?>
        <article class="studip">
            <header>
                <h1><?= _("Kommentar/Beschreibung") ?></h1>
            </header>
            <section>
                <?= formatLinks($course->beschreibung) ?>
            </section>
        </article>
    <? endif ?>

    <? if ($courseset = $sem->getCourseSet()) : ?>
        <article class="studip">
            <header>
                <h1><?=_("Anmelderegeln")?></h1>
            </header>
            <section>
                <div>
                    <?= sprintf(_('Diese Veranstaltung geh�rt zum Anmeldeset "%s".'), htmlReady($courseset->getName())) ?>
                </div>
                <div id="courseset_<?= $courseset->getId() ?>">
                    <?= $courseset->toString(true) ?>
                </div>
            </section>
        </article>
    <? endif ?>

    <? if ($course->admission_prelim == 1 || $course->admission_binding == 1) : ?>
        <article class="studip">
            <header>
                <h1><?= _('Anmeldemodus') ?></h1>
            </header>
            <? if ($course->admission_prelim == 1) : ?>
                <section>
                    <p><?= _("Die Auswahl der Teilnehmenden wird nach der Eintragung manuell vorgenommen.") ?></p>
                    <? if ($course->admission_prelim_txt) : ?>
                        <p><?= formatReady($course->admission_prelim_txt) ?></p>
                    <? else : ?>
                            <p><?=
                                _("Nutzer/-innen, die sich f�r diese Veranstaltung eintragen m�chten,
                        erhalten n�here Hinweise und k�nnen sich dann noch gegen eine Teilnahme entscheiden.")?>
                            </p>
                    <? endif ?>
                </section>
            <? endif ?>
            <? if ($course->admission_binding == 1) : ?>
                <section>
                    <p><?= _("Die Anmeldung ist verbindlich, Teilnehmende k�nnen sich nicht selbst austragen.") ?></p>
                </section>
            <? endif ?>
        </article>
    <? endif ?>

    <? if (!empty($course_domains)): ?>
        <article class="studip">
            <header>
                <h1><?= _("Zugelassenene Nutzerdom�nen:") ?></h1>
            </header>
            <ul>
                <? foreach ($course_domains as $domain): ?>
                    <li><?= htmlReady($domain->getName()) ?></li>
                <? endforeach ?>
            </ul>
        </article>
    <? endif ?>


<? if (Request::get('from')) : ?>
    <footer data-dialog-button>
        <?= \Studip\LinkButton::createCancel(_('Zur�ck'), URLHelper::getURL(Request::get('from')))?>
    </footer>
<? endif ?>
