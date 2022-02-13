
<script src="assets/js/dropzone.js"></script>
<link rel="stylesheet" href="assets/css/dropzone.css" type="text/css" property="" />

<script>
$(function() {
    Dropzone.autoDiscover = false;
    $('#dz form').dropzone({
        maxFilesize: <?php echo $max_mb; ?>,
        acceptedFiles: '.jpg,.jpeg,.png,.gif',
        dictDefaultMessage: 'Drop files here or click to upload.',
        dictFileTooBig: 'File is too big ({{filesize}} MB). Max size is {{maxFilesize}} MB.',
        sending: function() {
            $('#select_gallery').prop('disabled', true);
        },
        queuecomplete: function() {
            $('#select_gallery').prop('disabled', false);
            $('#upload-reload-admin').show();
        }
    });
});
</script>

<div class="section">
    <h2>Upload</h2>
    <div id="upload">
        <p>Max upload size: <?php echo $max_mb; ?> MB<br />
        Uploaded images with the same file name as a previously uploaded image will be renamed.</p>

        <?php if (!$gallery_id): ?>
            <label>First, select the gallery you want to upload images to:</label>
            <select id="select_gallery">
                <option value="0">-</option>
                <?php foreach ($galleries as $g): ?>
                <option value="<?php echo $g['id']; ?>"><?php echo $g['name']; ?></option>
                <?php endforeach; ?>
            </select>
        <?php endif; ?>

        <div id="upload-reload-admin" style="display: none;">
            <br />
            <form action="index.php" method="get">
                <?php if ($gallery_id): ?>
                <input type="hidden" name="a" value="gallery" />
                <input type="hidden" name="gallery" value="<?php echo $gallery_id; ?>" />
                <?php endif; ?>
                <input type="submit" value="Reload Page" class="orange" />
            </form>
        </div>

        <?php
        $dz_hide = '';
        if (!$gallery_id) {
            $dz_hide = ' style="display: none;"';
        }
        ?>
        <div id="dz"<?php echo $dz_hide; ?>>
            <form action="index.php?a=upload" method="post" class="dropzone" enctype="multipart/form-data">
                <input type="hidden" id="upload_gallery_id" name="gallery" value="<?php echo $gallery_id; ?>" />
                <div class="fallback">
                    <input name="file" type="file" />
                    <input type="submit" value="Upload" />
                </div>
            </form>
        </div>
    </div>
</div>