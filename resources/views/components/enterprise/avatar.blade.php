<?php
$user = $user ?? null;
$name = $name ?? ($user?->name ?? 'User');
$image = $image ?? ($user?->avatar ?? null);
$size = $size ?? 'md';
$surname = $surname ?? false;

$sizeClasses = match($size) {
    'xs' => 'h-6 w-6 text-[10px]',
    'sm' => 'h-8 w-8 text-xs',
    'md' => 'h-10 w-10 text-sm',
    'lg' => 'h-12 w-12 text-base',
    'xl' => 'h-16 w-16 text-lg',
    '2xl' => 'h-20 w-20 text-xl',
    default => 'h-10 w-10 text-sm',
};

if (! function_exists('getInitials')) {
    function getInitials(string $name, bool $surname = false): string {
        $parts = explode(' ', trim($name));
        if (count($parts) === 0) {
            return '?';
        }
        if ($surname || count($parts) === 1) {
            return strtoupper(substr($parts[0], 0, 2));
        }
        return strtoupper(substr($parts[0], 0, 1) . substr($parts[count($parts) - 1], 0, 1));
    }
}

if (! function_exists('getAvatarColor')) {
    function getAvatarColor(string $name): string {
        $colors = [
            'bg-blue-600',
            'bg-green-600',
            'bg-amber-600',
            'bg-violet-600',
            'bg-rose-600',
            'bg-cyan-600',
            'bg-orange-600',
            'bg-blue-600',
        ];
        $index = crc32($name) % count($colors);
        return $colors[$index];
    }
}

$initials = getInitials($name, $surname);
$bgColor = getAvatarColor($name);
?>

@if($image)
    <img
        src="{{ asset('storage/' . $image) }}"
        alt="{{ $name }}"
        class="{{ $sizeClasses }} rounded-full object-cover ring-2 ring-white shadow-sm"
    >
@else
    <div class="{{ $sizeClasses }} {{ $bgColor }} rounded-full flex items-center justify-center text-white font-medium ring-2 ring-white shadow-sm">
        {{ $initials }}
    </div>
@endif