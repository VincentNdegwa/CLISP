<script>
import PrimaryRoseButton from "@/Components/PrimaryRoseButton.vue";

export default {
    data() {
        return {
            big_card:
                "big-card md:h-[100%] h-fit lg:w-[20%] w-full bg-slate-900 rounded-lg p-2",
            medium_card:
                "medium-card md:h-[70%] h-fit lg:w-[20%] w-full bg-rose-500 rounded-lg p-2",
            small_card:
                "small-card md:h-[50%] h-fit lg:w-[20%] w-full bg-slate-900 rounded-lg p-0",
        };
    },
    components: {
        PrimaryRoseButton,
    },
};
</script>

<template>
    <section class="min-h-dvh h-fit">
        <div class="relative w-full h-full md:h-fit flex flex-col gap-20">
            <div
                class="flex justify-center w-full items-center lg:pr-40 lg:pl-40 pt-4"
            >
                <div class="flex flex-col items-center lg:mt-20 md:5">
                    <div
                        class="lg:text-7xl md:text-5xl sm:text-4xl text-2xl w-5/6 text-center font-bold mt-5"
                    >
                        Transforming Local Inventory
                        <span class="text-red-500">Management</span>
                    </div>
                    <div class="md:text-lg text-sm w-3/6 mt-2 text-center">
                        By facilitating easy and secure inventory transactions,
                        CLISP ensures that businesses can focus on what they do
                        best while we handle the logistics of resource sharing.
                    </div>

                    <PrimaryRoseButton class="text-white mt-10">
                        Start Your Journey!
                    </PrimaryRoseButton>
                </div>
            </div>

            <div
                class="icons absolute top-96 h-[300px] w-[300px] lg:flex hidden flex-col justify-between right-40"
            >
                <i
                    class="bi bi-graph-up-arrow text-xl self-end p-3 bg-slate-950 rounded-full w-[50px] h-[50px] grid place-items-center text-white"
                ></i>

                <i
                    class="bi bi-envelope-open self-center p-3 bg-white border border-slate-950 rounded-full w-[50px] h-[50px] grid place-items-center text-slate-950"
                ></i>

                <i
                    class="bi bi-bar-chart-line text-xl self-start p-3 bg-rose-500 rounded-full w-[50px] h-[50px] grid place-items-center text-white"
                ></i>
            </div>

            <div
                class="flex lg:flex-row items-end p-3 gap-4 flex-col w-full md:h-[50vh] h-fit"
            >
                <div :class="big_card">
                    <img
                        src="images/arrow.png"
                        alt="arrow image"
                        class="transform rotate-12 h-28 mt-20"
                    />
                    <div class="text-2xl font-extrabold text-white mt-5">
                        Our mission is to help businesses maximize their
                        resources, reduce waste, and build stronger local
                        networks
                    </div>
                </div>

                <div
                    :class="[medium_card, 'flex flex-col justify-center gap-2']"
                >
                    <div class="text-7xl text-white w-full mt-1 font-bold">
                        96%
                    </div>

                    <div class="text-sm font-bold text-white">
                        Our users love CLISP! With a 95% customer satisfaction
                        rate, businesses appreciate the platform’s ease of use,
                        security, and the opportunities it creates for local
                        collaboration.
                    </div>
                </div>

                <div :class="small_card">
                    <img
                        src="images/statistics.avif"
                        alt="statistics"
                        class="w-full h-full object-cover"
                    />
                </div>

                <div
                    :class="[medium_card, 'flex flex-col justify-center gap-2']"
                >
                    <div class="text-7xl text-white w-full mt-1 font-bold">
                        2000+
                    </div>

                    <div class="text-sm font-bold text-white">
                        Join a growing network of over 2,000 local businesses
                        that trust CLISP to manage and share their inventory.
                        Whether you’re a small shop or a medium-sized
                        enterprise, CLISP helps you optimize resources and
                        connect with nearby businesses.
                    </div>
                </div>
                <div :class="big_card">
                    <img
                        src="images/arrow.png"
                        alt="arrow image"
                        class="transform rotate-12 h-28 mt-20"
                    />
                    <div class="text-lg font-extrabold text-white">
                        Businesses using CLISP have reported a 20% increase in
                        resource optimization, thanks to our efficient
                        inventory-sharing system. By reducing waste and
                        maximizing the use of available resources, businesses
                        can significantly improve their bottom line.
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
