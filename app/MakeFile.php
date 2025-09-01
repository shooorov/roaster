<?php

namespace App;

class MakeFile
{
    public static function makeCache()
    {
        $tables = Generator::getDatabaseDetails();
        $filter_tables = [
            'Address',
            'Event',
            'Expense',
            'ItemInventory',
            'ItemInventoryItem',
            'Order',
            'OrderProductTopping',
            'OtherSale',
            'ProductInventory',
            'ProductInventoryProduct',
            'ProductItem',
            'ProductTopping',
            'Requisition',
            'RequisitionItem',
            'Topping',
            'Transaction',
            'Unit',
            // "Document",
            // "FailedJob",
            // "Image",
            // "Migration",
            // "Notification",
            // "PasswordReset",
            // "PersonalAccessToken",
            // "Remark",
            // "Status",
            // "UnitConversion",
        ];

        foreach ($tables as $table => $array) {
            if (! in_array($table, $filter_tables) || ! file_exists(base_path("app\Models\\$table.php"))) {
                continue;
            }

            $file_path = base_path('files\Generator\app\Http\Cache\Cache.stub');

            $order_by_n_get = [];
            in_array('name', $array['columns']) ? array_push($order_by_n_get, "orderBy('name')") : null;
            array_push($order_by_n_get, count($order_by_n_get) ? 'get()' : 'all()');
            $order_by_n_get = implode('->', $order_by_n_get);

            $content = file_get_contents($file_path);
            $content = str_replace('___CLASS___', $table, $content);
            $content = str_replace('___OBJECT___', $array['table'], $content);
            $content = str_replace('___ORDER_BY_N_GET___', $order_by_n_get, $content);

            $file_path = base_path("app\Http\Cache/")."$table.php";

            self::create($content, $file_path ?? public_path());
        }
    }

    public static function create($content, $file_path)
    {
        $file_directory = dirname($file_path);
        if (! file_exists($file_directory)) {
            mkdir($file_directory, 0777, true);
        }
        $fp = fopen($file_path, 'wb');
        fwrite($fp, $content);
        fclose($fp);
    }
}
