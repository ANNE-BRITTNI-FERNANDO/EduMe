$migrations = @(
    "create_cache_table.php",
    "create_jobs_table.php",
    "create_users_table.php",
    "create_products_table.php",
    "create_bundles_table.php",
    "create_carts_table.php",
    "create_cart_items_table.php",
    "create_payout_requests_table.php",
    "create_seller_balances_table.php",
    "create_orders_table.php",
    "create_order_items_table.php",
    "create_delivery_tracking_table.php",
    "create_sri_lanka_locations_table.php",
    "create_warehouses_table.php",
    "create_notifications_table.php",
    "create_deliveries_table.php",
    "create_delivery_trackings_table.php",
    "add_delivery_fields_to_users.php",
    "add_address_and_phone_to_users.php",
    "add_bundle_id_to_cart_items_table.php",
    "add_user_id_to_bundles_table.php",
    "add_seller_id_to_payout_requests_table.php",
    "add_order_item_id_to_payout_requests_table.php",
    "add_delivery_fee_to_orders_table.php",
    "add_approval_fields_to_bundles_table.php",
    "add_bundle_id_to_conversations_table.php",
    "add_receipt_fields_to_payout_requests_table.php",
    "add_rejection_reason_to_payout_requests_table.php",
    "add_approval_columns_to_payout_requests_table.php",
    "add_details_to_warehouses_table.php",
    "update_payout_requests_table.php",
    "make_product_id_nullable_in_cart_items_table.php",
    "fix_chat_tables.php",
    "fix_conversations_foreign_keys.php"
)

# First, rename all files to a temporary name to avoid conflicts
Get-ChildItem -Path "database/migrations" -Filter "*.php" | ForEach-Object {
    $tempName = "TEMP_" + $_.Name
    Rename-Item -Path $_.FullName -NewName $tempName -Force
}

# Now rename them in the correct order
$counter = 1
foreach ($migration in $migrations) {
    $oldFile = Get-ChildItem -Path "database/migrations" -Filter "*$migration" | Select-Object -First 1
    if ($oldFile) {
        $newName = "2024_12_14_{0:D6}_$migration" -f $counter
        Rename-Item -Path $oldFile.FullName -NewName $newName -Force
        $counter++
    }
}

# Remove any remaining temporary files
Get-ChildItem -Path "database/migrations" -Filter "TEMP_*.php" | Remove-Item -Force
