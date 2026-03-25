<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $permissions = Permission::withCount('roles')
            ->orderBy('group')
            ->orderBy('display_name')
            ->get()
            ->groupBy('group');

        return view('admin.permissions.index', compact('permissions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $groups = Permission::query()
            ->whereNotNull('group')
            ->where('group', '!=', '')
            ->distinct()
            ->orderBy('group')
            ->pluck('group');

        return view('admin.permissions.create', compact('groups'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:191',
                'regex:/^[a-zA-Z0-9_]+$/',
                Rule::unique('permissions', 'name'),
            ],
            'display_name' => ['required', 'string', 'max:191'],
            'description' => ['nullable', 'string', 'max:1000'],
            'group' => ['nullable', 'string', 'max:191'],
        ]);

        Permission::create([
            'name' => $validated['name'],
            'display_name' => $validated['display_name'],
            'description' => $validated['description'] ?? null,
            'group' => $validated['group'] ?: null,
        ]);

        return redirect()
            ->route('admin.permissions.index')
            ->with('success', 'تم إنشاء الصلاحية بنجاح');
    }

    /**
     * Display the specified resource.
     */
    public function show(Permission $permission)
    {
        $permission->load('roles');

        return view('admin.permissions.show', compact('permission'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Permission $permission)
    {
        $permission->load('roles');

        $groups = Permission::query()
            ->whereNotNull('group')
            ->where('group', '!=', '')
            ->distinct()
            ->orderBy('group')
            ->pluck('group');

        return view('admin.permissions.edit', compact('permission', 'groups'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Permission $permission)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:191',
                'regex:/^[a-zA-Z0-9_]+$/',
                Rule::unique('permissions', 'name')->ignore($permission->id),
            ],
            'display_name' => ['required', 'string', 'max:191'],
            'description' => ['nullable', 'string', 'max:1000'],
            'group' => ['nullable', 'string', 'max:191'],
        ]);

        $permission->update([
            'name' => $validated['name'],
            'display_name' => $validated['display_name'],
            'description' => $validated['description'] ?? null,
            'group' => $validated['group'] ?: null,
        ]);

        return redirect()
            ->route('admin.permissions.index')
            ->with('success', 'تم تحديث الصلاحية بنجاح');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Permission $permission)
    {
        $permission->loadCount('roles');
        if (($permission->roles_count ?? 0) > 0) {
            return back()->with('error', 'لا يمكن حذف صلاحية مرتبطة بأدوار. قم بإزالتها من الأدوار أولاً.');
        }

        $permission->roles()->detach();
        $permission->delete();

        return redirect()
            ->route('admin.permissions.index')
            ->with('success', 'تم حذف الصلاحية بنجاح');
    }
}
