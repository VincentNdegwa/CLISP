<template>
    <div class="card flex justify-center">
        <SplitButton
            :label="defaultItem.label"
            @click="defaultItem.method"
            :model="items"
            severity="contrast"
        />
    </div>
</template>

<script>
import SplitButton from "primevue/splitbutton";
import Toast from "primevue/toast";

export default {
    props: {
        SelectItems: {
            type: Array,
            required: true,
        },
        defaultItem: {
            type: Object,
            required: true,
        },
    },
    data() {
        return {
            items: [],
        };
    },
    mounted() {
        this.items = this.SelectItems.map((selected) => {
            let item = { label: "", command: null };
            item.label = selected.label;
            item.command = selected.method;
            return item;
        });
    },
    methods: {
        save() {
            this.$emit("click", "save");
        },
    },
    components: {
        SplitButton,
    },
};
</script>
