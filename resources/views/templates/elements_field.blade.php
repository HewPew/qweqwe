@php $default_value = isset($default_value) ? $default_value : ''; @endphp
@php $uniqueInputId = sha1(time() + rand(999,99999)); @endphp

@if($v['type'] !== 'select')
    <input
        @if(isset($v['saveToHistory']) && $v['type'] !== 'file')
            onchange="addFieldToHistory(event.target.value, '{{ $v['label'] }}');"
        @endif

        @if($v['type'] == 'file' && isset($v['resize']))
            id="croppie-input{{ $uniqueInputId }}"
        @endif

        @isset($v['step'])
            step="{{ $v['step'] }}"
        @endisset

        value="{{ $default_value }}"
        type="{{ $v['type'] }}" {{ $is_required }}
        name="{{ $k }}"
        placeholder="{{ $v['label'] }}"
        data-field="{{ $model }}_{{ $k }}"
        @if ($v['type'] !== 'file') class="form-control" @endif
    />

    @if($v['type'] == 'file' && isset($v['resize']))
        <div style="display: none;" id="croppie-block{{ $uniqueInputId }}" class="croppie-block text-center">
            <input type="hidden" name="{{ $k }}_base64" id="croppie-result-base64{{ $uniqueInputId }}">
            <div class="croppie-demo" data-croppie-id="{{ $uniqueInputId }}"></div>
            <button type="button" data-croppie-id="{{ $uniqueInputId }}" class="btn croppie-save btn-sm btn-success">Сохранить обрезку</button>
        </div>
    @endif

@elseif ($v['type'] === 'select')
    @php $default_value = is_array($default_value) ? $default_value : [$default_value] @endphp

    <select
        @isset($v['saveToHistory'])
            onchange="addFieldToHistory(event.target.value, '{{ $v['label'] }}');"
        @endisset

        @isset($v['multiple'])
            multiple="multiple"
            name="{{ $k }}[]"
        @else
            name="{{ $k }}"
        @endisset

        {{ $is_required }}
        data-field="{{ $model }}_{{ $k }}"
        class="js-chosen"
    >
        @if(is_array($v['values']))
            @foreach($v['values'] as $optionK => $optionV)
                <option
                    @if(in_array($optionV, $default_value)) selected @endif
                value="{{ $optionK }}">
                    {{ $optionV }}
                </option>
            @endforeach
        @else
            @foreach(app("App\\" . $v['values'])->all() as $option)
                <option
                    @isset($v['getFieldKey'])

                        @if(in_array($option[$v['getFieldKey']], $default_value)) selected @endif
                        value="{{ $option[$v['getFieldKey']] }}"

                    @else

                    @if(in_array($option->id, $default_value)) selected @endif
                        value="{{ $option->id }}"

                    @endisset
                >
                    @isset($v['getField'])
                        {{ $option[$v['getField']] }}
                    @else
                        {{ $option->name }}
                    @endisset
                </option>
            @endforeach
        @endif
    </select>
@endif
