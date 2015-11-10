<?//=var_dump($_activity)?>

<?
$actor = $_activity->getActor();
if ($actor ['id'] == $GLOBALS ['user']->id)
    $direction = "right";
else
    $direction = "left";
$object = $_activity->getObject ();

?>



<section class="activity <?=$direction?>">
    <header>
        <h1><?=sprintf(_("%s hat am %s ein %s %s "),User::find($actor['id'])->getFullname(), strftime('%x um %X Uhr', $_activity->getMkdate()) ,$object['objectType'], $_activity->getVerb())?></h1>
    </header>
    <section class="activity-content">
        <div class="activity-avatar-container">
            <a href="<?= URLHelper::getURL(sprintf('dispatch.php/profile?username=%s',User::find($actor['id'])->username))?>">
            <?=Avatar::getAvatar($actor['id'])->getImageTag(Avatar::MEDIUM)?>
            </a>
        </div>
            <section class="activity-description">
                <?=$_activity->getDescription()?><br>
                <b>Meinen Kontext habe ich leider schon vergessen :(</b><br>
                <span class="activity-object-link">
                <a href="<?=URLHelper::getURL($object['url'])?>"><?=_(sprintf("Direkt zum entsprechenden Aktivitätsobject \"%s\" springen", $object['objectType']))?></a>
                </span>
            </section>
        <div class='clear'></div>
    </section>
</section>
<div class='clear'></div>