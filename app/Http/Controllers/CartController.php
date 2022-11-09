<?php

namespace App\Http\Controllers;

use App\Service\ShopifyMultipass;
use App\Service\ShopifyStorefrontApi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CartController extends Controller
{
    public function index(Request $request, ShopifyStorefrontApi $shopify){
        $checkoutId = Session::get('checkout_id', null);

        if(!$checkout = $shopify->getCheckout($checkoutId)){
            $checkout = $shopify->createCheckout();
            Session::put('checkout_id', $checkout->id);
        }

        return view('cart/index', [
            'checkout' => $checkout
        ]);
    }

    public function addItem(Request $request, ShopifyStorefrontApi $shopify)
    {
        $checkoutId = Session::get('checkout_id', null);
        $variantId = $request->get('variant_id');
        $productTitle = $request->get('product_title');

        if(!$shopify->getCheckout($checkoutId)){
            $checkout = $shopify->createCheckout();
            Session::put('checkout_id', $checkout->id);
            $checkoutId = $checkout->id;
        }


        $shopify->addItemToCheckout($checkoutId, $variantId);


        return view('cart/add-item',[
            'productTitle' => $productTitle
        ]);
    }

    public function removeItem(Request $request, ShopifyStorefrontApi $shopify)
    {
        $checkoutId = Session::get('checkout_id');
        $itemId = $request->get('item_id');

        $shopify->removeItemFromCheckout($checkoutId, $itemId);

        return redirect('/cart');
    }

    public function checkout(ShopifyStorefrontApi $shopify, ShopifyMultipass $shopifyMultipass)
    {
        $checkoutId = Session::get('checkout_id');
        $user = Auth::user();

        $checkout = $shopify->getCheckout($checkoutId);
        $shopify->checkoutCustomerAssociate($checkout->id, $user->access_token);

        $multipassData = [
            'email' => $user->email,
            'return_to' => $checkout->webUrl,
            'created_at' => date('c')
        ];

        $multipassToken = $shopifyMultipass->generateToken($multipassData);
        return redirect('https://shop-dev33-visualive.myshopify.com/account/login/multipass/' . $multipassToken);

    }
}
