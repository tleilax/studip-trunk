<?php
$actor = $_activity->getActor();

if ($actor ['id'] == $GLOBALS ['user']->id) {
    $direction = "right";
} else {
    $direction = "left";
}

$object = $_activity->getObject ();

$description = $_activity->getDescription();

?>

<section class="activity <?=$direction?>">
    <header>
        <h1>
            <a href="<?= URLHelper::getURL($object['url']) ?>">
                <?= htmlReady($description['title']) ?>
                <? sprintf(_("%s hat %s %s "),
                    User::find($actor['id'])->getFullname(),
                    $object['objectType'],
                    $_activity->getVerb())
                ?>
            </a>
        </h1>
    </header>
    <section class="activity-content">
        <div class="activity-avatar-container">
            <a href="<?= URLHelper::getURL(sprintf('dispatch.php/profile?username=%s',User::find($actor['id'])->username))?>">
            <?=Avatar::getAvatar($actor['id'])->getImageTag(Avatar::MEDIUM)?>
            </a>
        </div>
        <section class="activity-description">
            <span class="activity-date">
                <?= strftime('%x um %X Uhr', $_activity->getMkdate()) ?>
            </span>

            <span class="activity-details">
                <?= $description['content'] ?>

                <!-- TODO: fade out at the bottom to signalize further content -->
            </span>

            <span class="activity-object-link">
                <?= $this->render_partial("_urls", array('urls' => $object['url'])) ?>
            </span>
        </section>
        <div class='clear'></div>
    </section>
</section>
<div class='clear'></div>