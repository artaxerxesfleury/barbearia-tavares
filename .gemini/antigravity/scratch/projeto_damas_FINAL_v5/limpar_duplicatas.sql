-- ============================================================
-- DAMAS ACESSÓRIOS — Remover Produtos Duplicados
-- Execute este script UMA VEZ antes de sincronizar novamente.
-- Ele mantém apenas o PRIMEIRO produto de cada MLB ID.
-- ============================================================

-- Apaga duplicatas mantendo o registro com menor ID (o original)
DELETE p1 FROM produtos p1
INNER JOIN produtos p2
    ON p1.link_mercadolivre = p2.link_mercadolivre
    AND p1.id > p2.id;

-- Confirma quantos produtos sobraram:
SELECT COUNT(*) AS produtos_apos_limpeza FROM produtos;
