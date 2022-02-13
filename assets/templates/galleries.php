
<script>
$(function() {
    $('#gallery').unitegallery({
        tile_width: <?php echo $idx_thumb_size; ?>,
        tile_height: <?php echo $idx_thumb_size; ?>,
        grid_num_rows: 4,
        tile_enable_shadow: false,
        tile_enable_border: true,
        tiles_space_between_cols: 15,
        tiles_justified_space_between: 15,
        tiles_col_width: 300,
        tile_border_color: "#ffffff",
        tile_enable_outline: true,
        tile_as_link: true,
        tile_link_newpage: false,
        tile_enable_textpanel: true,
        tile_textpanel_title_text_align: "center",
        tile_textpanel_always_on: true
    });
});
</script>

<div id="gallerie">

    <?php if($none): ?>
    <div class="info">No galleries.</div>
    <?php else: ?>
    <div id="gallery">
        <?php foreach ($galleries as $g): ?>
        <a href="<?php echo $path; ?>?g=<?php echo $g['id']; ?>" title="<?php echo htmlentities($g['name']); ?>">
            <img alt="<?php echo htmlentities($g['name']); ?><br /><i><?php echo $g['count']; ?> Images</i>"
                 src="<?php echo $g['idx_img']; ?>"
                data-image="" />
        </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

</div>
