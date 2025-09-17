<div class="row">
    <div class="col-lg-12">
        <div class="row justify-content-center gap-4">
            @if (@$property->video)
                <div class="col-lg-8">
                    <iframe width="100%" height="400px" src="{{ convertToEmbedUrl(@$property->video) }}" frameborder="0" allowfullscreen></iframe>
                </div>
            @endif
            <div class="col-12">
                <div class="form-group">
                    <label>@lang('Youtube Video Link')</label>
                    <input type="text" name="video" value="{{ old('video', @$property->video) }}" class="form-control">
                </div>
            </div>
        </div>
    </div>
</div>
