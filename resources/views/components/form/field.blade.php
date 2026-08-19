{{--
    Global Validation Field — used by every form in the project.
    Invalid state = red border + icon + popover (hover/click/tap), NEVER a
    permanent line of text under the input.
--}}
@props(['name', 'type' => 'text', 'label', 'toggle' => false, 'value' => null])
@php $hasError = $errors->has($name); @endphp
<div class="field vfield {{ $hasError ? 'has-error' : '' }}" data-field="{{ $name }}">
    <label for="{{ $name }}">{{ $label }}</label>
    <div class="input-wrap {{ $toggle ? 'has-leading' : '' }}">
        <input
            type="{{ $type }}"
            id="{{ $name }}"
            name="{{ $name }}"
            value="{{ old($name, $value) }}"
            {{ $attributes }}
        >
        @if($toggle)
            <button type="button" class="pw-toggle"
                    data-toggle-target="{{ $name }}"
                    data-show-label="{{ __('auth.show') }}"
                    data-hide-label="{{ __('auth.hide') }}">{{ __('auth.show') }}</button>
        @endif
        @if($hasError)
            <button type="button" class="vfield-icon" aria-label="{{ __('validation-ui.show_message') }}">
                <x-icon name="alert-circle" />
            </button>
        @endif
    </div>
    @if($hasError)
        <div class="vfield-popover" role="tooltip">
            @if(count($errors->get($name)) > 1)
                <ul>
                    @foreach($errors->get($name) as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            @else
                <p>{{ $errors->first($name) }}</p>
            @endif
        </div>
    @endif
</div>
