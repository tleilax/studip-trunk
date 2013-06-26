<?php
// Set some values depending on element attributes.
$rel = ($current != 'root' && $tree->tree_data[$current]['studip_object_id']) ? 'studip_object' : 'tree_level';
$selectable = ($current != 'root' && $tree->tree_data[$current]['studip_object_id']);
$elementValue = $tree->tree_data[$current]['studip_object_id'];
$elementSelected = ($selected[$elementValue] ? ' checked="checked"' : '');
?>
<li id="<?= $current ?>" rel="<?= $rel; ?>">
<?php
if ($tree->hasKids($current)) {
    if ($selectable) {
        echo '<input type="checkbox" name="institutes[]" class="institute" value="'.
            $elementValue.'"'.$elementSelected.'>';
    }
?>
    <a href=""><?= htmlReady($tree->tree_data[$current]['name']); ?></a>
    <ul>
        <?php
        foreach ($tree->getKids($current) as $kid) {
            echo $this->render_partial('admission/courseset/institutes',array('current' => $kid));
        }
        ?>
    </ul>
<?php
} else {
    if ($selectable) {
        echo '<input type="checkbox" name="institutes[]" class="institute" value="'.
            $elementValue.'"'.$elementSelected.'>';
    }
    echo '<a href="">'.htmlReady($tree->tree_data[$current]['name']).'</a>';
}
?>
</li>