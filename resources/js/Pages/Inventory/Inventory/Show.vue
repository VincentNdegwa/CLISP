<template>
  <AuthenticatedLayout>
    <Head title="Inventory Details" />

    <div>
<!--      &lt;!&ndash; Notifications &ndash;&gt;-->
<!--      <Message v-if="error" severity="error" :closable="true" @close="clearError" class="mb-4">-->
<!--        {{ error }}-->
<!--      </Message>-->

<!--      <Message v-if="success" severity="success" :closable="true" @close="clearSuccess" class="mb-4">-->
<!--        {{ success }}-->
<!--      </Message>-->

      <AlertNotification
            :open="error||success"
            :message="error||success"
            :status="error?'error': success?'success':'error'"
        />

      <!-- Main content -->
      <div class="mb-6">
        <Toolbar class="mb-4">

          <template #end>
            <div class="flex flex-wrap gap-2">
              <Button
                v-if="inventory"
                icon="pi pi-plus"
                label="Adjust Quantity"
                severity="secondary"
                size="small"
                @click="showAdjustQuantity = true"
              />
              <Button
                v-if="inventory"
                icon="pi pi-box"
                label="Manage Batches"
                severity="secondary"
                size="small"
                @click="showProcessBatch = true"
              />
            </div>
          </template>
        </Toolbar>

        <LoadingUI v-if="loading" />
        <div v-else-if="inventory" class="flex flex-col space-y-6">

          <Card class="p-2" >
            <template #header>
              <div class="flex justify-between items-center w-full">
                <div>
                  <h3 class="text-xl font-medium">Inventory Details</h3>
                  <p class="text-sm text-slate-500 dark:text-slate-400">Item information and current status</p>
                </div>
              </div>
            </template>

            <template #content>
              <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                <div class="col-span-1">
                  <div class="text-sm font-medium text-slate-500 dark:text-slate-400">Item Name</div>
                  <div class="mt-1 text-lg font-semibold">{{ inventory.item?.item_name || 'N/A' }}</div>
                </div>
                <div class="col-span-1">
                  <div class="text-sm font-medium text-slate-500 dark:text-slate-400">SKU</div>
                  <div class="mt-1 text-lg font-semibold">{{ inventory.item?.sku || 'N/A' }}</div>
                </div>
                <div class="col-span-1">
                  <div class="text-sm font-medium text-slate-500 dark:text-slate-400">Status</div>
                  <div class="mt-1">
                    <Tag :severity="getStatusSeverity(inventory.status)" :value="getStatusText(inventory.status)" />
                  </div>
                </div>
                <div class="col-span-1">
                  <div class="text-sm font-medium text-slate-500 dark:text-slate-400">Quantity</div>
                  <div class="mt-1 text-lg font-semibold">{{ inventory.quantity }}</div>
                </div>
                <div class="col-span-1">
                  <div class="text-sm font-medium text-slate-500 dark:text-slate-400">Warehouse</div>
                  <div class="mt-1 text-lg font-semibold">{{ inventory.warehouse?.name || 'N/A' }}</div>
                </div>
                <div class="col-span-1">
                  <div class="text-sm font-medium text-slate-500 dark:text-slate-400">Bin Location</div>
                  <div class="mt-1 text-lg font-semibold">{{ inventory.bin_location?.name || 'N/A' }}</div>
                </div>
                <div class="col-span-1">
                  <div class="text-sm font-medium text-slate-500 dark:text-slate-400">Min Stock Level</div>
                  <div class="mt-1 text-lg font-semibold">{{ inventory.min_stock_level || 'N/A' }}</div>
                </div>
                <div class="col-span-1">
                  <div class="text-sm font-medium text-slate-500 dark:text-slate-400">Max Stock Level</div>
                  <div class="mt-1 text-lg font-semibold">{{ inventory.max_stock_level || 'N/A' }}</div>
                </div>
                <div class="col-span-1">
                  <div class="text-sm font-medium text-slate-500 dark:text-slate-400">Reorder Point</div>
                  <div class="mt-1 text-lg font-semibold">{{ inventory.reorder_point || 'N/A' }}</div>
                </div>
                <div class="col-span-3">
                  <div class="text-sm font-medium text-slate-500 dark:text-slate-400">Notes</div>
                  <div class="mt-1 text-md">{{ inventory.notes || 'No notes available' }}</div>
                </div>
              </div>
            </template>
          </Card>

          <!-- Batch information card -->
          <Card class="p-2" >
            <template #header>
              <div class="flex justify-between items-center w-full">
                <div>
                  <h3 class="text-xl font-medium">Batch Information</h3>
                  <p class="text-sm text-slate-500 dark:text-slate-400">Batch details and expiry information</p>
                </div>
              </div>
            </template>

            <template #content>
              <div v-if="inventory.batches && inventory.batches.length > 0">
                <DataTable :value="inventory.batches" stripedRows responsiveLayout="scroll">
                  <Column field="batch_number" header="Batch #">
                    <template #body="{ data }">{{ data.batch_number || 'N/A' }}</template>
                  </Column>
                  <Column field="lot_number" header="Lot #">
                    <template #body="{ data }">{{ data.lot_number || 'N/A' }}</template>
                  </Column>
                  <Column field="quantity" header="Quantity" />
                  <Column field="status" header="Status">
                    <template #body="{ data }">
                      <Tag :severity="getBatchStatusSeverity(data.status)" :value="getBatchStatusText(data.status)" />
                    </template>
                  </Column>
                  <Column field="expiry_date" header="Expiry Date">
                    <template #body="{ data }">{{ formatDate(data.expiry_date) }}</template>
                  </Column>
                  <Column header="Actions">
                    <template #body="{ data }">
                      <Button icon="pi pi-eye" text severity="info" @click="viewBatchDetails(data)" />
                    </template>
                  </Column>
                </DataTable>
              </div>
              <div v-else class="text-center py-6">
                <i class="pi pi-box text-4xl text-slate-300 dark:text-slate-600 mb-2"></i>
                <p class="text-slate-500 dark:text-slate-400">No batches available for this inventory item</p>
                <Button
                  label="Create New Batch"
                  icon="pi pi-plus"
                  @click="showProcessBatch = true"
                  class="mt-3"
                  size="small"
                />
              </div>
            </template>
          </Card>

          <!-- Recent stock movements card -->
          <Card class="p-2" >
            <template #header>
              <div class="flex justify-between items-center w-full">
                <div>
                  <h3 class="text-xl font-medium">Recent Stock Movements</h3>
                  <p class="text-sm text-slate-500 dark:text-slate-400">Latest inventory transactions</p>
                </div>
              </div>
            </template>

            <template #content>
              <div v-if="inventory.stock_movements && inventory.stock_movements.length > 0">
                <DataTable :value="inventory.stock_movements" stripedRows responsiveLayout="scroll">
                  <Column field="created_at" header="Date">
                    <template #body="{ data }">{{ formatDate(data.created_at) }}</template>
                  </Column>
                  <Column field="movement_type" header="Type">
                    <template #body="{ data }">
                      <Tag :severity="getMovementTypeSeverity(data.movement_type)" :value="getMovementTypeText(data.movement_type)" />
                    </template>
                  </Column>
                  <Column field="quantity" header="Quantity">
                    <template #body="{ data }">
                      <span :class="{'text-green-600 dark:text-green-400': data.movement_type === 'in', 'text-red-600 dark:text-red-400': data.movement_type === 'out'}">
                        {{ data.movement_type === 'in' ? '+' : '-' }}{{ data.quantity }}
                      </span>
                    </template>
                  </Column>
                  <Column field="reference_type" header="Reference">
                    <template #body="{ data }">{{ data.reference_type || 'N/A' }}</template>
                  </Column>
                  <Column field="notes" header="Notes">
                    <template #body="{ data }">{{ data.notes || 'N/A' }}</template>
                  </Column>
                </DataTable>
              </div>
              <div v-else class="text-center py-6">
                <i class="pi pi-history text-4xl text-slate-300 dark:text-slate-600 mb-2"></i>
                <p class="text-slate-500 dark:text-slate-400">No stock movements recorded</p>
                <p class="text-sm text-slate-400 dark:text-slate-500 mt-1">Stock movements will appear here as inventory changes occur</p>
              </div>
            </template>
          </Card>
        </div>

        <!-- Not found state -->
        <div v-else-if="!loading" class="bg-white dark:bg-slate-800 rounded-lg shadow dark:shadow-slate-700/30 p-6">
          <div class="text-center">
            <i class="pi pi-exclamation-circle text-5xl text-slate-400 dark:text-slate-500 mb-4"></i>
            <h3 class="text-xl font-medium mb-2">No inventory found</h3>
            <p class="text-slate-500 dark:text-slate-400 mb-6">The requested inventory item could not be found.</p>

            <Button
              label="Go Back to Inventory"
              icon="pi pi-arrow-left"
              @click="$inertia.visit(route('inventory.index'))"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Modals -->
    <Modal :show="showAdjustQuantity" @close="showAdjustQuantity=false">
      <AdjustQuantity
        :data="inventory"
        @close="showAdjustQuantity = false"
        @success="handleAdjustmentSuccess"
      />
    </Modal>

    <Modal :show="showProcessBatch" @close="showProcessBatch=false">
      <BatchManager
        :data="inventory"
        @close="showProcessBatch = false"
        @success="handleBatchSuccess"
      />
    </Modal>

    <Dialog v-model:visible="showBatchDetails" modal header="Batch Details" :style="{ width: '450px' }">
      <div v-if="selectedBatch" class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
          <div>
            <p class="text-sm text-slate-500 dark:text-slate-400">Batch Number</p>
            <p class="font-medium">{{ selectedBatch.batch_number || 'N/A' }}</p>
          </div>
          <div>
            <p class="text-sm text-slate-500 dark:text-slate-400">Lot Number</p>
            <p class="font-medium">{{ selectedBatch.lot_number || 'N/A' }}</p>
          </div>
          <div>
            <p class="text-sm text-slate-500 dark:text-slate-400">Quantity</p>
            <p class="font-medium">{{ selectedBatch.quantity }}</p>
          </div>
          <div>
            <p class="text-sm text-slate-500 dark:text-slate-400">Status</p>
            <Tag :severity="getBatchStatusSeverity(selectedBatch.status)" :value="getBatchStatusText(selectedBatch.status)" />
          </div>
          <div>
            <p class="text-sm text-slate-500 dark:text-slate-400">Manufacturing Date</p>
            <p class="font-medium">{{ formatDate(selectedBatch.manufacturing_date) }}</p>
          </div>
          <div>
            <p class="text-sm text-slate-500 dark:text-slate-400">Expiry Date</p>
            <p class="font-medium">{{ formatDate(selectedBatch.expiry_date) }}</p>
          </div>
          <div>
            <p class="text-sm text-slate-500 dark:text-slate-400">Cost Price</p>
            <p class="font-medium">{{ selectedBatch.cost_price ? formatCurrency(selectedBatch.cost_price) : 'N/A' }}</p>
          </div>
          <div>
            <p class="text-sm text-slate-500 dark:text-slate-400">Received Date</p>
            <p class="font-medium">{{ formatDate(selectedBatch.received_date) }}</p>
          </div>
        </div>
        <div v-if="selectedBatch.days_until_expiry !== null" class="mt-4">
          <p class="text-sm text-slate-500 dark:text-slate-400">Days Until Expiry</p>
          <p class="font-medium" :class="{
            'text-red-600 dark:text-red-400': selectedBatch.days_until_expiry <= 0,
            'text-yellow-600 dark:text-yellow-400': selectedBatch.days_until_expiry > 0 && selectedBatch.days_until_expiry <= 30,
            'text-green-600 dark:text-green-400': selectedBatch.days_until_expiry > 30
          }">
            {{ selectedBatch.days_until_expiry <= 0 ? 'Expired' : selectedBatch.days_until_expiry + ' days' }}
          </p>
        </div>
      </div>
      <template #footer>
        <Button label="Close" icon="pi pi-times" @click="showBatchDetails = false" text />
      </template>
    </Dialog>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { useInventoryStore } from '@/Store/Inventory';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AdjustQuantity from './AdjustQuantity.vue';
