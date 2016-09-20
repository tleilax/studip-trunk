<?php
SkipLinks::addIndex(_('Mitarbeiterliste'), 'list_institute_members');    
?>

<? if ($institute): ?>
    <table class="default" id="list_institute_members">
        <caption><?= _('Mitarbeiterinnen und Mitarbeiter') ?></caption>
        <colgroup>
            <col width="32">
        <? foreach ($table_structure as $key => $field): ?>
            <? if ($key !== 'statusgruppe'): ?>
                <col <? if ($field['width']): ?> width="<?= $field['width'] ?>"<? endif; ?>>
            <? endif; ?>
        <? endforeach; ?>
        </colgroup>
        <thead>
            <tr>
            <? foreach ($table_structure as $key => $field): ?>
                <th <? if ($key === 'actions') echo 'class="actions"'; ?> <? if ($field['colspan']): ?>colspan="<?= $field['colspan'] ?>"<? endif; ?>>
                <? if ($field['link']): ?>
                    <a href="<?= URLHelper::getLink($field['link']) ?>">
                        <?= htmlReady($field['name']) ?>
                    </a>
                <? else: ?>
                    <?= htmlReady($field['name']) ?>
                <? endif; ?>
                </th>
            <? endforeach; ?>
            </tr>
        </thead>
        <?= $table_content ?>
    </table>
<? endif; ?>
