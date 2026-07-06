-- ============================================================
-- FarmaVida – Seed de Produtos
-- 10 produtos em cada seção: Infantil, Ortopédico, Hospitalar
-- + lotes iniciais para cada produto
-- ============================================================

-- ─────────────────────────────────────────────────────────────
-- PRODUTOS – INFANTIL (categoria_id = 10, categoria = 'Infantil')
-- ─────────────────────────────────────────────────────────────
INSERT INTO `produtos` (`nome`, `fabricante`, `categoria`, `preco_venda`, `descricao`, `foto`, `receita_obrigatoria`, `ativo`) VALUES
('Paracetamol Infantil Gotas 100mg/mL',  'EMS',         'Infantil', 12.90, 'Analgésico e antitérmico em gotas para crianças. Frasco 15mL.',       NULL, 0, 1),
('Ibuprofeno Infantil Suspensão 50mg/mL','Medley',      'Infantil', 18.50, 'Anti-inflamatório infantil em suspensão oral. Frasco 120mL.',          NULL, 0, 1),
('Vitassay C Mastigável 100mg',          'Legrand',     'Infantil', 22.90, 'Vitamina C mastigável sabor laranja para crianças. Caixa com 30 comp.',NULL, 0, 1),
('Soro Fisiológico Nasal Infantil',      'Novafito',    'Infantil',  8.90, 'Solução salina isotônica para higiene nasal. Kit com 30 ampolas 5mL.',  NULL, 0, 1),
('Bepantol Baby Pomada',                 'Bayer',       'Infantil', 29.90, 'Pomada preventiva e tratadora de assaduras. 30g.',                     NULL, 0, 1),
('Calcitran D3 Gotas',                   'Sanavita',    'Infantil', 34.90, 'Suplemento de cálcio e vitamina D3 em gotas para crianças. 30mL.',     NULL, 0, 1),
('Espasmo Bebê Gotas',                   'União Química','Infantil', 14.50, 'Antiespasmódico para cólicas infantis. Frasco 20mL.',                  NULL, 0, 1),
('Addera D3 400UI Gotas',               'Sanofi',      'Infantil', 27.90, 'Suplemento de vitamina D3 para bebês e crianças. Frasco 10mL.',         NULL, 0, 1),
('Floratil Baby 200mg Sachê',            'Merck',       'Infantil', 38.90, 'Probiótico para reequilíbrio da flora intestinal. Caixa com 10 sachês.',NULL, 0, 1),
('Histadin Pediátrico Xarope',           'Marjan',      'Infantil', 19.90, 'Xarope antialérgico para crianças. Frasco 120mL.',                     NULL, 0, 1);

-- ─────────────────────────────────────────────────────────────
-- PRODUTOS – ORTOPÉDICO (categoria = 'Ortopédico')
-- ─────────────────────────────────────────────────────────────
INSERT INTO `produtos` (`nome`, `fabricante`, `categoria`, `preco_venda`, `descricao`, `foto`, `receita_obrigatoria`, `ativo`) VALUES
('Voltaren Emulgel 1% 60g',              'Novartis',    'Ortopédico', 42.90, 'Anti-inflamatório tópico para dores musculares e articulares. Bisnaga 60g.',  NULL, 0, 1),
('Profenid Gel 2,5% 60g',               'Sanofi',      'Ortopédico', 38.50, 'Cetoprofeno gel para alívio de dores e inflamações locais. Bisnaga 60g.',     NULL, 0, 1),
('Cataflan 50mg',                        'Novartis',    'Ortopédico', 24.90, 'Diclofenaco potássico anti-inflamatório. Caixa com 20 comprimidos.',           NULL, 0, 1),
('Bengala Regulável Alumínio',           'Carci',       'Ortopédico',159.90, 'Bengala dobrável em alumínio com 4 pés antiderrapantes. Regulagem de altura.', NULL, 0, 1),
('Tornozeleira Elástica Neoprene M',     'Ortho Pauher','Ortopédico', 49.90, 'Suporte compressivo para tornozelo em neoprene. Tamanho M.',                  NULL, 0, 1),
('Joelheira com Abertura Patelar M',     'Corflex',     'Ortopédico', 59.90, 'Joelheira elástica com abertura patelar para suporte e compressão. Tamanho M.',NULL, 0, 1),
('Órtese Imobilizadora de Punho',        'Dyna',        'Ortopédico', 89.90, 'Imobilizador de punho com talas removíveis. Tamanho único ajustável.',         NULL, 0, 1),
('Cinto Lombar Elástico G',              'Saúde Life',  'Ortopédico', 74.90, 'Cinto lombar com barbatanas para suporte vertebral. Tamanho G.',               NULL, 0, 1),
('Muleta Axilar Alumínio Par',           'Carci',       'Ortopédico',189.90, 'Par de muletas axilares em alumínio com regulagem de altura. Capacidade 120kg.',NULL, 0, 1),
('Gel para Ultrassom 1kg',               'Carbogel',    'Ortopédico', 32.90, 'Gel condutor para aparelhos de ultrassom fisioterapêutico. Pote 1kg.',          NULL, 0, 1);

