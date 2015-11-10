<div style="white-space: nowrap;">
    <input type="text" name="<?= $name ?>[<?= $structure->getID() ?>][]"
           value="<?= $values[0] ?>" title="<?= _('Stunden') ?>"
           maxlength="2" size="1"
           <? if ($structure->getIsRequired()) echo 'required'; ?>>
    :
    <input type="text" name="<?= $name ?>[<?= $structure->getID() ?>][]"
           value="<?= $values[1] ?>" title="<?= _('Minuten') ?>"
           maxlength="2" size="1"
           <? if ($structure->getIsRequired()) echo 'required'; ?>>
</div>
