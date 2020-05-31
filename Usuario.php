<?php
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE');
header("Content-type: application/json");
if (isset($_SERVER['HTTP_ORIGIN'])) {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 1000');
}
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
        header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
    }

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
        header("Access-Control-Allow-Headers: Accept, Content-Type, Content-Length, Accept-Encoding, X-CSRF-Token, Authorization");
    }
    exit(0);
}

include("ClassUsuario.php");
$Usuario = new ClassUsuario();


$request_method=$_SERVER["REQUEST_METHOD"];
$data = json_decode(file_get_contents("php://input"));
switch($request_method)
	{
		case 'POST':
			$login   = !empty($_GET['login']) ? $_GET['login']: 0;
			if($login == 0){
				$Usuario->cadastroUsuario($data->usuario, $data->email, md5($data->senha));
			}
			else $Usuario->loginUsuario($data->email, md5($data->senha));
		break;
		case 'GET':
			$Usuario->buscaUsuario();
		break;
		case 'DELETE':
			$Usuario->deleteUsuario($_GET['id']);
		break;
	}
