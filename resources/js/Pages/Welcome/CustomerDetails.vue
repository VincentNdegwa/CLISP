<template>
    <div
        class="flex lg:flex-row flex-col gap-4 bg-slate-50 dark:bg-slate-900"
    >
        <div class="flex-1 flex items-center justify-center">
            <div class="p-6 pt-12 lg:p-12">
                <h1
                    class="text-3xl lg:text-5xl font-bold text-slate-900 dark:text-slate-50 mb-4 lg:leading-normal text-center lg:text-left"
                >
                    Create the screens your <br /><span
                        class="text-blue-500 dark:text-blue-400"
                        >visitors deserve to see</span
                    >
                </h1>
                <p
                    class="text-slate-700 dark:text-slate-200 leading-normal mb-8 text-center lg:text-left"
                >
                    Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed
                    do eiusmod tempor incididunt ut labore et dolore magna
                    aliqua.
                </p>
                <div
                    class="flex items-center justify-center lg:justify-start gap-6"
                >
                    <Button label="Learn More" type="button" />
                    <Button label="Live Demo" type="button" outlined />
                </div>
            </div>
        </div>
        <div class="flex-1 overflow-hidden">
            <img
                src="https://fqjltiegiezfetthbags.supabase.co/storage/v1/render/image/public/block.images/blocks/hero/hero-1.png"
                alt="hero-1"
                class="h-full w-full object-cover lg:[clip-path:polygon(12%_0,100%_0%,100%_100%,0_100%)]"
            />
        </div>
    </div>
</template>
<script setup>
import Button from "primevue/button";
</script>
