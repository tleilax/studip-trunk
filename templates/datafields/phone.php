<div style="white-space: nowrap;">
    +<input type="tel" name="<?= $name ?>[<?= $structure->getID() ?>][]"
            value="<?= $values[0] ?>" maxlength="3" size="2"
            title="<?= _('Landesvorwahl ohne f�hrende Nullen') ?>"
            placeholder="49"
            <? if ($structure->getIsRequired()) echo 'required'; ?>>

    (0)<input type="tel" name="<?= $name ?>[<?= $structure->getID() ?>][]"
            value="<?= $values[1] ?>" maxlength="6" size="5"
            title="<?= _('Ortsvorwahl ohne f�hrende Null') ?>"
            placeholder="541"
            <? if ($structure->getIsRequired()) echo 'required'; ?>>

    / <input type="tel" name="<?= $name ?>[<?= $structure->getID() ?>][]"
             value="<?= $values[2] ?>" maxlength="10" size="9"
             title="<?= _('Rufnummer') ?>"
             placeholder="969-0000"
             <? if ($structure->getIsRequired()) echo 'required'; ?>>
</div>
