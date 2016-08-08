<h1><?= htmlReady($version->getDisplayName()) ?></h1>
<table class="default nohover" data-mvv-id="<?= $version->getId(); ?>" data-mvv-type="stgteilversion">
    <tbody>
    <? if (!empty($version->start_sem)) : ?>
        <tr>
            <td colspan="2"><strong><?= _('G�ltigkeit') ?></strong>
                <div style="padding: 10px; float: left;" data-mvv-field="mvv_stgteilversion.start_sem">
                    <?= _('von Semester:') ?>
                    <? $sem = Semester::find($version->start_sem) ?>
                    <?= htmlReady($sem->name) ?>
                </div>
                <div style="padding: 10px;" data-mvv-field="mvv_stgteilversion.end_sem">
                    <?= _('bis Semester:') ?>
                    <? if ($version->end_sem != "") : ?>
                        <? $sem = Semester::find($version->end_sem) ?>
                        <?= htmlReady($sem->name) ?>
                    <? else : ?>
                        <?= _('unbegrenzt g�ltig') ?>
                    <? endif; ?>
                </div>
                <div style="padding: 10px; display:inline-block;" data-mvv-field="mvv_stgteilversion.beschlussdatum">
                    <?= _('Beschlussdatum:') ?>
                    <?= ($version->beschlussdatum ? strftime('%d.%m.%Y', $version->beschlussdatum) : '') ?>
                </div>
                <div style="padding: 10px; display:inline-block;" data-mvv-field="mvv_stgteilversion.fassung_nr">
                    <?= _('Fassung:') ?>
                    <?= htmlReady($version->fassung_nr) ?>
                </div>
                <div style="padding: 10px; display:inline-block;" data-mvv-field="mvv_stgteilversion.fassung_typ">
                    <?= $GLOBALS['MVV_STGTEILVERSION']['FASSUNG_TYP'][$version->fassung_typ]['name'] ?>
                </div>
            </td>
        </tr>
    <? endif; ?>
    <? if (!empty($version->code)) : ?>
        <tr>
            <td><strong><?= _('Code') ?></strong></td>
            <td data-mvv-field="mvv_stgteilversion.code">
                <?= htmlReady($version->code) ?>
            </td>
        </tr>
    <? endif; ?>
    <? if (!empty($version->beschreibung)) : ?>
        <tr>
            <td><strong><?= _('Beschreibung') ?></strong></td>
            <td>
                <div data-mvv-field="mvv_stgteilversion.beschreibung">
                    <img src="<?= Assets::image_path('languages/lang_de.gif') ?>" alt="<?= _('deutsch') ?>" style="vertical-align: top;">
                    <div><?= htmlReady($version->beschreibung) ?></div>
                </div>
                <div data-mvv-field="mvv_stgteilversion.beschreibung_en">
                    <img src="<?= Assets::image_path('languages/lang_en.gif') ?>" alt="<?= _('englisch') ?>" style="vertical-align: top;">
                    <div><?= htmlReady($version->beschreibung_en) ?></div>
                </div>
            </td>
        </tr>
    <? endif; ?>
    <? if (!empty($version->status)) : ?>
        <tr>
            <td><strong><?= _('Status der Bearbeitung') ?></strong></td>
            <td data-mvv-field="mvv_stgteilversion.stat">
                <?= $GLOBALS['MVV_STGTEILVERSION']['STATUS']['values'][$version->stat]['name'] ?>
            </td>
        </tr>
        <tr>
            <td><strong><?= _('Kommentar Bearbeitungsstatus:') ?></strong></td>
            <td data-mvv-field="mvv_stgteilversion.kommentar_status">
                <?= htmlReady($version->kommentar_status) ?>
            </td>
        </tr>
    <? endif; ?>
    </tbody>
</table>