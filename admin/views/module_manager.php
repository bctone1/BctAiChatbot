<?php
$moduleManager = new \Modules\ModuleManager();
$modules = $moduleManager->getModules();

//echo '<pre>'; print_r($modules); echo '</pre>';


// Pagination settings
$items_per_page = 25;
$total_modules = count($modules);
$total_pages = ceil($total_modules / $items_per_page);
$current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
$start_index = ($current_page - 1) * $items_per_page;
$modules_to_display = array_slice($modules, $start_index, $items_per_page);
// echo '<pre>'; print_r($modules_to_display); echo '</pre>';


wp_enqueue_style('module-manager-css', plugin_dir_url(__DIR__) . 'css/module_manager.css');
wp_enqueue_script('module-manager-js', plugin_dir_url(__DIR__) . 'js/module_manager.js', array('jquery'), null, true);

?>

<div class="wrap">
    <h1>모듈 관리</h1>
    <div class="module-grid">
        <?php foreach ($modules_to_display as $moduleName => $module): ?>
        <!-- <div class="module-item <?php echo $module->isActive() ? 'active' : ''; ?>" data-module="<?php echo esc_attr($moduleName); ?>"> -->
        <div class="module-item <?php echo $module->isActive() ? 'active' : ''; ?>" data-module="<?php echo esc_html($module->getName()); ?>">
            <!-- <img src="<?php echo plugin_dir_url(__FILE__) . 'images/' . $moduleName . '.png'; ?>" alt="<?php echo esc_html($module->getName()); ?>" /> -->
            <div class="module-name"><?php echo esc_html($module->getName()); ?></div>
            <div class="module-description"><?php echo esc_html($module->getDescription()); ?></div>
        </div>
        <?php endforeach; ?>
    </div>

    <?php if ($total_pages > 1): ?>
    <div class="pagination">
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
        <a href="<?php echo add_query_arg('paged', $i); ?>" class="<?php echo $i == $current_page ? 'current' : ''; ?>"><?php echo $i; ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>








