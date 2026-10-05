@props([
    'variant' => 'default',
])

@php
$classes = match(strtolower($variant)) {
    'active', 'approved', 'paid', 'present' => 'bg-[#2D2D2D] text-white',
    'partial', 'partially_paid' => 'bg-amber-100 text-amber-800 border border-amber-300',
    'inactive', 'draft', 'half_day' => 'bg-gray-200 text-gray-700',
    'pending', 'on_leave' => 'bg-gray-100 text-[#2D2D2D] border border-gray-400',
    'rejected', 'terminated', 'absent' => 'bg-gray-300 text-gray-800',
    default => 'bg-[#2D2D2D] text-white',
};
@endphp

<span {{ $attributes->merge(['class' => "inline-block px-2 py-0.5 rounded text-[10px] font-bold tracking-wider uppercase $classes"]) }}>
    {{ $slot }}
</span>
