<td colspan="6">
    <table class="default nohover">
        <colgroup>
            <col style="width: 20%">
            <col>
        </colgroup>
        <tbody>
            <tr>
                <td><strong><?= _('Alternativtext:') ?></strong></td>
                <td>
                <? if (!mb_strlen($lvgruppe->alttext) && !mb_strlen($lvgruppe->alttext_en)) : ?>
                    <span class="mvv-no-entry">
                    <?= _('Kein Alternativtext vorhanden.') ?>
                    </span>
                <? else : ?>
                    <? if (mb_strlen($lvgruppe->alttext)) : ?>
                    <div>
                        <img style="display: block;" src="<?= Assets::image_path('languages/lang_de.gif') ?>" alt="<?= _('deutsch') ?>">
                        <?= formatReady($lvgruppe->alttext) ?>
                    </div>
                    <? endif; ?>
                    <? if ($lvgruppe->alttext_en) : ?>
                    <div style="margin-top:10px;">
                        <img style="display: block;" src="<?= Assets::image_path('languages/lang_en.gif') ?>" alt="<?= _('englisch') ?>">
                        <?= formatReady($lvgruppe->alttext_en) ?>
                    </div>
                    <? endif; ?>
                <? endif; ?>
                </td>
            </tr>
            <tr>
                <td colspan="2"><strong><?= _('Verwendet in Modulteilen:') ?></strong>
                    <?= $this->render_partial('lvgruppen/lvgruppen/trails_table_lvgruppe') ?>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <strong><?= _('Zugeordnete Lehrveranstaltungen:') ?></strong>
                <? if (count($courses) == 0) : ?>
                    <div class="mvv-no-entry">
                    <?= _('Dieser Lehrveranstaltungsgruppe wurde für das ausgewählte Semester keine Lehrveranstaltung zugeordnet.') ?>
                    </div>
                <? elseif ($sem_filter == 'all') : ?>
                    <span style="margin-left: 20%" onClick="jQuery('#mvv-lvgruppen-semester').find('.mvv-sem-hidden').slideToggle(); jQuery(this).find('span').toggle(); return false;">
                        <a href="<?= $controller->url_for('/details/' . $lvgruppe->id, array('all_sem' => 1)) ?>">
                            <span>
                                <?= Icon::create('arr_1up', 'clickable', ['style' => 'vertical-align:text-bottom;'])->asImg(); ?>
                                <?= _('Alle Semester anzeigen') ?>
                            </span>
                            <span style="display: none;">
                                <?= Icon::create('arr_1down', 'clickable', ['style' => 'vertical-align:text-bottom;'])->asImg(); ?>
                                <?= _('Nur aktuelle Semester anzeigen') ?>
                            </span>
                        </a>
                    </span>
                    <ul style="list-style-type:none;" id="mvv-lvgruppen-semester">
                        <? foreach ($display_semesters as $semester) : ?>
                            <? if ($courses[$semester->id]) : ?>
                            	<? $show_sem = ($semester->id == $current_sem->id || $semester->id == $next_sem->id || Request::get('all_sem', 0))  ?>
                                <li<?= (!$show_sem ? ' style="display:none;" class="mvv-sem-hidden"' : '') ?>>
                                    <strong><?= htmlReady($semester->name) ?></strong>
                                    <ul style="list-style-type:none;">
                                    <? foreach ($courses[$semester->id] as $course) : ?>
                                        <li>
                                            <a href="<?= URLHelper::getLink('dispatch.php/course/details', ['sem_id' => $course['seminar_id']]) ?>">
                                                <?= htmlReady(($course['VeranstaltungsNummer'] ? $course['VeranstaltungsNummer'] . ' - ' : '') . $course['Name']) ?>
                                            </a>
                                        </li>
                                    <? endforeach; ?>
                                    </ul>
                                </li>
                            <? endif; ?>
                        <? endforeach; ?>
                    </ul>
                <? else : ?>
                    <ul style="list-style-type:none;" id="mvv-lvgruppen-semester">
                        <? foreach ($display_semesters as $semester) : ?>
                            <? if ($courses[$semester->id]) : ?>
                                <li>
                                    <strong><?= htmlReady($semester->name) ?></strong>
                                    <ul style="list-style-type:none;">
                                    <? foreach ($courses[$semester->id] as $course) : ?>
                                        <li>
                                            <a href="<?= URLHelper::getLink('dispatch.php/course/details', ['sem_id' => $course['seminar_id']]) ?>">
                                                <?= htmlReady(($course['VeranstaltungsNummer'] ? $course['VeranstaltungsNummer'] . ' - ' : '') . $course['Name']) ?>
                                            </a>
                                        </li>
                                    <? endforeach; ?>
                                    </ul>
                                </li>
                            <? endif; ?>
                        <? endforeach; ?>
                    </ul>
                <? endif; ?>
                </td>
            </tr>
            <? $archived_courses = $lvgruppe->getArchivedCourses(); ?>
            <? if (count($archived_courses)) : ?>
            <tr>
                <td><strong><?= _('Zugeordnete archivierte Veranstaltungen:') ?></strong></td>
                <td>
                    <ul>
                    <? foreach ($archived_courses as $archived_course) : ?>
                        <li>
                            <a href="<?= URLHelper::getLink('archiv.php', array('dump_id' => $archived_course['seminar_id'])) ?>" target="_blank">
                            <?= htmlReady($archived_course['name'] . '(' . $archived_course['semester'] . ')') ?>
                            </a>
                        </li>
                    <? endforeach; ?>
                    </ul>
                </td>
            </tr>
            <? endif; ?>
            <tr>
                <td><strong><?= _('Erstellt am:') ?></strong></td>
                <td>
                    <?= strftime('%x, %X', $lvgruppe->mkdate) . ', ' ?>
                    <?= get_fullname($lvgruppe->author_id) ?>
                    <?= ' (' . get_username($lvgruppe->author_id) . ')' ?>
                </td>
            </tr>
            <? if ($lvgruppe->mkdate != $lvgruppe->chdate) : ?>
            <tr>
                <td><strong><?= _('Letzte Änderung am:') ?></strong></td>
                <td>
                    <?= strftime('%x, %X', $lvgruppe->chdate) . ', ' ?>
                    <?= get_fullname($lvgruppe->editor_id) ?>
                    <?= ' (' . get_username($lvgruppe->editor_id) . ')' ?>
                </td>
            </tr>
            <? endif; ?>
        </tbody>
    </table>
</td>
