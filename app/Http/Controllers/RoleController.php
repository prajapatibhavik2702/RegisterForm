
composer require spatie/laravel-permission

php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"

php artisan migrate

roles,permissions,model_has_roles,model_has_permissions,role_has_permissions

use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasRoles;
}

php artisan make:seeder RolePermissionSeeder

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'view dashboard',
            'manage users',
            'edit profile',
            'assign tasks',
            'view reports',
            'create booking',
            'cancel booking',
            'approve request',
            'send notification',
            'update settings',
        ];

        // Create Permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create Roles and Assign Permissions
        $admin = Role::firstOrCreate(['name' => 'Admin']);
        $staff = Role::firstOrCreate(['name' => 'Staff']);
        $hotel = Role::firstOrCreate(['name' => 'Hotel']);

        // Assign permissions
        $admin->syncPermissions($permissions);
        $staff->syncPermissions(['view dashboard', 'assign tasks', 'view reports']);
        $hotel->syncPermissions(['view dashboard', 'create booking', 'cancel booking']);
    }
}

php artisan db:seed --class=RolePermissionSeeder

use App\Models\User;
$user = User::find(1); // or use Auth::user()
$user->assignRole('Admin'); // or 'Staff' or 'Hotel'

$user->syncRoles(['Staff']);


Route::group(['middleware' => ['role:Admin']], function () {
    Route::get('/admin/dashboard', 'AdminController@dashboard');
});

if ($user->can('view dashboard')) {
    // Allow access
}

@can('manage users')
    <a href="/users">Manage Users</a>
@endcan

'role' => \Spatie\Permission\Middlewares\RoleMiddleware::class,
'permission' => \Spatie\Permission\Middlewares\PermissionMiddleware::class,


//Role Side this code

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

public function createRoleWithPermissions(Request $request)
{
    $request->validate([
        'role_name'    => 'required|string|unique:roles,name',
        'permissions'  => 'required|array', // e.g. ['edit post', 'delete post']
    ]);

    // 1. Create Role
    $role = Role::create(['name' => $request->role_name]);

    // 2. Assign Permissions
    $role->syncPermissions($request->permissions);

    return response()->json([
        'message' => 'Role created and permissions assigned.',
        'role'    => $role->name,
    ]);
}



public function updateRolePermissions(Request $request, $roleId)
{
    $request->validate([
        'permissions' => 'required|array', // permission names or IDs
    ]);

    $role = Role::findOrFail($roleId);

    // 1. Update Role Permissions
    $role->syncPermissions($request->permissions);

    // 2. Optional: Sync with all users having this role (if using direct permissions on users)
    $users = \App\Models\User::role($role->name)->get();

    foreach ($users as $user) {
        $user->syncPermissions($role->permissions); // sync updated perms
    }

    return response()->json([
        'message' => 'Role permissions updated successfully and synced to users.'
    ]);
}

public function deleteRole($id)
{
    $role = Role::findOrFail($id);

    $role->permissions()->detach();

    // Delete the role
    $role->delete();

    return response()->json([
        'message' => 'Role deleted successfully.',
    ]);
}
//Role Exit Code pls..








public function store(Request $request)
{
    $request->validate([
        'name'      => 'required|string|max:255',
        'email'     => 'required|email|unique:users,email',
        'password'  => 'required|string|min:6|confirmed',
        'role'      => 'required|string|exists:roles,name',
    ]);

    // Create user
    $user = User::create([
        'name'     => $request->name,
        'email'    => $request->email,
        'password' => bcrypt($request->password),
    ]);

    // Assign role
    $user->assignRole($request->role);

    // Get permissions from the role
    $role = Role::where('name', $request->role)->first();
    $permissions = $role->permissions->pluck('name')->toArray();

    // Assign those permissions directly (optional)
    $user->syncPermissions($permissions);

    return response()->json([
        'message' => 'User created successfully with role and permissions.',
        'user_id' => $user->id,
    ]);
}


public function update(Request $request, $id)
{
    $request->validate([
        'name'  => 'required|string|max:255',
        'email' => 'required|email|unique:users,email,' . $id,
        'role'  => 'required|string|exists:roles,name',
    ]);

    $user = User::findOrFail($id);

    // Update basic info
    $user->update([
        'name'  => $request->name,
        'email' => $request->email,
    ]);

    // Remove old role and assign new one
    $user->syncRoles([$request->role]);

    // Assign updated permissions from new role
    $role = Role::where('name', $request->role)->first();
    $permissions = $role->permissions->pluck('name')->toArray();
    $user->syncPermissions($permissions);

    return response()->json([
        'message' => 'User updated successfully with new role and permissions.'
    ]);
}

public function destroy($id)
{
    $user = User::findOrFail($id);

    // Optional cleanup
    $user->syncRoles([]);
    $user->syncPermissions([]);

    $user->delete();

    return response()->json([
        'message' => 'User deleted successfully.'
    ]);
}





