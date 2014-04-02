<a href="<?= URLHelper::getLink('dispatch.php/multipersonsearch/no_js_form/?name=' . $name); ?>" class="openmultipersonsearch" data-dialogname="<?= $name; ?>">
    <?
    if (!empty($linkIconPath)) {
        print Assets::img($linkIconPath);
    }
    if (!empty($linkIconPath) && !empty($linkText)) {
        print " ";
    }
    if (!empty($linkText)) {
        print $linkText;
    }
    ?>
</a>

<div id="<?= $name; ?>" title="<?= $title; ?>" style="display: none;">
<? include("form.php"); ?>
</div>
