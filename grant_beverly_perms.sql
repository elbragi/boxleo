INSERT IGNORE INTO model_has_permissions (permission_id, model_type, model_id) 
SELECT p.id, 'App\\Models\\User', u.id 
FROM permissions p, users u 
WHERE p.name = 'view_team_leaves' 
AND u.id = 9;
SELECT 'Permission rows:', ROW_COUNT();
