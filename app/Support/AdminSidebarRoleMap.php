<?php

namespace App\Support;

use App\Models\Permission;
use Illuminate\Support\Collection;

final class AdminSidebarRoleMap
{
    /**
     * صفوف جاهزة للعرض في صفحة الدور: أقسام، مجموعات، عناصر + بيانات كل صلاحية.
     *
     * @return list<array{type: string, title?: string, note?: string, label?: string, depth?: int, permissions?: list<array{name: string, id: ?int, display_name?: string, missing: bool, first: bool}>}>
     */
    public static function buildFormRows(): array
    {
        /** @var Collection<string, Permission> $byName */
        $byName = Permission::query()->get()->keyBy('name');
        $renderedIds = [];
        $rows = [];

        foreach (self::sections() as $section) {
            $rows[] = [
                'type' => 'section',
                'title' => $section['title'] ?? '',
                'note' => $section['note'] ?? null,
            ];
            self::appendItemRows($section['items'] ?? [], $rows, $byName, $renderedIds, 0);
        }

        return $rows;
    }

    /**
     * @param  list<array<string, mixed>>  $items
     * @param  list<int>  $renderedIds
     */
    private static function appendItemRows(array $items, array &$rows, Collection $byName, array &$renderedIds, int $depth): void
    {
        foreach ($items as $item) {
            $label = $item['label'] ?? null;
            $children = $item['children'] ?? [];
            $permNames = $item['permissions'] ?? [];
            $permMeta = [];

            foreach ($permNames as $pname) {
                if (! is_string($pname) || $pname === '') {
                    continue;
                }
                $p = $byName->get($pname);
                if (! $p) {
                    $permMeta[] = ['name' => $pname, 'id' => null, 'display_name' => null, 'missing' => true, 'first' => false];

                    continue;
                }
                $first = ! in_array($p->id, $renderedIds, true);
                if ($first) {
                    $renderedIds[] = $p->id;
                }
                $permMeta[] = [
                    'name' => $pname,
                    'id' => $p->id,
                    'display_name' => $p->display_name,
                    'missing' => false,
                    'first' => $first,
                ];
            }

            if ($label !== null && $label !== '') {
                if ($permMeta !== [] || $children === []) {
                    $rows[] = [
                        'type' => 'item',
                        'label' => $label,
                        'note' => $item['note'] ?? null,
                        'depth' => $depth,
                        'permissions' => $permMeta,
                    ];
                } else {
                    $rows[] = [
                        'type' => 'group',
                        'label' => $label,
                        'note' => $item['note'] ?? null,
                        'depth' => $depth,
                    ];
                }
            }

            if ($children !== []) {
                self::appendItemRows($children, $rows, $byName, $renderedIds, $depth + 1);
            }
        }
    }

    /**
     * معرفات الصلاحيات المذكورة في خريطة السايدبار (لتصفية «الصلاحيات الأخرى»).
     *
     * @return list<int>
     */
    public static function permissionIdsInSidebarMap(): array
    {
        $ids = [];
        foreach (self::buildFormRows() as $row) {
            if (($row['type'] ?? '') !== 'item') {
                continue;
            }
            foreach ($row['permissions'] ?? [] as $meta) {
                if (! empty($meta['id'])) {
                    $ids[] = (int) $meta['id'];
                }
            }
        }

        return array_values(array_unique($ids));
    }

    /**
     * تجميع صفوف العرض حسب عنوان القسم (للواجهة).
     *
     * @return list<array{section: array<string, mixed>, rows: list<array<string, mixed>>}>
     */
    public static function blocksForView(): array
    {
        $blocks = [];
        $current = null;
        foreach (self::buildFormRows() as $row) {
            if (($row['type'] ?? '') === 'section') {
                if ($current !== null) {
                    $blocks[] = $current;
                }
                $current = ['section' => $row, 'rows' => []];
            } elseif ($current !== null) {
                $current['rows'][] = $row;
            }
        }
        if ($current !== null) {
            $blocks[] = $current;
        }

        return $blocks;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function sections(): array
    {
        return config('admin_sidebar_role_map.sections', []);
    }

    /**
     * @return list<string>
     */
    public static function introLines(): array
    {
        return config('admin_sidebar_role_map.intro', []);
    }

    /**
     * جميع أسماء الصلاحيات المذكورة في خريطة السايدبار (بدون تكرار).
     *
     * @return list<string>
     */
    public static function permissionNames(): array
    {
        $names = [];
        foreach (self::sections() as $section) {
            self::walkItems($section['items'] ?? [], $names);
        }

        return array_values(array_unique($names));
    }

    /**
     * @param  list<array<string, mixed>>  $items
     * @param  list<string>  $names
     */
    private static function walkItems(array $items, array &$names): void
    {
        foreach ($items as $item) {
            if (! empty($item['permissions']) && is_array($item['permissions'])) {
                foreach ($item['permissions'] as $p) {
                    if (is_string($p) && $p !== '') {
                        $names[] = $p;
                    }
                }
            }
            if (! empty($item['children']) && is_array($item['children'])) {
                self::walkItems($item['children'], $names);
            }
        }
    }
}
