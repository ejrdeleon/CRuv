@php
    $layout = auth()->user()->isResident() ? 'layouts.resident' : 'layouts.admin';
@endphp

<x-dynamic-component :component="$layout" title="Account Profile">
    <div class="max-w-3xl mx-auto space-y-4">
        <div class="bg-white rounded-lg border border-slate-200 p-5 shadow-xs">
            <div class="flex items-center gap-3.5 pb-4 border-b border-slate-200">
                <div class="w-11 h-11 rounded-md bg-teal-800 text-white flex items-center justify-center font-bold text-base shrink-0">
                    {{ strtoupper(substr($user->name, 0, 2)) }}
                </div>
                <div>
                    <h1 class="text-base font-bold text-slate-900">{{ $user->name }}</h1>
                    <p class="text-xs text-slate-500">{{ $user->email }}</p>
                    <div class="flex items-center gap-1.5 mt-1">
                        <span class="inline-block px-2 py-0.2 rounded text-[10px] font-semibold uppercase bg-slate-100 text-slate-700 border border-slate-200">
                            Role: {{ ucfirst($user->role) }}
                        </span>
                        @if($user->department)
                            <span class="inline-block px-2 py-0.2 rounded text-[10px] font-medium bg-slate-100 text-slate-700 border border-slate-200">
                                {{ $user->department->name }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            @if ($errors->any())
                <div class="mt-3 p-3 bg-rose-50 border border-rose-200 text-rose-800 rounded-md text-xs space-y-1">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('profile.update') }}" class="mt-4 space-y-4">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label for="name" class="block text-xs font-semibold text-slate-700 uppercase">Full Name</label>
                        <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required
                            class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-1.5 text-xs text-slate-900 focus:border-teal-700 focus:outline-none focus:ring-1 focus:ring-teal-700">
                    </div>

                    <div>
                        <label for="email" class="block text-xs font-semibold text-slate-700 uppercase">Email Address</label>
                        <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required
                            class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-1.5 text-xs text-slate-900 focus:border-teal-700 focus:outline-none focus:ring-1 focus:ring-teal-700">
                    </div>

                    <div>
                        <label for="phone" class="block text-xs font-semibold text-slate-700 uppercase">Mobile Number</label>
                        <input id="phone" name="phone" type="text" value="{{ old('phone', $user->phone) }}" required
                            class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-1.5 text-xs text-slate-900 focus:border-teal-700 focus:outline-none focus:ring-1 focus:ring-teal-700">
                    </div>

                    <div>
                        <label for="barangay" class="block text-xs font-semibold text-slate-700 uppercase">Barangay</label>
                        <input id="barangay" name="barangay" type="text" value="{{ old('barangay', $user->barangay ?? 'Barangay San Isidro') }}"
                            class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-1.5 text-xs text-slate-900 focus:border-teal-700 focus:outline-none focus:ring-1 focus:ring-teal-700">
                    </div>
                </div>

                <div>
                    <label for="address" class="block text-xs font-semibold text-slate-700 uppercase">Address / Street / Purok</label>
                    <input id="address" name="address" type="text" value="{{ old('address', $user->address) }}"
                        class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-1.5 text-xs text-slate-900 focus:border-teal-700 focus:outline-none focus:ring-1 focus:ring-teal-700">
                </div>

                <div class="pt-3 border-t border-slate-200">
                    <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wide mb-0.5">Password Update</h2>
                    <p class="text-[11px] text-slate-500 mb-3">Leave blank if you do not want to alter your login password.</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label for="current_password" class="block text-xs font-semibold text-slate-700 uppercase">Current Password</label>
                            <input id="current_password" name="current_password" type="password"
                                class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-1.5 text-xs text-slate-900 focus:border-teal-700 focus:outline-none focus:ring-1 focus:ring-teal-700">
                        </div>

                        <div>
                            <label for="new_password" class="block text-xs font-semibold text-slate-700 uppercase">New Password</label>
                            <input id="new_password" name="new_password" type="password"
                                class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-1.5 text-xs text-slate-900 focus:border-teal-700 focus:outline-none focus:ring-1 focus:ring-teal-700">
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-1">
                    <button type="submit" class="px-4 py-2 rounded-md bg-teal-800 hover:bg-teal-900 text-white font-semibold text-xs shadow-xs transition">
                        Save Profile
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-dynamic-component>
