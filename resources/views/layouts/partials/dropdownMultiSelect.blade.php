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
            const params = new URLSearchParams(window.location.search);
            const values = params.getAll(name + '[]');
            return values.length > 0 ? values : [];
        },
        getSelectedItems(dropdown) {
            return this.$refs[dropdown.name] ?
                dropdown.items.filter(item => this.$refs[dropdown.name].selected.includes(item.value)) : [];
        }
    };
    // console.log(sxdata);
</script>

<div class="border-0 border-green-500 rounded-lg flex flex-row" x-data="sxdata">
    <!-- Filters container -->
    <div class="flex flex-col md:flex-row items-start gap-2 border-0 border-red-500">
        <template x-for="dropdown in dropdowns" :key="dropdown.name">
            <div x-data="{
                open: true,
                search: '',
                selected: getUrlParams(dropdown.name),
                get filteredItems() {
                    return dropdown.items.filter(item =>
                        item.label.toLowerCase().includes(this.search.toLowerCase())
                    )
                },
                get selectedLabel() {
                    if (this.selected.length === 0) return dropdown.label;
                    return `${dropdown.label}: ${this.selected.length}`;
                }
            }" class="relative " :x-ref="dropdown.name">
                <!-- Hidden inputs for form submission -->
                <template x-for="value in selected" :key="value">
                    <input type="hidden" :name="dropdown.name + '[]'" :value="value" aria-label="dropdown">
                </template>

                <!-- Custom dropdown button -->
                <button type="button" @click="open = !open; $nextTick(() => { if(open) $refs.searchInput.focus() })"
                    class="inline-flex justify-between w-full rounded md:w-48 px-2 py-2 text-base text-stone-500 bg-gray-50 border border-stone-300 appearance-none focus:outline-none ring-0 focus:ring-2 focus:ring-rose-200 focus:border-rose-500 peer">
                    <span x-text="selectedLabel" class="truncate mx-2"></span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 flex-shrink-0" viewBox="0 0 20 20"
                        fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M6.293 9.293a1 1 0 011.414 0L10 11.586l2.293-2.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"
                            clip-rule="evenodd" />
                    </svg>
                </button>

                <!-- Dropdown menu -->
                <div x-show="open" @click.away="open = false"
                    class="absolute z-10 w-full mt-2 rounded bg-white ring-2 ring-blue-200 border border-teal-500"
                    style="display: none;">
                    <!-- Search input with clear button -->
                    <div class="relative">
                        <input x-model="search" x-ref="searchInput" @focus="$el.select()"
                            class="block w-full px-4 py-2 text-gray-800 rounded-t border-b focus:outline-none"
                            type="text" :placeholder="'Search for a ' + dropdown.label.toLowerCase()" @click.stop>
                        <!-- Clear button -->
                        <button type="button" @click="search = ''"
                            class="absolute inset-y-0 right-2 px-2 flex items-center" x-show="search.length > 0">
                            <svg class="h-4 w-4 text-gray-400 hover:text-rose-600" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                    clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>

                    <!-- Dropdown items -->
                    <div
                        class="rounded-b max-h-60 overflow-y-auto [&::-webkit-scrollbar]:w-2 [&::-webkit-scrollbar-track]:bg-rose-100 [&::-webkit-scrollbar-thumb]:bg-rose-300">
                        <template x-for="item in filteredItems" :key="item.value">
                            <div @click="selected.includes(item.value) ? selected = selected.filter(i => i !== item.value) : selected.push(item.value)"
                                class="block px-4 py-2 text-gray-700 hover:bg-rose-200 hover:text-rose-500 cursor-pointer bg-white w-full"
                                :class="{ 'bg-rose-200': selected.includes(item.value) }">
                                <div class="flex items-center gap-2">
                                    <input type="checkbox" :checked="selected.includes(item.value)"
                                        class="w-4 h-4 border-gray-300 rounded focus:ring-blue-500 flex-shrink-0"
                                        @click.stop>
                                    <span x-text="item.label" class="truncate"></span>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </template>

        <!-- Apply button -->
        {{-- <button type="submit"
            class="w-full md:w-auto inline-flex justify-center font-medium appearance-none border border-rose-700 bg-rose-700 rounded px-8 py-2 text-base text-white hover:bg-rose-800 ring-0 peer">
            Apply Filters
        </button> --}}
    </div>

    <!-- Selected filters summary -->
    <div class=" m-4 border-2 border-slate-300">
        <div class="flex flex-wrap gap-2">
            <template x-for="dropdown in dropdowns" :key="dropdown.name">
                <template x-if="$refs[dropdown.name] !== undefined">
                    <template x-for="item in getSelectedItems(dropdown)" :key="item.value">
                        <span
                            class="inline-flex items-center px-3 py-1 rounded-full text-base bg-rose-100 text-rose-800">
                            <span x-text="item.label"></span>
                            <button type="button"
                                @click="$refs[dropdown.name].selected = $refs[dropdown.name].selected.filter(i => i !== item.value); $refs[dropdown.name].$el.dispatchEvent(new Event('input'))"
                                class="ml-2 inline-flex items-center p-0.5 hover:bg-blue-200 rounded-full">
                                <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
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
