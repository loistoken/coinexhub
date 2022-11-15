<div class="header-bar">
    <div class="table-title">
        <h3>{{__('Coin Payment Details')}}</h3>
    </div>
</div>
<div class="profile-info-form">
    <form action="{{route('adminSavePaymentSettings')}}" method="post"
          enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-lg-6 col-12 mt-20">
                <div class="form-group">
                    <label for="#">{{__('COIN PAYMENT PUBLIC KEY')}}</label>
                    <input class="form-control" type="text" name="COIN_PAYMENT_PUBLIC_KEY"
                           autocomplete="off" placeholder=""
                           value="{{settings('COIN_PAYMENT_PUBLIC_KEY')}}">
                </div>
            </div>
            <div class="col-lg-6 col-12 mt-20">
                <div class="form-group">
                    <label for="#">{{__('COIN PAYMENT PRIVATE KEY')}}</label>
                    <input class="form-control" type="text" name="COIN_PAYMENT_PRIVATE_KEY"
                           autocomplete="off" placeholder=""
                           value="{{settings('COIN_PAYMENT_PRIVATE_KEY')}}">
                </div>
            </div>
            <div class="col-lg-6 col-12 mt-20">
                <div class="form-group">
                    <label for="#">{{__('COIN PAYMENT IPN MERCHANT ID')}}</label>
                    <input class="form-control" type="text" name="ipn_merchant_id"
                           autocomplete="off" placeholder=""
                           value="{{isset(settings()['ipn_merchant_id']) ? settings('ipn_merchant_id') : ''}}">
                </div>
            </div>
            <div class="col-lg-6 col-12 mt-20">
                <div class="form-group">
                    <label for="#">{{__('COIN PAYMENT IPN SECRET')}}</label>
                    <input class="form-control" type="text" name="ipn_secret"
                           autocomplete="off" placeholder=""
                           value="{{isset(settings()['ipn_secret']) ? settings('ipn_secret') : ''}}">
                </div>
            </div>
            <div class="col-lg-6 col-12 mt-20">
                <div class="form-group">
                    <label for="#">{{__('Withdrawal email verification enable / disable')}}</label>
                    <div class="cp-select-area">
                        <select name="coin_payment_withdrawal_email" class="form-control">
                            <option @if(isset($settings['coin_payment_withdrawal_email']) && $settings['coin_payment_withdrawal_email'] == STATUS_ACTIVE) selected @endif value="{{STATUS_ACTIVE}}">{{__("Yes")}}</option>
                            <option @if(isset($settings['coin_payment_withdrawal_email']) && $settings['coin_payment_withdrawal_email'] == STATUS_PENDING) selected @endif value="{{STATUS_PENDING}}">{{__("No")}}</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-2 col-12 mt-20">
                <button type="submit" class="button-primary theme-btn">{{__('Update')}}</button>
            </div>
        </div>
    </form>
</div>

<div class="user-management pt-4">
    <div class="row">
        <div class="col-12">
            <div class="header-bar">
                <div class="table-title">
                     <h3>{{ __('CoinPayment Network Records')}}</h3>
                </div>
                <div class="right d-flex align-items-center">
                    <div class="add-btn-new mb-2 mr-1">
                        <button id="sync_fees" class="float-right btn btn-primary">{{ __("Sync form CoinPayment") }}</button>
                    </div>
                </div>
            </div>
            <div class="table-area">
                <div class="table-responsive">
                    <table id="withdrawTable" class=" table table-borderless custom-table display text-lg-center" width="100%">
                        <thead>
                        <tr>
                            <th class="all">{{__('Coin type')}}</th>
                            <th class="desktop">{{__('BTC rate')}}</th>
                            <th class="desktop">{{__('Tx rate')}}</th>
                            <th class="desktop">{{__('Is fiat')}}</th>
                            <th class="desktop">{{__('status')}}</th>
                            <th class="desktop">{{__('Last Update')}}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @if(isset($coins))
                        @foreach($coins as $coin)
                            <tr>
                                <td> {{$coin->name}} </td>
                                <td> {{find_coin_type($coin->coin_type)}} </td>
                                <td> {{api_settings($coin->network)}} </td>
                                <td> {{number_format($coin->coin_price,2).' USD/ '.find_coin_type($coin->coin_type)}} </td>
                                <td>
                                    <div>
                                        <label class="switch">
                                            <input type="checkbox" onclick="return processForm('{{$coin->id}}')"
                                                   id="notification" name="security" @if($coin->status == STATUS_ACTIVE) checked @endif>
                                            <span class="slider" for="status"></span>
                                        </label>
                                    </div>
                                </td>
                                <td> {{$coin->updated_at}}</td>
                                <td>
                                    <ul class="d-flex activity-menu">
                                        <li class="viewuser">
                                            <a href="{{route('adminCoinEdit', encrypt($coin->id))}}" title="{{__("Update")}}" class="btn btn-primary btn-sm">
                                                <i class="fa fa-pencil"></i>
                                            </a>
                                        </li>
                                        <li class="viewuser">
                                            <a href="{{route('adminCoinSettings', encrypt($coin->id))}}" title="{{__("Settings")}}" class="btn btn-warning btn-sm">
                                                <i class="fa fa-cog"></i>
                                            </a>
                                        </li>
                                        <li class="viewuser">
                                            <a href="#delete1WV4d6uF6Ytu8v1Pl_{{($coin->id)}}" data-toggle="modal" title="{{__("Delete")}}" class="btn btn-danger btn-sm">
                                                <i class="fa fa-trash"></i>
                                            </a>
                                            <div id="delete1WV4d6uF6Ytu8v1Pl_{{($coin->id)}}" class="modal fade delete" role="dialog">
                                                <div class="modal-dialog modal-sm">
                                                    <div class="modal-content">
                                                        <div class="modal-header"><h6 class="modal-title">{{__('Delete')}}</h6><button type="button" class="close" data-dismiss="modal">&times;</button></div>
                                                        <div class="modal-body"><p>{{ __('Do you want to delete ?')}}</p></div>
                                                        <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">{{__("Close")}}</button>
                                                            <a class="btn btn-danger"href="{{route('adminCoinDelete', encrypt($coin->id))}}">{{__('Confirm')}} </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </td>
                            </tr>
                        @endforeach
                        @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
