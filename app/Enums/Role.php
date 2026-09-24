<?php

namespace App\Enums;

enum Role: string
{
    case CommunityMember = 'community_member';
    case Agent = 'agent';
    case WardCoordinator = 'ward_coordinator';
    case LgaCoordinator = 'lga_coordinator';
    case StateManager = 'state_manager';
    case Admin = 'admin';

    public function label(): string
    {
        return match ($this) {
            self::CommunityMember => 'Community Member',
            self::Agent => 'Agent',
            self::WardCoordinator => 'Ward Coordinator',
            self::LgaCoordinator => 'LGA Coordinator',
            self::StateManager => 'State Manager',
            self::Admin => 'Admin',
        };
    }

    public function requiresMandatoryEmailOtpAndTwoFactor(): bool
    {
        return match ($this) {
            self::CommunityMember => false,
            default => true,
        };
    }

    public function canIssueAgentReferralCodes(): bool
    {
        return match ($this) {
            self::WardCoordinator, self::LgaCoordinator, self::StateManager, self::Admin => true,
            default => false,
        };
    }

    public function canIssueMemberReferralCodes(): bool
    {
        return $this === self::Agent;
    }

    /**
     * @return list<self>
     */
    public static function staffRoles(): array
    {
        return [
            self::Agent,
            self::WardCoordinator,
            self::LgaCoordinator,
            self::StateManager,
            self::Admin,
        ];
    }
}
