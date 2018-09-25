<?php

use Illuminate\Routing\Router;

    $router->group(['prefix'=>'icredibanco'],function (Router $router){
        $locale = LaravelLocalization::setLocale() ?: App::getLocale();

        $router->post('/', [
            'as' => 'icredibanco',
            'uses' => 'PublicController@index',
        ]);

        $router->match(['get', 'post'],'/response', [
            'as' => 'icredibanco.response',
            'uses' => 'PublicController@response',
        ]);

        $router->get('/confirmation', [
            'as' => 'icredibanco.confirmation',
            'uses' => 'PublicController@confirmation',
        ]);

        $router->get('voucher/transaction/{tr}/{or}', [
            'as' => 'icredibanco.voucher.showvoucher',
            'uses' => 'PublicController@voucherShow',
        ]);

    });