<?php
include("ClassConexao.php");
error_reporting(0);

class ClassPessoa extends ClassConexao{

    public function cadastroPessoa($nome, $telefone, $cpf, $rg, $nascimento, $endereco, $token)
    {
        if($this->verificaUsuario($token)){
            $resposta = '';
            $nascimento = date("Y-d-m", strtotime($nascimento));
            $BFetch=$this->conectaDB()->prepare("INSERT INTO pessoas (nome, telefone, cpf, rg, nascimento) values ('$nome', '$telefone', '$cpf', '$rg', '$nascimento');");
            $BFetch->execute() or $resposta= array('mensagem' => "erro");
    		
            if($resposta == '') //VERIFICA SE NÃO OCORREU ERRO AO CADASTRAR A PESSOA
            {
                foreach ($endereco as $key => $value){
                    $rs=$this->conectaDB()->prepare("INSERT INTO endereco (logradouro, cep, numero, estado, bairro, complemento, cidade, id_pessoa) values ('$value->logradouro', '$value->cep', $value->numero, '$value->estado', '$value->bairro', '$value->complemento', '$value->cidade', (SELECT id_pessoa FROM pessoas ORDER BY id_pessoa DESC LIMIT 1));");
                    $rs->execute() or $resposta= array('mensagem' => "erro");
                }
            }
            
            if($resposta == '')
    		{
                http_response_code(201);
    			$resposta= array('mensagem' => "Pessoa inserida com sucesso!");
    		}
            else{
                http_response_code(500);
            }
            
    		echo json_encode($resposta);
        }
    }
	
    public function buscaPessoa($page, $limit, $token){
        if($this->verificaUsuario($token)){
            if(empty($page)){
                $page = 0;
            }
            if(empty($limit)){
                $page = 10;
            }
            $inicio_busca = ($page*$limit);
            $BFetch=$this->conectaDB()->prepare("SELECT id_pessoa, nome, DATE_FORMAT(nascimento, '%d/%m/%Y') AS nascimento,  telefone, rg, cpf FROM pessoas ORDER BY id_pessoa DESC LIMIT $inicio_busca, $limit");
            $BFetch->execute();

            $pessoas=[];
            $I=0;

            while($Fetch=$BFetch->fetch(PDO::FETCH_ASSOC)){
                $pessoas[$I]=[
                    "id_pessoa"=>$Fetch['id_pessoa'],
                    "nome"=>$Fetch['nome'],
                    "telefone"=>$Fetch['telefone'],
                    "cpf"=>$Fetch['cpf'],
                    "rg"=>$Fetch['rg'],
                    "nascimento"=>$Fetch['nascimento']
                ];
                $I++;
            }

            
            $rs = $this->conectaDB()->prepare("SELECT count(id_pessoa) AS total FROM pessoas LIMIT 1");
            $rs->execute();
            
            $result = $rs->fetchAll(\PDO::FETCH_ASSOC);
            $total_registros = $result[0]['total'];
            $total_paginas = ceil($total_registros/$limit);

            $resp = [
                "total_paginas"=>$total_paginas,
                "total_registros"=>$total_registros,
                "pessoas"=>$pessoas
            ];

    		http_response_code(201);
            echo json_encode($resp);
        }
	}

    public function selecionaPessoa($id, $token)
    {
        if($this->verificaUsuario($token)){
            $BFetch=$this->conectaDB()->prepare("SELECT id_pessoa, nome, DATE_FORMAT(nascimento, '%d/%m/%Y') AS nascimento,  telefone, rg, cpf FROM pessoas WHERE id_pessoa = $id");
            $BFetch->execute();

            $pessoas=[];
            $endereco=[];
            $I=0;

            while($Fetch=$BFetch->fetch(PDO::FETCH_ASSOC)){
                $pessoas=[
                    "id_pessoa"=>$Fetch['id_pessoa'],
                    "nome"=>$Fetch['nome'],
                    "telefone"=>$Fetch['telefone'],
                    "cpf"=>$Fetch['cpf'],
                    "rg"=>$Fetch['rg'],
                    "nascimento"=>$Fetch['nascimento']
                    
                ];
            }


            $rs=$this->conectaDB()->prepare("SELECT * FROM endereco WHERE id_pessoa = $id");
            $rs->execute();

            while($row=$rs->fetch(PDO::FETCH_ASSOC)){
                $endereco[$I]=[
                    "id_endereco"=>$row['id_endereco'],
                    "logradouro"=>$row['logradouro'],
                    "cidade"=>$row['cidade'],
                    "estado"=>($row['estado']) == null ? '' : $row['estado'],
                    "cep"=>$row['cep'],
                    "numero"=>$row['numero'],
                    "complemento"=>$row['complemento'] == null ? '' : $row['complemento'],
                    "bairro"=>$row['bairro']
                ];
                $I++;        
            }
            
            $resp = [
                "pessoa"=>$pessoas,
                "enderecos"=>$endereco
            ];

            http_response_code(201);
            echo json_encode($resp);
        }
    }

