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
            currentUrl: `${window.location.origin + this.$page.url}`,
            navItems: [
                {
                    name: "Dashboard",
                    route: "dashboard",
                    open: localStorage.getItem("Dashboard") === "true",
                    icon: "bi bi-speedometer2",
                    subItems: null,
                },
                {
                    name: "Inventory",
                    icon: "bi bi-box-seam",
                    open: localStorage.getItem("Inventory") === "true",
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
                    open: localStorage.getItem("Business") === "true",
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
                    open: localStorage.getItem("Customer") === "true",
                    icon: "bi bi-person-circle",
                    subItems: null,
                },
                {
                    name: "B2B Trade",
                    icon: "bi bi-arrow-left-right",
                    open: localStorage.getItem("B2B Trade") === "true",
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
                    open: localStorage.getItem("B2C Trade") === "true",
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
                {
                    name: "Logistics",
                    route: "logistics.shipments",
                    open: localStorage.getItem("Logistics") === "true",
                    icon: "bi bi-truck",
                    subItems: null,
                },
            ],
            downSideNav: [
                {
                    name: "Settings",
                    route: "settings.view",
                    open: localStorage.getItem("Settings") === "true",
                    icon: "bi bi-gear",
                    subItems: null,
                },
                {
                    name: "Billing",
                    route: "billing.view",
                    open: localStorage.getItem("Billing") === "true",
                    icon: "bi bi-receipt",
                    subItems: null,
                },
            ],
        };
    },
    mounted() {
        this.setActiveLink();
        this.checkActiveAfterMount();
    },
    watch: {
        currentUrl: {
            handler(newValue) {
                this.currentUrl = newValue;
            },
            deep: true,
            immediate: true,
        },
    },
    methods: {
        setActiveLink() {
            this.navItems = this.navItems.map((item) => {
                if (item.subItems) {
                    item.subItems.forEach((subItem) => {
                        if (this.route(subItem.route) === this.currentUrl) {
                            this.activeMainNav = item.name;
                            this.activeSubNav = subItem.name;
                            localStorage.setItem("activeMainNav", item.name);
                            localStorage.setItem("activeSubNav", subItem.name);
                        } else {
                            item.open = false;
                            return item;
                        }
                    });
                } else if (this.route(item.route) === this.currentUrl) {
                    this.activeMainNav = item.name;
                    localStorage.setItem("activeMainNav", item.name);
                } else {
                    item.open = false;
                    return item;
                }
                let status = localStorage.getItem(`${item.name}`);
                if (status) {
                    let trueOrFalse = JSON.parse(status);
                    if (trueOrFalse == true) {
                        item.open = true;
                        return item;
                    } else {
                        item.open = false;
                        return item;
                    }
                }

                return item;
            });
        },

        toggleMainNav(navItem) {
            this.navItems = this.navItems.map((item) => {
                if (item.name === navItem.name) {
                    item.open = !item.open;
                    localStorage.setItem(`${item.name}`, item.open);

                    if (item.name != this.activeMainNav) {
                        this.activeMainNav = item.name;
                        localStorage.setItem("activeMainNav", item.name);
                    }
                    return item;
                } else {
                    if (item.open == true) {
                        item.open = false;
                        localStorage.setItem(`${item.name}`, false);
                        return item;
                    }
                }
                return item;
            });
        },

        isActiveMainNav(item) {
            return route(item.route) == this.currentUrl;
        },

        isActiveSubNav(subItem) {
            return route(subItem.route) == this.currentUrl;
        },

        isChildActive(subItemsArray) {
            if (subItemsArray) {
                return subItemsArray.some((subItem) =>
                    this.isActiveSubNav(subItem)
                );
            }
            return false;
        },
        checkActiveAfterMount() {
            this.navItems = this.navItems.map((navItem) => {
                if (navItem.subItems) {
                    let isSubItemActive = false;

                    navItem.subItems = navItem.subItems.map((subItem) => {
                        if (route(subItem.route) === this.currentUrl) {
                            isSubItemActive = true;
                        }
                        return subItem;
                    });

                    navItem.open = isSubItemActive;
                }
                if (route(navItem.route) === this.currentUrl) {
                    navItem.open = true;
                    return navItem;
                }
                return navItem;
            });
        },
    },
};
</script>

<template>
    <div class="flex flex-col justify-between w-full h-full">
        <div>
            <div
                v-for="(item, index) in navItems"
                :key="index"
                :class="[
                    'w-full h-fit p-3 mt-1 cursor-pointer transition-none ease-linear duration-1000',
                    item.open ? '' : '',
                ]"
            >
                <div class="h-full w-full flex flex-col items-start">
                    <div
                        v-if="item.subItems"
                        :class="['flex justify-between w-full p-0']"
                        @click="toggleMainNav(item)"
                    >
                        <div class="text-sm flex flex-row gap-4">
                            <i :class="item.icon"></i>
                            <div>{{ item.name }}</div>
                        </div>
                        <div>
                            <i class="bi bi-chevron-down" v-if="item.open"></i>
                            <i class="bi bi-chevron-compact-right" v-else></i>
                        </div>
                    </div>
                    <div
                        v-else
                        :class="[' w-full p-0']"
                        @click="toggleMainNav(item)"
                    >
                        <Link
                            :href="route(item.route)"
                            :key="index"
                            class="p-0"
                        >
                            <div class="text-sm flex flex-row gap-4">
                                <i :class="item.icon"></i>
                                <div>{{ item.name }}</div>
                            </div>
                        </Link>
                    </div>
                </div>

                <div
                    v-if="item.subItems && item.open"
                    class="w-full flex flex-col gap-1 items-center"
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
                            <div class="text-sm flex flex-row gap-4">
                                <i :class="subItem.icon"></i>
                                <div>{{ subItem.name }}</div>
                            </div>
                        </div>
                    </Link>
                </div>
            </div>
        </div>
        <div class="flex flex-col">
            <div
                v-for="(item, index) in downSideNav"
                :key="index"
                :class="[
                    'w-full h-fit p-3 mt-1 cursor-pointer transition-none ease-linear duration-1000',
                    item.open ? 'bg-gray-100' : '',
                ]"
            >
                <div class="h-full w-full flex flex-col items-start">
                    <div :class="[' w-full p-0']" @click="toggleMainNav(item)">
                        <Link
                            :href="route(item.route)"
                            :key="index"
                            class="p-0"
                        >
                            <div class="text-sm flex flex-row gap-4">
                                <i :class="item.icon"></i>
                                <div>{{ item.name }}</div>
                            </div>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
