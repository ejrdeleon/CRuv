@php
    $layout = auth()->user()->isResident() ? 'layouts.resident' : 'layouts.admin';
@endphp

<x-dynamic-component :component="$layout" title="My Profile">
    <div class="max-w-3xl mx-auto space-y-6">
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
            <div class="flex items-center gap-4 pb-6 border-b border-slate-100">
                <div class="w-16 h-16 rounded-2xl bg-teal-600 text-white flex items-center justify-center font-bold text-2xl shadow-sm">
                    {{ strtoupper(substr($user->name, 0, 2)) }}
                </div>
                <div>
                    <h2 class="text-xl font-bold text-slate-900">{{ $user->name }}</h2>
                    <p class="text-sm text-slate-500">{{ $user->email }}</p>
                    <span class="inline-block mt-1 px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $user->isAdmin() ? 'bg-purple-100 text-purple-800' : ($user->isStaff() ? 'bg-blue-100 text-blue-800' : 'bg-teal-100 text-teal-800') }}">
                        Role: {{ ucfirst($user->role) }}
                    </span>
                    @if($user->department)
                        <span class="inline-block mt-1 ml-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-700">
                            {{ $user->department->name }}
                        </span>
                    @endif
                </div>
            </div>

            @if ($errors->any())
                <div class="mt-4 p-3 bg-rose-50 border border-rose-200 text-rose-700 rounded-xl text-xs space-y-1">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('profile.update') }}" class="mt-6 space-y-5">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="name" class="block text-xs font-semibold text-slate-700 uppercase">Full Name</label>
                        <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required
                            class="mt-1 block w-full rounded-xl border border-slate-300 px-3.5 py-2 text-sm text-slate-900 focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-500/20">
                    </div>

                    <div>
                        <label for="email" class="block text-xs font-semibold text-slate-700 uppercase">Email Address</label>
                        <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required
                            class="mt-1 block w-full rounded-xl border border-slate-300 px-3.5 py-2 text-sm text-slate-900 focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-500/20">
                    </div>

                    <div>
                        <label for="phone" class="block text-xs font-semibold text-slate-700 uppercase">Mobile Number</label>
                        <input id="phone" name="phone" type="text" value="{{ old('phone', $user->phone) }}" required
                            class="mt-1 block w-full rounded-xl border border-slate-300 px-3.5 py-2 text-sm text-slate-900 focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-500/20">
                    </div>

                    <div>
                        <label for="barangay" class="block text-xs font-semibold text-slate-700 uppercase">Barangay</label>
                        <input id="barangay" name="barangay" type="text" value="{{ old('barangay', $user->barangay ?? 'Barangay San Isidro') }}"
                            class="mt-1 block w-full rounded-xl border border-slate-300 px-3.5 py-2 text-sm text-slate-900 focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-500/20">
                    </div>
                </div>

                <div>
                    <label for="address" class="block text-xs font-semibold text-slate-700 uppercase">Address / Street / Purok</label>
                    <input id="address" name="address" type="text" value="{{ old('address', $user->address) }}"
                        class="mt-1 block w-full rounded-xl border border-slate-300 px-3.5 py-2 text-sm text-slate-900 focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-500/20">
                </div>

                <div class="pt-4 border-t border-slate-100">
                    <h3 class="text-sm font-bold text-slate-900 mb-1">Change Password (Optional)</h3>
                    <p class="text-xs text-slate-500 mb-4">Leave blank if you do not wish to modify your current password.</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="current_password" class="block text-xs font-semibold text-slate-700 uppercase">Current Password</label>
                            <input id="current_password" name="current_password" type="password"
                                class="mt-1 block w-full rounded-xl border border-slate-300 px-3.5 py-2 text-sm text-slate-900 focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-500/20">
                        </div>

                        <div>
                            <label for="new_password" class="block text-xs font-semibold text-slate-700 uppercase">New Password</label>
                            <input id="new_password" name="new_password" type="password"
                                class="mt-1 block w-full rounded-xl border border-slate-300 px-3.5 py-2 text-sm text-slate-900 focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-500/20">
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-2">
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-semibold text-sm shadow-sm transition">
                        Save Profile Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-dynamic-component>
