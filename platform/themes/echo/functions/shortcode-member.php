<?php

use Botble\Base\Forms\FieldOptions\SelectFieldOption;
use Botble\Base\Forms\FieldOptions\TextFieldOption;
use Botble\Base\Forms\Fields\SelectField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Member\Models\Member;
use Botble\Shortcode\Compilers\Shortcode as ShortcodeCompiler;
use Botble\Shortcode\Facades\Shortcode;
use Botble\Shortcode\Facades\Shortcode as ShortcodeFacade;
use Botble\Shortcode\Forms\ShortcodeForm;
use Botble\Theme\Facades\Theme;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Support\Arr;

app('events')->listen(RouteMatched::class, function (): void {
    if (! is_plugin_active('member')) {
        return;
    }

    Shortcode::register('members', __('Members'), __('Display the list of members'), function (ShortcodeCompiler $shortcode): ?string {
        $memberIds = ShortcodeFacade::fields()->getIds('member_ids', $shortcode);

        if (! $memberIds) {
            return null;
        }

        $members = Member::query()
            ->with('slugable')
            ->wherePublished()
            ->whereIn('id', $memberIds)
            ->get();

        if ($members->isEmpty()) {
            return null;
        }

        return Theme::partial('shortcodes.team.index', compact('shortcode', 'members'));
    });

    Shortcode::setPreviewImage('members', Theme::asset()->url('images/ui-blocks/members.png'));

    Shortcode::setAdminConfig('members', function (array $attributes) {
        $members = Member::query()
            ->select(['id', 'first_name', 'last_name'])
            ->wherePublished()
            ->get()
            ->pluck('name', 'id')
            ->all();

        return ShortcodeForm::createFromArray($attributes)
            ->add(
                'title',
                TextField::class,
                TextFieldOption::make()
                    ->label(__('Title'))
                    ->placeholder(__('Enter title'))
                    ->toArray()
            )
            ->add(
                'member_ids[]',
                SelectField::class,
                SelectFieldOption::make()
                    ->label(__('Choose members'))
                    ->choices($members)
                    ->when(Arr::get($attributes, 'member_ids'), function (SelectFieldOption $option, $memberIds): void {
                        $option->selected(explode(',', $memberIds));
                    })
                    ->searchable()
                    ->multiple()
                    ->toArray()
            );
    });
});
