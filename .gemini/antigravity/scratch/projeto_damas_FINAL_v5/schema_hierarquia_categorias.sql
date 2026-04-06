-- ============================================================
-- DAMAS ACESSÓRIOS — Hierarquia de Categorias (Pai -> Filho)
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- 1. Garante que a tabela categorias tenha a coluna pai_id
ALTER TABLE `categorias`
    ADD COLUMN IF NOT EXISTS `pai_id` INT UNSIGNED NULL DEFAULT NULL AFTER `id`;

-- 2. Adiciona a chave estrangeira (opcional, mas recomendado)
-- ALTER TABLE `categorias` ADD CONSTRAINT `fk_categoria_pai` FOREIGN KEY (`pai_id`) REFERENCES `categorias`(`id`) ON DELETE CASCADE;

-- 3. Garante que a tabela produtos tenha a coluna sub_categoria_id (já citado no functions.php)
ALTER TABLE `produtos`
    ADD COLUMN IF NOT EXISTS `sub_categoria_id` INT UNSIGNED NULL DEFAULT NULL AFTER `categoria_id`;

SET FOREIGN_KEY_CHECKS = 1;

-- Verifica as tabelas agora:
SHOW COLUMNS FROM `categorias`;
SHOW COLUMNS FROM `produtos`;
