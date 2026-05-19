import { createRouter, createWebHistory } from 'vue-router';
import { siteData } from './siteData';
import AnnouncementDetailPage from './pages/AnnouncementDetailPage.vue';
import AnnouncementsPage from './pages/AnnouncementsPage.vue';
import ContactPage from './pages/ContactPage.vue';
import DownloadsPage from './pages/DownloadsPage.vue';
import GalleryPage from './pages/GalleryPage.vue';
import HomePage from './pages/HomePage.vue';
import NewsDetailPage from './pages/NewsDetailPage.vue';
import NewsPage from './pages/NewsPage.vue';
import ProfilePage from './pages/ProfilePage.vue';
import ServicesPage from './pages/ServicesPage.vue';
import StaticPageView from './pages/StaticPageView.vue';

const routes = [
    { path: '/', component: HomePage, meta: { title: 'Beranda' } },
    { path: '/profil', component: ProfilePage, meta: { title: 'Profil' } },
    { path: '/berita', component: NewsPage, meta: { title: 'Berita' } },
    { path: '/berita/:slug', component: NewsDetailPage, meta: { title: 'Detail Berita' } },
    { path: '/pengumuman', component: AnnouncementsPage, meta: { title: 'Pengumuman' } },
    { path: '/pengumuman/:slug', component: AnnouncementDetailPage, meta: { title: 'Detail Pengumuman' } },
    { path: '/layanan', component: ServicesPage, meta: { title: 'Layanan' } },
    { path: '/galeri', component: GalleryPage, meta: { title: 'Galeri' } },
    { path: '/download', component: DownloadsPage, meta: { title: 'Download' } },
    { path: '/kontak', component: ContactPage, meta: { title: 'Kontak' } },
    { path: '/:pathMatch(.*)*', component: StaticPageView, meta: { title: 'Halaman' } },
];

export const router = createRouter({
    history: createWebHistory(),
    routes,
    scrollBehavior() {
        return { top: 0, behavior: 'smooth' };
    },
});

router.afterEach((to) => {
    document.title = `${to.meta.title} | ${siteData?.identity?.name ?? 'Portal Langkat'}`;
});
