<!-- TODO - move to stylesheet!!! -->
<style>
    .activity-date, .activity-details, .activity-object-link {
        display: block;
    }

    .activity-details {
        max-height: 200px;
        overflow: hidden;
    }

    .activity-object-link {
        border-top: 1px solid #CCC;
    }

    .activity-object-link ul {
        padding: 0px;
    }

    .activity-object-link ul li {
        list-style-type: none;
        display: inline;
        padding-right: 15px;
    }

    <? /*
    .activity-details .read-more {
        display: block;
        padding-top: 6em;
        background: -moz-linear-gradient(to bottom, transparent, white);
        background: -webkit-gradient(linear, 0 0, 0 100%, from(transparent), to(white));
        margin-top: -6em;
    }
     */ ?>
</style>

<div class="stream-container">
     <?= $this->render_partial_collection("_activity", $stream) ?>
</div>