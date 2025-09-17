<?php

if (!function_exists('module_path_info')) {
    /**
     * Get module path information
     * 
     * @param string $module
     * @return string
     */
    function module_path_info($module)
    {
        // Remove 'jiny-' prefix if present
        $moduleName = str_replace('jiny-', '', $module);
        
        // Get module path
        $basePath = base_path('jiny');
        
        // Convert module name to proper case (auth -> Auth)
        $moduleName = ucfirst($moduleName);
        
        return $basePath . '/' . $moduleName;
    }
}