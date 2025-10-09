<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestDduUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:ddu-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test DDU users and bcryptjs passwords';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Verificando miembros DDU...');

        try {
            // Buscar miembros DDU
            $members = \App\Models\UserPanelMiembro::with(['user', 'panel'])
                ->whereHas('panel', function($q) {
                    $q->where('company_name', 'DDU');
                })
                ->get();

            if ($members->isEmpty()) {
                $this->warn('❌ No se encontraron miembros DDU válidos');
            } else {
                $this->info('✅ Miembros DDU encontrados:');
                foreach ($members as $member) {
                    if ($member->user) {
                        $this->line("📧 {$member->user->email}");
                        $this->line("   Panel: {$member->panel->company_name}");
                        $this->line("   Role: {$member->role}");
                        $this->line('');
                    }
                }
            }

            // Probar validación con usuarios específicos
            $this->info('🧪 Probando validación DDU...');
            $testEmails = ['ddujuntify@gmail.com', 'tc5426244@gmail.com'];

            foreach ($testEmails as $email) {
                try {
                    $isDdu = \App\Models\UserPanelMiembro::isDduMember($email);
                    $icon = $isDdu ? '✅' : '❌';
                    $this->line("{$icon} {$email}: " . ($isDdu ? 'ES miembro DDU' : 'NO es miembro DDU'));
                } catch (\Exception $e) {
                    $this->line("❌ {$email}: Error - " . $e->getMessage());
                }
            }

        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
        }

        return 0;
    }
}
