@php
    $buttons = $buttons ?? [];
    $onDark = $onDark ?? false;
@endphp
@if(count($buttons) > 0)
    <div class="flex flex-col sm:flex-row flex-wrap gap-3 justify-center">
        @foreach($buttons as $i => $btn)
            @php
                $action = $btn['action'] ?? 'custom';
                $class = match($action) {
                    'whatsapp' => 'btn-whatsapp',
                    'pricing' => $onDark ? 'btn-outline !border-white !text-white hover:!bg-white/10' : 'btn-secondary',
                    'register' => 'btn-primary',
                    default => $i === 0 ? 'btn-primary' : ($onDark ? 'btn-outline !border-white !text-white' : 'btn-secondary'),
                };
            @endphp
            <a href="{{ $btn['url'] }}"
               class="{{ $class }}"
               @if(in_array($action, ['whatsapp', 'custom'], true)) target="_blank" rel="noopener noreferrer" @endif>
                @if($action === 'whatsapp')
                    <i class="fab fa-whatsapp"></i>
                @elseif($action === 'pricing')
                    <i class="fas fa-tags"></i>
                @elseif($action === 'register')
                    <i class="fas fa-user-plus"></i>
                @else
                    <i class="fas fa-external-link-alt"></i>
                @endif
                {{ $btn['label'] }}
            </a>
        @endforeach
    </div>
@endif
