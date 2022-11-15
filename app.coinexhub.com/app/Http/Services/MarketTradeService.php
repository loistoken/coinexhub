<?php
namespace App\Http\Services;

use App\Http\Repositories\CoinPairRepository;
use App\Model\CoinPair;

class MarketTradeService
{
    private $settingTolerance;
    function __construct()
    {
        $this->settingTolerance = settings('trading_bot_price_tolerance') ?? 10;
    }
    // market buy order
    public function makeMarketOrder($request,$coinPair = null)
    {
        try {
            if(allsetting('enable_bot_trade') == STATUS_ACTIVE) {
                $request->merge(['is_market' => 0]);
                if (isset($request->pair_id) && !is_null($coinPair)) {
                    if ($coinPair) {
                        if ($request->bot_order_type == 'buy') {
                            $this->startBotBuyOrderProcess($request, $coinPair);
                        } elseif ($request->bot_order_type == 'sell') {
                            $this->startBotSellOrderProcess($request, $coinPair);
                        } else {
                            storeException('marketBuyOrder', 'order type buy sell');
                            $this->startBotBuyOrderProcess($request, $coinPair);
                            $this->startBotSellOrderProcess($request, $coinPair);
                        }
                    } else {
                        storeException('marketBuyOrder', 'this coin pair not found or not active now');
                    }
                } else {
                    storeException('marketBuyOrder', 'all pair');
                    $coinPairs = CoinPair::where(['status' => STATUS_ACTIVE])->get();
                    if (isset($coinPairs[0])) {
                        if ($request->bot_order_type == 'buy') {
                            foreach ($coinPairs as $pair) {
                                $this->startBotBuyOrderProcess($request, $pair);
                            }
                        } elseif ($request->bot_order_type == 'sell') {
                            foreach ($coinPairs as $pair) {
                                $this->startBotSellOrderProcess($request, $pair);
                            }
                        } else {
                            foreach ($coinPairs as $pair) {
                                $this->startBotBuyOrderProcess($request, $pair);
                                $this->startBotSellOrderProcess($request, $pair);
                            }
                        }
                    } else {
                        storeException('marketBuyOrder', 'no active coin pair found');
                    }
                }
                return true;
            }
        } catch (\Exception $e) {
            storeException('marketBuyOrder', $e->getMessage());
        }
    }

    // start bot buy order
    public function startBotBuyOrderProcess($request,$pair)
    {
        $request->merge(['bot_order_type' => 'buy']);
        $requestData = $this->makeOrderPlaceData($request, $pair->parent_coin_id, $pair->child_coin_id);

        $request->merge([
            'price' => $requestData['requestPrice'],
            'amount' => $requestData['amount'],
            'base_coin_id' => $pair->parent_coin_id,
            'trade_coin_id' => $pair->child_coin_id,
            'is_bot' => 1
        ]);
        $this->placeBuyOrderByBot($request);
    }
    // start bot sell order
    public function startBotSellOrderProcess($request,$pair)
    {
        $request->merge(['bot_order_type' => 'sell']);
        $requestData = $this->makeOrderPlaceData($request, $pair->parent_coin_id, $pair->child_coin_id);

        $request->merge([
            'price' => $requestData['requestPrice'],
            'amount' => $requestData['amount'],
            'base_coin_id' => $pair->parent_coin_id,
            'trade_coin_id' => $pair->child_coin_id,
            'is_bot' => 1
        ]);
        $this->placeSellOrderByBot($request);
    }

    // make place order data
    public function makeOrderPlaceData($request,$baseCoinId,$tradeCoinId)
    {
        $data = [];
        try {
            $price = $this->getBuySellLatestPrice($baseCoinId,$tradeCoinId);
            if ($request->bot_order_type == 'buy') {
                $lastPrice = $price['buy_price'];
                $data['amount'] = $price['buy_amount'];
            } else {
                $lastPrice = $price['sell_price'];
                $data['amount'] = $price['sell_amount'];
            }

            $settingTolerance = intval($this->settingTolerance);
            $tolerancePrice = bcdiv(bcmul($lastPrice, $settingTolerance), "100");
            $highTolerance = bcadd($lastPrice, $tolerancePrice);
            $lowTolerance = bcsub($lastPrice, $tolerancePrice);
            $div = pow(10, 8);
            $data['requestPrice'] = custom_number_format(rand($lowTolerance * $div, $highTolerance * $div) / $div);

            return $data;
        } catch (\Exception $e) {
            storeException('makeOrderPlaceData', $e->getMessage());
            return $data;
        }
    }

    // unset some request
    public function unsetSomeRequest($request)
    {
        if (isset($request->maker_fees)) {
            unset($request['maker_fees']);
        }
        if (isset($request->taker_fees)) {
            unset($request['taker_fees']);
        }
        if (isset($request->btc_rate)) {
            unset($request['btc_rate']);
        }
        if (isset($request->dashboard_type)) {
            unset($request['dashboard_type']);
        }
        if (isset($request->order_type)) {
            unset($request['order_type']);
        }
    }
    // place buy order
    public function placeBuyOrderByBot($request)
    {
        $this->unsetSomeRequest($request);
//        storeException('placeBuyOrderByBot request', json_encode($request->all()));
        if($request->price > 0 && $request->amount > 0) {
            $response = app(BuyOrderService::class)->botOrderCreate($request);
        }
//        storeException('placeBuyOrderByBot response', json_encode($response));
    }

    // place sell order
    public function placeSellOrderByBot($request)
    {
        $this->unsetSomeRequest($request);
//        storeException('placeSellOrderByBot request', json_encode($request->all()));
        if($request->price > 0 && $request->amount > 0) {
            $response = app(SellOrderService::class)->botOrderCreate($request);
        }
//        storeException('placeSellOrderByBot response', json_encode($response));
    }

    // get buy sell last price
    public function getBuySellLatestPrice($baseCoinId, $tradeCoinId)
    {
        $data['sell_price'] = 0;
        $data['buy_price'] = 0;
        try {
            $dashboardService = new DashboardService();
            $coinPairRepo = new CoinPairRepository(CoinPair::class);
            $pairData = $coinPairRepo->getCoinPairsDataBot($baseCoinId, $tradeCoinId);
            $price = $dashboardService->getTotalVolumeBot($baseCoinId, $tradeCoinId);
            $data['sell_price'] = $price['sell_price'] > 0 ? $price['sell_price'] : $pairData->last_price;
            $data['buy_price'] = $price['buy_price'] > 0 ? $price['buy_price'] : $pairData->last_price;
            $amount = getBuySellLastAmount($data,$baseCoinId,$tradeCoinId);
            $data['buy_amount'] = $amount['buy_amount'];
            $data['sell_amount'] = $amount['sell_amount'];
        } catch (\Exception $e) {
            storeException('getBuySellLatestPrice', $e->getMessage());
        }

        return $data;
    }
}
