@props([
    'selectionSets' => [
        [
            'name' => 'asd',
            'label' => 'dsa',
            'items' => [['label' => 'a', 'value' => 1], ['label' => 'b', 'value' => 2]],
        ],
    ],
])

<script>
    // console.log(@json($selectionSets));
    let sxdata = {
        dropdowns: @json($selectionSets),
        getUrlParams(name) {
            const urlParams = new URLSearchParams(window.location.search);
            const values = urlParams.getAll(name + '[]');
            const last_val = values.filter(v => v !== '');
            console.log(last_val);
            return last_val;
        },

        getSelectedItems(dropdown) {
            return this.$refs[dropdown.name] ?
                dropdown.items.filter(item => this.$refs[dropdown.name].selected.includes(item.value)) : [];
        }
    };
    // console.log(sxdata);
</script>
<div class="border-0 border-green-500 rounded-lg flex flex-col" x-data="sxdata">
    <!-- Filters container -->
    <div class="flex flex-col md:flex-row items-start gap-2 border-0 border-red-500">
        <template x-for="dropdown in dropdowns" :key="dropdown.name">
            <div x-data="{
                open: false,
                search: '',
                selected: [],
                init() {
                    // Initialize selected values from URL
                    this.selected = getUrlParams(dropdown.name);
                    console.log('selected', this.selected);
                    // Watch for changes in URL parameters
                    window.addEventListener('popstate', () => {
                        this.selected = getUrlParams(dropdown.name);
                    });
                },
                get filteredItems() {
                    return dropdown.items.filter(item =>
                        item.label.toLowerCase().includes(this.search.toLowerCase())
                    );
                },
                get selectedLabel() {
                    if (this.selected.length === 0) return dropdown.label;
                    return `${dropdown.label}: ${this.selected.length}`;
                }
            }" class="relative" :x-ref="dropdown.name">
                <!-- Hidden inputs for form submission -->
                <template x-for="value in selected" :key="value">
                    <input type="hidden" :name="dropdown.name + '[]'" :value="value" aria-label="dropdown">
                </template>

                <!-- Custom dropdown button -->
                <button type="button" @click="open = !open; $nextTick(() => { if(open) $refs.searchInput?.focus() })"
                    class="inline-flex justify-between w-full rounded md:w-48 px-3 py-2 text-base text-stone-700 bg-gray-50 border border-stone-300 hover:border-stone-400 appearance-none focus:outline-none focus:ring-2 focus:ring-rose-200 focus:border-rose-500">
                    <span x-text="selectedLabel" class="truncate"></span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 flex-shrink-0 text-stone-500"
                        viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M6.293 9.293a1 1 0 011.414 0L10 11.586l2.293-2.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"
                            clip-rule="evenodd" />
                    </svg>
                </button>

                <!-- Dropdown menu -->
                <div x-show="open" @click.away="open = false" x-transition
                    class="absolute z-10 w-full mt-1 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5"
                    style="display: none;">
                    <!-- Search input with clear button -->
                    <div class="relative p-2 border-b">
                        <input x-model="search" x-ref="searchInput" @focus="$el.select()"
                            class="block w-full pl-3 pr-8 py-2 text-gray-800 rounded-md border border-gray-300 focus:outline-none focus:ring-rose-500 focus:border-rose-500"
                            type="text" :placeholder="'Search ' + dropdown.label.toLowerCase()" @click.stop>
                        <!-- Clear button -->
                        <button type="button" @click="search = ''" class="absolute inset-y-0 right-3 flex items-center"
                            x-show="search.length > 0">
                            <svg class="h-5 w-5 text-gray-400 hover:text-rose-600" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                    clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>

                    <!-- Dropdown items -->
                    <div class="py-1 max-h-60 overflow-y-auto">
                        <template x-for="item in filteredItems" :key="item.value">
                            <div @click.stop="selected.includes(item.value) ? selected = selected.filter(i => i !== item.value) : selected.push(item.value)"
                                class="flex items-center px-3 py-2 text-gray-700 hover:bg-rose-100 hover:text-rose-800 cursor-pointer"
                                :class="{ 'bg-rose-100': selected.includes(String(item.value)) }">
                                <input type="checkbox" :checked="selected.includes(String(item.value))"
                                    class="w-4 h-4 text-rose-600 border-gray-300 rounded focus:ring-rose-500 mr-2">
                                <span x-text="item.label" class="truncate"></span>
                            </div>
                        </template>
                        <div x-show="filteredItems.length === 0" class="px-3 py-2 text-gray-500">
                            No results found
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <!-- Selected filters summary -->
    <div class="mt-3 p-3 border border-slate-300 rounded-lg">
        <div class="flex flex-wrap gap-2">
            <template x-for="dropdown in dropdowns" :key="dropdown.name">
                <template x-if="$refs[dropdown.name]">
                    <template x-for="item in getSelectedItems(dropdown)" :key="item.value">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-rose-100 text-rose-800">
                            <span x-text="item.label"></span>
                            <button type="button"
                                @click="$refs[dropdown.name].selected = $refs[dropdown.name].selected.filter(i => i !== item.value)"
                                class="ml-1.5 inline-flex items-center justify-center h-4 w-4 rounded-full hover:bg-rose-200">
                                <svg class="h-2.5 w-2.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                            </button>
                        </span>
                    </template>
                </template>
            </template>
        </div>
    </div>
</div>
