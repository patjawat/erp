-- SELECT i.id,i.code,i.category_id,old.code,old.title,t.code,t.title,a.data_json->'$.asset_type' FROM asset a
-- LEFT JOIN categorise i ON i.code = a.asset_item
-- INNER JOIN categorise_old old ON old.code = i.category_id AND old.name = "asset_type"
-- INNER JOIN categorise t ON t.title = old.title AND t.name = "asset_type"
-- WHERE i.name = "asset_item" limit 100000;


-- SELECT a.asset_item,i.category_id,old.code,t.code FROM asset a
-- LEFT JOIN categorise i ON i.code = a.asset_item
-- INNER JOIN categorise_old old ON old.code = i.category_id AND old.name = "asset_type"
-- INNER JOIN categorise t ON t.title = old.title AND t.name = "asset_type"
-- WHERE i.name = "asset_item";


UPDATE asset a 
INNER JOIN categorise i ON i.code = a.asset_item
INNER JOIN categorise_old old ON old.code = i.category_id AND old.name = "asset_type"
INNER JOIN categorise t ON t.title = old.title AND t.name = "asset_type"
SET i.category_id = t.code,old.code = t.code
WHERE i.name = "asset_item";