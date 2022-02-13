$(function() {

    // Gallery Sort
    $('#galleries').sortable({
        update: function(event, ui) {
            $('#galleries-save-sort').show();
            var sort = [];
            $('.gallery-list-item').each(function() {
                sort.push($(this).attr('data-id'));
                $('#galleries-save-sort input[name="sort"]').val(JSON.stringify(sort));
            });
        }
    });

    // Gallery Rename
    $('form.gallery-list-item-rename').on('submit', function(e) {
        if (!$(this).find('input[name="name"]').is(':visible')) {
            $(this).find('input[name="name"]').show();
            e.preventDefault();
        } else {
            if (!$(this).find('input[name="name"]').val()) {
                $(this).find('input[name="name"]').hide();
                e.preventDefault();
            }
        }
    });

    // Gallery Delete
    $('.gallery-list-item-delete').on('submit', function(e) {
        var ok = confirm('Are you sure? This will delete the gallery and all of its images.');
        if (!ok) {
            e.preventDefault();
        }
    });

    // Select Gallery
    $('#select_gallery').on('change', function() {
        $('#upload_gallery_id').val($(this).val());
        if ($(this).val() > 0) {
            $('#dz').show();
            $('html, body').animate({
                scrollTop: $("#dz").offset().top
            }, 500);
        } else {
            $('#dz').hide();
        }
    });

    // Add Gallery
    $('.gallery-add').on('submit', function(e) {
        if (!$(this).find('input[type="text"]').val()) {
            e.preventDefault();
        }
    });

    // Gallery Images Sort
    $('#gallery-images').sortable({
        update: function(event, ui) {
            $('#gallery-images-save-sort').show();
            var sort = [];
            $('.gallery-image-item').each(function() {
                sort.push($(this).attr('data-id'));
                $('#gallery-images-save-sort input[name="sort"]').val(JSON.stringify(sort));
            });
        }
    });

    // Multi-select Delete
    $('#gallery-with-selected-delete').on('click', function(e) {
        var ok = confirm('Are you sure you want to delete the selected images?');
        if (!ok) {
            e.preventDefault();
            return false;
        }
        var selected = [];
        $('input[type="checkbox"]:checked.multi').each(function() {
            selected.push($(this).val());
        });
        $('#gallery-with-selected input[name="ids"]').val(JSON.stringify(selected));
        $('#gallery-with-selected input[name="action"]').val('delete');
    });

    // Multi-select Move
    $('#gallery-with-selected-move').on('click', function(e) {
        if ($('select[name="gallery"]').val() < 1) {
            e.preventDefault();
            return false;
        }
        var selected = [];
        $('input[type="checkbox"]:checked.multi').each(function() {
            selected.push($(this).val());
        });
        $('#gallery-with-selected input[name="ids"]').val(JSON.stringify(selected));
        $('#gallery-with-selected input[name="action"]').val('move');
    });

    // Image Delete
    $('.image-delete').on('submit', function(e) {
        var ok = confirm('Are you sure you want to delete this image?');
        if (!ok) {
            e.preventDefault();
        }
    });

    // Image Multi-select
    $('input.multi').on('click', function() {
        var count = $('input:checked.multi').length;
        if (count) {
            $('#gallery-with-selected').show();
        } else {
            $('#gallery-with-selected').hide();
        }
    });

    // Rebuild All Images
    $('#rebuild-images-button').on('click', function() {
        $('#rebuild-images-progress').show();
        var images;
        var cur = 0;
        var tot = 0;
        $.post('index.php?a=rebuild', {
            a: 'get'
        }).done(function(d) {
            images = JSON.parse(d);
            tot = images.length;
            function processNext() {
                $('#rebuild-images-progress .progress-text').text(cur + '/' + tot);
                $('#rebuild-images-progress .progress-bar-progress').css('width', Math.floor(cur / tot * 100) + '%');
                console.log(images[cur].id);
                $.post('index.php?a=rebuild', {
                    a: 'rebuild',
                    id: images[cur].id
                }).done(function(d) {
                    cur += 1;
                    if (cur < tot) {
                        processNext();
                    } else {
                        $('#rebuild-images-progress .progress-text').text('Done!');
                        $('#rebuild-images-progress .progress-bar-progress').css('width', '100%');
                        $('#rebuild-reload-admin').show();
                    }
                });
            }
            processNext();
        });
    });

});