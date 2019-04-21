<?php

use Illuminate\Database\Seeder;
use App\Role;
use App\Permission;

class PermissionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $role = new Role();
        $admin_role = $role->where('slug','administrator')->first();

        $createTasks = new Permission();
        $createTasks->slug = 'super';
        $createTasks->name = 'Super';
        $createTasks->save();
        $createTasks->roles()->attach($admin_role);

        $user_role = $role->where('slug','user')->first();
        $createTasks = new Permission();
        $createTasks->slug = 'edit-user';
        $createTasks->name = 'Edit User';
        $createTasks->save();
        $createTasks->roles()->attach($user_role);


        $createTasks = new Permission();
        $createTasks->slug = 'delete-user';
        $createTasks->name = 'Delete User';
        $createTasks->save();


    }
}
