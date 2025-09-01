<?php

namespace App;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class Generator
{
    public static function directories()
    {
        $directories = [
            // base_path('app\PDF'),
            // base_path('app\Models'),

            // base_path('app\Http\Controllers\Admin'),
            // base_path('app\Http\Controllers\Student'),
            // base_path('app\Http\Resources'),

            // base_path('database\migrations'),
            // base_path('database\seeders'),

            // base_path('resources\views\admin'),
            // base_path('resources\views\student'),
        ];

        $replace_words = [
            'exam' => 'written_exam',
            'Exam' => 'WrittenExam',

            // 'question'  => 'written_question',
            // 'Question'  => 'WrittenQuestion',
        ];
        $files_paths = [];
        foreach ($directories as $directory) {
            $files = array_diff(scandir($directory), ['.', '..']);

            foreach ($files as $file) {
                foreach ($replace_words as $string => $replace) {
                    $count = count($files_paths) + 1;
                    $count = ($count > 9 ? '' : '0').$count;
                    $notQuestion = strtolower($string) != 'question';

                    if (str_contains($file, $replace)) {
                        $files_paths[] = "$count . =========================> $directory/$file";
                    } elseif (
                        str_contains($file, $string)
                        && ($notQuestion
                            || (
                                (! str_contains($file, 'exam') ^ str_contains($file, 'written_exam'))
                                && (! str_contains($file, 'Exam') ^ str_contains($file, 'WrittenExam'))
                            )
                        )
                    ) { //second condition for only Question not exam
                        $file_or_directory_path = "$directory/$file";
                        $sl_file_or_directory_path = "$count. $file_or_directory_path";

                        $condition01 = (! str_contains($file, 'exam') ^ str_contains($file, 'written_exam'));
                        $condition02 = (! str_contains($file, 'Exam') ^ str_contains($file, 'WrittenExam'));
                        $padding = str_pad('', 150 - strlen($sl_file_or_directory_path), '=', STR_PAD_LEFT).'>';

                        $files_paths[] = "$sl_file_or_directory_path $padding ($notQuestion || ($condition01 & $condition02))";
                        // self::copyFileOrDirectory($file_or_directory_path);
                    }
                }
            }
        }

        print_r(implode('<br>', $files_paths));
        dd('Hello Shourov');
    }

    public static function copyFileOrDirectory($file_or_directory_path)
    {
        $replace_file = [
            'exam' => 'written_exam',
            'Exam' => 'WrittenExam',

            'question' => 'written_question',
            'Question' => 'WrittenQuestion',
        ];
        $replace_words = [
            'exam' => 'written_exam',
            'Exam' => 'WrittenExam',

            'question' => 'written_question',
            'Question' => 'WrittenQuestion',
        ];

        if (is_dir($file_or_directory_path)) {
            $files = array_diff(scandir($file_or_directory_path), ['.', '..']);
            foreach ($files as $file) {
                $file_path = "$file_or_directory_path/$file";

                self::copyFileOrDirectory($file_path);
            }
        } elseif (is_file($file_or_directory_path)) {
            $file_path = $file_or_directory_path;
            $content = file_get_contents($file_path);

            foreach ($replace_words as $string => $replace) {
                $content = str_replace($string, $replace, $content);
            }
            foreach ($replace_file as $string => $replace) {
                if (! str_contains($file_path, $replace) && str_contains($file_path, $string)) {
                    $file_path = str_replace($string, $replace, $file_path);
                    MakeFile::create($content, $file_path ?? public_path());
                }
            }
        }
    }

    public static function getSpaces($highest_column_length = 20, $column_length = 10)
    {
        $padding_char = ' ';
        $before_padding = '';
        $padding_length = $highest_column_length - $column_length;

        return Str::padLeft($before_padding, $padding_length, $padding_char);
    }

    public static function getIcon()
    {
        $versions = [
            base_path('files\hero-icon-v1.vue'),
            base_path('files\hero-icon-v2.vue'),
        ];

        $icons = [];
        foreach ($versions as $key => $version) {
            $v = 'v'.$key + 1;
            foreach (file($version) as $index => $line_content) {
                $after = '"></h1>';
                $before = '<h1 title="';
                if (str_contains($line_content, 'title')) {
                    $icon = Str::between($line_content, $before, $after);
                    $icon_key = explode('-', $icon)[0];
                    // $icons[$icon_key][$v][$icon] = Str::of($icon)->replace('-', ' ')->title()->studly()->value() . 'Icon';
                    $array[$icon] = $v;
                    $icons[$icon_key][] = "$icon => $v";
                }
            }
        }
        dd($icons);
    }

