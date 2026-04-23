@if (($timeReading = $post->time_reading) && $timeReading > 0)
    <x-core::icon name="ti ti-clock" class="me-1" />
    <span>
        {{ $timeReading == 1 ? __('1 minute read') : __(':count minutes read', ['count' => $timeReading]) }}
    </span>
@endif
