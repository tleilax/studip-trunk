<?
# Lifter010: TODO
?>
<? if (isset($flash['delete'])): ?>
    <?= createQuestion(sprintf(_('Wollen Sie das Datenfeld "%s" wirklich löschen? Bedenken Sie bitte, dass noch Einträge dazu existieren können'), $flash['delete']['name']),
                       array('delete' => 1),
                       array('back' => 1),
                       $controller->url_for('admin/datafields/delete'.'/' . $flash['delete']['datafield_id'])); ?>
<? endif; ?>

<!-- Alle Datenfelder  -->
<table class="collapsable default">
    <colgroup>
        <col>
        <col width="15%">
        <col width="8%">
        <col width="9%">
        <col width="9%">
        <col width="9%">
        <col width="6%">
        <col width="6%">
        <col width="6%">
        <col width="5%">
        <col width="1%">
        <col width="48">
    </colgroup>
    <caption>
        <?= _('Verwaltung von generischen Datenfeldern') ?>
    </caption>
    <thead style="vertical-align: bottom">
        <tr>
            <th rowspan="2"><?= _('Name') ?></th>
            <th rowspan="2"><?= _('Feldtyp') ?></th>
            <th rowspan="2" style="word-wrap: nowrap">
                <?= _('Typ') ?>
                <?= tooltipIcon(_('Veranstaltungskategorie, Einrichtungstyp bzw. Nutzerstatus')) ?>
            </th>
            <th colspan="3" style="text-align: center">
                <?= _('benötigter Status') ?>
            </th>
            <th rowspan="2"><?= _('Pflichtfeld') ?></th>
            <th rowspan="2"><?= _('Beschreibung') ?></th>
            <th rowspan="2"><?= _('Anmelderegel') ?></th>
            <th rowspan="2"><?= _('Position') ?></th>
            <th rowspan="2">
                <abbr title="<?= _('Einträge') ?>">
                    #
                </abbr>
            </th>
            <th rowspan="2" class="actions"></th>
        </tr>
        <tr>
            <th style="word-wrap: nowrap">
                <?= _('Änderbar') ?>
                <?= tooltipIcon(_('Gibt den Status an, ab dem das Datenfeld änderbar ist')) ?>
            </th>
            <th style="word-wrap: nowrap">
                <?= _('Öffentlich') ?>
                <?= tooltipIcon(_('Gibt den Status an, ab dem das Datenfeld für andere sichtbar ist')) ?>
            </th>
            <th style="word-wrap: nowrap">
                <?= _('Sichtbar') ?>
                <?= tooltipIcon(_('Gibt den Status an, ab dem das Datenfeld für sich selbst sichtbar ist')) ?>
            </th>
        </tr>
    </thead>
<? foreach ($datafields_list as $key => $data): ?>
    <tbody class="<? if ($current_class !== $key && !$class_filter) echo 'collapsed'; ?> <? if (empty($datafields_list[$key])) echo 'empty'; ?>">
        <tr class="table_header header-row">
            <th class="toggle-indicator" colspan="12">
            <? if (empty($datafields_list[$key])): ?>
                <?= sprintf(_('Datenfelder für %s'), $allclasses[$key]) ?>
            <? else: ?>
                <a name="<?= $key ?>" class="toggler" href="<?= $controller->url_for('admin/datafields/index/' . $key) ?>">
                    <?= sprintf(_('Datenfelder für %s'), $allclasses[$key]) ?>
                </a>
            <? endif; ?>
            </th>
        </tr>
    <? foreach ($data as $input => $val): ?>
        <tr>
            <td>
                <a name="item_<?= $val->getID() ?>"></a>
                <?= htmlReady($val->getName()) ?>
            </td>
            <td>
            <? if (in_array($val->getType(), words('selectbox selectboxmultiple radio combo'))): ?>
                <a data-dialog="size=auto" href="<?= $controller->url_for('admin/datafields/config/'. $val->getID()) ?>">
                    <?= Assets::img('icons/16/blue/edit.png', array('class'=> 'text-top', 'title' => 'Einträge bearbeiten')) ?>
                </a>
            <? endif; ?>
                 <span><?= htmlReady($val->getType()) ?></span>
            </td>
            <td>
            <? if ($key === 'sem'): ?>
                <?= $val->getObjectClass() !== null ? htmlReady($GLOBALS['SEM_CLASS'][$val->getObjectClass()]['name']) : _('alle')?>
            <? elseif ($key == 'inst'): ?>
                <?=  $val->getObjectClass() !== null ? htmlReady($GLOBALS['INST_TYPE'][$val->getObjectClass()]['name']) : _('alle')?>
            <? else: ?>
                <?= $val->getObjectClass() !== null ? DataFieldStructure::getReadableUserClass($val->getObjectClass()) : _('alle')?>
            <? endif; ?>
            </td>
            <td><?= $val->getEditPerms() ?></td>
            <td><?= $val->getViewPerms() ?></td>
            <td>???</td>
            <td>
            <? if ($key === 'sem'): ?>
                <? if ($val->getIsRequired()): ?>
                    <?= Assets::img('icons/grey/checkbox-checked.svg', tooltip2(_('Ja'))) ?>
                <? else: ?>
                    <?= Assets::img('icons/grey/checkbox-unchecked.svg', tooltip2(_('Nein'))) ?>
                <? endif; ?>
            <? endif; ?>
            </td>
            <td>
            <? if ($key === 'sem'): ?>
                <? if (trim($val->getDescription())): ?>
                    <?= Assets::img('icons/grey/checkbox-checked.svg', tooltip2(_('Ja'))) ?>
                <? else: ?>
                    <?= Assets::img('icons/grey/checkbox-unchecked.svg', tooltip2(_('Nein'))) ?>
                <? endif; ?>
            <? endif; ?>
            </td>
            <td>
            <? if ($key === 'user'): ?>
                <? if ($val->getIsUserFilter()): ?>
                    <?= Assets::img('icons/grey/checkbox-checked.svg', tooltip2(_('Ja'))) ?>
                <? else: ?>
                    <?= Assets::img('icons/grey/checkbox-unchecked.svg', tooltip2(_('Nein'))) ?>
                <? endif; ?>
            <? endif; ?>
            </td>
            <td><?= $val->getPriority() ?></td>
            <td><?= $val->getCachedNumEntries() ?></td>
            <td class="actions">
                <a href="<?=$controller->url_for('admin/datafields/edit/'.$val->getID())?>" data-dialog>
                    <?= Assets::img('icons/16/blue/edit.png', array('title' => 'Datenfeld ändern')) ?>
                </a>
                <a href="<?=$controller->url_for('admin/datafields/delete/'.$val->getID())?>">
                    <?= Assets::img('icons/16/blue/trash.png', array('title' => 'Datenfeld löschen')) ?>
                </a>
            </td>
        </tr>
    <? endforeach; ?>
    </tbody>
<? endforeach; ?>
</table>
