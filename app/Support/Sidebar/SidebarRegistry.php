<?php

namespace App\Support\Sidebar;

class SidebarRegistry
{
    protected static array $items = [];

    /**
     * Register a sidebar navigation item.
     */
    public static function register(array $item): void
    {
        self::$items[] = array_merge([
            'title' => '',
            'route' => '#',
            'icon' => 'link',
            'activePattern' => '',
            'group' => 'core', // core, modules, admin
            'module' => null,  // associated module slug
            'requiredPermission' => null,
            'order' => 100,
        ], $item);
    }

    /**
     * Retrieve all registered items grouped by their section.
     */
    public static function getGroupedItems(?\App\Models\Company $company = null): array
    {
        $moduleService = app(\App\Services\Platform\ModuleService::class);
        $filtered = [];

        foreach (self::$items as $item) {
            // If the item belongs to a module, ensure it is enabled for the company
            if ($item['module'] && $company) {
                if (!$moduleService->companyHasModule($company, $item['module'])) {
                    continue;
                }
            }
            $filtered[] = $item;
        }

        // Sort items by order
        usort($filtered, function ($a, $b) {
            return $a['order'] <=> $b['order'];
        });

        // Group by group key
        $grouped = [];
        foreach ($filtered as $item) {
            $grouped[$item['group']][] = $item;
        }

        return $grouped;
    }
}
