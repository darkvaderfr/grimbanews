<?php

use Botble\Base\Forms\FieldOptions\SelectFieldOption;
use Botble\Base\Forms\FieldOptions\TextFieldOption;
use Botble\Base\Forms\Fields\SelectField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Member\Models\Member;
use Botble\Widget\AbstractWidget;
use Botble\Widget\Forms\WidgetForm;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class MembersWidget extends AbstractWidget
{
    public function __construct()
    {
        parent::__construct([
            'name' => __('Members List'),
            'description' => __('Members list widget'),
            'members' => [],
        ]);
    }

    public function settingForm(): WidgetForm|string|null
    {
        if (! is_plugin_active('member')) {
            return null;
        }

        $members = Member::query()
            ->wherePublished()
            ->get();

        $membersSelect = [];

        foreach ($members as $member) {
            $membersSelect[$member->id] = $member->name;
        }

        return WidgetForm::createFromArray($this->getConfig())
            ->add(
                'title',
                TextField::class,
                TextFieldOption::make()
                    ->label('Title')
                    ->toArray()
            )
            ->add(
                'member_ids',
                SelectField::class,
                SelectFieldOption::make()
                    ->choices($membersSelect)
                    ->searchable()
                    ->multiple()
                    ->label(__('Choose Members'))
                    ->toArray()
            );
    }

    public function data(): array|Collection
    {
        $data = $this->getConfig();

        $members = collect();

        if ($memberIds = Arr::get($data, 'member_ids')) {
            $members = Member::query()
                ->with('slugable')
                ->whereIn('id', $memberIds)
                ->wherePublished()
                ->get();
        }

        return compact('members');
    }
}
