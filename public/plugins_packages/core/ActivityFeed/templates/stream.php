<div class="stream-container">
<? foreach($stream as $activity) :?>
     <?= $this->render_partial("_activity", array('activity' => $activity))?>
     <div class='clear'></div>
<? endforeach;?>
</div>