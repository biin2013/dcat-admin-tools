<?php

namespace Biin2013\DcatAdminTools\Trait;

use Dcat\Admin\Admin;
use Dcat\Admin\Grid\Filter;

trait UseDatetimeFilter
{
    /**
     * Add a between-datetime filter with time normalization behavior.
     *
     * Before the user manually picks a time, switching the date resets the time to 00:00:00.
     * Once a time has been picked, switching the date keeps that time.
     *
     * @param Filter $filter The filter object (can come from a useTrashFilter closure).
     * @param string $column The column name to filter on.
     * @param string $label The filter label (should be resolved via translation).
     */
    protected function datetimeFilter(Filter $filter, string $column, string $label): void
    {
        $filter->between($column, $label)->datetime(['useCurrent' => false]);

        $this->registerDatetimeFilterScript($column);
    }

    /**
     * Inject the script that normalizes the time part of a datetime filter.
     *
     * Namespace / data keys are derived from the column so multiple filters
     * on the same page do not interfere with each other.
     */
    protected function registerDatetimeFilterScript(string $column): void
    {
        $script = <<<'JS'
(function () {
    var $inputs = $('input[id^="filter-column-__COLUMN__-"]');
    var activeInput = null;

    $inputs.off('.__NS__')
        .on('dp.show.__NS__', function () {
            activeInput = this;
        })
        .on('dp.change.__NS__', function (e) {
            if (!e.date) {
                return;
            }
            var $input = $(this);
            if ($input.data('__NORMALIZING__')) {
                $input.data('__NORMALIZING__', false);
                return;
            }
            // A same-day change that actually alters the time means the user picked a time.
            if (e.oldDate && e.oldDate.isSame(e.date, 'day') && !e.oldDate.isSame(e.date)) {
                $input.data('__TOUCHED__', true);
                return;
            }
            // Once a time has been picked, keep it when the date changes.
            if ($input.data('__TOUCHED__')) {
                return;
            }
            var picker = $input.data('DateTimePicker');
            var midnight = e.date.clone().startOf('day');
            if (picker && !midnight.isSame(e.date)) {
                $input.data('__NORMALIZING__', true);
                picker.date(midnight);
                setTimeout(function () {
                    $input.data('__NORMALIZING__', false);
                }, 0);
            }
        });

    $(document).off('mousedown.__NS__', '.bootstrap-datetimepicker-widget .timepicker')
        .on('mousedown.__NS__', '.bootstrap-datetimepicker-widget .timepicker', function () {
            if (activeInput) {
                $(activeInput).data('__TOUCHED__', true);
            }
        });
})();
JS;

        Admin::script(strtr($script, [
            '__COLUMN__' => $column,
            '__NS__' => 'zdfarmTime-' . $column,
            '__TOUCHED__' => 'zdfarmTimeTouched-' . $column,
            '__NORMALIZING__' => 'zdfarmNormalizing-' . $column,
        ]));
    }
}