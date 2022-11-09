<?php

namespace App\Http\Controllers;

use App\Service\ShopifyStorefrontApi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class TopController extends Controller
{
    /**
     * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
     */
    function index() {
        $shopify = new ShopifyStorefrontApi();
        $products = $shopify->getProducts();


        return view('top/index', [
            "products" => $products
        ]);
    }

    public function test(Request $request,ShopifyStorefrontApi $shopify)
    {
//        $itemId = 'gid://shopify/ProductVariant/42262361014443';
//        $user = Auth::user();
//        $checkout = $shopify->createCheckout();
//        dd($shopify->getCheckout(null));
//        $shopify->checkoutCustomerAssociate($checkout->id, $user->access_token);
//        $shopify->addItemToCheckout($checkout->id, $itemId);
//        $shopify->getCheckout($checkout->id);
        Session::put('checkout_id', null);
    }

    public function test2(Request $request, ShopifyStorefrontApi $shopify)
    {
        $user = Auth::user();
        $customer = $shopify->getCustomer($user->access_token);
        $cartId = $shopify->createCart($user->access_token, (array)$customer->defaultAddress);
        $shopify->addItemToCart($cartId, 'gid://shopify/ProductVariant/42262361014443');

    }

}
