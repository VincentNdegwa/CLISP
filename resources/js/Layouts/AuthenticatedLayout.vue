<script>
import Dropdown from "@/Components/Dropdown.vue";
import DropdownLink from "@/Components/DropdownLink.vue";
import Modal from "@/Components/Modal.vue";
import NewBusiness from "@/Components/NewBusiness.vue";
import NoActiveSubscription from "@/Components/NoActiveSubscription.vue";
import SideNavigations from "@/Components/SideNavigations.vue";
import getImageUrl from "@/Utils/loadImageUtils.js";
import axios from "axios";
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
                components: null,
                closeable: true,
            },
            currentUrl: `${window.location.origin + this.$page.url}`,
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
        NoActiveSubscription,
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

        if (
            !this.$page.props.user_businesses.default_business
                .activeSubscription &&
            this.currentUrl != route("billing.view")
        ) {
            this.modal.open = true;
            this.modal.closeable = false;
            this.modal.components = "NoActiveSubscription";
        }
    },
    methods: {
        async changeBusiness(data) {
            const def_business = JSON.parse(
                window.localStorage.getItem("default_business")
            );
            if (def_business) {
                window.localStorage.setItem(
                    "default_business",
                    JSON.stringify(data)
                );
                this.default_business = data;
                if (def_business.business_id != data.business_id) {
                    await axios
                        .get(
                            `/api/business/${data.business_id}/${this.$page.props.auth.user.id}/set-default-business`
                        )
                        .then((res) => {
                            log(res);
                        })
                        .catch((err) => {
                            log(err);
                        })
                        .finally(() => {
                            this.fetchBusinessData();
                        });
                }
            }
        },
        fetchBusinessData() {
            location.reload();
        },
        createNewBusiness() {
            this.modal.open = true;
            this.modal.components = "NewBusiness";
            console.log("creating business");
        },
        closeModal() {
            this.modal.open = false;
        },
        toggle(event) {
            this.$refs.op.toggle(event);
        },
        getImageUrl,
    },
    computed: {
        businesses() {
            return this.$page.props.user_businesses.business_user.map(
                (x) => x.business
            );
        },
        isAdminOrOwner() {
            return true;
        },
    },
};
</script>

