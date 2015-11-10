<?
# Lifter010: TODO
use Studip\Button, Studip\LinkButton;

?>

<form action="<?= $controller->url_for('admin/datafields/edit/' . $item->getID()) ?>" method="post"
      class="default">
    <?= CSRFProtection::tokenTag() ?>

    <fieldset>
        <legend><?= _('Bearbeiten der Parameter') ?></legend>

        <label>
            <span class="required"><?= _('Name') ?></span>

            <input type="text" name="datafield_name" id="datafield_name"
                   required size="60" maxlength="254"
                   value="<?= htmlReady($item->getName()) ?>">
        </label>

        <label>
            <?= _('Feldtyp') ?>

            <select name="datafield_type" id="datafield_type">
            <? foreach (DataFieldEntry::getSupportedTypes() as $param): ?>
                <option <? if ($item->getType() === $param) echo 'selected'; ?>>
                     <?= htmlReady($param) ?>
                </option>
            <? endforeach; ?>
            </select>
        </label>

        <label>
        <? if ($item->getObjectType() === 'sem'): ?>
            <?= _('Veranstaltungskategorie') ?>:

            <select name="object_class[]" id="object_class">
                <option value="NULL"><?= _('alle') ?></option>
            <? foreach (SemClass::getClasses() as $key => $val): ?>
                <option value="<?= $key ?>" <? if ($item->getObjectClass() === $key) echo 'selected'; ?>>
                    <?= htmlReady($val['name']) ?>
                </option>
            <? endforeach; ?>
            </select>
        <? elseif ($item->getObjectType() === 'inst'): ?>
            <?= _('Einrichtungstyp') ?>:

            <select name="object_class[]" id="object_class">
                <option value="NULL"><?= _('alle') ?></option>
            <? foreach ($GLOBALS['INST_TYPE'] as $key => $val): ?>
                <option value="<?= $key ?>" <? if ($item->getObjectClass() == $key) echo 'selected'; ?>>
                    <?= htmlReady($val['name']) ?>
                </option>
            <? endforeach; ?>
            </select>
        <? else: ?>
            <?= _('Nutzerstatus') ?>:

            <select multiple size="<?= count($controller->user_status) ?>" name="object_class[]" id="object_class">
                <option value="NULL"><?= _('alle') ?></option>
            <? foreach ($controller->user_status as $key => $value): ?>
                <option value="<?= $value ?>" <? if ($item->getObjectClass() & DataFieldStructure::permMask($key)) echo 'selected'; ?>>
                    <?= $key ?>
                </option>
            <? endforeach; ?>
            </select>
        <? endif; ?>
        </label>

        <label>
            <?= _('benötigter Status') ?>

            <select name="edit_perms" id="edit_perms">
            <? foreach (array_keys($controller->user_status) as $key): ?>
                <option <? if ($item->getEditPerms() === $key) echo 'selected'; ?>>
                    <?= $key ?>
                </option>
            <? endforeach; ?>
            </select>
        </label>

        <label>
            <?= _('Sichtbarkeit') ?>

            <select name="visibility_perms" id="visibility_perms">
                <option value="all" <? if ($item->getViewPerms() == 'all') echo 'selected'; ?>>
                    <?= _('alle') ?>
                </option>
            <? foreach (array_keys($controller->user_status) as $key): ?>
                <option <? if ($item->getViewPerms() === $key) echo 'selected'; ?>>
                    <?= $key ?>
                </option>
            <? endforeach; ?>
            </select>
        </label>

        <label>
            <?= _('Position') ?>

            <input type="text" name="priority" id="priority"
                   maxlength="10" size="5"
                   value="<?= $item->getPriority() ?>">
           </td>
        </label>

    <? if ($item->getObjectType() === 'sem'): ?>
        <label>
            <?= _('Eintrag verpflichtend') ?>:

            <input type="checkbox" name="is_required" id="is_required" value="1"
                   <? if ($item->getIsRequired()) echo 'checked'; ?>>
        </label>

        <label>
            <?= _('Beschreibung') ?>:

            <textarea name="description" id="description"><?= htmlReady($item->getDescription()) ?></textarea>
        </label>
    <? endif; ?>
    
    <? if ($item->getObjectType() === 'user'): ?>
        <label>
            <?= _('Mögliche Bedingung für Anmelderegel') ?>:

            <input type="checkbox" name="is_userfilter" id="is_userfilter" value="1"
                   <? if ($item->getIsUserfilter()) echo 'checked'; ?>>
        </label>
    <? endif; ?>
    </fieldset>

    <footer data-dialog-button>
        <?= Button::createAccept(_('Übernehmen'), 'uebernehmen', array('title' => _('Änderungen übernehmen')))?>
        <?= LinkButton::createCancel(_('Abbrechen'), $controller->url_for('admin/datafields/index/'.$item->getType().'#'.$item->getType()), array('title' => _('Zurück zur Übersicht')))?>
    </footer>
</form>
