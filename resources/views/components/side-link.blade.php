@props([
    'active' => false,
])

<div class="pt-2">
   <a {{ $attributes->merge(['class' => 'flex items-center text-2xl px-4 py-3 rounded-md transition hover:bg-white hover:text-[#56AB2F]']) }}>
      <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
      </svg>
      <span>{{ $slot }}</span>
   </a>
</div>