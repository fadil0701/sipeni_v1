@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-4xl">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-xl font-bold text-gray-900">Notifikasi</h1>
        @if(auth()->user()->unreadNotifications()->count() > 0)
            <form method="POST" action="{{ route('notifications.mark-all-read') }}">
                @csrf
                <button type="submit" class="text-sm text-blue-600 hover:text-blue-800">Tandai semua sudah dibaca</button>
            </form>
        @endif
    </div>

    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
        @forelse($notifications as $notification)
            @php
                $data = $notification->data;
                $isUnread = $notification->read_at === null;
            @endphp
            <div class="border-b border-gray-100 px-4 py-4 last:border-b-0 {{ $isUnread ? 'bg-blue-50/50' : '' }}">
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0 flex-1">
                        <p class="text-sm text-gray-900">{{ $data['message'] ?? 'Notifikasi' }}</p>
                        <p class="mt-1 text-xs text-gray-500">{{ $notification->created_at->diffForHumans() }}</p>
                    </div>
                    @if(!empty($data['url']))
                        <form method="POST" action="{{ route('notifications.mark-read', $notification->id) }}">
                            @csrf
                            <button type="submit" class="shrink-0 text-sm font-medium text-blue-600 hover:text-blue-800">
                                Buka
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="px-4 py-12 text-center text-sm text-gray-500">Belum ada notifikasi.</div>
        @endforelse
    </div>

    <div class="mt-4">{{ $notifications->links() }}</div>
</div>
@endsection
