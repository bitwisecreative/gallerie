<?php
/**
 * Gallerie Admin
 */
require_once '../lib/Gallerie.php';
$gallerie = new Gallerie();

// ACTIONS
$a = (isset($_GET['a'])) ? $_GET['a'] : null;
// Auth
if (!$gallerie->auth) {
    $a = 'login';
}
switch ($a) {
    default:
    case 'home':
    case 'galleries':
        echo $gallerie->adminGalleries();
        break;
    case 'gallery':
        $gallery = (isset($_GET['gallery'])) ? $_GET['gallery'] : false;
        echo $gallerie->adminGallery($gallery);
        break;
    case 'image':
        $image = (isset($_GET['image'])) ? $_GET['image'] : false;
        echo $gallerie->adminImage($image);
        break;
    case 'login':
        echo $gallerie->adminLogin();
        break;
    case 'logout':
        $gallerie->adminLogout();
        echo $gallerie->adminLogin();
        break;
    case 'upload': // AJAX (Dropzone)
        $gallerie->ajaxUpload();
        break;
    case 'rebuild': // AJAX
        $gallerie->ajaxRebuildAllImages();
        break;
}