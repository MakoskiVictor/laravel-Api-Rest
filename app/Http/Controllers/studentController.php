<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
// Paquete de validaciones de Laravel
use Illuminate\Support\Facades\Validator;

class studentController extends Controller
{
    // Para obtener lista
    public function index () {
        $students = DB::select("
            SELECT * FROM student
        ");

        if(empty($students)) {
            $data = [
                'message' => 'No hay estudiantes registrados',
                'status' => 200
            ];
            return response()->json($data, 200);
        }

        $data = [
            'students' => $students,
            'status' => 200
        ];

        return response()->json($data, 200);
    }

    // Para almacenar un nuesvo student
    public function store (Request $request) {
        // Validamos los datos
        $validator = Validator::make($request->all(),[
            'name' => 'required|max:50',
            'email' => 'required|email|max:255',
            'phone' => 'required|numeric',
            'language' => 'required|max:50'
        ]);
        if($validator->fails()) {
            // Imprimir fallo en la consola del servidor
            error_log('Validation failed: ' . json_encode($validator->errors()->all()));

            // Retornar mensage genérico
            $data = [
                'message' => 'Something went  wrong',
                'status' => 400
            ];
            return response()->json($data, 400);
        }

        // Crear nuevo estudiante
        $student = DB::insert("
            INSERT INTO student (name, email, phone, language)
            VALUES (?, ?, ?, ?)
        ",[
            $request->input('name'),
            $request->input('email'),
            $request->input('phone'),
            $request->input('language'),
        ]);

        if($student) {
            return response()->json(['message' => 'Student created successfully!'], 201);
        } else {
            error_log('Creation failed: Could not insert student into the database.');
            return response()->json(['message' => 'Something went  wrong'], 500);
        }
    }

    public function getOne ($id) {
        $validator = Validator::make(['id'=>$id], [
            'id' => 'required|numeric'
        ]);
        //echo(json_encode(($validator)));
        // Si falla la validación
        if($validator->fails()){
            // Imprimir en la consola
            error_log('Validation failed ' . json_encode($validator->errors()->all()));

            // Retornar mensaje genérico
            return response()->json(['message' => 'Something went wrong'], 400);
        }

        // Si pasa la validación
        $data = DB::select(" 
            SELECT * FROM student
            WHERE id = ?
         ", [$id]);

         if(empty($data)) {
            return response()->json(['message' => 'There is no Student'], 200);
         }

         return response()->json([$data, 'status' => 200]);
    }

    public function delete ($id) {
        // Validar que sea un numero
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|numeric'
        ]);

        if($validator->fails()) {
            return response()->json(['message' => 'Unespected error', 'status' => 400], 400);
        }

        // Intentar eliminar
        try {
            $data = DB::delete("
                DELETE FROM student
                WHERE id = ?
            ", [$id]);
    
            if($data) {
                return response()->json(['message' => 'Deleted successfully!', 'status' => 203], 203);
            } else {
                return response()->json(['message' => 'Unespected error', 'status' => 404], 404);
            }

        } catch(\Exception $e) {
            error_log('Database Error: ' . $e->getMessage());

            return response()->json(['message' => 'Unespected server error', 'status' => 500], 500);
        }
    }

}
