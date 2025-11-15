<?php
/** @var array $_ */
?>

<link rel="stylesheet" href="<?php echo \OC::$server->getURLGenerator()->linkTo('verein', 'js/dist/style.css'); ?>">

<div id="app"></div>

<?php
script('verein', 'dist/nextcloud-verein');
?>
