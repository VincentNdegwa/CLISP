<script>
import Dropdown from "@/Components/Dropdown.vue";
import DropdownLink from "@/Components/DropdownLink.vue";
import SideNavigations from "@/Components/SideNavigations.vue";

export default {
    data() {
        return {
            menuOpen: true,
            default_business: null,
        };
    },
    components: {
        Dropdown,
        DropdownLink,
        SideNavigations,
    },
    mounted() {
        const def_business = JSON.parse(
            window.localStorage.getItem("default_business")
        );
        if (!def_business) {
            window.localStorage.setItem(
                "default_business",
                JSON.stringify(
                    this.$page.props.user_businesses.default_business
                )
            );
            this.default_business =
                this.$page.props.user_businesses.default_business;
        } else {
            this.default_business = def_business;
        }
    },
    methods: {
        changeBusiness(data) {
            const def_business = JSON.parse(
                window.localStorage.getItem("default_business")
            );
            if (def_business) {
                if (def_business.business_id != data.business_id) {
                    window.localStorage.setItem(
                        "default_business",
                        JSON.stringify(data)
                    );
                    this.default_business = data;
                    this.fetchBusinessData();
                }
            }
        },
        fetchBusinessData() {
            console.log(this.default_business);
        },
    },
};
</script>

<template>
    <div>
        <div class="min-h-screen bg-gray-100 text-slate-950">
            <nav
                :class="[
                    'bg-white border-b w-[230px] fixed top-0 left-0 h-[100vh] border-gray-100 z-20 transition-all duration-200 ease-linear',
                    !menuOpen ? '-translate-x-[230px]' : 'translate-x-0',
                ]"
            >
                <div class="flex flex-row items-center gap-5 h-[6vh]">
                    <div
                        class="relative w-full max-w-xs bg-white text-slate-950"
                    >
                        <div class="dropdown w-full p-0">
                            <div
                                tabindex="0"
                                role="button"
                                class="btn w-2/3 ms-1 bg-gray-100 text-slate-950 hover:bg-gray-200"
                            >
                                {{ default_business?.business_name }}
                            </div>
                            <ul
                                tabindex="0"
                                class="dropdown-content menu bg-gray-100 z-[1] w-52 p-2 shadow"
                            >
                                <li
                                    v-for="(item, index) in $page.props
                                        .user_businesses.business_user"
                                    :key="index"
                                    :value="item.business.business_id"
                                    @click="() => changeBusiness(item.business)"
                                >
                                    <a> {{ item.business.business_name }} </a>
                                </li>
                                <li class="mt-2">
                                    <a>
                                        <i class="bi bi-plus-circle"></i> Add
                                        Business</a
                                    >
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div
                    class="nav-bar-holder flex flex-col h-[84vh] overflow-y-scroll hide-overflow"
                >
                    <SideNavigations />
                </div>
            </nav>

            <header
                :class="[
                    'bg-white w-full h-[6vh] fixed top-0 ps-[230px] z-10 transition-all duration-200 ease-linear',
                    !menuOpen ? 'ps-0' : 'ps-[230px]',
                ]"
            >
                <div class="flex justify-between h-full">
                    <div
                        class="menu flex items-center ms-0 mt-1 text-slate-950"
                    >
                        <i
                            v-if="menuOpen"
                            class="bi bi-caret-left-square-fill text-2xl cursor-pointer"
                            @click="() => (menuOpen = !menuOpen)"
                        ></i>
                        <i
                            v-if="!menuOpen"
                            class="bi bi-caret-right-square-fill text-2xl cursor-pointer"
                            @click="() => (menuOpen = !menuOpen)"
                        ></i>
                    </div>
                    <div class="profile h-full flex items-center me-4 gap-3">
                        <!-- Drop Down -->
                        <Dropdown align="right" width="48">
                            <template #trigger>
                                <div
                                    class="flex items-center cursor-pointer gap-2 p-2"
                                >
                                    <div class="avatar online">
                                        <div class="w-10 h-10 rounded-full">
                                            <img
                                                :src="
                                                    $page.props.auth.user
                                                        .profile_image ||
                                                    'https://img.daisyui.com/images/stock/photo-1534528741775-53994a69daeb.webp'
                                                "
                                            />
                                        </div>
                                    </div>
                                    <div class="flex items-center">
                                        <div class="text-sm">
                                            {{ $page.props.auth.user.name }}
                                        </div>
                                        <svg
                                            class="ms-2 -me-0.5 h-4 w-4"
                                            xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 20 20"
                                            fill="currentColor"
                                        >
                                            <path
                                                fill-rule="evenodd"
                                                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                    </div>
                                </div>
                            </template>

                            <template #content>
                                <DropdownLink :href="route('profile.edit')">
                                    Profile
                                </DropdownLink>
                                <DropdownLink
                                    :href="route('logout')"
                                    method="post"
                                    as="button"
                                >
                                    Log Out
                                </DropdownLink>
                            </template>
                        </Dropdown>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main
                :class="[
                    'pt-[6vh] h-[100vh] overflow-y-scroll hide-overflow transition-all duration-200 ease-linear',
                    !menuOpen ? 'ps-0' : 'ps-[230px]',
                ]"
            >
                <slot />
            </main>
        </div>
    </div>
</template>
