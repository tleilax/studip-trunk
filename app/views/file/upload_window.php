<div class="file_uploader">
    <div class="file_upload_window">
        <div class="errorbox" style="display: none;">
            <?= MessageBox::error('<span class="errormessage"></span>')?>
        </div>
        <ul class="filenames clean"></ul>
        <div class="uploadbar">
            <?= Icon::create("ufo", "info_alt")->asImg(30, array('class' => "ufo")) ?>
            <?= Icon::create("upload", "info_alt")->asImg(30) ?>
        </div>
    </div>
</div>