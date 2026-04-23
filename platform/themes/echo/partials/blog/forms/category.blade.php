<x-core::form.select
    :label="__('Choose categories')"
    name="category_ids"
    :value="$categoryIds"
    :searchable="true"
    :multiple="true"
    :options="$categories"
    :placeholder="__('Choose categories')"
/>

<x-core::form.text-input
    type="number"
    name="limit"
    :label="__('Number display posts')"
    :value="Arr::get($attributes, 'limit', 6)"
/>
