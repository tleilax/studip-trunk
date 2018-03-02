<? $languages = Config::get()->CONTENT_LANGUAGES; ?>
<? $def_lang = reset(array_keys($languages)); ?>
<td colspan="5">
    <table class="default nohover">
        <colgroup>
            <col style="width: 20%">
            <col>
        </colgroup>
        <tbody>
            <tr>
                <td><strong><?= _('Name:') ?></strong></td>
                <td>
                    <? if (mb_strlen($dokument->name->original())) : ?>
                    <div>
                        <img style="display: block;" src="<?= Assets::image_path('languages/' . $languages[$def_lang]['picture']) ?>" alt="<?= $languages[$def_lang]['name'] ?>" title="<?= $languages[$def_lang]['name'] ?>">
                        <?= htmlReady($dokument->name->original()) ?>
                    </div>
                    <? endif; ?>
                    <? foreach ($dokument->name->toArray() as $lang => $name) : ?>
                    <div style="margin-top:10px;">
                        <img style="display: block;" src="<?= Assets::image_path('languages/' . $languages[$lang]['picture']) ?>" alt="<?= $languages[$lang]['name'] ?>" title="<?= $languages[$lang]['name'] ?>">
                        <?= htmlReady($name) ?>
                    </div>
                    <? endforeach; ?>
                </td>
            </tr>
            <tr>
                <td><strong><?= _('URL:') ?></strong></td>
                <td>
                    <?= formatLinks($dokument->url) ?>
                </td>
            </tr>
            <tr>
                <td><strong><?= _('Beschreibung:') ?></strong></td>
                <td>
                    <? if (!mb_strlen($dokument->beschreibung->original()) && !count($dokument->beschreibung->toArray())) : ?>
                        <?= _('Keine Beschreibung vorhanden.') ?>
                    <? else : ?>
                        <? if (mb_strlen($dokument->beschreibung->original())) : ?>
                        <div>
                            <img style="display: block;" src="<?= Assets::image_path('languages/' . $languages[$def_lang]['picture']) ?>" alt="<?= $languages[$def_lang]['name'] ?>" title="<?= $languages[$def_lang]['name'] ?>">
                            <?= formatReady($dokument->beschreibung->original()) ?>
                        </div>
                        <? endif; ?>
                        <? foreach ($dokument->beschreibung->toArray() as $lang => $beschreibung) : ?>
                        <div style="margin-top:10px;">
                            <img style="display: block;" src="<?= Assets::image_path('languages/' . $languages[$lang]['picture']) ?>" alt="<?= $languages[$lang]['name'] ?>" title="<?= $languages[$lang]['name'] ?>">
                            <?= formatReady($beschreibung) ?>
                        </div>
                    <? endforeach; ?>
                <? endif; ?>
                </td>
            </tr>
            <tr>
                <td colspan="2"><strong><?= _('Referenzierungen:') ?></strong><br>
                    <? if (!sizeof($relations)) : ?>
                        <?= _('Das Dokument wurde noch nicht referenziert.') ?>
                    <? else : ?>
                        <dl>
                            <? foreach ($relations as $object_type => $relation) : ?>
                            <dt>
                                <strong><?= htmlReady($object_type::getClassDisplayName()) ?></strong>
                            </dt>
                            <dd>
                                <ul>
                                <? foreach ($relation as $rel) : ?>
                                    <? $related_object = $object_type::getEnriched($rel['range_id']) ?>
                                    <li>
                                        <a href="<?= $this->controller->url_for('dispatch/index', mb_strtolower($object_type), $rel['range_id']) ?>">
                                            <?= $related_object->getDisplayName() ?>
                                        </a>
                                    </li>
                                <? endforeach; ?>
                                </ul>
                            </dd>
                            <? endforeach; ?>
                        </dl>
                    <? endif; ?>
                </td>
            </tr>
            <tr>
                <td><strong><?= _('Erstellt am:') ?></strong></td>
                <td>
                    <?= strftime('%x, %X', $dokument->mkdate) . ', ' ?>
                    <?= htmlReady(get_fullname($dokument->author_id)) ?>
                    <?= ' (' . htmlReady(get_username($dokument->author_id)) . ')' ?>
                </td>
            </tr>
            <? if ($dokument->mkdate != $dokument->chdate) : ?>
            <tr>
                <td><strong><?= _('Letzte Änderung am:') ?></strong></td>
                <td>
                    <?= strftime('%x, %X', $dokument->chdate) . ', ' ?>
                    <?= htmlReady(get_fullname($dokument->editor_id)) ?>
                    <?= ' (' . htmlReady(get_username($dokument->editor_id)) . ')' ?>
                </td>
            </tr>
            <? endif; ?>
        </tbody>
    </table>
</td>
