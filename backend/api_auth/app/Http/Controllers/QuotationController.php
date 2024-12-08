<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Quotation;
use App\Models\Product;
use App\Models\User;

class QuotationController extends Controller
{
    //metodo para guardar la cotizacion del cliente
    public function createQuotation(Request $request){

        //validar datos 

        $validateData = $request->validate([
            'products' => 'required|array',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1' 
        ]);

        //obtener el usuario autenticado y su tipo de cliente
        $user = auth()->user();
        $clientType = $user->loyalty_status;

        //calcular el precio total de la cotizacion

        $totalPrice = 0;
        $productsWithPrices = [];

        foreach ($validateData['products'] as $productData) {
            $product = Product::find($productData['id']);
            $quantity = $productData['quantity'];
            $priceAtTime = $product->price * $quantity;
            $totalPrice += $priceAtTime;

            //guardar el producto con su cantidad y precio al momento 

            $productsWithPrices[]= [
                'product_id' => $product->id,
                'quantity' => $quantity,
                'price_at_time' => $product->price,
            ];
        }

        //aplicar descuento segun el tipo del cliente

        $discount = 0;

        switch ($clientType) {
            case 'permanente' : 
                $discount = 0.10;
                break;
            case 'periodico':
            $discount = 0.05;
            break;
            case 'casual':
                $discount = 0.02;
                break;
            case 'nuevo':
                $discount = 0.00;
                break;
        }

        //aplicar descuento por tipo de cliente

        $discountAmount = $totalPrice * $discount;
        $totalPrice -= $discountAmount;

        //aplicar descuento adicional si supera los $100000

        if($totalPrice> 100000) {
            $additionalDiscount = 0.02;
            $totalPrice -= $totalPrice * $additionalDiscount;
        }

        //crear la cotizacion en la base de datos 

        $quotation =  Quotation::create([
            'user_id' => $user -> id, 
            'total_price' => $totalPrice,
            'discount' => $discountAmount,
        ]);

        //vincular productos a la cotizacion 

        foreach($productsWithPrices as $productsWithPrice) {
            $quotation->products()->attach($productsWithPrice['product_id'],[
                'quantity' => $productsWithPrice['quantity'],
                'price_at_time' => $productsWithPrice['price_at_time'],
            ]);
        }

        return response()->json([
            'message' => 'cotizacion  creada con exito',
            'quotation' => $quotation,
            'total_price' => $totalPrice,
        ],201);
    }
}
