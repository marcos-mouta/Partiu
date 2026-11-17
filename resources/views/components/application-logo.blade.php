@props(['size' => 'h-12'])

<img src="{{ asset('images/logo.png') }}" alt="Partiu" {{ $attributes->except('class')->merge(['class' => $size . ' w-auto']) }}>
