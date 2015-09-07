<? if (!Request::isXhr()): ?>
    <section class="contentbox clearfix">
        <header>
            <h1>
                <?= _('Allgemeine Einstellungen') ?>
            </h1>
        </header>
        <?= $this->render_partial('course/timesrooms/editSemester.php') ?>
    </section>
<? else : ?>
    <?= $this->render_partial('course/timesrooms/_select_semester_range.php') ?>
<? endif ?>

<? if ($show['regular']) : ?>
    <!--Regelmäßige Termine-->
    <?= $this->render_partial('course/timesrooms/_regularEvents.php', array()) ?>
<? endif; ?>

<? if ($show['irregular']) : ?>
    <!--Unregelmäßige Termine-->
    <?= $this->render_partial('course/timesrooms/_irregularEvents', array()) ?>
<? endif; ?>

<? if ($show['roomRequest']) : ?>
    <!--Raumanfrage-->
    <?= $this->render_partial('course/timesrooms/_roomRequest.php', array()) ?>
<? endif; ?>