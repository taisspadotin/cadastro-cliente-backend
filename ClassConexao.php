<?php
abstract class ClassConexao{

    #conexão com o banco de dados
    protected function conectaDB()
    {
         try{
             $Con=new PDO("mysql:host=127.0.0.1;dbname=cadastro","root","");
             return $Con;
         }catch (PDOException $Erro){
             return $Erro->getMessage();
         }
    }
}