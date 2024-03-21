<?php
require __DIR__ . '/vendor/autoload.php';

spl_autoload_register(function ($class) {
    // Chuyển đổi namespace và tên lớp thành đường dẫn tệp
    $model = __DIR__ . '/model/' . str_replace('\\', '/', $class) . '.php';
    $settings = __DIR__ . '/settings/' . str_replace('\\', '/', $class) . '.php';
  //var_dump($class);die;
    // Kiểm tra sự tồn tại của tệp và tải nó
    if (file_exists($model)) {
        require $model;
    }
    if (file_exists($settings)) {
        require $settings;
    }
});
