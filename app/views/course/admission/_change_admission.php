<? foreach(PageLayout::getMessages() as $pm) : ?>
    <?= $pm ?>
<? endforeach; ?>
<form class="studip_form" action="<?= $controller->link_for() ?>" method="post">
<?= CSRFProtection::tokenTag()?>
<? foreach($request as $k => $v) : ?>
    <?= addHiddenFields($k, $v) ?>
<? endforeach ?>
<?= Studip\Button::create(_("Ja"), $button_yes)?>
<?= Studip\Button::create(_("Nein"), $button_no)?>
<?= Studip\LinkButton::create(_("Abbrechen"), $controller->url_for('/index'), array('rel' => 'close')) ?>
</form>