<div class="header-bar">
    <div class="table-title">
        <h3>{{__('Stripe Settings')}}</h3>
    </div>
</div>
<div class="profile-info-form">
    <form action="{{route('adminSaveStripeApiSettings')}}" method="post"
          enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-lg-6 col-12 mt-20">
                <div class="form-group">
                    <label for="#">{{__('Stripe Publishable key')}}</label>
                    <input type="text" name="STRIPE_KEY" class="form-control" value="{{$settings['STRIPE_KEY'] ?? ''}}">
                </div>
            </div>
            <div class="col-lg-6 col-12 mt-20">
                <div class="form-group">
                    <label for="#">{{__('Stripe Secret key')}}</label>
                    <input type="text" name="STRIPE_SECRET" class="form-control" value="{{$settings['STRIPE_SECRET'] ?? ''}}">
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
