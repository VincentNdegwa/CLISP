export default function getImageUrl(
    url,
    defaultUrl = "/storage/fallback-image.jpg"
) {
    if (!url) {
        return defaultUrl;
    }
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

    return `/storage/${url}` || defaultUrl;
}
