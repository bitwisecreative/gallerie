<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Gallerie - PHP Image Gallery Management</title>
<meta name="description" content="PHP image gallery management application. Flexible and easy to use. Plug and play installation. Multi-file upload. Sort, crop, rotate, and flip images." />
<link rel="icon" href="favicon.ico" type="image/x-icon" />
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
<link rel="stylesheet" href="assets/css/styles.css" type="text/css" />
<script type="text/javascript" src="assets/unitegallery/js/unitegallery.min.js"></script>
<link rel="stylesheet" href="assets/unitegallery/css/unite-gallery.css" type="text/css" property="" />
<script type='text/javascript' src='assets/unitegallery/themes/tilesgrid/ug-theme-tilesgrid.js'></script>
<link rel="stylesheet" href="assets/unitegallery/themes/default/ug-theme-default.css" type="text/css" property="" />
</head>
<body>

<div id="page">

<div id="header">
    <div id="logo"><a href="http://gallerie.pics"><img src="assets/logo/gallerie_logo_64.png" alt="Gallerie" /></a></div>
    <h1><a href="http://gallerie.pics">Gallerie</a></h1>
</div>

<div id="desc">
    <p><a href="http://gallerie.pics/">Gallerie</a> is a PHP image gallery management application that is flexible and easy to use.</p>
</div>

<div id="demo">

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

    <div class="creds">
        <strong>Demo:</strong> Photo upload, management, and output data by <a href="http://gallerie.pics/">Gallerie</a>.
            Gallery display UI by <a href="http://unitegallery.net/">Unite Gallery</a>.
            Photos by <a href="https://unsplash.com/">Unsplash</a>.
    </div>
</div>

<div id="features">
    <h2>Features</h2>
    <ul>
        <li>Drag and drop (or multi-file select) image uploads</li>
        <li>Sort galleries and images (drag and drop)</li>
        <li>Auto display image and thumbnail image generation</li>
        <li>Crop images</li>
        <li>Rotate images</li>
        <li>Flip images</li>
        <li>Supports PNG, JPG, and GIF files</li>
        <li>Image titles (optional)</li>
        <li>Multi-image move (to gallery) or delete</li>
        <li>Responsive admin and demo</li>
        <li>SQLite database (embedded / no config)</li>
        <li>Rebuild all images feature (if you change size settings)</li>
        <li>Easy PHP templates</li>
        <li>Plug and play installation</li>
    </ul>
</div>

<div id="requirements">
    <h2>Requirements</h2>
    <ul>
        <li>PHP 5.3+</li>
        <li>PHP Fileinfo extension</li>
        <li>PHP GD extension</li>
        <li>PHP PDO + SQLite</li>
        <li>Modern Browser (HTML5)</li>
    </ul>
</div>

<div id="push"></div>
<div id="footer">
    &copy; <?php echo date('Y'); ?> gallerie.pics
</div>

</div>
</body>
</html>
