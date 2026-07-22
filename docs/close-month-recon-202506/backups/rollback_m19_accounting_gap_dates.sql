-- Rollback M19 accounting-gap date move (restore 3 ADJUST rows to 2026-06-30)
-- Created: 2026-07-21
-- Scope: stock_order.id IN (3464,3465,3526)
-- Tenant DB: dansai

START TRANSACTION;

UPDATE stock_order
SET order_date = CASE id
    WHEN 3464 THEN '2026-06-30 14:01:34'
    WHEN 3465 THEN '2026-06-30 14:07:37'
    WHEN 3526 THEN '2026-06-30 23:11:09'
    ELSE order_date
END,
updated_at = CASE id
    WHEN 3464 THEN '2026-07-21 21:54:51'
    WHEN 3465 THEN '2026-07-21 21:54:51'
    WHEN 3526 THEN '2026-07-20 23:11:09'
    ELSE updated_at
END
WHERE id IN (3464,3465,3526);

COMMIT;
