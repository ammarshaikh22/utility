@extends($activeTemplate . 'layouts.master')
@section('content')
    <div class="row dashboard-widget-wrapper justify-content-center">
        <div class="col-md-12">
            <form action="{{ route('ticket.store') }}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="form-group col-md-6">
                        <label class="form--label">@lang('Subject')</label>
                        <input type="text" name="subject" value="{{ old('subject') }}" class="form--control" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label class="form--label">@lang('Priority')</label>
                        <select name="priority" class="form-control form--control select2 on-change-submit"
                            data-minimum-results-for-search="-1" required>
                            <option value="3">@lang('High')</option>
                            <option value="2">@lang('Medium')</option>
                            <option value="1">@lang('Low')</option>
                        </select>
                    </div>
                    <div class="col-12 form-group">
                        <label class="form--label">@lang('Message')</label>
                        <textarea name="message" id="inputMessage" rows="6" class="form--control" required>{{ old('message') }}</textarea>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-9">
                        <button type="button" class="btn btn--dark btn--sm addAttachment"> <i class="fas fa-plus"></i>
                            @lang('Add Attachment') </button>
                        <p class="my-2"><span class="text--info">@lang('Max 5 files can be uploaded | Maximum upload size is ' . convertToReadableSize(ini_get('upload_max_filesize')) . ' | Allowed File Extensions: .jpg, .jpeg, .png, .pdf, .doc, .docx')</span></p>
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn--base btn--sm w-100" type="submit">
                            <i class="las la-paper-plane"></i> @lang('Submit')
                        </button>
                    </div>
                </div>
                <div class="row fileUploadsContainer"></div>
            </form>
        </div>
    </div>
@endsection

@push('style')
    <style>
        .input-group-text:focus {
            box-shadow: none !important;
        }

        .form-control:focus {
            box-shadow: none !important;
            border: 1px solid hsl(var(--base));
        }
    </style>
@endpush

@push('script')
    <script>
        (function($) {
            "use strict";

            $('.select2').select2();

            var fileAdded = 0;
            $('.addAttachment').on('click', function() {
                fileAdded++;
                if (fileAdded == 5) {
                    $(this).attr('disabled', true)
                }
                $(".fileUploadsContainer").append(`
                    <div class="col-lg-4 col-md-12 removeFileInput">
                        <div class="form-group">
                            <div class="input-group">
                                <input type="file" name="attachments[]" class="form-control" accept=".jpeg,.jpg,.png,.pdf,.doc,.docx" required>
                                <button type="button" class="input-group-text removeFile bg--danger text-white border--danger"><i class="fas fa-times"></i></button>
                            </div>
                        </div>
                    </div>
                `)
            });
            $(document).on('click', '.removeFile', function() {
                $('.addAttachment').removeAttr('disabled', true)
                fileAdded--;
                $(this).closest('.removeFileInput').remove();
            });
        })(jQuery);
    </script>
@endpush

@push('style-lib')
    <link rel="stylesheet" href="{{ asset('assets/global/css/select2.min.css') }}">
@endpush

@push('script-lib')
    <script src="{{ asset('assets/global/js/select2.min.js') }}"></script>
@endpush
