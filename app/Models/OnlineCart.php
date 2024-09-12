<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class OnlineCart extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_detail_id',
        'quantity',
        'price'
    ];

    protected $table = 'online_cart';

    public $timestamp = false;

    public function onlCartByUserId(int $idUser)
    {
        $query = DB::table('online_cart as onl_cart')
            ->select(
                'onl_cart.id',
                'onl_cart.quantity',
                'onl_cart.price',
                'onl_cart.product_detail_id',

                'pro.name as product_name',
                'pro.thumbnail as product_thumbnail',
                'pro_detail.price as product_price',
                'pro_detail.sale as product_sale',

                'size.name as size_name',

                'user.id as user_id',
                'user.name as user_name'
            )
            ->join('product_details as pro_detail', 'onl_cart.product_detail_id', '=', 'pro_detail.id')
            ->join('products as pro', 'pro_detail.product_id', '=', 'pro.id')
            ->join('sizes as size', 'pro_detail.size_id', '=', 'size.id')
            ->join('users as user', 'onl_cart.user_id', '=', 'user.id')
            ->orderBy('onl_cart.id')
            ->where('onl_cart.user_id', $idUser);

        return $query;
    }
}
