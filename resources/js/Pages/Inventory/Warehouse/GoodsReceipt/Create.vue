<script setup>
// This is a placeholder file. The actual implementation should follow the pattern in the PurchaseOrder/Create.vue
// that you've already created. This file just ensures that routes don't break until the full 
// implementation is completed.

import FormLayout from "@/Layouts/FormLayout.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";

const emit = defineEmits(["close"]);
</script>

<template>
    <FormLayout title="Create Goods Receipt">
        <p class="mb-4 text-gray-600">
            This page is currently under development. Please check back later.
        </p>
        
        <div class="flex justify-end gap-2 mt-4">
            <PrimaryButton @click="$emit('close')" type="button" class="mr-2">
                Close
            </PrimaryButton>
        </div>
    </FormLayout>
</template>
