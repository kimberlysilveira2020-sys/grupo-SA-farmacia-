<?php
class PedidoController extends Controller
{
    public function index(): void
    {
        if (!isset($_SESSION['usuario_id'])) { header("Location: login.php"); exit; }

        $page_title = "Pedidos Online";
        $extra_css = <<<CSS
        <style>
        /* ── Cards de resumo ─────────────────────────────── */
        .resumo-cards { display:flex; gap:14px; flex-wrap:wrap; margin-bottom:22px; }
        .resumo-card {
            flex:1; min-width:140px; border-radius:12px; padding:16px 20px;
            display:flex; align-items:center; gap:14px; background:#fff;
            box-shadow:0 2px 8px rgba(0,0,0,.08);
        }
        .resumo-card { transition: box-shadow .15s, transform .15s; }
        .resumo-card:hover { box-shadow:0 4px 16px rgba(25,135,84,.18); transform:translateY(-2px); }
        .resumo-card.ativo { box-shadow:0 0 0 2.5px #198754; }
        .resumo-card .icon { font-size:2rem; width:44px; text-align:center; }
        .resumo-card .info .num  { font-size:1.5rem; font-weight:800; line-height:1; }
        .resumo-card .info .label{ font-size:.75rem; color:#666; margin-top:2px; }

        /* ── Tabela ──────────────────────────────────────── */
        .tbl-pedidos { width:100%; border-collapse:collapse; font-size:.875rem; }
        .tbl-pedidos thead th {
            background:#f8f9fa; padding:10px 14px; text-align:left;
            font-weight:700; color:#555; border-bottom:2px solid #dee2e6;
            white-space:nowrap;
        }
        .tbl-pedidos tbody tr { border-bottom:1px solid #eee; transition:background .12s; }
        .tbl-pedidos tbody tr:hover { background:#f5f9f5; }
        .tbl-pedidos td { padding:10px 14px; vertical-align:middle; }

        /* ── Badges de status ────────────────────────────── */
        .badge-status {
            display:inline-block; padding:4px 10px; border-radius:20px;
            font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.5px;
        }
        .bs-pendente               { background:#fff3cd; color:#856404; }
        .bs-confirmado             { background:#d1e7dd; color:#0a3622; }
        .bs-cancelado              { background:#f8d7da; color:#58151c; }
        .bs-aguardando_confirmacao { background:#cfe2ff; color:#084298; } /* Pix declarado — aguarda validação */

        /* ── Badges pagamento ────────────────────────────── */
        .badge-pgto {
            display:inline-flex; align-items:center; gap:4px;
            padding:3px 9px; border-radius:20px; font-size:.72rem; font-weight:600;
        }
        .bp-pix     { background:#e8f5e9; color:#1b5e20; }
        .bp-credito { background:#e3f2fd; color:#0d47a1; }
        .bp-boleto  { background:#fff8e1; color:#e65100; }
        .bp-paypal  { background:#e8eaf6; color:#1a237e; }
        .bp-pago    { background:#c8e6c9; color:#1b5e20; }

        /* ── Filtros ─────────────────────────────────────── */
        .filtros-bar {
            display:flex; gap:10px; align-items:center; flex-wrap:wrap;
            background:#fff; padding:14px 18px; border-radius:10px;
            box-shadow:0 1px 6px rgba(0,0,0,.07); margin-bottom:18px;
        }
        .filtros-bar input, .filtros-bar select {
            border:1px solid #ced4da; border-radius:8px; padding:7px 12px;
            font-size:.87rem; outline:none;
        }
        .filtros-bar input:focus, .filtros-bar select:focus { border-color:#198754; }

        /* ── Modal detalhes ──────────────────────────────── */
        .modal-header-verde { background:#198754 !important; color:#fff !important; }
        .modal-header-verde .btn-close { filter:invert(1); }

        .detalhe-bloco {
            background:#f8f9fa; border-radius:10px; padding:14px 18px; margin-bottom:14px;
        }
        .detalhe-bloco h6 { font-weight:700; color:#198754; margin-bottom:10px; font-size:.85rem; }

        .itens-list { list-style:none; padding:0; margin:0; }
        .itens-list li {
            display:flex; justify-content:space-between; align-items:center;
            padding:8px 0; border-bottom:1px solid #e9ecef; font-size:.87rem;
        }
        .itens-list li:last-child { border-bottom:none; }
        .itens-list .qtd { font-weight:700; color:#198754; min-width:32px; }
        .itens-list .preco { font-weight:700; white-space:nowrap; }

        /* PIX QR no modal */
        .pix-modal-wrap { text-align:center; margin:10px 0; }
        .pix-modal-wrap canvas { border:3px solid #198754; border-radius:10px; padding:8px; background:#fff; }
        .pix-codigo-bloco {
            background:#f1f8f3; border:1px solid #c3e6cb; border-radius:8px;
            padding:10px 14px; font-size:.72rem; word-break:break-all;
            color:#155724; display:flex; align-items:center; gap:8px; margin-top:8px;
        }
        .pix-codigo-bloco span { flex:1; }

        /* Ações rápidas */
        .acoes-rapidas { display:flex; gap:6px; flex-wrap:wrap; }

        /* Loading overlay */
        #loading-overlay {
            display:none; position:fixed; inset:0;
            background:rgba(255,255,255,.7); z-index:9999;
            align-items:center; justify-content:center;
        }
        #loading-overlay.ativo { display:flex; }

        /* Tabela vazia */
        .tbl-vazio { text-align:center; padding:40px; color:#aaa; }
        .tbl-vazio i { font-size:2.5rem; display:block; margin-bottom:8px; }
        </style>
        CSS;
        $this->render('pedidos/index', get_defined_vars());
    }
}
