<ul class="<?= implode(' ', $css_classes) ?>">
<? foreach ($elements as $element): ?>
    <li<?= $element->icon ? ' style="' . $element->icon->asCSS() .'"' : "" ?><?= $element->active ? ' class="active"' : '' ?>>
        <?= $element->render() ?>
    </li>
<? endforeach; ?>
</ul>