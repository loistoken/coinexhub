<?php

namespace App\Http\Controllers;

use App\Http\Services\MarketTradeService;
use App\Jobs\MarketBotOrderJob;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class TestController extends Controller
{
    //
    public function index(Request $request)
    {
        $service = new MarketTradeService();
        $admin = User::where(['role' => USER_ROLE_ADMIN])->first();
        $request->merge(['user_id' => $admin->id]);
//        $a = $service->makeMarketOrder($request);
//        dd($a);
//        dispatch(new MarketBotOrderJob($request->all()))->onQueue('market-bot');
        Artisan::call('buy:order',['buy']);
        Artisan::call('buy:order',['sell']);
        return 'market bot started successfully';
    }
}
