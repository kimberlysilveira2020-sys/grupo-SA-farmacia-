<?php
class CadastroController extends Controller
{
    public function index(): void
    {
        // Se já estiver logado, manda pro dashboard
        if (isset($_SESSION['usuario_id'])) { header("Location: dashboard.php"); exit; }

        $erro = '';
        $sucesso = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nome = trim($_POST['nome'] ?? '');
            $login = trim($_POST['login'] ?? ''); 
            $senha = trim($_POST['senha'] ?? '');
            $cargo = trim($_POST['cargo'] ?? '');

            $cargos_permitidos = ['Atendente', 'Farmaceutico', 'Gerente'];

            if (!empty($nome) && !empty($login) && !empty($senha) && !empty($cargo)) {
                if (!in_array($cargo, $cargos_permitidos)) {
                    $erro = 'Cargo inválido selecionado.';
                } else {
                    try {
                        // 1. Carrega o Model responsável pelos usuários.
                        $model = new UsuarioModel();
                        
                        // 2. Verifica se o login já existe para evitar duplicação
                        $stmtCheck = $model->cadastrarBuscarUsuarios();
                        $stmtCheck->execute([$login]);
                        
                        if ($stmtCheck->fetch()) {
                            $erro = 'Este usuário/login já está em uso. Escolha outro.';
                        } else {
                            // 3. Criptografa a senha e cadastra pelo Model.
                            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
                            $stmt = $model->cadastrarInserirUsuarios();
                            $stmt->execute([$nome, $login, $senha_hash, $cargo]);
                            
                            $sucesso = 'Cadastro realizado com sucesso! Você já pode fazer login.';
                        }
                    } catch (PDOException $e) {
                        $erro = 'Erro ao salvar no banco de dados: ' . $e->getMessage();
                    }
                }
            } else {
                $erro = 'Por favor, preencha todos os campos.';
            }
        }

        $page_title = "Cadastrar Usuário";
        $hide_navbar = true;
        $this->render('auth/cadastrar', get_defined_vars());
    }
}
