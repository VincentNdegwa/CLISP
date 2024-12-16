<script>
import Dropdown from "@/Components/Dropdown.vue";
import DropdownLink from "@/Components/DropdownLink.vue";
import Modal from "@/Components/Modal.vue";
import NewBusiness from "@/Components/NewBusiness.vue";
import SideNavigations from "@/Components/SideNavigations.vue";
import Button from "primevue/button";
import Popover from "primevue/popover";
import Select from "primevue/select";

export default {
    data() {
        return {
            menuOpen: true,
            default_business: null,
            modal: {
                open: false,
            },
        };
    },
    components: {
        Dropdown,
        DropdownLink,
        SideNavigations,
        Modal,
        NewBusiness,
        Popover,
        Select,
        Button,
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
            location.reload();
        },
        createNewBusiness() {
            this.modal.open = true;
            console.log("creating business");
        },
        closeModal() {
            this.modal.open = false;
        },
        toggle(event) {
            this.$refs.op.toggle(event);
        },
    },
    computed: {
        businesses() {
            return this.$page.props.user_businesses.business_user;
        },
        isAdminOrOwner() {
            return true;
        },
    },
};
</script>

<template>
    <Modal :show="modal.open" @close="closeModal">
        <NewBusiness @close="closeModal" />
    </Modal>
    <div>
        <div class="min-h-screen bg-gray-100 text-slate-950 relative">
            <nav
                :class="[
                    'bg-white border-b w-[230px] fixed top-0 left-0 h-[100vh] border-gray-100 z-20 transition-all duration-200 ease-linear',
                    !menuOpen ? '-translate-x-[230px]' : 'translate-x-0',
                ]"
            >
                <div class="flex flex-row items-center gap-5 h-[10vh]">
                    <div
                        class="relative w-full max-w-xs bg-white text-slate-950"
                    >
                        <div class="card flex justify-center w-90 ">
                            <Select
                                v-model="default_business"
                                :options="businesses"
                                optionValue="business"
                                class="w-[95%] place-self-center"
                            >
                                <!-- Custom value display -->
                                <template #value="slotProps">
                                    <div
                                        v-if="slotProps.value"
                                        class="flex items-center relative"
                                    >
                                        <img
                                            :src="
                                                slotProps.value.logo ||
                                                '/images/default-business-logo.png'
                                            "
                                            alt="Business Logo"
                                            class="w-6 h-6 rounded-sm mr-1"
                                        />
                                        <div class="flex flex-col">
                                            <span
                                                class="font-medium text-ellipsis whitespace-nowrap w-[140px] overflow-hidden"
                                            >
                                                {{
                                                    slotProps.value
                                                        .business_name
                                                }}
                                            </span>
                                            <span
                                                class="text-sm text-ellipsis whitespace-nowrap w-[140px] overflow-hidden text-slate-950"
                                            >
                                                {{ slotProps.value.email }}
                                            </span>
                                        </div>
                                        <div>icon</div>
                                    </div>
                                </template>

                                <template #dropdownicon>
                                    <i class="pi pi-code rotate-90" />
                                </template>

                                <!-- Custom dropdown options -->
                                <template #option="slotProps">
                                    <div
                                        class="flex items-center w-full h-full hover:bg-gray-100 cursor-pointer relative"
                                        @click="
                                            changeBusiness(slotProps.option)
                                        "
                                    >
                                        <img
                                            :src="
                                                slotProps.option.business
                                                    .logo ||
                                                '/images/default-business-logo.png'
                                            "
                                            alt="Business Logo"
                                            class="w-8 h-8 rounded-sm mr-3"
                                        />
                                        <div class="flex flex-col">
                                            <span
                                                class="font-medium text-ellipsis whitespace-nowrap w-[200px] overflow-hidden"
                                            >
                                                {{
                                                    slotProps.option.business
                                                        .business_name
                                                }}
                                            </span>
                                            <span
                                                class="text-sm text-gray-500 text-ellipsis whitespace-nowrap w-[200px] overflow-hidden"
                                            >
                                                {{
                                                    slotProps.option.business
                                                        .email
                                                }}
                                            </span>
                                        </div>
                                        <div
                                            v-if="
                                                slotProps.option.business
                                                    .business_id ==
                                                default_business.business_id
                                            "
                                            class="grid h-4 w-4 place-items-center place-self-start rounded-full bg-slate-800"
                                        >
                                            <div
                                                class="bg-white h-2 w-2 rounded-full"
                                            ></div>
                                        </div>
                                    </div>
                                </template>

                                <!-- Custom footer for adding a new business -->
                                <template #footer>
                                    <div class="p-3" v-if="isAdminOrOwner">
                                        <Button
                                            label="Add New Business"
                                            fluid
                                            text
                                            size="small"
                                            icon="pi pi-plus"
                                            @click="createNewBusiness"
                                        />
                                    </div>
                                </template>
                            </Select>
                        </div>
                    </div>
                </div>

                <div
                    class="nav-bar-holder flex flex-col h-[80vh] no-scrollbar overflow-y-scroll hide-overflow"
                >
                    <SideNavigations />
                </div>

                <div
                    class="profile h-16 flex items-center px-2 py-1 gap-3 relative"
                >
                    <div
                        class="flex items-center ring-1 ring-slate-200 rounded-md h-full w-full cursor-pointer gap-2 p-2"
                        @click="toggle"
                    >
                        <div class="avatar online">
                            <div class="w-10 h-10 rounded-full">
                                <img
                                    :src="
                                        $page.props.auth.user.profile_image ||
                                        '/images/default-profile.png'
                                    "
                                />
                            </div>
                        </div>
                        <div
                            class="flex flex-col text-ellipsis ms-2 h-10 w-full"
                        >
                            <div
                                class="text-md h-6 w-36 truncate text-ellipsis whitespace-nowrap"
                            >
                                {{ $page.props.auth.user.name }}
                            </div>
                            <div
                                class="text-sm text-slate-500 h-6 w-36 truncate text-ellipsis whitespace-nowrap"
                            >
                                {{ $page.props.auth.user.email }}
                            </div>
                        </div>
                    </div>

                    <Popover ref="op" class="w-56 -ms-1 p-0">
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
                    </Popover>
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
                </div>
            </header>

            <!-- Page Content -->
            <main
                :class="[
                    'pt-[6vh] h-[100vh] transition-all duration-200 ease-linear overflow-y-scroll hide-overflow ',
                    !menuOpen ? 'ps-0' : 'ps-[230px]',
                ]"
            >
                <div class="pt-2 h-full">
                    <div class="w-full mx-auto sm:px-4 lg:px-6">
                        <div
                            class="bg-white min-h-[92vh] h-fit shadow-sm sm:rounded-lg p-2"
                        >
                            <slot />
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</template>

<style></style>
