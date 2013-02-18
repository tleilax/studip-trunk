<?php
    switch ($tree_type) {
        case 'range_tree':
            $rel = $tree->tree_data[$current]['studip_object_id'] ? 'studip_object' : 'tree_level';
            $selectable = ($current != 'root' && $tree->tree_data[$current]['studip_object_id']);
            $elementName = 'institute';
            $elementValue = $tree->tree_data[$current]['studip_object_id'];
            $elementSelected = ($selected[$elementValue] ? ' checked="checked"' : '');
            break;
        case 'sem_tree':
        default:
            $rel = $tree->hasKids($current) ? 'node' : 'leaf';
            $selectable = ($current != 'root');
            $elementName = 'sem_tree_entry';
            $elementValue = $current;
            $elementSelected = ($selected[$elementValue] ? ' checked="checked"' : '');
            break;
    }
?>
<li id="<?= $current ?>" rel="<?= $rel; ?>">
<?php
if ($tree->hasKids($current)) {
    if ($selectable) {
        echo '<input type="checkbox" name="'.$elementName.'[]" value="'.
            $elementValue.'"'.$elementSelected.'>';
    }
?>
    <a><?= htmlReady($tree->tree_data[$current]['name']); ?></a>
    <ul>
        <?php
        foreach ($tree->getKids($current) as $kid) {
            echo $this->render_partial('admission/courseset/treelevel',array('current' => $kid));
        }
        ?>
    </ul>
<?php
} else {
    if ($selectable) {
        echo '<input type="checkbox" name="'.$elementName.'[]" value="'.
            $elementValue.'"'.$elementSelected.'>';
    }
    echo '<a>'.htmlReady($tree->tree_data[$current]['name']).'</a>';
}
?>
</li>