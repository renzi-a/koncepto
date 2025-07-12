@props([
    'active' => false,
])

@php
    $base = '-ml-28 w-[28rem] text-lg text-white font-medium p-4 pl-10 rounded-full transition-all duration-300 ease-in-out flex flex-row items-center gap-4';
    $hover = 'hover:bg-[#3E8E24] hover:scale-105 hover:translate-x-12';
    $activeClasses = $active ? 'bg-[#3E8E24] scale-105 translate-x-12' : 'bg-[#56AB2F]';
@endphp

<div>
   <a {{ $attributes->merge([
       'class' => "$base $hover $activeClasses"
   ]) }}>
      {{ $slot }}
   </a>
</div>
