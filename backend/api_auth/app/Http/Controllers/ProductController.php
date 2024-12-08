<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class ProductController extends Controller
{
    // metodo que llama el insert en la base de datos 
    public function insertProduct(Request $request)
    {
        // Validación
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
            'url_imagen' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);
    
        try {
            // Subir imagen a Cloudinary
            $uploadedFileUrl = Cloudinary::upload($request->file('url_imagen')->getRealPath())->getSecurePath();
    
            // Llamar al procedimiento almacenado con parámetros
            DB::statement('CALL InsertProducts(?, ?, ?, ?, ?)', [
                $validatedData['name'],
                $validatedData['description'],
                $validatedData['price'],
                $validatedData['stock'],
                $uploadedFileUrl,
            ]);
    
            // Respuesta en caso de éxito
            return response()->json([
                'message' => 'Producto insertado correctamente',
                'data' => [
                    'name' => $validatedData['name'],
                    'description' => $validatedData['description'],
                    'price' => $validatedData['price'],
                    'stock' => $validatedData['stock'],
                    'url_imagen' => $uploadedFileUrl,
                ],
                'status' => 200,
            ]);
        } catch (\Exception $e) {
            // Manejo de errores
            return response()->json([
                'message' => 'Error al insertar producto',
                'error' => $e->getMessage(),
                'status' => 500,
            ]);
        }
    }
    

    //metodo para llamar el getProduct

    public function getProduct(){
        try{
            $products = DB::select('CALL getProducts()');

            if (empty($products)){
                return response()->json([
                    'message' => 'No hay productos disponibles',
                    'status' => 404
                ]);
            }
            return response()->json([
                'productos' => $products,
                'status' => 200
            ]);
        }catch(\Exception $e){
            return response()->json([
                'message' => 'failed to fetch products',
                'Error' => $e->getMessage(),
                'status' => 500
            ]);
        }
    }
    // metodo para al producto por id 
    public function getProductById($id){
        try{
            //llamar el procedimiento 
            $product = DB:: select('CALL getProductById(?)', [$id]);
            if(empty($product)){
                return response()->json([
                    'message' => 'producto no encontrado',
                    'status' => 404
                ]);
            }

            return response()->json([
                'producto' => $product[0],
                'status' => 200
            ]);

        }catch(\Exception $e){
            return response()->json([
                'message' => 'error al obtener el producto',
                'error' => $e->getMessage(),
                'status' => 500
            ]);
        }
    }
}
