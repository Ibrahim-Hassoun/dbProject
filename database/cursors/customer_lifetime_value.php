<?php

/**
 * Cursor: Calculate Customer Lifetime Value
 * Iterates through customers and calculates their total spending
 * Created: 2025-11-16
 */

// Get database connection
$pdo = require_once __DIR__ . '/../connection/db.php';

function create_customer_lifetime_value_cursor() {
    global $pdo;
    
    // Drop procedure if exists
    $pdo->exec("DROP PROCEDURE IF EXISTS calculate_customer_lifetime_value");
    
    $sql = "
        CREATE PROCEDURE calculate_customer_lifetime_value()
        BEGIN
            DECLARE done INT DEFAULT FALSE;
            DECLARE v_customer_id INT;
            DECLARE v_customer_name VARCHAR(100);
            DECLARE v_total_spent DECIMAL(10,2);
            DECLARE v_order_count INT;
            DECLARE v_avg_order DECIMAL(10,2);
            
            -- Declare cursor for customers
            DECLARE customer_cursor CURSOR FOR 
                SELECT c.customer_id, c.full_name
                FROM customers c
                WHERE c.customer_id > 1  -- Exclude admin
                ORDER BY c.customer_id;
            
            -- Declare continue handler
            DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
            
            -- Create temporary table for results
            DROP TEMPORARY TABLE IF EXISTS customer_value_report;
            CREATE TEMPORARY TABLE customer_value_report (
                customer_id INT,
                customer_name VARCHAR(100),
                total_spent DECIMAL(10,2),
                order_count INT,
                avg_order_value DECIMAL(10,2),
                customer_tier VARCHAR(20)
            );
            
            -- Open cursor
            OPEN customer_cursor;
            
            read_loop: LOOP
                FETCH customer_cursor INTO v_customer_id, v_customer_name;
                
                IF done THEN
                    LEAVE read_loop;
                END IF;
                
                -- Calculate customer metrics
                SELECT 
                    COALESCE(SUM(total), 0),
                    COUNT(*),
                    COALESCE(AVG(total), 0)
                INTO v_total_spent, v_order_count, v_avg_order
                FROM orders
                WHERE customer_id = v_customer_id 
                  AND status != 'canceled';
                
                -- Insert into results with tier classification
                INSERT INTO customer_value_report 
                VALUES (
                    v_customer_id, 
                    v_customer_name, 
                    v_total_spent, 
                    v_order_count, 
                    v_avg_order,
                    CASE 
                        WHEN v_total_spent > 100 THEN 'GOLD'
                        WHEN v_total_spent > 50 THEN 'SILVER'
                        WHEN v_total_spent > 0 THEN 'BRONZE'
                        ELSE 'NEW'
                    END
                );
            END LOOP;
            
            -- Close cursor
            CLOSE customer_cursor;
            
            -- Return results
            SELECT * FROM customer_value_report 
            ORDER BY total_spent DESC;
        END
    ";
    
    $pdo->exec($sql);
    echo "Procedure 'calculate_customer_lifetime_value' with cursor created successfully.\n";
}

function drop_customer_lifetime_value_cursor() {
    global $pdo;
    $sql = "DROP PROCEDURE IF EXISTS calculate_customer_lifetime_value";
    $pdo->exec($sql);
    echo "Procedure 'calculate_customer_lifetime_value' dropped successfully.\n";
}

function use_customer_lifetime_value_cursor() {
    global $pdo;
    $stmt = $pdo->query("CALL calculate_customer_lifetime_value()");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
