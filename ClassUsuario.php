<?php
include("ClassConexao.php");
error_reporting(0);

class ClassUsuario extends ClassConexao{

    public function cadastroUsuario($usuario, $email, $senha)
    {
		$resposta = '';
        $token = md5(uniqid(rand(), true));
		$BFetch=$this->conectaDB()->prepare("INSERT INTO usuarios (usuario, email, senha, token) values ('$usuario', '$email', '$senha', '$token');");
        $BFetch->execute() or $resposta= array('mensagem' => "erro", 'codigo' => 0);
		
        if($resposta == '')
		{   http_response_code(201); 
			$resposta= array('mensagem' => "Usuário cadastrado com sucesso!", 'codigo' => 1);
		}
        else{
            http_response_code(500);
        }
		echo json_encode($resposta);
      
    }
	public function buscaUsuario(){
		$BFetch=$this->conectaDB()->prepare("select * from usuarios");
        $BFetch->execute();

        $J=[];
        $I=0;

        while($Fetch=$BFetch->fetch(PDO::FETCH_ASSOC)){
            $J[$I]=[
                "id"=>$Fetch['id_usuario'],
                "usuario"=>$Fetch['usuario'],
                "email"=>$Fetch['email']
            ];
            $I++;
        }

        http_response_code(201);
        echo json_encode($J);

	}
	public function deletePessoa($id)
    {
        $resposta = '';
		$BFetch=$this->conectaDB()->prepare("DELETE FROM usuarios WHERE id_usuario = $id;");
        $BFetch->execute() or $resposta= array('mensagem' => "Erro ao deletar registro do banco");
		
        if($resposta == '')
        {
            http_response_code(201);
            $resposta= array('mensagem' => "sucesso");
        }
        else{
            http_response_code(500);
        }
        echo json_encode($resposta);

      
    }
    public function loginUsuario($email, $senha)
    {
        $resposta = '';
        $rs=$this->conectaDB()->prepare("SELECT token FROM usuarios WHERE email = '$email' AND senha = '$senha';");
        $rs->execute() or $resposta= array('mensagem' => "Erro ao buscar usuario no banco");
        $result = $rs->fetchAll(\PDO::FETCH_ASSOC);
        $token = $result[0]['token'];
        
        if($resposta == '')
        {
            http_response_code(201);
            if($token != null){
                $resposta = [
                    'mensagem' => 'Usuario logado com sucesso', 
                    'codigo' => 1,
                    'token' => $token
                ];
            }
            else{ $resposta= ['mensagem' => "Erro ao realizar o login", 'codigo' => 0];}
        }
        else{
            http_response_code(500);
        }
        echo json_encode($resposta);

    }
    
}