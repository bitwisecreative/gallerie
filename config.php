<?php
/**
 * Gallerie Config
 */

// MD5 hashed password for the admin area
// You can get an MD5 hash here: http://www.md5.net/md5-generator/
define('GALLERIE_PASS', '5f4dcc3b5aa765d61d8327deb882cf99');

// Image size preferences
define('GALLERIE_THUMB_SIZE', 120); // Square
define('GALLERIE_IDX_THUMB_SIZE', 240); // Square
define('GALLERIE_MAX_WIDTH', 1920); // Max resized (display) width
define('GALLERIE_MAX_HEIGHT', 1080); // Max resized (display) height
define('GALLERIE_JPG_QUALITY', 88); // 0-100
define('GALLERIE_PNG_COMPRESSION', 6); // 1-9

// Web path to gallerie folder
define('GALLERIE_PATH', '/gallerie/'); // Path to gallerie folder

// Web path to gallery display
define('GALLERIE_PATH_DISPLAY', '/gallerie/'); // Path to gallerie display (public gallery index)

// Output Templates
define('GALLERIE_TPL_PATH', 'assets/templates/');


/*****************************************************************************/
// You shouldn't need to modify these
define('GALLERIE_VERSION', '1');
define('GALLERIE_ROOT', str_replace('\\', '/', __DIR__) . '/');
define('GALLERIE_DATA', 'data/gallerie.sqlite');
define('GALLERIE_ORIGINALS', 'images/originals/');
define('GALLERIE_DISPLAY', 'images/display/');
define('GALLERIE_THUMBS', 'images/thumbs/');
define('GALLERIE_IDX_THUMBS', 'images/idx_thumbs/');
define('GALLERIE_MODIFIED', 'images/modified/');
define('GALLERIE_ADMIN_TPL_PATH', 'admin/assets/templates/');
