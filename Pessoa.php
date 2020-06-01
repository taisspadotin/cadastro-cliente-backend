<?php
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE, PATCH, HEADERS');
header('Access-Control-Max-Age: 1000');
header("Content-type: application/json");
if (isset($_SERVER['HTTP_ORIGIN'])) {
    // Decide if the origin in $_SERVER['HTTP_ORIGIN'] is one
    // you want to allow, and if so:
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 1000');
}
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
        // may also be using PUT, PATCH, HEAD etc
        header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE, PATCH, HEADERS");
    }

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
        header("Access-Control-Allow-Headers: Accept, Content-Type, Content-Length, Accept-Encoding, X-CSRF-Token, Authorization");
    }
    exit(0);
}
$token = '';
foreach (getallheaders() as $name => $value) { 
    if($name == 'Authorization'){
    	$token = $value;
    }
} 

include("ClassPessoa.php");
$Pessoa=new ClassPessoa();


$request_method=$_SERVER["REQUEST_METHOD"];
$data = json_decode(file_get_contents("php://input"));
switch($request_method)
	{
		case 'POST':
			$Pessoa->cadastroPessoa($data->nome, $data->telefone, $data->cpf, $data->rg, $data->nascimento, $data->enderecos, $token);
		break;
		case 'GET':
			$page   = !empty($_GET['page']) ? $_GET['page']: 0;
			$limit  = !empty($_GET['limit']) ? $_GET['limit']: 10;

			$id     = !empty($_GET['id']) ? $_GET['id']: 0;
			if($id == 0){
				$Pessoa->buscaPessoa($page, $limit, $token);
			}
			else{
				$Pessoa->selecionaPessoa($id, $token);	
			}

			
		break;
		case 'PATCH':
			$Pessoa->alteraPessoa($_GET['id'], $data->nome, $data->telefone, $data->cpf, $data->rg, $data->nascimento, $data->enderecos, $token);	
		break;
		case 'DELETE':
			$Pessoa->deletePessoa($_GET['id'], $token);
		break;
	}
