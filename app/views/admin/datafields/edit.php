<?
# Lifter010: TODO
?>
<? if (isset($flash['success'])): ?>
    <?= MessageBox::info($flash['success']) ?>
<? endif; ?>
<? if (isset($flash['error'])): ?>
    <?= MessageBox::error($flash['error'], $flash['error_detail']) ?>
<? endif; ?>

<? if (!$via_ajax): ?>
<h3><?= _('Bearbeiten der Parameter') ?></h3>
<? endif; ?>

<form action="<?= $controller->url_for('admin/datafields/edit/'.$item->getID()) ?>" method=post>
    <?= CSRFProtection::tokenTag() ?>
    <table class="default">
        <tr class="<?= TextHelper::cycle('cycle_odd', 'cycle_even') ?>">
            <td>
                <label for="datafield_name"><?= _('Name') ?>:</label>
            </td>
            <td>
                <input type="text" name="datafield_name" id="datafield_name" size="60" maxlength="254" value="<?= htmlReady($item->getName()) ?>">
            </td>
        </tr>
        <tr class="<?= TextHelper::cycle('cycle_odd', 'cycle_even') ?>">
            <td>
                <label for="datafield_type"><?= _('Feldtyp') ?>:</label>
            </td>
            <td>
                <select name="datafield_type" id="datafield_type">
                <? foreach (DataFieldEntry::getSupportedTypes() as $param): ?>
                    <option value="<?= $param ?>" <?= $item->getType() == $param ? 'selected="selected"' : ""?>>
                        <?= htmlReady($param) ?>
                    </option>
                <? endforeach; ?>
                </select>
            </td>
        </tr>
        <tr class="<?= TextHelper::cycle('cycle_odd', 'cycle_even') ?>">
            <td>
                <label for="object_class">
                <? if ($item->getObjectType() == 'sem'): ?>
                    <?= _('Veranstaltungskategorie') ?>
                <? elseif ($item->getObjectType() == 'inst'): ?>
                    <?= _('Einrichtungstyp') ?>
                <? else: ?>
                    <?= _('Nutzerstatus') ?>
                <? endif; ?>
                </label>
            </td>
            <td>
                <? if ($item->getObjectType() == 'sem'): ?>
                <select name="object_class" id="object_class">
                    <option value="NULL">
                        <?= _('alle') ?>
                    </option>
                    <? foreach ($GLOBALS['SEM_CLASS'] as $key=>$val): ?>
                    <option <?= $item->getObjectClass() == $key ? 'selected="selected"' : '' ?> value="<?= $key ?>">
                        <?= htmlReady($val['name']) ?>
                    </option>
                    <? endforeach; ?>
                <? elseif ($item->getObjectType() == 'inst'): ?>
                <select name="object_class" id="object_class">
                    <option value="NULL">
                        <?= _('alle') ?>
                    </option>
                    <? foreach ($GLOBALS['INST_TYPE'] as $key=>$val): ?>
                    <option <?= $item->getObjectClass() == $key ? "selected" : ""?> value="<?= $key ?>">
                        <?= htmlReady($val['name']) ?>
                    </option>
                    <? endforeach; ?>
                <? else: ?>
                <select multiple size="7" name="object_class[]" id="object_class">
                    <option value="NULL">
                        <?= _('alle') ?>
                    </option>
                    <? foreach ($controller->user_status as $key => $value): ?>
                    <option <?= ($item->getObjectClass() & DataFieldStructure::permMask($key)) ? 'selected="selected"' : '' ?> value="<?= $value ?>"><?= $key ?></option>
                    <? endforeach; ?>
                <? endif; ?>
                </select>
            </td>
        </tr>
        <tr class="<?= TextHelper::cycle('cycle_odd', 'cycle_even') ?>">
            <td>
                <label for="edit_perms"><?= _('ben�tigter Status') ?></label>
            </td>
            <td>
                <select name="edit_perms" id="edit_perms">
                <? foreach (array_keys($controller->user_status) as $key): ?>
                    <option <?= ($item->getEditPerms() == $key) ? 'selected="selected"' : '' ?>><?= $key ?></option>
                <? endforeach; ?>
                </select>
            </td>
        </tr>
        <tr class="<?= TextHelper::cycle('cycle_odd', 'cycle_even') ?>">
            <td>
                <label for="visibility_perms"><?= _('Sichtbarkeit') ?>:</label>
            </td>
            <td>
                <select name="visibility_perms" id="visibility_perms">
                <? foreach (array_keys($controller->user_status) as $key): ?>
                    <option <?= ($item->getViewPerms() == $key) ? 'selected="selected"' : '' ?>><?= $key ?></option>
                <? endforeach; ?>
                </select>
            </td>
        </tr>
        <tr class="<?= TextHelper::cycle('cycle_odd', 'cycle_even') ?>">
            <td>
                <label for="priority"><?= _('Reihenfolge') ?>:</label>
            </td>
            <td>
                <input type="text" name="priority" id="priority" value="<?= $item->getPriority() ?>" maxlength="10" size="5" />
           </td>
        </tr>
        <tr class="steel2">
            <td>&nbsp;</td>
            <td>
                <?= makeButton('uebernehmen2', 'input', _('�nderungen �bernehmen'), 'uebernehmen') ?>
                <a class="cancel" href="<?= $controller->url_for('admin/datafields/index/'.$item->getType().'#'.$item->getType()) ?>">
                    <?= makebutton('abbrechen', 'img', _('Zur�ck zur �bersicht')) ?>
                </a>
            </td>
        </tr>
    </table>
</form>

<? //infobox
$infobox = array(
    'picture' => 'infobox/administration.png',
    'content' => array(
        array(
            'kategorie' => _('Aktionen:'),
            'eintrag'   => array(
                array(
                    'icon' => 'icons/16/black/arr_2right.png',
                    'text' => $this->render_partial('admin/datafields/class_filter', compact('allclasses', 'class_filter'))
                ),
                array(
                    'text' => '<a href="'.$controller->url_for('admin/datafields/new/'.$class_filter).'">'._('Neues Datenfeld anlegen').'</a>',
                    'icon' => 'icons/16/black/plus.png',
                )
            )
        ),
        array(
            'kategorie' => _("Information"),
            'eintrag'   => array(
                array(
                   "text" => _("Hier haben Sie die M�glichkeit, ein neues Datenfeld im gew�hlten Bereich anzulegen."),
                   "icon" => "icons/16/black/info.png"
                )
            )
        )
    )
);
