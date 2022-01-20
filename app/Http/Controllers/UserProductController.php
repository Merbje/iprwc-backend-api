<?php

namespace App\Http\Controllers;

use App\Models\UserProduct;
use Illuminate\Http\Request;

class UserProductController extends Controller
{
    public function index(Request $request)
    {
        return UserProduct::where('user_id', $request->user()->id)->get();
    }

    public function store(Request $request)
    {
        if ($this->getUserProductId($request) > 0) {
            if ($request->amount <= 0) {
                return $this->destroy($request, $this->getUserProductId($request));
            }
            return UserProduct::where('user_id', $request->user()->id)
                ->where('product_id', $request->product_id)
                ->first()
                ->update($request->all());
        }

        if ($request->amount <= 0) {
            return response([
                'message' => 'Amount needs to be bigger than zero.'
            ], 400);
        }

        return UserProduct::create([
            'user_id' => $request->user()->id,
            'product_id' => $request->product_id,
            'amount' => $request->amount,
        ]);

    }

    public function destroy(Request $request, $id)
    {
        if($request->user()->id != UserProduct::where('user_id', $request->user()->id)
                ->where('id', $id)
                ->first()->user_id) {
            return response([
                'message' => 'Can not delete items from the shopping cart of other users.'
            ], 401);
        }

        UserProduct::destroy($id);

        return response([
            'message' => 'Product deleted from basket'
        ], 200);
    }

    public function getUserProductId(Request $request)
    {
        if (UserProduct::where('user_id', $request->user()->id)
            ->where('product_id', $request->product_id)->
            exists()) {
            return UserProduct::where('user_id', $request->user()->id)
                ->where('product_id', $request->product_id)
                ->first()->id;
        } else {
            return 0;
        }
    }
}
