<?php
require __DIR__ . '/autoload.php';
date_default_timezone_set('Asia/Ho_Chi_Minh');

use Settings\Treatment;
use Model\UserModel;
use Model\FilesModel;

define('URL_CLOUD', 'https://f005.backblazeb2.com/file/servertoidayhoc');

function giveBack($data)
{
	echo json_encode($data);die;
}

function forgot($data)
{
	if ($data) {
		$email = $data->data->email;
		$domain = $data->domain;
		if (!$email) {
			giveBack("Email error.");
		}
		$result = new UserModel();
		$users = $result->get("`email` = '$email'");

		if ($users) {
			$user = $users[0];
			$name = $user['name'];
			$time = date("Y-m-d h:i:s");
			$random = strtotime($time) . substr(str_shuffle(str_repeat('0123456789', mt_rand(1, 6))), 1, 6);
			$emergency_code = strtotime($time) . bin2hex(openssl_random_pseudo_bytes(12));
			$user = $result->get("`domain` LIKE CONCAT('%', '$domain', '%')");
			if ($user[0]['email'] == $email || !$user) {
				$data = array('remember_token' => $random, 'email_verified_at' =>$time, 'updated_at' => $time, 'emergency_code' => $emergency_code, 'au_domain' => $domain, 'condition' => "`email` = '$email'");
				$result->update($data);
				$return = Treatment::emailTemplate($email, $name, $random, $domain, $emergency_code);
			}  else {
				$data = array('remember_token' => $random, 'email_verified_at' => $time, 'emergency_code' => $emergency_code, 'au_domain' => $domain, 'condition' => "`email` = '$email'");
				$result->update($data);
				$return = Treatment::emailTemplate($email, $name, null, $domain, $emergency_code);
			}
		} else {
			$return = "Account does not exist!";
		}
		$result->close();
		giveBack($return);
	}
}


function signUp($data)
{
	if ($data) {
		$name = $data->data->name;
		$email = $data->data->gmail;
		$domain = $data->domain;
		if (strpos($domain, 'localhost') !== false) {
			giveBack("Local server cannot participate in cloud operations!");
		}
		if (!$email) {
			giveBack("Email error.");
		}
		$password = $data->data->password;

		$passwordHash = password_hash($password, PASSWORD_DEFAULT);
		//password_verify($password, $passwordHashFromDatabase);
		$userModel = new UserModel();
		$users = $userModel->get("`email` = '$email'");
		if ($users) {
			$user = $users[0];
			if ($user['remember_token']) {
				$return = "Email already exists.";
			} else {
				$domainArr = json_decode($user['domain'], true);
				if (strpos($user['domain'], $domain) === false) {
					array_push($domainArr, $domain);
				}
				$domainArr = json_encode($domainArr);
				$time = date("Y-m-d h:i:s");
				$random = strtotime($time) . substr(str_shuffle(str_repeat('0123456789', mt_rand(1, 6))), 1, 6);
				$emergency_code = strtotime($time) . bin2hex(openssl_random_pseudo_bytes(12));
				$data = array(
					'domain' => $domainArr, 'name' => $name, 'password' => $passwordHash, 'remember_token' => $random, 'email_verified_at' => $time, 'emergency_code' => $emergency_code, 'au_domain' => $domain, 'condition' => "`email` = '$email'"
				);

				$userModel->update($data);
				$return = Treatment::emailTemplate($email, $name, $random, $domain, $emergency_code);
			}
		} else {
			$domainArr[] = $domain;
			$domainArr = json_encode($domainArr);
			$time = date("Y-m-d h:i:s");
			$random = strtotime($time) . substr(str_shuffle(str_repeat('0123456789', mt_rand(1, 5))), 1, 5);
			$key = strtotime($time) . bin2hex(openssl_random_pseudo_bytes(10));
			$emergency_code = strtotime($time) . bin2hex(openssl_random_pseudo_bytes(12));

			$user = $userModel->get("`domain` LIKE CONCAT('%', '$domain', '%')");

			if ($user) {
				//$return = "If the domain has been registered, please click forget token if it is your account. If not, contact the old owner of the domain or contact customer service for help.";
				$data = array(
					'type' => 'user', 'data_limit' => 2147483648, 'server_cloud' => 'B2', 'created_at' => $time, 'key' => $key, 'name' => $name, 'email' => $email, 'password' => $passwordHash,
					'remember_token' => $random, 'email_verified_at' => $time, 'emergency_code' => $emergency_code, 'au_domain' => $domain
				);
				$userModel->insert($data);
				$return = Treatment::emailTemplate($email, $name, null, $domain, $emergency_code);
				$return = "The domain name already exists. Confirm your domain with your email, a message has been sent!";
			} else {
				$data = array(
					'type' => 'user', 'data_limit' => 2147483648, 'domain' => $domainArr, 'server_cloud' => 'B2', 'created_at' => $time, 'key' => $key, 'name' => $name, 'email' => $email, 'password' => $passwordHash,
					'remember_token' => $random, 'email_verified_at' => $time, 'emergency_code' => $emergency_code, 'au_domain' => $domain
				);
				$userModel->insert($data);
				$return = Treatment::emailTemplate($email, $name, $random, $domain, $emergency_code);
			}
		}
		$userModel->close();
		giveBack($return);
	}
}




