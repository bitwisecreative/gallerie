<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/Templates.php';

/**
 * Gallerie Class
 */
class Gallerie
{
    /**
     * @var bool Auth flag (for admin area)
     */
    public $auth = false;
    /**
     * @var PDO SQLite DB handler
     */
    private $db;
    /**
     * @var Templates Admin templates
     */
    private $at;
    /**
     * @var Templates Output templates
     */
    private $ot;
    /**
     * @var string Error string
     */
    public $error = '';
    /**
     * @var string Success string
     */
    public $success = '';

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->auth();
        $this->db = new PDO('sqlite:' . GALLERIE_ROOT . GALLERIE_DATA);
        $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $this->at = new Templates(GALLERIE_ROOT . GALLERIE_ADMIN_TPL_PATH);
        $this->ot = new Templates(GALLERIE_ROOT . GALLERIE_TPL_PATH);
    }

    /**
     * Auth
     */
    private function auth()
    {
        if (isset($_POST['gallerie_pass'])) {
            if (md5($_POST['gallerie_pass']) === GALLERIE_PASS) {
                $_SESSION['gallerie_auth'] = true;
                $this->auth = true;
            }
        }
        if (isset($_SESSION['gallerie_auth'])) {
            if ($_SESSION['gallerie_auth'] = true) {
                $this->auth = true;
            }
        }
    }

    /**
     * Admin galleries controller
     * @return string
     * @throws Exception
     */
    public function adminGalleries()
    {
        // Diagnostic
        $diagnostic = $this->diagnostic();
        if ($diagnostic) {
            $this->at->diagnostic = $diagnostic;
            $content = $this->at->render('diagnostic');
            $out = $this->outputAdmin($content);
            return $out;
        }

        // Galleries Add/Rename
        if (isset($_POST['name'])) {
            if (isset($_POST['id'])) {
                $action = array('renaming', 'renamed');
                $res = $this->renameGallery($_POST['name'], $_POST['id']);
            } else {
                $action = array('adding', 'added');
                $res = $this->addGallery($_POST['name']);
            }
            if (!$res) {
                $this->error = 'Error ' . $action[0] . ' gallery';
            } else {
                $this->success = 'Gallery ' . $action[1];
            }
        }

        // Galleries Sort
        if (isset($_POST['sort'])) {
            $order = json_decode($_POST['sort']);
            $res = $this->sortGalleries($order);
            if (!$res) {
                $this->error = 'Error sorting galleries';
            } else {
                $this->success = 'Galleries sorted';
            }
        }

        // Galleries Delete
        if (isset($_POST['delete'])) {
            $gallery_delete_id = (isset($_POST['delete'])) ? $_POST['delete'] : false;
            if (!$gallery_delete_id) {
                $this->error = 'Gallery not specified.';
            } else {
                $stmt = $this->db->prepare('SELECT * FROM galleries WHERE id = ?');
                $stmt->execute(array($gallery_delete_id));
                $gallery_delete = $stmt->fetch();
                if (!$gallery_delete) {
                    $this->error = 'Gallery not found.';
                } else {
                    $stmt = $this->db->prepare('SELECT * FROM images WHERE gallery_id = ?');
                    $stmt->execute(array($gallery_delete_id));
                    $gallery_delete_images = $stmt->fetchAll();
                    foreach ($gallery_delete_images as $img) {
                        $this->deleteImage($img['id']);
                    }
                    $stmt = $this->db->prepare('DELETE FROM galleries WHERE id = ?');
                    $stmt->execute(array($gallery_delete_id));
                    $this->success = 'Gallery deleted.';
                }
            }
        }

        // Build Upload Display
        $galleries = $this->getGalleries();
        $upload = '';
        if ($galleries) {
            $this->at->max_mb = $this->getMaxMb();
            $this->at->galleries = $galleries;
            $this->at->gallery_id = 0;
            $upload = $this->at->render('upload');
        }

        // Galleries Display
        $none = true;
        if ($galleries) {
            $none = false;
            foreach ($galleries as $i => $g) {
                $idx_img = '';
                if ($g['count']) {
                    $idx_img = '<img src="' . GALLERIE_PATH . GALLERIE_THUMBS . rawurlencode($g['idx_img']) . '?t=' . time() . '" alt="' . htmlentities($g['name']) . '" />';
                }
                $galleries[$i]['idx_img'] = $idx_img;
            }
        }
        $this->at->none = $none;
        $this->at->galleries = $galleries;
        $this->at->upload = $upload;
        $content = $this->at->render('galleries');

        // Output
        return $this->outputAdmin($content);
    }

    /**
     * Admin gallery controller
     * @param $gallery_id int The gallery ID
     * @return string
     * @throws Exception
     */
    public function adminGallery($gallery_id)
    {
        $gallery = $this->getGallery($gallery_id);
        if (!$gallery) {
            $this->error = 'Gallery not found.';
            $content = $this->at->render('not_found');
            $out = $this->outputAdmin($content);
            return $out;
        }

        // Gallery Sort
        if (isset($_POST['sort'])) {
            $order = json_decode($_POST['sort']);
            $res = $this->sortImages($order);
            if (!$res) {
                $this->error = 'Error sorting gallery images';
            } else {
                $this->success = 'Gallery images sorted';
            }
            // Update gallery var
            $gallery = $this->getGallery($gallery_id);
        }

        // Multi-actions
        $ids = (isset($_POST['ids'])) ? json_decode($_POST['ids']) : false;
        $action = (isset($_POST['action'])) ? $_POST['action'] : false;

        // Multi-delete
        if (count($ids) && $action == 'delete') {
            foreach ($ids as $id) {
                $this->deleteImage($id);
            }
            $this->success = 'Images deleted.';
            // Reload gallery data
            $gallery = $this->getGallery($gallery_id);
        }

        // Multi-move
        $gallery_move = (isset($_POST['gallery'])) ? $_POST['gallery'] : false;
        if (count($ids) && $action == 'move' && $gallery_move) {
            foreach ($ids as $id) {
                $this->moveImage($id, $gallery_move);
            }
            $this->success = 'Images moved.';
            // Reload gallery data
            $gallery = $this->getGallery($gallery_id);
        }

        // Build Upload Display
        $galleries = $this->getGalleries();
        $upload = '';
        if ($galleries) {
            $this->at->max_mb = $this->getMaxMb();
            $this->at->galleries = $galleries;
            $this->at->gallery_id = $gallery_id;
            $upload = $this->at->render('upload');
        }

        // Gallery Display
        foreach ($gallery['images'] as $i => $img) {
            $gallery['images'][$i]['img'] = GALLERIE_PATH . GALLERIE_THUMBS . rawurlencode($img['file']) . '?t=' . time();
        }
        $this->at->upload = $upload;
        $this->at->galleries = $galleries;
        $this->at->gallery = $gallery;
        $content = $this->at->render('gallery');

        // Output
        return $this->outputAdmin($content);
    }

    /**
     * Admin image controller
     * @param $image_id int The image ID
     * @return string
     * @throws Exception
     */
    public function adminImage($image_id)
    {
        $image = $this->getImage($image_id);
        if (!$image) {
            $this->error = 'Image not found.';
            $content = $this->at->render('not_found');
            $out = $this->outputAdmin($content);
            return $out;
        }

        // Image Title
        $title = (isset($_POST['title'])) ? $_POST['title'] : false;
        if ($title !== false) {
            $update_title = $this->imageTitle($image_id, $title);
            if (!$update_title) {
                $this->error = 'Error updating title.';
            } else {
                $this->success = 'Title updated.';
                // Reload data
                $image = $this->getImage($image_id);
            }
        }

        // Image Delete
        $delete = (isset($_POST['delete'])) ? $_POST['delete'] : false;
        if ($delete) {
            $this->deleteImage($image_id);
            header('Location: index.php?a=gallery&gallery=' . $image['gallery_id']);
            exit;
        }

        // Image Revert
        $revert = (isset($_POST['revert'])) ? $_POST['revert'] : false;
        if ($revert) {
            $this->revertImage($image_id);
            $this->success = 'Image reverted.';
        }

        // Image Crop
        // JSON data [w, h, c.x, c.y, c.x2, c.y2, c.w, c.h];
        $crop = (isset($_POST['crop'])) ? $_POST['crop'] : false;
        if ($crop) {
            $crop = json_decode($crop);
            $this->cropImage($image_id, $crop);
            $this->success = 'Image cropped.';
        }

        // Image Rotate
        $rotate = (isset($_POST['rotate'])) ? $_POST['rotate'] : false;
        if ($rotate) {
            $turn = (isset($_POST['turn'])) ? $_POST['turn'] : false;
            if ($turn) {
                $angle = false;
                switch ($turn) {
                    case 1:
                        $angle = 90;
                        break;
                    case 2:
                        $angle = -90;
                        break;
                    case 3:
                        $angle = 180;
                        break;
                }
                if ($angle) {
                    $this->rotateImage($image_id, $angle);
                    $this->success = 'Image rotated.';
                }
            }
        }

        // Image Flip
        $flip = (isset($_POST['flip'])) ? $_POST['flip'] : false;
        if ($flip) {
            $dir = (isset($_POST['dir'])) ? $_POST['dir'] : false;
            if ($dir) {
                $axis = false;
                switch ($dir) {
                    case 1:
                        $axis = 'x';
                        break;
                    case 2:
                        $axis = 'y';
                        break;
                }
                if ($axis) {
                    $this->flipImage($image_id, $axis);
                    $this->success = 'Image flipped.';
                }
            }
        }

        // Image Edit Display
        $this->at->config_max_width = GALLERIE_MAX_WIDTH;
        $this->at->config_max_height = GALLERIE_MAX_HEIGHT;
        $modified = false;
        if (file_exists(GALLERIE_ROOT . GALLERIE_MODIFIED . $image['file'])) {
            $modified = true;
        }
        $this->at->modified = $modified;
        $image['display'] = GALLERIE_PATH . GALLERIE_DISPLAY . rawurlencode($image['file']) . '?t=' . time();
        $image['thumb'] = GALLERIE_PATH . GALLERIE_THUMBS . rawurlencode($image['file']). '?t=' . time();
        $this->at->image = $image;
        $content = $this->at->render('image');

        // Output
        return $this->outputAdmin($content);
    }

    /**
     * Admin log out controller
     */
    public function adminLogout()
    {
        unset($_SESSION['gallerie_auth']);
        $this->auth = false;
    }

    /**
     * Admin log in controller
     * @return string
     * @throws Exception
     */
    public function adminLogin()
    {
        $content = $this->at->render('login');
        $this->at->content = $content;
        return $this->outputAdmin($content);
    }

    /**
     * @param $content string Admin output content
     * @return string
     * @throws Exception
     */
    private function outputAdmin($content)
    {
        $this->at->error = $this->error;
        $this->at->success = $this->success;
        $this->at->content = $content;
        $this->at->gallery_path = GALLERIE_PATH_DISPLAY;
        $this->at->auth = $this->auth;
        $out = $this->at->render('main');
        return $out;
    }

    /**
     * Dropzone AJAX handler
     * @return bool
     */
    public function ajaxUpload()
    {
        $gallery = (isset($_POST['gallery'])) ? $_POST['gallery'] : false;
        $file = (isset($_FILES['file'])) ? $_FILES['file'] : false;
        if (!$gallery || !$file) {
            header("HTTP/1.0 424 Failed Dependency");
            return false;
        }
        // Check mime
        $mime_ok = array('image/png', 'image/jpeg', 'image/pjpeg', 'image/gif');
        $tmp = $file['tmp_name'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        if (!in_array($mime, $mime_ok)) {
            header("HTTP/1.0 415 Unsupported Media Type");
            return false;
        }
        // Check exists
        $name = $file['name'];
        $path_original = GALLERIE_ROOT . GALLERIE_ORIGINALS;
        $original =  $path_original . $name;
        if (file_exists($original)) {
            $c = 1;
            $parts = explode('.', $original);
            $ext = array_pop($parts);
            $name_original = implode($parts);
            $name_new = $name_original . '-' . str_pad($c, 4, '0', STR_PAD_LEFT) . '.' . $ext;
            while (file_exists($name_new)) {
                $c++;
                $name_new = $name_original . '-' . str_pad($c, 4, '0', STR_PAD_LEFT) . '.' . $ext;
            }
            $original = $name_new;
        }
        // Move file to originals dir
        move_uploaded_file($tmp, $original);
        // INSERT image in DB
        $filename = str_replace('\\', '/', $original);
        $filename = explode('/', $filename);
        $filename = array_pop($filename);
        $stmt = $this->db->prepare('INSERT INTO images (gallery_id, file, mime, title, sort) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute(array($gallery, $filename, $mime, '', 0));
        $image_id = $this->db->lastInsertId();
        // Build Images
        $this->rebuildImages($image_id);
        return true;
    }

    /**
     * Rebuild all images from config
     * @return bool
     */
    public function ajaxRebuildAllImages()
    {
        $a = (isset($_POST['a'])) ? $_POST['a'] : false;
        if (!$a) {
            return false;
        }
        switch ($a) {
            case 'get':
                $images = $this->getImages();
                echo json_encode($images);
                break;
            case 'rebuild':
                $id = (isset($_POST['id'])) ? $_POST['id'] : false;
                if (!$id) {
                    return false;
                }
                $this->rebuildImages($id);
                echo $id;
                break;
        }
    }

    /**
     * Get gallery data
     * @param $gallery_id int The gallery ID
     * @return bool|mixed
     */
    public function getGallery($gallery_id)
    {
        $stmt = $this->db->prepare('SELECT * FROM galleries WHERE id = ?');
        $stmt->execute(array($gallery_id));
        $gallery = $stmt->fetch();
        if (!$gallery) {
            return false;
        }
        $stmt = $this->db->prepare('SELECT * FROM images WHERE gallery_id = ? ORDER BY sort ASC, title ASC, file ASC');
        $stmt->execute(array($gallery_id));
        $images = $stmt->fetchAll();

        $gallery['images'] = $images;
        return $gallery;
    }

    /**
     * Get galleries data
     * @return array
     */
    public function getGalleries()
    {
        $stmt = $this->db->prepare('SELECT * FROM galleries ORDER BY sort ASC, name ASC');
        $stmt->execute();
        $res = $stmt->fetchAll();
        if ($res) {
            foreach ($res as $i => $r) {
                $stmt = $this->db->prepare('SELECT COUNT(*) AS count FROM images WHERE gallery_id = ?');
                $stmt->execute(array($r['id']));
                $img = $stmt->fetch();
                $res[$i]['count'] = $img['count'];
                $stmt = $this->db->prepare('SELECT file FROM images WHERE gallery_id = ? ORDER BY sort ASC, title ASC, file ASC LIMIT 1');
                $stmt->execute(array($r['id']));
                $img = $stmt->fetch();
                $res[$i]['idx_img'] = $img['file'];
            }
        }
        return $res;
    }

    /**
     * Add gallery to database
     * @param $name string Gallery name
     * @return array|bool
     */
    private function addGallery($name)
    {
        if (!$name) {
            return false;
        }
        $name = trim($name);
        // Check exists
        $stmt = $this->db->prepare('SELECT * FROM galleries WHERE name = ?');
        $stmt->execute(array($name));
        $res = $stmt->fetchAll();
        if ($res) {
            return false;
        }
        $stmt = $this->db->prepare('INSERT INTO galleries (name, sort) VALUES (?, ?)');
        $res = $stmt->execute(array($name, 0));
        return $res;
    }

    /**
     * Rename a gallery
     * @param $name string New gallery name
     * @param $id int The gallery ID
     * @return array|bool
     */
    private function renameGallery($name, $id)
    {
        $name = trim($name);
        $id = (int) $id;
        if (!$name || !$id) {
            return false;
        }
        // Check exists
        $stmt = $this->db->prepare('SELECT * FROM galleries WHERE name = ?');
        $stmt->execute(array($name));
        $res = $stmt->fetchAll();
        if ($res) {
            return false;
        }
        $stmt = $this->db->prepare('UPDATE galleries SET name = ? WHERE id = ?');
        $res = $stmt->execute(array($name, $id));
        return $res;
    }

    /**
     * Sort galleries
     * @param $order array Array of gallery IDs in sort order
     * @return bool
     */
    private function sortGalleries($order)
    {
        if (!$order) {
            return false;
        }
        $c = 1;
        foreach ($order as $id) {
            $stmt = $this->db->prepare('UPDATE galleries SET sort = ? WHERE id = ?');
            $stmt->execute(array($c, $id));
            $c++;
        }
        return true;
    }

    /**
     * Get image data
     * @param $id int The image ID
     * @return mixed
     */
    private function getImage($id)
    {
        $stmt = $this->db->prepare('SELECT * FROM images WHERE id = ?');
        $stmt->execute(array($id));
        $res = $stmt->fetch();
        return $res;
    }

    /**
     * Get all images data
     * @return array
     */
    private function getImages()
    {
        $stmt = $this->db->prepare('SELECT * FROM images ORDER BY id ASC');
        $stmt->execute();
        $res = $stmt->fetchAll();
        return $res;
    }

    /**
     * Sort images
     * @param $order array Array of image IDs in sort order
     * @return bool
     */
    private function sortImages($order)
    {
        if (!$order) {
            return false;
        }
        $c = 1;
        foreach ($order as $id) {
            $stmt = $this->db->prepare('UPDATE images SET sort = ? WHERE id = ?');
            $stmt->execute(array($c, $id));
            $c++;
        }
        return true;
    }

    /**
     * Write image to disk
     * @param $image Resource The GD image resource
     * @param $tofile string The output image file
     * @param $mime string Image mime type
     */
    private function writeImage($image, $tofile, $mime)
    {
        switch ($mime) {
            case 'image/jpeg':
            case 'image/pjpeg':
                imagejpeg($image, $tofile, GALLERIE_JPG_QUALITY);
                break;
            case 'image/gif':
                imagegif($image, $tofile);
                break;
            case 'image/png':
                imagepng($image, $tofile, GALLERIE_PNG_COMPRESSION);
                break;
        }
    }

    /**
     * Move image to gallery
     * @param $id int The image ID
     * @param $gallery_id int The gallery ID
     * @return bool
     */
    private function moveImage($id, $gallery_id)
    {
        $stmt = $this->db->prepare('UPDATE images SET gallery_id = ? WHERE id = ?');
        $res = $stmt->execute(array($gallery_id, $id));
        return $res;
    }

    /**
     * Delete an image (from disk and DB)
     * @param $id int The image ID
     */
    private function deleteImage($id)
    {
        $img = $this->getImage($id);
        @unlink(GALLERIE_ROOT . GALLERIE_ORIGINALS . $img['file']);
        @unlink(GALLERIE_ROOT . GALLERIE_DISPLAY . $img['file']);
        @unlink(GALLERIE_ROOT . GALLERIE_IDX_THUMBS . $img['file']);
        @unlink(GALLERIE_ROOT . GALLERIE_THUMBS . $img['file']);
        @unlink(GALLERIE_ROOT . GALLERIE_MODIFIED . $img['file']);
        $stmt = $this->db->prepare('DELETE FROM images WHERE id = ?');
        $stmt->execute(array($id));
    }

    /**
     * Set image title
     * @param $id int The image ID
     * @param $title string The image title
     * @return bool
     */
    private function imageTitle($id, $title)
    {
        $stmt = $this->db->prepare('UPDATE images SET title = ? WHERE id = ?');
        $res = $stmt->execute(array($title, $id));
        return $res;
    }

    /**
     * Revert a modified image to original uploaded version
     * @param $id int The image ID
     * @return bool
     */
    private function revertImage($id)
    {
        $image = $this->getImage($id);
        if (!$image) {
            return false;
        }
        @unlink(GALLERIE_ROOT . GALLERIE_MODIFIED . $image['file']);
        $this->rebuildImages($id);
        return true;
    }

    /**
     * Crop an image
     * @param $id int The image ID
     * @param $crop array Crop data from jCrop
     * @return bool
     */
    private function cropImage($id, $crop)
    {
        $row = $this->getImage($id);
        if (!$row) {
            return false;
        }
        // Get file info
        $file = $row['file'];
        $mime = $row['mime'];
        $original = $this->getOriginal($file);
        $osize = getimagesize($original);
        // Crop data setup
        $pc = $osize[0] / $crop[0];
        $x = round($crop[2] * $pc);
        $y = round($crop[3] * $pc);
        $nw = round($crop[6] * $pc);
        $nh = round($crop[7] * $pc);
        // GD
        $image = $this->gdImage($original, $mime);
        $image_cropped = imagecreatetruecolor($nw, $nh);
        imagecopyresampled($image_cropped, $image, 0, 0, $x, $y, $nw, $nh, $nw, $nh);
        $modified = GALLERIE_ROOT . GALLERIE_MODIFIED . $file;
        $this->writeImage($image_cropped, $modified, $mime);
        $this->rebuildImages($id);
        return true;
    }

    /**
     * Rotate an image
     * @param $id int The image ID
     * @param $angle int The rotation angle
     * @return bool
     */
    private function rotateImage($id, $angle)
    {
        $row = $this->getImage($id);
        if (!$row) {
            return false;
        }
        // Get file info
        $file = $row['file'];
        $mime = $row['mime'];
        $original = $this->getOriginal($file);
        // GD
        $image = $this->gdImage($original, $mime);
        $image_rotated = imagerotate($image, $angle, 0);
        $modified = GALLERIE_ROOT . GALLERIE_MODIFIED . $file;
        $this->writeImage($image_rotated, $modified, $mime);
        $this->rebuildImages($id);
        return true;
    }

    /**
     * Flip an image
     * @param $id int The image ID
     * @param $axis string (x or y) The flip axis
     * @return bool
     */
    private function flipImage($id, $axis)
    {
        $row = $this->getImage($id);
        if (!$row) {
            return false;
        }
        // Get file info
        $file = $row['file'];
        $mime = $row['mime'];
        $original = $this->getOriginal($file);
        $osize = getimagesize($original);
        $w = $osize[0];
        $h = $osize[1];
        $nw = $w;
        $nh = $h;
        $x = 0;
        $y = 0;
        switch ($axis) {
            case 'x':
                $x = $w - 1;
                $nw = -$w;
                break;
            case 'y':
                $y = $h - 1;
                $nh = -$h;
                break;
        }
        // GD
        $image = $this->gdImage($original, $mime);
        $image_flipped = imagecreatetruecolor($w, $h);
        imagecopyresampled($image_flipped, $image, 0, 0, $x, $y, $w, $h, $nw, $nh);
        $modified = GALLERIE_ROOT . GALLERIE_MODIFIED . $file;
        $this->writeImage($image_flipped, $modified, $mime);
        $this->rebuildImages($id);
        return true;
    }

    /**
     * Get the working original (original or modified)
     * @param $filename string Image file name
     * @return string
     */
    private function getOriginal($filename)
    {
        $original = GALLERIE_ROOT . GALLERIE_ORIGINALS . $filename;
        // Check Modified
        $modified_file = GALLERIE_ROOT . GALLERIE_MODIFIED . $filename;
        if (file_exists($modified_file)) {
            $original = $modified_file;
        }
        return $original;
    }

    /**
     * Create GD image resource
     * @param $file string The image file name
     * @param $mime string The image mime type
     * @return null|resource
     */
    private function gdImage($file, $mime)
    {
        $image = null;
        switch ($mime) {
            case 'image/jpeg':
            case 'image/pjpeg':
                $image = imagecreatefromjpeg($file);
                break;
            case 'image/gif':
                $image = imagecreatefromgif($file);
                break;
            case 'image/png':
                $image = imagecreatefrompng($file);
                break;
        }
        return $image;
    }

    /**
     * Rebuild image images (display, thumb, etc.)
     * @param $id int The image ID
     * @return bool
     */
    private function rebuildImages($id)
    {
        // Get from DB
        $row = $this->getImage($id);
        if (!$row) {
            return false;
        }
        // Get file info
        $file = $row['file'];
        $mime = $row['mime'];
        $original = $this->getOriginal($file);
        // GD
        $image = $this->gdImage($original, $mime);
        $img_info = getimagesize($original);
        $ow = $img_info[0];
        $oh = $img_info[1];
        $w = $ow;
        $h = $oh;
        if ($w > GALLERIE_MAX_WIDTH) {
            $r = $w / GALLERIE_MAX_WIDTH;
            $w = GALLERIE_MAX_WIDTH;
            $h = $h / $r;
        }
        if ($h > GALLERIE_MAX_HEIGHT) {
            $r = $h / GALLERIE_MAX_HEIGHT;
            $h = GALLERIE_MAX_HEIGHT;
            $w = $w / $r;
        }
        $w = round($w);
        $h = round($h);
        $image_display = imagecreatetruecolor($w, $h);
        imagecopyresampled($image_display, $image, 0, 0, 0, 0, $w, $h, $ow, $oh);
        $image_write = GALLERIE_ROOT . GALLERIE_DISPLAY . $file;
        $this->writeImage($image_display, $image_write, $mime);
        // Create IDX THUMB image (from display just created)
        $image_idx_thumb = imagecreatetruecolor(GALLERIE_IDX_THUMB_SIZE, GALLERIE_IDX_THUMB_SIZE);
        $x = $y = 0;
        $nw = $w;
        $nh = $h;
        if ($w >= $h) {
            $x = ($w / 2) - ($h / 2);
            $nw = $h;
        } else {
            $y = ($h / 2) - ($w / 2);
            $nh = $w;
        }
        imagecopyresampled($image_idx_thumb, $image_display, 0, 0, $x, $y, GALLERIE_IDX_THUMB_SIZE, GALLERIE_IDX_THUMB_SIZE, $nw, $nh);
        $image_write = GALLERIE_ROOT . GALLERIE_IDX_THUMBS . $file;
        $this->writeImage($image_idx_thumb, $image_write, $mime);
        // Create THUMB image (from idx_thumb just created)
        $image_thumb = imagecreatetruecolor(GALLERIE_THUMB_SIZE, GALLERIE_THUMB_SIZE);
        imagecopyresampled($image_thumb, $image_idx_thumb, 0, 0, 0, 0, GALLERIE_THUMB_SIZE, GALLERIE_THUMB_SIZE, GALLERIE_IDX_THUMB_SIZE, GALLERIE_IDX_THUMB_SIZE);
        $image_write = GALLERIE_ROOT . GALLERIE_THUMBS . $file;
        $this->writeImage($image_thumb, $image_write, $mime);
        return true;
    }

    /**
     * Admin diagnostic
     * @return array
     */
    private function diagnostic()
    {
        $errors = array();
        if (!is_writable(GALLERIE_ROOT . GALLERIE_DATA)) {
            $errors[] = GALLERIE_ROOT . GALLERIE_DATA . ' is not writable.';
        }
        if (!is_writable(GALLERIE_ROOT . GALLERIE_ORIGINALS)) {
            $errors[] = GALLERIE_ROOT . GALLERIE_ORIGINALS . ' is not writable.';
        }
        if (!is_writable(GALLERIE_ROOT . GALLERIE_DISPLAY)) {
            $errors[] = GALLERIE_ROOT . GALLERIE_DISPLAY . ' is not writable.';
        }
        if (!is_writable(GALLERIE_ROOT . GALLERIE_THUMBS)) {
            $errors[] = GALLERIE_ROOT . GALLERIE_THUMBS . ' is not writable.';
        }
        if (!is_writable(GALLERIE_ROOT . GALLERIE_IDX_THUMBS)) {
            $errors[] = GALLERIE_ROOT . GALLERIE_IDX_THUMBS . ' is not writable.';
        }
        if (!is_writable(GALLERIE_ROOT . GALLERIE_MODIFIED)) {
            $errors[] = GALLERIE_ROOT . GALLERIE_MODIFIED . ' is not writable.';
        }
        $tmp_dir = sys_get_temp_dir();
        if (!is_writable($tmp_dir)) {
            $errors[] = 'Upload temp directory (' . $tmp_dir . ') is not writable.';
        }
        $file_uploads = ini_get('file_uploads');
        if (!$file_uploads) {
            $errors[] = 'File uploads are disabled by PHP config.';
        }
        if (!function_exists('finfo_open')) {
            $errors[] = 'Fileinfo extension is not installed.';
        }
        if (!function_exists('imagecreatetruecolor')) {
            $errors[] = 'GD extension is not installed.';
        }
        if (!defined('PDO::ATTR_DRIVER_NAME')) {
            $errors[] = 'PDO extension is not installed.';
        }
        $pdo_drivers = PDO::getAvailableDrivers();
        if (!in_array('sqlite', $pdo_drivers)) {
            $errors[] = 'SQLite driver for PDO is not installed.';
        }
        return $errors;
    }

    /**
     * Output galleries controller
     * @return string
     */
    public function galleries()
    {
        $galleries = $this->getGalleries();
        return $this->outputGalleries($galleries);
    }

    /**
     * Output galleries controller display/template
     * @param $galleries array Array of gallery data
     * @return string
     * @throws Exception
     */
    private function outputGalleries($galleries)
    {
        $none = false;
        $c = 0;
        foreach ($galleries as $d) {
            $c += $d['count'];
        }
        if (!$c) {
            $none = true;
        } else {
            foreach ($galleries as $i => $d) {
                $galleries[$i]['idx_img'] = GALLERIE_PATH . GALLERIE_IDX_THUMBS . rawurlencode($d['idx_img']);
            }
        }
        $this->ot->none = $none;
        $this->ot->path = GALLERIE_PATH_DISPLAY;
        $this->ot->idx_thumb_size = GALLERIE_IDX_THUMB_SIZE;
        $this->ot->galleries = $galleries;
        $out = $this->ot->render('galleries');
        return $out;
    }

    /**
     * Output gallery controller
     * @param $gallery_id int The gallery ID
     * @return string
     */
    public function gallery($gallery_id)
    {
        $gallery = $this->getGallery($gallery_id);
        return $this->outputGallery($gallery);
    }

    /**
     * Output gallery controller display/template
     * @param $gallery array Gallery data
     * @return string
     * @throws Exception
     */
    private function outputGallery($gallery)
    {
        $this->ot->path = GALLERIE_PATH_DISPLAY;
        $this->ot->thumb_size = GALLERIE_THUMB_SIZE;
        if ($gallery) {
            foreach ($gallery['images'] as $i => $d) {
                $gallery['images'][$i]['url'] = GALLERIE_PATH . GALLERIE_DISPLAY . rawurlencode($d['file']);
                $gallery['images'][$i]['img'] = GALLERIE_PATH . GALLERIE_THUMBS . rawurlencode($d['file']);
            }
        }
        $this->ot->gallery = $gallery;
        $out = $this->ot->render('gallery');
        return $out;
    }

    /**
     * Helper - Convert PHP Config size to bytes
     * @param $s string Config value
     * @return int|string
     */
    private function convertSizeStringToBytes($s)
    {
        if (is_numeric($s)) {
            return $s;
        }
        $suff = substr($s, -1);
        $val = substr($s, 0, -1);
        switch(strtoupper($suff)){
            case 'G':
                $val *= 1024;
            case 'M':
                $val *= 1024;
            case 'K':
                $val *= 1024;
                break;
        }
        return $val;
    }

    /**
     * Helper - Get max upload file size in bytes
     * @return mixed
     */
    private function getMaxFileUploadSize()
    {
        return min($this->convertSizeStringToBytes(ini_get('post_max_size')),
            $this->convertSizeStringToBytes(ini_get('upload_max_filesize')));
    }

    /**
     * Helper - Get max upload file size in MB
     * @return float
     */
    private function getMaxMb()
    {
        $max_bytes = $this->getMaxFileUploadSize();
        $max_mb = round($max_bytes / 1024 / 1024, 2);
        return $max_mb;
    }

    /**
     * Helper - Gallerie debug log
     * @param $msg
     */
    private function debug($msg) {
        file_put_contents(GALLERIE_ROOT . 'debug.log', '[' . date('Y-m-d H:i:s') . ']' . "\t" . $msg . PHP_EOL, FILE_APPEND);
    }
}