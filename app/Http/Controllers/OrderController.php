<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    
    public function index(Request $request){

    }

    public function store(Request $request){
        
        $items = $request->input('orders');
        $total = 0;

        $order = Sale::create([
            'invoice_no' => '#0000001',
            'cashier_id' => 1,
            'discounts' => 0,
            'total' => 0
        ]);

        // create order item
        //  ['id', 'sale_id', 'product_id', 'quantity', 'unit_price', 'subtotal'];
        foreach($items as $item){
            SaleItem::create([
                'sale_id' => $order->id,
                'product_id' => $item['product_id'],
                'quantity' => $item['qty'],
                'unit_price' => $item['unit_price'],
                'subtotal' => $item['qty'] * $item['unit_price']
            ]);

            $total += $item['qty'] * $item['unit_price'];

        }

        Sale::find($order->id)->update([
            'total' => $total
        ]);

        $order = Sale::with('saleItems.product')->find($order->id);
        return $order;
    }
}
