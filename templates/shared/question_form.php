<div class="modalshadow">
    <div class="messagebox messagebox_modal">
        <?= formatReady($question) ?>
        <div style="margin-top: 0.5em;">
           <form action="<?= $action ?>" method="post">
           <?foreach($elements as $e) :?>
           <div style="margin-top: 0.5em;">
           <?= $e?>
           </div>
           <?endforeach?>
           <div style="margin-top: 0.5em;">
           <?= $approvalbutton ?>
           <span style="margin-left: 1em;">
           <?= $disapprovalbutton ?>
           </span>
           </div>
           </form>
        </div>
    </div>
</div>