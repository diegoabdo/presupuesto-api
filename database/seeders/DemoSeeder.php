<?php

namespace Database\Seeders;

use App\Models\BudgetCategory;
use App\Models\DeductionRule;
use App\Models\IncomeSource;
use App\Models\PayPeriod;
use App\Models\SavingsGoal;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    /**
     * Puebla la base de datos con exactamente los mismos números que se
     * usaron en el diseño y en los datos de ejemplo de la app en Flutter,
     * para que lo primero que veas al conectar el backend ya te sea familiar.
     */
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'diego@example.com'],
            ['name' => 'Diego Abdo', 'password' => Hash::make('password')]
        );

        $igss = DeductionRule::firstOrCreate(
            ['user_id' => $user->id, 'label' => 'Cuota IGSS'],
            ['percentage' => 0.0483]
        );

        IncomeSource::insert([
            ['user_id' => $user->id, 'label' => 'Salario base', 'default_amount' => 2875, 'is_recurring' => true, 'created_at' => now(), 'updated_at' => now()],
            ['user_id' => $user->id, 'label' => 'Bonificación incentivo', 'default_amount' => 250, 'is_recurring' => true, 'created_at' => now(), 'updated_at' => now()],
            ['user_id' => $user->id, 'label' => 'Parking bonus', 'default_amount' => 200, 'is_recurring' => false, 'created_at' => now(), 'updated_at' => now()],
            ['user_id' => $user->id, 'label' => 'Bono 14', 'default_amount' => 5750, 'is_recurring' => false, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $this->seedPayPeriod($user, $igss, [
            'period_start' => '2026-07-01', 'period_end' => '2026-07-31', 'pay_date' => '2026-08-05',
            'work_schedule' => 'tiempo_completo',
            'incomes' => [
                ['label' => 'Salario base', 'amount' => 5750],
                ['label' => 'Bono 14', 'amount' => 5750],
            ],
            'deduction_base' => 5750,
        ]);

        $this->seedPayPeriod($user, $igss, [
            'period_start' => '2026-08-01', 'period_end' => '2026-08-31', 'pay_date' => '2026-09-05',
            'work_schedule' => 'medio_tiempo',
            'incomes' => [
                ['label' => 'Salario base', 'amount' => 2875],
                ['label' => 'Bonificación incentivo', 'amount' => 250],
            ],
            'deduction_base' => 2875,
        ]);

        $this->seedPayPeriod($user, $igss, [
            'period_start' => '2026-09-01', 'period_end' => '2026-09-30', 'pay_date' => '2026-10-05',
            'work_schedule' => 'medio_tiempo',
            'incomes' => [
                ['label' => 'Salario base', 'amount' => 2875],
                ['label' => 'Bonificación incentivo', 'amount' => 250],
                ['label' => 'Parking bonus', 'amount' => 200],
            ],
            'deduction_base' => 2875,
        ]);

        $categories = [
            ['name' => 'Renta', 'monthly_budget' => 1500, 'spent' => 1500],
            ['name' => 'Comida', 'monthly_budget' => 900, 'spent' => 745.50],
            ['name' => 'Transporte', 'monthly_budget' => 400, 'spent' => 380],
            ['name' => 'Salidas y ocio', 'monthly_budget' => 350, 'spent' => 410],
            ['name' => 'Servicios', 'monthly_budget' => 300, 'spent' => 275],
        ];

        foreach ($categories as $data) {
            $category = BudgetCategory::firstOrCreate(
                ['user_id' => $user->id, 'name' => $data['name']],
                ['monthly_budget' => $data['monthly_budget']]
            );

            // Se registra como un solo gasto "de ejemplo" este mes, solo
            // para que el acumulado de la categoría coincida con la demo.
            $category->expenses()->create([
                'user_id' => $user->id,
                'amount' => $data['spent'],
                'note' => 'Gasto de ejemplo',
                'spent_at' => now(),
            ]);
        }

        $engancheCasa = SavingsGoal::firstOrCreate(
            ['user_id' => $user->id, 'name' => 'Enganche de casa'],
            ['target_amount' => 120000, 'target_date' => '2028-03-01']
        );
        $engancheCasa->contributions()->delete();
        $engancheCasa->contributions()->createMany([
            ['amount' => 1200, 'contributed_at' => '2026-06-05'],
            ['amount' => 1800, 'contributed_at' => '2026-07-10'],
            ['amount' => 1200, 'contributed_at' => '2026-08-05'],
            ['amount' => 1200, 'contributed_at' => '2026-09-05'],
        ]);

        $fondoEmergencia = SavingsGoal::firstOrCreate(
            ['user_id' => $user->id, 'name' => 'Fondo de emergencia'],
            ['target_amount' => 15000, 'target_date' => null]
        );
        $fondoEmergencia->contributions()->delete();
        $fondoEmergencia->contributions()->createMany([
            ['amount' => 1500, 'contributed_at' => '2026-07-20'],
            ['amount' => 1000, 'contributed_at' => '2026-08-15'],
            ['amount' => 700, 'contributed_at' => '2026-09-10'],
        ]);
    }

    private function seedPayPeriod(User $user, DeductionRule $igss, array $data): void
    {
        $exists = $user->payPeriods()->where('pay_date', $data['pay_date'])->exists();

        if ($exists) {
            return;
        }

        $payPeriod = $user->payPeriods()->create([
            'period_start' => $data['period_start'],
            'period_end' => $data['period_end'],
            'pay_date' => $data['pay_date'],
            'payment_method' => 'Transferencia',
            'work_schedule' => $data['work_schedule'],
        ]);

        foreach ($data['incomes'] as $income) {
            $payPeriod->incomes()->create($income);
        }

        $payPeriod->deductions()->create([
            'label' => $igss->label,
            'percentage' => $igss->percentage,
            'base_amount' => $data['deduction_base'],
            'amount' => round($data['deduction_base'] * $igss->percentage, 2),
        ]);
    }
}
