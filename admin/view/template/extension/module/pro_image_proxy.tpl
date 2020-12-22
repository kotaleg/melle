<?php echo $header; ?><?php echo $column_left; ?>

<div id="content">
    <div id="<?php echo $codename; ?>"></div>
</div>

<script type="text/javascript">
window.__<?php echo $codename; ?>__ = <?php echo $state; ?>;
</script>

<?php foreach ($pro_scripts as $script) { ?>
<script type="text/javascript" src="<?php echo $script; ?>"></script>
<?php } ?>

<?php echo $footer; ?>
