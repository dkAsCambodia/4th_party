{{-- <input type="hidden" name="channel_id" value="{{ $id }}"> --}}
@forelse ($prameterSetting as $prameterSettingVal)
    <div id="row">
        <div class="input-group mb-3 col-md-6">
            <input type="text" class="form-control m-input" name="parameter_name[]"
                value="{{ $prameterSettingVal->parameter_name }}" readonly>
        </div>
    </div>
@empty
    {{-- <div id="row">
        <div class="input-group mb-3 col-md-6">
            <div class="input-group-prepend">
                <button class="btn btn-danger" id="DeleteRow" type="button">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
            <input type="text" class="form-control m-input" name="parameter_name[]">
        </div>
    </div> --}}
@endforelse
{{--
<div id="newinput"></div>

@if (!count($prameterSetting))
    <div class="input-group mb-3 col-md-6">
        <div class="input-group-prepend">
            <button id="rowAdderAdd" type="button" class="btn btn-dark">
                <i class="fa fa-plus"></i>
            </button>
        </div>
    </div>
@endif --}}