function verify($data)
{
	if (isset($data)) {
		$domain = $data->domain;
		$token = $data->token;
		$result = new UserModel();
		$users = $result->get("`au_domain` = '$domain' AND `remember_token` = '$token'");
		$user = $users[0];
		if ($user) {
			$timeB = $user['email_verified_at'];
			$timeE = date("Y-m-d h:i:s");
			//echo json_encode($data);die;
			if ((strtotime($timeE) - strtotime($timeB)) < 300) {
				$key = $user['key'];
				if (strpos($user['domain'], $domain) === false) {
					$users = $result->get("`domain` LIKE CONCAT('%', '$domain', '%')");
					if ($users) {
						$domainArrDeprive = json_decode($users[0]['domain'], true);
						if (in_array($domain, $domainArrDeprive) ) {
							$userid = $users[0]['id'];
							$domainArrDeprive = json_encode(array_diff($domainArrDeprive, [$domain]));
							$data = array(
								'domain' => $domainArrDeprive,
								'condition' => "`id` = '$userid'"
							);
							$result->update($data);
						}
					}
					$domainArrAccept = $user['domain'] ? json_decode($user['domain'], true) : array();
					array_push($domainArrAccept, $domain);
					$domainArrAccept = json_encode($domainArrAccept);
					$data = array(
						'domain' => $domainArrAccept,
						'remember_token' => null,
						'email_verified_at' => null,
						'emergency_code' => null,
						'au_domain' => null,
						'updated_at' => $timeE,
						'condition' => "remember_token = '$token'"
					);
				} else {
					$data = array(
						'remember_token' => null,
						'email_verified_at' => null,
						'emergency_code' => null,
						'au_domain' => null,
						'updated_at' => $timeE,
						'condition' => "remember_token = '$token'"
					);
				}
				$result->update($data);
				$return = array('key' => $key, 'notification' => "Verified successfully!", 'cloud_link' => URL_CLOUD);
			} else if ((strtotime($timeE) - strtotime($timeB)) > 300) {
				$return = 'Expired token!. We have sent a new code, please enter again.';
			}
		} else {
			$return = 'Invalid token. Please re-enter.';
		}
		$result->close();
		giveBack($return);
	}
}
function uploadFile($url, $info_array, $userid, $filename, $domain)
{

	$validData = Treatment::checkFileExistence($url);
	$checkcontent = Treatment::checkFileExistence($info_array['url']);

	$return = '';
	if ($checkcontent !== false) {
		$return = "Path already exists.";
	} elseif ($validData === false) {
		$return = "Invalid data. Review url: $url";
	} else {
		$fileSize = Treatment::curl_filesize($url);
		$storage = Treatment::used_storage_capacity($domain);
		$limit = Treatment::storage_limit($userid);
		
		if (($fileSize + $storage) < $limit) {

					$file_info = Treatment::replace_default_handle_upload($filename, $url);

					$ContentLength = $file_info->getContentLength();
					$return= '';
					if ($ContentLength) {
						$currentDateTime = date('Y-m-d H:i:s');
						$info_array["size"] = $ContentLength;
						$info = json_encode($info_array);
						$data = array(
							'user_id' => $userid, 'domain' => $domain, 'info' => $info, 'created_at' => $currentDateTime
						);
						$modelFiles = new FilesModel();
						$modelFiles->insert($data);
						$return = "Complete push up.";
					} else {
						$return = "An error occurred during the upload process!";
					}
		} else {
			$return = "Your cloud storage is full!";
		}
	}
	giveBack($return);
}

