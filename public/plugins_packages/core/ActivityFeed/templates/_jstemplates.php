<script type="text/template" class="activity_stream">
    <% if (num_entries > 0) {
        _.each(stream, function(act) { %>
            <%= activity({activity: act}) %>
        <% });
    } else { %>
        <?= MessageBox::info(_('Keine Aktivitäten gefunden.')) ?>
    <% } %>
</script>

<script type="text/template" class="activity">
    <section class="activity left">
        <header>
            <h1>
                <%- activity.description.title %>
            </h1>
        </header>
    </section>
    <section class="activity-content">
        <!-- TODO: Avatar-URL mitliefern (von der Rest-Route) -->
    </section>
</script>

<?php
/*
    <? $dateformat = '%d.%m.%Y';?>
    <div class="stream-container">
        <? if(sizeof($stream) > 0) : ?>
            <? foreach($stream as $activity) : ?>
                <? if($x != strftime($dateformat, $activity->getMkdate())) :?>
                    <span class="activity-day"><?=strftime($dateformat, $activity->getMkdate())?></span>
                    <? $x = strftime($dateformat, $activity->getMkdate()); ?>
                <?endif;?>
                <?= $this->render_partial("_activity", array('_activity' => $activity)) ?>
            <? endforeach; ?>
        <? else :?>
            <?= MessageBox::info(_('Keine Aktivitäten gefunden.')) ?>
        <? endif; ?>
    </div>
*/
?>

<?php
/*
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
            <? // no link here, we do not know which one to use if multiple links are present ?>
            <?= htmlReady($description['title']) ?>
            <? sprintf(_("%s hat %s %s "),
                User::find($actor['id'])->getFullname(),
                $object['objectType'],
                $_activity->getVerb())
            ?>
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
                <?= strftime('%d.%m.%Y um %X Uhr', $_activity->getMkdate()) ?>
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
 */
?>