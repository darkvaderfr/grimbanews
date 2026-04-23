<?php

namespace Theme\Echo\Support;

use Botble\Base\Models\BaseQueryBuilder;
use Botble\Blog\Models\Post;
use Botble\Media\Facades\RvMedia;
use Botble\Shortcode\Compilers\Shortcode;
use Botble\Widget\Facades\WidgetGroup;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ThemeHelper
{
    public static function getCustomSidebars(): array
    {
        $sidebars = ['' => __('No Sidebar')];

        foreach (WidgetGroup::getGroups() as $key => $sidebar) {
            if (! Str::startsWith($key, 'custom_')) {
                continue;
            }

            $sidebars[$key] = $sidebar->getName();
        }

        return $sidebars;
    }

    public static function getBlogPosts(array $categoryIds = [], ?string $type = null, int $limit = 6): Collection
    {
        if (! is_plugin_active('blog') || ! $categoryIds) {
            return collect();
        }

        $query = Post::query()
            ->whereHas('categories', fn (BaseQueryBuilder $query) => $query->whereIn('id', $categoryIds));

        if ($type) {
            $query = match ($type) {
                'popular' => $query->orderByDesc('views'),
                'featured' => $query->where('is_featured', 1),
                default => $query->orderByDesc('created_at'),
            };
        }

        return $query
            ->wherePublished()
            ->limit($limit)
            ->get();
    }

    public static function getVariableStylesByShortcode(Shortcode $shortcode): array
    {
        $backgroundColor = $shortcode->background_color;

        $backgroundImage = $shortcode->background_image;
        $textColor = $shortcode->text_color;

        $isBackgroundTransparent = echo_check_transparent_color($backgroundColor);
        $backgroundColor = $isBackgroundTransparent ? null : $backgroundColor;
        $backgroundImage = $backgroundImage ? RvMedia::url($backgroundImage) : null;

        $textColor = $textColor
            ? (echo_check_transparent_color($textColor) ? 'var(--color-heading)' : $textColor)
            : 'var(--color-heading)';

        $textBodyColor = ($isBackgroundTransparent && ! $backgroundImage) ? 'var(--text-body)' : '#ffffff';
        $titleColor = (! $isBackgroundTransparent || $backgroundImage) ? '#ffffff' : 'var(--color-heading)';

        return [
            "--background-color: $backgroundColor !important" => $backgroundColor,
            "--background-image: url('$backgroundImage') !important" => $backgroundImage,
            "--text-color: $textColor !important" => $textColor,
            "--text-body-color: $textBodyColor !important" => $textBodyColor,
            "--title-color: $titleColor !important" => $titleColor,
        ];
    }
}