function deleteFile($filename, $userid) {

	$currentDateTime = date('Y-m-d H:i:s');
	$Treatment = new Treatment();
	$modelFiles = new FilesModel();
	$return = $Treatment->deleteData($filename);
	if ($return) {
		$data = array(
			'datedTimeDel' => $currentDateTime, 'condition' => "info LIKE CONCAT('%', '$filename', '%') AND user_id = '$userid'"
		);
		$modelFiles->update($data);
		$return = "Deleted file successfully!";
	} else {
		$return = "Deletion failed!";
	}
	$modelFiles->close();
	giveBack($return);
}
function data_backup($key) {
	$UserModel = new UserModel();
	$users = $UserModel->get("`key` = '$key'");
	$userid = $users[0]["id"];

	$FilesModel = new FilesModel();
	$Files = $FilesModel->get("`user_id` = '$userid' AND info LIKE CONCAT('%', '/backup/', '%') AND `datedTimeDel` IS NULL", "`id` DESC");
	giveBack($Files);
};


function dataProcessing()
{
	$postData  = file_get_contents('php://input');
	$data = json_decode($postData);

	//echo json_encode($data);die;
	$action = $data->action;
	if ($action === 'sign_up_cloud') {
		signUp($data);
	}
	if ($action === 'verify_cloud') {
		verify($data);
	}
	if ($action === 'forgot_cloud') {
		forgot($data);
	}

	$key = $data->key;
	if ($action === 'get_backup_file') {
		data_backup($key);
	}
	$domain = $data->domain;

	$filename = $data->filename;
	$url = $data->url;
	$info = $data->info;
	$info_array = json_decode($info, true);

	$result = new UserModel();
	$users = $result->get("`key` = '$key'");
	$user = $users[0];
	$userid = $user['id'];
	$permission = Treatment::permission($domain, $user);
	$return= '';
	if ($permission == "ok") {
		if ($action == "check_key_ajax_function") {
			$return = array('notification' => "Successful authentication", 'cloud_link' => URL_CLOUD);
		} else if ($action == "upload-attachment" || $action == "post_media_function") {

			uploadFile($url, $info_array, $userid, $filename, $domain);
		} else if ($action == "delete_file_cloud") {
			deleteFile($filename, $userid);
		}
	} else if ($permission === "Domain is incorrect!") {
		if ($action == "check_key_ajax_function") {
			$domainArr = json_decode($user['domain'], true);
			array_push($domainArr, $domain);
			$domainArr = json_encode($domainArr);
			$user_already_exist = $result->get("`domain` LIKE CONCAT('%', '$domain', '%')");
			if ($user_already_exist) {
				$email = $user['email'];
				$time = date("Y-m-d h:i:s");
				$random = strtotime($time) . substr(str_shuffle(str_repeat('0123456789', mt_rand(1, 5))), 1, 5);
				$key = strtotime($time) . bin2hex(openssl_random_pseudo_bytes(10));
				$emergency_code = strtotime($time) . bin2hex(openssl_random_pseudo_bytes(12));
				$data = array('remember_token' => $random, 'email_verified_at' => $time, 'emergency_code' => $emergency_code, 'au_domain' => $domain, 'condition' => "`email` = '$email'");
				$result->update($data);
				$return = Treatment::emailTemplate($user['email'], $user['name'], null, $domain, $emergency_code);
				$return = "The domain name already exists. Confirm your domain with your email, a message has been sent!";
			} else {
				$data = array(
					'domain' => $domainArr, 'condition' => "id = '$userid'"
				);
				$result->update($data);
				$return = array('notification' => "Successful authentication", 'cloud_link' => URL_CLOUD);
			}
		} else {
			$return = 'There was an incorrect intervention causing an error not to be handled!';
		}
	}
	$result->close();
	giveBack($return);
}



if ($_SERVER['REQUEST_METHOD'] === 'GET') {
	getData();
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
	dataProcessing();
} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
	//deleteData();
}

function getData() {
	include_once "./AdminTab.php";
}