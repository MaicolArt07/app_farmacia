<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Person;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $idCountry = Country::where('name', 'Bolivia')->value('id')
            ?? Country::query()->value('id');

        $adminPerson = Person::firstOrCreate(
            ['ci' => '0000001'],
            ['name' => 'Administrador', 'lastname' => 'Sistema', 'id_country' => $idCountry, 'state' => 1, 'phone' => '00000000', 'address' => '-']
        );

        $admin = User::firstOrCreate(
            ['email' => 'admin@farmafamilia.com'],
            [
                'id_person' => $adminPerson->id,
                'name' => 'Administrador',
                'password' => Hash::make('Admin123!'),
            ]
        );

        $admin->syncPermissions(Permission::where('guard_name', 'web')->get());

        $empleadoPerson = Person::firstOrCreate(
            ['ci' => '0000002'],
            ['name' => 'Empleado', 'lastname' => 'Farmacia', 'id_country' => $idCountry, 'state' => 1, 'phone' => '00000000', 'address' => '-']
        );

        $empleado = User::firstOrCreate(
            ['email' => 'empleado@farmafamilia.com'],
            [
                'id_person' => $empleadoPerson->id,
                'name' => 'Empleado',
                'password' => Hash::make('Empleado123!'),
            ]
        );

        $empleado->syncPermissions([
            'Ver Inicio',
            'Ver Clientes',
            'Ver Productos',
            'Ver Lotes',
            'Ver Ventas',
            'Crear Ventas',
            'Cambiar Estado Ventas',
            'Ver Caja',
            'Crear Caja',
            'Editar Caja',
            'Cambiar Estado Caja',
            'Ver Movimientos Caja',
            'Crear Movimientos Caja',
            'Ver Kardex',
            'Ver Alertas Inventario',
        ]);
    }
}
