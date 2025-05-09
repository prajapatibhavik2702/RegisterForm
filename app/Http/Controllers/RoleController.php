
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

public function store(Request $request)
{
    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => bcrypt($request->password),
    ]);

    // Step 1: Assign Role
    $user->assignRole($request->role);

    // Step 2 (Optional): Assign Role's Permissions Directly
    $role = Role::where('name', $request->role)->first();
    $permissions = $role->permissions->pluck('name')->toArray();
    $user->syncPermissions($permissions); // Direct assign

    return response()->json(['message' => 'User created with role and permissions.']);
}



public function updateUser(Request $request, $id)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email,' . $id,
        'role_name' => 'required|string|exists:roles,name',
    ]);

    $user = User::findOrFail($id);

    // Check if role is changing
    $currentRole = $user->roles->pluck('name')->first(); // get current role
    $newRole = $request->role_name;

    // Update user data
    $user->update([
        'name' => $request->name,
        'email' => $request->email,
    ]);

    if ($currentRole !== $newRole) {
        // Step 1: Sync new role
        $user->syncRoles([$newRole]);

        // Step 2: Get permissions from new role
        $role = Role::where('name', $newRole)->first();
        $newPermissions = $role->permissions->pluck('name')->toArray();

        // Step 3: Sync permissions to user
        $user->syncPermissions($newPermissions);
    }

    return response()->json([
        'message' => 'User updated successfully.',
        'role_changed' => $currentRole !== $newRole,
        'current_role' => $newRole,
    ]);
}


public function destroy($id)
{
    $user = User::findOrFail($id);

    // Optional: Remove roles and permissions before deleting
    $user->syncRoles([]);         // remove all roles
    $user->syncPermissions([]);   // remove all direct permissions

    $user->delete();

    return response()->json(['message' => 'User deleted successfully.']);
}


