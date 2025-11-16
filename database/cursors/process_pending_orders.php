<?php

/**
 * Cursor: Process Pending Orders
 * Iterates through all pending orders and calculates analytics
 * Created: 2025-11-16
 */

// Get database connection
$pdo = require_once __DIR__ . '/../connection/db.php';

function create_process_pending_orders_cursor() {
    global $pdo;
    
    // Drop procedure if exists
    $pdo->exec("DROP PROCEDURE IF EXISTS process_pending_orders");
    
    $sql = "
        CREATE PROCEDURE process_pending_orders()
        BEGIN
            DECLARE done INT DEFAULT FALSE;
            DECLARE v_order_id INT;
            DECLARE v_customer_id INT;
            DECLARE v_total DECIMAL(10,2);
            DECLARE v_item_count INT;
            
            -- Declare cursor for pending orders
            DECLARE pending_cursor CURSOR FOR 
                SELECT o.order_id, o.customer_id, o.total,
                       (SELECT COUNT(*) FROM order_items WHERE order_id = o.order_id) as item_count
                FROM orders o
                WHERE o.status = 'pending'
                ORDER BY o.order_time;
            
            -- Declare continue handler
            DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
            
            -- Create temporary table for results
            DROP TEMPORARY TABLE IF EXISTS pending_orders_summary;
            CREATE TEMPORARY TABLE pending_orders_summary (
                order_id INT,
                customer_id INT,
                total DECIMAL(10,2),
                item_count INT,
                priority VARCHAR(20)
            );
            
            -- Open cursor
            OPEN pending_cursor;
            
            read_loop: LOOP
                FETCH pending_cursor INTO v_order_id, v_customer_id, v_total, v_item_count;
                
                IF done THEN
                    LEAVE read_loop;
                END IF;
                
                -- Determine priority based on total and item count
                INSERT INTO pending_orders_summary (order_id, customer_id, total, item_count, priority)
                VALUES (
                    v_order_id, 
                    v_customer_id, 
                    v_total, 
                    v_item_count,
                    CASE 
                        WHEN v_total > 50 THEN 'HIGH'
                        WHEN v_total > 25 THEN 'MEDIUM'
                        ELSE 'LOW'
                    END
                );
            END LOOP;
            
            -- Close cursor
            CLOSE pending_cursor;
            
            -- Return results
            SELECT * FROM pending_orders_summary ORDER BY priority DESC, total DESC;
        END
    ";
    
    $pdo->exec($sql);
    echo "Procedure 'process_pending_orders' with cursor created successfully.\n";
}

function drop_process_pending_orders_cursor() {
    global $pdo;
    $sql = "DROP PROCEDURE IF EXISTS process_pending_orders";
    $pdo->exec($sql);
    echo "Procedure 'process_pending_orders' dropped successfully.\n";
}

function use_process_pending_orders_cursor() {
    global $pdo;
    $stmt = $pdo->query("CALL process_pending_orders()");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