<template>
    <Modal :show="modal.open" :closeable="modal.closeable" @close="closeModal">
        <NewBusiness
            v-if="modal.components == 'NewBusiness'"
            @close="closeModal"
        />
        <NoActiveSubscription
            v-if="modal.components == 'NoActiveSubscription'"
            :business="$page.props.user_businesses.default_business"
        />
    </Modal>
    <div>
        <div class="min-h-screen bg-gradient-to-br from-slate-50 via-slate-100 to-slate-50 dark:from-slate-950 dark:via-slate-900 dark:to-slate-950 text-slate-950 dark:text-white relative">
            <!-- Animated Background Elements -->
            <div class="fixed inset-0 pointer-events-none -z-10">
                <div class="absolute top-0 right-0 w-1/3 h-1/3 bg-gradient-to-bl from-rose-500/10 to-blue-500/10 rounded-bl-full blur-3xl animate-pulse-slow"></div>
                <div class="absolute bottom-0 left-0 w-1/3 h-1/3 bg-gradient-to-tr from-amber-500/10 to-purple-500/10 rounded-tr-full blur-3xl animate-pulse-slower"></div>
                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-1/2 h-1/2 bg-gradient-to-t from-blue-500/5 to-emerald-500/5 rounded-full blur-3xl animate-spin-slowest"></div>
                <!-- Animated dots/circles -->
                <div class="absolute top-20 right-40 w-2 h-2 bg-rose-500/30 rounded-full animate-ping"></div>
                <div class="absolute top-40 left-1/4 w-3 h-3 bg-amber-500/20 rounded-full animate-ping" style="animation-delay: 1s"></div>
                <div class="absolute bottom-32 right-1/4 w-2.5 h-2.5 bg-blue-500/20 rounded-full animate-ping" style="animation-delay: 2s"></div>
            </div>

            <!-- Sidebar/Navigation -->
            <nav
                :class="[
                    'bg-slate-50/80 flex flex-col py-4 justify-between dark:bg-slate-800/80 backdrop-blur-md border-r w-[280px] fixed top-0 left-0 h-[100vh] border-slate-200/50 dark:border-slate-700/50 z-20 transition-all duration-300 ease-out shadow-xl dark:shadow-slate-900/50',
                    !menuOpen ? '-translate-x-[280px]' : 'translate-x-0',
                ]"
            >
                <!-- Business Selector Area -->
                <div class="flex flex-row items-center h-fit border-b border-slate-200/70 dark:border-slate-700/70 px-2">
                    <div class="relative w-full bg-slate-50 dark:bg-slate-800 text-slate-950 dark:text-white">
                        <div class="card flex justify-center w-full">
                            <Select
                                v-model="default_business"
                                :options="businesses"
                                class="w-full"
                                panelClass="business-dropdown-panel"
                            >
                                <!-- Custom value display -->
                                <template #value="slotProps">
                                    <div v-if="slotProps.value" class="flex items-center relative">
                                        <div class="relative w-10 h-10 mr-3 flex-shrink-0">
                                            <div class="absolute inset-0 bg-gradient-to-br from-rose-400 to-purple-500 rounded-lg animate-pulse-slow"></div>
                                            <img
                                                :src="getImageUrl(slotProps.value.logo, '/images/default-business-logo.png')"
                                                alt="Business Logo"
                                                class="w-full h-full object-cover rounded-lg shadow-sm relative z-10"
                                            />
                                            <div class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 rounded-full border-2 border-slate-50 dark:border-slate-800 z-20"></div>
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="font-medium text-ellipsis whitespace-nowrap w-[180px] overflow-hidden">
                                                {{ slotProps.value.business_name }}
                                            </span>
                                            <span class="text-xs text-ellipsis whitespace-nowrap w-[180px] overflow-hidden text-slate-500 dark:text-slate-400">
                                                {{ slotProps.value.email }}
                                            </span>
                                        </div>
                                    </div>
                                </template>

                                <template #dropdownicon>
                                    <i class="pi pi-chevron-down text-slate-400 dark:text-slate-500" />
                                </template>

                                <!-- Custom dropdown options -->
                                <template #option="slotProps">
                                    <div
                                        class="flex items-center w-full h-full gap-1 hover:bg-slate-100 dark:hover:bg-slate-700 cursor-pointer relative rounded-lg transition-all duration-150 my-1"
                                        @click="changeBusiness(slotProps.option)"
                                    >
                                        <div class="relative">
                                            <div class="absolute inset-0 bg-gradient-to-br from-rose-400/20 to-purple-500/20 rounded-lg animate-pulse-slow"></div>
                                            <img
                                                :src="getImageUrl(slotProps.option.logo, '/images/default-business-logo.png')"
                                                alt="Business Logo"
                                                class="w-12 h-12 object-cover rounded-lg shadow-md mr-3 relative z-10"
                                            />
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="font-medium text-ellipsis whitespace-nowrap w-[200px] overflow-hidden">
                                                {{ slotProps.option.business_name }}
                                            </span>
                                            <span class="text-xs text-slate-500 dark:text-slate-400 text-ellipsis whitespace-nowrap w-[200px] overflow-hidden">
                                                {{ slotProps.option.email }}
                                            </span>
                                        </div>
                                        <div
                                            v-if="slotProps.option.business_id == default_business.business_id"
                                            class="grid h-6 w-6 place-items-center place-self-start rounded-full bg-gradient-to-r from-rose-500 to-pink-600 shadow-md ml-auto"
                                        >
                                            <i class="pi pi-check text-white text-xs"></i>
                                        </div>
                                    </div>
                                </template>

                                <!-- Custom footer for adding a new business -->
                                <template #footer>
                                    <div class="p-4 border-t border-slate-200 dark:border-slate-700" v-if="isAdminOrOwner">
                                        <Button
                                            label="Add New Business"
                                            class="w-full bg-gradient-to-r from-rose-500 to-pink-600 hover:from-rose-600 hover:to-pink-700 dark:from-rose-600 dark:to-pink-700 dark:hover:from-rose-500 dark:hover:to-pink-600 text-white border-none transition-colors duration-200 shadow-md hover:shadow-lg"
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

                <!-- Navigation Menu with Animation -->
                <div class="nav-bar-holder flex flex-col h-[70vh] no-scrollbar overflow-y-scroll hide-overflow px-1 py-2">
                    <SideNavigations />
                </div>

                <!-- User Profile Section -->
                <div class="profile h-fit flex items-center gap-3 relative border-t border-slate-200 dark:border-slate-700 bg-slate-50/80 px-2 dark:bg-slate-800/80 backdrop-blur-sm">
                    <div
                        class="flex items-center bg-gradient-to-r from-slate-100 to-slate-50 dark:from-slate-700 dark:to-slate-800 rounded-lg h-fit w-full cursor-pointer gap-3 p-2 shadow-lg hover:shadow-xl transition-all duration-200 group"
                        @click="toggle"
                    >
                        <div class="relative">
                            <div class="absolute inset-0 bg-gradient-to-br from-blue-400/30 to-purple-500/30 rounded-full animate-pulse-slow"></div>
                            <div class="w-14 h-14 rounded-full overflow-hidden border-2 border-slate-200 dark:border-slate-600 shadow-inner relative z-10 group-hover:scale-105 transition-transform duration-200">
                                <img
                                    :src="getImageUrl($page.props.auth.user.profile_image, '/images/default-profile.png')"
                                    class="w-full h-full object-cover"
                                />
                            </div>
                            <div class="absolute bottom-0 right-0 w-3.5 h-3.5 bg-green-500 rounded-full border-2 border-slate-100 dark:border-slate-700 z-20"></div>
                        </div>
                        <div class="flex flex-col text-ellipsis h-auto w-full">
                            <div class="text-base font-semibold h-6 w-36 truncate text-ellipsis whitespace-nowrap group-hover:text-rose-600 dark:group-hover:text-rose-400 transition-colors duration-200">
                                {{ $page.props.auth.user.name }}
                            </div>
                            <div class="text-xs text-slate-500 dark:text-slate-400 h-6 w-36 truncate text-ellipsis whitespace-nowrap">
                                {{ $page.props.auth.user.email }}
                            </div>
                        </div>
                        <div class="text-slate-400 group-hover:text-rose-500 dark:group-hover:text-rose-400 transition-colors duration-200">
                            <i class="pi pi-chevron-up text-sm group-hover:rotate-180 transition-transform duration-300"></i>
                        </div>
                    </div>

                    <Popover ref="op" class="user-popover w-64 ms-3 p-0 shadow-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                        <div class="p-4 border-b border-slate-200 dark:border-slate-700 bg-gradient-to-r from-slate-50 to-slate-100 dark:from-slate-800 dark:to-slate-800 rounded-lg">
                            <div class="flex items-center gap-3">
                                <img
                                    :src="getImageUrl($page.props.auth.user.profile_image, '/images/default-profile.png')"
                                    class="w-12 h-12 rounded-full border-2 border-slate-200 dark:border-slate-700 object-cover"
                                />
                                <div>
                                    <div class="text-base font-bold text-slate-800 dark:text-white">{{ $page.props.auth.user.name }}</div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400">{{ $page.props.auth.user.email }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="py-2 bg-slate-50 dark:bg-slate-800">
                            <div class="px-3 py-2 text-xs font-semibold uppercase text-slate-400 dark:text-slate-500">Account</div>
                            
                            <DropdownLink :href="route('profile.edit')" class="flex items-center gap-3 px-4 py-3 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors duration-150">
                                <div class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                                    <i class="pi pi-user text-blue-500 text-sm"></i>
                                </div>
                                <div>
                                    <div class="font-medium">Profile Settings</div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400">Manage your account</div>
                                </div>
                            </DropdownLink>
                            
                            <div class="h-px bg-slate-200 dark:bg-slate-700 my-1 mx-4"></div>
                            
                            <DropdownLink
                                :href="route('logout')"
                                method="post"
                                as="button"
                                class="flex w-full items-center gap-3 px-4 py-3 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-rose-500 dark:hover:text-rose-400 transition-colors duration-150"
                            >
                                <div class="w-8 h-8 rounded-full bg-rose-100 dark:bg-rose-900/30 flex items-center justify-center">
                                    <i class="pi pi-power-off text-rose-500 text-sm"></i>
                                </div>
                                <div>
                                    <div class="font-medium">Log Out</div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400">End your session</div>
                                </div>
                            </DropdownLink>
                        </div>
                        
                        <!-- Version info -->
                        <!-- <div class="px-4 py-2 bg-slate-100 dark:bg-slate-700/50 text-xs text-center text-slate-400 dark:text-slate-500">
                            CLISP v2.0.5
                        </div> -->
                    </Popover>
                </div>
                
            </nav>

            <!-- Top Header Bar -->
            <header
                :class="[
                    'bg-slate-50/80 dark:bg-slate-800/80 backdrop-blur-md w-full h-[6vh] fixed top-0 ps-[280px] z-10 transition-all duration-300 ease-out shadow-md dark:shadow-slate-900/30',
                    !menuOpen ? 'ps-0' : 'ps-[280px]',
                ]"
            >
                <div class="flex justify-between items-center h-full px-5">
                    <div class="flex items-center gap-4">
                        <button 
                            class="group flex items-center justify-center w-8 h-8 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors duration-200" 
                            @click="() => (menuOpen = !menuOpen)"
                        >
                            <i
                                v-if="menuOpen"
                                class="pi pi-angle-left text-xl text-slate-500 dark:text-slate-400 group-hover:text-rose-500 dark:group-hover:text-rose-400 transition-colors duration-200"
                            ></i>
                            <i
                                v-if="!menuOpen"
                                class="pi pi-angle-right text-xl text-slate-500 dark:text-slate-400 group-hover:text-rose-500 dark:group-hover:text-rose-400 transition-colors duration-200"
                            ></i>
                        </button>
                    </div>

                    <!-- Header Actions -->
                    <div class="flex items-center gap-4">
                        <button class="flex items-center justify-center w-8 h-8 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors duration-200 relative">
                            <i class="pi pi-bell text-slate-500 dark:text-slate-400"></i>
                            <div class="absolute top-1 right-1 w-2 h-2 bg-rose-500 rounded-full"></div>
                        </button>
                        
                        <button class="flex items-center justify-center w-8 h-8 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors duration-200">
                            <i class="pi pi-inbox text-slate-500 dark:text-slate-400"></i>
                        </button>
                        
                        <button class="flex items-center justify-center w-8 h-8 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors duration-200">
                            <i class="pi pi-cog text-slate-500 dark:text-slate-400"></i>
                        </button>
                        
                        <div class="h-6 w-px bg-slate-200 dark:bg-slate-700"></div>
                        
                        <button class="flex items-center justify-center w-8 h-8 rounded-lg bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300">
                            <i class="pi pi-moon text-sm"></i>
                        </button>
                    </div>
                </div>
            </header>

            <!-- Main Content Area -->
            <main
                :class="[
                    'pt-[6vh] h-[100vh] transition-all duration-300 ease-out overflow-y-scroll hide-overflow',
                    !menuOpen ? 'ps-0' : 'ps-[280px]',
                ]"
            >
                <div class="pt-6 pb-8 h-full">
                    <div class="w-full mx-auto sm:px-6 lg:px-8">
                        <div class="bg-slate-50/80 dark:bg-slate-800/80 backdrop-blur-md min-h-[92vh] h-fit shadow-xl sm:rounded-2xl p-6 border border-slate-200/50 dark:border-slate-700/50 relative overflow-hidden">
                            <!-- Decorative elements for content area -->
                            <div class="absolute top-0 right-0 w-64 h-64 bg-gradient-to-bl from-rose-400/5 to-blue-500/5 rounded-full blur-3xl -z-10"></div>
                            <div class="absolute bottom-0 left-0 w-96 h-96 bg-gradient-to-tr from-purple-400/5 to-amber-500/5 rounded-full blur-3xl -z-10"></div>
                            
                            <slot />
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</template>

<style>
.hide-overflow::-webkit-scrollbar {
    display: none;
}
  
.hide-overflow {
    -ms-overflow-style: none;
    scrollbar-width: none;
}

/* Animation styles */
.animate-pulse-slow {
    animation: pulse 4s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

.animate-pulse-slower {
    animation: pulse 6s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

.animate-spin-slowest {
    animation: spin 30s linear infinite;
}

@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.5;
    }
}

@keyframes spin {
    to {
        transform: translate(-50%, -50%) rotate(360deg);
    }
}

/* Enhanced dropdown styling */
::v-deep(.p-dropdown) {
    border-radius: 0.75rem;
    box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.05);
}

::v-deep(.p-dropdown-panel) {
    border-radius: 0.75rem;
    overflow: hidden;
    box-shadow: 0 10px 25px -5px rgb(0 0 0 / 0.1);
}

::v-deep(.user-popover) {
    box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
}

/* Option hover effect */
::v-deep(.p-dropdown-items li:hover) {
    transform: translateY(-2px);
}
</style>
