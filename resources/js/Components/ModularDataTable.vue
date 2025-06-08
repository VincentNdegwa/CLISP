<!-- Create a new file with a modular DataTable component -->
<script>
import { ref, watch, computed, onMounted } from 'vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Menu from 'primevue/menu';
import Paginator from 'primevue/paginator';
import Toolbar from 'primevue/toolbar';
import InputText from 'primevue/inputtext';
import Dropdown from 'primevue/dropdown';
import NoRecords from '@/Components/NoRecords.vue';
import Badge from 'primevue/badge';
import Tag from 'primevue/tag';

export default {
    name: 'ModularDataTable',
    props: {
        // Data props
        value: {
            type: Array,
            required: true
        },
        loading: {
            type: Boolean,
            default: false
        },
        dataKey: {
            type: String,
            default: 'id'
        },
        columns: {
            type: Array,
            required: true
        },
        
        // Pagination props
        paginator: {
            type: Boolean,
            default: true
        },
        rows: {
            type: Number,
            default: 10
        },
        totalRecords: {
            type: Number,
            default: 0
        },
        currentPage: {
            type: Number,
            default: 1
        },
        rowsPerPageOptions: {
            type: Array,
            default: () => [10, 20, 50]
        },
        
        // Toolbar props
        showToolbar: {
            type: Boolean,
            default: true
        },
        toolbarTitle: {
            type: String,
            default: ''
        },
        
        // Search props
        showSearch: {
            type: Boolean,
            default: true
        },
        searchPlaceholder: {
            type: String,
            default: 'Search...'
        },
        
        // Filter props
        filters: {
            type: Array,
            default: () => []
        },
        
        // Action props
        startActions: {
            type: Array,
            default: () => []
        },
        endActions: {
            type: Array,
            default: () => []
        },
        rowActions: {
            type: Array,
            default: () => []
        },
        
        // Styling props
        stripedRows: {
            type: Boolean,
            default: true
        },
        rowHover: {
            type: Boolean,
            default: false
        },
        compact: {
            type: Boolean,
            default: true
        },
        
        // Empty state props
        emptyMessage: {
            type: String,
            default: 'No records found'
        },
        
        // Export props
        exportable: {
            type: Boolean,
            default: false
        }
    },
    emits: [
        'update:filters',
        'search',
        'page-change',
        'rows-change',
        'sort',
        'action',
        'row-action',
        'filter-change',
        'export'
    ],
    setup(props, { emit }) {
        const dt = ref(null);
        const menu = ref(null);
        const searchQuery = ref('');
        const selectedRow = ref(null);
        const activeFilters = ref({});
        const isFilterOpen = ref(false);
        
        // Initialize filters
        onMounted(() => {
            props.filters.forEach(filter => {
                activeFilters.value[filter.field] = filter.defaultValue || null;
            });
        });
        
        // Handle search input
        const handleSearch = () => {
            emit('search', searchQuery.value);
        };
        
        // Handle pagination
        const onPageChange = (event) => {
            emit('page-change', event);
        };
        
        const onRowsChange = (rows) => {
            emit('rows-change', rows);
        };
        
        // Handle sorting
        const onSort = (event) => {
            emit('sort', event);
        };
        
        // Handle toolbar actions
        const handleAction = (action) => {
            if (action.command) {
                action.command();
            } else {
                emit('action', action);
            }
        };
        
        // Handle row actions
        const toggleMenu = (event, rowData) => {
            selectedRow.value = rowData;
            menu.value.toggle(event);
        };
        
        const handleRowAction = (action) => {
            if (action.command) {
                action.command(selectedRow.value);
            } else {
                emit('row-action', { action, row: selectedRow.value });
            }
        };
        
        // Handle filter changes
        const handleFilterChange = (field, value) => {
            activeFilters.value[field] = value;
            emit('filter-change', activeFilters.value);
        };
        
        const clearFilters = () => {
            props.filters.forEach(filter => {
                activeFilters.value[filter.field] = null;
            });
            emit('filter-change', activeFilters.value);
        };
        
        const toggleFilterPanel = () => {
            isFilterOpen.value = !isFilterOpen.value;
        };
        
        // Handle export
        const exportData = () => {
            if (dt.value) {
                dt.value.exportCSV();
            }
            emit('export');
        };
        
        // Computed properties
        const hasFilters = computed(() => props.filters && props.filters.length > 0);
        const tableClass = computed(() => {
            return {
                'p-datatable-sm': props.compact,
                'p-datatable-striped': props.stripedRows
            };
        });
        
        const rowActionsModel = computed(() => {
            return props.rowActions.map(action => ({
                label: action.label,
                icon: action.icon,
                command: () => handleRowAction(action)
            }));
        });
        
        const recordsCount = computed(() => {
            return props.value ? props.value.length : 0;
        });
        
        const getNestedValue = (obj, field) => {
            return field.includes('.') 
                ? field.split('.').reduce((obj, key) => obj && obj[key], obj) 
                : obj[field];
        };
        
        return {
            dt,
            menu,
            searchQuery,
            selectedRow,
            activeFilters,
            isFilterOpen,
            handleSearch,
            onPageChange,
            onRowsChange,
            onSort,
            handleAction,
            toggleMenu,
            handleFilterChange,
            clearFilters,
            toggleFilterPanel,
            exportData,
            hasFilters,
            tableClass,
            rowActionsModel,
            recordsCount,
            getNestedValue
        };
    },
    components: {
        DataTable,
        Column,
        Button,
        Menu,
        Paginator,
        Toolbar,
        InputText,
        Dropdown,
        NoRecords,
        Badge,
        Tag
    }
};
</script>

