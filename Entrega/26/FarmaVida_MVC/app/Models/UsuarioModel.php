<?php
require_once __DIR__ . '/../../core/Model.php';

class UsuarioModel extends Model {

    public function buscarPorLogin(string $login): array|false {
        $s = $this->db->prepare("SELECT id, nome, login, senha_hash, cargo FROM usuarios WHERE login = ?");
        $s->execute([$login]);
        return $s->fetch();
    }

    public function loginExiste(string $login): bool {
        $s = $this->db->prepare("SELECT id FROM usuarios WHERE login = ?");
        $s->execute([$login]);
        return (bool)$s->fetch();
    }

    public function criar(string $nome, string $login, string $senhaHash, string $cargo): void {
        $s = $this->db->prepare("INSERT INTO usuarios (nome, login, senha_hash, cargo) VALUES (?, ?, ?, ?)");
        $s->execute([$nome, $login, $senhaHash, $cargo]);
    }
}
