@extends('layouts.app')

@section('content')
    <div style="padding: 10px; background-color: white; margin-bottom: 10px;">{{ $productTitle }}をカートへ追加しました</div>
    <a href="{{ route('top') }}">トップページへ</a>　<a href="{{ route('cart') }}" >カートへ</a>
@endsection
