<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Staff & Department Management</h1>
            <p class="text-xs text-slate-500">Configure municipal teams, field staff accounts, and monitor workload distribution.</p>
        </div>

        <div class="flex items-center gap-2">
            <button type="button" wire:click="openNewDeptModal"
                class="px-3.5 py-2 rounded-xl border border-slate-300 hover:bg-slate-100 text-slate-700 font-semibold text-xs shadow-sm transition">
                + Add Department
            </button>
            <button type="button" wire:click="openNewStaffModal"
                class="px-3.5 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs shadow-sm transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>New Staff Account</span>
            </button>
        </div>
    </div>

    <!-- Staff Accounts Workload Table -->
    <div class="bg-white rounded-3xl p-5 border border-slate-200 shadow-sm space-y-4">
        <h2 class="text-sm font-bold text-slate-900">Officers & Field Assignees ({{ $staffMembers->count() }})</h2>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="border-b border-slate-200 text-slate-400 uppercase font-semibold">
                    <tr>
                        <th class="pb-2.5">Officer Name</th>
                        <th class="pb-2.5">Role</th>
                        <th class="pb-2.5">Department</th>
                        <th class="pb-2.5">Contact</th>
                        <th class="pb-2.5">Active Cases</th>
                        <th class="pb-2.5">Completed</th>
                        <th class="pb-2.5 text-right">Account Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($staffMembers as $staff)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3">
                                <p class="font-bold text-slate-900">{{ $staff->name }}</p>
                                <span class="text-[11px] text-slate-400">{{ $staff->email }}</span>
                            </td>
                            <td class="py-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $staff->isAdmin() ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' }}">
                                    {{ $staff->role }}
                                </span>
                            </td>
                            <td class="py-3 text-slate-700 font-medium">
                                {{ $staff->department?->name ?? 'Central Administration' }}
                            </td>
                            <td class="py-3 text-slate-600">
                                {{ $staff->phone ?? 'N/A' }}
                            </td>
                            <td class="py-3 font-bold text-amber-700">
                                {{ $staff->active_cases_count }} active
                            </td>
                            <td class="py-3 font-bold text-emerald-700">
                                {{ $staff->completed_cases_count }} resolved
                            </td>
                            <td class="py-3 text-right">
                                @if($staff->id === auth()->id())
                                    <span class="text-slate-400 font-medium text-[11px]">Current User</span>
                                @else
                                    <button type="button" wire:click="toggleUserStatus({{ $staff->id }})"
                                        class="px-2.5 py-1 rounded-lg text-xs font-semibold transition {{ $staff->is_active ? 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' : 'bg-rose-50 text-rose-700 hover:bg-rose-100' }}">
                                        {{ $staff->is_active ? 'Active' : 'Disabled' }}
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
    <div class="space-y-3">
        <h2 class="text-sm font-bold text-slate-900">Departments & Units</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($departments as $dept)
                <div class="bg-white rounded-3xl p-5 border border-slate-200 shadow-sm space-y-3 flex flex-col justify-between">
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="px-2 py-0.5 rounded font-mono font-bold text-xs bg-slate-100 text-slate-700">
                                {{ $dept->code }}
                            </span>
                            <span class="text-xs font-bold text-teal-700">
                                {{ $dept->reports_count }} reports
                            </span>
                        </div>
                        <h3 class="font-bold text-sm text-slate-900">{{ $dept->name }}</h3>
                        <p class="text-xs text-slate-500 line-clamp-2 leading-relaxed">{{ $dept->description }}</p>
                    </div>

                    <div class="pt-3 border-t border-slate-100 text-xs space-y-1 text-slate-600">
                        @if($dept->contact_person)
                            <p><strong>Liaison:</strong> {{ $dept->contact_person }}</p>
                        @endif
                        @if($dept->contact_phone)
                            <p><strong>Hotline:</strong> {{ $dept->contact_phone }}</p>
                        @endif
                        <p class="text-slate-400 text-[11px] pt-1">{{ $dept->staff_count }} assigned staff accounts</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- New Staff Modal -->
    @if($showNewStaffModal)
        <div class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-base font-bold text-slate-900">Create Staff / Officer Account</h3>
                    <button type="button" wire:click="$set('showNewStaffModal', false)" class="text-slate-400 hover:text-slate-700 text-xl font-bold">×</button>
                </div>

                <form wire:submit="createStaff" class="space-y-3 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">Full Name *</label>
                        <input type="text" wire:model="name" class="w-full p-2.5 rounded-xl border border-slate-300">
                        @error('name') <span class="text-rose-600">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">Email Address *</label>
                        <input type="email" wire:model="email" class="w-full p-2.5 rounded-xl border border-slate-300">
                        @error('email') <span class="text-rose-600">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">Contact Phone *</label>
                        <input type="text" wire:model="phone" class="w-full p-2.5 rounded-xl border border-slate-300" placeholder="0918-000-0000">
                        @error('phone') <span class="text-rose-600">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block font-bold text-slate-700 uppercase mb-1">Role *</label>
                            <select wire:model="role" class="w-full p-2.5 rounded-xl border border-slate-300 bg-white">
                                <option value="staff">Staff Officer</option>
                                <option value="admin">Barangay Admin</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 uppercase mb-1">Department</label>
                            <select wire:model="department_id" class="w-full p-2.5 rounded-xl border border-slate-300 bg-white">
                                <option value="">None / Admin</option>
                                @foreach($departments as $d)
                                    <option value="{{ $d->id }}">{{ $d->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">Initial Password *</label>
                        <input type="password" wire:model="password" class="w-full p-2.5 rounded-xl border border-slate-300" placeholder="Min 8 characters">
                        @error('password') <span class="text-rose-600">{{ $message }}</span> @enderror
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex justify-end gap-2">
                        <button type="button" wire:click="$set('showNewStaffModal', false)" class="px-4 py-2 rounded-xl border border-slate-300 font-semibold text-slate-600 hover:bg-slate-50">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold shadow-sm">
                            Create Account
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- New Dept Modal -->
    @if($showNewDeptModal)
        <div class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-base font-bold text-slate-900">Add Department / Unit</h3>
                    <button type="button" wire:click="$set('showNewDeptModal', false)" class="text-slate-400 hover:text-slate-700 text-xl font-bold">×</button>
                </div>

                <form wire:submit="createDepartment" class="space-y-3 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">Department Name *</label>
                        <input type="text" wire:model="deptName" placeholder="e.g. Traffic Management Unit" class="w-full p-2.5 rounded-xl border border-slate-300">
                        @error('deptName') <span class="text-rose-600">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">Department Code *</label>
                        <input type="text" wire:model="deptCode" placeholder="e.g. TMU" class="w-full p-2.5 rounded-xl border border-slate-300 uppercase">
                        @error('deptCode') <span class="text-rose-600">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">Description</label>
                        <textarea wire:model="deptDescription" rows="2" class="w-full p-2.5 rounded-xl border border-slate-300"></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block font-bold text-slate-700 uppercase mb-1">Contact Person</label>
                            <input type="text" wire:model="deptContactPerson" class="w-full p-2.5 rounded-xl border border-slate-300">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 uppercase mb-1">Phone</label>
                            <input type="text" wire:model="deptContactPhone" class="w-full p-2.5 rounded-xl border border-slate-300">
                        </div>
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex justify-end gap-2">
                        <button type="button" wire:click="$set('showNewDeptModal', false)" class="px-4 py-2 rounded-xl border border-slate-300 font-semibold text-slate-600 hover:bg-slate-50">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold shadow-sm">
                            Save Department
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
