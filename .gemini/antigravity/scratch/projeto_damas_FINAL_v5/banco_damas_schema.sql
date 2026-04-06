-- ============================================================
-- DAMAS ACESSÓRIOS — Correção das Colunas Faltantes
-- Banco: u219240592_damas
--
-- ESTE SCRIPT NÃO APAGA NADA.
-- Apenas ADICIONA as colunas que estão faltando na tabela produtos.
--
-- Como usar:
-- 1. phpMyAdmin → selecione o banco u219240592_damas
-- 2. Aba SQL → cole este conteúdo → clique Executar
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Adiciona descricao_curta (texto resumido do produto)
ALTER TABLE `produtos`
    ADD COLUMN IF NOT EXISTS `descricao_curta` TEXT NULL;

-- Adiciona descricao_longa (texto completo do produto)
ALTER TABLE `produtos`
    ADD COLUMN IF NOT EXISTS `descricao_longa` LONGTEXT NULL;

-- Adiciona imagens_url (URLs separadas por vírgula)
ALTER TABLE `produtos`
    ADD COLUMN IF NOT EXISTS `imagens_url` TEXT NULL;

-- Adiciona link do Mercado Livre
ALTER TABLE `produtos`
    ADD COLUMN IF NOT EXISTS `link_mercadolivre` TEXT NULL;

-- Adiciona link do vídeo (YouTube)
ALTER TABLE `produtos`
    ADD COLUMN IF NOT EXISTS `video_url` VARCHAR(500) NULL;

-- Adiciona campo de visibilidade (1=visível, 0=oculto)
ALTER TABLE `produtos`
    ADD COLUMN IF NOT EXISTS `ativo` TINYINT(1) NOT NULL DEFAULT 1;

-- Adiciona categoria_id se não existir
ALTER TABLE `produtos`
    ADD COLUMN IF NOT EXISTS `categoria_id` INT UNSIGNED NULL DEFAULT NULL;

SET FOREIGN_KEY_CHECKS = 1;

-- Confirma quais colunas existem agora:
SHOW COLUMNS FROM `produtos`;
