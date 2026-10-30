{{-- <x-form-error field="email" />: primer error de validación de un campo.
     Va justo después del input que lleva la clase is-invalid (ver docs/guia-visual.md). --}}
@props(['field'])

@error($field)
    <div class="invalid-feedback" id="{{ $field }}-error">{{ $message }}</div>
@enderror
