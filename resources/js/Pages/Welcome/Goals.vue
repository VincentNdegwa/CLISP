<script>
import PrimaryRoseButton from '@/Components/PrimaryRoseButton.vue';

export default {
    components: {
        PrimaryRoseButton
    }
}
</script>

<template>
    <section class=" h-dvh">
        <div class="relative w-full h-[60vh]">

            <!-- <div class="absolute inset-0 z-10 bg-white transform rotate-0 clip-triangle">
          <div class="absolute inset-0 flex justify-center rotate-180 bg-sky-400">

          </div>
        </div> -->
            <div class="flex justify-center  w-full items-center lg:pr-40 lg:pl-40 pt-4">
                <div class="flex flex-col items-center lg:mt-20 md:5">

                    <div class=" lg:text-7xl md:text-5xl sm:text-4xl text-2xl w-5/6 text-center font-bold mt-5">
                        Transforming Local Inventory <span class="text-red-500">Management</span>
                    </div>
                    <div class="md:text-lg text-sm w-3/6 mt-2 text-center">
                        By facilitating easy and secure inventory transactions, CLISP ensures that businesses can focus
                        on what
                        they
                        do best while we handle the logistics of resource sharing.
                    </div>

                    <PrimaryRoseButton class="text-white mt-10"> Start Your Journey! </PrimaryRoseButton>
                </div>
            </div>
        </div>

    </section>
</template>