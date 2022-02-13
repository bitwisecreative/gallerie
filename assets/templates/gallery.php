
<script>
$(function() {
    $('#gallery').unitegallery({
        tile_width: <?php echo $thumb_size; ?>,
        tile_height: <?php echo $thumb_size; ?>,
        grid_num_rows: 5,
        tile_enable_shadow: false,
        tile_enable_border: true,
        tiles_space_between_cols: 15,
        tiles_justified_space_between: 15,
        tiles_col_width: 300,
        tile_border_color: "#ffffff",
        tile_enable_outline: true,
        lightbox_type: "compact",
        lightbox_arrows_position: "inside",
    });
});
</script>

<div id="gallerie">

<?php if (!$gallery): ?>
<div class="info">Gallery not found.</div>
<?php else: ?>
    <h2><?php echo $gallery['name']; ?></h2>
    <?php if (!$gallery['images']): ?>
    <div class="info">No images.</div>
    <?php else: ?>
    <div id="gallery">
        <?php foreach ($gallery['images'] as $img): ?>
        <img alt="<?php echo htmlentities($img['title']); ?>" src="<?php echo $img['img']; ?>"
             data-image="<?php echo $img['url']; ?>"
             data-description="<?php echo htmlentities($img['title']); ?>" />
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
<?php endif; ?>

</div>

<div class="gallerie-idx-link"><a href="<?php echo $path; ?>">&laquo; Galleries</a></div>