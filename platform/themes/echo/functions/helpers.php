<?php

use Botble\Blog\Models\Post;
use Botble\Theme\Supports\Youtube;
use Illuminate\Support\Facades\File;

if (! function_exists('echo_check_extension_audio')) {
    function echo_check_extension_audio(string $path): bool
    {
        return in_array(strtolower(File::extension($path)), ['mp3', 'wav', 'ogg']);
    }
}

if (! function_exists('echo_check_extension_video')) {
    function echo_check_extension_video(string $path): bool
    {
        return in_array(strtolower(File::extension($path)), ['mp4', 'webm', 'ogg']);
    }
}

if (! function_exists('echo_check_transparent_color')) {
    function echo_check_transparent_color(?string $color): bool
    {
        if (empty($color)) {
            return true;
        }

        return in_array($color, ['transparent', 'rgba(0, 0, 0, 0)', 'rgb(0, 0, 0)']);
    }
}

if (! function_exists('echo_is_video_post')) {
    function echo_is_video_post(Post $post): bool
    {
        return echo_get_post_video_url($post) !== null;
    }
}

if (! function_exists('echo_get_post_video_url')) {
    function echo_get_post_video_url(Post $post): ?string
    {
        $videoUrl = $post->getMetaData('video_url', true);

        if (Youtube::isYoutubeURL($videoUrl)) {
            $videoUrl ='https://www.youtube.com/watch?v=' . Youtube::getYoutubeVideoID($videoUrl);
        }

        if (empty($videoUrl)) {
            return null;
        }

        return $videoUrl;
    }
}

if (! function_exists('echo_is_audio_post')) {
    function echo_is_audio_post(Post $post): bool
    {
        return echo_get_post_audio_url($post) !== null;
    }
}

if (! function_exists('echo_get_post_audio_url')) {
    function echo_get_post_audio_url(Post $post): ?string
    {
        $audioUrl = $post->getMetaData('audio', true);

        if (empty($audioUrl)) {
            return null;
        }

        return $audioUrl;
    }
}

if (! function_exists('echo_is_show_post_meta')) {
    function echo_is_show_post_meta(string $type, bool $default = false): bool
    {
        $option = theme_option('blog_post_meta_display', null);

        if ($option === null) {
            return $default;
        }

        return in_array($type, json_decode($option, true));
    }
}
