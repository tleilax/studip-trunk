<?
# Lifter010: TODO
?>
<td align="left">

    <?php foreach ($domains as $domain) : ?>
        <div>

            <? if (isset($auto_sem['status'][$domain['id']]) && in_array($status, $auto_sem['status'][$domain['id']])) : ?>
                <a href="<?= $controller->url_for('admin/autoinsert/edit/' . $auto_sem['seminar_id'], array('domain_id' => $domain['id'], 'status' => $status, 'remove' => true)) ?>">
                    <?= Icon::create('checkbox-checked', 'clickable')->asImg(16) ?>
                </a>
            <? else : ?>
                <a href="<?= $controller->url_for('admin/autoinsert/edit/' . $auto_sem['seminar_id'], array('domain_id' => $domain['id'], 'status' => $status)) ?>">
                    <?= Icon::create('checkbox-unchecked', 'clickable')->asImg(16) ?>
                </a>
            <? endif ?>
            <?= htmlReady($domain['name']) ?></div>
    <?php endforeach; ?>

</td>