    public function alteraPessoa($id, $nome, $telefone, $cpf, $rg, $nascimento, $endereco, $token){
        if($this->verificaUsuario($token)){
            $id             = !empty($id) ? $id: '';
            $nome           = !empty($nome) ? $nome: '';
            $telefone       = !empty($telefone) ? $telefone: '';
            $cpf            = !empty($cpf) ? $cpf: '';
            $rg             = !empty($rg) ? $rg: '';
            $nascimento     = !empty($nascimento) ? '"'.date("Y-d-m", strtotime($nascimento)).'"': 'null';
            
            if($id == ''){
                $resp = ["mensagem"=>"ID invalido"];
                http_response_code(404);
                echo json_encode($resp);
                exit();
            }
            $rs=$this->conectaDB()->prepare("UPDATE pessoas SET 
                                                            nome = '$nome',
                                                            telefone = '$telefone',
                                                            cpf = '$cpf',
                                                            rg = '$rg',
                                                            nascimento = $nascimento
                                                    WHERE id_pessoa = $id;");
            $rs->execute() or $resposta= array('mensagem' => "Erro ao alterar registro do banco");

            $rs_delete=$this->conectaDB()->prepare("DELETE FROM endereco WHERE id_pessoa = $id;");
            $rs_delete->execute();

            if($resposta == '')
            {   
                foreach ($endereco as $key => $value){

                    $rs=$this->conectaDB()->prepare("INSERT INTO endereco (logradouro, cep, numero, estado, bairro, complemento, cidade, id_pessoa) values ('$value->logradouro', '$value->cep', $value->numero, '$value->estado', '$value->bairro', '$value->complemento', '$value->cidade', $id);");
                    $rs->execute() or $resposta= array('mensagem' => "erro");
                        
                }
            } 
            if($resposta == '')
            {
                http_response_code(201);
                $resposta= array('mensagem' => "Alteração realizada com sucesso!");
            }
            else{
                http_response_code(500);
            }

            echo json_encode($resposta);
        }
    }

	public function deletePessoa($id, $token)
    {
        if($this->verificaUsuario($token)){
            $resposta = '';
            //DELETAR OS ENDEREÇOS RELACIONADO A PESSOA:
            $rs=$this->conectaDB()->prepare("DELETE FROM endereco WHERE id_pessoa = $id;");
            $rs->execute();
            

    		$BFetch=$this->conectaDB()->prepare("DELETE FROM pessoas WHERE id_pessoa = $id;");
            $BFetch->execute() or $resposta= array('mensagem' => "Erro ao deletar registro do banco");
    		
            if($resposta == '')
            {
                http_response_code(201);
                $resposta= array('mensagem' => "Pessoa excluída com sucesso!");
            }
            else{
                http_response_code(500);
            }

            echo json_encode($resposta);
        }
    }
    public function verificaUsuario($hash){

        $rs = $this->conectaDB()->prepare("SELECT count(id_usuario) AS total FROM usuarios WHERE token='$hash' LIMIT 1");
        $rs->execute();
        
        $result = $rs->fetchAll(\PDO::FETCH_ASSOC);
        $total_registros = $result[0]['total'];

        if($total_registros > 0){
            return true;
        }
        else{
            return false;
        }
        
    }


}