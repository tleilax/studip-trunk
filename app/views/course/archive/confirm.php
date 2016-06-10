<?= MessageBox::info(_('Sie sind im Begriff, die aufgelistete(n) Veranstaltung(en) zu archivieren. Dieser Schritt kann nicht rückgängig gemacht werden!')); ?>
<table class="default withdetails">
    <caption><?= $_SESSION['SessSemName']["header_line"] ?></caption>
    <thead>
        <tr>
            <th><?= _('Name der Veranstaltung'); ?></th>
            <th><?= _('Letzte Aktivität'); ?></th>
        </tr>
    </thead>
    <tbody>
        <? foreach ($courses as $course) : ?>
        <tr class="open">
            <td>
                <a onclick="jQuery(this).closest('tr').toggleClass('open'); return false;" href="">
                    <?= htmlReady($course->name); ?>
                </a>
            </td>
            <td>
                
            </td>
        </tr>
        <tr class="details nohover">
            <td style="display:none;">
                <div class="detailscontainer">
                    <dl class="default nohover">
                        <dt><?= _('Untertitel') . ':'; ?></dt>
                        <dd>
                            <?= $course->untertitel ? htmlReady($course->untertitel) : ' ' ?>
                        </dd>
                        <dt><?= _('Lehrende'); ?></dt>
                        <dd>
                            <ul>
                            <? foreach ($dozenten[$course->id] as $dozent) : ?>
                                <li>
                                    <a href="<?= $controller->url_for('profile?username=' . htmlReady($dozent->username)); ?>" >
                                    <?= htmlReady($dozent->vorname) . ' ' . htmlReady($dozent->nachname) ?>
                                    </a>
                                </li>
                            <? endforeach ?>
                            </ul>
                        </dd>
                        <dt><?= _('Veranstaltungsort') . ':'; ?></dt>
                        <dd><?= $course->ort ? htmlReady($course->ort) : ' ' ?></dd>
                        <dt><?= _('Semester') . ':'; ?></dt>
                        <dd><?= $course->start_semester->name ? htmlReady($course->start_semester->name) : ' ' ?></dd>
                        <dt><?= _('Veranstaltungsnummer') . ':'; ?></dt>
                        <dd><?= $course->veranstaltungsnummer ? htmlReady($course->veranstaltungsnummer) : ' ' ?></dd>
                    </dl>
                </div>
            </td>
        </tr>
        <? endforeach ?>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="4">
                <form class="default" action="<?= $controller->url_for('course/archive/archive'); ?>" method="post">
                    <? foreach ($courses as $course) : ?>
                        <input type="hidden" name="courseIds[]" value="<?= $course->id; ?>">
                    <? endforeach ?>
                    <?= \Studip\Button::create(_('Archivieren')); ?>
                </form>
            </td>
        </tr>
    </tfoot>
</table>

