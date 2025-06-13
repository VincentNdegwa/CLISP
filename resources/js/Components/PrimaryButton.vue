<template>
    <button
        class="disabled:bg-gray-300 disabled:text-slate-600 disabled:cursor-not-allowed flex ring-0 focus:ring-0 justify-center items-center px-4 py-4 bg-gray-900 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-800 focus:bg-gray-700 active:bg-gray-900 focus:outline-none transition ease-in-out duration-150"
    >
        <slot />
    </button>
</template>
