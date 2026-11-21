<?php

namespace App\Http\Controllers;

use App\Enums\PayrollStatus;
use App\Models\Employee;
use App\Models\Payroll;
use App\Models\ProfitSettlement;
use Illuminate\View\View;

class TeamPaymentsController extends Controller
{
    public function index(): View
    {
        $settlements = ProfitSettlement::query()->orderByDesc('week_start')->get();
        $employees = Employee::query()->where('is_active', true)->orderBy('first_name')->get();
        $memberCount = max(1, $employees->count());

        $teamPayable = (float) $settlements->sum(fn (ProfitSettlement $settlement) => $settlement->totalTeamPayable());
        $paidTeamPayable = (float) $settlements
            ->where('status', 'paid')
            ->sum(fn (ProfitSettlement $settlement) => $settlement->totalTeamPayable());
        $pendingTeamPayable = (float) $settlements
            ->where('status', 'confirmed')
            ->sum(fn (ProfitSettlement $settlement) => $settlement->totalTeamPayable());
        $profitSharePerMember = $teamPayable / $memberCount;

        $payrolls = Payroll::query()
            ->with('employee')
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->limit(100)
            ->get();
        $paidPayrolls = $payrolls->filter(fn (Payroll $payroll) => $payroll->status === PayrollStatus::Paid);
        $payrollPaidTotal = (float) $paidPayrolls->sum('net_payment');

        $members = $employees->map(function (Employee $employee) use ($profitSharePerMember, $paidPayrolls): array {
            $payrollPaid = (float) $paidPayrolls->where('employee_id', $employee->id)->sum('net_payment');

            return [
                'name' => trim($employee->first_name.' '.$employee->last_name),
                'profit_share' => $profitSharePerMember,
                'payroll_paid' => $payrollPaid,
                'total' => $profitSharePerMember + $payrollPaid,
            ];
        });

        return view('team-payments.index', [
            'settlements' => $settlements,
            'payrolls' => $payrolls,
            'members' => $members,
            'memberCount' => $employees->count(),
            'teamPayable' => $teamPayable,
            'paidTeamPayable' => $paidTeamPayable,
            'pendingTeamPayable' => $pendingTeamPayable,
            'profitSharePerMember' => $profitSharePerMember,
            'payrollPaidTotal' => $payrollPaidTotal,
            'totalPaidToTeam' => $paidTeamPayable + $payrollPaidTotal,
        ]);
    }
}
