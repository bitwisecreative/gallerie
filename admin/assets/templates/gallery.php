<div class="section">
    <form method="post" id="gallery-with-selected" style="display: none;">

        <?php if (count($galleries) > 1): ?>
            <select name="gallery">
                <option value="0">-</option>
                <?php foreach ($galleries as $g): ?>
                    <?php if ($gallery['id'] != $g['id']): ?>
                        <option value="<?php echo $g['id']; ?>"><?php echo $g['name']; ?></option>
                    <?php endif; ?>
                <?php endforeach; ?>
            </select>
            <input type="submit" id="gallery-with-selected-move" value="Move Selected to Gallery" class="green" />
        <?php endif; ?>

        <input type="hidden" name="ids" value="" />
        <input type="hidden" name="action" value="" />
        <input type="submit" id="gallery-with-selected-delete" value="Delete Selected" class="red" />
    </form>
    <form method="get">
        <input type="hidden" name="a" value="home" />
        <input type="submit" value="Back to Galleries" />
    </form>
    <div class="clear"></div>
    <h2><?php echo $gallery['name']; ?></h2>
    <form method="post" id="gallery-images-save-sort" style="display: none;">
        <input type="hidden" name="sort" />
        <input type="submit" value="Save Sort" class="orange" />
    </form>

    <div id="gallery-images">
        <?php foreach ($gallery['images'] as $img): ?>
        <div class="gallery-image-item" data-id="<?php echo $img['id']; ?>">
            <div class="image"><img src="<?php echo $img['img']; ?>" alt="<?php echo $img['id']; ?>" /></div>
            <div class="title"><?php echo $img['title']; ?></div>
            <input type="checkbox" class="multi big" value="<?php echo $img['id']; ?>" />
            <form method="get" class="gallery-image-edit-button">
                <input type="hidden" name="a" value="image" />
                <input type="hidden" name="image" value="<?php echo $img['id']; ?>" />
                <input type="submit" value="Edit" />
            </form>
        </div>
        <?php endforeach; ?>
    </div>

</div>

<?php echo $upload; ?>