import BatchManager from './BatchManagement.vue';

// PrimeVue components
import Button from 'primevue/button';
import Card from 'primevue/card';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Dialog from 'primevue/dialog';
import Message from 'primevue/message';
import Tag from 'primevue/tag';
import Toolbar from 'primevue/toolbar';
import Modal from '@/Components/Modal.vue';
import LoadingUI from '@/Components/LoadingUI.vue';
import AlertNotification from '@/Components/AlertNotification.vue';

const props = defineProps({
  inventoryId: {
    type: [Number, String],
    required: true
  }
});

// Store and state
const inventoryStore = useInventoryStore();
const inventory = ref(null);
const loading = ref(true);
const error = computed(() => inventoryStore.error);
const success = ref(null);

// Modal states
const showAdjustQuantity = ref(false);
const showProcessBatch = ref(false);
const showBatchDetails = ref(false);
const selectedBatch = ref(null);

// Fetch inventory data
const fetchInventoryData = async () => {
  loading.value = true;
  inventory.value = await inventoryStore.getInventory(props.inventoryId);
  loading.value = false;
};

// Format date
const formatDate = (dateString) => {
  if (!dateString) return 'N/A';
  return new Date(dateString).toLocaleDateString();
};

// Format currency
const formatCurrency = (value) => {
  return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(value);
};

