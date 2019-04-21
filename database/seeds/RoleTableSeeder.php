<?php

use Illuminate\Database\Seeder;
use App\Role;
use App\Permission;

class RoleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permission = Permission::where('slug','super')->first();

        $dev_role = new Role();
        $dev_role->slug = 'administrator';
        $dev_role->name = 'Administrator';
        $dev_role->save();
        $dev_role->permissions()->attach($permission);

        $permission = Permission::where('slug','edit-user')->first();
        $dev_role = new Role();
        $dev_role->slug = 'user';
        $dev_role->name = 'User';
        $dev_role->save();
        $dev_role->permissions()->attach($permission);

    }
}
