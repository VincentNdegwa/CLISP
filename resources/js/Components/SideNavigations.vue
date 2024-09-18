<script>
import { Link, usePage } from "@inertiajs/vue3";

export default {
    components: {
        Link,
    },
    data() {
        return {
            activeMainNav: localStorage.getItem("activeMainNav") || null,
            activeSubNav: localStorage.getItem("activeSubNav") || "",
            navItems: [
                {
                    name: "Dashboard",
                    route: "dashboard",
                    open: localStorage.getItem("Dashboard-open") === "true",
                    icon: "bi bi-speedometer2",
                    subItems: null,
                },
                {
                    name: "Inventory",
                    icon: "bi bi-box-seam",
                    open: localStorage.getItem("Inventory-open") === "true",
                    subItems: [
                        {
                            name: "Resources",
                            route: "inventory.resources",
                            icon: "bi bi-archive",
                        },
                        {
                            name: "Categories",
                            route: "inventory.categories",
                            icon: "bi bi-tags",
                        },
                    ],
                },
                {
                    name: "Business",
                    icon: "bi bi-briefcase",
                    open: localStorage.getItem("Business-open") === "true",
                    subItems: [
                        {
                            name: "My business",
                            route: "business.my-business",
                            icon: "bi bi-shop",
                        },
                        {
                            name: "Connections",
                            route: "business.connection",
                            icon: "bi bi-people-fill",
                        },
                    ],
                },
                {
                    name: "Customer",
                    route: "customer.my-customers",
                    open: localStorage.getItem("Customer-open") === "true",
                    icon: "bi bi-person-circle",
                    subItems: null,
                },
                {
                    name: "B2B Trade",
                    icon: "bi bi-arrow-left-right",
                    open: localStorage.getItem("B2B-open") === "true",
                    subItems: [
                        {
                            name: "Purchases",
                            route: "b2b.purchase",
                            icon: "bi bi-cart",
                        },
                        {
                            name: "Borrowings",
                            route: "b2b.borrowing",
                            icon: "bi bi-arrow-down-up",
                        },
                        {
                            name: "Leasing",
                            route: "b2b.leasing",
                            icon: "bi bi-file-earmark-text",
                        },
                    ],
                },
                {
                    name: "B2C Trade",
                    icon: "bi bi-people",
                    open: localStorage.getItem("B2C-open") === "true",
                    subItems: [
                        {
                            name: "Direct Sale",
                            route: "b2c.sale",
                            icon: "bi bi-currency-dollar",
                        },
                        {
                            name: "Borrowings",
                            route: "b2c.borrowing",
                            icon: "bi bi-arrow-repeat",
                        },
                        {
                            name: "Leasing",
                            route: "b2c.leasing",
                            icon: "bi bi-file-earmark-text",
                        },
                    ],
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
                if (item.subItems) {
                    item.subItems.forEach((subItem) => {
                        if (this.route(subItem.route) === currentUrl) {
                            this.activeMainNav = item.name;
                            this.activeSubNav = subItem.name;
                            localStorage.setItem("activeMainNav", item.name);
                            localStorage.setItem("activeSubNav", subItem.name);
                        }
                    });
                } else if (this.route(item.route) === currentUrl) {
                    this.activeMainNav = item.name;
                    localStorage.setItem("activeMainNav", item.name);
                }
            });
        },

        toggleMainNav(item) {
            this.activeMainNav = item.name;
            localStorage.setItem("activeMainNav", item.name);

            this.navItems = this.navItems.map((navItem) => {
                if (navItem.name === item.name) {
                    navItem.open = !navItem.open;
                    localStorage.setItem(`${item.name}-open`, navItem.open);
                    return navItem;
                } else {
                    navItem.open = false;
                    localStorage.setItem(`${navItem.name}-open`, false);
                    return navItem;
                }
            });
        },

        isActiveMainNav(item) {
            return this.activeMainNav === item.name;
        },

        isActiveSubNav(subItem) {
            return this.activeSubNav === subItem.name;
        },

        isChildActive(subItemsArray) {
            return subItemsArray.some((subItem) =>
                this.isActiveSubNav(subItem)
            );
        },
    },
};
</script>

<template>
    <div>
        <div
            v-for="(item, index) in navItems"
            :key="index"
            :class="['w-full h-fit p-0 cursor-pointer']"
        >
            <div class="h-full w-full flex flex-col items-start">
                <div
                    v-if="!item.subItems"
                    :class="['hover:bg-gray-100 w-full p-2']"
                    @click="toggleMainNav(item)"
                >
                    <Link :href="route(item.route)" :key="index" class="p-0">
                        <div class="text-sm flex flex-row gap-2">
                            <i :class="item.icon"></i>
                            <div>{{ item.name }}</div>
                        </div>
                    </Link>
                </div>
                <div
                    v-else
                    :class="[
                        'flex justify-between w-full hover:bg-gray-100 p-2',
                    ]"
                    @click="toggleMainNav(item)"
                >
                    <div class="text-sm flex flex-row gap-2">
                        <i :class="item.icon"></i>
                        <div>{{ item.name }}</div>
                    </div>
                    <div>
                        <i class="bi bi-chevron-down" v-if="item.open"></i>
                        <i class="bi bi-chevron-compact-right" v-else></i>
                    </div>
                </div>
            </div>

            <div
                v-if="item.subItems && isActiveMainNav(item) && item.open"
                class="h-fit w-full flex flex-col gap-1 items-center transition-all ease-linear duration-1000"
            >
                <Link
                    v-for="(subItem, subIndex) in item.subItems"
                    :href="route(subItem.route)"
                    :key="subIndex"
                    :class="[
                        'w-full h-fit pl-6 p-2 cursor-pointer',
                        isActiveSubNav(subItem)
                            ? 'bg-rose-50 text-rose-500'
                            : '',
                    ]"
                >
                    <div class="p-0 h-full w-full">
                        <div class="text-sm flex flex-row gap-2">
                            <i :class="subItem.icon"></i>
                            <div>{{ subItem.name }}</div>
                        </div>
                    </div>
                </Link>
            </div>
        </div>
    </div>
</template>
