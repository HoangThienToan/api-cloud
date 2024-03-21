<?php
require '../autoload.php';

use Model\UserModel;
if (isset($_GET['emergency_code'])) {
    $emergency_code = $_GET['emergency_code'];
    $result = new UserModel();
    date_default_timezone_set('Asia/Ho_Chi_Minh');
    $time = date("Y-m-d h:i:s");
    $adjusted_time = date("Y-m-d h:i:s", strtotime($time) - 295);
    $users = $result->get("`emergency_code` = '$emergency_code'");
    $random = strtotime($time) . substr(str_shuffle(str_repeat('0123456789', mt_rand(1, 6))), 1, 6);
    if ($users) {
        $user = $users[0];
        $tokenSubmit = $user['remember_token'];
        $au_domain = $user['au_domain'];
        $data = array(
            'email_verified_at' => $adjusted_time,    'emergency_code' => null, 'condition' => "`emergency_code` = '$emergency_code'"
        );
        $result->update($data);

        header("Location: https://$au_domain/wp-admin/admin.php?page=edu2work-admin&Token=$tokenSubmit");
    } else {
        $return = "Emergency code does not exist";
    }
    echo $return;
}
