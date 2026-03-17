<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Announcement;
use App\Models\User;
use App\Notifications\AnnouncementPublishedNotification;

/**
 * Script to verify the announcement notification logic.
 * Usage: php scripts/verify_announcement.php
 */

// 1. Mock an announcement
$announcement = Announcement::latest()->first() ?? new Announcement([
    'subject' => 'System Maintenance',
    'description' => 'We will be performing scheduled maintenance this weekend.',
    'author' => 1,
    'publish_date' => now(),
]);

// 2. Mock a user
$user = User::where('is_enabled', true)->first() ?? new User(['firstname' => 'Team', 'email' => 'test@example.com']);

echo "🔍 Starting Announcement Notification Verification...\n";
echo "--------------------------------------------------\n";

// Test Case 1: send_email = true
echo "Test 1: shouldSendMail = true\n";
$notificationTrue = new AnnouncementPublishedNotification($announcement, true);
$channelsTrue = $notificationTrue->via($user);
echo "   Channels: [" . implode(', ', $channelsTrue) . "]\n";

if (in_array('mail', $channelsTrue) && in_array('database', $channelsTrue)) {
    echo "   ✅ Result: SUCCESS (Both channels present)\n";
} else {
    echo "   ❌ Result: FAILURE (Expected database and mail)\n";
}

$mailMessage = $notificationTrue->toMail($user);
echo "   Sender: " . $mailMessage->from[1] . " <" . $mailMessage->from[0] . ">\n";
echo "   Subject: " . $mailMessage->subject . "\n";

if ($mailMessage->from[1] === 'Boxleo Support') {
     echo "   ✅ Sender Name: SUCCESS\n";
} else {
     echo "   ❌ Sender Name: FAILURE (" . $mailMessage->from[1] . ")\n";
}

// Test Case 2: send_email = false
echo "\nTest 2: shouldSendMail = false\n";
$notificationFalse = new AnnouncementPublishedNotification($announcement, false);
$channelsFalse = $notificationFalse->via($user);
echo "   Channels: [" . implode(', ', $channelsFalse) . "]\n";

if (!in_array('mail', $channelsFalse) && in_array('database', $channelsFalse)) {
    echo "   ✅ Result: SUCCESS (Only database channel present)\n";
} else {
    echo "   ❌ Result: FAILURE (Expected only database)\n";
}

echo "--------------------------------------------------\n";
echo "🏁 Verification Complete.\n";
