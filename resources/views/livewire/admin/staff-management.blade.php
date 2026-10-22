<div class="space-y-6">
    <!-- Feedback Alerts -->
    @if(session()->has('success'))
        <div class="rounded-md border border-emerald-300 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-950/40 p-3 text-xs text-emerald-800 dark:text-emerald-300 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-emerald-600 dark:text-emerald-400 font-bold ml-2">✕</button>
        </div>
    @endif

    @if(session()->has('error'))
        <div class="rounded-md border border-rose-300 dark:border-rose-800 bg-rose-50 dark:bg-rose-950/40 p-3 text-xs text-rose-800 dark:text-rose-300 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-rose-600 dark:text-rose-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span>{{ session('error') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-rose-600 dark:text-rose-400 font-bold ml-2">✕</button>
        </div>
    @endif

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-base sm:text-lg font-bold text-slate-900 dark:text-slate-100">Municipal Operations & Registry</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400">Configure municipal response departments, citizen report categories, and authorized administrators.</p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <button type="button" wire:click="openNewCategoryModal"
                class="px-3 py-1.5 rounded-md border border-teal-700 bg-teal-50 dark:bg-teal-950/40 hover:bg-teal-100 dark:hover:bg-teal-900/40 text-teal-800 dark:text-teal-300 font-semibold text-xs transition">
                + Add Category
            </button>
            <button type="button" wire:click="openNewDeptModal"
                class="px-3 py-1.5 rounded-md border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 font-semibold text-xs transition">
                + Add Department
            </button>
            <button type="button" wire:click="openNewAdminModal"
                class="px-3.5 py-1.5 rounded-md bg-teal-800 hover:bg-teal-900 text-white font-semibold text-xs shadow-xs transition flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>+ New Administrator</span>
            </button>
        </div>
    </div>

    <!-- Category Management Section -->
    <div class="bg-white dark:bg-slate-900 rounded-lg border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
        <div class="p-3.5 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
            <div>
                <h2 class="text-xs font-bold text-slate-900 dark:text-slate-100 uppercase tracking-wider">Report Categories ({{ $categories->count() }})</h2>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">Categories available in the mobile resident app and admin triage queue.</p>
            </div>
            <button type="button" wire:click="openNewCategoryModal" class="text-xs text-teal-700 dark:text-teal-400 hover:underline font-semibold">
                + New Category
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 font-semibold text-[11px]">
                    <tr>
                        <th class="py-2.5 px-3">Category</th>
                        <th class="py-2.5 px-3">Assigned Department</th>
                        <th class="py-2.5 px-3">Default Priority</th>
                        <th class="py-2.5 px-3">Hazard Flag</th>
                        <th class="py-2.5 px-3">Volume</th>
                        <th class="py-2.5 px-3">Status</th>
                        <th class="py-2.5 px-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($categories as $category)
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition">
                            <td class="py-2.5 px-3">
                                <div class="flex items-center gap-2">
                                    <span class="w-3.5 h-3.5 rounded-full shrink-0 border border-black/10" style="background-color: {{ $category->color ?? '#0F766E' }};"></span>
                                    <div>
                                        <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $category->name }}</p>
                                        <p class="text-[11px] font-mono text-slate-400 dark:text-slate-500">{{ $category->slug }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-2.5 px-3 text-slate-600 dark:text-slate-400">
                                @if($category->department)
                                    <span class="px-1.5 py-0.5 rounded text-[11px] font-mono bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                        {{ $category->department->code }}
                                    </span>
                                    <span class="ml-1 text-[11px]">{{ $category->department->name }}</span>
                                @else
                                    <span class="text-slate-400 italic text-[11px]">Unassigned</span>
                                @endif
                            </td>
                            <td class="py-2.5 px-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider
                                    @if($category->default_priority === 'urgent') bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-400 border border-rose-200 dark:border-rose-900
                                    @elseif($category->default_priority === 'high') bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-900
                                    @elseif($category->default_priority === 'low') bg-pink-50 dark:bg-pink-950/40 text-pink-700 dark:text-pink-400 border border-pink-200 dark:border-pink-900
                                    @else bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 @endif">
                                    {{ $category->default_priority }}
                                </span>
                            </td>
                            <td class="py-2.5 px-3">
                                @if($category->is_hazardous)
                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-semibold bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-400 border border-rose-200 dark:border-rose-900">
                                        <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                        Hazard
                                    </span>
                                @else
                                    <span class="text-slate-400 text-[11px]">Standard</span>
                                @endif
                            </td>
                            <td class="py-2.5 px-3 font-semibold text-slate-700 dark:text-slate-300">
                                {{ $category->reports_count }} {{ Str::plural('report', $category->reports_count) }}
                            </td>
                            <td class="py-2.5 px-3">
                                <button type="button" wire:click="toggleCategoryActive({{ $category->id }})"
                                    class="px-2 py-0.5 rounded text-[11px] font-semibold border transition {{ $category->is_active ? 'border-emerald-300 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-300 hover:bg-emerald-100' : 'border-slate-300 dark:border-slate-700 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200' }}">
                                    {{ $category->is_active ? 'Active' : 'Inactive' }}
                                </button>
                            </td>
                            <td class="py-2.5 px-3 text-right space-x-1.5 whitespace-nowrap">
                                <button type="button" wire:click="editCategory({{ $category->id }})"
                                    class="px-2 py-1 rounded text-[11px] font-semibold text-teal-700 dark:text-teal-400 hover:bg-teal-50 dark:hover:bg-teal-950/50 border border-teal-200 dark:border-teal-900 transition">
                                    Edit
                                </button>
                                @if($category->reports_count === 0)
                                    <button type="button" wire:click="deleteCategory({{ $category->id }})"
                                        wire:confirm="Are you sure you want to delete category '{{ $category->name }}'? This cannot be undone."
                                        class="px-2 py-1 rounded text-[11px] font-semibold text-rose-700 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/50 border border-rose-200 dark:border-rose-900 transition">
                                        Delete
                                    </button>
                                @else
                                    <button type="button" disabled
                                        title="Cannot delete because this category has associated reports. Deactivate it instead."
                                        class="px-2 py-1 rounded text-[11px] font-semibold text-slate-400 dark:text-slate-600 border border-slate-200 dark:border-slate-800 cursor-not-allowed">
                                        Delete
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-6 text-center text-slate-400 text-xs">No categories defined yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Admin Accounts Table -->
    <div class="bg-white dark:bg-slate-900 rounded-lg border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
        <div class="p-3.5 border-b border-slate-200 dark:border-slate-800">
            <h2 class="text-xs font-bold text-slate-900 dark:text-slate-100 uppercase tracking-wider">Authorized Barangay Administrators ({{ $adminUsers->count() }})</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 font-semibold text-[11px]">
                    <tr>
                        <th class="py-2 px-3">Administrator</th>
                        <th class="py-2 px-3">Role</th>
                        <th class="py-2 px-3">Contact Phone</th>
                        <th class="py-2 px-3">Barangay</th>
                        <th class="py-2 px-3 text-right">Account Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach($adminUsers as $admin)
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition">
                            <td class="py-2.5 px-3">
                                <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $admin->name }}</p>
                                <span class="text-[11px] text-slate-500 dark:text-slate-400">{{ $admin->email }}</span>
                            </td>
                            <td class="py-2.5 px-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold uppercase bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                    {{ $admin->role }}
                                </span>
                            </td>
                            <td class="py-2.5 px-3 text-slate-600 dark:text-slate-400">
                                {{ $admin->phone ?? 'N/A' }}
                            </td>
                            <td class="py-2.5 px-3 text-slate-600 dark:text-slate-400">
                                {{ $admin->barangay ?? config('community.barangays.0', 'Poblacion I') }}
                            </td>
                            <td class="py-2.5 px-3 text-right">
                                @if($admin->id === auth()->id())
                                    <span class="text-slate-400 dark:text-slate-500 font-medium text-[11px]">Current Session</span>
                                @else
                                    <button type="button" wire:click="toggleUserStatus({{ $admin->id }})"
                                        class="px-2.5 py-1 rounded text-xs font-semibold border transition {{ $admin->is_active ? 'border-emerald-300 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-300 hover:bg-emerald-100' : 'border-rose-300 dark:border-rose-800 bg-rose-50 dark:bg-rose-950/40 text-rose-800 dark:text-rose-300 hover:bg-rose-100' }}">
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
        <h2 class="text-xs font-bold text-slate-900 dark:text-slate-100 uppercase tracking-wider">Municipal Departments & Action Units</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach($departments as $dept)
                <div class="bg-white dark:bg-slate-900 rounded-lg p-3.5 border border-slate-200 dark:border-slate-800 shadow-xs space-y-2 flex flex-col justify-between">
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between">
                            <span class="px-1.5 py-0.5 rounded font-mono font-bold text-[11px] bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                {{ $dept->code }}
                            </span>
                            <span class="text-[11px] font-semibold text-teal-800 dark:text-teal-400">
                                {{ $dept->reports_count }} reports
                            </span>
                        </div>
                        <h3 class="font-bold text-xs text-slate-900 dark:text-slate-100">{{ $dept->name }}</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 line-clamp-2 leading-relaxed">{{ $dept->description }}</p>
                    </div>

                    <div class="pt-2 border-t border-slate-100 dark:border-slate-800 text-xs space-y-0.5 text-slate-600 dark:text-slate-400">
                        @if($dept->contact_person)
                            <p class="truncate"><span class="font-semibold text-slate-700 dark:text-slate-300">Liaison:</span> {{ $dept->contact_person }}</p>
                        @endif
                        @if($dept->contact_phone)
                            <p><span class="font-semibold text-slate-700 dark:text-slate-300">Hotline:</span> {{ $dept->contact_phone }}</p>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Category Modal (Add / Edit) -->
    @if($showCategoryModal)
        <div class="fixed inset-0 z-50 bg-slate-900/60 flex items-center justify-center p-4 overflow-y-auto">
            <div class="bg-white dark:bg-slate-900 rounded-lg max-w-lg w-full p-5 shadow-xl border border-slate-200 dark:border-slate-800 space-y-3.5 my-8">
                <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-2.5">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100">
                        {{ $editingCategoryId ? 'Edit Report Category' : 'Create New Category' }}
                    </h3>
                    <button type="button" wire:click="$set('showCategoryModal', false)" class="text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 text-lg leading-none">✕</button>
                </div>

                <form wire:submit="saveCategory" class="space-y-3 text-xs">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Category Name *</label>
                            <input type="text" wire:model="catName" placeholder="e.g. Drainage Clog"
                                class="w-full p-2 rounded-md border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-teal-700">
                            @error('catName') <span class="text-rose-600 dark:text-rose-400">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Slug (Optional)</label>
                            <input type="text" wire:model="catSlug" placeholder="Auto-generated if empty"
                                class="w-full p-2 rounded-md border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-teal-700">
                            @error('catSlug') <span class="text-rose-600 dark:text-rose-400">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Description</label>
                        <textarea wire:model="catDescription" rows="2" placeholder="Citizen guidance for reporting this issue..."
                            class="w-full p-2 rounded-md border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-teal-700"></textarea>
                        @error('catDescription') <span class="text-rose-600 dark:text-rose-400">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Responsible Department</label>
                            <select wire:model="catDepartmentId"
                                class="w-full p-2 rounded-md border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-teal-700">
                                <option value="">None (Unassigned triage)</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->name }} ({{ $dept->code }})</option>
                                @endforeach
                            </select>
                            @error('catDepartmentId') <span class="text-rose-600 dark:text-rose-400">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Default Priority *</label>
                            <select wire:model="catDefaultPriority"
                                class="w-full p-2 rounded-md border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-teal-700">
                                <option value="low">Low (Soft Pink)</option>
                                <option value="medium">Medium (Neutral)</option>
                                <option value="high">High (Amber)</option>
                                <option value="urgent">Urgent (Coral Red)</option>
                            </select>
                            @error('catDefaultPriority') <span class="text-rose-600 dark:text-rose-400">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Palette Color *</label>
                            <div class="flex items-center gap-2">
                                <input type="color" wire:model="catColor" class="w-9 h-9 p-0.5 rounded border border-slate-300 dark:border-slate-700 cursor-pointer bg-white dark:bg-slate-800">
                                <input type="text" wire:model="catColor" placeholder="#0F766E" class="flex-1 p-2 rounded-md border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 font-mono text-slate-900 dark:text-slate-100">
                            </div>
                            @error('catColor') <span class="text-rose-600 dark:text-rose-400">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">System Icon *</label>
                            <select wire:model="catIcon"
                                class="w-full p-2 rounded-md border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-teal-700">
                                <option value="alert-triangle">Alert Triangle (General / Hazard)</option>
                                <option value="zap">Zap / Flash (Electrical)</option>
                                <option value="droplet">Droplet (Water / Drainage)</option>
                                <option value="truck">Truck (Waste / Sanitation)</option>
                                <option value="shield">Shield (Security / Peace & Order)</option>
                                <option value="construction">Construction (Roads / Infrastructure)</option>
                                <option value="volume-2">Volume / Speaker (Noise / Nuisance)</option>
                                <option value="heart-pulse">Heart Pulse (Health / Emergency)</option>
                            </select>
                            @error('catIcon') <span class="text-rose-600 dark:text-rose-400">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Hazard Flag & Advisory -->
                    <div class="p-3 rounded-md border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/40 space-y-2">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" wire:model.live="catIsHazardous" class="rounded border-slate-300 text-teal-700 focus:ring-teal-700">
                            <span class="font-bold text-slate-800 dark:text-slate-200">Flag as Hazardous Issue</span>
                        </label>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">Requires residents to acknowledge safety warnings prior to submitting photo evidence.</p>

                        @if($catIsHazardous)
                            <div class="pt-2">
                                <label class="block font-bold text-rose-700 dark:text-rose-400 uppercase mb-1">Safety Advisory Notice</label>
                                <textarea wire:model="catSafetyAdvisory" rows="2" placeholder="e.g. Do not approach downed electrical lines. Keep a safe perimeter."
                                    class="w-full p-2 rounded-md border border-rose-300 dark:border-rose-800 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-rose-700"></textarea>
                                @error('catSafetyAdvisory') <span class="text-rose-600 dark:text-rose-400">{{ $message }}</span> @enderror
                            </div>
                        @endif
                    </div>

                    <div class="flex items-center gap-2 pt-1">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" wire:model="catIsActive" class="rounded border-slate-300 text-teal-700 focus:ring-teal-700">
                            <span class="font-semibold text-slate-800 dark:text-slate-200">Active (Visible in Mobile Citizen App)</span>
                        </label>
                    </div>

                    <div class="pt-3 border-t border-slate-200 dark:border-slate-800 flex justify-end gap-2">
                        <button type="button" wire:click="$set('showCategoryModal', false)"
                            class="px-3 py-1.5 rounded-md border border-slate-300 dark:border-slate-700 font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-3.5 py-1.5 rounded-md bg-teal-800 hover:bg-teal-900 text-white font-semibold shadow-xs transition">
                            {{ $editingCategoryId ? 'Save Changes' : 'Create Category' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- New Admin Modal -->
    @if($showNewAdminModal)
        <div class="fixed inset-0 z-50 bg-slate-900/60 flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-900 rounded-lg max-w-md w-full p-5 shadow-lg border border-slate-200 dark:border-slate-800 space-y-3.5">
                <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-2.5">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100">Add Barangay Administrator</h3>
                    <button type="button" wire:click="$set('showNewAdminModal', false)" class="text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 text-lg leading-none">✕</button>
                </div>

                <form wire:submit="createAdmin" class="space-y-3 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Full Name *</label>
                        <input type="text" wire:model="name" class="w-full p-2 rounded-md border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-teal-700">
                        @error('name') <span class="text-rose-600 dark:text-rose-400">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Email Address *</label>
                        <input type="email" wire:model="email" class="w-full p-2 rounded-md border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-teal-700">
                        @error('email') <span class="text-rose-600 dark:text-rose-400">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Contact Phone *</label>
                        <input type="text" wire:model="phone" class="w-full p-2 rounded-md border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-teal-700" placeholder="0917-000-0000">
                        @error('phone') <span class="text-rose-600 dark:text-rose-400">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Password *</label>
                        <input type="password" wire:model="password" class="w-full p-2 rounded-md border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-teal-700" placeholder="Min 8 characters">
                        @error('password') <span class="text-rose-600 dark:text-rose-400">{{ $message }}</span> @enderror
                    </div>

                    <div class="pt-2.5 border-t border-slate-200 dark:border-slate-800 flex justify-end gap-2">
                        <button type="button" wire:click="$set('showNewAdminModal', false)" class="px-3 py-1.5 rounded-md border border-slate-300 dark:border-slate-700 font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800">
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
        <div class="fixed inset-0 z-50 bg-slate-900/60 flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-900 rounded-lg max-w-md w-full p-5 shadow-lg border border-slate-200 dark:border-slate-800 space-y-3.5">
                <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-2.5">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100">Add Department / Action Unit</h3>
                    <button type="button" wire:click="$set('showNewDeptModal', false)" class="text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 text-lg leading-none">✕</button>
                </div>

                <form wire:submit="createDepartment" class="space-y-3 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Department Name *</label>
                        <input type="text" wire:model="deptName" placeholder="e.g. Traffic Management Unit" class="w-full p-2 rounded-md border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-teal-700">
                        @error('deptName') <span class="text-rose-600 dark:text-rose-400">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Department Code *</label>
                        <input type="text" wire:model="deptCode" placeholder="e.g. TMU" class="w-full p-2 rounded-md border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-teal-700 uppercase">
                        @error('deptCode') <span class="text-rose-600 dark:text-rose-400">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Description</label>
                        <textarea wire:model="deptDescription" rows="2" class="w-full p-2 rounded-md border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-teal-700"></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Liaison Contact</label>
                            <input type="text" wire:model="deptContactPerson" class="w-full p-2 rounded-md border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-teal-700">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Hotline Phone</label>
                            <input type="text" wire:model="deptContactPhone" class="w-full p-2 rounded-md border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-teal-700">
                        </div>
                    </div>

                    <div class="pt-2.5 border-t border-slate-200 dark:border-slate-800 flex justify-end gap-2">
                        <button type="button" wire:click="$set('showNewDeptModal', false)" class="px-3 py-1.5 rounded-md border border-slate-300 dark:border-slate-700 font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800">
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
