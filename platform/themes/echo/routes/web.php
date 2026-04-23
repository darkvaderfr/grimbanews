<?php

use Botble\Base\Http\Middleware\RequiresJsonRequestMiddleware;
use Botble\Blog\Models\Post;
use Botble\SeoHelper\Facades\SeoHelper;
use Botble\Theme\Facades\Theme;
use Illuminate\Support\Facades\Route;
use Theme\Echo\Http\Controllers\EchoController;

Route::group(['middleware' => ['web', 'core']], function (): void {
    Theme::registerRoutes(function (): void {
        Route::get('comparatif/{clusterId}', function (int $clusterId) {
            $posts = Post::query()
                ->where('story_cluster_id', $clusterId)
                ->where('status', 'published')
                ->orderByRaw("CASE bias_rating WHEN 'left' THEN 1 WHEN 'center' THEN 2 WHEN 'right' THEN 3 ELSE 4 END")
                ->get();

            $storyTitle = $posts->first()->name ?? ('Dossier #' . $clusterId);

            SeoHelper::setTitle('Comparaison des sources — ' . $storyTitle)
                ->setDescription('Comparez comment les médias couvrent la même histoire.');

            Theme::breadcrumb()
                ->add('Accueil', url('/'))
                ->add('Comparaison', url('/comparatif/' . $clusterId));

            return Theme::scope('comparison', [
                'posts'      => $posts,
                'storyTitle' => $storyTitle,
                'clusterId'  => $clusterId,
            ])->render();
        })->name('public.comparison');

        Route::get('angles-morts', function () {
            $posts = Post::query()
                ->where('is_blindspot', true)
                ->where('status', 'published')
                ->latest()
                ->paginate(12);

            SeoHelper::setTitle('Angles morts — GrimbaNews')
                ->setDescription("Les histoires qu'un seul camp couvre.");

            Theme::breadcrumb()
                ->add('Accueil', url('/'))
                ->add('Angles morts', url('/angles-morts'));

            return Theme::scope('blindspot', compact('posts'))->render();
        })->name('public.blindspot');

        Route::group([
            'prefix' => 'ajax',
            'as' => 'public.ajax.',
            'middleware' => RequiresJsonRequestMiddleware::class,
            'controller' => EchoController::class,
        ], function (): void {
            Route::get('categories/{categoryId}/posts', 'ajaxGetPostByCategory')
                ->name('posts-by-category');

            Route::get('shortcode-blog-posts', 'ajaxShortcodeBlogPosts')
                ->name('shortcode-blog-posts');

            Route::get('shortcode-blog-categories', 'ajaxShortcodeBlogCategories')
                ->name('shortcode-blog-categories');

            Route::get('widget-blog-posts', 'ajaxWidgetBlogPosts')
                ->name('widget-blog-posts');

            Route::get('widget-blog-categories', 'ajaxWidgetBlogCategories')
                ->name('widget-blog-categories');

            Route::get('widget-breaking-news', 'ajaxWidgetBreakingNews')
                ->name('widget-breaking-news');

            Route::get('menu-sidebar', 'ajaxMenuSidebar')
                ->name('menu-sidebar');
        });
    });
});

Theme::routes();
