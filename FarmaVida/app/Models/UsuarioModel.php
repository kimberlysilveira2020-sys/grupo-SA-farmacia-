<?php
class UsuarioModel extends Model
{
    public function loginBuscarUsuarios()
    {
        return $this->db()->prepare("SELECT id, nome, login, senha_hash, cargo FROM usuarios WHERE login = ?");
    }

    public function cadastrarBuscarUsuarios()
    {
        return $this->db()->prepare("SELECT id FROM usuarios WHERE login = ?");
    }

    public function cadastrarInserirUsuarios()
    {
        return $this->db()->prepare("INSERT INTO usuarios (nome, login, senha_hash, cargo) VALUES (?, ?, ?, ?)");
    }
}
