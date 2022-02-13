# Gallerie
## _PHP Image Gallery Management_

[![gallerie.pics](https://gallerie.pics/assets/logo/gallerie_logo_64.png)](https://gallerie.pics)

Demo: https://gallerie.pics/
Admin Screenshots: https://gallerie.pics/?g=5

----

Gallerie is a PHP Image Gallery management application designed to be flexible and easy to use. The demo included shows how you can output Gallerie for the Unite Gallery UI. You can update the templates to output for other UIs. The code base is light and simple for easy customizations (if you require them).

### Features
 - Drag and drop (or multi-file select) image uploads
 - Sort galleries and images (drag and drop)
 - Auto display image and thumbnail image generation
 - Crop images
 - Rotate images
 - Flip images
 - Image titles (optional)
 - Multi-image move (to gallery) or delete
 - Responsive admin and demo
 - SQLite database (embedded / no config)
 - Rebuild all images feature (if you change size settings)
 - Easy PHP templates
 - Plug and play installation

### Requirements

 - PHP 5.3+
 - PHP Fileinfo extension
 - PHP GD extension
 - PHP PDO + SQLite
 - Modern Browser (HTML5)

----

### Getting Started
1. Upload the gallerie folder to your server
2. Open a browser and navigate to the `gallerie/admin/` folder
3. The default password is... `password`
4. The diagnostic will run on the Gallerie Admin homepage, and display any errors you will need to resolve

### Configuration

The Gallerie config file is located at `gallerie/config.php`

The first thing you will need to do is update the admin password. Obtain an MD5 hash of your updated password and update the `GALLERIE_PASS` variable. You can obtain an MD5 hash here: https://www.md5hashgenerator.com/

### Config Variables:

`GALLERIE_PASS`
The Gallerie Admin password (MD5 hashed)

`GALLERIE_THUMB_SIZE`
The width and height of thumbnail images

`GALLERIE_IDX_THUMB_SIZE`
The width and height of gallery listing thumbnail images

`GALLERIE_MAX_WIDTH`
The maximum width of a generated display image

`GALLERIE_MAX_HEIGHT`
The maximum height of a generated display image

`GALLERIE_JPG_QUALITY`
The JPG quality setting. Lower is less quality and smaller file size. Higher is better quality and larger file size.

`GALLERIE_PNG_COMPRESSION`
The PNG compression setting. Lower for less compression and faster image build. Higher for more compression and slower image build. Quality is unaffected.

`GALLERIE_PATH`
The absolute web path to your gallerie folder.

`GALLERIE_PATH_DISPLAY`
The absolute web path to your gallery display page.

`GALLERIE_TPL_PATH`
The relative server path (from the gallerie folder) to your output templates folder.
Once you have the password updated and the config variables to your liking, you can access the admin area at gallerie/admin

----

### Admin Interface

Using the admin area is self-explanatory. However, there are some things you should know:

 - The first image of a gallery will be its index image.
 - If you upload a file with the same file name as a previously uploaded file, the new file will be renamed (a number will be appended to the end of the file).
 - The upload max file size is detected from your PHP config.
 - If you change the Gallerie image sizing configuration after you already have images uploaded, you can run the "Rebuild All Images" function at the bottom of the Gallerie Admin home page to have all of your images rebuilt with the new configuration options.
 - The admin builds three versions of your uploaded images:
   - *display*: The images resized to fit your `GALLERIE_MAX_WIDTH` and `GALLERIE_MAX_HEIGHT` configuration settings.
   - *thumbs*: The gallery display thumbnails center cropped to fix your `GALLERIE_THUMB_SIZE` configuration setting.
   - *idx_thumbs*: The galleries list index thumbnails center cropped to fix your `GALLERIE_IDX_THUMB_SIZ`E configuration setting.
 - If you modify an image (crop, rotate, or flip) a modified version of your original uploaded file will be created and used.
 - Gallerie uses either the original image, or the high res modified image to build display images and thumbnails.
 - If you modify an image, you can revert it to the original.

----

### Gallery Output

Gallerie outputs galleries using PHP templates. The demo includes an example gallery page at `gallerie/index.php`. The templates for the demo are located in `gallerie/assets/templates/`.

You can modify the templates, or how the index works.
To reference the demo, the `index.php` page sets up the Gallerie object, checks for a provided gallery ID, and displays the appropriate content.

```php
<?php
/**
 * Gallerie Demo
 */
require_once 'lib/Gallerie.php';
$gallerie = new Gallerie();
$g = (isset($_GET['g'])) ? $_GET['g'] : false;
if ($g === false) {
    echo $gallerie->galleries();
} else {
    echo $gallerie->gallery($g);
}
?>
```

If a gallery ID is not provided, is displays the `galleries.php` template. Otherwise, it will display the `gallery.php` template.

The `galleries.php` template displays all of the galleries with output configured for the Unite Gallery UI:

```html
<?php if ($none): ?>
    <div class="info">No galleries.</div>
<?php else: ?>
    <div id="gallery">
    <?php foreach ($galleries as $g): ?>
        <a href="<?php echo $path; ?>?g=<?php echo $g['id']; ?>" title="<?php echo htmlentities($g['name']); ?>"> <img alt="<?php echo htmlentities($g['name']); ?><br /><i><?php echo $g['count']; ?> Images</i>" src="<?php echo $g['idx_img']; ?>" data-image="" /> </a>
    <?php endforeach; ?>
    </div>
<?php endif; ?>
```

And, the` gallery.php` template displays the gallery images with output configured for the Unite Gallery UI:

```html
<?php if (!$gallery): ?>
    <div class="info">Gallery not found.</div>
<?php else: ?>
    <h2><?php echo $gallery['name']; ?></h2>
    <?php if (!$gallery['images']): ?>
        <div class="info">No images.</div>
    <?php else: ?>
        <div id="gallery">
        <?php foreach ($gallery['images'] as $img): ?>
            <img alt="<?php echo htmlentities($img['title']); ?>" src="<?php echo $img['img']; ?>" data-image="<?php echo $img['url']; ?>" data-description="<?php echo htmlentities($img['title']); ?>" />
        <?php endforeach; ?>
        </div>
    <?php endif; ?>
<?php endif; ?>
```

----

### Files

`admin/`
The Gallerie Admin directory

`assets/`
Assets (css, js, templates, etc.) for the demo

`data/`
The SQLite Gallerie database file

`images/`
Where Gallerie Admin uploads and works with your images

`lib/`
PHP - The Gallerie class and the Templates class

`config.php`
The Gallerie config file
