<script>
import { Link } from "@inertiajs/vue3";
export default {
    components: {
        Link,
    },
    data() {
        return {
            active: "Dashboard",
            navItems: [
                {
                    name: "Dashboard",
                    route: "dashboard",
                    icon: "bi bi-menu-up",
                },
                {
                    name: "Inventory",
                    route: "dashboard",
                    icon: "bi bi-box-seam-fill",
                },
            ],
        };
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