// Get inventory status text and severity
const getStatusText = (status) => {
  const statusMap = {
    0: 'In Stock',
    1: 'Low Stock',
    2: 'Out of Stock',
    3: 'Reserved',
    4: 'Damaged',
    5: 'Expired'
  };
  return statusMap[status] || 'Unknown';
};

const getStatusSeverity = (status) => {
  const severityMap = {
    0: 'success', // In Stock - green
    1: 'warning', // Low Stock - yellow
    2: 'danger',  // Out of Stock - red
    3: 'info',    // Reserved - blue
    4: 'help',    // Damaged - purple
    5: 'secondary' // Expired - gray
  };
  return severityMap[status] || 'secondary';
};

// Get batch status text and severity
const getBatchStatusText = (status) => {
  if (typeof status === 'string') {
    const stringStatusMap = {
      'available': 'Available',
      'reserved': 'Reserved',
      'sold': 'Sold',
      'expired': 'Expired',
      'damaged': 'Damaged',
      'quarantine': 'In Quarantine',
      'in_transit': 'In Transit',
      'returned': 'Returned'
    };
    return stringStatusMap[status] || status.charAt(0).toUpperCase() + status.slice(1);
  } else {
    const numericStatusMap = {
      0: 'Available',
      1: 'Reserved',
      2: 'Sold',
      3: 'Expired',
      4: 'Damaged',
      5: 'Quarantine',
      6: 'In Transit',
      7: 'Returned'
    };
    return numericStatusMap[status] || 'Unknown';
  }
};

