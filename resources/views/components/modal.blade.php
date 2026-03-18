@props([
    'id',
    'title' => '',
    'centered' => true,
    'backdrop' => true,
    'keyboard' => true,
    'size' => null,
])

@php
    $sizeClass = match ($size) {
        'sm' => 'modal-sm',
        'lg' => 'modal-lg',
        'xl' => 'modal-xl',
        'fullscreen' => 'modal-fullscreen',
        default => '',
    };
@endphp

<div 
    class="modal fade" 
    id="{{ $id }}" 
    tabindex="-1" 
    aria-labelledby="{{ $id }}Label" 
    aria-hidden="true"
    data-bs-backdrop="{{ $backdrop ? 'true' : 'static' }}"
    data-bs-keyboard="{{ $keyboard ? 'true' : 'false' }}"
>
  <div class="modal-dialog 
      {{ $centered ? 'modal-dialog-centered' : '' }} 
      {{ $sizeClass }}">
      
    <div class="modal-content">
      
      @if($title)
      <div class="modal-header">
        <h5 class="modal-title" id="{{ $id }}Label">{{ $title }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      @endif

      <div class="modal-body">
        {{ $slot }}
      </div>

      @isset($footer)
        <div class="modal-footer">
            {{ $footer }}
        </div>
      @endisset

    </div>
  </div>
</div>
