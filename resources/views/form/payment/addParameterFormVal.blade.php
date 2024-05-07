@foreach ($parameterSetting as $parameterSettingVal)
    @foreach ($parameterValue as $parameterValueVal)
        @if ($parameterValueVal->parameter_setting_id == $parameterSettingVal->id)
            <div class="mb-3 col-md-6">
                <label class="form-label">{{ $parameterSettingVal->parameter_name }}</label>
                <input type="hidden" class="form-control" name="parameter_id[]" value="{{ $parameterSettingVal->id }}">
                <input type="text" class="form-control" name="parameter_val{{ $parameterSettingVal->id }}"
                    value="{{ $parameterValueVal->parameter_setting_value }}" required>
            </div>
        @endif
    @endforeach
@endforeach
