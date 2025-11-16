<?php

/**
 * Cursor: Update Low Stock Items
 * Iterates through menu items and flags low stock items
 * Created: 2025-11-16
 */

// Get database connection
$pdo = require_once __DIR__ . '/../connection/db.php';

function create_update_low_stock_cursor() {
    global $pdo;
    
    // Drop procedure if exists
    $pdo->exec("DROP PROCEDURE IF EXISTS check_low_stock_items");
    
    $sql = "
        CREATE PROCEDURE check_low_stock_items()
        BEGIN
            DECLARE done INT DEFAULT FALSE;
            DECLARE v_item_id INT;
            DECLARE v_item_name VARCHAR(120);
            DECLARE v_in_stock INT;
            DECLARE v_total_ordered INT;
            
            -- Declare cursor for menu items
            DECLARE stock_cursor CURSOR FOR 
                SELECT item_id, item_name, in_stock
                FROM menu_items
                ORDER BY in_stock ASC;
            
            -- Declare continue handler
            DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
            
            -- Create temporary table for results
            DROP TEMPORARY TABLE IF EXISTS low_stock_report;
            CREATE TEMPORARY TABLE low_stock_report (
                item_id INT,
                item_name VARCHAR(120),
                current_stock INT,
                total_ordered INT,
                stock_status VARCHAR(20),
                reorder_recommended BOOLEAN
            );
            
            -- Open cursor
            OPEN stock_cursor;
            
            read_loop: LOOP
                FETCH stock_cursor INTO v_item_id, v_item_name, v_in_stock;
                
                IF done THEN
                    LEAVE read_loop;
                END IF;
                
                -- Calculate total times this item was ordered
                SELECT COALESCE(SUM(quantity), 0)
                INTO v_total_ordered
                FROM order_items
                WHERE item_id = v_item_id;
                
                -- Insert into report
                INSERT INTO low_stock_report 
                VALUES (
                    v_item_id,
                    v_item_name,
                    v_in_stock,
                    v_total_ordered,
                    CASE 
                        WHEN v_in_stock = 0 THEN 'OUT_OF_STOCK'
                        WHEN v_in_stock < 10 THEN 'CRITICAL'
                        WHEN v_in_stock < 20 THEN 'LOW'
                        WHEN v_in_stock < 30 THEN 'MODERATE'
                        ELSE 'GOOD'
                    END,
                    CASE 
                        WHEN v_in_stock < 20 THEN TRUE
                        ELSE FALSE
                    END
                );
            END LOOP;
            
            -- Close cursor
            CLOSE stock_cursor;
            
            -- Return results ordered by urgency
            SELECT * FROM low_stock_report 
            WHERE reorder_recommended = TRUE OR stock_status = 'OUT_OF_STOCK'
            ORDER BY current_stock ASC;
        END
    ";
    
    $pdo->exec($sql);
    echo "Procedure 'check_low_stock_items' with cursor created successfully.\n";
}

function drop_update_low_stock_cursor() {
    global $pdo;
    $sql = "DROP PROCEDURE IF EXISTS check_low_stock_items";
    $pdo->exec($sql);
    echo "Procedure 'check_low_stock_items' dropped successfully.\n";
}

function use_low_stock_cursor() {
    global $pdo;
    $stmt = $pdo->query("CALL check_low_stock_items()");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
