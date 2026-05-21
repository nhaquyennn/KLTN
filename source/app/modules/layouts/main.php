<?php 
require_once $header; // header động
?>

<div class="app">

    <?php require_once ROOT_PATH . '/modules/layouts/sidebar.php'; ?>

    <div id="app">
        <?php require_once $view; ?>
    </div>

</div>

<?php require_once ROOT_PATH . '/modules/layouts/footer.php'; ?>