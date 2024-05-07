<form method="POST" id="add_parameter_setting">
    @csrf
    <input type="hidden" name="channel_id" value="{{ $id }}">
    @forelse ($prameterSetting as $prameterSettingVal)
        <div id="row">
            <div class="input-group mb-3 col-md-6">
                {{-- <div class="input-group-prepend">
                    <button class="btn btn-danger" id="DeleteRow" type="button">
                        <i class="bi bi-trash"></i>
                    </button>
                </div> --}}
                <input type="text" class="form-control m-input" name="parameter_name[]"
                    value="{{ $prameterSettingVal->parameter_name }}" readonly>
            </div>
        </div>
    @empty
        <div id="row">
            <div class="input-group mb-3 col-md-6">
                <div class="input-group-prepend">
                    <button class="btn btn-danger" id="DeleteRow" type="button">
                        <i class="bi bi-trash"></i>
                        {{-- {{ __('messages.Delete') }} --}}
                    </button>
                </div>
                <input type="text" class="form-control m-input" name="parameter_name[]">
            </div>
        </div>
    @endforelse


    <div id="newinput"></div>


    @if (!count($prameterSetting))
        <button id="rowAdder" type="button" class="btn btn-dark">
            <i class="fa fa-plus"></i> {{-- __('messages.ADD') --}}
        </button>
        <div class="submit-section">
            <button type="submit" class="btn btn-danger shadow btn-xs me-1">{{ __('messages.Save') }}</button>
        </div>
    @endif
</form>