    public static function getStringsFromRecord($record)
    {
        $name = class_basename($record); // default studlyCase
        $class = get_class($record);
        $object = Str::of($name)->snake()->lower()->value;
        $object_id = $object.'_id';
        $name_plural = Str::of($name)->snake()->plural()->replace('_', ' ')->title()->value;
        $object_plural = Str::of($object)->plural()->value;

        $params = [
            'id' => 'id => '.$record->id,
            'name' => 'name => '.$name,
            'class' => 'class => '.$class,
            'object' => 'object => '.$object,
            'object_id' => 'object_id => '.$object_id,
            'name_plural' => 'name_plural => '.$name_plural,
            'object_plural' => 'object_plural => '.$object_plural,
        ];

        return (object) [
            'params' => $params,
            'name' => $name,
            'class' => $class,
            'object' => $object,
            'name_plural' => $name_plural,
            'object_plural' => $object_plural,
        ];
    }

    public static function getStringsFromString($string)
    {
        $snake = Str::of($string)->lower()->snake();
        if (str_contains($snake, '_id')) {
            $string = Str::of($snake)->replace('_id', '')->replace('_', ' ')->title();
        }

        $class = Str::of($string)->studly()->value;
        $object = Str::of($string)->lower()->snake()->value;
        $object_name = Str::of($object)->append('_name')->value;
        $child_table = Str::of($string)->plural()->value;
        $object_plural = Str::of($object)->plural()->value;
        $string_plural = Str::of($string)->plural()->value;

        return [
            'name' => $string,
            'class' => $class,
            'object' => $object,
            'object_name' => $object_name,
            'name_plural' => $string_plural,
            'child_table' => $child_table,
            'object_plural' => $object_plural,
        ];
    }

