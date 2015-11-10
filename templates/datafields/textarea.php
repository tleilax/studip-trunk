<textarea name="<?= $name ?>[<?= $structure->getID() ?>]"
          id="<?= $name ?>_<?= $structure->getID() ?>"
          rows="6" cols="58"
          <? if ($structure->getIsRequired)) echo 'required'; ?>
><?= htmlReady($value) ?></textarea>