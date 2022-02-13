<div class="section">
    <h2>Galleries</h2>
    <form method="post" id="galleries-save-sort" style="display: none;">
        <input type="hidden" name="sort" />
        <input type="submit" value="Save Sort" class="orange" />
    </form>
    <div id="galleries">

        <?php if($none): ?>
        <div class="info">No galleries.</div>
        <?php endif; ?>

        <?php foreach ($galleries as $gallery): ?>
        <div class="gallery-list-item" data-sort="<?php echo $gallery['sort']; ?>"
                data-name="<?php echo $gallery['name']; ?>"
                data-id="<?php echo $gallery['id']; ?>">
            <div class="idx-img"><?php echo $gallery['idx_img']; ?></div>
            <div class="name"><?php echo $gallery['name']; ?></div>
            <div class="gallery-id">Gallery ID: <?php echo $gallery['id']; ?></div>
            <div class="count"><?php echo $gallery['count']; ?> images</div>
            <div class="actions">
                <form action="index.php" method="post" class="gallery-list-item-rename">
                    <input type="hidden" name="id" value="<?php echo $gallery['id']; ?>" />
                    <input type="text" name="name" style="display: none;" />
                    <input type="submit" value="Rename" class="green" />
                </form>
                <form action="index.php" method="post" class="gallery-list-item-delete">
                    <input type="hidden" name="delete" value="<?php echo $gallery['id']; ?>" />
                    <input type="submit" value="Delete" class="red" />
                </form>
                <form action="index.php" method="get" class="gallery-list-item-manage">
                    <input type="hidden" name="a" value="gallery" />
                    <input type="hidden" name="gallery" value="<?php echo $gallery['id']; ?>" />
                    <input type="submit" value="Manage Images" />
                </form>
            </div>
            <div class="clear"></div>
        </div>
        <?php endforeach; ?>

    </div>
    <form method="post" class="gallery-add">
        <label>Add Gallery</label>
        <input type="text" name="name" />
        <input type="submit" value="Add" class="green" />
    </form>
</div>

<?php echo $upload; ?>

<div class="section">
    <h2>Rebuild</h2>
    <p>If you've changed the config settings for image sizes after already having images uploaded, you can
        run this action to rebuild all of the images (thumbnails and display images).</p>
    <div id="rebuild-reload-admin" style="display: none;">
        <form action="index.php" method="get">
            <input type="submit" value="Reload Page" class="orange" />
        </form>
    </div>
    <div id="rebuild-images-progress" style="display: none;">
        <div class="progress-text"></div>
        <div class="progress-bar">
            <div class="progress-bar-progress"></div>
        </div>
    </div>
    <input type="button" id="rebuild-images-button" value="Rebuild All Images" class="green" />
</div>
