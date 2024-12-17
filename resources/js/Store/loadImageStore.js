// loadImageStore.js
import { defineStore } from "pinia";

export const useLoadImageStore = defineStore("loadImageStore", {
    state: () => ({
        imageUrl: null,
    }),
    getters: {
    
        getImage:
            () =>
            (url, defaultUrl = null) => {
                if (url?.startsWith("https://") || url?.startsWith("http://")) {
                    return url;
                }

                if (url?.startsWith("data:image/")) {
                    return url;
                }

                if (url?.startsWith("/storage")) {
                    return url;
                }

                if (url?.startsWith("storage")) {
                    return `/${url}`;
                }

                return defaultUrl || "/storage/fallback-image.jpg";
            },
    },
});
