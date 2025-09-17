@extends('admin.layouts.app')
@section('panel')
    <div class="row">
        <div class="col-md-12">
            <div class="card overflow-hidden">
                <div class="card-body p-0">
                    <div class="table-responsive table-responsive--sm">
                        <table class="table align-items-center table--light">
                            <thead>
                                <tr>
                                    <th>@lang('Short Code')</th>
                                    <th>@lang('Description')</th>
                                </tr>
                            </thead>
                            <tbody class="list">
                                @foreach (gs('contract_shortcodes') as $shortcode => $key)
                                    <tr>
                                        {{-- blade-formatter-disable --}}
                                    <td><span class="short-codes">@php echo "{{". $shortcode ."}}"  @endphp</span></td>
                                    {{-- blade-formatter-enable --}}
                                        <td>{{ __($key) }}</td>
                                    </tr>
                                @endforeach
                                @foreach (gs('global_shortcodes') as $shortCode => $codeDetails)
                                    <tr>
                                        {{-- blade-formatter-disable --}}
                                    <td><span class="short-codes">@{{@php echo $shortCode @endphp}}</span></td>
                                    {{-- blade-formatter-enable --}}
                                        <td>{{ __($codeDetails) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card mt-4">
                <div class="card-header bg--primary">
                    <h5 class="card-title text-white">@lang('Contract Template')</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.manage.contract.template.store') }}" method="post" class="disableSubmission">
                        @csrf
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>@lang('Template')</label>
                                    <textarea name="contract_template" rows="10" class="form-control nicEdit" placeholder="@lang('Your message using short-codes')">{{ gs('contract_template') }}</textarea>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn--primary w-100 h-45">@lang('Submit')</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
