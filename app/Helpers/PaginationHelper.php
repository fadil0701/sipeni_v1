<?php

namespace App\Helpers;

class PaginationHelper
{
    /**
     * Get validated per page value from request
     * 
     * @param \Illuminate\Http\Request $request
     * @param int $default
     * @return int
     */
    public static function getPerPage($request, $default = 10)
    {
        $perPage = $request->get('per_page', $default);
        $allowed = [10, 25, 50, 100];
        
        return in_array($perPage, $allowed) ? $perPage : $default;
    }
}






