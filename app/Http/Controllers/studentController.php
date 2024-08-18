<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
// Paquete de validaciones de Laravel
use Illuminate\Support\Facades\Validator;
// Respuestas genéricas
use App\Helpers;
use App\Helpers\ResponseHelper;

class studentController extends Controller
{
    // Para obtener lista
    public function index () {
        $students = DB::select("
            SELECT * FROM student
        ");

        if(empty($students)) {
            return ResponseHelper::successResponseWithData($students, 'No students registered');
        }

        return ResponseHelper::successResponseWithData($students);
    }

    // Para almacenar un nuesvo student
    public function store (Request $request) {
        // Validamos los datos
        $validator = Validator::make($request->all(),[
            'name' => 'required|string|max:50',
            'email' => 'required|email|max:255',
            'phone' => 'required|numeric',
            'language' => 'required|string|max:50'
        ]);
        if($validator->fails()) {
            // Imprimir fallo en la consola del servidor
            error_log('Validation failed: ' . json_encode($validator->errors()->all()));

            // Retornar mensage genérico
            return ResponseHelper::errorResponse();
        }

        // Crear nuevo estudiante
        try {
            $database = DB::transaction("
                INSERT INTO student (name, email, phone, language)
                VALUES (?, ?, ?, ?)
            ",[
                $request->input('name'),
                $request->input('email'),
                $request->input('phone'),
                $request->input('language'),
            ]);
    
            if($database) {
                return ResponseHelper::successResponse('Success', 201);
            } else {
                error_log('Creation failed: Could not insert student into the database.');
                return ResponseHelper::errorResponse();
            }
        } catch (\Exception $e) {
            error_log('Database error: ' . json_encode($e));
            return ResponseHelper::serverErrorResponse();
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
            return ResponseHelper::errorResponse();
        }

        // Si pasa la validación
        $data = DB::select(" 
            SELECT * FROM student
            WHERE id = ?
         ", [$id]);

         if(empty($data)) {
            return ResponseHelper::notFoundResponse();
         }

         return ResponseHelper::successResponseWithData($data);
    }

    public function delete ($id) {
        // Validar que sea un numero
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|numeric'
        ]);

        if($validator->fails()) {
            // Imprimir en la consola
            error_log('Validation failed ' . json_encode($validator->errors()->all()));
            return ResponseHelper::errorResponse();
        }

        // Intentar eliminar
        try {
            $data = DB::delete("
                DELETE FROM student
                WHERE id = ?
            ", [$id]);
    
            if($data) {
                return ResponseHelper::successResponse();
            } else {
                return ResponseHelper::errorResponse();
            }

        } catch(\Exception $e) {
            error_log('Database Error: ' . $e->getMessage());

            return ResponseHelper::serverErrorResponse();
        }
    }

    public function update (Request $request, $id) {
        // Validación
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:50',
            'email' => 'required|email|max:255',
            'phone' => 'required|numeric',
            'language' => 'required|string|max:50',
        ]);

        if($validator->fails()) {
            // Imprimir en la consola
            error_log('Validation failed ' . json_encode($validator->errors()->all()));
            return ResponseHelper::errorResponse();
        }

        try {
            $data = DB::update("
                UPDATE student
                SET name = ?, email = ?, phone = ?, language = ?, updated_at = CURRENT_TIMESTAMP
                WHERE id = ?
            ", [
                $request->input('name'),
                $request->input('email'),
                $request->input('phone'),
                $request->input('language'),
                $id
            ]);
    
            if($data) {
                return ResponseHelper::successResponseWithData($data);
            } else {
                return ResponseHelper::errorResponse();
            }
        } catch (\Exception $e) {
            error_log('Database error: '.$e->getMessage());

            return ResponseHelper::serverErrorResponse();
        }
    }

    public function updateOne (Request $request, $id) {
        // Validación
        $validator = Validator::make($request->all(), [
            'name' => 'string|max:50|nullable',
            'email' => 'email|max:255|nullable',
            'phone' => 'numeric|nullable',
            'language' => 'string|max:50|nullable',
        ]);

        if($validator->fails()) {
            return ResponseHelper::errorResponse();
        }

        // Contruyo una query dinámica
        $fields = [];
        $values = [];

        if($request->filled('name')) {
            $fields[] = 'name = ?';
            $values[] = $request->input('name');
        }
        if($request->filled('email')) {
            $fields[] = 'email = ?';
            $values[] = $request->input('email');
        }
        if($request->filled('phone')) {
            $fields[] = 'phone = ?';
            $values[] = $request->input('phone');
        }
        if($request->filled('language')) {
            $fields[] = 'language = ?';
            $values[] = $request->input('language');
        }
        if(count($fields) > 0) {
            $fields[] = 'updated_at = CURRENT_TIMESTAMP';
        }

        // Se añade el id al final
        $values[] = $id;

        $query = "UPDATE student SET ".implode(', ', $fields) . " WHERE id = ?";

        try {
            $database = DB::update($query, $values);
    
            if($database) {
                return ResponseHelper::successResponse();
            } else {
                error_log('Database error: ' . json_encode($database));
                return ResponseHelper::errorResponse();
            }
        } catch (\Exception $e) {
            error_log('Database error: '.json_encode($e));
            return ResponseHelper::serverErrorResponse();
        }
    }

}
