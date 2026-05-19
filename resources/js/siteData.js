import logoLangkatUrl from '../img/logo_langkat.png';

export const siteData = window.__SITE_DATA__ ?? {};
export const brandLogoUrl = logoLangkatUrl;

export const mainNavigation = siteData.navigation ?? [];
export const staticPages = siteData.pages ?? [];
export const newsItems = siteData.news ?? [];
export const announcementItems = siteData.announcements ?? [];
export const serviceItems = siteData.services ?? [];
export const serviceApps = siteData.service_apps ?? [];
export const galleryItems = siteData.gallery ?? [];
export const downloadItems = siteData.downloads ?? [];
export const departmentNews = siteData.department_news ?? { enabled: false, title: '', description: '', items: [] };
export const heroWidgets = siteData.hero_widgets ?? [];
export const preFooterWidgets = siteData.pre_footer_widgets ?? {};

export function findNewsBySlug(slug) {
    return newsItems.find((item) => item.slug === slug);
}

export function findAnnouncementBySlug(slug) {
    return announcementItems.find((item) => item.slug === slug);
}

export function findStaticPageByPath(path) {
    return staticPages.find((item) => item.path === path);
}
