<script>
import { Link, usePage } from "@inertiajs/vue3";

export default {
    components: {
        Link,
    },
    data() {
        return {
            active: "",
            navItems: [
                {
                    name: "Dashboard",
                    route: "dashboard",
                    icon: "bi bi-menu-up",
                },
                {
                    name: "Inventory",
                    route: "inventory.open",
                    icon: "bi bi-box-seam-fill",
                },
            ],
        };
    },
    mounted() {
        this.setActiveLink();
    },
    methods: {
        setActiveLink() {
            const currentUrl = `${window.location.origin + this.$page.url}`;

            this.navItems.forEach((item) => {
                if (this.route(item.route) === currentUrl) {
                    this.active = item.name;
                }
            });
        },
    },
};
</script>

<template>
    <Link
        v-for="(item, index) in navItems"
        :href="route(item.route)"
        :key="index"
        :class="[
            'w-full h-[2.5rem] p-4 cursor-pointer',
            active === item.name ? 'bg-rose-50 text-rose-500' : '',
        ]"
    >
        <div class="p-1 h-full w-full flex items-center">
            <div class="text-sm flex flex-row gap-2">
                <i :class="item.icon"></i>
                <div>{{ item.name }}</div>
            </div>
        </div>
    </Link>
</template>
