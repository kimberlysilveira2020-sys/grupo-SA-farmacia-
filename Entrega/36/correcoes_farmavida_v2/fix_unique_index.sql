-- =============================================================
-- SCRIPT DE CORREÇÃO — Farmavida / tabela `produtos`
-- Execute UMA vez no banco via phpMyAdmin ou linha de comando.
-- =============================================================

-- -------------------------------------------------------
-- PASSO 1: Remover duplicatas reais na tabela produtos
-- Mantém apenas o registro de MAIOR id por (nome, fabricante, ativo)
-- Isso elimina os cards duplicados que aparecem na tela.
-- -------------------------------------------------------

DELETE p1
FROM produtos p1
INNER JOIN produtos p2
  ON  p1.nome       = p2.nome
  AND p1.fabricante = p2.fabricante
  AND p1.ativo      = p2.ativo
  AND p1.id         < p2.id;

-- -------------------------------------------------------
-- PASSO 2: Remover índice UNIQUE problemático
-- `uq_nome_fabricante` (nome, fabricante) SEM considerar
-- o campo `ativo` impede o soft-delete + recadastro:
--   (Dipirona, Dori, ativo=0) bloqueia INSERT de
--   (Dipirona, Dori, ativo=1) mesmo sendo produto diferente.
-- -------------------------------------------------------

ALTER TABLE `produtos`
  DROP INDEX IF EXISTS `uq_nome_fabricante`;

-- -------------------------------------------------------
-- PASSO 3: Garantir que o índice correto (com ativo) existe
-- Permite: um produto ativo=1 e um desativado ativo=0
-- com mesmo nome+fabricante coexistindo sem conflito.
-- Impede: dois produtos ativo=1 com mesmo nome+fabricante.
-- -------------------------------------------------------

-- Remove se existir com nome antigo para recriar limpo
ALTER TABLE `produtos`
  DROP INDEX IF EXISTS `idx_nome_fabricante_ativo`;

ALTER TABLE `produtos`
  ADD UNIQUE KEY `uq_produto_ativo` (`nome`, `fabricante`, `ativo`);

-- -------------------------------------------------------
-- VERIFICAÇÃO (opcional — rode para confirmar limpeza)
-- -------------------------------------------------------
-- SELECT nome, fabricante, ativo, COUNT(*) AS qtd
-- FROM produtos
-- GROUP BY nome, fabricante, ativo
-- HAVING qtd > 1;
-- Resultado esperado: nenhuma linha.
-- =============================================================
