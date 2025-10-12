<?php

return [
    // Λεπτά μεταξύ των εγγραφών last_activity_at
    'touch_interval' => (int) env('USER_ACTIVITY_TOUCH_MINUTES', 5),
];
