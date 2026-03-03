<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Subject;
use App\Models\Section;
use App\Models\Enrollment;
use App\Models\Laboratory;
use Faker\Factory as Faker;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create('es_ES');
        $password = Hash::make('12345678'); // Contraseña universal para pruebas

        // 1. Crear Laboratorios
        $labs = [
            'Laboratorio 1',
            'Laboratorio 2',
            'Laboratorio 3',
            'Laboratorio 4',
            'Laboratorio 35',
            'Laboratorio 6',
            'Laboratorio 7',
            'Laboratorio 8',
            'Laboratorio 9',
            'Laboratorio 10',
            'Laboratorio 11',
            'Laboratorio 12',
            'Laboratorio 13',
            'Laboratorio 14',
            'Laboratorio 15'
        ];
        foreach ($labs as $lab) {
            Laboratory::create(['name' => $lab]);
        }

        // 2. Crear Administrador (Tú)
        User::create([
            'name' => 'Admin',
            'email' => 'admin@qrlab.com',
            'password' => $password,
            'role' => 'admin',
            'user_code' => 'ADM-001',
        ]);

        // 3. Crear Estudiantes de Prueba (El equipo QRLAB)
        $estudiantes = [
            User::create(['name' => 'Kevin Vanegas', 'email' => 'kevin@qrlab.com', 'password' => $password, 'role' => 'student', 'user_code' => '2705632024', 'career' => 'Ingeniería en Sistemas']),
            User::create(['name' => 'Diego Hernandez', 'email' => 'diego@qrlab.com', 'password' => $password, 'role' => 'student', 'user_code' => 'DH-2026', 'career' => 'Ingeniería en Sistemas']),
            User::create(['name' => 'Ana Lopez', 'email' => 'ana@qrlab.com', 'password' => $password, 'role' => 'student', 'user_code' => 'AL-2026', 'career' => 'Ingeniería en Sistemas']),
        ];

        // --- NUEVO: GENERAR 50 ESTUDIANTES EXTRA ---
        for ($i = 1; $i <= 50; $i++) {
            $estudiantes[] = User::create([
                'name' => $faker->firstName . ' ' . $faker->lastName,
                'email' => "alumno{$i}@qrlab.com",
                'password' => $password,
                'role' => 'student',
                'user_code' => 'ES-' . str_pad($i, 4, '0', STR_PAD_LEFT) . '-2026', // Ej. ES-0001-2026
                'career' => $faker->randomElement(['Ingeniería en Sistemas', 'Licenciatura en Computación', 'Ingeniería Industrial'])
            ]);
        }
        // -------------------------------------------

        // 4. Crear Materias
        $materiasData = [
            ['name' => 'Programación II', 'code' => 'PRG2'],
            ['name' => 'Redes I', 'code' => 'RED1'],
            ['name' => 'Bases de Datos', 'code' => 'BDD1'],
            ['name' => 'Sistemas Operativos', 'code' => 'SOP1'],
            ['name' => 'Desarrollo Web', 'code' => 'DWE1']
        ];
        $materias = [];
        foreach ($materiasData as $data) {
            $materias[] = Subject::create($data);
        }

        // 5. Crear 20 Docentes
        $docentes = [];
        for ($i = 1; $i <= 20; $i++) {
            $docentes[] = User::create([
                'name' => 'Ing. ' . $faker->firstName . ' ' . $faker->lastName,
                'email' => "docente{$i}@qrlab.com",
                'password' => $password,
                'role' => 'teacher',
                'user_code' => "DOC-" . str_pad($i, 4, '0', STR_PAD_LEFT),
            ]);
        }

        // 6. Asignar Materias a los Docentes (Secciones)
        // Cada docente dará entre 1 y 4 materias en distintos horarios
        $dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];
        $horas = ['08:00 - 10:00', '10:00 - 12:00', '13:00 - 15:00', '15:00 - 17:00'];

        $todasLasSecciones = [];
        foreach ($docentes as $docente) {
            // Un docente imparte entre 1 y 4 clases
            $numClases = rand(1, 4);
            $materiasAsignadas = $faker->randomElements($materias, $numClases);

            foreach ($materiasAsignadas as $materia) {
                $dia = $faker->randomElement($dias);
                $hora = $faker->randomElement($horas);
                $seccionCode = '0' . rand(1, 5); // Ej. 01, 02

                // Usamos firstOrCreate para evitar duplicados exactos que rompan la base
                $seccion = Section::firstOrCreate(
                    ['subject_id' => $materia->id, 'teacher_id' => $docente->id, 'section_code' => $seccionCode],
                    ['schedule' => "$dia $hora"]
                );
                $todasLasSecciones[] = $seccion;
            }
        }

        // 7. Inscribir a los estudiantes en algunas secciones al azar
        foreach ($estudiantes as $estudiante) {
            $seccionesInscritas = $faker->randomElements($todasLasSecciones, 4); // Inscribimos 4 materias por alumno
            foreach ($seccionesInscritas as $seccion) {
                Enrollment::create([
                    'section_id' => $seccion->id,
                    'student_id' => $estudiante->id
                ]);
            }
        }
    }
}