<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    // metodo para registrar
    public function register(Request $request)
    {
        $registerdData = $request->validate([
            'email' => 'email|required|unique:users',
            'password' => 'required|confirmed'
        ]);

        //generar un nombre aleatorio

        $registerdData['name']=Str::random(6);
        $registerdData['password'] = Hash::make($request->password);
        $registerdData['loyalty_status'] = 'nuevo';
        $registerdData['address'] = null;
        $registerdData['phone'] = null;
        $registerdData['last_purchase_date'] = null;
        $registerdData['total_orders'] = 0;
        // creacion del usuario
        $user = User::create($registerdData);
        $accessToken = $user->createToken('authToken')->accessToken;
    
        return response(['user' => $user, 'access_token' => $accessToken], 201);
        
    }

    //metodo para iniciar sesion
    public function login(Request $request)
    {
        $loginData = $request->validate([
            'email' => 'required|string|email|',
            'password' => 'required|string',
        ]);
    
        if (!auth()->attempt($loginData)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }
    
        $user = auth()->user();
        // guardar el user_id en la sesion
        $request->session()->put('user_id',$user->id);
        $accessToken = $user->createToken('authToken')->accessToken;
    
        return response()->json([
            'user' => $user,
            'access_token' => $accessToken,
        ]);
    }

    //metodo para subir la imagen a la nube 

    public function uploadProfileImagen(Request $request){
        try {
            // Validar que se recibe un archivo de imagen
            $validator = Validator::make($request->all(), [
                'profile_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:4000',
            ]);
    
            // Si la validación falla, devuelve los errores
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
    
            // Verificar si se ha recibido el archivo de imagen
            if (!$request->hasFile('profile_image')) {
                return response()->json(['error' => 'No image file provided'], 400);
            }
    
            // Subir la imagen a Cloudinary
            $uploadedFileUrl = Cloudinary::upload($request->file('profile_image')->getRealPath())->getSecurePath();
    
            // Obtener el usuario autenticado
            $user = auth()->user();
            if (!$user) {
                return response()->json(['error' => 'User not authenticated'], 401);
            }
    
            // Guardar la URL en el campo `profile_imagen_url`
            $user->profile_imagen_url = $uploadedFileUrl;
            $user->save();
    
            return response()->json([
                'message' => 'Profile image uploaded successfully',
                'profile_imagen_url' => $uploadedFileUrl,
            ], 200);
    
        } catch (\Exception $e) {
            // Capturar y devolver errores de la carga
            return response()->json([
                'error' => 'An error occurred while uploading the image',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    //metodo oara obtener datos 

    public function store(){

        //obtener el usuario
        $user = auth()-> user();

        //retornar los datos de usuario

        return response()->json([
            'user' => [
                'name'=> $user->name,
                'profile_imagen_url' => $user->profile_imagen_url,
                'email' => $user->email,
                'address' => $user->address,
                'phone' => $user->phone,
                'loyalty_status' => $user->loyalty_status
            ]
        ], 200);
    }

    // metodo para actualizar 
    public function update(Request $request)
    {
        $user = auth()->user();
    
        // Comprobar si el usuario está autenticado
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
    
        // Validar los campos recibidos y asegurarse de que sean valores planos
        $validatedData = $request->validate([
            'name' => 'nullable|string|max:55',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:15',
        ]);
    
        // Actualizar los datos del usuario con el array plano
        $user->update(array_filter($validatedData));
    
        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user
        ], 200);
    }
    
    

    //metodo para eliminar usuario

    public function destroy(Request $request){

        $user= auth()->user();

          // Comprobar si el usuario está autenticado
          if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $user->delete();



        return response()->json([
            'message' => 'user deleted succesfully'
        ], 200);
    }



}