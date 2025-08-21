@props(['active' => false])

@php
    $base = '-ml-28 w-[25rem] text-base text-white font-medium py-3 px-5 pl-10 rounded-full transition-all duration-300 ease-in-out flex flex-row items-center gap-4';
    $hover = 'hover:bg-[#3E8E24] hover:scale-105 hover:translate-x-12';
    $activeClasses = $active ? 'bg-[#3E8E24] scale-105 translate-x-1' : 'bg-[#56AB2F]';
@endphp

<a {{ $attributes->merge([
    'class' => "sidebar-link $base $hover $activeClasses"
]) }}>
    {{ $slot }}
</a>
