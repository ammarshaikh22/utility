@extends($activeTemplate.'layouts.master')
@section('content')
    <div class="container">
        <form action="{{route('user.kyc.submit')}}" method="post" enctype="multipart/form-data">
            @csrf

            <x-viser-form identifier="act" identifierValue="kyc" />

            <div class="form-group">
                <button type="submit" class="btn btn--base w-100">@lang('Submit')</button>
            </div>
        </form>
    </div>
@endsection
