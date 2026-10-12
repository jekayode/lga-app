<?php

namespace App\Console\Commands\Membership;

use App\Enums\Role;
use App\Mail\CoordinatorReferralCodeMail;
use App\Models\User;
use App\Services\ReferralCodeGenerator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendCoordinatorCodesCommand extends Command
{
    protected $signature = 'membership:send-coordinator-codes
                            {--email=* : Only send to these coordinator email addresses}
                            {--dry-run : List recipients and codes without sending}';

    protected $description = 'Email every active LGA and Ward Coordinator their referral code and agent sign-up link';

    /**
     * Resend's default API limit is 2 requests per second.
     */
    protected const int DELAY_BETWEEN_SENDS_MICROSECONDS = 600_000;

    public function handle(ReferralCodeGenerator $codes): int
    {
        $coordinators = User::query()
            ->whereIn('role', [Role::LgaCoordinator, Role::WardCoordinator])
            ->where('is_active', true)
            ->when($this->option('email'), fn ($query, array $emails) => $query->whereIn('email', array_map('strtolower', $emails)))
            ->with('localGovernment')
            ->orderBy('name')
            ->get();

        if ($coordinators->isEmpty()) {
            $this->warn('No active coordinators found.');

            return self::SUCCESS;
        }

        $sent = 0;
        $failed = 0;

        foreach ($coordinators as $coordinator) {
            if (blank($coordinator->referral_code) && ! $this->option('dry-run')) {
                $coordinator->forceFill(['referral_code' => $codes->generate()])->save();
            }

            $this->line(sprintf(
                '%s %s <%s> · %s · %s',
                $this->option('dry-run') ? 'WOULD SEND' : 'SEND',
                $coordinator->name,
                $coordinator->email,
                $coordinator->localGovernment?->name ?? '—',
                $coordinator->referral_code ?: '(code generated on send)',
            ));

            if ($this->option('dry-run')) {
                continue;
            }

            try {
                Mail::to($coordinator->email)->send(new CoordinatorReferralCodeMail($coordinator));
                $sent++;
            } catch (Throwable $exception) {
                report($exception);
                $this->error("  failed: {$exception->getMessage()}");
                $failed++;
            }

            usleep(self::DELAY_BETWEEN_SENDS_MICROSECONDS);
        }

        $this->newLine();
        $this->info($this->option('dry-run')
            ? "{$coordinators->count()} coordinator(s) would be emailed."
            : "Sent: {$sent}, failed: {$failed}.");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
