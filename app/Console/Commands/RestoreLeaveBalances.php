<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class RestoreLeaveBalances extends Command
{
    protected $signature = 'leaves:restore {--file=/home/master/requisitions_backup_20260224.sql} {--dry-run}';
    protected $description = 'Restore leave balances from a SQL backup using email mapping and apply minimums and caps';

    public function handle()
    {
        ini_set('memory_limit', '1024M');
        $backupFile = $this->option('file');
        $dryRun = $this->option('dry-run');

        if (!file_exists($backupFile)) {
            $this->error("Backup file not found: $backupFile");
            return 1;
        }

        $this->info("Parsing backup file: $backupFile");

        $users = [];
        $historicalBalances = [];

        $handle = fopen($backupFile, "r");
        if (!$handle) {
            $this->error("Failed to open backup file.");
            return 1;
        }

        $isParsingUsers = false;
        $isParsingBalances = false;

        while (($line = fgets($handle)) !== false) {
            if (stripos($line, 'INSERT INTO') !== false && stripos($line, '`users`') !== false) {
                $isParsingUsers = true;
                $isParsingBalances = false;
            } elseif (stripos($line, 'INSERT INTO') !== false && stripos($line, '`leave_balances`') !== false) {
                $isParsingUsers = false;
                $isParsingBalances = true;
            } elseif (stripos($line, 'INSERT INTO') !== false) {
                $isParsingUsers = false;
                $isParsingBalances = false;
            }

            if ($isParsingUsers) {
                preg_match_all("/\((\d+),.*?'([^@'\s]+@[^'\s]+\.[a-z]{2,})'/", $line, $matches, PREG_SET_ORDER);
                foreach ($matches as $m) {
                    $users[$m[1]] = strtolower(trim($m[2]));
                }
            }

            if ($isParsingBalances) {
                preg_match_all("/\((\d+),(\d+),(\d+),([\d\.]+),[\d\.]+,([\d\.]+)/", $line, $matches, PREG_SET_ORDER);
                foreach ($matches as $m) {
                    $historicalBalances[$m[2]][$m[3]] = [
                        'alloc' => (float)$m[4],
                        'bal' => (float)$m[5]
                    ];
                }
            }
        }
        fclose($handle);

        $this->info("Mapping backup data...");

        $mapping = [];
        foreach ($users as $uid => $email) {
            if (isset($historicalBalances[$uid])) {
                $mapping[$email] = $historicalBalances[$uid];
            }
        }

        $currentUsers = User::all();
        $this->info("Processing " . $currentUsers->count() . " current users...");

        foreach ($currentUsers as $user) {
            $email = strtolower(trim($user->email));
            $isIntern = ($user->designation_id == 4);
            
            if (isset($mapping[$email])) {
                $userBalances = $mapping[$email];
                foreach ($userBalances as $typeId => $data) {
                    $this->updateOrCreateBalance($user->id, $typeId, $data['alloc'], $data['bal'], $dryRun);
                }
                // March accrual
                $this->addAccrual($user->id, 1, 1.75, $dryRun);
            } else {
                $this->applyMinimums($user, $isIntern, $dryRun);
            }
            
            // Apply 3.5 cap and ensuring minimum 3.5 if they had 0
            $this->ensureCapAndMinAnnual($user->id, 3.5, $dryRun);
            
            // Final step: ensure they have EVERY leave type record
            $this->ensureAllTypesExist($user->id, $dryRun);
        }

        $this->info("Restoration and capping complete!");
        return 0;
    }

    protected function updateOrCreateBalance($userId, $typeId, $alloc, $bal, $dryRun)
    {
        if ($dryRun) return;
        LeaveBalance::updateOrCreate(
            ['user_id' => $userId, 'leave_type_id' => $typeId],
            ['allocated' => $alloc, 'balance' => $bal]
        );
    }

    protected function addAccrual($userId, $typeId, $amount, $dryRun)
    {
        if ($dryRun) return;
        $balance = LeaveBalance::where('user_id', $userId)->where('leave_type_id', $typeId)->first();
        if ($balance) {
            $balance->increment('allocated', $amount);
            $balance->increment('balance', $amount);
        }
    }

    protected function applyMinimums($user, $isIntern, $dryRun)
    {
        if ($isIntern) {
            $this->updateOrCreateBalance($user->id, 13, 3, 3, $dryRun);
            $this->updateOrCreateBalance($user->id, 14, 2, 2, $dryRun);
            $this->updateOrCreateBalance($user->id, 10, 0, 1, $dryRun);
        }
        $this->updateOrCreateBalance($user->id, 1, 0, 0, $dryRun);
    }

    protected function ensureCapAndMinAnnual($userId, $val, $dryRun)
    {
        if ($dryRun) return;
        $balance = LeaveBalance::where('user_id', $userId)->where('leave_type_id', 1)->first();
        if ($balance) {
            // Cap at 3.5
            if ($balance->allocated > $val) {
                $diff = $balance->allocated - $val;
                $balance->allocated = $val;
                $balance->balance = max(0, $balance->balance - $diff);
                $balance->save();
            }
            // Min at 3.5 (if bal < 3.5)
            if ($balance->balance < $val) {
                $diff = $val - $balance->balance;
                $balance->increment('allocated', $diff);
                $balance->increment('balance', $diff);
            }
        }
    }

    protected function ensureAllTypesExist($userId, $dryRun)
    {
        if ($dryRun) return;
        $types = LeaveType::all();
        foreach ($types as $type) {
            LeaveBalance::firstOrCreate(
                ['user_id' => $userId, 'leave_type_id' => $type->id],
                ['allocated' => 0, 'taken' => 0, 'balance' => 0]
            );
        }
    }
}
