<?php
    require_once "Manager.php";
    $manager = Manager::getManager();
?>

<html>
<head>
    <title>Simple file manager</title>
    <link rel="stylesheet" href="css/bootstrap.css">
    <link rel="stylesheet" href="css/bootstrap-glyph-icon.css">
</head>
<body>
<div class="main">
<?php $manager->displayCatalog(); ?>
</div>
</body>
</html>
