<script src="assets/js/jquery.Jcrop.min.js"></script>
<link rel="stylesheet" href="assets/css/jquery.Jcrop.min.css" type="text/css" property="" />

<script>
$(function() {
    var shiftDown = false;
    var cropApi;
    var cropData;
    var configMaxSize = [<?php echo $config_max_width; ?>, <?php echo $config_max_height; ?>];
    // Init Crop
    $('form.image-crop').on('submit', function(e) {
        if (!$('#crop-cancel').is(':visible')) {
            e.preventDefault();
            var w = $('div.image-display img').eq(0).width();
            var h = $('div.image-display img').eq(0).height();
            $('div.image-display img').Jcrop({
                setSelect: [0, 0, w, h],
                onSelect: function (c) {
                    cropData = [w, h, c.x, c.y, c.x2, c.y2, c.w, c.h];
                    var cropDataJson = JSON.stringify(cropData);
                    $('input[name="crop"]').val(cropDataJson);
                    // Keydown fix (moving disables keydown, and breaks the shift ratio lock)
                    setTimeout(function () {
                        document.activeElement.blur();
                    }, 100);
                }
            }, function () {
                cropApi = this;
            });
            // Scroll Fix
            $('html, body').animate({
                scrollTop: $("#crop-button").offset().top - 20
            }, 250);
            // DOM updates
            $('div.crop-selections').show();
            $('#crop-button').val('Save Crop').addClass('green');
            $('#crop-cancel').show();
        }
    });
    // Cancel Crop
    $('#crop-cancel').on('click', function() {
        cropApi.destroy();
        $('div.crop-selections').hide();
        $('#crop-button').val('Crop').removeClass('green');
        $('#crop-cancel').hide();
    });
    // Auto Selections
    $('a.crop-select-config-max').on('click', function() {
        var loc = ratioCalc(parseInt(configMaxSize[0]), parseInt(configMaxSize[1]), cropData[0], cropData[1]);
        cropApi.setOptions({setSelect: loc});
    });
    $('a.crop-select-wide').on('click', function() {
        var loc = ratioCalc(16, 9, cropData[0], cropData[1]);
        cropApi.setOptions({setSelect: loc});
    });
    $('a.crop-select-standard').on('click', function() {
        var loc = ratioCalc(4, 3, cropData[0], cropData[1]);
        cropApi.setOptions({setSelect: loc});
    });
    $('a.crop-select-square').on('click', function() {
        var loc = ratioCalc(1, 1, cropData[0], cropData[1]);
        cropApi.setOptions({setSelect: loc});
    });
    // Ratio Calc
    function ratioCalc(rw, rh, w, h) {
        var r = rw / rh;
        var rr = rh / rw;
        var cr = w / h;
        var crr = h / w;
        var x, y, x2, y2;
        if (w >= h) {
            x = 0;
            x2 = w;
            y = h / 2 - w / r / 2;
            y2 = h / 2 + w / r / 2;
            if (cr > r) {
                x = w / 2 - h / rr / 2;
                x2 = w / 2 + h / rr / 2;
                y = 0;
                y2 = h;
            }
        } else {
            x = w / 2 - h / r / 2;
            x2 = w / 2 + h / r / 2;
            y = 0;
            y2 = h;
            if (crr > r) {
                x = 0;
                x2 = w;
                y = h / 2 - w / rr / 2;
                y2 = h / 2 + w / rr / 2;
            }
        }
        return [x, y, x2, y2];
    }
    // Shift Key Crop Ratio Lock
    $(document).on('keydown', function(e) {
        if (e.keyCode == 16) {
            if (!shiftDown) {
                shiftDown = true;
                if (typeof cropApi == 'object') {
                    cropApi.setOptions({aspectRatio: cropData[6] / cropData[7]});
                }
            }
        }
    });
    $(document).on('keyup', function(e) {
        if (e.keyCode == 16) {
            if (shiftDown) {
                shiftDown = false;
                if (typeof cropApi == 'object') {
                    cropApi.setOptions({aspectRatio: null});
                }
            }
        }
    });
});
</script>

<div class="section">
    <form method="post" class="image-delete">
        <input type="hidden" name="delete" value="<?php echo $image['id']; ?>" />
        <input type="submit" value="Delete Image" class="red" />
    </form>
    <form method="get">
        <input type="hidden" name="a" value="gallery" />
        <input type="hidden" name="gallery" value="<?php echo $image['gallery_id']; ?>" />
        <input type="submit" value="Back to Gallery" />
    </form>
    <div class="clear"></div>
    <h2>Editing: <?php echo $image['file']; ?></h2>
    <?php if ($image['title']): ?>
    <h3><?php echo $image['title']; ?></h3>
    <?php endif; ?>
    <div class="image-thumb">
        <img src="<?php echo $image['thumb']; ?>" alt="<?php echo $image['id']; ?>" />
    </div>
    <div class="image-actions">

        <div class="image-action-row">
            <form method="post" class="image-title">
                <input type="text" name="title" value="<?php echo htmlentities($image['title']); ?>" />
                <input type="submit" value="Update Title" class="green" />
            </form>

            <?php if ($modified): ?>
            <form method="post" class="image-revert">
                <input type="hidden" name="revert" value="<?php echo $image['id']; ?>" />
                <input type="submit" value="Revert to Original" class="green" />
            </form>
            <?php endif; ?>
        </div>

        <div class="image-action-row">
            <form method="post" class="image-rotate">
                <select name="turn">
                    <option value="1">90&deg; Counterclockwise</option>
                    <option value="2">90&deg; Clockwise</option>
                    <option value="3">180&deg;</option>
                </select>
                <input type="hidden" name="rotate" value="<?php echo $image['id']; ?>" />
                <input type="submit" value="Rotate" class="green" />
            </form>
            <form method="post" class="image-flip">
                <select name="dir">
                    <option value="1">Horizontal</option>
                    <option value="2">Vertical</option>
                </select>
                <input type="hidden" name="flip" value="<?php echo $image['id']; ?>" />
                <input type="submit" value="Flip" class="green" />
            </form>
        </div>

        <div class="image-action-row">
            <form method="post" class="image-crop">
                <input type="hidden" name="crop" value="" />
                <input type="submit" id="crop-button" value="Crop" />
                <input type="button" id="crop-cancel" value="Cancel Crop" class="orange" style="display: none;" />
                <div class="crop-selections" style="display: none;">
                    <a href="javascript:void(0);" class="crop-select-config-max">Fit Config Ratio</a>
                    <a href="javascript:void(0);" class="crop-select-wide">Fit 16:9</a>
                    <a href="javascript:void(0);" class="crop-select-standard">Fit 4:3</a>
                    <a href="javascript:void(0);" class="crop-select-square">Fit Square</a>
                </div>
            </form>
        </div>

    </div>
    <div class="clear"></div>
    <div class="image-display">
        <img src="<?php echo $image['display']; ?>" alt="<?php echo $image['id']; ?>" />
    </div>
</div>