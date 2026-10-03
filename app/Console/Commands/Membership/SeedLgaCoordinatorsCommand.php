<?php

namespace App\Console\Commands\Membership;

use App\Enums\Role;
use App\Models\LocalGovernment;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class SeedLgaCoordinatorsCommand extends Command
{
    protected $signature = 'membership:seed-lga-coordinators
                            {--send-reset : Email each coordinator a password reset link}
                            {--dry-run : Show planned creates/updates without writing or mailing}';

    protected $description = 'Create or update LGA Coordinator accounts from the roster data file';

    public function handle(): int
    {
        /** @var list<array{name: string, email: string, phone: string, local_government: string}> $roster */
        $roster = require database_path('seeders/data/lga_coordinators.php');

        $dryRun = (bool) $this->option('dry-run');
        $sendReset = (bool) $this->option('send-reset');

        if ($dryRun) {
            $this->warn('Dry run — no database writes or emails.');
        }

        $created = 0;
        $updated = 0;
        $resetsSent = 0;
        $failed = 0;

        foreach ($roster as $row) {
            $email = Str::lower(trim($row['email']));
            $name = trim($row['name']);
            $phone = User::normalizePhone($row['phone']);
            $lgaName = $row['local_government'];

            $lga = LocalGovernment::query()
                ->where('state', 'Lagos')
                ->where('name', $lgaName)
                ->first();

            if ($lga === null) {
                $this->error("Missing Local Government: {$lgaName} ({$email})");
                $failed++;

                continue;
            }

            $existing = User::query()->where('email', $email)->first();
            $action = $existing === null ? 'create' : 'update';

            $this->line(sprintf(
                '%s %s (%s) → %s',
                strtoupper($action),
                $name,
                $email,
                $lgaName,
            ));

            if ($dryRun) {
                if ($action === 'create') {
                    $created++;
                } else {
                    $updated++;
                }

                if ($sendReset) {
                    $this->line("  would send password reset to {$email}");
                    $resetsSent++;
                }

                continue;
            }

            $attributes = [
                'name' => $name,
                'phone' => $phone,
                'role' => Role::LgaCoordinator,
                'local_government_id' => $lga->id,
                'email_verified_at' => now(),
                'otp_verified_at' => now(),
                'must_setup_two_factor' => true,
                'is_active' => true,
            ];

            if ($existing === null) {
                $user = User::query()->create([
                    ...$attributes,
                    'email' => $email,
                    'password' => Hash::make(Str::password(32)),
                ]);
                $created++;
            } else {
                $existing->fill($attributes);
                $existing->save();
                $user = $existing->fresh();
                $updated++;
            }

            if ($sendReset) {
                $status = Password::sendResetLink(['email' => $user->email]);

                if ($status === Password::RESET_LINK_SENT) {
                    $this->info("  reset link sent to {$user->email}");
                    $resetsSent++;
                } else {
                    $this->error("  failed to send reset to {$user->email}: {$status}");
                    $failed++;
                }
            }
        }

        $this->newLine();
        $this->info("Created: {$created}, updated: {$updated}, resets: {$resetsSent}, failures: {$failed}");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