-- ─────────────────────────────────────────────────────────────
-- PRODUTOS – HOSPITALAR (categoria = 'Hospitalar')
-- ─────────────────────────────────────────────────────────────
INSERT INTO `produtos` (`nome`, `fabricante`, `categoria`, `preco_venda`, `descricao`, `foto`, `receita_obrigatoria`, `ativo`) VALUES
('Luva Procedimento Látex M Caixa 100', '3M',           'Hospitalar',  49.90, 'Luvas de látex sem pó para procedimentos. Caixa com 100 unidades. Tamanho M.',NULL, 0, 1),
('Seringa 5mL Bico Luer Lock',           'BD',           'Hospitalar',   2.90, 'Seringa descartável 5mL com bico Luer Lock. Unidade.',                         NULL, 0, 1),
('Agulha 40x12 Caixa 100',              'Descarpack',   'Hospitalar',  19.90, 'Agulhas hipodérmicas 40x12mm descartáveis. Caixa com 100 unidades.',            NULL, 0, 1),
('Curativo Tegaderm 10x12cm',           '3M',           'Hospitalar',  18.90, 'Filme transparente para curativo com borda adesiva. Caixa com 5 unidades.',     NULL, 0, 1),
('Esparadrapo Impermeável 10mx5cm',     'Missner',      'Hospitalar',  14.90, 'Esparadrapo impermeável bege. Rolo 10mx5cm.',                                   NULL, 0, 1),
('Álcool 70% Líquido 1L',               'Rioquímica',   'Hospitalar',  19.90, 'Álcool etílico hidratado 70% INPM para antissepsia. Frasco 1L.',                NULL, 0, 1),
('Atadura de Crepe 10cm x 1,8m',        'Neve',         'Hospitalar',   3.90, 'Atadura de crepe 10cm de largura. Rolo de 1,8m. Unitário.',                     NULL, 0, 1),
('Termômetro Digital Axilar',            'G-Tech',       'Hospitalar',  29.90, 'Termômetro digital com alarme sonoro. Resultado em 60 segundos.',               NULL, 0, 1),
('Máscara Cirúrgica Tripla Cx 50',      'Descarpack',   'Hospitalar',  24.90, 'Máscara descartável tripla camada com elástico. Caixa com 50 unidades.',        NULL, 0, 1),
('Oxímetro de Pulso Digital',            'G-Tech',       'Hospitalar',  89.90, 'Oxímetro portátil para medição de SpO2 e frequência cardíaca. Pilha inclusa.',  NULL, 0, 1);

-- ─────────────────────────────────────────────────────────────
-- LOTES – um lote inicial para cada produto inserido acima
-- Os IDs seguem a sequência a partir do próximo AUTO_INCREMENT
-- atual da tabela produtos (AUTO_INCREMENT=62), então:
--   Infantil:    IDs 62 a 71
--   Ortopédico:  IDs 72 a 81
--   Hospitalar:  IDs 82 a 91
-- ─────────────────────────────────────────────────────────────

-- Lotes – Infantil
INSERT INTO `lotes` (`produto_id`, `numero_lote`, `data_validade`, `qtd_atual`, `qtd_inicial`) VALUES
(62,  'INF-001', '2027-06-30', 50, 50),
(63,  'INF-002', '2027-08-31', 40, 40),
(64,  'INF-003', '2027-12-31', 60, 60),
(65,  'INF-004', '2027-03-31', 80, 80),
(66,  'INF-005', '2027-09-30', 45, 45),
(67,  'INF-006', '2027-11-30', 55, 55),
(68,  'INF-007', '2027-07-31', 70, 70),
(69,  'INF-008', '2028-01-31', 35, 35),
(70,  'INF-009', '2027-05-31', 30, 30),
(71,  'INF-010', '2027-10-31', 50, 50);

-- Lotes – Ortopédico
INSERT INTO `lotes` (`produto_id`, `numero_lote`, `data_validade`, `qtd_atual`, `qtd_inicial`) VALUES
(72,  'ORT-001', '2027-06-30', 40, 40),
(73,  'ORT-002', '2027-08-31', 35, 35),
(74,  'ORT-003', '2027-12-31', 60, 60),
(75,  'ORT-004', '2028-06-30', 10, 10),
(76,  'ORT-005', '2028-12-31', 20, 20),
(77,  'ORT-006', '2028-12-31', 15, 15),
(78,  'ORT-007', '2028-12-31', 12, 12),
(79,  'ORT-008', '2028-12-31', 18, 18),
(80,  'ORT-009', '2028-06-30',  8,  8),
(81,  'ORT-010', '2027-10-31', 30, 30);

-- Lotes – Hospitalar
INSERT INTO `lotes` (`produto_id`, `numero_lote`, `data_validade`, `qtd_atual`, `qtd_inicial`) VALUES
(82,  'HOS-001', '2027-06-30', 20, 20),
(83,  'HOS-002', '2027-12-31',100,100),
(84,  'HOS-003', '2027-12-31', 50, 50),
(85,  'HOS-004', '2027-09-30', 25, 25),
(86,  'HOS-005', '2027-08-31', 15, 15),
(87,  'HOS-006', '2027-06-30', 30, 30),
(88,  'HOS-007', '2027-12-31',100,100),
(89,  'HOS-008', '2028-06-30', 20, 20),
(90,  'HOS-009', '2027-12-31', 40, 40),
(91,  'HOS-010', '2028-06-30', 15, 15);

