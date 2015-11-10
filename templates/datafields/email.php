<input type="email" name="<?= $name ?>[<?= $structure->getID() ?>]"
       value="<?= htmlReady($value) ?>" id="<?= $name ?>_<?= $structure->getID() ?>"
       <? if ($structure->getIsRequired()) echo 'required'; ?>>
