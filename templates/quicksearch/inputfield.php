<? if ($withButton) : ?>
<div style="width: <?= $box_width ?>px; background-color: #ffffff; border: 1px #999999 solid; display:inline-block">
<? $input_style = " style=\"width: ".($box_width-23)."px; background-color:#ffffff; border: 0px;\""; ?>
    <? if ($box_align === "left") : ?>
            <input class="text-bottom" type="image" src="<?= Assets::image_path("icons/16/blue/search.png")?>">
    <? endif ?>
<? endif ?>
<? if ($inputStyle) {
           $input_style = " style=\"".$inputStyle."\"";
        }
        if ($beschriftung) {
            $clear_input = " onFocus=\"if (this.value == '$beschriftung'){this.value = ''; jQuery(this).css('opacity', '1');}\" " .
                "onBlur=\"if (this.value == ''){this.value = '$beschriftung';jQuery(this).css('opacity', '0.7');}\"";
        } ?>
            <input type=hidden id="<?= $id ?>_realvalue" name="<?= $name ?>" value="<?= $defaultID ?>">
            <input<?= $input_style.($inputClass ? " class=\"".$inputClass."\"" : "")
                ?> id="<?= $id ?>"<?= ($clear_input ? $clear_input : "") ?> type=text name="<?=
                    $name ?>_parameter" value="<?= $defaultName ?>">
<? if ($withButton) : ?>
    <? if ($box_align !== "left") : ?>
            <input class="text-bottom" type="image" src="<?= Assets::image_path("icons/16/blue/search.png")?>">
    <? endif ?>
        </div>
<? endif ?>
        <script type="text/javascript" language="javascript">
            //Die Autovervollst�ndigen-Funktion aktivieren:
            STUDIP.QuickSearch.autocomplete("<?= $id ?>",
                "<?= URLHelper::getURL("dispatch.php/quicksearch/response/".$query_id) ?>",
                <?= $jsfunction ? htmlReady($jsfunction) : "null" ?>,
                <? if ($beschriftung && !$defaultID) : ?>
                '<?= $beschriftung ?>');
                <? else : ?>
                null);
                <? endif ?>
        </script>
