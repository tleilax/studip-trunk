<form action="<?= $controller->url_for('admin/configuration/user_configuration') ?>" method="post">
    <?= CSRFProtection::insertToken() ?>
    <?= QuickSearch::get("user_id",new StandardSearch("user_id"))
                   ->noSelectbox()
                   ->setInputStyle("width: 150px")
                   ->render(); ?>
</form>
