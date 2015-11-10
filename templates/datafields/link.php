<input type="url" name="<?= $name ?>[<?= $structure->getID() ?>]"
       value="<?= htmlReady($value) ?>" id="<?= $name ?>_<?= $structure->getID() ?>"
       size="30" placeholder="http://"
       <? if ($structure->getIsRequired()) echo 'required'; ?>>
