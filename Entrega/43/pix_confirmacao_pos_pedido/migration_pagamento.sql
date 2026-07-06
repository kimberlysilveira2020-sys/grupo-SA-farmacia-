-- Migração: adiciona coluna forma_pagamento na tabela pedidos
-- Execute uma vez no banco de dados farmavida

ALTER TABLE `pedidos`
  ADD COLUMN `forma_pagamento` ENUM('credito','pix','paypal','boleto') DEFAULT 'pix'
  AFTER `total`;
