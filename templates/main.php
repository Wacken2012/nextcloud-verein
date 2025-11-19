<?php
/** @var array $_ */
?>

<link rel="stylesheet" href="<?php echo \OC::$server->getURLGenerator()->linkTo('verein', 'js/dist/style.css'); ?>">

<div id="app-content" role="main" tabindex="-1">
	<div id="app" tabindex="0"></div>
</div>

<?php
script('verein', 'dist/nextcloud-verein');
?>
