<?php

namespace Theme\Echo\Http\Controllers;

use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Blog\Models\Category;
use Botble\Blog\Models\Post;
use Botble\Shortcode\Compilers\Shortcode as ShortcodeCompiler;
use Botble\Shortcode\Facades\Shortcode;
use Botble\Theme\Facades\Theme;
use Botble\Theme\Http\Controllers\PublicController;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Theme\Echo\Http\Requests\ShortcodeAttributeRequest;
use Theme\Echo\Http\Requests\WidgetConfigRequest;
use Theme\Echo\Support\ThemeHelper;

class EchoController extends PublicController
{
    public function __construct(protected BaseHttpResponse $response)
    {
    }

    public function ajaxGetPostByCategory(string $categoryId, Request $request): BaseHttpResponse
    {
        $request->validate(['limit' => 'nullable|integer']);

        $paginate = (int) $request->input('paginate') ?: 12;

        $category = Category::query()
            ->wherePublished()
            ->whereKey($categoryId)
            ->firstOrFail();

        $posts = get_posts_by_category($categoryId, $paginate);

        return $this
            ->response
            ->setData(Theme::partial('shortcodes.blog-categories.partials.posts', compact('posts', 'category')));
    }

    public function ajaxShortcodeBlogPosts(ShortcodeAttributeRequest $request): BaseHttpResponse
    {
        $shortcode = new ShortcodeCompiler('blog-posts', $request->input('shortcode'), null);

        $data = [
            'content' => null,
            'hasSwiper' => false,
            'hasSidebar' => false,
        ];

        $categoryIds = Shortcode::fields()->getIds('category_ids', $shortcode);

        $limit = (int) $shortcode->limit ?: 4;

        $posts = Post::query()
            ->with('slugable')
            ->wherePublished()
            ->when(! empty($categoryIds), function ($query) use ($categoryIds): void {
                $query->whereHas('categories', function ($query) use ($categoryIds): void {
                    $query->whereIn('id', $categoryIds);
                });
            })
            ->limit($limit)
            ->latest()
            ->get();

        if ($posts->isEmpty()) {
            return $this->response->setData($data);
        }

        $style = (int) Str::after($shortcode->style, 'style-') <= 18 ? $shortcode->style : 'style-1';
        $style = $style ?: 'style-1';

        $sidebar = $shortcode->sidebar;

        $variableStyles = ThemeHelper::getVariableStylesByShortcode($shortcode);

        $data['content'] = Theme::partial('shortcodes.blog-posts.styles.' . $style, compact('shortcode', 'posts', 'sidebar', 'variableStyles'));

        $data['hasSwiper'] = str_contains($data['content'], 'swiper-slide');

        $data['hasSidebar'] = (bool) $sidebar;

        return $this
            ->response
            ->setData($data);
    }

    public function ajaxShortcodeBlogCategories(ShortcodeAttributeRequest $request): BaseHttpResponse
    {
        $shortcode = new ShortcodeCompiler('blog-categories', $request->input('shortcode'), null);

        $data = [
            'content' => null,
            'hasSwiper' => false,
            'hasSidebar' => false,
        ];

        if (! $categoryIds = Shortcode::fields()->getIds('category_ids', $shortcode)) {
            return $this->response->setData($data);
        }

        $style = $shortcode->style;
        $title = $shortcode->title;
        $firstCategory = null;
        $postsPerCategory = (int) $shortcode->number_of_post_per_category ?: 3;
        $postsPerCategory = max($postsPerCategory, 1);

        if (count($categoryIds) === 1) {
            $firstCategory = Category::query()
                ->wherePublished()
                ->with(['children' => fn ($query) => $query->limit(3)])
                ->where('id', $categoryIds)
                ->when($style == 'style-3', fn (Builder $query) => $query->withCount('posts'))
                ->first();

            if (! $firstCategory) {
                return $this->response->setData($data);
            }

            $categories = $firstCategory->children;

            $title = $title ?: $firstCategory->name;
        } else {
            $categories = Category::query()
                ->wherePublished()
                ->whereIn('id', $categoryIds)
                ->when($style == 'style-3', fn (Builder $query) => $query->withCount('posts'))
                ->get();
        }

        if ($categories->isEmpty()) {
            return $this->response->setData($data);
        }

        $variableStyles = ThemeHelper::getVariableStylesByShortcode($shortcode);
        $sidebar = $shortcode->sidebar;
        $style = (int) Str::after($shortcode->style, 'style-') <= 3 ? $shortcode->style : 'style-1';

        $data['content'] = Theme::partial(
            'shortcodes.blog-categories.styles.' . $style,
            compact(
                'shortcode',
                'firstCategory',
                'categories',
                'title',
                'postsPerCategory',
                'categoryIds',
                'variableStyles',
                'sidebar'
            )
        );

        $data['hasSwiper'] = str_contains($data['content'], 'swiper-slide');
        $data['hasSidebar'] = (bool) $sidebar;

        return $this
            ->response
            ->setData($data);
    }

