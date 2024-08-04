update  `categorise` 
SET code = concat('BT',code)
WHERE `name` LIKE 'budget_type';