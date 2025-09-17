<div class="row justify-content-center">
    <div class="col-xxl-4 col-xl-6">
        <div class="form-group">
            <x-image-uploader name="thumb_image" :imagePath="getImage(
                getFilePath('propertyThumb') . '/' . @$property->thumb_image,
                getFileSize('propertyThumb'),
            )" :size="getFileSize('propertyThumb')" class="w-100" id="thumbImage"
                :required="false" />
        </div>
    </div>
    <div class="col-sm-12">
        <div class="form-group">
            <div class="d-flex justify-content-between">
                <label>@lang('Title')</label>
                <a href="javascript:void(0)" class="buildSlug">
                    <i class="las la-link"></i> @lang('Make Slug')
                </a>
            </div>
            <input type="text" class="form-control" name="title" value="{{ old('title', @$property->title) }}"
                required>
        </div>
    </div>
    <div class="col-sm-12">
        <div class="form-group">
            <div class="d-flex justify-content-between">
                <label> @lang('Slug')</label>
                <div class="slug-verification d-none"></div>
            </div>
            <input type="text" class="form-control" name="slug" value="{{ old('slug', @$property->slug) }}"
                required>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="form-group position-relative">
            <label>@lang('Location')</label>
            <select name="location_id" class="select2" required>
                <option value="">@lang('Select One')</option>
                @foreach ($localities as $location)
                    <option value="{{ $location->id }}" @selected($location->id == old('location_id', @$property->location_id))>
                        {{ __($location->name) }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="form-group">
            <label>@lang('Location Map URL')</label>
            <input type="text" class="form-control" name="map_url" value="{{ old('map_url', @$property->map_url) }}"
                required>
        </div>
    </div>
    <div class="col-md-12">
        <div class="form-group">
            <label class="form--label">
                @lang('Gallery Images') <small class="text--danger">
                    (@lang('You can add multiple images'))
                </small>
            </label>
            <div class="row">
                <div class="input-images pb-3">
                </div>
                <small class="text-muted"> @lang('Supported Files:')
                    <b>@lang('.png, .jpg, .jpeg')</b>
                    @lang('Image will be resized into') <b>{{ getFileSize('propertyGallery') }}</b>@lang('px')</b>
                </small>
            </div>
        </div>
    </div>
    <div class="col-sm-12">
        <div class="form-group">
            <label>@lang('Details')</label>
            <textarea rows="10" class="form-control nicEdit" name="details">{{ old('details', @$property->details) }}</textarea>
        </div>
    </div>
    <div class="col-sm-12">
        <div class="form-group">
            <label>@lang('Keyword')</label>
            <select name="keywords[]" multiple class="select2-auto-tokenize form-control">
                <option value="" disabled>@lang('Enter Keyword')</option>
                @foreach (@$property->keywords ?? [] as $keyword)
                    <option value="{{ $keyword }}" selected>
                        {{ __($keyword) }}
                    </option>
                @endforeach
            </select>
            <span class="text--small"><i>@lang('Optimize your property details page SEO by providing multiple keywords')</i></span>
        </div>
    </div>
</div>

@push('script-lib')
    <script src="{{ asset('assets/admin/js/image-uploader.min.js') }}"></script>
@endpush

@push('style-lib')
    <link href="{{ asset('assets/admin/css/image-uploader.min.css') }}" rel="stylesheet">
@endpush

@push('script')
    <script>
        (function($) {
            "use strict";
            $(document).ready(function() {
                @if (isset($images))
                    let preloaded = @json($images);
                @else
                    let preloaded = [];
                @endif

                $('.input-images').imageUploader({
                    preloaded: preloaded,
                    imagesInputName: 'gallery_image',
                    preloadedInputName: 'old',
                });
            });

            $('.buildSlug').on('click', function() {
                let closestForm = $(this).closest('form');
                let title = closestForm.find('[name=title]').val();
                closestForm.find('[name=slug]').val(title);
                closestForm.find('[name=slug]').trigger('input');
            });

            $('[name=slug]').on('input', function() {
                let closestForm = $(this).closest('form');
                closestForm.find('[type=submit]').addClass('disabled')
                let slug = $(this).val();
                slug = slug.toLowerCase().replace(/ /g, '-').replace(/[^\w-]+/g, '');
                $(this).val(slug)
                if (slug) {
                    $('.slug-verification').removeClass('d-none');
                    $('.slug-verification').html(`
                        <small class="text--info"><i class="las la-spinner la-spin"></i> @lang('Verifying')</small>
                    `);
                    $.get("{{ route('admin.manage.property.check.slug', @$property->id) }}", {
                        slug: slug
                    }, function(response) {
                        if (!response.exists) {
                            $('.slug-verification').html(`
                                <small class="text--success"><i class="las la-check"></i> @lang('Verified')</small>
                            `);
                            closestForm.find('[type=submit]').removeClass('disabled')
                        }
                        if (response.exists) {
                            $('.slug-verification').html(`
                                <small class="text--danger"><i class="las la-times"></i> @lang('Slug already exists')</small>
                            `);
                        }
                    });
                } else {
                    $('.slug-verification').addClass('d-none');
                }
            })
        })(jQuery);
    </script>
@endpush

@push('style')
    <style>
        .image-uploader {
            border: 2px dashed #c8c8c8;
        }
    </style>
@endpush