    public static function getChildrenFromDatabase($record)
    {
        $name = class_basename($record);
        $class = get_class($record);
        $object = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $name));
        $object_id = $object.'_id';
        $object_plural = Str::plural($object);

        $params = [
            'id' => 'id => '.$record->id,
            'name' => 'name => '.$name,
            'class' => 'class => '.$class,
            'object' => 'object => '.$object,
            'object_id' => 'object_id => '.$object_id,
            'object_plural' => 'object_plural => '.$object_plural,
        ];

        print_r(implode('<br>', $params));
        print_r('<br><br>');
        $directory = base_path('database\migrations');
        $directory_files = array_diff(scandir($directory), ['.', '..']);

        $array = [];
        foreach ($directory_files as $file) {
            if (is_dir($file_path = "$directory/$file")) {
                continue;
            }

            $content = file_get_contents($file_path);
            $contain = str_contains($content, $object_id) || str_contains($content, $object_plural);
            $not_self = ! str_contains($content, "Schema::create('$object_plural', function");
            if ($not_self && $contain) {
                $sub_array = [];
                foreach (file($file_path) as $line_index => $line_content) {
                    $before = "('";
                    $after = "', function (Blueprint";
                    if (str_contains($line_content, $after)) {
                        $child = Str::between($line_content, $before, $after);
                        // $child = Str::of($child)->when(!in_array($child, ['leaves', 'receives']), function ($child) {
                        //     return $child->singular();
                        // })->studly();
                        $sub_array[] = $child;
                        $sub_array = array_unique($sub_array);
                    }
                }
                $array[] = $file.' => '.implode('||', $sub_array);
            }
        }

        print_r(implode('<br>', $array));
        dd();
    }

    public static function getChildrenFromMigration($record)
    {
        $name = class_basename($record);
        $class = get_class($record);
        $object = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $name));
        $object_id = $object.'_id';
        $object_plural = Str::plural($object);

        $params = [
            'id' => 'id => '.$record->id,
            'name' => 'name => '.$name,
            'class' => 'class => '.$class,
            'object' => 'object => '.$object,
            'object_id' => 'object_id => '.$object_id,
            'object_plural' => 'object_plural => '.$object_plural,
        ];

        print_r(implode('<br>', $params));
        print_r('<br><br>');
        $directory = base_path('database\migrations');
        $directory_files = array_diff(scandir($directory), ['.', '..']);

        $array = [];
        foreach ($directory_files as $file) {
            if (is_dir($file_path = "$directory/$file")) {
                continue;
            }

            $content = file_get_contents($file_path);
            $contain = str_contains($content, $object_id) || str_contains($content, $object_plural);
            $not_self = ! str_contains($content, "Schema::create('$object_plural', function");
            if ($not_self && $contain) {
                $sub_array = [];
                foreach (file($file_path) as $line_content) {
                    $before = "('";
                    $after = "', function (Blueprint";
                    if (str_contains($line_content, $after)) {
                        $child = Str::between($line_content, $before, $after);
                        // $child = Str::of($child)->when(!in_array($child, ['leaves', 'receives']), function ($child) {
                        //     return $child->singular();
                        // })->studly();
                        $sub_array[] = $child;
                        $sub_array = array_unique($sub_array);
                    }
                }
                $array[] = $file.' => '.implode('||', $sub_array);
            }
        }

        print_r(implode('<br>', $array));
        dd();
    }

    public static function getChildrenFromModel($record)
    {
        $strings = self::getStringsFromRecord($record);

        $children = [];
        $file_path = base_path("app/Models/$strings->name.php");

        $file_in_array = file($file_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($file_in_array as $line_index => $line_content) {
            $before = 'hasMany(';
            $after = '::class';
            if (str_contains($line_content, $before)) {
                $child_model = Str::between($line_content, $before, $after);

                $before = 'public function ';
                $after = '()';
                $child_method = Str::between($file_in_array[$line_index - 2], $before, $after);
                $child_list = $record->$child_method;
                $children[$child_method] = [
                    'name' => Str::of($child_model)->snake()->replace('_', ' ')->title()->value,
                    'count' => count($child_list),
                    'records' => count($child_list) ? $child_list : [],
                ];
            }
        }

        return $children;
    }

    public static function getDatabaseDetails()
    {
        $ignore_models = [
            // 'Account',
        ];

        $filter_models = [
            'Employee',
            'Order',
            'OrderProduct',
        ];

        $tables = UseDatabase::tables();
        $tables_list = [];
        foreach ($tables as $table) {
            $table = Str::of($table)->when(! Str::endsWith($table, 'ves'), function ($string) {
                return $string->singular();
            })->when(Str::endsWith($table, 'ves'), function ($string) {
                return $string->replace('ves', 've');
            })->studly()->value;
            // array_push($tables_list, $table);
            // !in_array($table, $ignore_models) ? array_push($tables_list, $table) : null;
            in_array($table, $filter_models) ? array_push($tables_list, $table) : null;
        }

        $tables = [];
        foreach ($tables_list as $table) {
            $tables[$table] = [
                'table' => Str::of($table)->snake()->plural()->value,
                'columns' => [],
                'fails' => [],
                'hasMany' => [],
                'belongsTo' => [],
            ];
        }

        foreach ($tables_list as $table) {
            $file_path = base_path("app\Models\\$table.php");

            if (! file_exists($file_path) || in_array($table, $ignore_models)) {
                continue;
            }

            $table_name = Str::of($table)->snake()->plural()->value;
            $model = "App\Models\\".$table;
            $model = new $model;
            $table_columns = Schema::getColumnListing($table_name);

            $remove_columns = ['created_at', 'updated_at', 'deleted_at'];
            foreach ($remove_columns as $remove_column) {
                if (($key = array_search($remove_column, $table_columns)) !== false) {
                    unset($table_columns[$key]);
                }
            }
            $tables[$table]['columns'] = $table_columns;

            foreach ($table_columns as $column) {
                $use_column = Str::of($column)->replace('_id', '')->studly()->value;

                if (array_key_exists($use_column, $tables)) {
                    array_push($tables[$use_column]['hasMany'], $table);
                } else {
                    array_push($tables[$table]['fails'], $column);
                }

                if (str_contains($column, '_id')) {
                    $use_column = Str::of($column)->replace('_id', '')->studly()->value;
                    array_push($tables[$table]['belongsTo'], $use_column);
                }
            }
        }

        return $tables;
    }

    public static function relation()
    {
        $tables = self::getDatabaseDetails();

        $rows = [];
        $headers = [
            '<th style="border: 1px solid black; width: 10%"></th>',
            // '<th style="border: 1px solid black; width: 30%">Columns</th>',
            // '<th style="border: 1px solid black; width: 20%">Fails</th>',
            '<th style="border: 1px solid black; width: 20%">Has Many</th>',
            '<th style="border: 1px solid black; width: 20%">Belongs To</th>',
        ];
        $rows[] = '<tr>'.implode('', $headers).'</tr>';

        foreach ($tables as $model => $table) {
            $tds = [];
            if (count($table['hasMany'])) {
                $tds[] = '<th style="border: 1px solid black;">'.$model.'</th>';
                // $tds[] = '<td style="border: 1px solid black;">' . implode(', ', $table['columns']) . '</td>';
                // $tds[] = '<td style="border: 1px solid black;">' . implode('<br>', $table['fails']) . '</td>';
                $tds[] = '<td style="border: 1px solid black;">'.implode(', ', $table['hasMany']).'</td>';
                $tds[] = '<td style="border: 1px solid black;">'.implode(', ', $table['belongsTo']).'</td>';

                $rows[] = '<tr>'.implode('', $tds).'</tr>';
            }
        }
        $rows = implode('', $rows);

        $string = '
        <div style="margin: auto; width: 90%;">
            <h1 style="text-align:center"> Table Relations </h1>

            <table style="border: 1px solid black; margin: auto;">
                <tbody>
                    '.$rows.'
                </tbody>
            </table>
        </div>';

        // return $tables;
        dd($tables);
        echo $string;
        dd();
    }

    public static function getDatabaseDiagram()
    {
        $directory = base_path('database\migrations');
        $directory_files = array_diff(scandir($directory), ['.', '..']);

        $filter = [
            'organizations',
            'clients',
            'item_categories',
            'items',
            'projects',
            'requisitions',
        ];
        $tables = [];
        foreach ($directory_files as $file) {
            if (is_dir($file_path = "$directory/$file")) {
                continue;
            }

            $table_open = false;
            $count = 0;
            $dummy_array = [];
            foreach (file($file_path) as $line_index => $line_content) {
                $ignores = [
                    'table->id',
                    'table->timestamps',
                    'table->softDeletes',
                    'table->rememberToken',
                    'dropColumn',
                ];
                $skip = false;
                foreach ($ignores as $ignore) {
                    if (str_contains($line_content, $ignore)) {
                        $skip = true;
                        break;
                    }
                }

                if (! $table_open && (str_contains($line_content, 'Schema::create') ^ str_contains($line_content, 'Schema::table'))) { // getting table
                    $table_open = true;
                    $new_table_before = "Schema::create('";
                    $old_table_before = "Schema::table('";
                    $new_table = str_contains($line_content, $new_table_before);

                    $before = $new_table ? $new_table_before : $old_table_before;
                    $after = "', function (Blueprint";

                    $table = Str::between($line_content, $before, $after);
                }
                if ($table_open && str_contains($line_content, '}')) {
                    $table_open = false;

                    continue;
                }
                if (! $table_open || $skip || ! str_contains($line_content, 'table->') || ! str_contains($line_content, "'")) {
                    continue;
                }

                $key = $count++;
                $column = explode("'", $line_content);
                // dd([
                //     'table_open'    => $table_open,
                //     'file_path'     => $file_path,
                //     'line'          => $line_index + 1,
                //     'content'       => $line_content,
                // ]);

                $type = 'varchar';
                $column = $column[1];
                if (str_contains($line_content, 'foreignId')) {
                    $native_constrained = str_contains($line_content, 'constrained()');
                    $table_from_column = Str::of($column)->replace('_id', '')->plural()->value;
                    $table_from_constrained = Str::between($line_content, "constrained('", "')");
                    $key = $native_constrained ? $table_from_column : $table_from_constrained;
                    $type = 'foreignId';
                    $dummy_array["$column"]['table'] = $key;
                } else {
                }
                // dd($tables, $key, $column);
                $dummy_array["$column"]['type'] = $type;
            }
            if (! in_array($table, $filter)) {
                continue;
            }

            $tables[$table] = $dummy_array;
            // $tables[$table] = array_unique($tables[$table]);
        }

        dd($tables);
        $content = '';
        foreach ($tables as $table => $columns) {
            $start = "Table $table {";
            $end = '}';

            $column_array = [];
            $column_array[] = 'id int';
            foreach ($columns as $key => $column) {
                if ($column['type'] == 'foreignId') {
                    $child = $column['table'];
                    $column_array[] = $key." int [ref: > $child.id]";
                } else {
                    // $column_array[] = $key . ' varchar';
                }
            }
            $content .= $start."\n    ".implode("\n    ", $column_array)."\n".$end."\n\n";
        }

        $file_path = base_path('files\database\db-diagram.io.txt');
        MakeFile::create($content, $file_path ?? public_path());
    }
}