<template>
    <div class="modular-data-table card">
        <!-- Toolbar -->
        <Toolbar v-if="showToolbar" class="mb-4">
            <template #start>
                <div class="flex items-center gap-2">
                    <h3 v-if="toolbarTitle" class="text-lg font-medium text-slate-800 dark:text-slate-200 mr-4">
                        {{ toolbarTitle }}
                    </h3>
                    <div class="flex flex-wrap gap-2">
                        <Button v-for="(action, index) in startActions" :key="'start-action-' + index"
                            :label="action.label" :icon="action.icon" :severity="action.severity || 'secondary'"
                            :size="action.size || 'small'" :outlined="action.outlined" :text="action.text"
                            @click="handleAction(action)" />
                    </div>
                </div>
            </template>

            <template #end>
                <div class="flex flex-wrap gap-2">
                    <!-- Filters -->
                    <div v-if="hasFilters" class="relative">
                        <Button icon="pi pi-filter" size="small" label="Filters" @click="toggleFilterPanel" outlined
                            :class="{ 'p-button-info': isFilterOpen }" />

                        <div v-if="isFilterOpen"
                            class="absolute right-0 top-full mt-2 bg-slate-50 dark:bg-slate-800 rounded-lg shadow-lg border border-slate-200 dark:border-slate-700 p-4 z-50 w-80">
                            <div class="flex justify-between items-center mb-3">
                                <h3 class="font-medium text-slate-900 dark:text-white">Filters</h3>
                                <Button icon="pi pi-times" @click.stop="toggleFilterPanel" text />
                            </div>

                            <div class="space-y-4">
                                <div v-for="(filter, index) in filters" :key="'filter-' + index" class="mb-3">
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                                        {{ filter.label }}
                                    </label>

                                    <!-- Dropdown filter -->
                                    <Dropdown v-if="filter.type === 'dropdown'" 
                                        v-model="activeFilters[filter.field]"
                                        :options="filter.options" 
                                        :optionLabel="filter.optionLabel || 'label'" 
                                        :optionValue="filter.optionValue || 'value'"
                                        :placeholder="filter.placeholder || 'Select option'"
                                        class="w-full"
                                        @change="handleFilterChange(filter.field, activeFilters[filter.field])" />

                                    <!-- Text filter -->
                                    <InputText v-else-if="filter.type === 'text'"
                                        v-model="activeFilters[filter.field]"
                                        :placeholder="filter.placeholder || ''"
                                        class="w-full"
                                        @input="handleFilterChange(filter.field, activeFilters[filter.field])" />

                                    <!-- Date filter -->
                                    <Calendar v-else-if="filter.type === 'date'"
                                        v-model="activeFilters[filter.field]"
                                        :placeholder="filter.placeholder || 'Select date'"
                                        class="w-full"
                                        @date-select="handleFilterChange(filter.field, activeFilters[filter.field])" />

                                    <!-- Number range filter -->
                                    <div v-else-if="filter.type === 'range'" class="flex gap-2">
                                        <InputNumber v-model="activeFilters[filter.field + 'Min']"
                                            :placeholder="filter.minPlaceholder || 'Min'"
                                            class="w-full"
                                            @input="handleFilterChange(filter.field + 'Min', activeFilters[filter.field + 'Min'])" />
                                        <InputNumber v-model="activeFilters[filter.field + 'Max']"
                                            :placeholder="filter.maxPlaceholder || 'Max'"
                                            class="w-full"
                                            @input="handleFilterChange(filter.field + 'Max', activeFilters[filter.field + 'Max'])" />
                                    </div>
                                </div>

                                <div class="flex justify-between pt-2 border-t border-slate-200 dark:border-slate-700">
                                    <Button @click.stop="clearFilters" icon="pi pi-filter-slash" label="Clear Filters"
                                        text />
                                    <Button @click.stop="toggleFilterPanel" label="Apply" size="small" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- End actions -->
                    <Button v-for="(action, index) in endActions" :key="'end-action-' + index" :label="action.label"
                        :icon="action.icon" :severity="action.severity || 'secondary'" :size="action.size || 'small'"
                        :outlined="action.outlined" :text="action.text" @click="handleAction(action)" />

                    <!-- Export button -->
                    <Button v-if="exportable" label="Export" size="small" icon="pi pi-download" severity="info"
                        @click="exportData" />
                </div>
            </template>
        </Toolbar>

        <!-- Search and records count -->
        <div class="flex justify-between flex-column sm:flex-row mb-4">
            <div v-if="showSearch" class="w-full sm:w-64 mb-3 sm:mb-0">
                <span class="p-input-icon-left w-full">
                    <InputText v-model="searchQuery" @input="handleSearch" :placeholder="searchPlaceholder"
                        class="w-full" />
                </span>
            </div>

            <div v-if="value && value.length > 0" class="flex items-center">
                <span class="text-sm text-slate-500 dark:text-slate-400">
                    {{ recordsCount }} records found
                </span>
            </div>
        </div>

        <!-- Click outside handler for filter dropdown -->
        <div v-if="isFilterOpen" class="fixed inset-0 z-40" @click="toggleFilterPanel"></div>

        <!-- Empty state -->
        <NoRecords v-if="!loading && (!value || value.length === 0)" :message="emptyMessage" class="mt-4" />

        <!-- Data table -->
        <DataTable v-if="value && value.length > 0" :value="value" :loading="loading" :dataKey="dataKey"
            responsiveLayout="scroll" :class="tableClass" :rowHover="rowHover" ref="dt" @sort="onSort">
            
            <!-- Dynamic columns -->
            <template v-for="(column, index) in columns" :key="'col-' + index">
                <Column :header="column.header" :field="column.field" :sortable="column.sortable" 
                       :exportable="column.exportable !== false" :style="column.style">
                    <template #body="slotProps">
                        <div v-if="column.template && typeof column.template === 'object'">
                            <!-- If template is a component object -->
                            <component :is="column.template" 
                                      :data="slotProps.data" 
                                      :field="column.field" 
                                      :value="getNestedValue(slotProps.data, column.field)" />
                        </div>
                        <div v-else-if="column.template && typeof column.template === 'function'" 
                             v-html="column.template(getNestedValue(slotProps.data, column.field), slotProps.data)">
                        </div>
                        <template v-else-if="column.format">
                            {{ column.format(getNestedValue(slotProps.data, column.field), slotProps.data) }}
                        </template>
                        <template v-else>
                            {{ getNestedValue(slotProps.data, column.field) }}
                        </template>
                    </template>
                </Column>
            </template>

            <!-- Actions column -->
            <Column v-if="rowActions && rowActions.length > 0" header="Actions" :exportable="false">
                <template #body="slotProps">
                    <Button type="button" severity="secondary" icon="pi pi-ellipsis-v"
                        @click="toggleMenu($event, slotProps.data)" aria-haspopup="true" aria-controls="row_actions_menu" />
                    <Menu ref="menu" id="row_actions_menu" :model="rowActionsModel" :popup="true" />
                </template>
            </Column>

        </DataTable>

        <!-- Paginator -->
        <Paginator v-if="paginator && value && value.length > 0" :rows="rows" :totalRecords="totalRecords"
            :first="(currentPage - 1) * rows" @page="onPageChange" @update:rows="onRowsChange"
            :rowsPerPageOptions="rowsPerPageOptions" class="mt-4" />
    </div>
</template>

<style scoped>
.modular-data-table {
    position: relative;
}
</style>
