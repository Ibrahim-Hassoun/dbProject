<?php

/**
 * Trigger: trg_update_order_total
 * Automatically updates order total when an order item is inserted
 * Created: 2025-11-16
 */

function create_trg_update_order_total($pdo) {
    // Drop trigger if exists
    $pdo->exec("DROP TRIGGER IF EXISTS trg_update_order_total");
    
    $sql = "
        CREATE TRIGGER trg_update_order_total
        AFTER INSERT ON order_items
        FOR EACH ROW
        BEGIN
            UPDATE orders
            SET total = total + (NEW.quantity * NEW.unit_price)
            WHERE order_id = NEW.order_id;
        END
    ";
    
    $pdo->exec($sql);
    echo "Trigger 'trg_update_order_total' created successfully.\n";
}

function drop_trg_update_order_total($pdo) {
    $sql = "DROP TRIGGER IF EXISTS trg_update_order_total";
    $pdo->exec($sql);
    echo "Trigger 'trg_update_order_total' dropped successfully.\n";
}
