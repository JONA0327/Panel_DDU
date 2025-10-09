<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestLogin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:login {email} {password}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test complete DDU login process';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $password = $this->argument('password');

        $this->info("🧪 Probando sistema de login DDU...");
        $this->line("📧 Usuario: {$email}");
        $this->line("🔑 Password: {$password}");
        $this->line('');

        try {
            // Paso 1: Verificar si el usuario existe
            $this->info('1️⃣ Verificando usuario...');
            $user = \App\Models\User::where('email', $email)->first();

            if (!$user) {
                $this->error("   ❌ Usuario no encontrado");
                return 1;
            }
            $this->line("   ✅ Usuario encontrado: ID {$user->id}");

            // Paso 2: Verificar contraseña
            $this->info('2️⃣ Verificando contraseña...');
            $passwordCorrect = password_verify($password, $user->password);

            if (!$passwordCorrect) {
                $this->error("   ❌ Contraseña incorrecta");
                $this->line("   Hash almacenado: " . substr($user->password, 0, 20) . "...");
                return 1;
            }
            $this->line("   ✅ Contraseña correcta");

            // Paso 3: Verificar membresía DDU
            $this->info('3️⃣ Verificando membresía DDU...');
            $isDduMember = \App\Models\UserPanelMiembro::isDduMember($email);

            if (!$isDduMember) {
                $this->error("   ❌ Usuario NO es miembro DDU");
                $this->warn("   🚫 Acceso restringido: Solo personal autorizado de DDU puede acceder");
                return 1;
            }
            $this->line("   ✅ Usuario ES miembro DDU válido");

            // Paso 4: Login exitoso
            $this->info('4️⃣ Resultado final...');
            $this->line('');
            $this->info('🎉 ¡LOGIN EXITOSO!');
            $this->line("✅ Todas las validaciones pasaron");
            $this->line("✅ El usuario puede acceder al sistema");
            $this->line('');

            // Simular inicio de sesión
            \Illuminate\Support\Facades\Auth::login($user);
            $this->line("🔐 Sesión iniciada para: " . \Illuminate\Support\Facades\Auth::user()->email);

        } catch (\Exception $e) {
            $this->error("❌ Error durante el proceso: " . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
