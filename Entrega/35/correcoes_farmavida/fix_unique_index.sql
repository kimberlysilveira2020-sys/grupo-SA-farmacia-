-- =============================================================
-- Correção dos índices UNIQUE conflitantes na tabela `produtos`
-- 
-- Problema: a chave `uq_nome_fabricante` (nome, fabricante) sem
-- considerar o campo `ativo` impede o soft-delete + recadastro do
-- mesmo produto, pois mesmo com ativo=0 o UNIQUE já está ocupado.
--
-- Solução: remover o índice sem ativo e manter apenas o que
-- inclui ativo, garantindo que produtos desativados não bloqueiem
-- o cadastro de novos com mesmo nome/fabricante.
-- =============================================================

ALTER TABLE `produtos`
  DROP INDEX IF EXISTS `uq_nome_fabricante`;

-- Garante que o índice composto (nome, fabricante, ativo) existe
-- Ele já deve existir como idx_nome_fabricante_ativo — apenas
-- recria caso tenha sido removido acidentalmente.
ALTER TABLE `produtos`
  ADD UNIQUE KEY IF NOT EXISTS `idx_nome_fabricante_ativo` (`nome`, `fabricante`, `ativo`);
