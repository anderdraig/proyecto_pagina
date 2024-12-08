<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function placeOrder(Request $request)
    {
        $request->validate([
            'total_price' => 'required|numeric',
            'products' => 'required|array',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|numeric|min:1',
            'products.*.price_at_time' => 'required|numeric',
        ]);

        // Obtener el ID del usuario autenticado
        $userId = auth()->id();

        // Inicializar el ID de la orden
        $orderId = null;

        try {
            // Iniciar transacción
            DB::beginTransaction();

            // Llamar al procedimiento para crear la orden
            DB::statement('CALL InserOrder(?, ?, @orderId)', [$userId, $request->input('total_price')]);

            // Obtener el ID de la orden creada
            $orderId = DB::select('SELECT @orderId AS orderId')[0]->orderId;

            // Insertar los productos asociados a la orden
            foreach ($request->input('products') as $product) {
                DB::statement('CALL InsertOrderProducts(?, ?, ?, ?)', [
                    $orderId,
                    $product['product_id'],
                    $product['quantity'],
                    $product['price_at_time']
                ]);
            }

            // Confirmar transacción
            DB::commit();

            // Respuesta exitosa
            return response()->json([
                'message' => 'Order placed successfully',
                'order_id' => $orderId,
            ], 201);

        } catch (\Exception $e) {
            // Revertir transacción en caso de error
            DB::rollBack();

            // Log del error para depuración
            Log::error('Order placement failed', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'stack' => $e->getTraceAsString()
            ]);

            // Respuesta de error
            return response()->json([
                'message' => 'Failed to place order',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
