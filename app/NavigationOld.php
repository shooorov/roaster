<?php

namespace App;

class NavigationOld extends RolePermission
{
    public static function get($use_specific = false)
    {
        $valid_routes = self::routes();

        $search = UseSystemName::search();
        $replace = UseSystemName::replace();

        $menu = collect(config('menu'));

        $navigation = $menu->map(function ($sub_menu) use ($search, $replace, $valid_routes, $use_specific) {
            $sub_menu['items'] = collect($sub_menu['items'])->map(function ($heading) use ($search, $replace, $valid_routes, $use_specific) {
                $heading_name = str_replace($search, $replace, strtolower($heading['name']));
                $heading['name'] = $heading_name != strtolower($heading['name']) ? ucwords($heading_name) : $heading['name'];

                if (isset($heading['items']) && count($heading['items']) > 1) {
                    $heading['items'] = collect($heading['items'])->map(function ($sub_heading) use ($search, $replace, $valid_routes) {
                        $sub_heading_name = str_replace($search, $replace, strtolower($sub_heading['name']));
                        $sub_heading['name'] = $sub_heading_name != strtolower($sub_heading['name']) ? ucwords($sub_heading_name) : $sub_heading['name'];

                        $sub_heading['current'] = request()->route()->named($sub_heading['valid']);
                        $sub_heading['visible'] = in_array($sub_heading['route_name'], $valid_routes);

                        return $sub_heading;
                    });

                    $heading['visible'] = (bool) $heading['items']->first(fn ($item) => $item['visible'] == true);
                    $heading['is_open'] = (bool) $heading['items']->first(fn ($item) => $item['current'] == true);
                } else {
                    $heading['current'] = request()->route()->named($heading['valid']);
                    $heading['visible'] = in_array($heading['route_name'], $valid_routes);
                }

                if (isset($heading['use_specific']) && (bool) $heading['use_specific']) {
                    $heading['visible'] = $heading['visible'] && $use_specific;
                }

                return $heading;
            });

            $sub_menu['visible'] = (bool) $sub_menu['items']->first(fn ($item) => $item['visible'] == true);

            return $sub_menu;
        });

        // dd($navigation->all());
        return $navigation->all();
    }
}
