@php
    $names = [9 => 'El pacificador', 1 => 'El reformador', 2 => 'El ayudador', 3 => 'El motivador', 4 => 'El artista', 5 => 'El pensador', 6 => 'El lealista', 7 => 'El generalista', 8 => 'El líder'];
    $colors = [1 => '#2b5d8c', 2 => '#b5121b', 3 => '#b5121b', 4 => '#2b5d8c', 5 => '#2b5d8c', 6 => '#2b5d8c', 7 => '#b5121b', 8 => '#b5121b', 9 => '#3c8c5b'];
    $pos = []; foreach (range(1, 9) as $n) { $a = deg2rad(-90 + ($n % 9) * 40); $pos[$n] = [200 + 150 * cos($a), 200 + 150 * sin($a)]; }
    $lines = [[9, 3], [3, 6], [6, 9], [1, 4], [4, 2], [2, 8], [8, 5], [5, 7], [7, 1]];
@endphp
<figure class="my-6">
    <svg viewBox="0 0 400 420" class="mx-auto w-full max-w-md" role="img" aria-label="Eneagrama">
        <circle cx="200" cy="200" r="150" fill="none" stroke="#d6d3d1" stroke-width="2"/>
        @foreach ($lines as [$a, $b])<line x1="{{ $pos[$a][0] }}" y1="{{ $pos[$a][1] }}" x2="{{ $pos[$b][0] }}" y2="{{ $pos[$b][1] }}" stroke="#a8a29e" stroke-width="1.5"/>@endforeach
        @foreach ($pos as $n => [$x, $y])
            @php $lx = 200 + 192 * cos(deg2rad(-90 + ($n % 9) * 40)); $ly = 200 + 192 * sin(deg2rad(-90 + ($n % 9) * 40)); @endphp
            <circle cx="{{ $x }}" cy="{{ $y }}" r="18" fill="{{ $colors[$n] }}"/>
            <text x="{{ $x }}" y="{{ $y + 6 }}" text-anchor="middle" fill="#fff" font-size="16" font-weight="700">{{ $n }}</text>
            <text x="{{ $lx }}" y="{{ $ly + 4 }}" text-anchor="middle" fill="#44403c" font-size="11">{{ $names[$n] }}</text>
        @endforeach
    </svg>
    <figcaption class="text-center text-xs text-stone-500">{{ __('Rojo: carácter extrovertido · Azul: introvertido · Verde: ambos. Las líneas unen cada tipo con sus direcciones de integración y desintegración.') }}</figcaption>
</figure>