const getBatchStatusSeverity = (status) => {
  // Map both string and numeric statuses to PrimeVue Tag severities
  const stringSeverityMap = {
    'available': 'success',
    'reserved': 'info',
    'sold': 'secondary',
    'expired': 'danger',
    'damaged': 'help',
    'quarantine': 'warning',
    'in_transit': 'info',
    'returned': 'warning'
  };

  const numericSeverityMap = {
    0: 'success',
    1: 'info',
    2: 'secondary',
    3: 'danger',
    4: 'help',
    5: 'warning',
    6: 'info',
    7: 'warning'
  };

  if (typeof status === 'string') {
    return stringSeverityMap[status] || 'secondary';
  } else {
    return numericSeverityMap[status] || 'secondary';
  }
};

// Get movement type text and severity
const getMovementTypeText = (type) => {
  const typeMap = {
    'in': 'In',
    'out': 'Out',
    'adjustment': 'Adjustment',
    'transfer': 'Transfer',
  };
  return typeMap[type] || type;
};

const getMovementTypeSeverity = (type) => {
  const severityMap = {
    'in': 'success',
    'out': 'danger',
    'adjustment': 'info',
    'transfer': 'help',
  };
  return severityMap[type] || 'secondary';
};

const viewBatchDetails = (batch) => {
  selectedBatch.value = batch;
  showBatchDetails.value = true;
};

const handleAdjustmentSuccess = async (operation, result) => {
  success.value = `Inventory quantity adjusted successfully`;
  await fetchInventoryData();
};

const handleBatchSuccess = async (operation, result) => {
  success.value = `Batch operation completed successfully`;
  await fetchInventoryData();
};

const clearError = () => {
  if (typeof inventoryStore.clearMessages === 'function') {
    inventoryStore.clearMessages();
  } else {
    inventoryStore.error = null;
  }
};

const clearSuccess = () => {
  success.value = null;
};

onMounted(() => {
  fetchInventoryData();
});
</script>
