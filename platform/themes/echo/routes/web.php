<?php

use Botble\Base\Http\Middleware\RequiresJsonRequestMiddleware;
use Botble\Blog\Models\Post;
use Botble\SeoHelper\Facades\SeoHelper;
use Botble\Theme\Facades\Theme;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
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

        Route::post('topics/follow', function (Request $request) {
            $id = (int) $request->input('category_id');
            if (! $id) {
                return response()->json(['ok' => false, 'message' => 'Missing category_id'], 422);
            }

            $raw = (string) $request->cookie('grimba_follow', '');
            $ids = array_filter(array_map('intval', explode(',', $raw)));

            $action = $request->input('action', 'toggle');
            if ($action === 'follow' || ($action === 'toggle' && ! in_array($id, $ids, true))) {
                $ids[] = $id;
            } elseif ($action === 'unfollow' || ($action === 'toggle' && in_array($id, $ids, true))) {
                $ids = array_values(array_filter($ids, fn ($i) => $i !== $id));
            }

            $ids = array_values(array_unique($ids));
            $value = implode(',', $ids);

            return response()
                ->json(['ok' => true, 'followed' => $ids, 'count' => count($ids)])
                ->cookie('grimba_follow', $value, 60 * 24 * 365, '/', null, false, false);
        })->name('public.topics.follow');

        Route::get('pour-vous', function (Request $request) {
            $raw = (string) $request->cookie('grimba_follow', '');
            $ids = array_filter(array_map('intval', explode(',', $raw)));

            $postsQuery = Post::query()
                ->where('status', 'published')
                ->latest();

            if (! empty($ids)) {
                $postsQuery->whereHas('categories', fn ($q) => $q->whereIn('categories.id', $ids));
            }

            $posts = $postsQuery->paginate(12);

            SeoHelper::setTitle('Pour vous — GrimbaNews')
                ->setDescription("Votre fil personnalisé selon les sujets que vous suivez.");

            Theme::breadcrumb()
                ->add('Accueil', url('/'))
                ->add('Pour vous', url('/pour-vous'));

            return Theme::scope('for-you', [
                'posts'         => $posts,
                'followedIds'   => $ids,
            ])->render();
        })->name('public.for-you');

        Route::post('newsletter/subscribe', function (Request $request) {
            $data = Validator::make($request->all(), [
                'email'      => ['required', 'email:rfc', 'max:191'],
                'source_key' => ['nullable', 'string', 'max:64'],
            ])->validate();

            $now    = now();
            $locale = app()->getLocale();

            $email = mb_strtolower($data['email']);
            $table = DB::table('newsletter_subscriptions');
            $existing = $table->where('email', $email)->first();

            $payload = [
                'email'      => $email,
                'locale'     => $locale,
                'source_key' => $data['source_key'] ?? 'unknown',
                'ip_address' => $request->ip(),
                'user_agent' => mb_substr((string) $request->userAgent(), 0, 255),
                'updated_at' => $now,
            ];

            if ($existing) {
                $table->where('email', $email)->update($payload);
            } else {
                $payload['created_at'] = $now;
                $table->insert($payload);
            }

            return back()
                ->with('newsletter_flash', 'Merci ! Votre inscription à l\'infolettre GrimbaNews est enregistrée.')
                ->withFragment('newsletter');
        })->name('public.newsletter.subscribe');

        Route::get('methodologie', function () {
            SeoHelper::setTitle('Méthodologie — GrimbaNews')
                ->setDescription("Comment GrimbaNews classe les biais, repère les angles morts et note la crédibilité des sources.");

            Theme::breadcrumb()
                ->add('Accueil', url('/'))
                ->add('Méthodologie', url('/methodologie'));

            return Theme::scope('methodology', [])->render();
        })->name('public.methodology');

        Route::get('sources', function () {
            $rows = \Illuminate\Support\Facades\DB::table('news_sources')
                ->orderBy('credibility_score', 'desc')
                ->orderBy('name')
                ->get();

            $grouped = $rows->groupBy(fn ($r) => in_array($r->bias_rating, ['left','center','right']) ? $r->bias_rating : 'unknown');

            SeoHelper::setTitle('Sources classées — GrimbaNews')
                ->setDescription('Biais, propriété, crédibilité et origine des sources suivies.');

            Theme::breadcrumb()
                ->add('Accueil', url('/'))
                ->add('Sources', url('/sources'));

            return Theme::scope('sources', [
                'grouped' => $grouped,
                'total'   => $rows->count(),
            ])->render();
        })->name('public.sources');

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
