<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Gallerie Admin</title>
<link rel="icon" href="favicon.ico" type="image/x-icon" />
<link rel="stylesheet" href="assets/css/normalize.css" type="text/css" />
<link rel="stylesheet" href="assets/css/gallerie.css" type="text/css" />
<link rel="stylesheet" href="assets/theme/jquery-ui.css" type="text/css" />
<script src="assets/js/jquery-1.11.2.min.js"></script>
<script src="assets/js/jquery-ui.min.js"></script>
<script src="assets/js/jquery.ui.touch-punch.min.js"></script>
<script src="assets/js/gallerie.js"></script>
</head>
<body>
<div id="page">

<div id="nav">
    <ul>
        <li><a href="<?php echo $gallery_path; ?>">View Gallery</a></li>
        <?php if ($auth): ?>
        <li><a href="index.php?a=logout">Log Out</a></li>
        <?php endif; ?>
    </ul>
</div>

<div id="header">
    <div id="logo"><a href="index.php"><img src="assets/img/gallerie_logo_32.png" alt="Gallerie Admin" /></a></div>
    <h1><a href="index.php">Gallerie Admin</a></h1>
</div>

<?php if ($error): ?>
<div class="error"><?php echo $error; ?></div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="success"><?php echo $success; ?></div>
<?php endif; ?>

<?php echo $content; ?>

</div>
</body>
</html>
