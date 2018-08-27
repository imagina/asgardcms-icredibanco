@extends('email.plantilla')
@section('content')
    <div id="contend-mail" class="p-3">
        @php
            $user=$data['content']['user'];
            $orderID=$data['content']['orderID'];
            $msj=$data['content']['msj'];
            $total=$data['content']['total'];
        @endphp
        <h3 class="text-center text-uppercase">
            {{trans('icommerce::common.emailMsg.order')}} # {{$orderID}}
            
        </h3>

        <br>
        
        <p class="px-3">
            <strong>Mr/Mrs: </strong>{{$user}} <br>
            <strong>Order: </strong>#{{$orderID}} 
            <br><br>

            <strong>Msj:</strong>
            {{$msj}}<br>

        </p>
        
    </div>

@endsection