    public function ajaxWidgetBlogPosts(WidgetConfigRequest $request): BaseHttpResponse
    {
        $config = $request->input('config');

        if (! $config) {
            return $this->response->setData(null);
        }

        $categoryIds = Arr::get($config, 'category_ids', []) ?: [];
        $limit = (int) Arr::get($config, 'limit', 4);
        $type = Arr::get($config, 'type');

        $posts = ThemeHelper::getBlogPosts($categoryIds, $type, $limit);

        if ($posts->isEmpty()) {
            return $this->response->setData(null);
        }

        $sidebar = $request->input('sidebar');

        $cardStyle = Arr::get($config, 'card_style', 'default-card');

        return $this->response->setData(
            view(Theme::getThemeNamespace('widgets.blog-posts.templates.partials.posts'), compact('config', 'cardStyle', 'posts', 'sidebar'))->render()
        );
    }

    public function ajaxWidgetBlogCategories(WidgetConfigRequest $request): BaseHttpResponse
    {
        $config = $request->input('config');

        if (! $config) {
            return $this->response->setData(null);
        }

        $title = Arr::get($config, 'title');

        $tabs = Shortcode::fields()->getTabsData([
            'background_image', 'background_color', 'category_id', 'title',
        ], new ShortcodeCompiler('blog-categories', $config));

        $tabs = collect($tabs);

        $categoryIds = array_filter($tabs->pluck('category_id')->all());

        if (empty($categoryIds)) {
            return $this->response->setData(null);
        }

        $categories = Category::query()
            ->with('slugable')
            ->wherePublished()
            ->whereIn('id', $categoryIds)
            ->get()
            ->keyBy('id')
            ->all();

        if (! $categories) {
            return $this->response->setData(null);
        }

        $tabs->transform(fn ($tab) => [
            'category' => $categories[$tab['category_id']] ?? null,
            'title' => $tab['title'],
            'background_image' => $tab['background_image'],
            'background_color' => $tab['background_color'],
        ]);

        $sidebar = $request->input('sidebar');

        return $this
            ->response
            ->setData(
                view(
                    Theme::getThemeNamespace('widgets.blog-categories.templates.partials.blog-categories'),
                    compact('config', 'title', 'tabs', 'sidebar')
                )->render()
            );
    }

    public function ajaxWidgetBreakingNews(WidgetConfigRequest $request): BaseHttpResponse
    {
        $config = $request->input('config');

        if (! $config) {
            return $this->response->setData(null);
        }

        $categoryIds = Arr::get($config, 'category_ids', []) ?: [];
        $limit = (int) Arr::get($config, 'limit', 4);
        $type = Arr::get($config, 'type');
        $sidebar = $request->input('sidebar');
        $posts = ThemeHelper::getBlogPosts($categoryIds, $type, $limit);

        if ($posts->isEmpty()) {
            return $this->response->setData(null);
        }

        return $this->response->setData(
            view(
                Theme::getThemeNamespace('widgets.breaking-news.templates.partials.breaking-news'),
                compact('config', 'posts', 'sidebar')
            )->render()
        );
    }

    public function ajaxMenuSidebar(): BaseHttpResponse
    {
        return $this
            ->response
            ->setData(Theme::partial('menu-sidebar.content'));
    }
}
