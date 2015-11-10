<input type="hidden" name="<?= $name ?>[<?= $structure->getID() ?>]" value="0">
<input type="checkbox" name="<?= $name ?>[<?= $structure->getID()?>]"
       value="1" id="<?= $name ?>_<?= $structure->getID() ?>"
       <? if ($value) echo 'checked'; ?>
       <? if ($structure->getIsRequired()) echo 'required'; ?>>