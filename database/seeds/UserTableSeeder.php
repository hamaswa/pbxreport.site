<?php

use Illuminate\Database\Seeder;
use App\Role;
use App\Permission;
use App\User;
use App\Permissions\HasPermissionsTrait;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $role = Role::where('slug','administrator')->first();
        $perm = Permission::where('slug','super')->first();

        $developer = new User();
        $developer->name = 'Admin Admin';
        $developer->email = 'support@nautilus-network.com';
        $developer->password = bcrypt('boy2cat4');
        $developer->save();
        $developer->roles()->attach($role);
        $developer->permissions()->attach($perm);

        $role = Role::where('slug','user')->first();
        $perm = Permission::where('slug','edit-user')->first();
        $user = new User();
        $user->name="Hamayun Khan";
        $user->email = 'hama_swa@yahoo.com';
        $user->password = bcrypt('Hama@123');
        $user->save();
        $user->roles()->attach($role);
        $user->permissions()->attach($perm);
    }
}
