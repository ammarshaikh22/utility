@extends($activeTemplate . 'layouts.' . $layout)
@php
    $label = 'form--label';
    $formControl = '';
@endphp

@section('content')
    @if ($layout == 'frontend')
        <section class="account">
            <div class="account-inner py-120 bg-pattern3">
                <div class="container ">
    @endif
                    <div class="row @if ($layout == 'master') dashboard-widget-wrapper @endif justify-content-center">
                        <div class="col-md-12">
                        @if ($layout == 'frontend')
                            <div class="account-form">
                        @endif
                                <form method="post" action="{{ route('ticket.reply', $myTicket->id) }}" enctype="multipart/form-data">
                                    @csrf
                                @if ($layout == 'frontend')
                                    <div class="account-form__header">
                                @else
                                        <div class="d-flex justify-content-between align-items-center mb-4">
                                @endif
                                            <h6 class="@if ($layout == 'master') m-0 @endif">
                                                @php echo $myTicket->statusBadge; @endphp [@lang('Ticket')#{{ $myTicket->ticket }}] {{ $myTicket->subject }}
                                            </h6>
                                        @if ($myTicket->status != Status::TICKET_CLOSE && $myTicket->user)
                                            <button class="btn btn--danger close-button action--btn confirmationBtn" type="button">
                                                <i class="la la-times"></i>
                                            </button>
                                        @endif
                                        </div>
                                    @if ($layout == 'frontend')
                                        <div class="account-form__body">
                                    @endif
                                            <div class="row justify-content-between">
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <textarea name="message" class="form--control" rows="4">{{ old('message') }}</textarea>
                                                    </div>
                                                </div>
                                                <div class="col-md-9">
                                                    <button type="button" class="btn btn--dark btn--sm addAttachment">
                                                        <i class="fas fa-plus"></i> @lang('Add Attachment')
                                                    </button>
                                                    <p class="my-2"><span class="text--info">@lang('Max 5 files can be uploaded | Maximum upload size is ' . convertToReadableSize(ini_get('upload_max_filesize')) . ' | Allowed File Extensions: .jpg, .jpeg, .png, .pdf, .doc, .docx')</span></p>
                                                </div>
                                                <div class="col-md-3">
                                                    <button class="btn btn--base btn--sm w-100" type="submit">
                                                        <i class="la la-fw la-lg la-reply"></i> @lang('Reply')
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="row fileUploadsContainer"></div>
                                        @if (!auth()->check())
                                            <x-captcha :label="$label" :formControl="$formControl" />
                                        @endif
                                    @if ($layout == 'frontend')
                                        </div>
                                    @endif
                                </form>
                        @if ($layout == 'frontend')
                            </div>
                        @endif
                        @if ($layout == 'frontend')
                            <div class="account-form mt-4">
                                <div class="account-form__body">
                        @endif
                            @foreach ($messages as $message)
                                @if ($message->admin_id == 0)
                                    <div class="support-ticket">
                                        <div class="flex-align gap-3 mb-2">
                                            <h6 class="support-ticket-name">{{ $message->ticket->name }}</h6>
                                            <p class="support-ticket-date"> @lang('Posted on')
                                                {{ $message->created_at->format('l, dS F Y @ H:i') }}</p>
                                        </div>
                                        <p class="support-ticket-message">{{ $message->message }}</p>
                                    @if ($message->attachments->count() > 0)
                                        <div class="support-ticket-file mt-2">
                                        @foreach ($message->attachments as $k => $image)
                                            <a href="{{ route('ticket.download', encrypt($image->id)) }}" class="me-3"> <span class="icon"><i
                                                        class="la la-file-download"></i></span> @lang('Attachment')
                                                {{ ++$k }}
                                            </a>
                                        @endforeach
                                        </div>
                                    @endif
                                    </div>
                                @else
                                    <div class="support-ticket reply">
                                        <div class="flex-align gap-3 mb-2">
                                            <h6 class="support-ticket-name">{{ $message->admin->name }} <span class="staff">@lang('Staff')</span></h6>
                                            <p class="support-ticket-date"> @lang('Posted on')
                                                {{ $message->created_at->format('l, dS F Y @ H:i') }}
                                            </p>
                                        </div>
                                        <p class="support-ticket-message">{{ $message->message }}</p>
                                        @if ($message->attachments->count() > 0)
                                        <div class="support-ticket-file mt-2">
                                        @foreach ($message->attachments as $k => $image)
                                            <a href="{{ route('ticket.download', encrypt($image->id)) }}" class="me-3"> <span class="icon"><i
                                                        class="la la-file-download"></i></span> @lang('Attachment')
                                                {{ ++$k }}
                                            </a>
                                        @endforeach
                                    </div>
                                        @endif
                                </div>
                                @endif
                            @endforeach
                    @if ($layout == 'frontend')
                            </div>
                        </div>
                    @endif
                    </div>
                </div>
    @if ($layout == 'frontend')
            </div>
        </div>
    </section>
    @endif

    <div class="modal fade custom--modal" id="confirmationModal">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Confirmation Alert!')</h5>
                    <button class="close-btn" type="button" data-bs-dismiss="modal">
                        <i class="las fa-times"></i>
                    </button>
                </div>
                <form action="{{ route('ticket.close', $myTicket->id) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="modal-form__header">
                            <h6 class="modal-form__title">@lang('Are you sure to close this ticket?')</h6>
                        </div>
                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn--dark btn--sm" data-bs-dismiss="modal">@lang('No')</button>
                            <button class="btn btn--base btn--sm">@lang('Yes')</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('style')
    <style>
        .input-group-text:focus {
            box-shadow: none !important;
        }

        .form-control:focus{
            box-shadow: none !important;
            border: 1px solid hsl(var(--base));
        }
    </style>
@endpush

@push('script')
    <script>
        (function($) {
            "use strict";
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

            $('.confirmationBtn').on('click', function() {
                let modal = $('#confirmationModal');
                modal.modal('show');
            });
        })(jQuery);
    </script>
@endpush
