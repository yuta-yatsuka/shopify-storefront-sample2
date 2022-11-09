@extends('layouts.app')

@section('content')
    @if(count($checkout->lineItems) > 0)
        <div>
            @foreach($checkout->lineItems as $item)
                <div style="margin-bottom: 10px; background-color: white;padding: 10px;">
                    {{ $item->title }}　
                    @if($item->variant->title !== 'Default Title')
                        {{ $item->variant->title }}
                    @endif
                    　{{ (int)$item->variant->price->amount }}円　{{ $item->quantity }}個
                    <a href="{{ route('cart.remove', ['item_id' => $item->id]) }}">削除</a>
                </div>
            @endforeach
        </div>
        <br>
        <a href="{{ route('cart.checkout') }}">購入手続きへ</a>
    @else
        <div>
            カートに商品がありません
        </div>
    @endif
@endsection
