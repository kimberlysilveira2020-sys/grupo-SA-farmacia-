<?php
class ClienteController extends Controller
{
    public function index(): void
    {
        if (!isset($_SESSION['usuario_id'])) { header("Location: login.php"); exit; }

        $model = new ClienteModel();

        $busca = trim($_GET['busca'] ?? '');

        $whereInterno = $busca ? "WHERE (c.nome LIKE :like OR c.cpf LIKE :like2 OR c.telefone LIKE :like3)" : "";
        $whereLoja    = $busca ? "WHERE (cl.nome LIKE :like4 OR cl.cpf LIKE :like5 OR cl.telefone LIKE :like6 OR cl.email LIKE :like7)" : "";

        $sql = "
            SELECT
                c.id,
                c.nome,
                c.cpf,
                c.telefone,
                c.criado_em,
                COUNT(v.id)               AS total_compras,
                COALESCE(SUM(v.total), 0) AS valor_total,
                'interno'                 AS origem,
                NULL                      AS email
            FROM clientes c
            LEFT JOIN vendas v ON v.cliente_id = c.id
            $whereInterno
            GROUP BY c.id

            UNION ALL

            SELECT
                cl.id,
                cl.nome,
                cl.cpf,
                cl.telefone,
                cl.criado_em,
                COUNT(p.id)               AS total_compras,
                COALESCE(SUM(p.total), 0) AS valor_total,
                'loja'                    AS origem,
                cl.email                  AS email
            FROM clientes_loja cl
            LEFT JOIN pedidos p ON p.cliente_id = cl.id
            $whereLoja
            GROUP BY cl.id

            ORDER BY nome ASC
        ";

        $stmt = $model->clientesExecutar($sql);
        if ($busca) {
            $like = "%$busca%";
            $stmt->bindValue(':like',  $like); $stmt->bindValue(':like2', $like); $stmt->bindValue(':like3', $like);
            $stmt->bindValue(':like4', $like); $stmt->bindValue(':like5', $like); $stmt->bindValue(':like6', $like);
            $stmt->bindValue(':like7', $like);
        }
        $stmt->execute();
        $clientes       = $stmt->fetchAll();
        $total_clientes = count($clientes);
        $total_loja     = count(array_filter($clientes, fn($c) => $c['origem'] === 'loja'));
        $total_interno  = $total_clientes - $total_loja;

        $page_title = "Clientes";
        $this->render('clientes/index', get_defined_vars());
    }
}
