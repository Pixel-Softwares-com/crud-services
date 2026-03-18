<?php

namespace CRUDServices\Helpers;

class ActivityBatchHelper
{
    public static function startBatch(): void
    {
        if (class_exists(\Spatie\Activitylog\Facades\LogBatch::class)) {
            \Spatie\Activitylog\Facades\LogBatch::startBatch();
        }
    }

    public static function endBatch(): void
    {
        if (class_exists(\Spatie\Activitylog\Facades\LogBatch::class)) {
            \Spatie\Activitylog\Facades\LogBatch::endBatch();
        }
    }
}
