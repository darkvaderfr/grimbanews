<?php

register_sidebar([
    'id' => 'header_top_right_sidebar',
    'name' => __('Header top right sidebar'),
    'description' => __('Header top right sidebar'),
]);

register_sidebar([
    'id' => 'header_top_left_sidebar',
    'name' => __('Header top left sidebar'),
    'description' => __('Header top left sidebar'),
]);

app()->booted(function (): void {
    remove_sidebar('header_sidebar');
    remove_sidebar('header_top_sidebar');
});
