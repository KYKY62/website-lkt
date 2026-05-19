<?php

namespace App\Http\Controllers;

use App\Models\NewsArticle;
use App\Models\Announcement;
use App\Models\DownloadDocument;
use App\Models\PageWidget;
use App\Models\ServiceShortcut;
use App\Models\SiteMenu;
use App\Models\StaticPage;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class PublicSiteController extends Controller
{
    public function __invoke(Request $request): View
    {
        $siteData = config('langkat_site');
        $siteData['navigation'] = $this->publicNavigation();
        $siteData['pages'] = $this->publicPages();
        $siteData['news'] = $this->publicNews();
        $siteData['announcements'] = $this->publicAnnouncements();
        $siteData['downloads'] = $this->publicDownloads();
        $siteData['services'] = $this->publicServices();
        $siteData['service_apps'] = $siteData['services'];
        $siteData['hero_widgets'] = $this->publicHeroWidgets();
        $siteData['pre_footer_widgets'] = $this->publicPreFooterWidgets();

        return view('app', [
            'siteData' => $siteData,
            'currentPath' => $request->path() === '/' ? '/' : '/'.$request->path(),
        ]);
    }

    private function publicNews(): array
    {
        if (! Schema::hasTable('news_articles')) {
            return [];
        }

        $articles = NewsArticle::query()
            ->with('publishedBy')
            ->published()
            ->orderByDesc('published_at')
            ->get();

        return $articles
            ->map(function (NewsArticle $article): array {
                return [
                    'slug' => $article->slug,
                    'title' => $article->title,
                    'category' => $article->category,
                    'date' => $article->published_at?->locale('id')->translatedFormat('d F Y'),
                    'summary' => $article->excerpt,
                    'cover_image_url' => $article->coverImage(),
                    'gallery_images' => $article->galleryImages(),
                    'editor_name' => $article->publishedBy?->name ?: $article->legacy_author,
                    'content_html' => $this->resolveContentHtml($article->content),
                ];
            })
            ->values()
            ->all();
    }

    private function publicAnnouncements(): array
    {
        if (! Schema::hasTable('announcements')) {
            return [];
        }

        return Announcement::query()
            ->with('publishedBy')
            ->published()
            ->orderByDesc('published_at')
            ->get()
            ->map(fn (Announcement $announcement): array => $announcement->publicPayload())
            ->values()
            ->all();
    }

    private function publicDownloads(): array
    {
        if (! Schema::hasTable('download_documents')) {
            return [];
        }

        return DownloadDocument::query()
            ->published()
            ->orderByDesc('published_at')
            ->get()
            ->map(fn (DownloadDocument $download): array => $download->publicPayload())
            ->values()
            ->all();
    }

    private function publicNavigation(): array
    {
        if (! Schema::hasTable('site_menus')) {
            return collect(config('langkat_site.navigation', []))
                ->map(fn (array $item): array => [
                    'label' => $item['label'],
                    'path' => $item['path'],
                    'target' => '_self',
                    'children' => [],
                ])
                ->values()
                ->all();
        }

        $menus = SiteMenu::query()
            ->with(['page', 'children.page'])
            ->active()
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->orderBy('label')
            ->get();

        if ($menus->isEmpty()) {
            return collect(config('langkat_site.navigation', []))
                ->map(fn (array $item): array => [
                    'label' => $item['label'],
                    'path' => $item['path'],
                    'target' => '_self',
                    'children' => [],
                ])
                ->values()
                ->all();
        }

        return $menus
            ->filter(fn (SiteMenu $menu): bool => $this->menuIsVisible($menu))
            ->map(fn (SiteMenu $menu): array => $this->mapMenuItem($menu))
            ->values()
            ->all();
    }

    private function publicPages(): array
    {
        if (! Schema::hasTable('static_pages')) {
            return [];
        }

        return StaticPage::query()
            ->published()
            ->orderBy('title')
            ->get()
            ->map(fn (StaticPage $page): array => [
                'title' => $page->title,
                'path' => $page->path,
                'excerpt' => $page->excerpt,
                'content_html' => $this->resolveContentHtml($page->content),
            ])
            ->values()
            ->all();
    }

    private function publicServices(): array
    {
        if (! Schema::hasTable('service_shortcuts')) {
            return [];
        }

        return ServiceShortcut::query()
            ->published()
            ->ordered()
            ->get()
            ->map(fn (ServiceShortcut $service): array => $service->publicPayload())
            ->values()
            ->all();
    }

    private function publicPreFooterWidgets(): array
    {
        if (! Schema::hasTable('page_widgets')) {
            return [];
        }

        return PageWidget::query()
            ->with('staticPage')
            ->published()
            ->where('display_area', PageWidget::AREA_PRE_FOOTER)
            ->ordered()
            ->get()
            ->filter(fn (PageWidget $widget): bool => filled($widget->targetPath()))
            ->groupBy(fn (PageWidget $widget): string => $widget->targetPath())
            ->map(function ($widgets): array {
                return [
                    PageWidget::COLUMN_LEFT => $widgets
                        ->where('column', PageWidget::COLUMN_LEFT)
                        ->sortBy('sort_order')
                        ->map(fn (PageWidget $widget): array => $widget->publicPayload())
                        ->values()
                        ->all(),
                    PageWidget::COLUMN_RIGHT => $widgets
                        ->where('column', PageWidget::COLUMN_RIGHT)
                        ->sortBy('sort_order')
                        ->map(fn (PageWidget $widget): array => $widget->publicPayload())
                        ->values()
                        ->all(),
                ];
            })
            ->filter(fn (array $columns): bool => $columns[PageWidget::COLUMN_LEFT] !== [] || $columns[PageWidget::COLUMN_RIGHT] !== [])
            ->all();
    }

    private function publicHeroWidgets(): array
    {
        if (! Schema::hasTable('page_widgets')) {
            return [];
        }

        return PageWidget::query()
            ->published()
            ->where('display_area', PageWidget::AREA_HOME_HERO)
            ->where('target_type', PageWidget::TARGET_BUILTIN)
            ->where('target_path', '/')
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get()
            ->map(fn (PageWidget $widget): array => $widget->publicPayload())
            ->values()
            ->all();
    }

    private function mapMenuItem(SiteMenu $menu): array
    {
        return [
            'label' => $menu->label,
            'path' => $this->resolveMenuPath($menu),
            'target' => $menu->item_type === SiteMenu::TYPE_LINK ? $menu->target : '_self',
            'children' => $menu->children
                ->where('is_active', true)
                ->filter(fn (SiteMenu $child): bool => $this->menuIsVisible($child))
                ->sortBy(fn (SiteMenu $child): string => str_pad((string) $child->sort_order, 6, '0', STR_PAD_LEFT).'_'.$child->label)
                ->map(fn (SiteMenu $child): array => [
                    'label' => $child->label,
                    'path' => $this->resolveMenuPath($child),
                    'target' => $child->item_type === SiteMenu::TYPE_LINK ? $child->target : '_self',
                ])
                ->values()
                ->all(),
        ];
    }

    private function menuIsVisible(SiteMenu $menu): bool
    {
        if ($menu->item_type === SiteMenu::TYPE_PAGE) {
            return $menu->page && $menu->page->status === 'published';
        }

        if ($menu->item_type === SiteMenu::TYPE_MODULE) {
            return (bool) SiteMenu::modulePath((string) $menu->module_key);
        }

        return filled($menu->url) || $menu->item_type !== SiteMenu::TYPE_LINK;
    }

    private function resolveMenuPath(SiteMenu $menu): string
    {
        return match ($menu->item_type) {
            SiteMenu::TYPE_PAGE => $menu->page?->path ?? '/',
            SiteMenu::TYPE_LINK => $menu->url ?: '/',
            SiteMenu::TYPE_MODULE => SiteMenu::modulePath((string) $menu->module_key) ?? '/',
            default => '/',
        };
    }

    private function resolveContentHtml(string $content): string
    {
        if (Str::contains($content, '<')) {
            return $content;
        }

        return collect(preg_split("/\r\n\r\n|\n\n|\r\r/", $content))
            ->map(fn (?string $paragraph): string => trim((string) $paragraph))
            ->filter()
            ->map(fn (string $paragraph): string => '<p>'.e($paragraph).'</p>')
            ->implode('');
    }
}
