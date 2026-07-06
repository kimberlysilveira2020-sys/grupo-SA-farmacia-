<?php
require_once __DIR__ . '/../../core/Model.php';

class ClienteModel extends Model {

    public function listarTodos(string $busca = ''): array {
        $whereInterno = $busca ? "WHERE (c.nome LIKE :like OR c.cpf LIKE :like2 OR c.telefone LIKE :like3)" : "";
        $whereLoja    = $busca ? "WHERE (cl.nome LIKE :like4 OR cl.cpf LIKE :like5 OR cl.telefone LIKE :like6 OR cl.email LIKE :like7)" : "";

        $sql = "
            SELECT c.id, c.nome, c.cpf, c.telefone, c.criado_em,
                   COUNT(v.id) AS total_compras,
                   COALESCE(SUM(v.total),0) AS valor_total,
                   'interno' AS origem, NULL AS email
            FROM clientes c
            LEFT JOIN vendas v ON v.cliente_id = c.id
            $whereInterno
            GROUP BY c.id
            UNION ALL
            SELECT cl.id, cl.nome, cl.cpf, cl.telefone, cl.criado_em,
                   COUNT(p.id) AS total_compras,
                   COALESCE(SUM(p.total),0) AS valor_total,
                   'loja' AS origem, cl.email AS email
            FROM clientes_loja cl
            LEFT JOIN pedidos p ON p.cliente_id = cl.id
            $whereLoja
            GROUP BY cl.id
            ORDER BY nome ASC
        ";

        $stmt = $this->db->prepare($sql);
        if ($busca) {
            $like = "%$busca%";
            $stmt->bindValue(':like',  $like); $stmt->bindValue(':like2', $like);
            $stmt->bindValue(':like3', $like); $stmt->bindValue(':like4', $like);
            $stmt->bindValue(':like5', $like); $stmt->bindValue(':like6', $like);
            $stmt->bindValue(':like7', $like);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function criar(string $nome, ?string $cpf, ?string $telefone): void {
        $cpfFmt = $cpf ? preg_replace('/^(\d{3})(\d{3})(\d{3})(\d{2})$/', '$1.$2.$3-$4', $cpf) : null;
        $s = $this->db->prepare("INSERT INTO clientes (nome, cpf, telefone) VALUES (?, ?, ?)");
        $s->execute([$nome, $cpfFmt, $telefone]);
    }

    public function editar(int $id, string $nome, ?string $cpf, ?string $telefone): void {
        $cpfFmt = $cpf ? preg_replace('/^(\d{3})(\d{3})(\d{3})(\d{2})$/', '$1.$2.$3-$4', $cpf) : null;
        $s = $this->db->prepare("UPDATE clientes SET nome=?, cpf=?, telefone=? WHERE id=?");
        $s->execute([$nome, $cpfFmt, $telefone, $id]);
    }

    public function deletar(int $id): void {
        $this->db->prepare("DELETE FROM clientes WHERE id=?")->execute([$id]);
    }

    public function contar(): array {
        $internos = (int)$this->db->query("SELECT COUNT(*) FROM clientes")->fetchColumn();
        try {
            $loja = (int)$this->db->query("SELECT COUNT(*) FROM clientes_loja")->fetchColumn();
        } catch (Exception $e) { $loja = 0; }
        return ['internos' => $internos, 'loja' => $loja, 'total' => $internos + $loja];
    }
}
