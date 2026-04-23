<?php

app()->booted(function (): void {
    remove_sidebar('header_top_sidebar');
});
