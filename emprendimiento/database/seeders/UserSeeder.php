<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create([
            'nombre' => 'Linka Yujra',
            'email' => 'linkayujrapoma@gmail.com',
            'password' => Hash::make('hana12345M'),
            'rol' => 'administrador',
            'telefono' => '79531213',
            'direccion' => 'Oficina Principal',
            'created_by' => null,
        ]);

        $propietarios = [
            [
                'nombre' => 'Juan Pérez',
                'email' => 'juan@gmail.com',
                'password' => Hash::make('password123'),
                'rol' => 'propietario',
                'telefono' => '3001111111',
                'direccion' => 'Calle 123 #45-67',
                'created_by' => $admin->id,
            ],
            [
                'nombre' => 'María García',
                'email' => 'maria@gmail.com',
                'password' => Hash::make('password123'),
                'rol' => 'propietario',
                'telefono' => '3002222222',
                'direccion' => 'Avenida Principal #89-10',
                'created_by' => $admin->id,
            ],
            [
                'nombre' => 'Carlos Rodríguez',
                'email' => 'carlos@gmail.com',
                'password' => Hash::make('password123'),
                'rol' => 'propietario',
                'telefono' => '3003333333',
                'direccion' => 'Carrera 56 #78-90',
                'created_by' => $admin->id,
            ]
        ];

        $propietariosIds = [];
        foreach ($propietarios as $propietarioData) {
            $propietario = User::create($propietarioData);
            $propietariosIds[] = $propietario->id;
        }
        $residentes = [
            [
                'nombre' => 'Ana López',
                'email' => 'ana@gmail.com',
                'password' => Hash::make('password123'),
                'rol' => 'residente',
                'telefono' => '3004444444',
                'direccion' => 'Apartamento 101, Torre A',
                'created_by' => $propietariosIds[0], 
            ],
            [
                'nombre' => 'Pedro Martínez',
                'email' => 'pedro@gmail.com',
                'password' => Hash::make('password123'),
                'rol' => 'residente',
                'telefono' => '3005555555',
                'direccion' => 'Apartamento 102, Torre A',
                'created_by' => $propietariosIds[0], 
            ],
            [
                'nombre' => 'Laura Sánchez',
                'email' => 'laura@gmail.com',
                'password' => Hash::make('password123'),
                'rol' => 'residente',
                'telefono' => '3006666666',
                'direccion' => 'Apartamento 201, Torre B',
                'created_by' => $propietariosIds[0], 
            ],
            [
                'nombre' => 'Miguel Torres',
                'email' => 'miguel@gmail.com',
                'password' => Hash::make('password123'),
                'rol' => 'residente',
                'telefono' => '3007777777',
                'direccion' => 'Apartamento 202, Torre B',
                'created_by' => $propietariosIds[1],
            ],
            [
                'nombre' => 'Sofía Ramírez',
                'email' => 'sofia@gmail.com',
                'password' => Hash::make('password123'),
                'rol' => 'residente',
                'telefono' => '3008888888',
                'direccion' => 'Apartamento 301, Torre C',
                'created_by' => $propietariosIds[1],
            ],
            [
                'nombre' => 'Diego Herrera',
                'email' => 'diego@gmail.com',
                'password' => Hash::make('password123'),
                'rol' => 'residente',
                'telefono' => '3009999999',
                'direccion' => 'Apartamento 302, Torre C',
                'created_by' => $propietariosIds[1],
            ],
            [
                'nombre' => 'Elena Castro',
                'email' => 'elena@gmail.com',
                'password' => Hash::make('password123'),
                'rol' => 'residente',
                'telefono' => '3010000000',
                'direccion' => 'Apartamento 401, Torre D',
                'created_by' => $propietariosIds[2], 
            ],
            [
                'nombre' => 'Jorge Mendoza',
                'email' => 'jorge@gmail.com',
                'password' => Hash::make('password123'),
                'rol' => 'residente',
                'telefono' => '3011111111',
                'direccion' => 'Apartamento 402, Torre D',
                'created_by' => $propietariosIds[2], 
            ],
            [
                'nombre' => 'Carmen Vargas',
                'email' => 'carmen@gmail.com',
                'password' => Hash::make('password123'),
                'rol' => 'residente',
                'telefono' => '3012222222',
                'direccion' => 'Apartamento 501, Torre E',
                'created_by' => $propietariosIds[2], 
            ],
            [
                'nombre' => 'Ricardo Silva',
                'email' => 'ricardo@gmail.com',
                'password' => Hash::make('password123'),
                'rol' => 'residente',
                'telefono' => '3013333333',
                'direccion' => 'Apartamento 502, Torre E',
                'created_by' => $propietariosIds[2],
            ]
        ];

        foreach ($residentes as $residente) {
            User::create($residente);
        }
    }
}