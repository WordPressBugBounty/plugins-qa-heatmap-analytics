<?php

namespace QAAnalyticsVendor;

// Don't redefine the functions if included multiple times.
if (!\function_exists('QAAnalyticsVendor\\GuzzleHttp\\Promise\\promise_for')) {
    require __DIR__ . '/functions.php';
}
