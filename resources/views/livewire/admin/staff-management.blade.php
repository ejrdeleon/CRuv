<div class="space-y-4">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-base sm:text-lg font-bold text-slate-900">Administrator & Department Registry</h1>
            <p class="text-xs text-slate-500">Configure municipal response departments, liaisons, and administrator logins.</p>
        </div>

        <div class="flex items-center gap-2">
            <button type="button" wire:click="openNewDeptModal"
                class="px-3 py-1.5 rounded-md border border-slate-300 hover:bg-slate-50 text-slate-700 font-semibold text-xs transition">
                + Add Department
            </button>
            <button type="button" wire:click="openNewAdminModal"
                class="px-3.5 py-1.5 rounded-md bg-teal-800 hover:bg-teal-900 text-white font-semibold text-xs shadow-xs transition flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>+ New Administrator</span>
            </button>
        </div>
    </div>

    <!-- Admin Accounts Table -->
    <div class="bg-white rounded-lg border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-3.5 border-b border-slate-200">
            <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Authorized Barangay Administrators ({{ $adminUsers->count() }})</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 font-semibold text-[11px]">
                    <tr>
                        <th class="py-2 px-3">Administrator</th>
                        <th class="py-2 px-3">Role</th>
                        <th class="py-2 px-3">Contact Phone</th>
                        <th class="py-2 px-3">Barangay</th>
                        <th class="py-2 px-3 text-right">Account Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($adminUsers as $admin)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-2.5 px-3">
                                <p class="font-semibold text-slate-900">{{ $admin->name }}</p>
                                <span class="text-[11px] text-slate-500">{{ $admin->email }}</span>
                            </td>
                            <td class="py-2.5 px-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold uppercase bg-slate-100 text-slate-700 border border-slate-200">
                                    {{ $admin->role }}
                                </span>
                            </td>
                            <td class="py-2.5 px-3 text-slate-600">
                                {{ $admin->phone ?? 'N/A' }}
                            </td>
                            <td class="py-2.5 px-3 text-slate-600">
                                {{ $admin->barangay ?? 'Barangay San Isidro' }}
                            </td>
                            <td class="py-2.5 px-3 text-right">
                                @if($admin->id === auth()->id())
                                    <span class="text-slate-400 font-medium text-[11px]">Current Session</span>
                                @else
                                    <button type="button" wire:click="toggleUserStatus({{ $admin->id }})"
                                        class="px-2.5 py-1 rounded text-xs font-semibold border transition {{ $admin->is_active ? 'border-emerald-300 bg-emerald-50 text-emerald-800 hover:bg-emerald-100' : 'border-rose-300 bg-rose-50 text-rose-800 hover:bg-rose-100' }}">
                                        {{ $admin->is_active ? 'Active' : 'Disabled' }}
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Departments Grid -->
    <div class="space-y-2.5">
        <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Municipal Departments & Action Units</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach($departments as $dept)
                <div class="bg-white rounded-lg p-3.5 border border-slate-200 shadow-xs space-y-2 flex flex-col justify-between">
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between">
                            <span class="px-1.5 py-0.5 rounded font-mono font-bold text-[11px] bg-slate-100 text-slate-700 border border-slate-200">
                                {{ $dept->code }}
                            </span>
                            <span class="text-[11px] font-semibold text-teal-800">
                                {{ $dept->reports_count }} reports
                            </span>
                        </div>
                        <h3 class="font-bold text-xs text-slate-900">{{ $dept->name }}</h3>
                        <p class="text-xs text-slate-500 line-clamp-2 leading-relaxed">{{ $dept->description }}</p>
                    </div>

                    <div class="pt-2 border-t border-slate-100 text-xs space-y-0.5 text-slate-600">
                        @if($dept->contact_person)
                            <p class="truncate"><span class="font-semibold text-slate-700">Liaison:</span> {{ $dept->contact_person }}</p>
                        @endif
                        @if($dept->contact_phone)
                            <p><span class="font-semibold text-slate-700">Hotline:</span> {{ $dept->contact_phone }}</p>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- New Admin Modal -->
    @if($showNewAdminModal)
        <div class="fixed inset-0 z-50 bg-slate-900/50 flex items-center justify-center p-4">
            <div class="bg-white rounded-lg max-w-md w-full p-5 shadow-lg border border-slate-200 space-y-3.5">
                <div class="flex items-center justify-between border-b border-slate-200 pb-2.5">
                    <h3 class="text-sm font-bold text-slate-900">Add Barangay Administrator</h3>
                    <button type="button" wire:click="$set('showNewAdminModal', false)" class="text-slate-400 hover:text-slate-700 text-lg leading-none">×</button>
                </div>

                <form wire:submit="createAdmin" class="space-y-3 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">Full Name *</label>
                        <input type="text" wire:model="name" class="w-full p-2 rounded-md border border-slate-300">
                        @error('name') <span class="text-rose-600">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">Email Address *</label>
                        <input type="email" wire:model="email" class="w-full p-2 rounded-md border border-slate-300">
                        @error('email') <span class="text-rose-600">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">Contact Phone *</label>
                        <input type="text" wire:model="phone" class="w-full p-2 rounded-md border border-slate-300" placeholder="0917-000-0000">
                        @error('phone') <span class="text-rose-600">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">Password *</label>
                        <input type="password" wire:model="password" class="w-full p-2 rounded-md border border-slate-300" placeholder="Min 8 characters">
                        @error('password') <span class="text-rose-600">{{ $message }}</span> @enderror
                    </div>

                    <div class="pt-2.5 border-t border-slate-200 flex justify-end gap-2">
                        <button type="button" wire:click="$set('showNewAdminModal', false)" class="px-3 py-1.5 rounded-md border border-slate-300 font-semibold text-slate-700 hover:bg-slate-50">
                            Cancel
                        </button>
                        <button type="submit" class="px-3.5 py-1.5 rounded-md bg-teal-800 hover:bg-teal-900 text-white font-semibold shadow-xs">
                            Create Account
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- New Dept Modal -->
    @if($showNewDeptModal)
        <div class="fixed inset-0 z-50 bg-slate-900/50 flex items-center justify-center p-4">
            <div class="bg-white rounded-lg max-w-md w-full p-5 shadow-lg border border-slate-200 space-y-3.5">
                <div class="flex items-center justify-between border-b border-slate-200 pb-2.5">
                    <h3 class="text-sm font-bold text-slate-900">Add Department / Action Unit</h3>
                    <button type="button" wire:click="$set('showNewDeptModal', false)" class="text-slate-400 hover:text-slate-700 text-lg leading-none">×</button>
                </div>

                <form wire:submit="createDepartment" class="space-y-3 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">Department Name *</label>
                        <input type="text" wire:model="deptName" placeholder="e.g. Traffic Management Unit" class="w-full p-2 rounded-md border border-slate-300">
                        @error('deptName') <span class="text-rose-600">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">Department Code *</label>
                        <input type="text" wire:model="deptCode" placeholder="e.g. TMU" class="w-full p-2 rounded-md border border-slate-300 uppercase">
                        @error('deptCode') <span class="text-rose-600">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">Description</label>
                        <textarea wire:model="deptDescription" rows="2" class="w-full p-2 rounded-md border border-slate-300"></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block font-bold text-slate-700 uppercase mb-1">Liaison Contact</label>
                            <input type="text" wire:model="deptContactPerson" class="w-full p-2 rounded-md border border-slate-300">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 uppercase mb-1">Hotline Phone</label>
                            <input type="text" wire:model="deptContactPhone" class="w-full p-2 rounded-md border border-slate-300">
                        </div>
                    </div>

                    <div class="pt-2.5 border-t border-slate-200 flex justify-end gap-2">
                        <button type="button" wire:click="$set('showNewDeptModal', false)" class="px-3 py-1.5 rounded-md border border-slate-300 font-semibold text-slate-700 hover:bg-slate-50">
                            Cancel
                        </button>
                        <button type="submit" class="px-3.5 py-1.5 rounded-md bg-teal-800 hover:bg-teal-900 text-white font-semibold shadow-xs">
                            Save Department
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
