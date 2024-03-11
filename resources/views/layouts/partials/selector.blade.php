@php
    $name = $name ?? 'status';
    $selected = $selected ?? old($name, $value ?? null);
    $label = $label ?? __(ucfirst($name));
@endphp

<div class="form-group">
    <label for="{{ $name }}">{{ __($label) }}</label>
    <select name="{{ $name }}" class="form-control @error($name) is-invalid @enderror" id="{{ $name }}">
        @foreach ($options as $value => $label)
            <option value="{{ $value }}" {{ $selected == $value ? 'selected' : '' }}>{{ __($label) }}</option>
        @endforeach
    </select>
    @error($name)
        <span class="invalid-feedback" role="alert">
            <strong>{{ $message }}</strong>
        </span>
    @enderror
</div>
