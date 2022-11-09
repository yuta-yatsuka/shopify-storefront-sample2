@extends('layouts/app')
@section('content')
    <div>
        <h1 style="font-size: 36px;">Sample Shopify Storefront</h1>
        <a href="{{ route('cart') }}">カートへ</a>
    </div>
    <br>
    <div class="products">
        @foreach($products as $product)
            <div class="product-card" style="margin-bottom: 10px; background-color: white;padding: 10px;">
                <div class="product-title">
                    {{ $product->title }}
                </div>
                <form method="post" action="{{ route('cart.add') }}">
                    @csrf
                    @if(count($product->variants) > 1)
                        <select name="variant_id">
                            @foreach($product->variants as $variant)
                                <option value="{{ $variant->id }}">{{ $variant->title }}</option>
                            @endforeach
                        </select>
                    @else
                        <input type="hidden" name="variant_id" value="{{ $product->variants[0]->id }}" />
                    @endif
                    <input type="hidden" name="product_title" value="{{ $product->title }}"/>
                    <input type="submit" value="カートに追加" style="background-color: blue;color: white; padding: 5px" />
                </form>
            </div>
        @endforeach
    </div>
@endsection
