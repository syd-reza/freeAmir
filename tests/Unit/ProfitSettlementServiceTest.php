<?php

namespace Tests\Unit;

use App\Services\ProfitSettlementService;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class ProfitSettlementServiceTest extends TestCase
{
    public function test_profit_is_split_fifty_fifty_between_team_and_investor(): void
    {
        $split = ProfitSettlementService::split(1_000_000, 50, 10);

        self::assertSame(500_000.0, $split['team_share']);
        self::assertSame(500_000.0, $split['investor_share']);
        self::assertSame(50_000.0, $split['team_vat']);
        self::assertSame(50_000.0, $split['investor_vat']);
    }

    public function test_team_percent_changes_the_share_and_rounding_keeps_the_total(): void
    {
        $split = ProfitSettlementService::split(999.99, 60, 0);

        self::assertSame(600.0, $split['team_share']);
        self::assertSame(399.99, $split['investor_share']);
        self::assertSame(600.0 + 399.99, $split['team_share'] + $split['investor_share']);
    }

    public function test_loss_weeks_do_not_create_payable_shares(): void
    {
        $split = ProfitSettlementService::split(-250_000, 50, 10);

        self::assertSame(0.0, $split['team_share']);
        self::assertSame(0.0, $split['investor_share']);
        self::assertSame(0.0, $split['team_vat']);
        self::assertSame(0.0, $split['investor_vat']);
    }

    public function test_week_runs_from_saturday_to_friday(): void
    {
        [$start, $end] = ProfitSettlementService::weekRange(Carbon::parse('2026-11-16'));

        self::assertSame('2026-11-14', $start->toDateString());
        self::assertSame('2026-11-20', $end->toDateString());
        self::assertSame(Carbon::SATURDAY, $start->dayOfWeek);
        self::assertSame(Carbon::FRIDAY, $end->dayOfWeek);
    }
}
