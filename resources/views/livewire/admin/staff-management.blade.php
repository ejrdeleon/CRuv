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

    <!-- Header & Action Toolbar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-200 dark:border-slate-800 pb-4">
        <div>
            <h1 class="text-base sm:text-lg font-bold text-slate-900 dark:text-slate-100">Municipal Units, Personnel & Registry</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400">Configure response action units, official personnel records, report categories, and administrative logins.</p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <button type="button" wire:click="openNewDeptModal"
                class="px-3 py-1.5 rounded-md border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 font-semibold text-xs transition">
                + Add Unit
            </button>
            <button type="button" wire:click="openNewPersonnelModal"
                class="px-3 py-1.5 rounded-md border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 font-semibold text-xs transition">
                + Add Personnel
            </button>
            <button type="button" wire:click="openNewCategoryModal"
                class="px-3 py-1.5 rounded-md border border-teal-700 bg-teal-50 dark:bg-teal-950/40 hover:bg-teal-100 dark:hover:bg-teal-900/40 text-teal-800 dark:text-teal-300 font-semibold text-xs transition">
                + Add Category
            </button>
            <button type="button" wire:click="openNewAdminModal"
                class="px-3.5 py-1.5 rounded-md bg-teal-800 hover:bg-teal-900 text-white font-semibold text-xs shadow-xs transition flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>+ New Admin</span>
            </button>
        </div>
    </div>

    <!-- Section 1: Municipal Units (Departments) -->
    <div id="units-section" class="bg-white dark:bg-slate-900 rounded-lg border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
        <div class="p-3.5 border-b border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
            <div>
                <h2 class="text-xs font-bold text-slate-900 dark:text-slate-100 uppercase tracking-wider">Municipal Action Units ({{ $departments->count() }})</h2>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">Departments and responder units handling community issue assignments in Santa Cruz, Laguna.</p>
            </div>
            <button type="button" wire:click="openNewDeptModal"
                class="text-xs text-teal-700 dark:text-teal-400 hover:underline font-semibold">
                + New Unit
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 font-semibold text-[11px]">
                    <tr>
                        <th class="py-2.5 px-3">Code</th>
                        <th class="py-2.5 px-3">Unit Name</th>
                        <th class="py-2.5 px-3">Liaison / Officer</th>
                        <th class="py-2.5 px-3">Hotline / Contact</th>
                        <th class="py-2.5 px-3">Personnel</th>
                        <th class="py-2.5 px-3">Open Reports</th>
                        <th class="py-2.5 px-3">Status</th>
                        <th class="py-2.5 px-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($departments as $dept)
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition">
                            <td class="py-2.5 px-3">
                                <span class="px-1.5 py-0.5 rounded font-mono font-bold text-[11px] bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                    {{ $dept->code }}
                                </span>
                            </td>
                            <td class="py-2.5 px-3">
                                <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $dept->name }}</p>
                                @if($dept->description)
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400 line-clamp-1">{{ $dept->description }}</p>
                                @endif
                            </td>
                            <td class="py-2.5 px-3 text-slate-700 dark:text-slate-300">
                                {{ $dept->contact_person ?: '—' }}
                            </td>
                            <td class="py-2.5 px-3 text-slate-600 dark:text-slate-400">
                                {{ $dept->contact_phone ?: '—' }}
                            </td>
                            <td class="py-2.5 px-3 font-semibold text-slate-700 dark:text-slate-300">
                                <button type="button" wire:click="$set('personnelFilterDeptId', {{ $dept->id }})" class="text-teal-700 dark:text-teal-400 hover:underline">
                                    {{ $dept->personnel_count }} personnel
                                </button>
                            </td>
                            <td class="py-2.5 px-3 font-semibold text-slate-700 dark:text-slate-300">
                                {{ $dept->reports_count }}
                            </td>
                            <td class="py-2.5 px-3">
                                <button type="button" wire:click="toggleDepartmentStatus({{ $dept->id }})"
                                    class="px-2 py-0.5 rounded text-[11px] font-semibold border transition {{ $dept->is_active ? 'border-emerald-300 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-300 hover:bg-emerald-100' : 'border-slate-300 dark:border-slate-700 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200' }}">
                                    {{ $dept->is_active ? 'Active' : 'Inactive' }}
                                </button>
                            </td>
                            <td class="py-2.5 px-3 text-right space-x-1.5 whitespace-nowrap">
                                <button type="button" wire:click="openNewPersonnelModal({{ $dept->id }})"
                                    title="Add personnel to this unit"
                                    class="px-2 py-1 rounded text-[11px] font-semibold text-teal-700 dark:text-teal-400 hover:bg-teal-50 dark:hover:bg-teal-950/50 border border-teal-200 dark:border-teal-900 transition">
                                    + Personnel
                                </button>
                                <button type="button" wire:click="editDepartment({{ $dept->id }})"
                                    class="px-2 py-1 rounded text-[11px] font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-300 dark:border-slate-700 transition">
                                    Edit
                                </button>
                                @if($dept->reports_count === 0 && $dept->personnel_count === 0)
                                    <button type="button" wire:click="deleteDepartment({{ $dept->id }})"
                                        wire:confirm="Are you sure you want to delete unit '{{ $dept->name }}'? This action cannot be undone."
                                        class="px-2 py-1 rounded text-[11px] font-semibold text-rose-700 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/50 border border-rose-200 dark:border-rose-900 transition">
                                        Delete
                                    </button>
                                @else
                                    <button type="button" disabled
                                        title="Unit is referenced by reports or personnel. Deactivate it instead."
                                        class="px-2 py-1 rounded text-[11px] font-semibold text-slate-400 dark:text-slate-600 border border-slate-200 dark:border-slate-800 cursor-not-allowed">
                                        Delete
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-6 text-center text-slate-400 text-xs">No municipal units configured yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Section 2: Unit Personnel Records -->
    <div id="personnel-section" class="bg-white dark:bg-slate-900 rounded-lg border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
        <div class="p-3.5 border-b border-slate-200 dark:border-slate-800 flex flex-col md:flex-row md:items-center justify-between gap-3">
            <div>
                <h2 class="text-xs font-bold text-slate-900 dark:text-slate-100 uppercase tracking-wider">Unit Personnel Registry ({{ $personnelList->count() }})</h2>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">Personnel records eligible for triage assignment. Personnel belong to municipal action units; they are not administrative logins.</p>
            </div>

            <!-- Search and Filter Bar -->
            <div class="flex items-center gap-2 flex-wrap">
                <select wire:model.live="personnelFilterDeptId" class="py-1.5 px-2.5 rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-white text-xs">
                    <option value="">All Municipal Units</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}">{{ $dept->code }} — {{ $dept->name }}</option>
                    @endforeach
                </select>

                <div class="relative">
                    <input type="text" wire:model.live.debounce.300ms="personnelSearch" placeholder="Search personnel..."
                        class="py-1.5 pl-7 pr-2.5 rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-white text-xs w-44">
                    <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>

                <button type="button" wire:click="openNewPersonnelModal"
                    class="px-3 py-1.5 rounded-md bg-teal-800 hover:bg-teal-900 text-white font-semibold text-xs shadow-xs transition inline-flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Add Personnel</span>
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 font-semibold text-[11px]">
                    <tr>
                        <th class="py-2.5 px-3">Name</th>
                        <th class="py-2.5 px-3">Unit / Department</th>
                        <th class="py-2.5 px-3">Designated Position</th>
                        <th class="py-2.5 px-3">Contact Number</th>
                        <th class="py-2.5 px-3">Official Email</th>
                        <th class="py-2.5 px-3">Assigned Reports</th>
                        <th class="py-2.5 px-3">Active Status</th>
                        <th class="py-2.5 px-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($personnelList as $person)
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition">
                            <td class="py-2.5 px-3">
                                <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $person->name }}</p>
                            </td>
                            <td class="py-2.5 px-3">
                                @if($person->department)
                                    <span class="px-1.5 py-0.5 rounded font-mono text-[11px] bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                        {{ $person->department->code }}
                                    </span>
                                    <span class="ml-1 text-[11px] text-slate-600 dark:text-slate-400">{{ $person->department->name }}</span>
                                @else
                                    <span class="text-slate-400 italic">None</span>
                                @endif
                            </td>
                            <td class="py-2.5 px-3 text-slate-700 dark:text-slate-300 font-medium">
                                {{ $person->position }}
                            </td>
                            <td class="py-2.5 px-3 text-slate-600 dark:text-slate-400 font-mono text-[11px]">
                                {{ $person->contact_number }}
                            </td>
                            <td class="py-2.5 px-3 text-slate-500 dark:text-slate-400 text-[11px]">
                                {{ $person->email ?: '—' }}
                            </td>
                            <td class="py-2.5 px-3 font-semibold text-slate-700 dark:text-slate-300">
                                {{ $person->reports_count }}
                            </td>
                            <td class="py-2.5 px-3">
                                <button type="button" wire:click="togglePersonnelStatus({{ $person->id }})"
                                    class="px-2 py-0.5 rounded text-[11px] font-semibold border transition {{ $person->is_active ? 'border-emerald-300 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-300 hover:bg-emerald-100' : 'border-slate-300 dark:border-slate-700 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200' }}">
                                    {{ $person->is_active ? 'Active' : 'Inactive' }}
                                </button>
                            </td>
                            <td class="py-2.5 px-3 text-right space-x-1.5 whitespace-nowrap">
                                <button type="button" wire:click="editPersonnel({{ $person->id }})"
                                    class="px-2 py-1 rounded text-[11px] font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-300 dark:border-slate-700 transition">
                                    Edit
                                </button>
                                @if($person->reports_count === 0)
                                    <button type="button" wire:click="deletePersonnel({{ $person->id }})"
                                        wire:confirm="Are you sure you want to delete personnel record '{{ $person->name }}'?"
                                        class="px-2 py-1 rounded text-[11px] font-semibold text-rose-700 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/50 border border-rose-200 dark:border-rose-900 transition">
                                        Delete
                                    </button>
                                @else
                                    <button type="button" disabled
                                        title="Personnel has assigned report history. Deactivate them instead."
                                        class="px-2 py-1 rounded text-[11px] font-semibold text-slate-400 dark:text-slate-600 border border-slate-200 dark:border-slate-800 cursor-not-allowed">
                                        Delete
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-6 text-center text-slate-400 text-xs">No personnel records found for the current filter.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Section 3: Report Categories -->
    <div id="categories-section" class="bg-white dark:bg-slate-900 rounded-lg border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
        <div class="p-3.5 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
            <div>
                <h2 class="text-xs font-bold text-slate-900 dark:text-slate-100 uppercase tracking-wider">Report Categories ({{ $categories->count() }})</h2>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">Categories available in the mobile resident app with default municipal unit routing suggestions.</p>
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
                        <th class="py-2.5 px-3">Default Unit Suggestion</th>
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
                                {{ $category->reports_count }}
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

    <!-- Section 4: Authorized Administrators -->
    <div id="admins-section" class="bg-white dark:bg-slate-900 rounded-lg border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
        <div class="p-3.5 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
            <div>
                <h2 class="text-xs font-bold text-slate-900 dark:text-slate-100 uppercase tracking-wider">Authorized Administrative Logins ({{ $adminUsers->count() }})</h2>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">Users authorized to sign into the municipal admin web portal. Assignments are handled by municipal personnel, not admin logins.</p>
            </div>
            <button type="button" wire:click="openNewAdminModal"
                class="text-xs text-teal-700 dark:text-teal-400 hover:underline font-semibold">
                + New Admin Login
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 font-semibold text-[11px]">
                    <tr>
                        <th class="py-2.5 px-3">Administrator</th>
                        <th class="py-2.5 px-3">Role</th>
                        <th class="py-2.5 px-3">Contact Phone</th>
                        <th class="py-2.5 px-3">Barangay</th>
                        <th class="py-2.5 px-3 text-right">Account Status</th>
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

    <!-- Unit (Department) Modal (Add / Edit) -->
    @if($showDeptModal)
        <div class="fixed inset-0 z-50 bg-slate-900/60 flex items-center justify-center p-4 overflow-y-auto">
            <div class="bg-white dark:bg-slate-900 rounded-lg max-w-lg w-full p-5 shadow-md border border-slate-200 dark:border-slate-800 space-y-3.5 my-8">
                <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-2.5">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100">
                        {{ $editingDeptId ? 'Edit Municipal Unit' : 'Add Municipal Action Unit' }}
                    </h3>
                    <button type="button" wire:click="$set('showDeptModal', false)" class="text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 text-lg leading-none">✕</button>
                </div>

                <form wire:submit="saveDepartment" class="space-y-3 text-xs">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="sm:col-span-2">
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Unit Full Name *</label>
                            <input type="text" wire:model="deptName" placeholder="e.g. Santa Cruz Municipal Health Office" class="w-full p-2 rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-white text-xs">
                            @error('deptName') <span class="text-rose-600 text-[11px]">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Unit Code *</label>
                            <input type="text" wire:model="deptCode" placeholder="e.g. MHO" class="w-full p-2 rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-white text-xs uppercase">
                            @error('deptCode') <span class="text-rose-600 text-[11px]">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Description / Responsibilities</label>
                        <textarea wire:model="deptDescription" rows="2" placeholder="Brief outline of unit operations..." class="w-full p-2 rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-white text-xs"></textarea>
                        @error('deptDescription') <span class="text-rose-600 text-[11px]">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Lead Liaison</label>
                            <input type="text" wire:model="deptContactPerson" placeholder="Officer name" class="w-full p-2 rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-white text-xs">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Contact Hotline</label>
                            <input type="text" wire:model="deptContactPhone" placeholder="09XX-XXX-XXXX" class="w-full p-2 rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-white text-xs">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Official Email</label>
                            <input type="email" wire:model="deptContactEmail" placeholder="unit@santacruz.gov.ph" class="w-full p-2 rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-white text-xs">
                        </div>
                    </div>

                    <div class="flex items-center gap-2 pt-1">
                        <input type="checkbox" id="deptIsActive" wire:model="deptIsActive" class="rounded border-slate-300 text-teal-700 focus:ring-teal-700">
                        <label for="deptIsActive" class="font-semibold text-slate-700 dark:text-slate-300">Unit is Active and Available for Assignments</label>
                    </div>

                    <div class="flex justify-end gap-2 pt-2 border-t border-slate-200 dark:border-slate-800">
                        <button type="button" wire:click="$set('showDeptModal', false)" class="px-3 py-1.5 rounded border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 font-semibold hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-1.5 rounded bg-teal-800 hover:bg-teal-900 text-white font-semibold shadow-xs transition">
                            {{ $editingDeptId ? 'Save Changes' : 'Create Unit' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Personnel Modal (Add / Edit) -->
    @if($showPersonnelModal)
        <div class="fixed inset-0 z-50 bg-slate-900/60 flex items-center justify-center p-4 overflow-y-auto">
            <div class="bg-white dark:bg-slate-900 rounded-lg max-w-lg w-full p-5 shadow-md border border-slate-200 dark:border-slate-800 space-y-3.5 my-8">
                <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-2.5">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100">
                        {{ $editingPersonnelId ? 'Edit Personnel Record' : 'Add Unit Personnel Record' }}
                    </h3>
                    <button type="button" wire:click="$set('showPersonnelModal', false)" class="text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 text-lg leading-none">✕</button>
                </div>

                <form wire:submit="savePersonnel" class="space-y-3 text-xs">
                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Assigned Action Unit *</label>
                        <select wire:model="personnelDeptId" class="w-full p-2 rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-white text-xs">
                            <option value="">Select Unit</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->code }} — {{ $dept->name }}</option>
                            @endforeach
                        </select>
                        @error('personnelDeptId') <span class="text-rose-600 text-[11px]">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Full Name *</label>
                            <input type="text" wire:model="personnelName" placeholder="e.g. Juan Dela Cruz" class="w-full p-2 rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-white text-xs">
                            @error('personnelName') <span class="text-rose-600 text-[11px]">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Designated Position *</label>
                            <input type="text" wire:model="personnelPosition" placeholder="e.g. Lead Field Inspector" class="w-full p-2 rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-white text-xs">
                            @error('personnelPosition') <span class="text-rose-600 text-[11px]">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Contact Number *</label>
                            <input type="text" wire:model="personnelContactNumber" placeholder="09XX-XXX-XXXX" class="w-full p-2 rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-white text-xs">
                            @error('personnelContactNumber') <span class="text-rose-600 text-[11px]">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Official Email (Optional)</label>
                            <input type="email" wire:model="personnelEmail" placeholder="officer@santacruz.gov.ph" class="w-full p-2 rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-white text-xs">
                            @error('personnelEmail') <span class="text-rose-600 text-[11px]">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="flex items-center gap-2 pt-1">
                        <input type="checkbox" id="personnelIsActive" wire:model="personnelIsActive" class="rounded border-slate-300 text-teal-700 focus:ring-teal-700">
                        <label for="personnelIsActive" class="font-semibold text-slate-700 dark:text-slate-300">Active and Eligible for Assignments</label>
                    </div>

                    <div class="flex justify-end gap-2 pt-2 border-t border-slate-200 dark:border-slate-800">
                        <button type="button" wire:click="$set('showPersonnelModal', false)" class="px-3 py-1.5 rounded border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 font-semibold hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-1.5 rounded bg-teal-800 hover:bg-teal-900 text-white font-semibold shadow-xs transition">
                            {{ $editingPersonnelId ? 'Save Changes' : 'Register Personnel' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Category Modal (Add / Edit) -->
    @if($showCategoryModal)
        <div class="fixed inset-0 z-50 bg-slate-900/60 flex items-center justify-center p-4 overflow-y-auto">
            <div class="bg-white dark:bg-slate-900 rounded-lg max-w-lg w-full p-5 shadow-md border border-slate-200 dark:border-slate-800 space-y-3.5 my-8">
                <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-2.5">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100">
                        {{ $editingCategoryId ? 'Edit Report Category' : 'Create New Category' }}
                    </h3>
                    <button type="button" wire:click="$set('showCategoryModal', false)" class="text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 text-lg leading-none">✕</button>
                </div>

                <form wire:submit="saveCategory" class="space-y-3 text-xs">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Category Name *</label>
                            <input type="text" wire:model="catName" placeholder="e.g. Damaged Roads" class="w-full p-2 rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-white text-xs">
                            @error('catName') <span class="text-rose-600 text-[11px]">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">URL / API Slug</label>
                            <input type="text" wire:model="catSlug" placeholder="Auto-generated if blank" class="w-full p-2 rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-white text-xs font-mono">
                            @error('catSlug') <span class="text-rose-600 text-[11px]">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Description</label>
                        <textarea wire:model="catDescription" rows="2" placeholder="Citizen guidance on when to pick this category..." class="w-full p-2 rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-white text-xs"></textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Default Unit</label>
                            <select wire:model="catDepartmentId" class="w-full p-2 rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-white text-xs">
                                <option value="">Unassigned</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->code }} — {{ $dept->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Default Priority *</label>
                            <select wire:model="catDefaultPriority" class="w-full p-2 rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-white text-xs">
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Map Color *</label>
                            <div class="flex items-center gap-2">
                                <input type="color" wire:model="catColor" class="w-8 h-8 rounded border border-slate-300 cursor-pointer">
                                <input type="text" wire:model="catColor" class="w-full p-2 rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-white text-xs font-mono">
                            </div>
                        </div>
                    </div>

                    <div class="p-2.5 rounded bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-900/50 space-y-2">
                        <div class="flex items-center gap-2">
                            <input type="checkbox" id="catIsHazardous" wire:model.live="catIsHazardous" class="rounded border-amber-300 text-amber-600 focus:ring-amber-500">
                            <label for="catIsHazardous" class="font-bold text-amber-900 dark:text-amber-300 text-xs">Public Hazard Flag (Requires safety disclaimer prompt)</label>
                        </div>
                        @if($catIsHazardous)
                            <div>
                                <label class="block font-semibold text-amber-900 dark:text-amber-300 text-[11px] mb-1">Safety Advisory Message</label>
                                <textarea wire:model="catSafetyAdvisory" rows="2" placeholder="e.g. Keep at least 10 meters distance from downed power cables..." class="w-full p-2 rounded border border-amber-300 dark:border-amber-800 bg-white dark:bg-slate-950 text-slate-900 dark:text-white text-xs"></textarea>
                            </div>
                        @endif
                    </div>

                    <div class="flex items-center gap-2 pt-1">
                        <input type="checkbox" id="catIsActive" wire:model="catIsActive" class="rounded border-slate-300 text-teal-700 focus:ring-teal-700">
                        <label for="catIsActive" class="font-semibold text-slate-700 dark:text-slate-300">Category is Active and Available to Citizens</label>
                    </div>

                    <div class="flex justify-end gap-2 pt-2 border-t border-slate-200 dark:border-slate-800">
                        <button type="button" wire:click="$set('showCategoryModal', false)" class="px-3 py-1.5 rounded border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 font-semibold hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-1.5 rounded bg-teal-800 hover:bg-teal-900 text-white font-semibold shadow-xs transition">
                            {{ $editingCategoryId ? 'Save Changes' : 'Create Category' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Admin Account Modal -->
    @if($showNewAdminModal)
        <div class="fixed inset-0 z-50 bg-slate-900/60 flex items-center justify-center p-4 overflow-y-auto">
            <div class="bg-white dark:bg-slate-900 rounded-lg max-w-md w-full p-5 shadow-md border border-slate-200 dark:border-slate-800 space-y-3.5 my-8">
                <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-2.5">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100">Create Administrator Login</h3>
                    <button type="button" wire:click="$set('showNewAdminModal', false)" class="text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 text-lg leading-none">✕</button>
                </div>

                <form wire:submit="createAdmin" class="space-y-3 text-xs">
                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Full Name *</label>
                        <input type="text" wire:model="name" placeholder="Hon. Captain Juan Dela Cruz" class="w-full p-2 rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-white text-xs">
                        @error('name') <span class="text-rose-600 text-[11px]">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Email Address *</label>
                        <input type="email" wire:model="email" placeholder="admin@barangay.gov.ph" class="w-full p-2 rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-white text-xs">
                        @error('email') <span class="text-rose-600 text-[11px]">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Contact Phone *</label>
                        <input type="text" wire:model="phone" placeholder="0917-000-0000" class="w-full p-2 rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-white text-xs">
                        @error('phone') <span class="text-rose-600 text-[11px]">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Temporary Password *</label>
                        <input type="password" wire:model="password" placeholder="Minimum 8 characters" class="w-full p-2 rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-white text-xs">
                        @error('password') <span class="text-rose-600 text-[11px]">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex justify-end gap-2 pt-2 border-t border-slate-200 dark:border-slate-800">
                        <button type="button" wire:click="$set('showNewAdminModal', false)" class="px-3 py-1.5 rounded border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 font-semibold hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-1.5 rounded bg-teal-800 hover:bg-teal-900 text-white font-semibold shadow-xs transition">
                            Create Administrator
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
