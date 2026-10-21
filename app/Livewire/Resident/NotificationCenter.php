<?php

namespace App\Livewire\Resident;

use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.resident')]
class NotificationCenter extends Component
{
    public function markAsRead(int $notificationId)
    {
        $notif = Notification::where('id', $notificationId)
            ->where('user_id', Auth::id())
            ->first();

        if ($notif && ! $notif->is_read) {
            $notif->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }
    }

    public function markAllAsRead()
    {
        Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        session()->flash('success', 'All notifications marked as read.');
    }

    public function render()
    {
        $notifications = Notification::with('report')
            ->where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('livewire.resident.notification-center', [
            'notifications' => $notifications,
        ]);
    }
}
