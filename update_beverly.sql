-- Step 1: Create Chief designation
INSERT INTO designations (name, created_at, updated_at) VALUES ('Chief', NOW(), NOW());

-- Step 2: Create Customer Experience department
INSERT INTO departments (name, created_at, updated_at) VALUES ('Customer Experience', NOW(), NOW());

-- Step 3: Show the new IDs
SELECT id, name FROM designations WHERE name = 'Chief' ORDER BY id DESC LIMIT 1;
SELECT id, name FROM departments WHERE name = 'Customer Experience' ORDER BY id DESC LIMIT 1;

-- Step 4: Show Beverly's current record
SELECT id, firstname, lastname, department_id, designation_id FROM users WHERE firstname LIKE '%Beverly%' AND lastname LIKE '%Awinja%' AND deleted_at IS NULL;

-- Step 5: Update Beverly's department and designation
UPDATE users 
SET 
    department_id = (SELECT id FROM departments WHERE name = 'Customer Experience' ORDER BY id DESC LIMIT 1),
    designation_id = (SELECT id FROM designations WHERE name = 'Chief' ORDER BY id DESC LIMIT 1)
WHERE firstname LIKE '%Beverly%' AND lastname LIKE '%Awinja%' AND deleted_at IS NULL;

-- Step 6: Confirm the update
SELECT id, firstname, lastname, department_id, designation_id FROM users WHERE firstname LIKE '%Beverly%' AND lastname LIKE '%Awinja%' AND deleted_at IS NULL;
SELECT id, name FROM designations ORDER BY id;
SELECT id, name FROM departments ORDER BY id;
